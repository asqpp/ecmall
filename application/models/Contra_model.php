<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contra_model extends MY_Model {

    protected $table = 'contra_voucher';
    protected $primary_key = 'contra_id';
    protected $timestamps = false;

    /**
     * Get contra vouchers with pagination
     */
    public function get_paginated($per_page = 25, $page = 1, $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('c.*,
            a1.account_name as from_account_name,
            a2.account_name as to_account_name'
        );
        $this->db->from($this->table . ' c');
        $this->db->join('accounts a1', 'c.from_account = a1.account_code', 'left');
        $this->db->join('accounts a2', 'c.to_account = a2.account_code', 'left');

        // Apply filters
        if (!empty($filters['from_date'])) {
            $this->db->where('c.date >=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $this->db->where('c.date <=', $filters['to_date']);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('c.date', 'DESC');
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
     * Create contra voucher (Cash/Bank transfer)
     *
     * Double-Entry:
     * Dr: To Account (destination)
     * Cr: From Account (source)
     */
    public function create_contra($contra_data) {
        $this->db->trans_start();

        // Insert contra header
        $contra_header = [
            'date' => $contra_data['date'],
            'voucher_no' => $this->generate_voucher_number(),
            'from_account' => $contra_data['from_account'], // Source (CASH/BANK)
            'to_account' => $contra_data['to_account'],     // Destination (BANK/CASH)
            'amount' => $contra_data['amount'],
            'description' => $contra_data['description'],
            'cheque_number' => $contra_data['cheque_number'] ?? null,
            'cheque_date' => $contra_data['cheque_date'] ?? null,
            'created_by' => $this->session->userdata('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table, $contra_header);
        $contra_id = $this->db->insert_id();

        // Post to daybook (double-entry)
        $this->load->model('Daybook_model');

        // Entry 1: Debit destination account (money received)
        $this->Daybook_model->post_entry([
            'date' => $contra_data['date'],
            'account_code' => $contra_data['to_account'],
            'description' => 'Contra Voucher: ' . ($contra_data['description'] ?? 'Transfer'),
            'debit' => $contra_data['amount'],
            'credit' => 0,
            'reference_type' => 'contra',
            'reference_id' => $contra_id
        ]);

        // Entry 2: Credit source account (money paid)
        $this->Daybook_model->post_entry([
            'date' => $contra_data['date'],
            'account_code' => $contra_data['from_account'],
            'description' => 'Contra Voucher: ' . ($contra_data['description'] ?? 'Transfer'),
            'debit' => 0,
            'credit' => $contra_data['amount'],
            'reference_type' => 'contra',
            'reference_id' => $contra_id
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status() ? $contra_id : false;
    }

    /**
     * Get contra voucher details with accounting entries
     */
    public function get_contra_details($contra_id) {
        $this->db->select('c.*,
            a1.account_name as from_account_name,
            a2.account_name as to_account_name'
        );
        $this->db->from($this->table . ' c');
        $this->db->join('accounts a1', 'c.from_account = a1.account_code', 'left');
        $this->db->join('accounts a2', 'c.to_account = a2.account_code', 'left');
        $this->db->where('c.contra_id', $contra_id);

        $contra = $this->db->get()->row();

        if ($contra) {
            // Get accounting entries
            $this->load->model('Daybook_model');
            $contra->entries = $this->Daybook_model->get_by_reference('contra', $contra_id);
        }

        return $contra;
    }

    /**
     * Delete contra voucher
     */
    public function delete_contra($contra_id) {
        $this->db->trans_start();

        // Reverse accounting entries
        $this->load->model('Daybook_model');
        $this->Daybook_model->reverse_entries('contra', $contra_id);

        // Delete contra
        $this->delete($contra_id);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Generate contra voucher number
     */
    public function generate_voucher_number() {
        $this->db->select_max($this->primary_key);
        $result = $this->db->get($this->table)->row();
        $next_id = ($result->{$this->primary_key} ?? 0) + 1;

        return 'CV-' . date('Y') . '-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get cash/bank accounts for dropdown
     */
    public function get_cash_bank_accounts() {
        $this->db->select('account_code, account_name');
        $this->db->from('accounts');
        $this->db->where_in('account_code', ['CASH', 'BANK']);
        $this->db->or_like('account_code', 'BANK_', 'after'); // For multiple bank accounts

        return $this->db->get()->result();
    }

    /**
     * Common contra templates
     */
    public function get_templates() {
        return [
            'cash_to_bank' => [
                'name' => 'Deposit Cash to Bank',
                'from_account' => 'CASH',
                'to_account' => 'BANK'
            ],
            'bank_to_cash' => [
                'name' => 'Withdraw Cash from Bank',
                'from_account' => 'BANK',
                'to_account' => 'CASH'
            ],
            'bank_transfer' => [
                'name' => 'Transfer Between Banks',
                'from_account' => 'BANK_1',
                'to_account' => 'BANK_2'
            ]
        ];
    }
}
