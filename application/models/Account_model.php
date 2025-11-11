<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account_model extends MY_Model {

    protected $table = 'accounts';
    protected $primary_key = 'account_id';
    protected $timestamps = false;

    /**
     * Get accounts with pagination and search
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '', $account_type = null) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('account_name', $search);
            $this->db->or_like('account_code', $search);
            $this->db->group_end();
        }

        if ($account_type) {
            $this->db->where('account_type', $account_type);
        }

        // Get total count
        $total = $this->db->count_all_results('', false);

        // Get paginated results
        $this->db->limit($per_page, $offset);
        $this->db->order_by('account_code', 'ASC');
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
     * Get account by code
     */
    public function get_by_code($account_code) {
        $this->db->where('account_code', $account_code);
        return $this->db->get($this->table)->row();
    }

    /**
     * Get all accounts for dropdown
     */
    public function get_for_dropdown($account_type = null) {
        $this->db->select('account_id, account_code, account_name, account_type');
        $this->db->from($this->table);

        if ($account_type) {
            $this->db->where('account_type', $account_type);
        }

        $this->db->order_by('account_code', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get account ledger with balance
     */
    public function get_ledger($account_code, $from_date = null, $to_date = null) {
        $this->db->select('*');
        $this->db->from('daybook');
        $this->db->where('account_code', $account_code);

        if ($from_date) {
            $this->db->where('date >=', $from_date);
        }

        if ($to_date) {
            $this->db->where('date <=', $to_date);
        }

        $this->db->order_by('date', 'ASC');
        $this->db->order_by('id', 'ASC');

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
     * Get account balance
     */
    public function get_balance($account_code, $as_of_date = null) {
        $this->db->select_sum('debit');
        $this->db->select_sum('credit');
        $this->db->from('daybook');
        $this->db->where('account_code', $account_code);

        if ($as_of_date) {
            $this->db->where('date <=', $as_of_date);
        }

        $result = $this->db->get()->row();

        $debit_total = $result->debit ?? 0;
        $credit_total = $result->credit ?? 0;

        return $debit_total - $credit_total;
    }

    /**
     * Get trial balance (all accounts with balances)
     */
    public function get_trial_balance($as_of_date = null) {
        $this->db->select('a.account_code, a.account_name, a.account_type,
            COALESCE(SUM(d.debit), 0) as total_debit,
            COALESCE(SUM(d.credit), 0) as total_credit
        ');
        $this->db->from($this->table . ' a');
        $this->db->join('daybook d', 'a.account_code = d.account_code', 'left');

        if ($as_of_date) {
            $this->db->where('d.date <=', $as_of_date);
        }

        $this->db->group_by('a.account_id');
        $this->db->order_by('a.account_code', 'ASC');

        $accounts = $this->db->get()->result();

        // Calculate balance for each account
        foreach ($accounts as $account) {
            $account->balance = $account->total_debit - $account->total_credit;

            // Determine debit/credit side based on balance
            if ($account->balance >= 0) {
                $account->debit_balance = $account->balance;
                $account->credit_balance = 0;
            } else {
                $account->debit_balance = 0;
                $account->credit_balance = abs($account->balance);
            }
        }

        return $accounts;
    }

    /**
     * Get account types for dropdown
     */
    public function get_account_types() {
        return [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'income' => 'Income',
            'expense' => 'Expense'
        ];
    }

    /**
     * Check if account code already exists
     */
    public function code_exists($account_code, $exclude_id = null) {
        $this->db->where('account_code', $account_code);
        if ($exclude_id) {
            $this->db->where($this->primary_key . ' !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }
}
