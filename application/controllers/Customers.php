<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Customer_model');

        // Check if user is logged in
        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
    }

    /**
     * List all customers
     */
    public function index() {
        $per_page = 25;
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        // Get paginated customers
        $result = $this->Customer_model->get_paginated($per_page, $page, $search);

        $data = [
            'page_title' => 'Customers',
            'active_menu' => 'customers',
            'main_content' => 'customers/index',
            'customers' => $result->data,
            'pagination' => $result,
            'search' => $search
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new customer
     */
    public function add() {
        if ($this->input->post()) {
            // Validate form
            $this->load->library('form_validation');

            $this->form_validation->set_rules('customer_name', 'Customer Name', 'required|trim');
            $this->form_validation->set_rules('customer_mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('customer_email', 'Email', 'valid_email|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                // Check if mobile already exists
                if ($this->Customer_model->mobile_exists($this->input->post('customer_mobile'))) {
                    $this->session->set_flashdata('error', 'Mobile number already exists!');
                } else {
                    // Prepare data
                    $customer_data = [
                        'customer_name' => $this->input->post('customer_name'),
                        'customer_mobile' => $this->input->post('customer_mobile'),
                        'customer_email' => $this->input->post('customer_email'),
                        'customer_address_1' => $this->input->post('customer_address_1'),
                        'customer_address_2' => $this->input->post('customer_address_2'),
                        'city' => $this->input->post('city'),
                        'state' => $this->input->post('state'),
                        'zip' => $this->input->post('zip'),
                        'country' => $this->input->post('country'),
                        'contact' => $this->input->post('contact'),
                        'credit_limit' => $this->input->post('credit_limit') ?: 0,
                        'status' => 1
                    ];

                    // Insert customer
                    $customer_id = $this->Customer_model->insert($customer_data);

                    if ($customer_id) {
                        $this->session->set_flashdata('success', 'Customer added successfully!');
                        redirect('customers/view/' . $customer_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to add customer!');
                    }
                }
            }
        }

        $data = [
            'page_title' => 'Add Customer',
            'active_menu' => 'customers',
            'main_content' => 'customers/form',
            'form_action' => base_url('customers/add'),
            'customer' => null
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Edit customer
     */
    public function edit($customer_id) {
        $customer = $this->Customer_model->get_by_id($customer_id);

        if (!$customer) {
            $this->session->set_flashdata('error', 'Customer not found!');
            redirect('customers');
        }

        if ($this->input->post()) {
            // Validate form
            $this->load->library('form_validation');

            $this->form_validation->set_rules('customer_name', 'Customer Name', 'required|trim');
            $this->form_validation->set_rules('customer_mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('customer_email', 'Email', 'valid_email|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                // Check if mobile already exists (excluding current customer)
                if ($this->Customer_model->mobile_exists($this->input->post('customer_mobile'), $customer_id)) {
                    $this->session->set_flashdata('error', 'Mobile number already exists!');
                } else {
                    // Prepare data
                    $customer_data = [
                        'customer_name' => $this->input->post('customer_name'),
                        'customer_mobile' => $this->input->post('customer_mobile'),
                        'customer_email' => $this->input->post('customer_email'),
                        'customer_address_1' => $this->input->post('customer_address_1'),
                        'customer_address_2' => $this->input->post('customer_address_2'),
                        'city' => $this->input->post('city'),
                        'state' => $this->input->post('state'),
                        'zip' => $this->input->post('zip'),
                        'country' => $this->input->post('country'),
                        'contact' => $this->input->post('contact'),
                        'credit_limit' => $this->input->post('credit_limit') ?: 0
                    ];

                    // Update customer
                    if ($this->Customer_model->update($customer_id, $customer_data)) {
                        $this->session->set_flashdata('success', 'Customer updated successfully!');
                        redirect('customers/view/' . $customer_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to update customer!');
                    }
                }
            }
        }

        $data = [
            'page_title' => 'Edit Customer',
            'active_menu' => 'customers',
            'main_content' => 'customers/form',
            'form_action' => base_url('customers/edit/' . $customer_id),
            'customer' => $customer
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View customer details
     */
    public function view($customer_id) {
        $customer = $this->Customer_model->get_with_outstanding($customer_id);

        if (!$customer) {
            $this->session->set_flashdata('error', 'Customer not found!');
            redirect('customers');
        }

        // Get ledger
        $from_date = $this->input->get('from_date');
        $to_date = $this->input->get('to_date');
        $ledger = $this->Customer_model->get_ledger($customer_id, $from_date, $to_date);

        $data = [
            'page_title' => 'Customer Details',
            'active_menu' => 'customers',
            'main_content' => 'customers/view',
            'customer' => $customer,
            'ledger' => $ledger,
            'from_date' => $from_date,
            'to_date' => $to_date
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete customer
     */
    public function delete($customer_id) {
        // Check if customer has invoices
        $this->db->where('customer_id', $customer_id);
        $invoice_count = $this->db->count_all_results('invoice');

        if ($invoice_count > 0) {
            $this->session->set_flashdata('error', 'Cannot delete customer with existing invoices!');
        } else {
            if ($this->Customer_model->delete($customer_id)) {
                $this->session->set_flashdata('success', 'Customer deleted successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete customer!');
            }
        }

        redirect('customers');
    }

    /**
     * Export customers to CSV
     */
    public function export() {
        $search = $this->input->get('search') ?? '';
        $result = $this->Customer_model->get_paginated(10000, 1, $search);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, ['ID', 'Name', 'Mobile', 'Email', 'Address', 'City', 'Outstanding']);

        // CSV data
        foreach ($result->data as $customer) {
            fputcsv($output, [
                $customer->customer_id,
                $customer->customer_name,
                $customer->customer_mobile,
                $customer->customer_email,
                $customer->customer_address_1,
                $customer->city,
                $customer->outstanding ?? 0
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * AJAX: Get customer by ID for autocomplete
     */
    public function get_customer($customer_id) {
        $customer = $this->Customer_model->get_with_outstanding($customer_id);

        if ($customer) {
            echo json_encode(['success' => true, 'customer' => $customer]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
        }
    }

    /**
     * AJAX: Search customers for autocomplete
     */
    public function search() {
        $term = $this->input->get('term');
        $customers = $this->Customer_model->search_for_autocomplete($term);

        $results = [];
        foreach ($customers as $customer) {
            $results[] = [
                'id' => $customer->customer_id,
                'label' => $customer->customer_name . ' (' . $customer->customer_mobile . ')',
                'value' => $customer->customer_name,
                'mobile' => $customer->customer_mobile,
                'email' => $customer->customer_email
            ];
        }

        echo json_encode($results);
    }
}
