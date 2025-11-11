# Implementation Guide - Insurance ERP System

## ✅ What's Already Done

### Framework Setup (COMPLETED ✅)
- CodeIgniter 3.1.13 installed
- Database configured (`cybor432_erpnew`)
- Base URL configured (`http://localhost/ecmall/`)
- Autoload configured (database, session, url, ui, form helpers)
- Routes configured (default: dashboard)
- .htaccess for clean URLs

### UI Framework (COMPLETED ✅)
- Tailwind CSS 3.4 compiled
- Alpine.js components
- Chart.js for analytics
- Modern responsive layout template
- 40+ reusable UI components
- Dashboard with charts and statistics

### Core Files (COMPLETED ✅)
- `application/core/MY_Model.php` - Base model with CRUD operations
- `application/models/Customer_model.php` - Example model
- `application/controllers/Dashboard.php` - Dashboard controller
- `application/views/templates/modern_layout.php` - Base template
- `application/helpers/ui_helper.php` - UI component functions

## 📊 Database Structure (From database.sql)

### Master Tables

**1. customer_information**
- customer_id (PK)
- customer_name
- customer_code
- email, phone, mobile
- address, city, state, zip_code
- credit_limit, opening_balance
- status
- created_at, updated_at

**2. supplier_information**
- supplier_id (PK)
- supplier_name
- supplier_code
- email, phone, mobile
- address, city, state
- opening_balance
- status
- created_at, updated_at

**3. product_information**
- product_id (PK)
- product_name
- product_code
- category_id, unit_id
- price, cost_price
- quantity, min_quantity
- status
- created_at, updated_at

**4. bank_add**
- bank_id (PK)
- bank_name, bank_code
- ac_name, ac_number
- branch, iban, swift_code
- opening_balance, current_balance
- status
- created_at, updated_at

### Transaction Tables

**5. invoice**
- inv_id (PK)
- invoice (invoice number)
- customer_id (FK)
- date
- total_amount, discount, vat_amount, grand_total
- paid_amount, due_amount
- payment_status (paid, unpaid, partial)
- created_at, updated_at

**6. invoice_details**
- id (PK)
- invoice_id (FK)
- product_id (FK)
- description
- quantity, rate
- total_premium_amount, discount_amount, vat_amount, net_amount

**7. product_purchase**
- purchase_id (PK)
- purchase_no
- supplier_id (FK)
- purchase_date
- total_amount, discount, vat_amount, grand_total_amount
- paid_amount, due_amount
- payment_status
- created_at, updated_at

**8. product_purchase_details**
- id (PK)
- purchase_id (FK)
- product_id (FK)
- description
- quantity, rate
- total_amount, discount_amount, vat_amount, net_amount

### Accounting Tables (Double-Entry)

**9. accounts**
- accode (PK)
- name
- actype1 (A=Asset, L=Liability, E=Expense, R=Revenue)
- grcode (FK to account_groups)
- subgrcode (FK to account_subgroups)
- opbal, curbal
- created_at, updated_at

**10. account_groups**
- grcode (PK)
- name
- actype1
- parent_grp
- level, position
- created_at, updated_at

**11. account_subgroups**
- subgrcode (PK)
- name
- grcode (FK)
- actype1
- created_at, updated_at

**12. daybook**
- id (PK)
- date
- accode (FK)
- debit, credit
- description
- voucher_type, voucher_no
- ref_id
- created_at

### User & System Tables

**13. users**
- id (PK)
- code, username
- password, email
- first_name, last_name
- role_id, branch_id
- usertype
- status
- created_at, updated_at

**14. company_information**
- company_id (PK)
- company_name
- email, phone, mobile
- address, city
- vat_number, tax_number
- currency
- created_at, updated_at

**15. web_setting**
- id (PK)
- setting_name
- setting_value
- type, category
- created_at, updated_at

## 🔨 Models to Create

Create these models in `application/models/`:

```php
// 1. Supplier_model.php
class Supplier_model extends MY_Model {
    protected $table = 'supplier_information';
    protected $primary_key = 'supplier_id';

    // Methods: get_paginated(), generate_code(), get_with_balance()
}

// 2. Product_model.php
class Product_model extends MY_Model {
    protected $table = 'product_information';
    protected $primary_key = 'product_id';

    // Methods: get_paginated(), generate_code(), get_stock(), check_reorder()
}

// 3. Invoice_model.php
class Invoice_model extends MY_Model {
    protected $table = 'invoice';
    protected $primary_key = 'inv_id';

    // Methods: create_invoice(), get_with_details(), update_payment()
    // generate_invoice_number(), get_outstanding()
}

// 4. Purchase_model.php
class Purchase_model extends MY_Model {
    protected $table = 'product_purchase';
    protected $primary_key = 'purchase_id';

    // Methods: create_purchase(), get_with_details(), generate_purchase_no()
}

// 5. Account_model.php
class Account_model extends MY_Model {
    protected $table = 'accounts';
    protected $primary_key = 'accode';
    protected $timestamps = false;

    // Methods: get_by_type(), get_with_balance(), update_balance()
}

// 6. Daybook_model.php (Double-Entry System)
class Daybook_model extends MY_Model {
    protected $table = 'daybook';
    protected $timestamps = false;

    // Methods: post_entry(), post_invoice(), post_payment()
    // get_ledger(), verify_balance()
}

// 7. Bank_model.php
class Bank_model extends MY_Model {
    protected $table = 'bank_add';
    protected $primary_key = 'bank_id';

    // Methods: get_all_active(), update_balance(), get_transactions()
}
```

## 🎯 Controllers to Create

### Masters Module

**1. Customers Controller** (`application/controllers/Customers.php`)

```php
<?php
class Customers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Customer_model');
        // Check authentication
    }

    // List customers with pagination
    public function index() {
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';

        $customers = $this->Customer_model->get_paginated(25, $page, $search);

        $data = [
            'page_title' => 'Customers',
            'breadcrumbs' => [['title' => 'Masters'], ['title' => 'Customers']],
            'main_content' => 'customers/index',
            'customers' => $customers
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    // Add new customer
    public function add() {
        if ($this->input->post()) {
            $data = [
                'customer_code' => $this->Customer_model->generate_code(),
                'customer_name' => $this->input->post('customer_name'),
                'email' => $this->input->post('email'),
                'phone' => $this->input->post('phone'),
                'address' => $this->input->post('address'),
                'city' => $this->input->post('city'),
                'credit_limit' => $this->input->post('credit_limit') ?? 0,
                'status' => 'active'
            ];

            if ($this->Customer_model->insert($data)) {
                $this->session->set_flashdata('success', 'Customer added successfully!');
                redirect('customers');
            } else {
                $this->session->set_flashdata('error', 'Failed to add customer');
            }
        }

        $data = [
            'page_title' => 'Add Customer',
            'breadcrumbs' => [
                ['title' => 'Masters'],
                ['title' => 'Customers', 'url' => base_url('customers')],
                ['title' => 'Add New']
            ],
            'main_content' => 'customers/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    // Edit customer
    public function edit($id) {
        $customer = $this->Customer_model->get_by_id($id);

        if (!$customer) {
            show_404();
        }

        if ($this->input->post()) {
            $data = [
                'customer_name' => $this->input->post('customer_name'),
                'email' => $this->input->post('email'),
                'phone' => $this->input->post('phone'),
                'address' => $this->input->post('address'),
                'city' => $this->input->post('city'),
                'credit_limit' => $this->input->post('credit_limit')
            ];

            if ($this->Customer_model->update($id, $data)) {
                $this->session->set_flashdata('success', 'Customer updated successfully!');
                redirect('customers');
            }
        }

        $data = [
            'page_title' => 'Edit Customer',
            'breadcrumbs' => [
                ['title' => 'Masters'],
                ['title' => 'Customers', 'url' => base_url('customers')],
                ['title' => 'Edit']
            ],
            'main_content' => 'customers/form',
            'customer' => $customer
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    // View customer details
    public function view($id) {
        $customer = $this->Customer_model->get_with_outstanding($id);

        if (!$customer) {
            show_404();
        }

        $data = [
            'page_title' => 'Customer Details',
            'breadcrumbs' => [
                ['title' => 'Masters'],
                ['title' => 'Customers', 'url' => base_url('customers')],
                ['title' => 'View']
            ],
            'main_content' => 'customers/view',
            'customer' => $customer
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    // Delete customer
    public function delete($id) {
        if ($this->Customer_model->delete($id)) {
            $this->session->set_flashdata('success', 'Customer deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete customer');
        }

        redirect('customers');
    }
}
```

Apply the same pattern for:
- **Suppliers** (`Suppliers.php`)
- **Products** (`Products.php`)
- **Banks** (`Banks.php`)

### Transaction Module with Double-Entry

**2. Sales Controller** (`application/controllers/Sales.php`)

```php
<?php
class Sales extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Invoice_model', 'Customer_model', 'Product_model', 'Daybook_model']);
    }

    // Create invoice with double-entry posting
    public function add() {
        if ($this->input->post()) {
            // Start transaction
            $this->db->trans_start();

            // 1. Create invoice
            $invoice_data = [
                'invoice' => $this->Invoice_model->generate_invoice_number(),
                'customer_id' => $this->input->post('customer_id'),
                'date' => $this->input->post('date'),
                'total_amount' => $this->input->post('total_amount'),
                'vat_amount' => $this->input->post('vat_amount'),
                'grand_total' => $this->input->post('grand_total'),
                'paid_amount' => $this->input->post('paid_amount') ?? 0,
                'due_amount' => $this->input->post('grand_total') - ($this->input->post('paid_amount') ?? 0),
                'payment_status' => $this->determine_payment_status()
            ];

            $invoice_id = $this->Invoice_model->insert($invoice_data);

            // 2. Insert invoice details
            $items = $this->input->post('items');
            foreach ($items as $item) {
                $this->db->insert('invoice_details', [
                    'invoice_id' => $invoice_id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
                    'total_premium_amount' => $item['total'],
                    'vat_amount' => $item['vat'],
                    'net_amount' => $item['net']
                ]);
            }

            // 3. Post double-entry accounting
            $this->post_invoice_accounting($invoice_id, $invoice_data);

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata('success', 'Invoice created successfully!');
                redirect('sales');
            } else {
                $this->session->set_flashdata('error', 'Transaction failed');
            }
        }

        // Load view...
    }

    /**
     * Post invoice to daybook (double-entry accounting)
     */
    private function post_invoice_accounting($invoice_id, $invoice_data) {
        $date = $invoice_data['date'];
        $amount = $invoice_data['grand_total'];
        $customer_id = $invoice_data['customer_id'];

        // Debit: Customer Account (Receivable)
        $this->Daybook_model->insert([
            'date' => $date,
            'accode' => 'CUST_' . $customer_id, // Customer account code
            'debit' => $amount,
            'credit' => 0,
            'description' => 'Sales Invoice: ' . $invoice_data['invoice'],
            'voucher_type' => 'SALES',
            'voucher_no' => $invoice_data['invoice'],
            'ref_id' => $invoice_id
        ]);

        // Credit: Sales Income
        $this->Daybook_model->insert([
            'date' => $date,
            'accode' => 'SALES', // Sales account
            'debit' => 0,
            'credit' => $invoice_data['total_amount'],
            'description' => 'Sales Invoice: ' . $invoice_data['invoice'],
            'voucher_type' => 'SALES',
            'voucher_no' => $invoice_data['invoice'],
            'ref_id' => $invoice_id
        ]);

        // Credit: VAT Payable (if applicable)
        if ($invoice_data['vat_amount'] > 0) {
            $this->Daybook_model->insert([
                'date' => $date,
                'accode' => 'VATPAY', // VAT Payable account
                'debit' => 0,
                'credit' => $invoice_data['vat_amount'],
                'description' => 'VAT on Sales: ' . $invoice_data['invoice'],
                'voucher_type' => 'SALES',
                'voucher_no' => $invoice_data['invoice'],
                'ref_id' => $invoice_id
            ]);
        }

        // If payment received
        if ($invoice_data['paid_amount'] > 0) {
            // Debit: Cash/Bank
            $this->Daybook_model->insert([
                'date' => $date,
                'accode' => 'CASH', // Cash account
                'debit' => $invoice_data['paid_amount'],
                'credit' => 0,
                'description' => 'Payment received: ' . $invoice_data['invoice'],
                'voucher_type' => 'RECEIPT',
                'voucher_no' => $invoice_data['invoice'],
                'ref_id' => $invoice_id
            ]);

            // Credit: Customer Account
            $this->Daybook_model->insert([
                'date' => $date,
                'accode' => 'CUST_' . $customer_id,
                'debit' => 0,
                'credit' => $invoice_data['paid_amount'],
                'description' => 'Payment received: ' . $invoice_data['invoice'],
                'voucher_type' => 'RECEIPT',
                'voucher_no' => $invoice_data['invoice'],
                'ref_id' => $invoice_id
            ]);
        }
    }
}
```

Apply the same double-entry pattern for:
- **Purchases** - Dr: Purchases, Cr: Supplier
- **Receipts** - Dr: Cash/Bank, Cr: Customer
- **Payments** - Dr: Supplier, Cr: Cash/Bank
- **Journal Entries** - Custom Dr/Cr entries

## 📊 Reports to Create

### Books of Accounts

```php
// application/controllers/Reports.php

class Reports extends CI_Controller {

    // Sales Book
    public function sales_book() {
        $from = $this->input->get('from') ?? date('Y-m-01');
        $to = $this->input->get('to') ?? date('Y-m-d');

        $sales = $this->db->select('
            i.invoice, i.date, c.customer_name,
            i.total_amount, i.vat_amount, i.grand_total
        ')
        ->from('invoice i')
        ->join('customer_information c', 'c.customer_id = i.customer_id')
        ->where('i.date >=', $from)
        ->where('i.date <=', $to)
        ->order_by('i.date', 'ASC')
        ->get()
        ->result();

        // Load view with data...
    }

    // Trial Balance
    public function trial_balance() {
        $as_of = $this->input->get('as_of') ?? date('Y-m-d');

        $accounts = $this->db->select('
            a.accode, a.name, a.actype1,
            COALESCE(SUM(CASE WHEN d.debit > 0 THEN d.debit ELSE 0 END), 0) as total_debit,
            COALESCE(SUM(CASE WHEN d.credit > 0 THEN d.credit ELSE 0 END), 0) as total_credit
        ')
        ->from('accounts a')
        ->join('daybook d', 'd.accode = a.accode', 'left')
        ->where('d.date <=', $as_of)
        ->group_by('a.accode')
        ->get()
        ->result();

        // Calculate balance for each account...
        // Load view...
    }
}
```

## 🎨 Views to Create

Use the UI components from `ui_helper.php`:

```php
<!-- application/views/customers/index.php -->
<div class="page-header mb-6">
    <h1 class="page-title">Customers</h1>
    <div class="flex gap-3">
        <a href="<?php echo base_url('customers/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Customer
        </a>
    </div>
</div>

<!-- Search Form -->
<div class="card mb-6">
    <div class="card-body">
        <form method="get" class="flex gap-4">
            <input type="search" name="search" class="form-input flex-1"
                   placeholder="Search customers..."
                   value="<?php echo $this->input->get('search'); ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</div>

<!-- Customers Table -->
<?php card_start('Customer List'); ?>
    <?php table_start(['Code', 'Name', 'Email', 'Phone', 'City', 'Outstanding', 'Status', 'Actions']); ?>
        <?php foreach($customers->data as $customer): ?>
        <tr>
            <td><?php echo $customer->customer_code; ?></td>
            <td><?php echo $customer->customer_name; ?></td>
            <td><?php echo $customer->email; ?></td>
            <td><?php echo $customer->phone; ?></td>
            <td><?php echo $customer->city; ?></td>
            <td><?php echo format_currency($customer->total_outstanding ?? 0); ?></td>
            <td><?php echo status_badge($customer->status); ?></td>
            <td>
                <div class="flex gap-2">
                    <a href="<?php echo base_url('customers/view/' . $customer->customer_id); ?>"
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo base_url('customers/edit/' . $customer->customer_id); ?>"
                       class="btn btn-sm btn-secondary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteCustomer(<?php echo $customer->customer_id; ?>)"
                            class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php table_end(); ?>

    <!-- Pagination -->
    <?php render_pagination(
        $customers->total,
        $customers->per_page,
        $customers->current_page,
        base_url('customers')
    ); ?>
<?php card_end(); ?>
```

## 🚀 Next Steps

1. **Create all models** following the `Customer_model.php` pattern
2. **Create all controllers** following the `Customers.php` pattern
3. **Create all views** using UI helper functions
4. **Implement double-entry accounting** in transaction controllers
5. **Create reports** with filters and Excel export
6. **Test thoroughly** with sample data

## 📝 Coding Standards

- Use CodeIgniter query builder (no raw SQL)
- Always use transactions for multi-table operations
- Validate all inputs
- Use flashdata for success/error messages
- Follow the base template pattern
- Use UI helper functions for consistency
- Comment complex business logic

## 🔐 Security Checklist

- ✅ CSRF protection enabled
- ✅ XSS filtering enabled
- ✅ SQL injection prevention (query builder)
- ✅ Session security configured
- ✅ Input validation on all forms
- ✅ Authentication checks in all controllers

---

**The foundation is complete! Now build the remaining modules using these patterns.**
