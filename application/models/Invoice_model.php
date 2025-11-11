<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_model extends MY_Model {

    protected $table = 'invoice';
    protected $primary_key = 'invoice_id';
    protected $timestamps = false;

    /**
     * Get invoices with pagination and search
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '', $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('i.*, c.customer_name, c.customer_mobile');
        $this->db->from($this->table . ' i');
        $this->db->join('customer_information c', 'i.customer_id = c.customer_id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('i.invoice', $search);
            $this->db->or_like('c.customer_name', $search);
            $this->db->or_like('c.customer_mobile', $search);
            $this->db->group_end();
        }

        // Apply filters
        if (!empty($filters['payment_status'])) {
            $this->db->where('i.payment_status', $filters['payment_status']);
        }

        if (!empty($filters['from_date'])) {
            $this->db->where('i.date >=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $this->db->where('i.date <=', $filters['to_date']);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('i.date', 'DESC');
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
     * Get invoice with full details (items, customer, payments)
     */
    public function get_invoice_details($invoice_id) {
        // Get invoice header
        $this->db->select('i.*, c.*');
        $this->db->from($this->table . ' i');
        $this->db->join('customer_information c', 'i.customer_id = c.customer_id', 'left');
        $this->db->where('i.invoice_id', $invoice_id);
        $invoice = $this->db->get()->row();

        if (!$invoice) {
            return null;
        }

        // Get invoice items
        $this->db->select('ii.*, p.product_name, p.product_model');
        $this->db->from('invoice_item ii');
        $this->db->join('product_information p', 'ii.product_id = p.product_id', 'left');
        $this->db->where('ii.invoice_id', $invoice_id);
        $invoice->items = $this->db->get()->result();

        // Get payments
        $this->db->select('*');
        $this->db->from('payment');
        $this->db->where('invoice_id', $invoice_id);
        $invoice->payments = $this->db->get()->result();

        return $invoice;
    }

    /**
     * Create invoice with items and accounting entries
     */
    public function create_invoice($invoice_data, $items) {
        $this->db->trans_start();

        // Insert invoice
        $this->db->insert($this->table, $invoice_data);
        $invoice_id = $this->db->insert_id();

        // Insert items
        foreach ($items as $item) {
            $item['invoice_id'] = $invoice_id;
            $this->db->insert('invoice_item', $item);
        }

        // Post to daybook (double-entry)
        $this->load->model('Daybook_model');

        // Dr: Customer Account (Receivable)
        $this->Daybook_model->post_entry([
            'date' => $invoice_data['date'],
            'account_code' => 'CUST_' . $invoice_data['customer_id'],
            'description' => 'Sales Invoice #' . $invoice_data['invoice'],
            'debit' => $invoice_data['grand_total'],
            'credit' => 0,
            'reference_type' => 'invoice',
            'reference_id' => $invoice_id
        ]);

        // Cr: Sales Income
        $sales_amount = $invoice_data['grand_total'] - ($invoice_data['vat'] ?? 0);
        $this->Daybook_model->post_entry([
            'date' => $invoice_data['date'],
            'account_code' => 'SALES',
            'description' => 'Sales Invoice #' . $invoice_data['invoice'],
            'debit' => 0,
            'credit' => $sales_amount,
            'reference_type' => 'invoice',
            'reference_id' => $invoice_id
        ]);

        // Cr: VAT Payable (if VAT exists)
        if (!empty($invoice_data['vat']) && $invoice_data['vat'] > 0) {
            $this->Daybook_model->post_entry([
                'date' => $invoice_data['date'],
                'account_code' => 'VATPAY',
                'description' => 'VAT on Invoice #' . $invoice_data['invoice'],
                'debit' => 0,
                'credit' => $invoice_data['vat'],
                'reference_type' => 'invoice',
                'reference_id' => $invoice_id
            ]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $invoice_id : false;
    }

    /**
     * Generate invoice number
     */
    public function generate_invoice_number() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'INV-' . date('Y') . '-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Update payment status based on paid amount
     */
    public function update_payment_status($invoice_id) {
        // Get invoice total
        $invoice = $this->get_by_id($invoice_id);
        if (!$invoice) return false;

        // Calculate total paid
        $this->db->select_sum('amount');
        $this->db->where('invoice_id', $invoice_id);
        $result = $this->db->get('payment')->row();
        $total_paid = $result->amount ?? 0;

        // Determine status
        if ($total_paid == 0) {
            $status = 'unpaid';
        } elseif ($total_paid >= $invoice->grand_total) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        // Update invoice
        $this->db->where($this->primary_key, $invoice_id);
        $this->db->update($this->table, ['payment_status' => $status]);

        return true;
    }
}
