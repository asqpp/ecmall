<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchases extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Purchase_model');
        $this->load->model('Supplier_model');
        $this->load->model('Product_model');
    }

    /**
     * List all purchases
     */
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $filters = [
            'from_date' => $this->input->get('from_date'),
            'to_date' => $this->input->get('to_date')
        ];

        $result = $this->Purchase_model->get_paginated(25, $page, $search, $filters);

        $data = [
            'page_title' => 'Purchases',
            'purchases' => $result->data,
            'pagination' => $result,
            'search' => $search,
            'filters' => $filters,
            'main_content' => 'purchases/index'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Add new purchase
     */
    public function add() {
        if ($this->input->post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('supplier_id', 'Supplier', 'required');
            $this->form_validation->set_rules('purchase_date', 'Purchase Date', 'required');
            $this->form_validation->set_rules('items', 'Items', 'required');

            if ($this->form_validation->run() === TRUE) {
                // Parse items JSON
                $items_json = $this->input->post('items');
                $items = json_decode($items_json, true);

                if (empty($items)) {
                    $this->session->set_flashdata('error', 'Please add at least one item to the purchase.');
                } else {
                    // Calculate totals
                    $subtotal = 0;
                    foreach ($items as &$item) {
                        $item['total_price'] = $item['quantity'] * $item['rate'];
                        $subtotal += $item['total_price'];
                    }

                    $vat = ($subtotal * 5) / 100; // 5% VAT
                    $grand_total = $subtotal + $vat;

                    // Prepare purchase data
                    $purchase_data = [
                        'supplier_id' => $this->input->post('supplier_id'),
                        'purchase_date' => $this->input->post('purchase_date'),
                        'chalan_no' => $this->Purchase_model->generate_chalan_number(),
                        'total_amount' => $subtotal,
                        'vat' => $vat,
                        'grand_total_amount' => $grand_total,
                        'purchase_details' => $this->input->post('notes'),
                        'payment_status' => 'unpaid',
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    $purchase_id = $this->Purchase_model->create_purchase($purchase_data, $items);

                    if ($purchase_id) {
                        $this->session->set_flashdata('success', 'Purchase recorded successfully! Stock has been updated.');
                        redirect('purchases/view/' . $purchase_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to record purchase. Please try again.');
                    }
                }
            }
        }

        // Get suppliers and products for dropdowns
        $data = [
            'page_title' => 'Add Purchase',
            'suppliers' => $this->Supplier_model->get_all(),
            'products' => $this->Product_model->get_all(),
            'main_content' => 'purchases/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * View purchase details
     */
    public function view($purchase_id) {
        $purchase = $this->Purchase_model->get_purchase_details($purchase_id);

        if (!$purchase) {
            show_404();
        }

        $data = [
            'page_title' => 'Purchase Details',
            'purchase' => $purchase,
            'main_content' => 'purchases/view'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Delete purchase
     */
    public function delete($purchase_id) {
        $purchase = $this->Purchase_model->get_by_id($purchase_id);

        if (!$purchase) {
            show_404();
        }

        // Check if there are payments
        $this->db->where('purchase_id', $purchase_id);
        $payments_count = $this->db->count_all_results('payment');

        if ($payments_count > 0) {
            $this->session->set_flashdata('error', 'Cannot delete purchase. It has associated payments.');
            redirect('purchases');
        }

        // Delete purchase (should also reverse accounting entries and stock)
        $this->load->model('Daybook_model');
        $this->Daybook_model->reverse_entries('purchase', $purchase_id);

        // TODO: Reverse stock changes

        $deleted = $this->Purchase_model->delete($purchase_id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Purchase deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete purchase. Please try again.');
        }

        redirect('purchases');
    }

    /**
     * Export purchases to CSV
     */
    public function export() {
        $purchases = $this->Purchase_model->get_all();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="purchases_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Chalan No', 'Supplier', 'Date', 'Total', 'VAT', 'Grand Total', 'Payment Status']);

        foreach ($purchases as $purchase) {
            fputcsv($output, [
                $purchase->purchase_id,
                $purchase->chalan_no,
                $purchase->supplier_name ?? 'N/A',
                $purchase->purchase_date,
                $purchase->total_amount,
                $purchase->vat,
                $purchase->grand_total_amount,
                ucfirst($purchase->payment_status ?? 'unpaid')
            ]);
        }

        fclose($output);
    }

    /**
     * Get product details (AJAX)
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
