<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Units extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Unit_model');
    }

    /**
     * List all units
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $result = $this->Unit_model->get_paginated(25, $page, $search);

        $data = [
            'page_title' => 'Units of Measurement',
            'units' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'main_content' => 'units/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new unit
     */
    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('unit_name', 'Unit Name', 'required|trim');
            $this->form_validation->set_rules('unit_short_name', 'Short Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $unit_data = [
                    'unit_name' => $this->input->post('unit_name'),
                    'unit_short_name' => $this->input->post('unit_short_name'),
                    'status' => 1
                ];

                $unit_id = $this->Unit_model->insert($unit_data);

                if ($unit_id) {
                    $this->session->set_flashdata('success', 'Unit added successfully!');
                    redirect('units');
                } else {
                    $this->session->set_flashdata('error', 'Failed to add unit.');
                }
            }
        }

        $data = [
            'page_title' => 'Add Unit',
            'unit' => null,
            'main_content' => 'units/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Edit unit
     */
    public function edit($unit_id) {
        $unit = $this->Unit_model->get_by_id($unit_id);

        if (!$unit) {
            show_404();
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('unit_name', 'Unit Name', 'required|trim');
            $this->form_validation->set_rules('unit_short_name', 'Short Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $unit_data = [
                    'unit_name' => $this->input->post('unit_name'),
                    'unit_short_name' => $this->input->post('unit_short_name')
                ];

                $updated = $this->Unit_model->update($unit_id, $unit_data);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Unit updated successfully!');
                    redirect('units');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update unit.');
                }
            }
        }

        $data = [
            'page_title' => 'Edit Unit',
            'unit' => $unit,
            'main_content' => 'units/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete unit
     */
    public function delete($unit_id) {
        $unit = $this->Unit_model->get_by_id($unit_id);

        if (!$unit) {
            show_404();
        }

        // Check if unit has any products
        $this->db->where('unit_id', $unit_id);
        $product_count = $this->db->count_all_results('product_information');

        if ($product_count > 0) {
            $this->session->set_flashdata('error', "Cannot delete unit. {$product_count} product(s) are using this unit.");
        } else {
            $deleted = $this->Unit_model->delete($unit_id);

            if ($deleted) {
                $this->session->set_flashdata('success', 'Unit deleted successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete unit.');
            }
        }

        redirect('units');
    }
}
