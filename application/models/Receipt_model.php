<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Receipt_model extends MY_Model {

    protected $table = 'receipt';
    protected $primary_key = 'receipt_id';
    protected $timestamps = false;

    /**
     * Get receipts with pagination
     */
    public function get_paginated($per_page = 25, $page = 1, $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('r.*,
            c.customer_name,
            i.invoice as invoice_number
        ');
        $this->db->from($this->table . ' r');
        $this->db->join('invoice i', 'r.invoice_id = i.invoice_id', 'left');
        $this->db->join('customer_information c', 'i.customer_id = c.customer_id', 'left');

        // Apply filters
        if (!empty($filters['from_date'])) {
            $this->db->where('r.receipt_date >=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $this->db->where('r.receipt_date <=', $filters['to_date']);
        }

        if (!empty($filters['payment_method'])) {
            $this->db->where('r.payment_method', $filters['payment_method']);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('r.receipt_date', 'DESC');
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
     * Create receipt with accounting entries
     */
    public function create_receipt($receipt_data) {
        $this->db->trans_start();

        // Insert receipt
        $this->db->insert($this->table, $receipt_data);
        $receipt_id = $this->db->insert_id();

        // Get invoice details for customer_id
        $this->db->select('customer_id, invoice');
        $this->db->where('invoice_id', $receipt_data['invoice_id']);
        $invoice = $this->db->get('invoice')->row();

        if ($invoice) {
            // Post to daybook (double-entry)
            $this->load->model('Daybook_model');

            // Dr: Cash/Bank (based on payment method)
            $account_code = ($receipt_data['payment_method'] == 'cash') ? 'CASH' : 'BANK';
            $this->Daybook_model->post_entry([
                'date' => $receipt_data['receipt_date'],
                'account_code' => $account_code,
                'description' => 'Receipt from customer - Invoice #' . $invoice->invoice,
                'debit' => $receipt_data['amount'],
                'credit' => 0,
                'reference_type' => 'receipt',
                'reference_id' => $receipt_id
            ]);

            // Cr: Customer Account (reduces receivable)
            $this->Daybook_model->post_entry([
                'date' => $receipt_data['receipt_date'],
                'account_code' => 'CUST_' . $invoice->customer_id,
                'description' => 'Receipt from customer - Invoice #' . $invoice->invoice,
                'debit' => 0,
                'credit' => $receipt_data['amount'],
                'reference_type' => 'receipt',
                'reference_id' => $receipt_id
            ]);

            // Update invoice payment status
            $this->load->model('Invoice_model');
            $this->Invoice_model->update_payment_status($receipt_data['invoice_id']);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $receipt_id : false;
    }

    /**
     * Get total receipts for an invoice
     */
    public function get_invoice_receipts($invoice_id) {
        $this->db->select('*');
        $this->db->where('invoice_id', $invoice_id);
        $this->db->order_by('receipt_date', 'ASC');

        return $this->db->get($this->table)->result();
    }

    /**
     * Generate receipt number
     */
    public function generate_receipt_number() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'RCP-' . date('Y') . '-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    }
}
