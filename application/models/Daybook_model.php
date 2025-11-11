<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daybook_model extends MY_Model {

    protected $table = 'daybook';
    protected $primary_key = 'id';
    protected $timestamps = false;

    /**
     * Post a single entry to daybook
     */
    public function post_entry($entry_data) {
        // Validate entry
        if (empty($entry_data['account_code']) || empty($entry_data['date'])) {
            return false;
        }

        // Ensure debit and credit are set
        $entry_data['debit'] = $entry_data['debit'] ?? 0;
        $entry_data['credit'] = $entry_data['credit'] ?? 0;

        // Insert entry
        return $this->db->insert($this->table, $entry_data);
    }

    /**
     * Post multiple entries (journal entry)
     */
    public function post_entries($entries) {
        $this->db->trans_start();

        foreach ($entries as $entry) {
            $this->post_entry($entry);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Get daybook entries with pagination
     */
    public function get_paginated($per_page = 50, $page = 1, $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('d.*, a.account_name, a.account_type');
        $this->db->from($this->table . ' d');
        $this->db->join('accounts a', 'd.account_code = a.account_code', 'left');

        // Apply filters
        if (!empty($filters['account_code'])) {
            $this->db->where('d.account_code', $filters['account_code']);
        }

        if (!empty($filters['from_date'])) {
            $this->db->where('d.date >=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $this->db->where('d.date <=', $filters['to_date']);
        }

        if (!empty($filters['reference_type'])) {
            $this->db->where('d.reference_type', $filters['reference_type']);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('d.date', 'DESC');
        $this->db->order_by('d.id', 'DESC');
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
     * Get entries by reference
     */
    public function get_by_reference($reference_type, $reference_id) {
        $this->db->select('d.*, a.account_name, a.account_type');
        $this->db->from($this->table . ' d');
        $this->db->join('accounts a', 'd.account_code = a.account_code', 'left');
        $this->db->where('d.reference_type', $reference_type);
        $this->db->where('d.reference_id', $reference_id);
        $this->db->order_by('d.id', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Reverse entries (for deletion/cancellation)
     */
    public function reverse_entries($reference_type, $reference_id, $reversal_date = null) {
        $this->db->trans_start();

        // Get original entries
        $entries = $this->get_by_reference($reference_type, $reference_id);

        if (empty($entries)) {
            return false;
        }

        $reversal_date = $reversal_date ?? date('Y-m-d');

        // Create reversal entries (swap debit and credit)
        foreach ($entries as $entry) {
            $reversal = [
                'date' => $reversal_date,
                'account_code' => $entry->account_code,
                'description' => 'Reversal: ' . $entry->description,
                'debit' => $entry->credit,  // Swap
                'credit' => $entry->debit,  // Swap
                'reference_type' => $reference_type . '_reversal',
                'reference_id' => $reference_id
            ];

            $this->post_entry($reversal);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Get cash book (all cash transactions)
     */
    public function get_cash_book($from_date = null, $to_date = null) {
        $this->db->select('d.*, a.account_name');
        $this->db->from($this->table . ' d');
        $this->db->join('accounts a', 'd.account_code = a.account_code', 'left');
        $this->db->where('d.account_code', 'CASH');

        if ($from_date) {
            $this->db->where('d.date >=', $from_date);
        }

        if ($to_date) {
            $this->db->where('d.date <=', $to_date);
        }

        $this->db->order_by('d.date', 'ASC');
        $this->db->order_by('d.id', 'ASC');

        $entries = $this->db->get()->result();

        // Calculate running balance
        $running_balance = 0;
        foreach ($entries as $entry) {
            $running_balance += ($entry->debit - $entry->credit);
            $entry->running_balance = $running_balance;
        }

        return $entries;
    }

    /**
     * Get bank book (all bank transactions)
     */
    public function get_bank_book($bank_account_code = 'BANK', $from_date = null, $to_date = null) {
        $this->db->select('d.*, a.account_name');
        $this->db->from($this->table . ' d');
        $this->db->join('accounts a', 'd.account_code = a.account_code', 'left');
        $this->db->where('d.account_code', $bank_account_code);

        if ($from_date) {
            $this->db->where('d.date >=', $from_date);
        }

        if ($to_date) {
            $this->db->where('d.date <=', $to_date);
        }

        $this->db->order_by('d.date', 'ASC');
        $this->db->order_by('d.id', 'ASC');

        $entries = $this->db->get()->result();

        // Calculate running balance
        $running_balance = 0;
        foreach ($entries as $entry) {
            $running_balance += ($entry->debit - $entry->credit);
            $entry->running_balance = $running_balance;
        }

        return $entries;
    }

    /**
     * Validate double-entry (debits = credits)
     */
    public function validate_entries($entries) {
        $total_debit = 0;
        $total_credit = 0;

        foreach ($entries as $entry) {
            $total_debit += ($entry['debit'] ?? 0);
            $total_credit += ($entry['credit'] ?? 0);
        }

        // Allow small rounding differences (0.01)
        return abs($total_debit - $total_credit) < 0.01;
    }
}
