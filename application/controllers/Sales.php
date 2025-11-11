<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Invoice_model');
        $this->load->model('Customer_model');
        $this->load->model('Product_model');

        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
    }

    /**
     * List all invoices
     */
    public function index() {
        $per_page = 25;
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';
        $payment_status = $this->input->get('payment_status') ?? '';
        $from_date = $this->input->get('from_date') ?? '';
        $to_date = $this->input->get('to_date') ?? '';

        $filters = [
            'payment_status' => $payment_status,
            'from_date' => $from_date,
            'to_date' => $to_date
        ];

        $result = $this->Invoice_model->get_paginated($per_page, $page, $search, $filters);

        $data = [
            'page_title' => 'Sales Invoices',
            'active_menu' => 'sales',
            'main_content' => 'sales/index',
            'invoices' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'payment_status' => $payment_status,
            'from_date' => $from_date,
            'to_date' => $to_date
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Create new invoice
     */
    public function add() {
        if ($this->input->post()) {
            // Validate form
            $this->load->library('form_validation');

            $this->form_validation->set_rules('customer_id', 'Customer', 'required|numeric');
            $this->form_validation->set_rules('date', 'Invoice Date', 'required');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                // Get form data
                $customer_id = $this->input->post('customer_id');
                $date = $this->input->post('date');
                $items = $this->input->post('items');

                // Validate items
                if (empty($items) || !is_array($items)) {
                    $this->session->set_flashdata('error', 'Please add at least one item to the invoice!');
                    redirect('sales/add');
                }

                // Calculate totals
                $subtotal = 0;
                $invoice_items = [];

                foreach ($items as $item) {
                    if (empty($item['product_id']) || empty($item['quantity']) || empty($item['rate'])) {
                        continue;
                    }

                    $quantity = floatval($item['quantity']);
                    $rate = floatval($item['rate']);
                    $total_price = $quantity * $rate;

                    $invoice_items[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                        'rate' => $rate,
                        'total_price' => $total_price,
                        'description' => $item['description'] ?? ''
                    ];

                    $subtotal += $total_price;
                }

                if (empty($invoice_items)) {
                    $this->session->set_flashdata('error', 'Please add valid items to the invoice!');
                    redirect('sales/add');
                }

                // Calculate VAT (5% for UAE)
                $vat_rate = floatval($this->input->post('vat_rate')) ?: 5;
                $vat = ($subtotal * $vat_rate) / 100;
                $grand_total = $subtotal + $vat;

                // Prepare invoice data
                $invoice_data = [
                    'customer_id' => $customer_id,
                    'date' => $date,
                    'invoice' => $this->Invoice_model->generate_invoice_number(),
                    'total' => $subtotal,
                    'vat' => $vat,
                    'grand_total' => $grand_total,
                    'payment_status' => 'unpaid',
                    'sales_by' => $this->session->userdata('user_id'),
                    'sales_date' => date('Y-m-d H:i:s')
                ];

                // Create invoice (will automatically post to daybook)
                $invoice_id = $this->Invoice_model->create_invoice($invoice_data, $invoice_items);

                if ($invoice_id) {
                    $this->session->set_flashdata('success', 'Invoice created successfully! Invoice #' . $invoice_data['invoice']);
                    redirect('sales/view/' . $invoice_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to create invoice!');
                }
            }
        }

        // Get customers for dropdown
        $customers = $this->Customer_model->get_all();

        $data = [
            'page_title' => 'Create New Invoice',
            'active_menu' => 'sales',
            'main_content' => 'sales/form',
            'form_action' => base_url('sales/add'),
            'invoice' => null,
            'customers' => $customers
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Edit invoice
     */
    public function edit($invoice_id) {
        $invoice = $this->Invoice_model->get_invoice_details($invoice_id);

        if (!$invoice) {
            $this->session->set_flashdata('error', 'Invoice not found!');
            redirect('sales');
        }

        // Don't allow editing paid invoices
        if ($invoice->payment_status == 'paid') {
            $this->session->set_flashdata('error', 'Cannot edit paid invoice!');
            redirect('sales/view/' . $invoice_id);
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('customer_id', 'Customer', 'required|numeric');
            $this->form_validation->set_rules('date', 'Invoice Date', 'required');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $customer_id = $this->input->post('customer_id');
                $date = $this->input->post('date');
                $items = $this->input->post('items');

                if (empty($items) || !is_array($items)) {
                    $this->session->set_flashdata('error', 'Please add at least one item!');
                    redirect('sales/edit/' . $invoice_id);
                }

                $this->db->trans_start();

                // Reverse old accounting entries
                $this->load->model('Daybook_model');
                $this->Daybook_model->reverse_entries('invoice', $invoice_id);

                // Delete old items
                $this->db->delete('invoice_item', ['invoice_id' => $invoice_id]);

                // Calculate new totals
                $subtotal = 0;
                $invoice_items = [];

                foreach ($items as $item) {
                    if (empty($item['product_id']) || empty($item['quantity']) || empty($item['rate'])) {
                        continue;
                    }

                    $quantity = floatval($item['quantity']);
                    $rate = floatval($item['rate']);
                    $total_price = $quantity * $rate;

                    $invoice_items[] = [
                        'invoice_id' => $invoice_id,
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                        'rate' => $rate,
                        'total_price' => $total_price,
                        'description' => $item['description'] ?? ''
                    ];

                    $subtotal += $total_price;
                }

                $vat_rate = floatval($this->input->post('vat_rate')) ?: 5;
                $vat = ($subtotal * $vat_rate) / 100;
                $grand_total = $subtotal + $vat;

                // Update invoice
                $invoice_data = [
                    'customer_id' => $customer_id,
                    'date' => $date,
                    'total' => $subtotal,
                    'vat' => $vat,
                    'grand_total' => $grand_total
                ];

                $this->Invoice_model->update($invoice_id, $invoice_data);

                // Insert new items
                foreach ($invoice_items as $item) {
                    $this->db->insert('invoice_item', $item);
                }

                // Post new accounting entries
                $this->Daybook_model->post_entry([
                    'date' => $date,
                    'account_code' => 'CUST_' . $customer_id,
                    'description' => 'Sales Invoice #' . $invoice->invoice,
                    'debit' => $grand_total,
                    'credit' => 0,
                    'reference_type' => 'invoice',
                    'reference_id' => $invoice_id
                ]);

                $sales_amount = $grand_total - $vat;
                $this->Daybook_model->post_entry([
                    'date' => $date,
                    'account_code' => 'SALES',
                    'description' => 'Sales Invoice #' . $invoice->invoice,
                    'debit' => 0,
                    'credit' => $sales_amount,
                    'reference_type' => 'invoice',
                    'reference_id' => $invoice_id
                ]);

                if ($vat > 0) {
                    $this->Daybook_model->post_entry([
                        'date' => $date,
                        'account_code' => 'VATPAY',
                        'description' => 'VAT on Invoice #' . $invoice->invoice,
                        'debit' => 0,
                        'credit' => $vat,
                        'reference_type' => 'invoice',
                        'reference_id' => $invoice_id
                    ]);
                }

                $this->db->trans_complete();

                if ($this->db->trans_status()) {
                    $this->session->set_flashdata('success', 'Invoice updated successfully!');
                    redirect('sales/view/' . $invoice_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to update invoice!');
                }
            }
        }

        $customers = $this->Customer_model->get_all();

        $data = [
            'page_title' => 'Edit Invoice',
            'active_menu' => 'sales',
            'main_content' => 'sales/form',
            'form_action' => base_url('sales/edit/' . $invoice_id),
            'invoice' => $invoice,
            'customers' => $customers
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View invoice details
     */
    public function view($invoice_id) {
        $invoice = $this->Invoice_model->get_invoice_details($invoice_id);

        if (!$invoice) {
            $this->session->set_flashdata('error', 'Invoice not found!');
            redirect('sales');
        }

        // Get accounting entries
        $this->load->model('Daybook_model');
        $accounting_entries = $this->Daybook_model->get_by_reference('invoice', $invoice_id);

        $data = [
            'page_title' => 'Invoice Details',
            'active_menu' => 'sales',
            'main_content' => 'sales/view',
            'invoice' => $invoice,
            'accounting_entries' => $accounting_entries
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete invoice
     */
    public function delete($invoice_id) {
        $invoice = $this->Invoice_model->get_by_id($invoice_id);

        if (!$invoice) {
            $this->session->set_flashdata('error', 'Invoice not found!');
            redirect('sales');
        }

        // Don't allow deleting paid invoices
        if ($invoice->payment_status == 'paid') {
            $this->session->set_flashdata('error', 'Cannot delete paid invoice!');
            redirect('sales');
        }

        $this->db->trans_start();

        // Reverse accounting entries
        $this->load->model('Daybook_model');
        $this->Daybook_model->reverse_entries('invoice', $invoice_id);

        // Delete items
        $this->db->delete('invoice_item', ['invoice_id' => $invoice_id]);

        // Delete invoice
        $this->Invoice_model->delete($invoice_id);

        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            $this->session->set_flashdata('success', 'Invoice deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete invoice!');
        }

        redirect('sales');
    }

    /**
     * Print/Download invoice PDF
     */
    public function print_invoice($invoice_id) {
        $invoice = $this->Invoice_model->get_invoice_details($invoice_id);

        if (!$invoice) {
            show_404();
        }

        // Load print view (simplified version without layout)
        $this->load->view('sales/print', ['invoice' => $invoice]);
    }

    /**
     * Get product info (AJAX)
     */
    public function get_product($product_id) {
        $product = $this->Product_model->get_by_id($product_id);

        if ($product) {
            echo json_encode([
                'success' => true,
                'product' => $product
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
        }
    }
}
