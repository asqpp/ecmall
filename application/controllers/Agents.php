<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agents extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Agent_model');
    }

    /**
     * List all agents
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $result = $this->Agent_model->get_paginated(25, $page, $search);

        $data = [
            'page_title' => 'Agents',
            'agents' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'main_content' => 'agents/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new agent
     */
    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('agent_name', 'Agent Name', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'valid_email|trim');
            $this->form_validation->set_rules('commission_rate', 'Commission Rate', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $agent_data = [
                    'agent_code' => $this->Agent_model->generate_code(),
                    'agent_name' => $this->input->post('agent_name'),
                    'contact_person' => $this->input->post('contact_person'),
                    'mobile' => $this->input->post('mobile'),
                    'email' => $this->input->post('email'),
                    'address' => $this->input->post('address'),
                    'commission_rate' => $this->input->post('commission_rate'),
                    'status' => 1
                ];

                $agent_id = $this->Agent_model->insert($agent_data);

                if ($agent_id) {
                    $this->session->set_flashdata('success', 'Agent added successfully!');
                    redirect('agents/view/' . $agent_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to add agent.');
                }
            }
        }

        $data = [
            'page_title' => 'Add Agent',
            'agent' => null,
            'main_content' => 'agents/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Edit agent
     */
    public function edit($agent_id) {
        $agent = $this->Agent_model->get_by_id($agent_id);

        if (!$agent) {
            show_404();
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('agent_name', 'Agent Name', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'valid_email|trim');
            $this->form_validation->set_rules('commission_rate', 'Commission Rate', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $agent_data = [
                    'agent_name' => $this->input->post('agent_name'),
                    'contact_person' => $this->input->post('contact_person'),
                    'mobile' => $this->input->post('mobile'),
                    'email' => $this->input->post('email'),
                    'address' => $this->input->post('address'),
                    'commission_rate' => $this->input->post('commission_rate')
                ];

                $updated = $this->Agent_model->update($agent_id, $agent_data);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Agent updated successfully!');
                    redirect('agents/view/' . $agent_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to update agent.');
                }
            }
        }

        $data = [
            'page_title' => 'Edit Agent',
            'agent' => $agent,
            'main_content' => 'agents/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View agent details with commission tracking
     */
    public function view($agent_id) {
        $agent = $this->Agent_model->get_with_commission($agent_id);

        if (!$agent) {
            show_404();
        }

        // Get commission history
        $this->db->select('ac.*, i.invoice as invoice_no, i.date as invoice_date, i.grand_total,
                          c.customer_name');
        $this->db->from('agent_commission ac');
        $this->db->join('invoice i', 'ac.invoice_id = i.invoice_id', 'left');
        $this->db->join('customer_information c', 'i.customer_id = c.customer_id', 'left');
        $this->db->where('ac.agent_id', $agent_id);
        $this->db->order_by('i.date', 'DESC');
        $commissions = $this->db->get()->result();

        $data = [
            'page_title' => 'Agent Details',
            'agent' => $agent,
            'commissions' => $commissions,
            'main_content' => 'agents/view'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete agent
     */
    public function delete($agent_id) {
        $agent = $this->Agent_model->get_by_id($agent_id);

        if (!$agent) {
            show_404();
        }

        // Check if agent has any invoices
        $this->db->where('agent_id', $agent_id);
        $invoice_count = $this->db->count_all_results('invoice');

        if ($invoice_count > 0) {
            $this->session->set_flashdata('error', 'Cannot delete agent with existing invoices.');
        } else {
            $deleted = $this->Agent_model->delete($agent_id);

            if ($deleted) {
                $this->session->set_flashdata('success', 'Agent deleted successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete agent.');
            }
        }

        redirect('agents');
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

            $updated = $this->db->update('agent_commission');

            if ($updated) {
                $this->session->set_flashdata('success', 'Commission payment recorded successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to record payment.');
            }

            // Get agent_id to redirect
            $commission = $this->db->get_where('agent_commission', ['commission_id' => $commission_id])->row();
            redirect('agents/view/' . $commission->agent_id);
        }
    }

    /**
     * Commission report
     */
    public function commission_report() {
        $from_date = $this->input->get('from_date');
        $to_date = $this->input->get('to_date');
        $agent_id = $this->input->get('agent_id');

        $this->db->select('a.agent_name, a.commission_rate,
                          COUNT(DISTINCT i.invoice_id) as total_invoices,
                          COALESCE(SUM(i.grand_total), 0) as total_sales,
                          COALESCE(SUM(ac.commission_amount), 0) as total_commission,
                          COALESCE(SUM(ac.paid_amount), 0) as commission_paid,
                          (COALESCE(SUM(ac.commission_amount), 0) - COALESCE(SUM(ac.paid_amount), 0)) as commission_due');
        $this->db->from('agent a');
        $this->db->join('invoice i', 'a.agent_id = i.agent_id', 'left');
        $this->db->join('agent_commission ac', 'i.invoice_id = ac.invoice_id', 'left');

        if ($agent_id) {
            $this->db->where('a.agent_id', $agent_id);
        }

        if ($from_date) {
            $this->db->where('i.date >=', $from_date);
        }

        if ($to_date) {
            $this->db->where('i.date <=', $to_date);
        }

        $this->db->group_by('a.agent_id');
        $this->db->order_by('total_commission', 'DESC');
        $report = $this->db->get()->result();

        // Get all agents for filter
        $agents = $this->Agent_model->get_for_dropdown();

        $data = [
            'page_title' => 'Agent Commission Report',
            'report' => $report,
            'agents' => $agents,
            'filters' => [
                'from_date' => $from_date,
                'to_date' => $to_date,
                'agent_id' => $agent_id
            ],
            'main_content' => 'agents/commission_report'
        ];

        $this->load->view('templates/modern_layout', $data);
    }
}
