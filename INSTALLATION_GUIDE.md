# NA-FIX ERP System - Complete Installation Guide

## 🚀 System Features
- ✅ Complete Login & Authentication System
- ✅ Role-Based Access Control (Admin, Manager, Accountant, User)
- ✅ Double-Entry Accounting System
- ✅ Complete ERP Modules (Sales, Purchase, Inventory, Accounting)
- ✅ Insurance Management (Policies, Claims, Commissions)
- ✅ Multi-Currency Support
- ✅ VAT/Tax Management
- ✅ Comprehensive Reporting
- ✅ Audit Logs

## 📋 Prerequisites
- PHP 7.2 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- Apache/Nginx Web Server
- Composer (optional)

## 🔧 Installation Steps

### Step 1: Prepare Database

1. **Create MySQL Database:**
   ```sql
   CREATE DATABASE cybor432_erpnew CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Database Structure:**

   Your provided phpMyAdmin SQL export contains the complete database structure. Save it as a file and import it:

   **Option A: Using Command Line:**
   ```bash
   mysql -u root -p cybor432_erpnew < your_sql_file.sql
   ```

   **Option B: Using phpMyAdmin:**
   - Open phpMyAdmin
   - Select database `cybor432_erpnew`
   - Click "Import" tab
   - Choose your SQL file
   - Click "Go"

   The database includes:
   - 100+ tables for complete ERP functionality
   - Default admin user (username: admin, password: admin123)
   - Sample data for testing
   - All necessary indexes and foreign keys

### Step 2: Configure Database Connection

Edit `application/config/database.php`:

```php
$db['default'] = array(
    'dsn'	=> '',
    'hostname' => 'localhost',
    'username' => 'root',          // Your MySQL username
    'password' => '',              // Your MySQL password
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

### Step 3: Configure Base URL

Edit `application/config/config.php`:

```php
$config['base_url'] = 'http://localhost/ecmall/';  // Change to your URL
```

### Step 4: Set Permissions (Linux/Mac)

```bash
chmod -R 755 application/cache
chmod -R 755 application/logs
chmod -R 755 assets/uploads
```

### Step 5: Access the System

1. **Navigate to:**
   ```
   http://localhost/ecmall/
   ```

2. **Default Login Credentials:**
   - Username: `admin`
   - Password: `admin123`

3. **You will be redirected to the dashboard after successful login**

## 🔐 Security Configuration

### Change Default Password

After first login:
1. Go to User Management
2. Select admin user
3. Change password to a strong one

### Session Configuration

Edit `application/config/config.php`:

```php
$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 7200;  // 2 hours
$config['sess_save_path'] = NULL;
$config['sess_match_ip'] = FALSE;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = FALSE;
```

## 📊 Database Structure Overview

### Core Tables:
- **users** - User accounts and authentication
- **roles** - Role definitions
- **role_permissions** - Permission management
- **audit_logs** - System activity tracking

### Accounting Tables:
- **accounts** - Chart of accounts
- **account_groups** - Account grouping
- **daybook** - Journal entries
- **ledger** - Account ledger
- **journals** - Journal vouchers

### Sales & Purchase:
- **invoice** - Sales invoices
- **invoice_details** - Invoice line items
- **product_purchase** - Purchase orders
- **product_purchase_details** - Purchase line items
- **quotations** - Sales quotations
- **receipts** - Customer receipts
- **payments** - Supplier payments

### Inventory:
- **product_information** - Product master
- **customer_information** - Customer master
- **supplier_information** - Supplier master
- **categories** - Product categories
- **units** - Unit of measurements

### Insurance Management:
- **policies** - Insurance policies
- **policy_types** - Policy type definitions
- **claims** - Insurance claims
- **claim_types** - Claim categories
- **commissions** - Agent/Broker commissions
- **brokers** - Broker information
- **agents** - Agent information

### Financial:
- **currencies** - Multi-currency support
- **exchange_rate_history** - Exchange rates
- **vat_returns** - VAT filing
- **financial_years** - Fiscal year management
- **bank_accounts** - Bank account management

## 🎯 Features by Role

### Admin (Full Access)
- User & Role Management
- System Settings
- All Financial Operations
- Reports & Analytics
- Data Backup/Restore

### Manager
- View All Reports
- Approve Transactions
- Manage Branch Operations
- Customer/Supplier Management

### Accountant
- Accounting Entries
- Financial Reports
- Bank Reconciliation
- VAT Returns

### User (Regular)
- View Own Data
- Create Invoices/Quotations
- Basic Reporting

## 🔄 Daily Operations

### Recording a Sale:
1. Sales → Create Invoice
2. Select Customer
3. Add Products/Services
4. VAT calculated automatically
5. Save Invoice
6. Print/Email to Customer

### Recording Payment:
1. Receipts → New Receipt
2. Select Customer/Invoice
3. Enter Amount & Payment Method
4. System posts to accounting automatically

### Viewing Reports:
1. Reports → Select Report Type
2. Set Date Range
3. Apply Filters
4. Export to PDF/Excel

## 📈 Accounting Features

### Double-Entry System:
- Every transaction creates balanced entries
- Automatic posting to General Ledger
- Real-time account balances
- Audit trail for all transactions

### Financial Reports:
- Trial Balance
- Profit & Loss Statement
- Balance Sheet
- Cash Flow Statement
- Ledger Reports
- Day Book
- Bank Book

## 🛠️ Troubleshooting

### Login Issues:
```sql
-- Reset admin password to 'admin123'
UPDATE users
SET password = '$2y$10$hIB4Etgi98K5TemqY5i/AuQ2QruojMpbqMyPsUDr9ePs0Fu9Dzq7K'
WHERE username = 'admin';
```

### Permission Errors:
```bash
# Linux/Mac
chmod -R 755 application/
chmod -R 777 application/cache
chmod -R 777 application/logs
```

### Database Connection Errors:
- Check MySQL is running
- Verify database credentials
- Ensure database exists
- Check PHP mysqli extension is enabled

### Blank Page / PHP Errors:
```php
// Enable error display in index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 Support

For issues or questions:
- Check error logs: `application/logs/`
- Review CodeIgniter documentation
- Check PHP error logs

## 🔄 Backup Recommendations

### Daily Backups:
```bash
# Database backup
mysqldump -u root -p cybor432_erpnew > backup_$(date +%Y%m%d).sql

# Files backup
tar -czf files_backup_$(date +%Y%m%d).tar.gz application/ assets/
```

### Automated Backups:
Use the built-in backup module:
1. Settings → Database Backup
2. Configure automatic backups
3. Set backup frequency
4. Specify backup location

## 📝 Important Notes

1. **Always backup before updates**
2. **Change default passwords immediately**
3. **Keep system updated**
4. **Monitor audit logs regularly**
5. **Test in development before production**
6. **Use HTTPS in production**
7. **Regular database optimization**

## ✅ Post-Installation Checklist

- [ ] Database imported successfully
- [ ] Admin login works
- [ ] Change default password
- [ ] Configure company information
- [ ] Set up users and roles
- [ ] Test creating invoice
- [ ] Test creating payment
- [ ] Verify accounting entries
- [ ] Check reports generation
- [ ] Configure backup schedule
- [ ] Set up email configuration
- [ ] Test PDF generation

## 🎉 You're Ready!

Your NA-FIX ERP System is now fully operational!

Access the system at: **http://localhost/ecmall/**

Login with: **admin / admin123**

Enjoy your new ERP system! 🚀
