<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Journal_model extends MY_Model {

    protected $table = 'journal_voucher';
    protected $primary_key = 'journal_id';
    protected $timestamps = false;

    /**
     * Get journal vouchers with pagination
     */
    public function get_paginated($per_page = 25, $page = 1, $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('*');
        $this->db->from($this->table);

        // Apply filters
        if (!empty($filters['from_date'])) {
            $this->db->where('date >=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $this->db->where('date <=', $filters['to_date']);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('date', 'DESC');
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
     * Get journal voucher with entries
     */
    public function get_journal_details($journal_id) {
        // Get journal header
        $this->db->where('journal_id', $journal_id);
        $journal = $this->db->get($this->table)->row();

        if (!$journal) {
            return null;
        }

        // Get journal entries from daybook
        $this->load->model('Daybook_model');
        $journal->entries = $this->Daybook_model->get_by_reference('journal', $journal_id);

        return $journal;
    }

    /**
     * Create journal voucher with double-entry validation
     */
    public function create_journal($journal_data, $entries) {
        $this->db->trans_start();

        // Validate entries first
        $this->load->model('Daybook_model');
        if (!$this->Daybook_model->validate_entries($entries)) {
            $this->db->trans_rollback();
            return false; // Debits != Credits
        }

        // Insert journal header
        $journal_header = [
            'date' => $journal_data['date'],
            'voucher_no' => $this->generate_voucher_number(),
            'description' => $journal_data['description'],
            'created_by' => $this->session->userdata('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table, $journal_header);
        $journal_id = $this->db->insert_id();

        // Post all entries to daybook
        foreach ($entries as $entry) {
            $this->Daybook_model->post_entry([
                'date' => $journal_data['date'],
                'account_code' => $entry['account_code'],
                'description' => $journal_data['description'],
                'debit' => $entry['debit'] ?? 0,
                'credit' => $entry['credit'] ?? 0,
                'reference_type' => 'journal',
                'reference_id' => $journal_id
            ]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $journal_id : false;
    }

    /**
     * Update journal voucher
     */
    public function update_journal($journal_id, $journal_data, $entries) {
        $this->db->trans_start();

        // Validate new entries
        $this->load->model('Daybook_model');
        if (!$this->Daybook_model->validate_entries($entries)) {
            $this->db->trans_rollback();
            return false;
        }

        // Reverse old entries
        $this->Daybook_model->reverse_entries('journal', $journal_id);

        // Update journal header
        $journal_header = [
            'date' => $journal_data['date'],
            'description' => $journal_data['description'],
            'updated_by' => $this->session->userdata('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('journal_id', $journal_id);
        $this->db->update($this->table, $journal_header);

        // Post new entries
        foreach ($entries as $entry) {
            $this->Daybook_model->post_entry([
                'date' => $journal_data['date'],
                'account_code' => $entry['account_code'],
                'description' => $journal_data['description'],
                'debit' => $entry['debit'] ?? 0,
                'credit' => $entry['credit'] ?? 0,
                'reference_type' => 'journal',
                'reference_id' => $journal_id
            ]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Delete journal voucher
     */
    public function delete_journal($journal_id) {
        $this->db->trans_start();

        // Reverse entries
        $this->load->model('Daybook_model');
        $this->Daybook_model->reverse_entries('journal', $journal_id);

        // Delete journal
        $this->delete($journal_id);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Generate journal voucher number
     */
    public function generate_voucher_number() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'JV-' . date('Y') . '-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get common journal entry templates
     */
    public function get_templates() {
        return [
            'depreciation' => [
                'name' => 'Depreciation Entry',
                'entries' => [
                    ['account_code' => 'DEPREC_EXP', 'debit' => 0, 'credit' => 0],
                    ['account_code' => 'ACCUM_DEPREC', 'debit' => 0, 'credit' => 0]
                ]
            ],
            'accrual' => [
                'name' => 'Accrued Expense',
                'entries' => [
                    ['account_code' => 'EXPENSE', 'debit' => 0, 'credit' => 0],
                    ['account_code' => 'ACCRUED_PAY', 'debit' => 0, 'credit' => 0]
                ]
            ],
            'prepaid' => [
                'name' => 'Prepaid Expense',
                'entries' => [
                    ['account_code' => 'PREPAID', 'debit' => 0, 'credit' => 0],
                    ['account_code' => 'CASH', 'debit' => 0, 'credit' => 0]
                ]
            ]
        ];
    }
}
