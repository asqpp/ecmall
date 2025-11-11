<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agent_model extends MY_Model {

    protected $table = 'agent';
    protected $primary_key = 'agent_id';
    protected $timestamps = false;

    /**
     * Get agents with pagination and search
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '') {
        $offset = ($page - 1) * $per_page;

        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('agent_name', $search);
            $this->db->or_like('contact_person', $search);
            $this->db->or_like('mobile', $search);
            $this->db->or_like('email', $search);
            $this->db->group_end();
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('agent_name', 'ASC');
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
     * Get agents for dropdown
     */
    public function get_for_dropdown() {
        $this->db->select('agent_id, agent_name');
        $this->db->from($this->table);
        $this->db->order_by('agent_name', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get agent with commission summary
     */
    public function get_with_commission($agent_id) {
        $this->db->select('a.*,
            COUNT(i.invoice_id) as total_invoices,
            COALESCE(SUM(i.grand_total), 0) as total_sales,
            COALESCE(SUM(ac.commission_amount), 0) as total_commission,
            COALESCE(SUM(ac.paid_amount), 0) as commission_paid,
            (COALESCE(SUM(ac.commission_amount), 0) - COALESCE(SUM(ac.paid_amount), 0)) as commission_due
        ');
        $this->db->from($this->table . ' a');
        $this->db->join('invoice i', 'a.agent_id = i.agent_id', 'left');
        $this->db->join('agent_commission ac', 'i.invoice_id = ac.invoice_id', 'left');
        $this->db->where('a.agent_id', $agent_id);
        $this->db->group_by('a.agent_id');

        return $this->db->get()->row();
    }

    /**
     * Generate agent code
     */
    public function generate_code() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'AGT-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
    }
}
