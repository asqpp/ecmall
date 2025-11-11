<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_model extends MY_Model {

    protected $table = 'supplier_information';
    protected $primary_key = 'supplier_id';
    protected $timestamps = false;

    /**
     * Get suppliers with pagination and search
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '') {
        $offset = ($page - 1) * $per_page;

        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('supplier_name', $search);
            $this->db->or_like('supplier_mobile', $search);
            $this->db->or_like('emailnumber', $search);
            $this->db->or_like('contact', $search);
            $this->db->group_end();
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('supplier_name', 'ASC');
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
     * Get suppliers with outstanding balance
     */
    public function get_with_outstanding() {
        $this->db->select('s.*,
            COALESCE(SUM(p.grand_total_amount), 0) as total_purchases,
            COALESCE(SUM(pay.amount), 0) as total_paid,
            (COALESCE(SUM(p.grand_total_amount), 0) - COALESCE(SUM(pay.amount), 0)) as outstanding
        ');
        $this->db->from($this->table . ' s');
        $this->db->join('product_purchase p', 's.supplier_id = p.supplier_id', 'left');
        $this->db->join('payment pay', 'p.purchase_id = pay.purchase_id', 'left');
        $this->db->group_by('s.supplier_id');
        $this->db->having('outstanding >', 0);

        return $this->db->get()->result();
    }

    /**
     * Get top suppliers by purchase value
     */
    public function get_top_suppliers($limit = 10) {
        $this->db->select('s.*,
            COUNT(p.purchase_id) as purchase_count,
            COALESCE(SUM(p.grand_total_amount), 0) as total_purchase
        ');
        $this->db->from($this->table . ' s');
        $this->db->join('product_purchase p', 's.supplier_id = p.supplier_id', 'left');
        $this->db->group_by('s.supplier_id');
        $this->db->order_by('total_purchase', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    /**
     * Get supplier ledger with all transactions
     */
    public function get_ledger($supplier_id, $from_date = null, $to_date = null) {
        $this->db->select("
            'Purchase' as type,
            p.purchase_id as reference,
            p.purchase_date as date,
            p.chalan_no as description,
            p.grand_total_amount as debit,
            0 as credit,
            p.grand_total_amount as balance
        ", FALSE);
        $this->db->from('product_purchase p');
        $this->db->where('p.supplier_id', $supplier_id);

        if ($from_date) $this->db->where('p.purchase_date >=', $from_date);
        if ($to_date) $this->db->where('p.purchase_date <=', $to_date);

        $purchases_query = $this->db->get_compiled_select();

        $this->db->select("
            'Payment' as type,
            pay.payment_id as reference,
            pay.payment_date as date,
            pay.details as description,
            0 as debit,
            pay.amount as credit,
            -pay.amount as balance
        ", FALSE);
        $this->db->from('payment pay');
        $this->db->join('product_purchase p', 'pay.purchase_id = p.purchase_id');
        $this->db->where('p.supplier_id', $supplier_id);

        if ($from_date) $this->db->where('pay.payment_date >=', $from_date);
        if ($to_date) $this->db->where('pay.payment_date <=', $to_date);

        $payments_query = $this->db->get_compiled_select();

        // Combine and order by date
        $query = $this->db->query("
            SELECT * FROM (
                $purchases_query
                UNION ALL
                $payments_query
            ) as ledger
            ORDER BY date ASC
        ");

        // Calculate running balance
        $ledger = $query->result();
        $running_balance = 0;
        foreach ($ledger as $entry) {
            $running_balance += $entry->balance;
            $entry->running_balance = $running_balance;
        }

        return $ledger;
    }

    /**
     * Generate supplier code
     */
    public function generate_code() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'SUPP-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if supplier mobile already exists
     */
    public function mobile_exists($mobile, $exclude_id = null) {
        $this->db->where('supplier_mobile', $mobile);
        if ($exclude_id) {
            $this->db->where($this->primary_key . ' !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }
}
