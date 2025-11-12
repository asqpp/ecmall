-- =====================================================
-- Insurance Management System v2.0 - Sample Data
-- Database: cybor432_erpnew
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- =====================================================
-- 1. SYSTEM ACCOUNTS (Chart of Accounts)
-- =====================================================
INSERT INTO `accounts` (`account_code`, `account_name`, `account_type`, `description`, `is_system`, `status`) VALUES
('CASH', 'Cash on Hand', 'asset', 'Cash available at office', 1, 1),
('BANK', 'Bank Account', 'asset', 'Main bank account', 1, 1),
('PDCREC', 'PDC Receivable', 'asset', 'Post-dated cheques received from customers', 1, 1),
('PDCPAY', 'PDC Payable', 'liability', 'Post-dated cheques issued to suppliers', 1, 1),
('SALES', 'Sales Revenue', 'income', 'Revenue from sales', 1, 1),
('PURCHASE', 'Purchase Cost', 'expense', 'Cost of purchases', 1, 1),
('DISCALL', 'Discount Allowed', 'expense', 'Discounts given to customers', 1, 1),
('DISCREC', 'Discount Received', 'income', 'Discounts received from suppliers', 1, 1),
('VAT-OUT', 'VAT Output', 'liability', 'VAT collected from customers', 1, 1),
('VAT-IN', 'VAT Input', 'asset', 'VAT paid to suppliers', 1, 1),
('CAPITAL', 'Capital', 'equity', 'Owner capital', 1, 1),
('SALARY', 'Salary Expense', 'expense', 'Employee salaries', 0, 1),
('RENT', 'Rent Expense', 'expense', 'Office rent', 0, 1),
('UTILITIES', 'Utilities Expense', 'expense', 'Electricity, water, internet', 0, 1),
('COMMISSION', 'Commission Expense', 'expense', 'Broker and agent commissions', 1, 1);

-- =====================================================
-- 2. CATEGORIES
-- =====================================================
INSERT INTO `category_list` (`category_name`, `description`, `status`) VALUES
('Motor Insurance', 'Vehicle insurance products', 1),
('Health Insurance', 'Medical and health insurance products', 1),
('Property Insurance', 'Home and property insurance', 1),
('Life Insurance', 'Life and term insurance products', 1),
('Travel Insurance', 'Travel and trip insurance', 1),
('Business Insurance', 'Commercial and business insurance', 1);

-- =====================================================
-- 3. UNITS
-- =====================================================
INSERT INTO `unit` (`unit_name`, `unit_short_name`, `status`) VALUES
('Policy', 'pol', 1),
('Unit', 'unit', 1),
('Piece', 'pc', 1),
('Set', 'set', 1),
('Package', 'pkg', 1);

-- =====================================================
-- 4. CUSTOMERS
-- =====================================================
INSERT INTO `customer_information` (`customer_name`, `customer_mobile`, `customer_email`, `customer_address_1`, `city`, `state`, `country`, `credit_limit`, `status`) VALUES
('Ahmed Mohammed Ali', '+971-50-1234567', 'ahmed.ali@email.com', 'Al Nahda Street, Building 123', 'Dubai', 'Dubai', 'UAE', 50000.00, 1),
('Fatima Hassan', '+971-55-2345678', 'fatima.hassan@email.com', 'Deira City Center, Office 456', 'Dubai', 'Dubai', 'UAE', 30000.00, 1),
('Mohammed Abdullah', '+971-50-3456789', 'mohammed.abdullah@email.com', 'Sheikh Zayed Road, Tower 789', 'Dubai', 'Dubai', 'UAE', 75000.00, 1),
('Sara Ibrahim', '+971-55-4567890', 'sara.ibrahim@email.com', 'Jumeirah Beach Road, Villa 12', 'Dubai', 'Dubai', 'UAE', 40000.00, 1),
('Omar Khalid', '+971-50-5678901', 'omar.khalid@email.com', 'Business Bay, Floor 15', 'Dubai', 'Dubai', 'UAE', 60000.00, 1),
('Aisha Rashid', '+971-55-6789012', 'aisha.rashid@email.com', 'Marina Walk, Apartment 234', 'Dubai', 'Dubai', 'UAE', 35000.00, 1),
('Khalid Salem', '+971-50-7890123', 'khalid.salem@email.com', 'Al Barsha, Street 45', 'Dubai', 'Dubai', 'UAE', 45000.00, 1),
('Mariam Youssef', '+971-55-8901234', 'mariam.youssef@email.com', 'JBR, Block A', 'Dubai', 'Dubai', 'UAE', 55000.00, 1);

-- =====================================================
-- 5. SUPPLIERS
-- =====================================================
INSERT INTO `supplier_information` (`supplier_name`, `supplier_mobile`, `supplier_email`, `supplier_address_1`, `city`, `state`, `country`, `credit_limit`, `status`) VALUES
('Dubai Insurance Company LLC', '+971-4-1234567', 'info@dubaiinsurance.ae', 'Dubai World Trade Center', 'Dubai', 'Dubai', 'UAE', 500000.00, 1),
('Emirates Insurance Group', '+971-4-2345678', 'sales@emiratesinsurance.ae', 'DIFC Gate Avenue', 'Dubai', 'Dubai', 'UAE', 750000.00, 1),
('Abu Dhabi National Insurance', '+971-2-3456789', 'contact@adnic.ae', 'Al Khaleej Al Arabi Street', 'Abu Dhabi', 'Abu Dhabi', 'UAE', 1000000.00, 1),
('Orient Insurance PJSC', '+971-4-4567890', 'info@orientinsurance.ae', 'Trade Center Road', 'Dubai', 'Dubai', 'UAE', 600000.00, 1),
('Oman Insurance Company', '+971-4-5678901', 'sales@oman-insurance.ae', 'Bur Dubai', 'Dubai', 'Dubai', 'UAE', 800000.00, 1);

-- =====================================================
-- 6. BROKERS
-- =====================================================
INSERT INTO `broker` (`broker_code`, `broker_name`, `contact_person`, `mobile`, `email`, `address`, `commission_rate`, `status`) VALUES
('BRK-0001', 'Gulf Insurance Brokers', 'Ali Hassan', '+971-50-1111111', 'ali@gulfbrokers.ae', 'Sheikh Zayed Road, Dubai', 5.00, 1),
('BRK-0002', 'Emirates Brokerage House', 'Fatima Ahmed', '+971-55-2222222', 'fatima@emiratesbroker.ae', 'DIFC, Dubai', 4.50, 1),
('BRK-0003', 'Middle East Insurance Brokers', 'Mohammed Said', '+971-50-3333333', 'mohammed@mebrokers.ae', 'Business Bay, Dubai', 5.50, 1),
('BRK-0004', 'Dubai Insurance Consultants', 'Sara Khalid', '+971-55-4444444', 'sara@dubaiinsuranceconsult.ae', 'Deira, Dubai', 4.00, 1);

-- =====================================================
-- 7. AGENTS
-- =====================================================
INSERT INTO `agent` (`agent_code`, `agent_name`, `contact_person`, `mobile`, `email`, `address`, `commission_rate`, `status`) VALUES
('AGT-0001', 'Ahmed Insurance Services', 'Ahmed Ali', '+971-50-5555555', 'ahmed@ahmedinsurance.ae', 'Al Nahda, Dubai', 3.00, 1),
('AGT-0002', 'Khalid Agency', 'Khalid Omar', '+971-55-6666666', 'khalid@khalidagency.ae', 'Sharjah', 3.50, 1),
('AGT-0003', 'Aisha Insurance Agency', 'Aisha Mohammed', '+971-50-7777777', 'aisha@aishaagency.ae', 'Ajman', 3.25, 1),
('AGT-0004', 'Omar Consultants', 'Omar Hassan', '+971-55-8888888', 'omar@omarconsultants.ae', 'Ras Al Khaimah', 2.75, 1),
('AGT-0005', 'Mariam Insurance Hub', 'Mariam Saeed', '+971-50-9999999', 'mariam@mariamhub.ae', 'Fujairah', 3.00, 1);

-- =====================================================
-- 8. PRODUCTS (Insurance Policies)
-- =====================================================
INSERT INTO `product_information` (`product_name`, `product_model`, `category_id`, `unit_id`, `price`, `quantity`, `min_stock`, `description`, `status`) VALUES
('Comprehensive Motor Insurance', 'CMI-2024', 1, 1, 2500.00, 50, 10, 'Full coverage motor insurance policy', 1),
('Third Party Motor Insurance', 'TPL-2024', 1, 1, 850.00, 100, 20, 'Third party liability motor insurance', 1),
('Individual Health Insurance', 'IHI-2024', 2, 1, 5000.00, 75, 15, 'Individual health insurance coverage', 1),
('Family Health Insurance', 'FHI-2024', 2, 1, 12000.00, 40, 10, 'Family floater health insurance', 1),
('Home Insurance - Basic', 'HBI-2024', 3, 1, 1500.00, 60, 12, 'Basic home insurance coverage', 1),
('Home Insurance - Comprehensive', 'HCI-2024', 3, 1, 3500.00, 35, 8, 'Comprehensive home insurance', 1),
('Term Life Insurance', 'TLI-2024', 4, 1, 8000.00, 30, 5, 'Term life insurance policy', 1),
('Whole Life Insurance', 'WLI-2024', 4, 1, 15000.00, 20, 5, 'Whole life insurance coverage', 1),
('Travel Insurance - Single Trip', 'TST-2024', 5, 1, 250.00, 200, 50, 'Single trip travel insurance', 1),
('Travel Insurance - Annual', 'TAM-2024', 5, 1, 1200.00, 80, 20, 'Annual multi-trip travel insurance', 1),
('Business Liability Insurance', 'BLI-2024', 6, 1, 10000.00, 25, 5, 'Commercial general liability', 1),
('Professional Indemnity', 'PIN-2024', 6, 1, 7500.00, 30, 5, 'Professional indemnity insurance', 1);

-- =====================================================
-- 9. SAMPLE INVOICES
-- =====================================================
INSERT INTO `invoice` (`invoice`, `customer_id`, `date`, `total`, `vat`, `grand_total`, `payment_status`, `broker_id`, `agent_id`, `sales_by`) VALUES
('INV-2024-0001', 1, '2024-01-15', 5000.00, 250.00, 5250.00, 'paid', 1, 1, 1),
('INV-2024-0002', 2, '2024-01-18', 12000.00, 600.00, 12600.00, 'partial', 2, 2, 1),
('INV-2024-0003', 3, '2024-01-22', 3500.00, 175.00, 3675.00, 'unpaid', 1, 3, 1),
('INV-2024-0004', 4, '2024-01-25', 8000.00, 400.00, 8400.00, 'paid', 3, 1, 1),
('INV-2024-0005', 5, '2024-02-01', 2500.00, 125.00, 2625.00, 'partial', 2, 4, 1),
('INV-2024-0006', 6, '2024-02-05', 15000.00, 750.00, 15750.00, 'unpaid', 4, 2, 1),
('INV-2024-0007', 7, '2024-02-10', 1500.00, 75.00, 1575.00, 'paid', 1, 5, 1),
('INV-2024-0008', 8, '2024-02-15', 10000.00, 500.00, 10500.00, 'partial', 3, 3, 1);

-- =====================================================
-- 10. INVOICE DETAILS
-- =====================================================
INSERT INTO `invoice_details` (`invoice_id`, `product_id`, `quantity`, `rate`, `total_price`) VALUES
(1, 3, 1, 5000.00, 5000.00),
(2, 4, 1, 12000.00, 12000.00),
(3, 6, 1, 3500.00, 3500.00),
(4, 7, 1, 8000.00, 8000.00),
(5, 1, 1, 2500.00, 2500.00),
(6, 8, 1, 15000.00, 15000.00),
(7, 5, 1, 1500.00, 1500.00),
(8, 11, 1, 10000.00, 10000.00);

-- =====================================================
-- 11. SAMPLE PURCHASES
-- =====================================================
INSERT INTO `product_purchase` (`chalan_no`, `supplier_id`, `purchase_date`, `total_amount`, `vat`, `grand_total_amount`, `payment_status`) VALUES
('PUR-2024-0001', 1, '2024-01-10', 50000.00, 2500.00, 52500.00, 'paid'),
('PUR-2024-0002', 2, '2024-01-12', 75000.00, 3750.00, 78750.00, 'partial'),
('PUR-2024-0003', 3, '2024-01-20', 100000.00, 5000.00, 105000.00, 'unpaid'),
('PUR-2024-0004', 4, '2024-02-05', 60000.00, 3000.00, 63000.00, 'paid'),
('PUR-2024-0005', 5, '2024-02-12', 80000.00, 4000.00, 84000.00, 'partial');

-- =====================================================
-- 12. PURCHASE DETAILS
-- =====================================================
INSERT INTO `product_purchase_details` (`purchase_id`, `product_id`, `quantity`, `rate`, `total_amount`) VALUES
(1, 1, 20, 2000.00, 40000.00),
(1, 2, 10, 750.00, 7500.00),
(1, 9, 10, 200.00, 2000.00),
(2, 3, 15, 4500.00, 67500.00),
(2, 5, 5, 1500.00, 7500.00),
(3, 4, 10, 11000.00, 110000.00),
(4, 6, 10, 3200.00, 32000.00),
(4, 7, 5, 7500.00, 37500.00),
(5, 11, 8, 9500.00, 76000.00);

-- =====================================================
-- 13. SAMPLE RECEIPTS
-- =====================================================
INSERT INTO `receipt` (`invoice_id`, `customer_id`, `receipt_date`, `amount`, `payment_method`, `discount`, `notes`) VALUES
(1, 1, '2024-01-20', 5250.00, 'bank', 0.00, 'Full payment received'),
(2, 2, '2024-01-25', 6000.00, 'cash', 100.00, 'Partial payment with discount'),
(4, 4, '2024-02-01', 8400.00, 'cheque', 0.00, 'Payment by cheque'),
(5, 5, '2024-02-08', 1500.00, 'bank', 50.00, 'Advance payment'),
(7, 7, '2024-02-15', 1575.00, 'cash', 0.00, 'Cash payment'),
(8, 8, '2024-02-20', 5000.00, 'bank', 0.00, 'First installment');

-- =====================================================
-- 14. SAMPLE PAYMENTS
-- =====================================================
INSERT INTO `supplier_payment` (`purchase_id`, `supplier_id`, `payment_date`, `amount`, `payment_method`, `discount`, `notes`) VALUES
(1, 1, '2024-01-15', 52500.00, 'bank', 0.00, 'Full payment'),
(2, 2, '2024-01-20', 40000.00, 'bank', 500.00, 'Partial payment with discount'),
(4, 4, '2024-02-10', 63000.00, 'cheque', 0.00, 'Payment by cheque'),
(5, 5, '2024-02-18', 50000.00, 'bank', 1000.00, 'First installment');

-- =====================================================
-- 15. BROKER COMMISSIONS
-- =====================================================
INSERT INTO `broker_commission` (`broker_id`, `invoice_id`, `commission_amount`, `commission_rate`, `paid_amount`, `payment_status`) VALUES
(1, 1, 262.50, 5.00, 262.50, 'paid'),
(2, 2, 567.00, 4.50, 300.00, 'partial'),
(1, 3, 201.88, 5.50, 0.00, 'unpaid'),
(3, 4, 462.00, 5.50, 462.00, 'paid'),
(2, 5, 118.13, 4.50, 0.00, 'unpaid'),
(4, 6, 630.00, 4.00, 0.00, 'unpaid'),
(1, 7, 86.63, 5.50, 86.63, 'paid'),
(3, 8, 577.50, 5.50, 300.00, 'partial');

-- =====================================================
-- 16. AGENT COMMISSIONS
-- =====================================================
INSERT INTO `agent_commission` (`agent_id`, `invoice_id`, `commission_amount`, `commission_rate`, `paid_amount`, `payment_status`) VALUES
(1, 1, 157.50, 3.00, 157.50, 'paid'),
(2, 2, 441.00, 3.50, 200.00, 'partial'),
(3, 3, 119.44, 3.25, 0.00, 'unpaid'),
(1, 4, 252.00, 3.00, 252.00, 'paid'),
(4, 5, 72.19, 2.75, 0.00, 'unpaid'),
(2, 6, 551.25, 3.50, 0.00, 'unpaid'),
(5, 7, 47.25, 3.00, 47.25, 'paid'),
(3, 8, 341.25, 3.25, 150.00, 'partial');

-- =====================================================
-- 17. SAMPLE QUOTATIONS
-- =====================================================
INSERT INTO `quotation` (`quotation_no`, `customer_id`, `quotation_date`, `valid_until`, `subtotal`, `vat`, `grand_total`, `items`, `notes`, `status`) VALUES
('QT-2024-0001', 1, '2024-03-01', '2024-03-31', 5000.00, 250.00, 5250.00, '[{"product_id":3,"quantity":1,"rate":5000}]', 'Special offer for new customer', 'pending'),
('QT-2024-0002', 3, '2024-03-05', '2024-04-05', 15000.00, 750.00, 15750.00, '[{"product_id":8,"quantity":1,"rate":15000}]', 'Premium package quote', 'pending'),
('QT-2024-0003', 5, '2024-03-10', '2024-04-10', 2750.00, 137.50, 2887.50, '[{"product_id":1,"quantity":1,"rate":2500},{"product_id":9,"quantity":1,"rate":250}]', 'Motor + Travel bundle', 'pending');

-- =====================================================
-- 18. SAMPLE PDC (Post-Dated Cheques)
-- =====================================================
INSERT INTO `pdc` (`cheque_no`, `cheque_date`, `amount`, `bank`, `cheque_type`, `customer_id`, `supplier_id`, `status`, `reference_type`, `reference_id`, `notes`) VALUES
('CHQ-001-2024', '2024-04-15', 6600.00, 'Emirates NBD', 'received', 2, NULL, 'pending', 'invoice', 2, 'Balance payment for invoice INV-2024-0002'),
('CHQ-002-2024', '2024-04-20', 3675.00, 'ADCB', 'received', 3, NULL, 'pending', 'invoice', 3, 'Full payment PDC'),
('CHQ-003-2024', '2024-04-25', 15750.00, 'Mashreq Bank', 'received', 6, NULL, 'pending', 'invoice', 6, 'Full payment PDC'),
('CHQ-101-2024', '2024-04-10', 38750.00, 'Dubai Islamic Bank', 'issued', NULL, 2, 'pending', 'purchase', 2, 'Balance payment for purchase'),
('CHQ-102-2024', '2024-04-30', 105000.00, 'First Abu Dhabi Bank', 'issued', NULL, 3, 'pending', 'purchase', 3, 'Full payment PDC');

-- =====================================================
-- 19. DAYBOOK ENTRIES (Sample)
-- =====================================================
INSERT INTO `daybook` (`date`, `account_code`, `debit`, `credit`, `narration`, `reference_type`, `reference_id`) VALUES
-- Invoice 1
('2024-01-15', 'CUST_1', 5250.00, 0.00, 'Sales to Ahmed Mohammed Ali', 'invoice', 1),
('2024-01-15', 'SALES', 0.00, 5000.00, 'Sales revenue', 'invoice', 1),
('2024-01-15', 'VAT-OUT', 0.00, 250.00, 'VAT on sales', 'invoice', 1),
-- Receipt 1
('2024-01-20', 'BANK', 5250.00, 0.00, 'Receipt from Ahmed Mohammed Ali', 'receipt', 1),
('2024-01-20', 'CUST_1', 0.00, 5250.00, 'Payment received', 'receipt', 1),
-- Purchase 1
('2024-01-10', 'PURCHASE', 50000.00, 0.00, 'Purchase from Dubai Insurance Company', 'purchase', 1),
('2024-01-10', 'VAT-IN', 2500.00, 0.00, 'VAT on purchase', 'purchase', 1),
('2024-01-10', 'SUPP_1', 0.00, 52500.00, 'Purchase liability', 'purchase', 1),
-- Payment 1
('2024-01-15', 'SUPP_1', 52500.00, 0.00, 'Payment to Dubai Insurance Company', 'payment', 1),
('2024-01-15', 'BANK', 0.00, 52500.00, 'Payment made', 'payment', 1),
-- Commission entries
('2024-01-20', 'COMMISSION', 262.50, 0.00, 'Broker commission - Gulf Insurance Brokers', 'commission', 1),
('2024-01-20', 'BANK', 0.00, 262.50, 'Commission paid', 'commission', 1);

-- =====================================================
-- END OF SAMPLE DATA
-- =====================================================
