<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotation_model extends MY_Model {

    protected $table = 'quotation';
    protected $primary_key = 'quotation_id';
    protected $timestamps = false;

    /**
     * Get quotations with pagination and search
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '', $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('q.*, c.customer_name, c.customer_mobile');
        $this->db->from($this->table . ' q');
        $this->db->join('customer_information c', 'q.customer_id = c.customer_id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('q.quotation_number', $search);
            $this->db->or_like('c.customer_name', $search);
            $this->db->or_like('c.customer_mobile', $search);
            $this->db->group_end();
        }

        // Apply filters
        if (!empty($filters['status'])) {
            $this->db->where('q.status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $this->db->where('q.quotation_date >=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $this->db->where('q.quotation_date <=', $filters['to_date']);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('q.quotation_date', 'DESC');
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
     * Get quotation with full details
     */
    public function get_quotation_details($quotation_id) {
        // Get quotation header
        $this->db->select('q.*, c.*');
        $this->db->from($this->table . ' q');
        $this->db->join('customer_information c', 'q.customer_id = c.customer_id', 'left');
        $this->db->where('q.quotation_id', $quotation_id);
        $quotation = $this->db->get()->row();

        if (!$quotation) {
            return null;
        }

        // Get quotation items
        $this->db->select('qi.*, p.product_name, p.product_model');
        $this->db->from('quotation_item qi');
        $this->db->join('product_information p', 'qi.product_id = p.product_id', 'left');
        $this->db->where('qi.quotation_id', $quotation_id);
        $quotation->items = $this->db->get()->result();

        return $quotation;
    }

    /**
     * Create quotation with items
     */
    public function create_quotation($quotation_data, $items) {
        $this->db->trans_start();

        // Insert quotation
        $this->db->insert($this->table, $quotation_data);
        $quotation_id = $this->db->insert_id();

        // Insert items
        foreach ($items as $item) {
            $item['quotation_id'] = $quotation_id;
            $this->db->insert('quotation_item', $item);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $quotation_id : false;
    }

    /**
     * Convert quotation to invoice
     */
    public function convert_to_invoice($quotation_id) {
        $quotation = $this->get_quotation_details($quotation_id);

        if (!$quotation || $quotation->status == 'converted') {
            return false;
        }

        $this->load->model('Invoice_model');

        // Prepare invoice data
        $invoice_data = [
            'customer_id' => $quotation->customer_id,
            'date' => date('Y-m-d'),
            'invoice' => $this->Invoice_model->generate_invoice_number(),
            'total' => $quotation->total_amount,
            'grand_total' => $quotation->grand_total,
            'vat' => $quotation->vat_amount ?? 0,
            'payment_status' => 'unpaid'
        ];

        // Prepare invoice items
        $invoice_items = [];
        foreach ($quotation->items as $item) {
            $invoice_items[] = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'rate' => $item->rate,
                'total_price' => $item->total_price
            ];
        }

        // Create invoice
        $invoice_id = $this->Invoice_model->create_invoice($invoice_data, $invoice_items);

        if ($invoice_id) {
            // Update quotation status
            $this->update($quotation_id, [
                'status' => 'converted',
                'converted_invoice_id' => $invoice_id
            ]);

            return $invoice_id;
        }

        return false;
    }

    /**
     * Generate quotation number
     */
    public function generate_quotation_number() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'QUO-' . date('Y') . '-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    }
}
