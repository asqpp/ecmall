<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Category_model');
    }

    /**
     * List all categories
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $result = $this->Category_model->get_paginated(25, $page, $search);

        $data = [
            'page_title' => 'Product Categories',
            'categories' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'main_content' => 'categories/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new category
     */
    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('category_name', 'Category Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $category_data = [
                    'category_name' => $this->input->post('category_name'),
                    'description' => $this->input->post('description'),
                    'status' => 1
                ];

                $category_id = $this->Category_model->insert($category_data);

                if ($category_id) {
                    $this->session->set_flashdata('success', 'Category added successfully!');
                    redirect('categories');
                } else {
                    $this->session->set_flashdata('error', 'Failed to add category.');
                }
            }
        }

        $data = [
            'page_title' => 'Add Category',
            'category' => null,
            'main_content' => 'categories/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Edit category
     */
    public function edit($category_id) {
        $category = $this->Category_model->get_by_id($category_id);

        if (!$category) {
            show_404();
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('category_name', 'Category Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $category_data = [
                    'category_name' => $this->input->post('category_name'),
                    'description' => $this->input->post('description')
                ];

                $updated = $this->Category_model->update($category_id, $category_data);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Category updated successfully!');
                    redirect('categories');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update category.');
                }
            }
        }

        $data = [
            'page_title' => 'Edit Category',
            'category' => $category,
            'main_content' => 'categories/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete category
     */
    public function delete($category_id) {
        $category = $this->Category_model->get_by_id($category_id);

        if (!$category) {
            show_404();
        }

        // Check if category has any products
        $this->db->where('category_id', $category_id);
        $product_count = $this->db->count_all_results('product_information');

        if ($product_count > 0) {
            $this->session->set_flashdata('error', "Cannot delete category. {$product_count} product(s) are using this category.");
        } else {
            $deleted = $this->Category_model->delete($category_id);

            if ($deleted) {
                $this->session->set_flashdata('success', 'Category deleted successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete category.');
            }
        }

        redirect('categories');
    }
}
