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
     * Enhanced with stock tracking
     */
    public function create_purchase($purchase_data, $items) {
        $this->db->trans_start();

        // Insert purchase
        $this->db->insert($this->table, $purchase_data);
        $purchase_id = $this->db->insert_id();

        // Insert items and update stock
        foreach ($items as $item) {
            $item['purchase_id'] = $purchase_id;
            $this->db->insert('purchase_item', $item);
            $item_id = $this->db->insert_id();

            // Update product stock (increase inventory)
            $this->update_product_stock(
                $item['product_id'],
                $item['quantity'],
                'add',
                $purchase_data['purchase_date'],
                $purchase_id
            );

            // Create batch tracking if batch info provided
            if (!empty($item['batch'])) {
                $this->create_batch_entry([
                    'product_id' => $item['product_id'],
                    'batch' => $item['batch'],
                    'weight' => $item['weight'] ?? 0,
                    'touch' => $item['touch'] ?? 0,
                    'less_weight' => $item['less_weight'] ?? 0,
                    'amount' => $item['total_price'],
                    'issued_weight' => 0,
                    'pending' => 1,
                    'transaction_date' => $purchase_data['purchase_date'],
                    'document_no' => $purchase_data['chalan_no'],
                    'purchase_item_id' => $item_id
                ]);
            }

            // Update stock ledger (itemsstk table)
            $this->update_stock_ledger([
                'product_id' => $item['product_id'],
                'transaction_date' => $purchase_data['purchase_date'],
                'document_no' => $purchase_data['chalan_no'],
                'transaction_type' => 'purchase',
                'quantity_in' => $item['quantity'],
                'quantity_out' => 0,
                'rate' => $item['rate'],
                'reference_id' => $purchase_id
            ]);
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
     * Update product stock quantity
     *
     * @param int $product_id Product ID
     * @param float $quantity Quantity to add/subtract
     * @param string $operation 'add' or 'subtract'
     * @param string $date Transaction date
     * @param int $reference_id Reference transaction ID
     * @return bool
     */
    public function update_product_stock($product_id, $quantity, $operation = 'add', $date = null, $reference_id = null) {
        // Get current product details
        $this->db->select('quantity, price');
        $this->db->where('product_id', $product_id);
        $product = $this->db->get('product_information')->row();

        if (!$product) {
            return false;
        }

        // Calculate new quantity
        if ($operation == 'add') {
            $new_quantity = $product->quantity + $quantity;
        } else {
            $new_quantity = $product->quantity - $quantity;
        }

        // Update product quantity
        $this->db->where('product_id', $product_id);
        $this->db->update('product_information', [
            'quantity' => $new_quantity
        ]);

        return true;
    }

    /**
     * Create batch tracking entry (for items with batch/lot numbers)
     *
     * @param array $batch_data Batch data
     * @return int|false Batch ID or false
     */
    public function create_batch_entry($batch_data) {
        // Check if oglist table exists
        if (!$this->db->table_exists('oglist')) {
            return false;
        }

        $entry = [
            'code' => $batch_data['product_id'],
            'batch' => $batch_data['batch'],
            'weight' => $batch_data['weight'],
            'touch' => $batch_data['touch'],
            'lesswgt' => $batch_data['less_weight'],
            'amount' => $batch_data['amount'],
            'issuedwgt' => $batch_data['issued_weight'],
            'pend' => $batch_data['pending'],
            'tdate' => $batch_data['transaction_date'],
            'docno' => $batch_data['document_no']
        ];

        $this->db->insert('oglist', $entry);
        return $this->db->insert_id();
    }

    /**
     * Update stock ledger (itemsstk table)
     *
     * @param array $ledger_data Stock ledger data
     * @return int|false Entry ID or false
     */
    public function update_stock_ledger($ledger_data) {
        // Check if itemsstk table exists
        if (!$this->db->table_exists('itemsstk')) {
            return false;
        }

        $entry = [
            'code' => $ledger_data['product_id'],
            'tdate' => $ledger_data['transaction_date'],
            'docno' => $ledger_data['document_no'],
            'ttype' => $ledger_data['transaction_type'],
            'qtyin' => $ledger_data['quantity_in'],
            'qtyout' => $ledger_data['quantity_out'],
            'rate' => $ledger_data['rate']
        ];

        $this->db->insert('itemsstk', $entry);
        return $this->db->insert_id();
    }

    /**
     * Update payment status based on payments received
     */
    public function update_payment_status($purchase_id) {
        // Get purchase total
        $this->db->select('grand_total_amount');
        $this->db->where('purchase_id', $purchase_id);
        $purchase = $this->db->get($this->table)->row();

        if (!$purchase) {
            return false;
        }

        // Get total payments
        $this->db->select('SUM(amount) as total_paid');
        $this->db->where('purchase_id', $purchase_id);
        $payment_result = $this->db->get('payment')->row();
        $total_paid = $payment_result->total_paid ?? 0;

        // Determine payment status
        if ($total_paid >= $purchase->grand_total_amount) {
            $payment_status = 'paid';
        } elseif ($total_paid > 0) {
            $payment_status = 'partial';
        } else {
            $payment_status = 'unpaid';
        }

        // Update purchase
        $this->db->where('purchase_id', $purchase_id);
        $this->db->update($this->table, ['payment_status' => $payment_status]);

        return true;
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
