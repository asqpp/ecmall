<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Collection Model
 *
 * Tracks individual payment collections against invoices
 * Based on legacy collection table structure
 */
class Collection_model extends MY_Model {

    protected $table = 'collection';
    protected $primary_key = 'slno';
    protected $timestamps = false;

    /**
     * Get paginated collections
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '', $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('col.*, c.customer_name, i.invoice as invoice_no');
        $this->db->from($this->table . ' col');
        $this->db->join('customer_information c', 'col.code = c.customer_id', 'left');
        $this->db->join('invoice i', 'col.billno = i.invoice_id', 'left');

        // Search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('c.customer_name', $search);
            $this->db->or_like('i.invoice', $search);
            $this->db->group_end();
        }

        // Filter by customer
        if (!empty($filters['customer_id'])) {
            $this->db->where('col.code', $filters['customer_id']);
        }

        // Filter by invoice
        if (!empty($filters['invoice_id'])) {
            $this->db->where('col.billno', $filters['invoice_id']);
        }

        // Filter by date range
        if (!empty($filters['from_date'])) {
            $this->db->where('col.tdate >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('col.tdate <=', $filters['to_date']);
        }

        $total = $this->db->count_all_results('', false);
        $this->db->limit($per_page, $offset);
        $this->db->order_by('col.tdate', 'DESC');
        $data = $this->db->get()->result();

        return (object) [
            'data' => $data,
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'total_pages' => ceil($total / $per_page)
        ];
    }

    /**
     * Create collection entry
     *
     * @param array $data Collection data
     * @return int|false Collection ID or false
     */
    public function create_collection($data) {
        $collection_data = [
            'code' => $data['customer_id'],
            'tdate' => $data['transaction_date'],
            'billno' => $data['invoice_id'],
            'tranamt' => $data['amount'],
            'discount' => $data['discount'] ?? 0,
            'duedate' => $data['due_date'] ?? null,
            'control' => $data['control'] ?? date('Y-m-d H:i:s'),
            'islno' => $data['receipt_id'] ?? null,
            'grate' => $data['rate'] ?? 0,
            'grate2' => $data['rate2'] ?? 0
        ];

        $this->db->insert($this->table, $collection_data);
        return $this->db->insert_id();
    }

    /**
     * Get collections by customer
     *
     * @param int $customer_id Customer ID
     * @param string $from_date From date
     * @param string $to_date To date
     * @return array
     */
    public function get_by_customer($customer_id, $from_date = null, $to_date = null) {
        $this->db->select('col.*, i.invoice as invoice_no, i.grand_total');
        $this->db->from($this->table . ' col');
        $this->db->join('invoice i', 'col.billno = i.invoice_id', 'left');
        $this->db->where('col.code', $customer_id);

        if ($from_date) {
            $this->db->where('col.tdate >=', $from_date);
        }
        if ($to_date) {
            $this->db->where('col.tdate <=', $to_date);
        }

        $this->db->order_by('col.tdate', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get collections by invoice
     *
     * @param int $invoice_id Invoice ID
     * @return array
     */
    public function get_by_invoice($invoice_id) {
        $this->db->select('col.*, c.customer_name');
        $this->db->from($this->table . ' col');
        $this->db->join('customer_information c', 'col.code = c.customer_id', 'left');
        $this->db->where('col.billno', $invoice_id);
        $this->db->order_by('col.tdate', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get total collected for an invoice
     *
     * @param int $invoice_id Invoice ID
     * @return float
     */
    public function get_total_collected($invoice_id) {
        $this->db->select('SUM(tranamt - discount) as total');
        $this->db->from($this->table);
        $this->db->where('billno', $invoice_id);
        $result = $this->db->get()->row();

        return $result ? (float)$result->total : 0;
    }

    /**
     * Get collection summary for a period
     *
     * @param string $from_date From date
     * @param string $to_date To date
     * @return object
     */
    public function get_summary($from_date, $to_date) {
        $this->db->select('
            COUNT(*) as total_collections,
            SUM(tranamt) as total_amount,
            SUM(discount) as total_discount,
            SUM(tranamt - discount) as net_amount
        ');
        $this->db->from($this->table);
        $this->db->where('tdate >=', $from_date);
        $this->db->where('tdate <=', $to_date);

        return $this->db->get()->row();
    }

    /**
     * Get top collecting customers
     *
     * @param int $limit Number of customers
     * @param string $from_date From date
     * @param string $to_date To date
     * @return array
     */
    public function get_top_collectors($limit = 10, $from_date = null, $to_date = null) {
        $this->db->select('
            col.code,
            c.customer_name,
            COUNT(*) as collection_count,
            SUM(col.tranamt) as total_amount,
            SUM(col.discount) as total_discount
        ');
        $this->db->from($this->table . ' col');
        $this->db->join('customer_information c', 'col.code = c.customer_id', 'left');

        if ($from_date) {
            $this->db->where('col.tdate >=', $from_date);
        }
        if ($to_date) {
            $this->db->where('col.tdate <=', $to_date);
        }

        $this->db->group_by('col.code');
        $this->db->order_by('total_amount', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }
}
