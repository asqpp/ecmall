<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broker_model extends MY_Model {

    protected $table = 'broker';
    protected $primary_key = 'broker_id';
    protected $timestamps = false;

    /**
     * Get brokers with pagination and search
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '') {
        $offset = ($page - 1) * $per_page;

        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('broker_name', $search);
            $this->db->or_like('contact_person', $search);
            $this->db->or_like('mobile', $search);
            $this->db->or_like('email', $search);
            $this->db->group_end();
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('broker_name', 'ASC');
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
     * Get brokers for dropdown
     */
    public function get_for_dropdown() {
        $this->db->select('broker_id, broker_name');
        $this->db->from($this->table);
        $this->db->order_by('broker_name', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get broker with commission summary
     */
    public function get_with_commission($broker_id) {
        $this->db->select('b.*,
            COUNT(i.invoice_id) as total_invoices,
            COALESCE(SUM(i.grand_total), 0) as total_sales,
            COALESCE(SUM(bc.commission_amount), 0) as total_commission,
            COALESCE(SUM(bc.paid_amount), 0) as commission_paid,
            (COALESCE(SUM(bc.commission_amount), 0) - COALESCE(SUM(bc.paid_amount), 0)) as commission_due
        ');
        $this->db->from($this->table . ' b');
        $this->db->join('invoice i', 'b.broker_id = i.broker_id', 'left');
        $this->db->join('broker_commission bc', 'i.invoice_id = bc.invoice_id', 'left');
        $this->db->where('b.broker_id', $broker_id);
        $this->db->group_by('b.broker_id');

        return $this->db->get()->row();
    }

    /**
     * Generate broker code
     */
    public function generate_code() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'BRK-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
    }
}
