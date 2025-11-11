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
     */
    public function create_payment($payment_data) {
        $this->db->trans_start();

        // Insert payment
        $this->db->insert($this->table, $payment_data);
        $payment_id = $this->db->insert_id();

        // Get purchase details for supplier_id
        $this->db->select('supplier_id');
        $this->db->where('purchase_id', $payment_data['purchase_id']);
        $purchase = $this->db->get('product_purchase')->row();

        if ($purchase) {
            // Post to daybook (double-entry)
            $this->load->model('Daybook_model');

            // Dr: Supplier Account (reduces liability)
            $this->Daybook_model->post_entry([
                'date' => $payment_data['payment_date'],
                'account_code' => 'SUPP_' . $purchase->supplier_id,
                'description' => 'Payment - ' . ($payment_data['details'] ?? 'Supplier Payment'),
                'debit' => $payment_data['amount'],
                'credit' => 0,
                'reference_type' => 'payment',
                'reference_id' => $payment_id
            ]);

            // Cr: Cash/Bank (based on payment method)
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
