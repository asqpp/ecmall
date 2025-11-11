<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_model extends MY_Model {

    protected $table = 'product_purchase';
    protected $primary_key = 'purchase_id';
    protected $timestamps = false;

    /**
     * Get purchases with pagination and search
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '', $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('p.*, s.supplier_name, s.supplier_mobile');
        $this->db->from($this->table . ' p');
        $this->db->join('supplier_information s', 'p.supplier_id = s.supplier_id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p.chalan_no', $search);
            $this->db->or_like('s.supplier_name', $search);
            $this->db->or_like('s.supplier_mobile', $search);
            $this->db->group_end();
        }

        // Apply filters
        if (!empty($filters['from_date'])) {
            $this->db->where('p.purchase_date >=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $this->db->where('p.purchase_date <=', $filters['to_date']);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('p.purchase_date', 'DESC');
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
     * Get purchase with full details
     */
    public function get_purchase_details($purchase_id) {
        // Get purchase header
        $this->db->select('p.*, s.*');
        $this->db->from($this->table . ' p');
        $this->db->join('supplier_information s', 'p.supplier_id = s.supplier_id', 'left');
        $this->db->where('p.purchase_id', $purchase_id);
        $purchase = $this->db->get()->row();

        if (!$purchase) {
            return null;
        }

        // Get purchase items
        $this->db->select('pi.*, prod.product_name, prod.product_model');
        $this->db->from('purchase_item pi');
        $this->db->join('product_information prod', 'pi.product_id = prod.product_id', 'left');
        $this->db->where('pi.purchase_id', $purchase_id);
        $purchase->items = $this->db->get()->result();

        // Get payments
        $this->db->select('*');
        $this->db->from('payment');
        $this->db->where('purchase_id', $purchase_id);
        $purchase->payments = $this->db->get()->result();

        return $purchase;
    }

    /**
     * Create purchase with items and accounting entries
     */
    public function create_purchase($purchase_data, $items) {
        $this->db->trans_start();

        // Insert purchase
        $this->db->insert($this->table, $purchase_data);
        $purchase_id = $this->db->insert_id();

        // Insert items
        foreach ($items as $item) {
            $item['purchase_id'] = $purchase_id;
            $this->db->insert('purchase_item', $item);
        }

        // Post to daybook (double-entry)
        $this->load->model('Daybook_model');

        // Dr: Purchases
        $purchase_amount = $purchase_data['grand_total_amount'] - ($purchase_data['vat'] ?? 0);
        $this->Daybook_model->post_entry([
            'date' => $purchase_data['purchase_date'],
            'account_code' => 'PURCH',
            'description' => 'Purchase #' . $purchase_data['chalan_no'],
            'debit' => $purchase_amount,
            'credit' => 0,
            'reference_type' => 'purchase',
            'reference_id' => $purchase_id
        ]);

        // Dr: VAT Recoverable (if VAT exists)
        if (!empty($purchase_data['vat']) && $purchase_data['vat'] > 0) {
            $this->Daybook_model->post_entry([
                'date' => $purchase_data['purchase_date'],
                'account_code' => 'VATREC',
                'description' => 'VAT on Purchase #' . $purchase_data['chalan_no'],
                'debit' => $purchase_data['vat'],
                'credit' => 0,
                'reference_type' => 'purchase',
                'reference_id' => $purchase_id
            ]);
        }

        // Cr: Supplier Account (Payable)
        $this->Daybook_model->post_entry([
            'date' => $purchase_data['purchase_date'],
            'account_code' => 'SUPP_' . $purchase_data['supplier_id'],
            'description' => 'Purchase #' . $purchase_data['chalan_no'],
            'debit' => 0,
            'credit' => $purchase_data['grand_total_amount'],
            'reference_type' => 'purchase',
            'reference_id' => $purchase_id
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status() ? $purchase_id : false;
    }

    /**
     * Generate purchase/chalan number
     */
    public function generate_chalan_number() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'PUR-' . date('Y') . '-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    }
}
