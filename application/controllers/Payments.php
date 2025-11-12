<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payments extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Payment_model');
        $this->load->model('Purchase_model');
        $this->load->model('Supplier_model');
        $this->load->model('PDC_model');
    }

    /**
     * List all payments
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;

        $filters = [
            'from_date' => $this->input->get('from_date'),
            'to_date' => $this->input->get('to_date'),
            'payment_method' => $this->input->get('payment_method')
        ];

        $result = $this->Payment_model->get_paginated(25, $page, $filters);

        $data = [
            'page_title' => 'Payments',
            'payments' => $result->data,
            'pagination' => $result,
            'filters' => $filters,
            'main_content' => 'payments/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new payment
     */
    public function add() {
        $purchase_id = $this->input->get('purchase_id');
        $selected_purchase = null;

        if ($purchase_id) {
            $selected_purchase = $this->Purchase_model->get_purchase_details($purchase_id);
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('purchase_id', 'Purchase', 'required');
            $this->form_validation->set_rules('payment_date', 'Payment Date', 'required');
            $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');

            if ($this->form_validation->run() === TRUE) {
                $payment_data = [
                    'purchase_id' => $this->input->post('purchase_id'),
                    'payment_date' => $this->input->post('payment_date'),
                    'amount' => $this->input->post('amount'),
                    'payment_method' => $this->input->post('payment_method'),
                    'bank' => $this->input->post('bank'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cheque_date' => $this->input->post('cheque_date'),
                    'discount' => $this->input->post('discount') ?? 0,
                    'details' => $this->input->post('details'),
                    'payment_voucher_no' => 'PAY-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT)
                ];

                $payment_id = $this->Payment_model->create_payment($payment_data);

                if ($payment_id) {
                    // Check if it was a PDC
                    $is_pdc = ($payment_data['payment_method'] == 'cheque' &&
                               !empty($payment_data['cheque_date']) &&
                               $payment_data['cheque_date'] > date('Y-m-d'));

                    if ($is_pdc) {
                        $this->session->set_flashdata('success', 'Payment recorded successfully as Post-Dated Cheque! Supplier account updated.');
                    } else {
                        $this->session->set_flashdata('success', 'Payment recorded successfully!');
                    }

                    redirect('payments/view/' . $payment_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to record payment. Please try again.');
                }
            }
        }

        // Get unpaid/partial purchases
        $this->db->select('p.*, s.supplier_name');
        $this->db->from('product_purchase p');
        $this->db->join('supplier_information s', 'p.supplier_id = s.supplier_id', 'left');
        $this->db->where_in('p.payment_status', ['unpaid', 'partial']);
        $this->db->order_by('p.purchase_date', 'DESC');
        $purchases = $this->db->get()->result();

        $data = [
            'page_title' => 'Add Payment',
            'purchases' => $purchases,
            'selected_purchase' => $selected_purchase,
            'main_content' => 'payments/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View payment details
     */
    public function view($payment_id) {
        $payment = $this->Payment_model->get_by_id($payment_id);

        if (!$payment) {
            show_404();
        }

        // Get purchase details
        $purchase = $this->Purchase_model->get_purchase_details($payment->purchase_id);

        // Get supplier details
        if ($purchase) {
            $supplier = $this->Supplier_model->get_by_id($purchase->supplier_id);
        }

        // Check if this is a PDC
        $pdc = null;
        if ($payment->payment_method == 'cheque' && !empty($payment->cheque_date)) {
            $this->db->where('docno LIKE', '%' . $payment->payment_id . '%');
            $this->db->where('rp', 'P');
            $pdc = $this->db->get('pdclist')->row();
        }

        $data = [
            'page_title' => 'Payment Details',
            'payment' => $payment,
            'purchase' => $purchase,
            'supplier' => $supplier ?? null,
            'pdc' => $pdc,
            'main_content' => 'payments/view'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete payment
     */
    public function delete($payment_id) {
        $payment = $this->Payment_model->get_by_id($payment_id);

        if (!$payment) {
            show_404();
        }

        // Reverse accounting entries
        $this->load->model('Daybook_model');
        $this->Daybook_model->reverse_entries('payment', $payment_id);

        // Delete payment
        $deleted = $this->Payment_model->delete($payment_id);

        if ($deleted) {
            // Update purchase payment status
            $this->Purchase_model->update_payment_status($payment->purchase_id);
            $this->session->set_flashdata('success', 'Payment deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete payment. Please try again.');
        }

        redirect('payments');
    }

    /**
     * Export payments to CSV
     */
    public function export() {
        $payments = $this->Payment_model->get_all();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="payments_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Date', 'Purchase', 'Supplier', 'Amount', 'Method', 'Cheque No', 'Cheque Date', 'Discount']);

        foreach ($payments as $payment) {
            fputcsv($output, [
                $payment->payment_id,
                $payment->payment_date,
                $payment->purchase_reference ?? 'N/A',
                $payment->supplier_name ?? 'N/A',
                $payment->amount,
                ucfirst($payment->payment_method),
                $payment->cheque_no ?? '-',
                $payment->cheque_date ?? '-',
                $payment->discount ?? 0
            ]);
        }

        fclose($output);
    }

    /**
     * Get purchase details (AJAX)
     */
    public function get_purchase($purchase_id) {
        $purchase = $this->Purchase_model->get_purchase_details($purchase_id);

        if ($purchase) {
            // Calculate outstanding
            $this->db->select('SUM(amount) as total_paid');
            $this->db->where('purchase_id', $purchase_id);
            $result = $this->db->get('payment')->row();
            $total_paid = $result->total_paid ?? 0;
            $outstanding = $purchase->grand_total_amount - $total_paid;

            echo json_encode([
                'success' => true,
                'purchase' => $purchase,
                'outstanding' => $outstanding
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Purchase not found'
            ]);
        }
    }

    /**
     * PDC Management - List pending PDCs
     */
    public function pdc_list() {
        $this->load->model('PDC_model');

        $pending_pdcs = $this->PDC_model->get_pending('P'); // P = Payment
        $due_clearance = $this->PDC_model->get_due_for_clearance();

        // Filter only payment PDCs
        $due_clearance = array_filter($due_clearance, function($pdc) {
            return $pdc->rp == 'P';
        });

        $data = [
            'page_title' => 'Post-Dated Cheques (Payments)',
            'pending_pdcs' => $pending_pdcs,
            'due_clearance' => $due_clearance,
            'main_content' => 'payments/pdc_list'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Clear PDC
     */
    public function clear_pdc($pdc_id) {
        $this->load->model('PDC_model');

        $clearance_date = $this->input->post('clearance_date') ?? date('Y-m-d');
        $cleared = $this->PDC_model->clear_pdc($pdc_id, $clearance_date);

        if ($cleared) {
            $this->session->set_flashdata('success', 'PDC cleared successfully! Bank account debited.');
        } else {
            $this->session->set_flashdata('error', 'Failed to clear PDC. Please try again.');
        }

        redirect('payments/pdc_list');
    }
}
