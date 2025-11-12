<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Account_model');
    }

    /**
     * Chart of Accounts - List all accounts
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';
        $account_type = $this->input->get('account_type') ?? '';

        $result = $this->Account_model->get_paginated(50, $page, $search, $account_type);

        // Calculate balances for each account
        foreach ($result->data as $account) {
            $account->balance = $this->Account_model->get_balance($account->account_code);
        }

        $data = [
            'page_title' => 'Chart of Accounts',
            'accounts' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'account_type' => $account_type,
            'account_types' => $this->Account_model->get_account_types(),
            'main_content' => 'accounts/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new account
     */
    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('account_code', 'Account Code', 'required|trim');
            $this->form_validation->set_rules('account_name', 'Account Name', 'required|trim');
            $this->form_validation->set_rules('account_type', 'Account Type', 'required');

            if ($this->form_validation->run() === TRUE) {
                $account_code = $this->input->post('account_code');

                // Check if code already exists
                if ($this->Account_model->code_exists($account_code)) {
                    $this->session->set_flashdata('error', 'Account code already exists!');
                } else {
                    $account_data = [
                        'account_code' => $account_code,
                        'account_name' => $this->input->post('account_name'),
                        'account_type' => $this->input->post('account_type'),
                        'description' => $this->input->post('description'),
                        'is_system' => 0
                    ];

                    $account_id = $this->Account_model->insert($account_data);

                    if ($account_id) {
                        $this->session->set_flashdata('success', 'Account added successfully!');
                        redirect('accounts/view/' . $account_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to add account.');
                    }
                }
            }
        }

        $data = [
            'page_title' => 'Add Account',
            'account' => null,
            'account_types' => $this->Account_model->get_account_types(),
            'main_content' => 'accounts/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Edit account
     */
    public function edit($account_id) {
        $account = $this->Account_model->get_by_id($account_id);

        if (!$account) {
            show_404();
        }

        // Prevent editing system accounts
        if ($account->is_system ?? false) {
            $this->session->set_flashdata('error', 'Cannot edit system accounts.');
            redirect('accounts');
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('account_code', 'Account Code', 'required|trim');
            $this->form_validation->set_rules('account_name', 'Account Name', 'required|trim');
            $this->form_validation->set_rules('account_type', 'Account Type', 'required');

            if ($this->form_validation->run() === TRUE) {
                $account_code = $this->input->post('account_code');

                // Check if code already exists (excluding current account)
                if ($this->Account_model->code_exists($account_code, $account_id)) {
                    $this->session->set_flashdata('error', 'Account code already exists!');
                } else {
                    $account_data = [
                        'account_code' => $account_code,
                        'account_name' => $this->input->post('account_name'),
                        'account_type' => $this->input->post('account_type'),
                        'description' => $this->input->post('description')
                    ];

                    $updated = $this->Account_model->update($account_id, $account_data);

                    if ($updated) {
                        $this->session->set_flashdata('success', 'Account updated successfully!');
                        redirect('accounts/view/' . $account_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to update account.');
                    }
                }
            }
        }

        $data = [
            'page_title' => 'Edit Account',
            'account' => $account,
            'account_types' => $this->Account_model->get_account_types(),
            'main_content' => 'accounts/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View account details and ledger
     */
    public function view($account_id) {
        $account = $this->Account_model->get_by_id($account_id);

        if (!$account) {
            show_404();
        }

        // Get account balance
        $account->balance = $this->Account_model->get_balance($account->account_code);

        // Get recent ledger entries (last 25)
        $ledger_entries = $this->Account_model->get_ledger($account->account_code);
        $recent_entries = array_slice($ledger_entries, -25);

        $data = [
            'page_title' => 'Account Details',
            'account' => $account,
            'ledger_entries' => $recent_entries,
            'total_entries' => count($ledger_entries),
            'main_content' => 'accounts/view'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View full account ledger
     */
    public function ledger($account_id) {
        $account = $this->Account_model->get_by_id($account_id);

        if (!$account) {
            show_404();
        }

        $from_date = $this->input->get('from_date');
        $to_date = $this->input->get('to_date');

        // Get ledger entries
        $ledger_entries = $this->Account_model->get_ledger($account->account_code, $from_date, $to_date);

        // Get opening balance (balance before from_date)
        $opening_balance = 0;
        if ($from_date) {
            $this->db->select_sum('debit');
            $this->db->select_sum('credit');
            $this->db->where('account_code', $account->account_code);
            $this->db->where('date <', $from_date);
            $result = $this->db->get('daybook')->row();

            $opening_balance = ($result->debit ?? 0) - ($result->credit ?? 0);
        }

        // Recalculate running balance with opening balance
        $running_balance = $opening_balance;
        foreach ($ledger_entries as $entry) {
            $running_balance += ($entry->debit - $entry->credit);
            $entry->running_balance = $running_balance;
        }

        $data = [
            'page_title' => 'Account Ledger',
            'account' => $account,
            'ledger_entries' => $ledger_entries,
            'opening_balance' => $opening_balance,
            'filters' => [
                'from_date' => $from_date,
                'to_date' => $to_date
            ],
            'main_content' => 'accounts/ledger'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete account
     */
    public function delete($account_id) {
        $account = $this->Account_model->get_by_id($account_id);

        if (!$account) {
            show_404();
        }

        // Prevent deleting system accounts
        if ($account->is_system ?? false) {
            $this->session->set_flashdata('error', 'Cannot delete system accounts.');
            redirect('accounts');
        }

        // Check if account has any transactions
        $this->db->where('account_code', $account->account_code);
        $transaction_count = $this->db->count_all_results('daybook');

        if ($transaction_count > 0) {
            $this->session->set_flashdata('error', 'Cannot delete account with existing transactions.');
        } else {
            $deleted = $this->Account_model->delete($account_id);

            if ($deleted) {
                $this->session->set_flashdata('success', 'Account deleted successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete account.');
            }
        }

        redirect('accounts');
    }

    /**
     * Trial Balance Report
     */
    public function trial_balance() {
        $as_of_date = $this->input->get('as_of_date') ?? date('Y-m-d');

        $accounts = $this->Account_model->get_trial_balance($as_of_date);

        // Calculate totals
        $total_debit = 0;
        $total_credit = 0;

        foreach ($accounts as $account) {
            $total_debit += $account->debit_balance;
            $total_credit += $account->credit_balance;
        }

        $data = [
            'page_title' => 'Trial Balance',
            'accounts' => $accounts,
            'as_of_date' => $as_of_date,
            'total_debit' => $total_debit,
            'total_credit' => $total_credit,
            'main_content' => 'accounts/trial_balance'
        ];

        $this->load->view('templates/modern_layout', $data);
    }
}
