<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotations extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Quotation_model');
        $this->load->model('Customer_model');
        $this->load->model('Product_model');
    }

    /**
     * List all quotations
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $result = $this->Quotation_model->get_paginated(25, $page, $search);

        $data = [
            'page_title' => 'Quotations',
            'quotations' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'main_content' => 'quotations/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new quotation
     */
    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('customer_id', 'Customer', 'required');
            $this->form_validation->set_rules('quotation_date', 'Quotation Date', 'required');
            $this->form_validation->set_rules('items', 'Items', 'required');

            if ($this->form_validation->run() === TRUE) {
                $items_json = $this->input->post('items');
                $items = json_decode($items_json, true);

                if (empty($items)) {
                    $this->session->set_flashdata('error', 'Please add at least one item.');
                } else {
                    $subtotal = 0;
                    foreach ($items as &$item) {
                        $item['total_price'] = $item['quantity'] * $item['rate'];
                        $subtotal += $item['total_price'];
                    }

                    $vat = ($subtotal * 5) / 100;
                    $grand_total = $subtotal + $vat;

                    $quotation_data = [
                        'customer_id' => $this->input->post('customer_id'),
                        'quotation_date' => $this->input->post('quotation_date'),
                        'quotation_no' => $this->Quotation_model->generate_quotation_number(),
                        'subtotal' => $subtotal,
                        'vat' => $vat,
                        'grand_total' => $grand_total,
                        'status' => 'pending',
                        'notes' => $this->input->post('notes'),
                        'valid_until' => $this->input->post('valid_until')
                    ];

                    $quotation_id = $this->Quotation_model->create_quotation($quotation_data, $items);

                    if ($quotation_id) {
                        $this->session->set_flashdata('success', 'Quotation created successfully!');
                        redirect('quotations/view/' . $quotation_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to create quotation.');
                    }
                }
            }
        }

        $data = [
            'page_title' => 'Create Quotation',
            'customers' => $this->Customer_model->get_all(),
            'products' => $this->Product_model->get_all(),
            'quotation' => null,
            'main_content' => 'quotations/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View quotation details
     */
    public function view($quotation_id) {
        $quotation = $this->Quotation_model->get_quotation_details($quotation_id);

        if (!$quotation) {
            show_404();
        }

        $data = [
            'page_title' => 'Quotation Details',
            'quotation' => $quotation,
            'main_content' => 'quotations/view'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Convert quotation to invoice
     */
    public function convert_to_invoice($quotation_id) {
        $quotation = $this->Quotation_model->get_by_id($quotation_id);

        if (!$quotation) {
            show_404();
        }

        if ($quotation->status == 'converted') {
            $this->session->set_flashdata('error', 'Quotation has already been converted to invoice.');
            redirect('quotations/view/' . $quotation_id);
        }

        $invoice_id = $this->Quotation_model->convert_to_invoice($quotation_id);

        if ($invoice_id) {
            $this->session->set_flashdata('success', 'Quotation converted to invoice successfully!');
            redirect('sales/view/' . $invoice_id);
        } else {
            $this->session->set_flashdata('error', 'Failed to convert quotation.');
            redirect('quotations/view/' . $quotation_id);
        }
    }

    /**
     * Delete quotation
     */
    public function delete($quotation_id) {
        $quotation = $this->Quotation_model->get_by_id($quotation_id);

        if (!$quotation) {
            show_404();
        }

        if ($quotation->status == 'converted') {
            $this->session->set_flashdata('error', 'Cannot delete converted quotation.');
            redirect('quotations');
        }

        // Delete items
        $this->db->delete('quotation_item', ['quotation_id' => $quotation_id]);

        // Delete quotation
        $deleted = $this->Quotation_model->delete($quotation_id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Quotation deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete quotation.');
        }

        redirect('quotations');
    }

    /**
     * Print quotation
     */
    public function print_quotation($quotation_id) {
        $quotation = $this->Quotation_model->get_quotation_details($quotation_id);

        if (!$quotation) {
            show_404();
        }

        $this->load->view('quotations/print', ['quotation' => $quotation]);
    }
}
