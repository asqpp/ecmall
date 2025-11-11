# Insurance Management System - Complete ERP v2.0

A comprehensive Insurance Management System built with **CodeIgniter**, **Tailwind CSS**, **Alpine.js**, and modern web technologies.

## 📋 Table of Contents

- [Features](#features)
- [What's Been Implemented](#whats-been-implemented)
- [Technology Stack](#technology-stack)
- [Installation Guide](#installation-guide)
- [Database Setup](#database-setup)
- [Project Structure](#project-structure)
- [UI Components](#ui-components)
- [Next Steps](#next-steps)
- [Support](#support)

## ✨ Features

### Completed Features

✅ **Modern UI Framework**
- Tailwind CSS 3.4 with custom configuration
- Alpine.js for reactive components
- Responsive, mobile-first design
- Beautiful animations with AOS and GSAP
- Chart.js for data visualization
- SweetAlert2 for elegant dialogs
- Font Awesome 6 icons

✅ **Base Template System**
- Modern sidebar layout
- Responsive navigation
- User authentication checking
- Flash message system
- Breadcrumb navigation
- Mobile-friendly menu

✅ **Dashboard**
- Sales statistics cards
- Revenue charts (Sales vs Purchases)
- Product/Policy distribution pie chart
- Monthly revenue analysis
- Recent invoices list
- Pending payments tracking
- Top customers ranking

✅ **Reusable UI Components Helper**
- Cards (with hover effects)
- Statistics cards
- Buttons (all variants)
- Badges and status badges
- Alerts (success, warning, danger, info)
- Tables (with pagination)
- Forms (input, select, textarea)
- Tabs
- Modals
- Dropdowns
- Breadcrumbs

### Planned Features (From Package Spec)

📋 **Masters Modules**
- Customers Management
- Suppliers Management
- Brokers Management
- Agents/Salesmen Management
- Products/Services Management
- Chart of Accounts
- Account Subgroups

📋 **Transaction Modules**
- Sales/Invoices (with print)
- Quotations (convertible to sales)
- Purchases
- Receipts (with payment allocation)
- Payments
- Journal Entries
- Commission Payments

📋 **Reports Modules**
- Books of Accounts (Sales Book, Purchase Book, Cash Book, Bank Book, Day Book)
- Financial Reports (Trial Balance, Balance Sheet, P&L, Cash Flow)
- Account Reports (Ledgers, Account Statement)
- Sales Reports (Sales Summary, Credit Sales, Salesman-wise, Customer-wise)
- Commission Reports (Broker & Salesman)
- Customer Reports (Ledger, Ageing, Outstanding)

📋 **HR & Payroll**
- Staff Management
- Attendance Tracking
- Leave Management
- Salary Processing

📋 **Settings**
- Company Settings
- User Management
- Role & Permissions
- Backup & Restore
- System Configuration

## 🎨 What's Been Implemented

### 1. Modern UI Framework ✅

**Files Created:**
```
package.json              # NPM dependencies
tailwind.config.js        # Tailwind configuration
assets/css/main.css       # Source CSS with Tailwind
assets/css/output.css     # Compiled Tailwind CSS (generated)
assets/js/app.js          # Alpine.js components & utilities
```

**Features:**
- Custom color palette (primary, success, warning, danger, info)
- Utility classes for common patterns
- Animations and transitions
- Responsive breakpoints
- Form styling
- Print styles

### 2. Base Template Layout ✅

**File:** `application/views/templates/modern_layout.php`

**Features:**
- Responsive sidebar navigation with collapsible menu groups
- Top header with search, notifications, and user menu
- Breadcrumb navigation
- Flash message display
- Footer with version info
- Mobile overlay for sidebar
- CDN links for all libraries

### 3. UI Components Helper ✅

**File:** `application/helpers/ui_helper.php`

**Functions Available:**
- `card_start()` / `card_end()` - Card components
- `render_stat_card()` - Statistics cards with icons
- `render_button()` - Button generator
- `badge()` / `status_badge()` - Badge components
- `alert()` - Alert messages
- `table_start()` / `table_end()` - Table components
- `form_input_group()` - Form input with label
- `form_select_group()` - Form select with label
- `form_textarea_group()` - Form textarea with label
- `tabs_start()` / `tabs_end()` - Tab components
- `render_breadcrumb()` - Breadcrumb navigation
- `render_pagination()` - Pagination
- `format_currency()` - Currency formatter
- `format_date()` / `format_datetime()` - Date formatters
- `percentage()` - Percentage calculator
- `truncate()` - Text truncator

### 4. Dashboard Controller & View ✅

**Files:**
- `application/controllers/Dashboard.php`
- `application/views/dashboard/index.php`

**Features:**
- Statistics cards (Total Sales, Customers, Outstanding, Today's Sales)
- Charts (Sales vs Purchases, Policy Distribution, Monthly Revenue)
- Recent invoices listing
- Pending payments tracking
- Top customers ranking
- Floating action button for quick actions

## 🛠 Technology Stack

### Backend
- **PHP** 7.4+
- **CodeIgniter** 3.x (Framework)
- **MySQL** 5.7+ (Database)

### Frontend
- **Tailwind CSS** 3.4 (Utility-first CSS)
- **Alpine.js** 3.13 (Lightweight JavaScript framework)
- **Chart.js** 4.4 (Charts and graphs)
- **AOS** 2.3 (Animate On Scroll)
- **GSAP** 3.12 (Professional animations)
- **SweetAlert2** 11 (Beautiful popups)
- **Toastify** 1.12 (Toast notifications)
- **Font Awesome** 6.5 (Icons)

### Development Tools
- **Node.js** & **NPM** (Package management)
- **Tailwind CLI** (CSS compilation)

## 📦 Installation Guide

### Prerequisites

1. **XAMPP/WAMP/LAMP** or similar PHP development environment
2. **PHP 7.4 or higher**
3. **MySQL 5.7 or higher**
4. **Node.js 14 or higher**
5. **Composer** (for CodeIgniter dependencies)

### Step 1: Setup Web Server

```bash
# Place the project in your web server directory
# For XAMPP: C:\xampp\htdocs\insurance-erp
# For Linux: /var/www/html/insurance-erp

# Ensure proper permissions (Linux)
sudo chown -R www-data:www-data /var/www/html/insurance-erp
sudo chmod -R 755 /var/www/html/insurance-erp
```

### Step 2: Install CodeIgniter

Since the CodeIgniter framework is not yet installed, you have two options:

**Option A: Download CodeIgniter**
```bash
cd /path/to/project
wget https://github.com/bcit-ci/CodeIgniter/archive/3.1.13.zip
unzip 3.1.13.zip
cp -r CodeIgniter-3.1.13/* .
rm -rf CodeIgniter-3.1.13 3.1.13.zip
```

**Option B: Use Composer**
```bash
composer create-project codeigniter/framework insurance-erp
# Then merge our created files into it
```

### Step 3: Install Node Dependencies

```bash
cd /home/user/ecmall

# Install all dependencies
npm install

# Build Tailwind CSS (development with watch)
npm run dev

# OR build for production
npm run build
```

### Step 4: Database Setup

1. **Create Database:**
```sql
CREATE DATABASE cybor432_erpnew CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Import Database:**
```bash
mysql -u root -p cybor432_erpnew < database.sql
```

3. **Configure Database Connection:**

Edit `application/config/database.php`:
```php
$db['default'] = array(
    'dsn'      => '',
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'cybor432_erpnew',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
```

### Step 5: Configure Application

1. **Base URL:**

Edit `application/config/config.php`:
```php
$config['base_url'] = 'http://localhost/insurance-erp/';
```

2. **Autoload UI Helper:**

Edit `application/config/autoload.php`:
```php
$autoload['helper'] = array('url', 'ui');
```

3. **Autoload Database:**
```php
$autoload['libraries'] = array('database', 'session');
```

### Step 6: Set Default Route

Edit `application/config/routes.php`:
```php
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
```

### Step 7: Access Application

Open your browser and navigate to:
```
http://localhost/insurance-erp/dashboard
```

**Default Login Credentials:**
- Username: `admin`
- Password: `admin123`

## 🗂 Project Structure

```
insurance-erp/
│
├── application/
│   ├── controllers/
│   │   └── Dashboard.php           ✅ Created
│   ├── models/                     📋 To create
│   ├── views/
│   │   ├── templates/
│   │   │   └── modern_layout.php   ✅ Created
│   │   └── dashboard/
│   │       └── index.php           ✅ Created
│   ├── helpers/
│   │   └── ui_helper.php           ✅ Created
│   ├── config/                     📋 To configure
│   ├── libraries/                  📋 To create
│   └── core/                       📋 To create
│
├── assets/
│   ├── css/
│   │   ├── main.css                ✅ Created
│   │   └── output.css              ✅ Generated
│   ├── js/
│   │   └── app.js                  ✅ Created
│   └── images/                     📋 To add
│
├── database.sql                    ✅ Provided
├── package.json                    ✅ Created
├── tailwind.config.js              ✅ Created
└── README.md                       ✅ This file

✅ = Completed
📋 = Pending
```

## 🎨 UI Components Usage

### Statistics Cards

```php
<?php render_stat_card(
    'Total Sales',
    'AED 125,000',
    '<i class="fas fa-arrow-up"></i> 12% increase',
    'fas fa-chart-line',
    'bg-gradient-primary'
); ?>
```

### Buttons

```php
<button class="btn btn-primary">
    <i class="fas fa-save"></i> Save
</button>

<button class="btn btn-outline">Cancel</button>

<?php render_button('Submit', 'success', 'fas fa-check'); ?>
```

### Cards

```php
<?php card_start('Customer Details'); ?>
    <p>Card content here...</p>
<?php card_end(); ?>
```

### Tables

```php
<?php table_start(['Name', 'Email', 'Status', 'Actions']); ?>
    <tr>
        <td>John Doe</td>
        <td>john@example.com</td>
        <td><?php echo status_badge('active'); ?></td>
        <td>
            <button class="btn btn-sm btn-primary">Edit</button>
        </td>
    </tr>
<?php table_end(); ?>
```

### Forms

```php
<?php form_input_group(
    'customer_name',
    'Customer Name',
    'text',
    true,
    '',
    'Enter full name',
    'As per official documents'
); ?>

<?php form_select_group(
    'policy_type',
    'Policy Type',
    ['motor' => 'Motor', 'health' => 'Health'],
    true
); ?>
```

### Alerts

```php
<?php alert('Record saved successfully!', 'success'); ?>
<?php alert('Please check your input', 'warning'); ?>
<?php alert('An error occurred', 'danger'); ?>
```

### Badges

```php
<?php echo badge('Active', 'success'); ?>
<?php echo status_badge('pending'); ?>
```

## 📊 Dashboard Features

### Charts

The dashboard includes three interactive charts:

1. **Sales vs Purchases (Line Chart)** - Shows trend over last 6 months
2. **Product Distribution (Donut Chart)** - Shows revenue by product type
3. **Monthly Revenue Analysis (Bar Chart)** - Shows revenue, collected, and pending amounts

All charts use Chart.js with custom color schemes and tooltips.

### Statistics

- **Total Sales (YTD)** - Year-to-date sales with growth percentage
- **Total Customers** - Active customer count
- **Outstanding** - Total pending collections
- **Today's Sales** - Current day revenue

### Recent Activities

- **Recent Invoices** - Last 5 invoices with status
- **Pending Payments** - Invoices awaiting payment
- **Top Customers** - Ranked by purchase amount

## 🚀 Next Steps

To complete the Insurance Management System, follow these steps:

### 1. Complete CodeIgniter Setup

Create necessary config files:
- `application/config/config.php`
- `application/config/database.php`
- `application/config/autoload.php`
- `application/config/routes.php`

### 2. Create Core Components

**Authentication:**
- `application/controllers/Login.php`
- `application/views/auth/login.php`
- `application/libraries/Auth.php`

**Base Classes:**
- `application/core/MY_Controller.php` (for shared functionality)
- `application/libraries/Pdf.php` (for PDF generation)

### 3. Implement Masters Modules

Create controllers, models, and views for:
- **Customers** - Full CRUD with KYC management
- **Suppliers** - Supplier management
- **Brokers** - Broker management with commission setup
- **Agents** - Agent/Salesman management
- **Products** - Insurance products/policies
- **Accounts** - Chart of Accounts with subgroups

### 4. Implement Transaction Modules

- **Sales** - Invoice management with payment tracking
- **Quotations** - Quotation with convert-to-sale feature
- **Purchases** - Purchase management
- **Receipts** - Payment receipt with allocation
- **Payments** - Payment vouchers
- **Journals** - Journal entry system

### 5. Implement Reports

Create report controllers and views for:
- Books of Accounts
- Financial Reports
- Sales Reports
- Commission Reports
- Customer Reports

### 6. Add Advanced Features

- **Payment Allocation System** - Allocate receipts to invoices
- **Commission Calculation** - Auto-calculate broker/agent commissions
- **VAT Management** - VAT calculation and returns
- **Multi-currency Support** - Handle multiple currencies
- **Email Notifications** - Send invoices, receipts via email
- **PDF Generation** - Print invoices, reports
- **Excel Export** - Export reports to Excel

### 7. Security & Performance

- Implement CSRF protection
- Add SQL injection prevention
- Set up user permissions
- Enable caching
- Optimize database queries

## 📝 Code Examples

### Creating a New Controller

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('ui');
        $this->load->model('Customer_model');

        // Check authentication
        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
    }

    public function index() {
        $data = [
            'page_title' => 'Customers',
            'breadcrumbs' => [
                ['title' => 'Masters', 'url' => '#'],
                ['title' => 'Customers']
            ],
            'main_content' => 'customers/list',
            'customers' => $this->Customer_model->get_all()
        ];

        $this->load->view('templates/modern_layout', $data);
    }

    public function add() {
        if ($this->input->post()) {
            $data = $this->input->post();

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
                ['title' => 'Masters', 'url' => '#'],
                ['title' => 'Customers', 'url' => base_url('customers')],
                ['title' => 'Add New']
            ],
            'main_content' => 'customers/form'
        ];

        $this->load->view('templates/modern_layout', $data);
    }
}
```

### Creating a Model

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model {

    protected $table = 'customer_information';
    protected $primary_key = 'customer_id';

    public function get_all($filters = []) {
        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($filters['search'])) {
            $this->db->like('customer_name', $filters['search']);
        }

        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }

        $this->db->order_by('customer_id', 'DESC');

        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where($this->table, [
            $this->primary_key => $id
        ])->row();
    }

    public function insert($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update(
            $this->table,
            $data,
            [$this->primary_key => $id]
        );
    }

    public function delete($id) {
        return $this->db->delete($this->table, [
            $this->primary_key => $id
        ]);
    }
}
```

## 🎯 Testing the Setup

### 1. Check Tailwind CSS Build

```bash
# The output.css file should exist and have content
ls -lh assets/css/output.css

# Should show something like: -rw-r--r-- 1 user user 250K Nov 11 21:55 output.css
```

### 2. Test Dashboard Access

```
http://localhost/insurance-erp/dashboard
```

Should display:
- ✅ Modern sidebar navigation
- ✅ Statistics cards with data
- ✅ Charts displaying
- ✅ Recent activities lists
- ✅ Responsive layout

### 3. Check Console

Open browser developer tools (F12) and check:
- ✅ No JavaScript errors
- ✅ All CSS loaded
- ✅ Alpine.js initialized
- ✅ Charts rendering

## 📞 Support & Resources

### Documentation

- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Alpine.js Docs](https://alpinejs.dev)
- [Chart.js Docs](https://www.chartjs.org/docs)
- [CodeIgniter 3 Docs](https://codeigniter.com/userguide3)

### Troubleshooting

**CSS not loading?**
```bash
npm run build
```

**Charts not displaying?**
- Check browser console for errors
- Ensure Chart.js CDN is loaded
- Verify canvas elements have IDs

**Database errors?**
- Check `application/config/database.php` settings
- Ensure database user has proper permissions
- Verify database name matches configuration

## 📄 License

Proprietary - NA-FIX ERP Solutions
Version: 2.0.0
Copyright © 2025 NA-FIX ERP Solutions. All rights reserved.

---

**Note:** This is a work in progress. The UI framework and dashboard are complete. Continue with the next steps to implement the full system.

For questions or support, contact: support@nafixerp.com
