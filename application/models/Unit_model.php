<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_model extends MY_Model {

    protected $table = 'unit';
    protected $primary_key = 'unit_id';
    protected $timestamps = false;

    public function get_paginated($per_page = 25, $page = 1, $search = '') {
        $offset = ($page - 1) * $per_page;

        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->like('unit_name', $search);
        }

        $total = $this->db->count_all_results('', false);
        $this->db->limit($per_page, $offset);
        $this->db->order_by('unit_name', 'ASC');
        $data = $this->db->get()->result();

        return (object) [
            'data' => $data,
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'total_pages' => ceil($total / $per_page)
        ];
    }

    public function get_for_dropdown() {
        $this->db->select('unit_id, unit_name');
        $this->db->from($this->table);
        $this->db->order_by('unit_name', 'ASC');
        return $this->db->get()->result();
    }
}
