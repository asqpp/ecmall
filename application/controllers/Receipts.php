<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Receipts extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Receipt_model');
        $this->load->model('Invoice_model');
        $this->load->model('Customer_model');
        $this->load->model('PDC_model');
    }

    /**
     * List all receipts
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;

        $filters = [
            'from_date' => $this->input->get('from_date'),
            'to_date' => $this->input->get('to_date'),
            'payment_method' => $this->input->get('payment_method')
        ];

        $result = $this->Receipt_model->get_paginated(25, $page, $filters);

        $data = [
            'page_title' => 'Receipts',
            'receipts' => $result->data,
            'pagination' => $result,
            'filters' => $filters,
            'main_content' => 'receipts/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new receipt
     */
    public function add() {
        $invoice_id = $this->input->get('invoice_id');
        $selected_invoice = null;

        if ($invoice_id) {
            $selected_invoice = $this->Invoice_model->get_invoice_details($invoice_id);
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('invoice_id', 'Invoice', 'required');
            $this->form_validation->set_rules('receipt_date', 'Receipt Date', 'required');
            $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');

            if ($this->form_validation->run() === TRUE) {
                $receipt_data = [
                    'invoice_id' => $this->input->post('invoice_id'),
                    'receipt_date' => $this->input->post('receipt_date'),
                    'amount' => $this->input->post('amount'),
                    'payment_method' => $this->input->post('payment_method'),
                    'bank' => $this->input->post('bank'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cheque_date' => $this->input->post('cheque_date'),
                    'discount' => $this->input->post('discount') ?? 0,
                    'notes' => $this->input->post('notes'),
                    'receipt_voucher_no' => $this->Receipt_model->generate_receipt_number(),
                    'due_date' => $this->input->post('due_date')
                ];

                $receipt_id = $this->Receipt_model->create_receipt($receipt_data);

                if ($receipt_id) {
                    // Check if it was a PDC
                    $is_pdc = ($receipt_data['payment_method'] == 'cheque' &&
                               !empty($receipt_data['cheque_date']) &&
                               $receipt_data['cheque_date'] > date('Y-m-d'));

                    if ($is_pdc) {
                        $this->session->set_flashdata('success', 'Receipt recorded successfully as Post-Dated Cheque! Customer account updated.');
                    } else {
                        $this->session->set_flashdata('success', 'Receipt recorded successfully! Payment received.');
                    }

                    redirect('receipts/view/' . $receipt_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to record receipt. Please try again.');
                }
            }
        }

        // Get unpaid/partial invoices
        $this->db->select('i.*, c.customer_name, c.customer_mobile');
        $this->db->from('invoice i');
        $this->db->join('customer_information c', 'i.customer_id = c.customer_id', 'left');
        $this->db->where_in('i.payment_status', ['unpaid', 'partial']);
        $this->db->order_by('i.date', 'DESC');
        $invoices = $this->db->get()->result();

        $data = [
            'page_title' => 'Add Receipt',
            'invoices' => $invoices,
            'selected_invoice' => $selected_invoice,
            'main_content' => 'receipts/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View receipt details
     */
    public function view($receipt_id) {
        $receipt = $this->Receipt_model->get_by_id($receipt_id);

        if (!$receipt) {
            show_404();
        }

        // Get invoice details
        $invoice = $this->Invoice_model->get_invoice_details($receipt->invoice_id);

        // Get customer details
        if ($invoice) {
            $customer = $this->Customer_model->get_by_id($invoice->customer_id);
        }

        // Check if this is a PDC
        $pdc = null;
        if ($receipt->payment_method == 'cheque' && !empty($receipt->cheque_date)) {
            $this->db->where('docno LIKE', '%' . $receipt->receipt_id . '%');
            $this->db->where('rp', 'R');
            $pdc = $this->db->get('pdclist')->row();
        }

        $data = [
            'page_title' => 'Receipt Details',
            'receipt' => $receipt,
            'invoice' => $invoice,
            'customer' => $customer ?? null,
            'pdc' => $pdc,
            'main_content' => 'receipts/view'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete receipt
     */
    public function delete($receipt_id) {
        $receipt = $this->Receipt_model->get_by_id($receipt_id);

        if (!$receipt) {
            show_404();
        }

        // Reverse accounting entries
        $this->load->model('Daybook_model');
        $this->Daybook_model->reverse_entries('receipt', $receipt_id);

        // Delete collection entry
        $this->db->where('islno', $receipt_id);
        $this->db->delete('collection');

        // Delete receipt
        $deleted = $this->Receipt_model->delete($receipt_id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Receipt deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete receipt. Please try again.');
        }

        redirect('receipts');
    }

    /**
     * Export receipts to CSV
     */
    public function export() {
        $receipts = $this->Receipt_model->get_all();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="receipts_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Date', 'Invoice', 'Customer', 'Amount', 'Method', 'Cheque No', 'Cheque Date', 'Discount']);

        foreach ($receipts as $receipt) {
            fputcsv($output, [
                $receipt->receipt_id,
                $receipt->receipt_date,
                $receipt->invoice_number ?? 'N/A',
                $receipt->customer_name ?? 'N/A',
                $receipt->amount,
                ucfirst($receipt->payment_method),
                $receipt->cheque_no ?? '-',
                $receipt->cheque_date ?? '-',
                $receipt->discount ?? 0
            ]);
        }

        fclose($output);
    }

    /**
     * Get invoice details (AJAX)
     */
    public function get_invoice($invoice_id) {
        $invoice = $this->Invoice_model->get_invoice_details($invoice_id);

        if ($invoice) {
            // Calculate outstanding
            $this->load->model('Collection_model');
            $total_paid = $this->Collection_model->get_total_collected($invoice_id);
            $outstanding = $invoice->grand_total - $total_paid;

            echo json_encode([
                'success' => true,
                'invoice' => $invoice,
                'outstanding' => $outstanding
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invoice not found'
            ]);
        }
    }

    /**
     * PDC Management - List pending PDCs
     */
    public function pdc_list() {
        $this->load->model('PDC_model');

        $pending_pdcs = $this->PDC_model->get_pending('R'); // R = Receipt
        $due_clearance = $this->PDC_model->get_due_for_clearance();

        $data = [
            'page_title' => 'Post-Dated Cheques (Receipts)',
            'pending_pdcs' => $pending_pdcs,
            'due_clearance' => $due_clearance,
            'main_content' => 'receipts/pdc_list'
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
            $this->session->set_flashdata('success', 'PDC cleared successfully! Bank account updated.');
        } else {
            $this->session->set_flashdata('error', 'Failed to clear PDC. Please try again.');
        }

        redirect('receipts/pdc_list');
    }
}
