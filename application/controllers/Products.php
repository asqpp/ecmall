<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Category_model');
        $this->load->model('Unit_model');
    }

    /**
     * List all products with pagination and search
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $result = $this->Product_model->get_paginated(25, $page, $search);

        $data = [
            'page_title' => 'Products',
            'products' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'main_content' => 'products/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new product
     */
    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('product_name', 'Product Name', 'required|trim');
            $this->form_validation->set_rules('category_id', 'Category', 'required');
            $this->form_validation->set_rules('unit_id', 'Unit', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $product_data = [
                    'product_name' => $this->input->post('product_name'),
                    'product_model' => $this->input->post('product_model'),
                    'category_id' => $this->input->post('category_id'),
                    'unit_id' => $this->input->post('unit_id'),
                    'price' => $this->input->post('price'),
                    'quantity' => $this->input->post('quantity') ?? 0,
                    'supplier_price' => $this->input->post('supplier_price') ?? 0,
                    'product_details' => $this->input->post('product_details'),
                    'status' => $this->input->post('status') ?? 1,
                    'image' => $this->input->post('image'),
                    'tax' => $this->input->post('tax') ?? 0
                ];

                $product_id = $this->Product_model->insert($product_data);

                if ($product_id) {
                    $this->session->set_flashdata('success', 'Product added successfully!');
                    redirect('products/view/' . $product_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to add product. Please try again.');
                }
            }
        }

        // Get categories and units for dropdowns
        $data = [
            'page_title' => 'Add Product',
            'categories' => $this->Category_model->get_all(),
            'units' => $this->Unit_model->get_all(),
            'product' => null,
            'main_content' => 'products/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Edit product
     */
    public function edit($product_id) {
        $product = $this->Product_model->get_by_id($product_id);

        if (!$product) {
            show_404();
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('product_name', 'Product Name', 'required|trim');
            $this->form_validation->set_rules('category_id', 'Category', 'required');
            $this->form_validation->set_rules('unit_id', 'Unit', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $product_data = [
                    'product_name' => $this->input->post('product_name'),
                    'product_model' => $this->input->post('product_model'),
                    'category_id' => $this->input->post('category_id'),
                    'unit_id' => $this->input->post('unit_id'),
                    'price' => $this->input->post('price'),
                    'quantity' => $this->input->post('quantity'),
                    'supplier_price' => $this->input->post('supplier_price') ?? 0,
                    'product_details' => $this->input->post('product_details'),
                    'status' => $this->input->post('status'),
                    'image' => $this->input->post('image'),
                    'tax' => $this->input->post('tax') ?? 0
                ];

                $updated = $this->Product_model->update($product_id, $product_data);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Product updated successfully!');
                    redirect('products/view/' . $product_id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to update product. Please try again.');
                }
            }
        }

        // Get categories and units for dropdowns
        $data = [
            'page_title' => 'Edit Product',
            'categories' => $this->Category_model->get_all(),
            'units' => $this->Unit_model->get_all(),
            'product' => $product,
            'main_content' => 'products/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View product details
     */
    public function view($product_id) {
        $product = $this->Product_model->get_with_stock($product_id);

        if (!$product) {
            show_404();
        }

        // Get stock movements from itemsstk if table exists
        $stock_movements = [];
        if ($this->db->table_exists('itemsstk')) {
            $this->db->where('code', $product_id);
            $this->db->order_by('tdate', 'DESC');
            $this->db->limit(50);
            $stock_movements = $this->db->get('itemsstk')->result();
        }

        $data = [
            'page_title' => 'Product Details',
            'product' => $product,
            'stock_movements' => $stock_movements,
            'main_content' => 'products/view'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete product
     */
    public function delete($product_id) {
        $product = $this->Product_model->get_by_id($product_id);

        if (!$product) {
            show_404();
        }

        // Check if product is used in any transactions
        $this->db->where('product_id', $product_id);
        $invoice_items = $this->db->count_all_results('invoice_item');

        $this->db->where('product_id', $product_id);
        $purchase_items = $this->db->count_all_results('purchase_item');

        if ($invoice_items > 0 || $purchase_items > 0) {
            $this->session->set_flashdata('error', 'Cannot delete product. It is used in transactions.');
            redirect('products');
        }

        $deleted = $this->Product_model->delete($product_id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Product deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete product. Please try again.');
        }

        redirect('products');
    }

    /**
     * Export products to CSV
     */
    public function export() {
        $products = $this->Product_model->get_all();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Model', 'Category', 'Unit', 'Price', 'Supplier Price', 'Quantity', 'Status']);

        foreach ($products as $product) {
            fputcsv($output, [
                $product->product_id,
                $product->product_name,
                $product->product_model,
                $product->category_id,
                $product->unit_id,
                $product->price,
                $product->supplier_price,
                $product->quantity,
                $product->status == 1 ? 'Active' : 'Inactive'
            ]);
        }

        fclose($output);
    }

    /**
     * Search products (for autocomplete)
     */
    public function search() {
        $term = $this->input->get('term');
        $products = $this->Product_model->search_for_autocomplete($term);

        echo json_encode($products);
    }

    /**
     * Get low stock products
     */
    public function low_stock() {
        $threshold = $this->input->get('threshold') ?? 10;
        $products = $this->Product_model->get_low_stock($threshold);

        $data = [
            'page_title' => 'Low Stock Products',
            'products' => $products,
            'threshold' => $threshold,
            'main_content' => 'products/low_stock'
        ];

        $this->load->view('templates/modern_layout', $data);
    }
}
