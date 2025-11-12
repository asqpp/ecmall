<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends MY_Model {

    protected $table = 'category_list';
    protected $primary_key = 'category_id';
    protected $timestamps = false;

    public function get_paginated($per_page = 25, $page = 1, $search = '') {
        $offset = ($page - 1) * $per_page;

        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->like('category_name', $search);
        }

        $total = $this->db->count_all_results('', false);
        $this->db->limit($per_page, $offset);
        $this->db->order_by('category_name', 'ASC');
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
        $this->db->select('category_id, category_name');
        $this->db->from($this->table);
        $this->db->order_by('category_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_with_product_count() {
        $this->db->select('c.*, COUNT(p.product_id) as product_count');
        $this->db->from($this->table . ' c');
        $this->db->join('product_information p', 'c.category_id = p.category_id', 'left');
        $this->db->group_by('c.category_id');
        $this->db->order_by('c.category_name', 'ASC');
        return $this->db->get()->result();
    }
}
