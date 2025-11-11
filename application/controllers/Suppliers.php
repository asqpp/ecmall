<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Supplier_model');

        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
    }

    public function index() {
        $per_page = 25;
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $result = $this->Supplier_model->get_paginated($per_page, $page, $search);

        $data = [
            'page_title' => 'Suppliers',
            'active_menu' => 'suppliers',
            'main_content' => 'suppliers/index',
            'suppliers' => $result->data,
            'pagination' => $result,
            'search' => $search
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('supplier_name', 'Supplier Name', 'required|trim');
            $this->form_validation->set_rules('supplier_mobile', 'Mobile', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                if ($this->Supplier_model->mobile_exists($this->input->post('supplier_mobile'))) {
                    $this->session->set_flashdata('error', 'Mobile number already exists!');
                } else {
                    $supplier_data = [
                        'supplier_name' => $this->input->post('supplier_name'),
                        'supplier_mobile' => $this->input->post('supplier_mobile'),
                        'emailnumber' => $this->input->post('emailnumber'),
                        'contact' => $this->input->post('contact'),
                        'address' => $this->input->post('address'),
                        'city' => $this->input->post('city'),
                        'state' => $this->input->post('state'),
                        'zip' => $this->input->post('zip'),
                        'country' => $this->input->post('country'),
                        'details' => $this->input->post('details'),
                        'status' => 1
                    ];

                    $supplier_id = $this->Supplier_model->insert($supplier_data);

                    if ($supplier_id) {
                        $this->session->set_flashdata('success', 'Supplier added successfully!');
                        redirect('suppliers/view/' . $supplier_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to add supplier!');
                    }
                }
            }
        }

        $data = [
            'page_title' => 'Add Supplier',
            'active_menu' => 'suppliers',
            'main_content' => 'suppliers/form',
            'form_action' => base_url('suppliers/add'),
            'supplier' => null
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    public function edit($supplier_id) {
        $supplier = $this->Supplier_model->get_by_id($supplier_id);

        if (!$supplier) {
            $this->session->set_flashdata('error', 'Supplier not found!');
            redirect('suppliers');
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('supplier_name', 'Supplier Name', 'required|trim');
            $this->form_validation->set_rules('supplier_mobile', 'Mobile', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                if ($this->Supplier_model->mobile_exists($this->input->post('supplier_mobile'), $supplier_id)) {
                    $this->session->set_flashdata('error', 'Mobile number already exists!');
                } else {
                    $supplier_data = [
                        'supplier_name' => $this->input->post('supplier_name'),
                        'supplier_mobile' => $this->input->post('supplier_mobile'),
                        'emailnumber' => $this->input->post('emailnumber'),
                        'contact' => $this->input->post('contact'),
                        'address' => $this->input->post('address'),
                        'city' => $this->input->post('city'),
                        'state' => $this->input->post('state'),
                        'zip' => $this->input->post('zip'),
                        'country' => $this->input->post('country'),
                        'details' => $this->input->post('details')
                    ];

                    if ($this->Supplier_model->update($supplier_id, $supplier_data)) {
                        $this->session->set_flashdata('success', 'Supplier updated successfully!');
                        redirect('suppliers/view/' . $supplier_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to update supplier!');
                    }
                }
            }
        }

        $data = [
            'page_title' => 'Edit Supplier',
            'active_menu' => 'suppliers',
            'main_content' => 'suppliers/form',
            'form_action' => base_url('suppliers/edit/' . $supplier_id),
            'supplier' => $supplier
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    public function view($supplier_id) {
        $supplier = $this->Supplier_model->get_by_id($supplier_id);

        if (!$supplier) {
            $this->session->set_flashdata('error', 'Supplier not found!');
            redirect('suppliers');
        }

        // Get ledger
        $from_date = $this->input->get('from_date');
        $to_date = $this->input->get('to_date');
        $ledger = $this->Supplier_model->get_ledger($supplier_id, $from_date, $to_date);

        $data = [
            'page_title' => 'Supplier Details',
            'active_menu' => 'suppliers',
            'main_content' => 'suppliers/view',
            'supplier' => $supplier,
            'ledger' => $ledger,
            'from_date' => $from_date,
            'to_date' => $to_date
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    public function delete($supplier_id) {
        $this->db->where('supplier_id', $supplier_id);
        $purchase_count = $this->db->count_all_results('product_purchase');

        if ($purchase_count > 0) {
            $this->session->set_flashdata('error', 'Cannot delete supplier with existing purchases!');
        } else {
            if ($this->Supplier_model->delete($supplier_id)) {
                $this->session->set_flashdata('success', 'Supplier deleted successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete supplier!');
            }
        }

        redirect('suppliers');
    }

    public function export() {
        $search = $this->input->get('search') ?? '';
        $result = $this->Supplier_model->get_paginated(10000, 1, $search);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="suppliers_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Mobile', 'Email', 'Address', 'City']);

        foreach ($result->data as $supplier) {
            fputcsv($output, [
                $supplier->supplier_id,
                $supplier->supplier_name,
                $supplier->supplier_mobile,
                $supplier->emailnumber,
                $supplier->address,
                $supplier->city
            ]);
        }

        fclose($output);
        exit;
    }

    public function search() {
        $term = $this->input->get('term');

        $this->db->select('supplier_id, supplier_name, supplier_mobile, emailnumber');
        $this->db->like('supplier_name', $term);
        $this->db->or_like('supplier_mobile', $term);
        $this->db->limit(10);
        $suppliers = $this->db->get('supplier_information')->result();

        $results = [];
        foreach ($suppliers as $supplier) {
            $results[] = [
                'id' => $supplier->supplier_id,
                'label' => $supplier->supplier_name . ' (' . $supplier->supplier_mobile . ')',
                'value' => $supplier->supplier_name,
                'mobile' => $supplier->supplier_mobile,
                'email' => $supplier->emailnumber
            ];
        }

        echo json_encode($results);
    }
}
