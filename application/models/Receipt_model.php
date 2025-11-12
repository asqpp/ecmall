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
     * Enhanced with PDC handling and collection tracking
     */
    public function create_receipt($receipt_data) {
        $this->db->trans_start();

        // Insert receipt
        $this->db->insert($this->table, $receipt_data);
        $receipt_id = $this->db->insert_id();

        // Get invoice details for customer_id
        $this->db->select('customer_id, invoice, grand_total');
        $this->db->where('invoice_id', $receipt_data['invoice_id']);
        $invoice = $this->db->get('invoice')->row();

        if ($invoice) {
            // Load required models
            $this->load->model('Daybook_model');
            $this->load->model('Collection_model');

            // Determine account code based on payment method
            $is_pdc = ($receipt_data['payment_method'] == 'cheque' && !empty($receipt_data['cheque_date']) && $receipt_data['cheque_date'] > date('Y-m-d'));

            if ($is_pdc) {
                // Post-Dated Cheque - Different accounting treatment
                $this->load->model('PDC_model');

                // Create PDC entry
                $pdc_id = $this->PDC_model->create_pdc([
                    'transaction_date' => $receipt_data['receipt_date'],
                    'document_no' => $receipt_data['receipt_voucher_no'] ?? 'RCP-' . $receipt_id,
                    'bank' => $receipt_data['bank'] ?? '',
                    'party_code' => $invoice->customer_id,
                    'cheque_no' => $receipt_data['cheque_no'] ?? '',
                    'cheque_date' => $receipt_data['cheque_date'],
                    'amount' => $receipt_data['amount'],
                    'particulars' => 'Receipt against Invoice #' . $invoice->invoice,
                    'type' => 'R', // Receipt
                    'control' => date('Y-m-d H:i:s')
                ]);

                // Dr: PDC Receivable (Asset - will convert to cash when cleared)
                $this->Daybook_model->post_entry([
                    'date' => $receipt_data['receipt_date'],
                    'account_code' => 'PDCREC',
                    'description' => 'PDC Received - Cheque #' . ($receipt_data['cheque_no'] ?? '') . ' - Invoice #' . $invoice->invoice,
                    'debit' => $receipt_data['amount'],
                    'credit' => 0,
                    'reference_type' => 'pdc',
                    'reference_id' => $pdc_id
                ]);

                // Cr: Customer Account (reduces receivable immediately)
                $this->Daybook_model->post_entry([
                    'date' => $receipt_data['receipt_date'],
                    'account_code' => 'CUST_' . $invoice->customer_id,
                    'description' => 'PDC Received - Cheque #' . ($receipt_data['cheque_no'] ?? '') . ' - Invoice #' . $invoice->invoice,
                    'debit' => 0,
                    'credit' => $receipt_data['amount'],
                    'reference_type' => 'pdc',
                    'reference_id' => $pdc_id
                ]);

            } else {
                // Regular receipt (Cash/Bank/Cleared Cheque)
                $account_code = ($receipt_data['payment_method'] == 'cash') ? 'CASH' : 'BANK';

                // Dr: Cash/Bank
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
            }

            // Create collection entry (legacy system tracking)
            $this->Collection_model->create_collection([
                'customer_id' => $invoice->customer_id,
                'transaction_date' => $receipt_data['receipt_date'],
                'invoice_id' => $receipt_data['invoice_id'],
                'amount' => $receipt_data['amount'],
                'discount' => $receipt_data['discount'] ?? 0,
                'due_date' => $receipt_data['due_date'] ?? null,
                'receipt_id' => $receipt_id,
                'rate' => $receipt_data['rate'] ?? 0,
                'rate2' => $receipt_data['rate2'] ?? 0
            ]);

            // Handle discount if provided
            if (!empty($receipt_data['discount']) && $receipt_data['discount'] > 0) {
                // Dr: Discount Allowed (Expense)
                $this->Daybook_model->post_entry([
                    'date' => $receipt_data['receipt_date'],
                    'account_code' => 'DISCALL',
                    'description' => 'Discount allowed - Invoice #' . $invoice->invoice,
                    'debit' => $receipt_data['discount'],
                    'credit' => 0,
                    'reference_type' => 'receipt',
                    'reference_id' => $receipt_id
                ]);

                // Cr: Customer Account
                $this->Daybook_model->post_entry([
                    'date' => $receipt_data['receipt_date'],
                    'account_code' => 'CUST_' . $invoice->customer_id,
                    'description' => 'Discount allowed - Invoice #' . $invoice->invoice,
                    'debit' => 0,
                    'credit' => $receipt_data['discount'],
                    'reference_type' => 'receipt',
                    'reference_id' => $receipt_id
                ]);
            }

            // Update invoice payment status
            $this->load->model('Invoice_model');
            $this->Invoice_model->update_payment_status($receipt_data['invoice_id']);

            // Update customer due date if provided
            if (!empty($receipt_data['due_date'])) {
                $this->db->where('customer_id', $invoice->customer_id);
                $this->db->update('customer_information', ['duedate' => $receipt_data['due_date']]);
            }
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
