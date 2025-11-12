<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PDC (Post-Dated Cheque) Model
 *
 * Handles post-dated cheques from customers and to suppliers
 * Based on legacy pdclist table structure
 */
class PDC_model extends MY_Model {

    protected $table = 'pdclist';
    protected $primary_key = 'slno';
    protected $timestamps = false;

    /**
     * Get paginated PDC list with filters
     */
    public function get_paginated($per_page = 25, $page = 1, $search = '', $filters = []) {
        $offset = ($page - 1) * $per_page;

        $this->db->select('p.*, c.customer_name, s.supplier_name');
        $this->db->from($this->table . ' p');
        $this->db->join('customer_information c', 'p.code = c.customer_id', 'left');
        $this->db->join('supplier_information s', 'p.code = s.supplier_id', 'left');

        // Search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p.chqno', $search);
            $this->db->or_like('p.bank', $search);
            $this->db->or_like('c.customer_name', $search);
            $this->db->or_like('s.supplier_name', $search);
            $this->db->group_end();
        }

        // Filter by status (pending/cleared)
        if (isset($filters['status'])) {
            if ($filters['status'] == 'pending') {
                $this->db->where('p.pend', 1);
            } elseif ($filters['status'] == 'cleared') {
                $this->db->where('p.pend', 0);
            }
        }

        // Filter by type (receipt/payment)
        if (isset($filters['type'])) {
            $this->db->where('p.rp', $filters['type']);
        }

        // Filter by date range
        if (!empty($filters['from_date'])) {
            $this->db->where('p.chqdate >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('p.chqdate <=', $filters['to_date']);
        }

        $total = $this->db->count_all_results('', false);
        $this->db->limit($per_page, $offset);
        $this->db->order_by('p.chqdate', 'ASC');
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
     * Create PDC entry
     *
     * @param array $data PDC data
     * @return int|false PDC ID or false on failure
     */
    public function create_pdc($data) {
        $pdc_data = [
            'tdate' => $data['transaction_date'],
            'docno' => $data['document_no'],
            'bank' => $data['bank'],
            'code' => $data['party_code'], // customer_id or supplier_id
            'chqno' => $data['cheque_no'],
            'chqdate' => $data['cheque_date'],
            'amount' => $data['amount'],
            'particulars' => $data['particulars'] ?? '',
            'rp' => $data['type'], // 'R' for Receipt, 'P' for Payment
            'pend' => 1, // Initially pending
            'control' => $data['control'] ?? date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table, $pdc_data);
        return $this->db->insert_id();
    }

    /**
     * Get pending PDCs
     *
     * @param string $type 'R' for Receipt, 'P' for Payment, null for all
     * @return array
     */
    public function get_pending($type = null) {
        $this->db->select('p.*, c.customer_name, s.supplier_name');
        $this->db->from($this->table . ' p');
        $this->db->join('customer_information c', 'p.code = c.customer_id AND p.rp = "R"', 'left');
        $this->db->join('supplier_information s', 'p.code = s.supplier_id AND p.rp = "P"', 'left');
        $this->db->where('p.pend', 1);

        if ($type) {
            $this->db->where('p.rp', $type);
        }

        $this->db->order_by('p.chqdate', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get PDCs due for clearance (cheque date has passed)
     *
     * @param string $as_of_date Date to check against
     * @return array
     */
    public function get_due_for_clearance($as_of_date = null) {
        if (!$as_of_date) {
            $as_of_date = date('Y-m-d');
        }

        $this->db->select('p.*, c.customer_name, s.supplier_name');
        $this->db->from($this->table . ' p');
        $this->db->join('customer_information c', 'p.code = c.customer_id AND p.rp = "R"', 'left');
        $this->db->join('supplier_information s', 'p.code = s.supplier_id AND p.rp = "P"', 'left');
        $this->db->where('p.pend', 1);
        $this->db->where('p.chqdate <=', $as_of_date);
        $this->db->order_by('p.chqdate', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Clear PDC (mark as cleared and post to accounts)
     *
     * @param int $pdc_id PDC ID
     * @param string $clearance_date Actual clearance date
     * @return bool
     */
    public function clear_pdc($pdc_id, $clearance_date = null) {
        if (!$clearance_date) {
            $clearance_date = date('Y-m-d');
        }

        $this->db->trans_start();

        // Get PDC details
        $pdc = $this->db->get_where($this->table, ['slno' => $pdc_id])->row();
        if (!$pdc) {
            return false;
        }

        // Mark as cleared
        $this->db->where('slno', $pdc_id);
        $this->db->update($this->table, ['pend' => 0]);

        // Post to daybook
        $this->load->model('Daybook_model');

        if ($pdc->rp == 'R') {
            // Receipt PDC - Clear to bank
            // Dr: Bank Account
            $this->Daybook_model->post_entry([
                'date' => $clearance_date,
                'account_code' => 'BANK',
                'description' => 'PDC Cleared - Cheque #' . $pdc->chqno . ' from ' . $pdc->bank,
                'debit' => $pdc->amount,
                'credit' => 0,
                'reference_type' => 'pdc_clearance',
                'reference_id' => $pdc_id
            ]);

            // Cr: PDC Receivable (or reduce Cash if it was posted there)
            $this->Daybook_model->post_entry([
                'date' => $clearance_date,
                'account_code' => 'PDCREC',
                'description' => 'PDC Cleared - Cheque #' . $pdc->chqno,
                'debit' => 0,
                'credit' => $pdc->amount,
                'reference_type' => 'pdc_clearance',
                'reference_id' => $pdc_id
            ]);
        } else {
            // Payment PDC - Clear from bank
            // Cr: Bank Account
            $this->Daybook_model->post_entry([
                'date' => $clearance_date,
                'account_code' => 'BANK',
                'description' => 'PDC Issued Cleared - Cheque #' . $pdc->chqno . ' - ' . $pdc->bank,
                'debit' => 0,
                'credit' => $pdc->amount,
                'reference_type' => 'pdc_clearance',
                'reference_id' => $pdc_id
            ]);

            // Dr: PDC Payable
            $this->Daybook_model->post_entry([
                'date' => $clearance_date,
                'account_code' => 'PDCPAY',
                'description' => 'PDC Issued Cleared - Cheque #' . $pdc->chqno,
                'debit' => $pdc->amount,
                'credit' => 0,
                'reference_type' => 'pdc_clearance',
                'reference_id' => $pdc_id
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Return/Bounce PDC
     *
     * @param int $pdc_id PDC ID
     * @param string $reason Reason for return
     * @return bool
     */
    public function return_pdc($pdc_id, $reason = '') {
        $this->db->trans_start();

        // Get PDC details
        $pdc = $this->db->get_where($this->table, ['slno' => $pdc_id])->row();
        if (!$pdc) {
            return false;
        }

        // Mark as returned (we can add a 'returned' field or delete)
        $this->db->where('slno', $pdc_id);
        $this->db->update($this->table, [
            'pend' => 0,
            'particulars' => $pdc->particulars . ' [RETURNED: ' . $reason . ']'
        ]);

        // Reverse the PDC entries if they were posted
        $this->load->model('Daybook_model');
        $this->Daybook_model->reverse_entries('pdc', $pdc_id);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Get PDCs by party (customer or supplier)
     *
     * @param int $party_code Customer or Supplier ID
     * @param string $type 'R' or 'P'
     * @return array
     */
    public function get_by_party($party_code, $type = 'R') {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('code', $party_code);
        $this->db->where('rp', $type);
        $this->db->order_by('chqdate', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get PDC summary
     *
     * @return object
     */
    public function get_summary() {
        // Pending receipts
        $this->db->select('COUNT(*) as count, SUM(amount) as total');
        $this->db->from($this->table);
        $this->db->where('rp', 'R');
        $this->db->where('pend', 1);
        $pending_receipts = $this->db->get()->row();

        // Pending payments
        $this->db->select('COUNT(*) as count, SUM(amount) as total');
        $this->db->from($this->table);
        $this->db->where('rp', 'P');
        $this->db->where('pend', 1);
        $pending_payments = $this->db->get()->row();

        // Due for clearance
        $this->db->select('COUNT(*) as count, SUM(amount) as total');
        $this->db->from($this->table);
        $this->db->where('pend', 1);
        $this->db->where('chqdate <=', date('Y-m-d'));
        $due_clearance = $this->db->get()->row();

        return (object) [
            'pending_receipts' => $pending_receipts,
            'pending_payments' => $pending_payments,
            'due_for_clearance' => $due_clearance
        ];
    }
}
