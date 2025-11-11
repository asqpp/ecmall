<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // Load helpers
        $this->load->helper('ui');

        // Check if user is logged in
        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
    }

    public function index() {
        // Get dashboard statistics
        $data = [
            'page_title' => 'Dashboard',
            'breadcrumbs' => [
                ['title' => 'Dashboard']
            ],
            'main_content' => 'dashboard/index',

            // Statistics
            'stats' => $this->get_dashboard_stats(),

            // Chart data
            'sales_chart_data' => $this->get_sales_chart_data(),
            'policy_distribution' => $this->get_policy_distribution(),
            'monthly_revenue' => $this->get_monthly_revenue(),

            // Recent activities
            'recent_invoices' => $this->get_recent_invoices(),
            'pending_payments' => $this->get_pending_payments(),
            'top_customers' => $this->get_top_customers()
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    /**
     * Get dashboard statistics
     */
    private function get_dashboard_stats() {
        // Total Sales
        $total_sales = $this->db->select('SUM(grand_total) as total')
            ->from('invoice')
            ->where('YEAR(date)', date('Y'))
            ->get()->row()->total ?? 0;

        // Total Customers
        $total_customers = $this->db->count_all_results('customer_information');

        // Total Purchases
        $total_purchases = $this->db->select('SUM(grand_total_amount) as total')
            ->from('product_purchase')
            ->where('YEAR(purchase_date)', date('Y'))
            ->get()->row()->total ?? 0;

        // Outstanding Amount
        $outstanding = $this->db->select('SUM(due_amount) as total')
            ->from('invoice')
            ->where('due_amount >', 0)
            ->get()->row()->total ?? 0;

        // Today's Sales
        $today_sales = $this->db->select('SUM(grand_total) as total')
            ->from('invoice')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->get()->row()->total ?? 0;

        // This Month Sales
        $month_sales = $this->db->select('SUM(grand_total) as total')
            ->from('invoice')
            ->where('YEAR(date)', date('Y'))
            ->where('MONTH(date)', date('m'))
            ->get()->row()->total ?? 0;

        // Calculate growth percentages
        $last_month_sales = $this->db->select('SUM(grand_total) as total')
            ->from('invoice')
            ->where('YEAR(date)', date('Y'))
            ->where('MONTH(date)', date('m', strtotime('-1 month')))
            ->get()->row()->total ?? 1;

        $sales_growth = $last_month_sales > 0 ? (($month_sales - $last_month_sales) / $last_month_sales * 100) : 0;

        return [
            'total_sales' => $total_sales,
            'total_customers' => $total_customers,
            'total_purchases' => $total_purchases,
            'outstanding' => $outstanding,
            'today_sales' => $today_sales,
            'month_sales' => $month_sales,
            'sales_growth' => $sales_growth
        ];
    }

    /**
     * Get sales chart data (last 6 months)
     */
    private function get_sales_chart_data() {
        $months = [];
        $sales = [];
        $purchases = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $month_name = date('M Y', strtotime("-$i months"));

            $months[] = $month_name;

            // Sales
            $month_sales = $this->db->select('SUM(grand_total) as total')
                ->from('invoice')
                ->where('DATE_FORMAT(date, "%Y-%m")', $date)
                ->get()->row()->total ?? 0;

            $sales[] = round($month_sales, 2);

            // Purchases
            $month_purchases = $this->db->select('SUM(grand_total_amount) as total')
                ->from('product_purchase')
                ->where('DATE_FORMAT(purchase_date, "%Y-%m")', $date)
                ->get()->row()->total ?? 0;

            $purchases[] = round($month_purchases, 2);
        }

        return [
            'months' => $months,
            'sales' => $sales,
            'purchases' => $purchases
        ];
    }

    /**
     * Get policy/product distribution for pie chart
     */
    private function get_policy_distribution() {
        $query = $this->db->select('p.product_name, COUNT(*) as count, SUM(id.net_amount) as total')
            ->from('invoice_details id')
            ->join('product_information p', 'p.product_id = id.product_id', 'left')
            ->group_by('id.product_id')
            ->order_by('total', 'DESC')
            ->limit(6)
            ->get();

        $labels = [];
        $data = [];

        foreach ($query->result() as $row) {
            $labels[] = $row->product_name ?? 'Others';
            $data[] = round($row->total, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get monthly revenue data
     */
    private function get_monthly_revenue() {
        $query = $this->db->select("
                MONTH(date) as month,
                SUM(total_amount) as revenue,
                SUM(paid_amount) as collected,
                SUM(due_amount) as pending
            ")
            ->from('invoice')
            ->where('YEAR(date)', date('Y'))
            ->group_by('MONTH(date)')
            ->order_by('MONTH(date)', 'ASC')
            ->get();

        $months = [];
        $revenue = [];
        $collected = [];
        $pending = [];

        foreach ($query->result() as $row) {
            $months[] = date('M', mktime(0, 0, 0, $row->month, 1));
            $revenue[] = round($row->revenue, 2);
            $collected[] = round($row->collected, 2);
            $pending[] = round($row->pending, 2);
        }

        return [
            'months' => $months,
            'revenue' => $revenue,
            'collected' => $collected,
            'pending' => $pending
        ];
    }

    /**
     * Get recent invoices
     */
    private function get_recent_invoices() {
        $query = $this->db->select('i.*, c.customer_name')
            ->from('invoice i')
            ->join('customer_information c', 'c.customer_id = i.customer_id', 'left')
            ->order_by('i.created_at', 'DESC')
            ->limit(5)
            ->get();

        return $query->result();
    }

    /**
     * Get pending payments
     */
    private function get_pending_payments() {
        $query = $this->db->select('i.*, c.customer_name')
            ->from('invoice i')
            ->join('customer_information c', 'c.customer_id = i.customer_id', 'left')
            ->where('i.due_amount >', 0)
            ->order_by('i.date', 'ASC')
            ->limit(5)
            ->get();

        return $query->result();
    }

    /**
     * Get top customers
     */
    private function get_top_customers() {
        $query = $this->db->select('c.*, SUM(i.grand_total) as total_purchase, COUNT(i.inv_id) as invoice_count')
            ->from('customer_information c')
            ->join('invoice i', 'i.customer_id = c.customer_id', 'left')
            ->where('YEAR(i.date)', date('Y'))
            ->group_by('c.customer_id')
            ->order_by('total_purchase', 'DESC')
            ->limit(5)
            ->get();

        return $query->result();
    }
}
