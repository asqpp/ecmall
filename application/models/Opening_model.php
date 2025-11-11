<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Opening_model extends MY_Model {

    protected $table = 'opening_balance';
    protected $primary_key = 'opening_id';
    protected $timestamps = false;

    /**
     * Get opening balances for a fiscal year
     */
    public function get_opening_balances($fiscal_year) {
        $this->db->select('ob.*, a.account_name, a.account_type');
        $this->db->from($this->table . ' ob');
        $this->db->join('accounts a', 'ob.account_code = a.account_code', 'left');
        $this->db->where('ob.fiscal_year', $fiscal_year);
        $this->db->order_by('a.account_code', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Post opening balances for a fiscal year
     *
     * This posts all opening balances to daybook
     * Must maintain: Total Debits = Total Credits
     */
    public function post_opening_balances($fiscal_year, $balances) {
        $this->db->trans_start();

        // Validate balances first
        $total_debit = 0;
        $total_credit = 0;

        foreach ($balances as $balance) {
            $total_debit += ($balance['debit'] ?? 0);
            $total_credit += ($balance['credit'] ?? 0);
        }

        // Check if balanced
        if (abs($total_debit - $total_credit) > 0.01) {
            $this->db->trans_rollback();
            return [
                'success' => false,
                'message' => 'Opening balances not balanced! Debits: ' . $total_debit . ', Credits: ' . $total_credit
            ];
        }

        // Delete existing opening balances for this year
        $this->db->delete($this->table, ['fiscal_year' => $fiscal_year]);

        // Also reverse any existing opening entries in daybook
        $this->load->model('Daybook_model');
        $this->Daybook_model->reverse_entries('opening', $fiscal_year);

        $opening_date = $fiscal_year . '-01-01'; // First day of fiscal year

        // Insert new opening balances
        foreach ($balances as $balance) {
            if (empty($balance['account_code'])) {
                continue;
            }

            $debit = floatval($balance['debit'] ?? 0);
            $credit = floatval($balance['credit'] ?? 0);

            // Skip if both are zero
            if ($debit == 0 && $credit == 0) {
                continue;
            }

            // Save to opening_balance table
            $opening_data = [
                'fiscal_year' => $fiscal_year,
                'account_code' => $balance['account_code'],
                'debit' => $debit,
                'credit' => $credit,
                'created_by' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert($this->table, $opening_data);

            // Post to daybook
            $this->Daybook_model->post_entry([
                'date' => $opening_date,
                'account_code' => $balance['account_code'],
                'description' => 'Opening Balance ' . $fiscal_year,
                'debit' => $debit,
                'credit' => $credit,
                'reference_type' => 'opening',
                'reference_id' => $fiscal_year
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            return [
                'success' => true,
                'message' => 'Opening balances posted successfully!',
                'total_debit' => $total_debit,
                'total_credit' => $total_credit
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to post opening balances'
            ];
        }
    }

    /**
     * Get all accounts with opening balance form
     */
    public function get_accounts_for_opening($fiscal_year) {
        // Get all accounts
        $this->db->select('a.account_code, a.account_name, a.account_type,
            COALESCE(ob.debit, 0) as opening_debit,
            COALESCE(ob.credit, 0) as opening_credit'
        );
        $this->db->from('accounts a');
        $this->db->join(
            $this->table . ' ob',
            'a.account_code = ob.account_code AND ob.fiscal_year = ' . $this->db->escape($fiscal_year),
            'left'
        );
        $this->db->order_by('a.account_type', 'ASC');
        $this->db->order_by('a.account_code', 'ASC');

        $accounts = $this->db->get()->result();

        // Group by account type
        $grouped = [];
        foreach ($accounts as $account) {
            $type = $account->account_type;
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $account;
        }

        return $grouped;
    }

    /**
     * Copy opening balances from previous year
     */
    public function copy_from_previous_year($from_year, $to_year) {
        // Get closing balances from previous year as opening for new year
        $this->load->model('Account_model');

        $last_date = $from_year . '-12-31';
        $accounts = $this->Account_model->get_trial_balance($last_date);

        $balances = [];
        foreach ($accounts as $account) {
            if ($account->debit_balance > 0 || $account->credit_balance > 0) {
                $balances[] = [
                    'account_code' => $account->account_code,
                    'debit' => $account->debit_balance,
                    'credit' => $account->credit_balance
                ];
            }
        }

        return $this->post_opening_balances($to_year, $balances);
    }

    /**
     * Validate opening balances (Debits = Credits)
     */
    public function validate_opening_balances($balances) {
        $total_debit = 0;
        $total_credit = 0;

        foreach ($balances as $balance) {
            $total_debit += floatval($balance['debit'] ?? 0);
            $total_credit += floatval($balance['credit'] ?? 0);
        }

        $difference = abs($total_debit - $total_credit);

        return [
            'balanced' => ($difference < 0.01),
            'total_debit' => $total_debit,
            'total_credit' => $total_credit,
            'difference' => $difference
        ];
    }

    /**
     * Get fiscal years list
     */
    public function get_fiscal_years() {
        $this->db->select('DISTINCT fiscal_year');
        $this->db->from($this->table);
        $this->db->order_by('fiscal_year', 'DESC');

        $result = $this->db->get()->result();

        $years = [];
        foreach ($result as $row) {
            $years[] = $row->fiscal_year;
        }

        // Add current year if not in list
        $current_year = date('Y');
        if (!in_array($current_year, $years)) {
            array_unshift($years, $current_year);
        }

        return $years;
    }
}
