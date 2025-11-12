<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo htmlspecialchars($invoice->invoice); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
        .invoice-container { max-width: 800px; margin: 0 auto; }
        .invoice-header { margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 20px; }
        .company-info { text-align: center; margin-bottom: 20px; }
        .company-info h1 { font-size: 28px; color: #333; margin-bottom: 5px; }
        .company-info p { color: #666; }
        .invoice-details { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .invoice-details div { flex: 1; }
        .invoice-details h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .invoice-details p { margin: 5px 0; }
        .invoice-number { font-size: 24px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #f5f5f5; padding: 12px; text-align: left; font-weight: bold; border-bottom: 2px solid #ddd; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-section { margin-top: 20px; float: right; width: 300px; }
        .totals-section table { margin: 0; }
        .totals-section td { border: none; padding: 8px 12px; }
        .grand-total { font-size: 18px; font-weight: bold; background-color: #f5f5f5; }
        .footer { clear: both; margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Print Button -->
        <div class="no-print" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Print Invoice
            </button>
        </div>

        <!-- Company Info -->
        <div class="company-info">
            <h1>INSURANCE MANAGEMENT SYSTEM</h1>
            <p>123 Business Street | City, Country | Phone: +971 XXX XXXX | Email: info@company.com</p>
            <p>TRN: XXXXXXXXX</p>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-details">
                <div>
                    <h3>INVOICE TO:</h3>
                    <p><strong><?php echo htmlspecialchars($invoice->customer_name ?? 'N/A'); ?></strong></p>
                    <p><?php echo htmlspecialchars($invoice->customer_mobile ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($invoice->customer_email ?? ''); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($invoice->customer_address ?? '')); ?></p>
                </div>
                <div style="text-align: right;">
                    <div class="invoice-number"><?php echo htmlspecialchars($invoice->invoice); ?></div>
                    <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($invoice->date)); ?></p>
                    <p><strong>Status:</strong>
                        <?php
                        $status = $invoice->payment_status ?? 'unpaid';
                        echo strtoupper($status);
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product Description</th>
                    <th class="text-center" style="width: 100px;">Quantity</th>
                    <th class="text-right" style="width: 120px;">Rate</th>
                    <th class="text-right" style="width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoice->items)): ?>
                    <?php foreach ($invoice->items as $index => $item): ?>
                        <tr>
                            <td><?php echo ($index + 1); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($item->product_name); ?></strong>
                                <?php if ($item->product_model): ?>
                                    <br><small><?php echo htmlspecialchars($item->product_model); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo $item->quantity; ?></td>
                            <td class="text-right">AED <?php echo number_format($item->rate, 2); ?></td>
                            <td class="text-right">AED <?php echo number_format($item->total_price, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <table>
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="text-right">AED <?php echo number_format($invoice->total, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>VAT (5%):</strong></td>
                    <td class="text-right">AED <?php echo number_format($invoice->vat, 2); ?></td>
                </tr>
                <tr class="grand-total">
                    <td><strong>GRAND TOTAL:</strong></td>
                    <td class="text-right"><strong>AED <?php echo number_format($invoice->grand_total, 2); ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Payment Terms:</strong> Payment is due within 30 days</p>
            <p>Thank you for your business!</p>
            <p style="margin-top: 10px;">This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
