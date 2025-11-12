<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends MY_Model {

    protected $table = 'payment';
    protected $primary_key = 'payment_id';
    protected $timestamps = false;

    /**
     * Get payments with pagination
     */
    public function get_paginated($per_page = 25, $page = 1, $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('pay.*,
            s.supplier_name,
            p.chalan_no as purchase_reference
        ');
        $this->db->from($this->table . ' pay');
        $this->db->join('product_purchase p', 'pay.purchase_id = p.purchase_id', 'left');
        $this->db->join('supplier_information s', 'p.supplier_id = s.supplier_id', 'left');

        // Apply filters
        if (!empty($filters['from_date'])) {
            $this->db->where('pay.payment_date >=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $this->db->where('pay.payment_date <=', $filters['to_date']);
        }

        if (!empty($filters['payment_method'])) {
            $this->db->where('pay.payment_method', $filters['payment_method']);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('pay.payment_date', 'DESC');
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
     * Create payment with accounting entries
     * Enhanced with PDC handling
     */
    public function create_payment($payment_data) {
        $this->db->trans_start();

        // Insert payment
        $this->db->insert($this->table, $payment_data);
        $payment_id = $this->db->insert_id();

        // Get purchase details for supplier_id
        $this->db->select('supplier_id, chalan_no');
        $this->db->where('purchase_id', $payment_data['purchase_id']);
        $purchase = $this->db->get('product_purchase')->row();

        if ($purchase) {
            // Load required models
            $this->load->model('Daybook_model');

            // Determine if this is a post-dated cheque
            $is_pdc = ($payment_data['payment_method'] == 'cheque' && !empty($payment_data['cheque_date']) && $payment_data['cheque_date'] > date('Y-m-d'));

            if ($is_pdc) {
                // Post-Dated Cheque - Different accounting treatment
                $this->load->model('PDC_model');

                // Create PDC entry
                $pdc_id = $this->PDC_model->create_pdc([
                    'transaction_date' => $payment_data['payment_date'],
                    'document_no' => $payment_data['payment_voucher_no'] ?? 'PAY-' . $payment_id,
                    'bank' => $payment_data['bank'] ?? '',
                    'party_code' => $purchase->supplier_id,
                    'cheque_no' => $payment_data['cheque_no'] ?? '',
                    'cheque_date' => $payment_data['cheque_date'],
                    'amount' => $payment_data['amount'],
                    'particulars' => 'Payment for Purchase #' . $purchase->chalan_no,
                    'type' => 'P', // Payment
                    'control' => date('Y-m-d H:i:s')
                ]);

                // Dr: Supplier Account (reduces payable immediately)
                $this->Daybook_model->post_entry([
                    'date' => $payment_data['payment_date'],
                    'account_code' => 'SUPP_' . $purchase->supplier_id,
                    'description' => 'PDC Issued - Cheque #' . ($payment_data['cheque_no'] ?? '') . ' - Purchase #' . $purchase->chalan_no,
                    'debit' => $payment_data['amount'],
                    'credit' => 0,
                    'reference_type' => 'pdc',
                    'reference_id' => $pdc_id
                ]);

                // Cr: PDC Payable (Liability - will convert to bank deduction when cleared)
                $this->Daybook_model->post_entry([
                    'date' => $payment_data['payment_date'],
                    'account_code' => 'PDCPAY',
                    'description' => 'PDC Issued - Cheque #' . ($payment_data['cheque_no'] ?? '') . ' - Purchase #' . $purchase->chalan_no,
                    'debit' => 0,
                    'credit' => $payment_data['amount'],
                    'reference_type' => 'pdc',
                    'reference_id' => $pdc_id
                ]);

            } else {
                // Regular payment (Cash/Bank/Cleared Cheque)

                // Dr: Supplier Account (reduces payable)
                $this->Daybook_model->post_entry([
                    'date' => $payment_data['payment_date'],
                    'account_code' => 'SUPP_' . $purchase->supplier_id,
                    'description' => 'Payment - ' . ($payment_data['details'] ?? 'Supplier Payment'),
                    'debit' => $payment_data['amount'],
                    'credit' => 0,
                    'reference_type' => 'payment',
                    'reference_id' => $payment_id
                ]);

                // Cr: Cash/Bank (reduces asset)
                $account_code = ($payment_data['payment_method'] == 'cash') ? 'CASH' : 'BANK';
                $this->Daybook_model->post_entry([
                    'date' => $payment_data['payment_date'],
                    'account_code' => $account_code,
                    'description' => 'Payment - ' . ($payment_data['details'] ?? 'Supplier Payment'),
                    'debit' => 0,
                    'credit' => $payment_data['amount'],
                    'reference_type' => 'payment',
                    'reference_id' => $payment_id
                ]);
            }

            // Handle discount received if provided
            if (!empty($payment_data['discount']) && $payment_data['discount'] > 0) {
                // Dr: Supplier Account (additional reduction)
                $this->Daybook_model->post_entry([
                    'date' => $payment_data['payment_date'],
                    'account_code' => 'SUPP_' . $purchase->supplier_id,
                    'description' => 'Discount received - Purchase #' . $purchase->chalan_no,
                    'debit' => $payment_data['discount'],
                    'credit' => 0,
                    'reference_type' => 'payment',
                    'reference_id' => $payment_id
                ]);

                // Cr: Discount Received (Income)
                $this->Daybook_model->post_entry([
                    'date' => $payment_data['payment_date'],
                    'account_code' => 'DISCREC',
                    'description' => 'Discount received - Purchase #' . $purchase->chalan_no,
                    'debit' => 0,
                    'credit' => $payment_data['discount'],
                    'reference_type' => 'payment',
                    'reference_id' => $payment_id
                ]);
            }

            // Update purchase payment status
            $this->load->model('Purchase_model');
            $this->Purchase_model->update_payment_status($payment_data['purchase_id']);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $payment_id : false;
    }

    /**
     * Get total payments for a purchase
     */
    public function get_purchase_payments($purchase_id) {
        $this->db->select('*');
        $this->db->where('purchase_id', $purchase_id);
        $this->db->order_by('payment_date', 'ASC');

        return $this->db->get($this->table)->result();
    }
}
