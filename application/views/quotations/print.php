<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation #<?php echo htmlspecialchars($quotation->quotation_no); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
        .quotation-container { max-width: 800px; margin: 0 auto; }
        .quotation-header { margin-bottom: 30px; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; }
        .company-info { text-align: center; margin-bottom: 20px; }
        .company-info h1 { font-size: 28px; color: #333; margin-bottom: 5px; }
        .company-info p { color: #666; }
        .document-title { text-align: center; background-color: #fef3c7; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .document-title h2 { color: #f59e0b; font-size: 24px; }
        .quotation-details { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .quotation-details div { flex: 1; }
        .quotation-details h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .quotation-details p { margin: 5px 0; }
        .quotation-number { font-size: 24px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #fef3c7; padding: 12px; text-align: left; font-weight: bold; border-bottom: 2px solid #f59e0b; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-section { margin-top: 20px; float: right; width: 300px; }
        .totals-section table { margin: 0; }
        .totals-section td { border: none; padding: 8px 12px; }
        .grand-total { font-size: 18px; font-weight: bold; background-color: #fef3c7; }
        .validity-notice { clear: both; margin-top: 30px; padding: 15px; background-color: #fef3c7; border-left: 4px solid #f59e0b; }
        .notes-section { clear: both; margin-top: 30px; padding: 15px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 5px; }
        .footer { clear: both; margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: bold; }
        .status-pending { background-color: #fef3c7; color: #f59e0b; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="quotation-container">
        <!-- Print Button -->
        <div class="no-print" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #f59e0b; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Print Quotation
            </button>
        </div>

        <!-- Company Info -->
        <div class="company-info">
            <h1>INSURANCE MANAGEMENT SYSTEM</h1>
            <p>123 Business Street | City, Country | Phone: +971 XXX XXXX | Email: info@company.com</p>
            <p>TRN: XXXXXXXXX</p>
        </div>

        <!-- Document Title -->
        <div class="document-title">
            <h2>QUOTATION</h2>
        </div>

        <!-- Quotation Header -->
        <div class="quotation-header">
            <div class="quotation-details">
                <div>
                    <h3>QUOTATION TO:</h3>
                    <p><strong><?php echo htmlspecialchars($quotation->customer_name ?? 'N/A'); ?></strong></p>
                    <p><?php echo htmlspecialchars($quotation->customer_mobile ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($quotation->customer_email ?? ''); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($quotation->customer_address ?? '')); ?></p>
                </div>
                <div style="text-align: right;">
                    <div class="quotation-number"><?php echo htmlspecialchars($quotation->quotation_no); ?></div>
                    <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($quotation->quotation_date)); ?></p>
                    <?php if ($quotation->valid_until): ?>
                        <p><strong>Valid Until:</strong> <?php echo date('d M Y', strtotime($quotation->valid_until)); ?></p>
                    <?php endif; ?>
                    <p><strong>Status:</strong>
                        <?php
                        $status = $quotation->status ?? 'pending';
                        if ($status == 'converted') {
                            echo '<span class="status-badge" style="background: #d1fae5; color: #059669;">CONVERTED</span>';
                        } elseif ($status == 'rejected') {
                            echo '<span class="status-badge" style="background: #fee2e2; color: #dc2626;">REJECTED</span>';
                        } else {
                            echo '<span class="status-badge status-pending">PENDING</span>';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Quotation Items -->
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
                <?php if (!empty($quotation->items)): ?>
                    <?php foreach ($quotation->items as $index => $item): ?>
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
                    <td class="text-right">AED <?php echo number_format($quotation->subtotal, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>VAT (5%):</strong></td>
                    <td class="text-right">AED <?php echo number_format($quotation->vat, 2); ?></td>
                </tr>
                <tr class="grand-total">
                    <td><strong>GRAND TOTAL:</strong></td>
                    <td class="text-right"><strong>AED <?php echo number_format($quotation->grand_total, 2); ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- Validity Notice -->
        <?php if ($quotation->valid_until): ?>
            <div class="validity-notice">
                <strong>Validity:</strong> This quotation is valid until <?php echo date('d M Y', strtotime($quotation->valid_until)); ?>
            </div>
        <?php endif; ?>

        <!-- Notes Section -->
        <?php if (!empty($quotation->notes)): ?>
            <div class="notes-section">
                <h3 style="margin-bottom: 10px;">Terms & Conditions:</h3>
                <p style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($quotation->notes)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Important:</strong> This is a quotation and not an invoice. No payment is required at this time.</p>
            <p>Please contact us if you have any questions or would like to proceed with this quotation.</p>
            <p style="margin-top: 10px;">This is a computer-generated quotation and does not require a signature.</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
