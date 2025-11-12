-- =====================================================
-- Additional Essential Data for NA-FIX ERP
-- Run this AFTER importing the main database
-- =====================================================

USE `cybor432_erpnew`;

-- =====================================================
-- 1. ADD DEFAULT FINANCIAL YEAR
-- =====================================================
INSERT INTO `financial_years` (`year_label`, `start_date`, `end_date`, `status`) VALUES
('FY 2025', '2025-01-01', '2025-12-31', 'open'),
('FY 2024', '2024-01-01', '2024-12-31', 'closed');

-- =====================================================
-- 2. ADD MORE DETAILED CHART OF ACCOUNTS
-- =====================================================

-- Revenue Accounts
INSERT INTO `accounts` (`accode`, `name`, `actype1`, `actype2`, `reserve`, `grcode`, `opbal`, `curbal`, `control`, `blocked`) VALUES
('PRMINC-MOT', 'Motor Insurance Premium', 'R', NULL, 'N', 'DIRINC', 0.00, 0.00, 1, 'N'),
('PRMINC-HLT', 'Health Insurance Premium', 'R', NULL, 'N', 'DIRINC', 0.00, 0.00, 1, 'N'),
('PRMINC-LIF', 'Life Insurance Premium', 'R', NULL, 'N', 'DIRINC', 0.00, 0.00, 1, 'N'),
('PRMINC-TRV', 'Travel Insurance Premium', 'R', NULL, 'N', 'DIRINC', 0.00, 0.00, 1, 'N'),
('INTINC', 'Interest Income', 'R', NULL, 'N', 'INDINC', 0.00, 0.00, 1, 'N'),
('OTHERINC', 'Other Income', 'R', NULL, 'N', 'INDINC', 0.00, 0.00, 1, 'N');

-- Expense Accounts
INSERT INTO `accounts` (`accode`, `name`, `actype1`, `actype2`, `reserve`, `grcode`, `opbal`, `curbal`, `control`, `blocked`) VALUES
('CLMEXP-MOT', 'Motor Claims Expense', 'E', NULL, 'N', 'DIREXP', 0.00, 0.00, 1, 'N'),
('CLMEXP-HLT', 'Health Claims Expense', 'E', NULL, 'N', 'DIREXP', 0.00, 0.00, 1, 'N'),
('CLMEXP-LIF', 'Life Claims Expense', 'E', NULL, 'N', 'DIREXP', 0.00, 0.00, 1, 'N'),
('ADVEXP', 'Advertising Expense', 'E', NULL, 'N', 'INDEXP', 0.00, 0.00, 1, 'N'),
('OFFEXP', 'Office Expense', 'E', NULL, 'N', 'INDEXP', 0.00, 0.00, 1, 'N'),
('TELEXP', 'Telephone Expense', 'E', NULL, 'N', 'INDEXP', 0.00, 0.00, 1, 'N'),
('TRAVEXP', 'Travel Expense', 'E', NULL, 'N', 'INDEXP', 0.00, 0.00, 1, 'N'),
('DEPEXP', 'Depreciation Expense', 'E', NULL, 'N', 'INDEXP', 0.00, 0.00, 1, 'N'),
('INTEXP', 'Interest Expense', 'E', NULL, 'N', 'INDEXP', 0.00, 0.00, 1, 'N');

-- Asset Accounts
INSERT INTO `accounts` (`accode`, `name`, `actype1`, `actype2`, `reserve`, `grcode`, `opbal`, `curbal`, `control`, `blocked`) VALUES
('FURNI', 'Furniture & Fixtures', 'A', NULL, 'N', 'FIXA', 0.00, 0.00, 1, 'N'),
('VEHICLE', 'Vehicles', 'A', NULL, 'N', 'FIXA', 0.00, 0.00, 1, 'N'),
('COMPUTER', 'Computer & Equipment', 'A', NULL, 'N', 'FIXA', 0.00, 0.00, 1, 'N'),
('PREPAID', 'Prepaid Expenses', 'A', NULL, 'N', 'CURA', 0.00, 0.00, 1, 'N'),
('SECURITY', 'Security Deposits', 'A', NULL, 'N', 'CURA', 0.00, 0.00, 1, 'N');

-- Liability Accounts
INSERT INTO `accounts` (`accode`, `name`, `actype1`, `actype2`, `reserve`, `grcode`, `opbal`, `curbal`, `control`, `blocked`) VALUES
('SALARPAY', 'Salary Payable', 'L', NULL, 'N', 'CURL', 0.00, 0.00, 1, 'N'),
('RENTPAY', 'Rent Payable', 'L', NULL, 'N', 'CURL', 0.00, 0.00, 1, 'N'),
('LOAN', 'Bank Loan', 'L', NULL, 'N', 'LONGL', 0.00, 0.00, 1, 'N');

-- =====================================================
-- 3. ADD COMPREHENSIVE ROLE PERMISSIONS
-- =====================================================

-- Manager Permissions
INSERT INTO `role_permissions` (`role_id`, `module_name`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_approve`, `can_export`) VALUES
(2, 'Customers', 1, 1, 1, 0, 1, 1),
(2, 'Policies', 1, 1, 1, 0, 1, 1),
(2, 'Claims', 1, 1, 1, 0, 1, 1),
(2, 'Sales', 1, 1, 1, 0, 1, 1),
(2, 'Receipts', 1, 1, 1, 0, 1, 1),
(2, 'Payments', 1, 1, 1, 0, 1, 1),
(2, 'Reports', 1, 0, 0, 0, 0, 1);

-- Accountant Permissions
INSERT INTO `role_permissions` (`role_id`, `module_name`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_approve`, `can_export`) VALUES
(3, 'Customers', 1, 0, 0, 0, 0, 1),
(3, 'Sales', 1, 0, 1, 0, 0, 1),
(3, 'Receipts', 1, 1, 1, 0, 0, 1),
(3, 'Payments', 1, 1, 1, 0, 0, 1),
(3, 'Accounting', 1, 1, 1, 1, 0, 1),
(3, 'Reports', 1, 0, 0, 0, 0, 1);

-- Underwriter Permissions
INSERT INTO `role_permissions` (`role_id`, `module_name`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_approve`, `can_export`) VALUES
(4, 'Customers', 1, 1, 1, 0, 0, 1),
(4, 'Policies', 1, 1, 1, 0, 1, 1),
(4, 'Reports', 1, 0, 0, 0, 0, 1);

-- Claims Officer Permissions
INSERT INTO `role_permissions` (`role_id`, `module_name`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_approve`, `can_export`) VALUES
(5, 'Customers', 1, 0, 0, 0, 0, 0),
(5, 'Policies', 1, 0, 0, 0, 0, 1),
(5, 'Claims', 1, 1, 1, 0, 1, 1),
(5, 'Reports', 1, 0, 0, 0, 0, 1);

-- =====================================================
-- 4. ADD ADDITIONAL SYSTEM SETTINGS
-- =====================================================

INSERT INTO `settings` (`code`, `cvalue`, `description`, `category`) VALUES
('auto_backup', 'yes', 'Enable automatic database backup', 'backup'),
('backup_frequency', 'daily', 'Backup frequency (daily/weekly)', 'backup'),
('invoice_auto_number', 'yes', 'Auto-generate invoice numbers', 'numbering'),
('policy_auto_number', 'yes', 'Auto-generate policy numbers', 'numbering'),
('email_notification', 'yes', 'Enable email notifications', 'email'),
('sms_notification', 'no', 'Enable SMS notifications', 'sms'),
('multi_branch', 'no', 'Enable multi-branch support', 'general'),
('multi_currency', 'yes', 'Enable multi-currency', 'financial'),
('decimal_precision', '2', 'Decimal places for amounts', 'financial'),
('low_stock_alert', 'yes', 'Enable low stock alerts', 'inventory'),
('commission_auto_calculate', 'yes', 'Auto-calculate commissions', 'commission');

-- =====================================================
-- 5. ADD ADDITIONAL WEB SETTINGS
-- =====================================================

INSERT INTO `web_setting` (`setting_name`, `setting_value`, `type`, `category`, `description`) VALUES
('maintenance_mode', '0', 'boolean', 'general', 'Enable maintenance mode'),
('registration_enabled', '0', 'boolean', 'security', 'Allow self registration'),
('password_min_length', '6', 'number', 'security', 'Minimum password length'),
('session_timeout', '3600', 'number', 'security', 'Session timeout in seconds'),
('max_login_attempts', '5', 'number', 'security', 'Maximum login attempts'),
('enable_2fa', '0', 'boolean', 'security', 'Enable two-factor authentication'),
('default_rows_per_page', '25', 'number', 'display', 'Default rows per page in tables'),
('enable_dark_mode', '1', 'boolean', 'appearance', 'Enable dark mode option'),
('auto_logout', '1800', 'number', 'security', 'Auto logout after inactivity (seconds)'),
('enable_audit_log', '1', 'boolean', 'security', 'Enable audit logging');

-- =====================================================
-- 6. ADD SAMPLE CATEGORIES & UNITS
-- =====================================================

INSERT INTO `categories` (`code`, `name`, `description`) VALUES
('INS-MOT', 'Motor Insurance', 'Vehicle insurance products'),
('INS-HLT', 'Health Insurance', 'Medical insurance products'),
('INS-LIF', 'Life Insurance', 'Life insurance products'),
('INS-TRV', 'Travel Insurance', 'Travel insurance products'),
('INS-PRO', 'Property Insurance', 'Property insurance products'),
('INS-MAR', 'Marine Insurance', 'Marine & cargo insurance'),
('INS-LIA', 'Liability Insurance', 'Liability insurance products');

INSERT INTO `units` (`code`, `name`, `symbol`, `description`) VALUES
('POL', 'Policy', 'Pol', 'Insurance policy'),
('PCS', 'Pieces', 'Pcs', 'Pieces'),
('BOX', 'Box', 'Box', 'Box'),
('SET', 'Set', 'Set', 'Set'),
('PKG', 'Package', 'Pkg', 'Package'),
('MTR', 'Meter', 'm', 'Meter'),
('KG', 'Kilogram', 'kg', 'Kilogram'),
('LTR', 'Liter', 'L', 'Liter');

-- =====================================================
-- 7. ADD CUSTOMER GROUPS
-- =====================================================

INSERT INTO `customer_groups` (`code`, `name`, `description`, `discount_percentage`, `credit_limit`, `credit_days`, `active`) VALUES
('VIP', 'VIP Customers', 'High value customers', 10.00, 100000.00, 60, 1),
('CORP', 'Corporate', 'Corporate clients', 5.00, 500000.00, 45, 1),
('REG', 'Regular', 'Regular customers', 0.00, 50000.00, 30, 1),
('WALK', 'Walk-in', 'Walk-in customers', 0.00, 0.00, 0, 1);

-- =====================================================
-- 8. ADD DEPARTMENTS & DESIGNATIONS
-- =====================================================

INSERT INTO `departments` (`code`, `name`, `description`) VALUES
('SALES', 'Sales Department', 'Sales and marketing'),
('CLAIMS', 'Claims Department', 'Claims processing'),
('UW', 'Underwriting', 'Underwriting department'),
('FIN', 'Finance', 'Finance and accounts'),
('IT', 'IT Department', 'Information technology'),
('HR', 'Human Resources', 'HR department');

INSERT INTO `designations` (`code`, `name`, `description`) VALUES
('MGR', 'Manager', 'Manager'),
('EXEC', 'Executive', 'Executive'),
('ASST', 'Assistant', 'Assistant'),
('SR-MGR', 'Senior Manager', 'Senior Manager'),
('ANALYST', 'Analyst', 'Analyst'),
('CLERK', 'Clerk', 'Clerk');

-- =====================================================
-- 9. ADD MORE SAMPLE USERS (FOR TESTING)
-- =====================================================

INSERT INTO `users` (`code`, `username`, `email`, `password`, `first_name`, `last_name`, `role_id`, `usertype`, `status`) VALUES
('USR-2025-0002', 'manager', 'manager@example.com', '$2y$10$hIB4Etgi98K5TemqY5i/AuQ2QruojMpbqMyPsUDr9ePs0Fu9Dzq7K', 'John', 'Manager', 2, 'USER', 'active'),
('USR-2025-0003', 'accountant', 'accountant@example.com', '$2y$10$hIB4Etgi98K5TemqY5i/AuQ2QruojMpbqMyPsUDr9ePs0Fu9Dzq7K', 'Sarah', 'Accountant', 3, 'USER', 'active'),
('USR-2025-0004', 'underwriter', 'underwriter@example.com', '$2y$10$hIB4Etgi98K5TemqY5i/AuQ2QruojMpbqMyPsUDr9ePs0Fu9Dzq7K', 'Mike', 'Underwriter', 4, 'USER', 'active'),
('USR-2025-0005', 'claims', 'claims@example.com', '$2y$10$hIB4Etgi98K5TemqY5i/AuQ2QruojMpbqMyPsUDr9ePs0Fu9Dzq7K', 'Linda', 'Claims', 5, 'USER', 'active');

-- Note: All test users have password: admin123

-- =====================================================
-- 10. ADD OPENING BALANCES FOR ACCOUNTS
-- =====================================================

INSERT INTO `opening_balance` (`account_code`, `opening_balance`, `opening_date`) VALUES
('CASH', 50000.00, '2025-01-01'),
('BANK', 200000.00, '2025-01-01'),
('CAPITAL', -250000.00, '2025-01-01');

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Additional data imported successfully!' AS Status,
       'Your system now has:' AS Message,
       '- Default fiscal year 2025' AS Item1,
       '- Comprehensive chart of accounts' AS Item2,
       '- Role permissions for all roles' AS Item3,
       '- Additional system settings' AS Item4,
       '- Sample categories and units' AS Item5,
       '- Customer groups' AS Item6,
       '- Departments and designations' AS Item7,
       '- 5 test users (admin, manager, accountant, underwriter, claims)' AS Item8,
       '- Opening balances' AS Item9,
       'All users password: admin123' AS Note;
