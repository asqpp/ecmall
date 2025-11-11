<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends MY_Model {

    protected $table = 'product_information';
    protected $primary_key = 'product_id';
    protected $timestamps = false;

    /**
     * Get products with pagination and search
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '', $category_id = null) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('p.*, c.category_name');
        $this->db->from($this->table . ' p');
        $this->db->join('category_list c', 'p.category_id = c.category_id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p.product_name', $search);
            $this->db->or_like('p.product_model', $search);
            $this->db->or_like('p.manufacturer', $search);
            $this->db->group_end();
        }

        if ($category_id) {
            $this->db->where('p.category_id', $category_id);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('p.product_name', 'ASC');
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
     * Get product with stock information
     */
    public function get_with_stock($product_id) {
        $this->db->select('p.*,
            COALESCE(SUM(pi.quantity), 0) as total_purchased,
            COALESCE(SUM(ii.quantity), 0) as total_sold,
            (COALESCE(SUM(pi.quantity), 0) - COALESCE(SUM(ii.quantity), 0)) as current_stock
        ');
        $this->db->from($this->table . ' p');
        $this->db->join('purchase_item pi', 'p.product_id = pi.product_id', 'left');
        $this->db->join('invoice_item ii', 'p.product_id = ii.product_id', 'left');
        $this->db->where('p.product_id', $product_id);
        $this->db->group_by('p.product_id');

        return $this->db->get()->row();
    }

    /**
     * Get low stock products
     */
    public function get_low_stock($threshold = 10) {
        $this->db->select('p.*,
            COALESCE(SUM(pi.quantity), 0) as total_purchased,
            COALESCE(SUM(ii.quantity), 0) as total_sold,
            (COALESCE(SUM(pi.quantity), 0) - COALESCE(SUM(ii.quantity), 0)) as current_stock
        ');
        $this->db->from($this->table . ' p');
        $this->db->join('purchase_item pi', 'p.product_id = pi.product_id', 'left');
        $this->db->join('invoice_item ii', 'p.product_id = ii.product_id', 'left');
        $this->db->group_by('p.product_id');
        $this->db->having('current_stock <', $threshold);
        $this->db->order_by('current_stock', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get top selling products
     */
    public function get_top_selling($limit = 10) {
        $this->db->select('p.*,
            COUNT(DISTINCT ii.invoice_id) as sale_count,
            COALESCE(SUM(ii.quantity), 0) as total_quantity,
            COALESCE(SUM(ii.total_price), 0) as total_revenue
        ');
        $this->db->from($this->table . ' p');
        $this->db->join('invoice_item ii', 'p.product_id = ii.product_id', 'left');
        $this->db->group_by('p.product_id');
        $this->db->order_by('total_revenue', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    /**
     * Get all categories for dropdown
     */
    public function get_categories() {
        $this->db->select('category_id, category_name');
        $this->db->from('category_list');
        $this->db->order_by('category_name', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Generate product code
     */
    public function generate_code() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'PROD-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Search products for autocomplete (used in invoice/purchase forms)
     */
    public function search_for_autocomplete($term, $limit = 10) {
        $this->db->select('product_id, product_name, product_model, price, category_id');
        $this->db->from($this->table);
        $this->db->group_start();
        $this->db->like('product_name', $term);
        $this->db->or_like('product_model', $term);
        $this->db->group_end();
        $this->db->limit($limit);

        return $this->db->get()->result();
    }
}
