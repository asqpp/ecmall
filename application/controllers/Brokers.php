<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Brokers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Broker_model');
    }

    /**
     * List all brokers
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $result = $this->Broker_model->get_paginated(25, $page, $search);

        $data = [
            'page_title' => 'Brokers',
            'brokers' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'main_content' => 'brokers/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new broker
     */
    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('broker_name', 'Broker Name', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'valid_email|trim');
            $this->form_validation->set_rules('commission_rate', 'Commission Rate', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $broker_data = [
                    'broker_code' => $this->Broker_model->generate_code(),
                    'broker_name' => $this->input->post('broker_name'),
                    'contact_person' => $this->input->post('contact_person'),
                    'mobile' => $this->input->post('mobile'),
                    'email' => $this->input->post('email'),
                    'address' => $this->input->post('address'),
                    'commission_rate' => $this->input->post('commission_rate'),
                    'status' => 1
                ];

                $broker_id = $this->Broker_model->insert($broker_data);

                if ($broker_id) {
                    $this->session->set_flashdata('success', 'Broker added successfully!');
                    redirect('brokers/view/' . $broker_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to add broker.');
                }
            }
        }

        $data = [
            'page_title' => 'Add Broker',
            'broker' => null,
            'main_content' => 'brokers/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Edit broker
     */
    public function edit($broker_id) {
        $broker = $this->Broker_model->get_by_id($broker_id);

        if (!$broker) {
            show_404();
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('broker_name', 'Broker Name', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'valid_email|trim');
            $this->form_validation->set_rules('commission_rate', 'Commission Rate', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $broker_data = [
                    'broker_name' => $this->input->post('broker_name'),
                    'contact_person' => $this->input->post('contact_person'),
                    'mobile' => $this->input->post('mobile'),
                    'email' => $this->input->post('email'),
                    'address' => $this->input->post('address'),
                    'commission_rate' => $this->input->post('commission_rate')
                ];

                $updated = $this->Broker_model->update($broker_id, $broker_data);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Broker updated successfully!');
                    redirect('brokers/view/' . $broker_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to update broker.');
                }
            }
        }

        $data = [
            'page_title' => 'Edit Broker',
            'broker' => $broker,
            'main_content' => 'brokers/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View broker details with commission tracking
     */
    public function view($broker_id) {
        $broker = $this->Broker_model->get_with_commission($broker_id);

        if (!$broker) {
            show_404();
        }

        // Get commission history
        $this->db->select('bc.*, i.invoice as invoice_no, i.date as invoice_date, i.grand_total,
                          c.customer_name');
        $this->db->from('broker_commission bc');
        $this->db->join('invoice i', 'bc.invoice_id = i.invoice_id', 'left');
        $this->db->join('customer_information c', 'i.customer_id = c.customer_id', 'left');
        $this->db->where('bc.broker_id', $broker_id);
        $this->db->order_by('i.date', 'DESC');
        $commissions = $this->db->get()->result();

        $data = [
            'page_title' => 'Broker Details',
            'broker' => $broker,
            'commissions' => $commissions,
            'main_content' => 'brokers/view'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete broker
     */
    public function delete($broker_id) {
        $broker = $this->Broker_model->get_by_id($broker_id);

        if (!$broker) {
            show_404();
        }

        // Check if broker has any invoices
        $this->db->where('broker_id', $broker_id);
        $invoice_count = $this->db->count_all_results('invoice');

        if ($invoice_count > 0) {
            $this->session->set_flashdata('error', 'Cannot delete broker with existing invoices.');
        } else {
            $deleted = $this->Broker_model->delete($broker_id);

            if ($deleted) {
                $this->session->set_flashdata('success', 'Broker deleted successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete broker.');
            }
        }

        redirect('brokers');
    }

    /**
     * Pay commission
     */
    public function pay_commission($commission_id) {
        if ($this->input->post()) {
            $paid_amount = $this->input->post('paid_amount');
            $payment_date = $this->input->post('payment_date');
            $payment_method = $this->input->post('payment_method');
            $notes = $this->input->post('notes');

            $this->db->set('paid_amount', 'paid_amount + ' . $paid_amount, FALSE);
            $this->db->set('payment_status', 'partial');
            $this->db->set('last_payment_date', $payment_date);
            $this->db->set('payment_method', $payment_method);
            $this->db->set('payment_notes', $notes);
            $this->db->where('commission_id', $commission_id);

            // Check if fully paid
            $this->db->where('(paid_amount + ' . $paid_amount . ') >= commission_amount', null, false);
            $this->db->set('payment_status', 'paid');

            $updated = $this->db->update('broker_commission');

            if ($updated) {
                $this->session->set_flashdata('success', 'Commission payment recorded successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to record payment.');
            }

            // Get broker_id to redirect
            $commission = $this->db->get_where('broker_commission', ['commission_id' => $commission_id])->row();
            redirect('brokers/view/' . $commission->broker_id);
        }
    }

    /**
     * Commission report
     */
    public function commission_report() {
        $from_date = $this->input->get('from_date');
        $to_date = $this->input->get('to_date');
        $broker_id = $this->input->get('broker_id');

        $this->db->select('b.broker_name, b.commission_rate,
                          COUNT(DISTINCT i.invoice_id) as total_invoices,
                          COALESCE(SUM(i.grand_total), 0) as total_sales,
                          COALESCE(SUM(bc.commission_amount), 0) as total_commission,
                          COALESCE(SUM(bc.paid_amount), 0) as commission_paid,
                          (COALESCE(SUM(bc.commission_amount), 0) - COALESCE(SUM(bc.paid_amount), 0)) as commission_due');
        $this->db->from('broker b');
        $this->db->join('invoice i', 'b.broker_id = i.broker_id', 'left');
        $this->db->join('broker_commission bc', 'i.invoice_id = bc.invoice_id', 'left');

        if ($broker_id) {
            $this->db->where('b.broker_id', $broker_id);
        }

        if ($from_date) {
            $this->db->where('i.date >=', $from_date);
        }

        if ($to_date) {
            $this->db->where('i.date <=', $to_date);
        }

        $this->db->group_by('b.broker_id');
        $this->db->order_by('total_commission', 'DESC');
        $report = $this->db->get()->result();

        // Get all brokers for filter
        $brokers = $this->Broker_model->get_for_dropdown();

        $data = [
            'page_title' => 'Broker Commission Report',
            'report' => $report,
            'brokers' => $brokers,
            'filters' => [
                'from_date' => $from_date,
                'to_date' => $to_date,
                'broker_id' => $broker_id
            ],
            'main_content' => 'brokers/commission_report'
        ];

        $this->load->view('templates/modern_layout', $data);
    }
}
