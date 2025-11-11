<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customer Model
 * Handles customer data operations
 */
class Customer_model extends MY_Model {

    protected $table = 'customer_information';
    protected $primary_key = 'customer_id';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get customers with pagination and search
     *
     * @param int $per_page Items per page
     * @param int $page Current page
     * @param string $search Search term
     * @return object
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '') {
        $offset = ($page - 1) * $per_page;

        // Build query
        $this->db->select('*');
        $this->db->from($this->table);

        // Apply search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('customer_name', $search);
            $this->db->or_like('customer_code', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('phone', $search);
            $this->db->group_end();
        }

        // Get total count
        $total = $this->db->count_all_results('', FALSE);

        // Get results
        $this->db->order_by('customer_id', 'DESC');
        $this->db->limit($per_page, $offset);
        $query = $this->db->get();

        return (object) [
            'data' => $query->result(),
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => ceil($total / $per_page),
            'from' => $offset + 1,
            'to' => min($offset + $per_page, $total)
        ];
    }

    /**
     * Get customer with outstanding balance
     *
     * @param int $customer_id Customer ID
     * @return object
     */
    public function get_with_outstanding($customer_id) {
        $this->db->select('
            c.*,
            COALESCE(SUM(i.grand_total), 0) as total_invoiced,
            COALESCE(SUM(i.paid_amount), 0) as total_paid,
            COALESCE(SUM(i.due_amount), 0) as total_outstanding
        ');
        $this->db->from($this->table . ' c');
        $this->db->join('invoice i', 'i.customer_id = c.customer_id', 'left');
        $this->db->where('c.customer_id', $customer_id);
        $this->db->group_by('c.customer_id');

        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get top customers by revenue
     *
     * @param int $limit Number of customers
     * @param string $period Period (month, year, all)
     * @return array
     */
    public function get_top_customers($limit = 10, $period = 'year') {
        $this->db->select('
            c.customer_id,
            c.customer_name,
            c.customer_code,
            c.email,
            c.phone,
            COUNT(DISTINCT i.inv_id) as invoice_count,
            COALESCE(SUM(i.grand_total), 0) as total_revenue,
            COALESCE(SUM(i.paid_amount), 0) as total_paid,
            COALESCE(SUM(i.due_amount), 0) as total_outstanding
        ');
        $this->db->from($this->table . ' c');
        $this->db->join('invoice i', 'i.customer_id = c.customer_id', 'left');

        // Apply period filter
        if ($period == 'month') {
            $this->db->where('MONTH(i.date)', date('m'));
            $this->db->where('YEAR(i.date)', date('Y'));
        } elseif ($period == 'year') {
            $this->db->where('YEAR(i.date)', date('Y'));
        }

        $this->db->where('c.status', 'active');
        $this->db->group_by('c.customer_id');
        $this->db->order_by('total_revenue', 'DESC');
        $this->db->limit($limit);

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get customer ledger
     *
     * @param int $customer_id Customer ID
     * @param string $from_date Start date
     * @param string $to_date End date
     * @return array
     */
    public function get_ledger($customer_id, $from_date = null, $to_date = null) {
        $this->db->select('
            i.invoice,
            i.date,
            i.grand_total as debit,
            i.paid_amount as credit,
            i.due_amount as balance,
            i.payment_status,
            "Invoice" as type
        ');
        $this->db->from('invoice i');
        $this->db->where('i.customer_id', $customer_id);

        if ($from_date) {
            $this->db->where('i.date >=', $from_date);
        }
        if ($to_date) {
            $this->db->where('i.date <=', $to_date);
        }

        $this->db->order_by('i.date', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Generate customer code
     *
     * @return string
     */
    public function generate_code() {
        $this->db->select_max('customer_id');
        $query = $this->db->get($this->table);
        $row = $query->row();

        $next_id = $row->customer_id ? $row->customer_id + 1 : 1;
        return 'CUST-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
    }
}
