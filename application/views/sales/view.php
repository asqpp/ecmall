<?php
$payment_status = $invoice->payment_status ?? 'unpaid';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="<?php echo base_url('sales'); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Invoices
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Invoice #<?php echo htmlspecialchars($invoice->invoice); ?></h1>
    </div>
    <div class="flex gap-2">
        <?php if ($payment_status != 'paid'): ?>
            <a href="<?php echo base_url('receipts/add?invoice_id=' . $invoice->invoice_id); ?>"
               class="btn btn-success">
                <i class="fas fa-money-bill"></i> Add Receipt
            </a>
            <a href="<?php echo base_url('sales/edit/' . $invoice->invoice_id); ?>"
               class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
        <?php endif; ?>
        <a href="<?php echo base_url('sales/print_invoice/' . $invoice->invoice_id); ?>"
           class="btn btn-secondary" target="_blank">
            <i class="fas fa-print"></i> Print
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Invoice Info -->
        <?php card_start('Invoice Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Invoice ID</label>
                <p class="font-semibold text-gray-900">#<?php echo $invoice->invoice_id; ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Invoice Number</label>
                <p class="font-semibold text-gray-900">
                    <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($invoice->invoice); ?></code>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Invoice Date</label>
                <p class="font-semibold text-gray-900"><?php echo format_date($invoice->date); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Payment Status</label>
                <p>
                    <?php
                    if ($payment_status == 'paid') {
                        echo '<span class="badge badge-success">Paid</span>';
                    } elseif ($payment_status == 'partial') {
                        echo '<span class="badge badge-warning">Partially Paid</span>';
                    } else {
                        echo '<span class="badge badge-danger">Unpaid</span>';
                    }
                    ?>
                </p>
            </div>
        </div>
        <?php card_end(); ?>

        <!-- Customer Info -->
        <?php card_start('Customer Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Customer Name</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($invoice->customer_name ?? 'N/A'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Mobile</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($invoice->customer_mobile ?? 'N/A'); ?></p>
            </div>

            <div class="col-span-2">
                <label class="text-sm text-gray-600">Email</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($invoice->customer_email ?? 'N/A'); ?></p>
            </div>

            <div class="col-span-2">
                <a href="<?php echo base_url('customers/view/' . $invoice->customer_id); ?>" class="btn btn-sm btn-outline">
                    <i class="fas fa-user"></i> View Customer
                </a>
            </div>
        </div>
        <?php card_end(); ?>

        <!-- Invoice Items -->
        <?php card_start('Invoice Items'); ?>
        <div class="overflow-x-auto">
            <?php table_start(['#', 'Product', 'Quantity', 'Rate', 'Total']); ?>
                <?php if (!empty($invoice->items)): ?>
                    <?php foreach ($invoice->items as $index => $item): ?>
                        <tr>
                            <td><?php echo ($index + 1); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($item->product_name); ?></strong>
                                <?php if ($item->product_model): ?>
                                    <br><span class="text-sm text-gray-600"><?php echo htmlspecialchars($item->product_model); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item->quantity; ?></td>
                            <td><?php echo format_currency($item->rate); ?></td>
                            <td class="font-semibold"><?php echo format_currency($item->total_price); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Totals -->
                    <tr class="bg-gray-50 font-semibold">
                        <td colspan="4" class="text-right">Subtotal:</td>
                        <td><?php echo format_currency($invoice->total); ?></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="text-right">VAT (5%):</td>
                        <td><?php echo format_currency($invoice->vat); ?></td>
                    </tr>
                    <tr class="bg-success-50 font-bold text-lg">
                        <td colspan="4" class="text-right">Grand Total:</td>
                        <td class="text-success-600"><?php echo format_currency($invoice->grand_total); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500">No items found</td>
                    </tr>
                <?php endif; ?>
            <?php table_end(); ?>
        </div>
        <?php card_end(); ?>

        <!-- Payments/Receipts -->
        <?php if (!empty($invoice->payments)): ?>
            <?php card_start('Payment History'); ?>
            <div class="overflow-x-auto">
                <?php table_start(['Date', 'Amount', 'Method', 'Notes']); ?>
                    <?php foreach ($invoice->payments as $payment): ?>
                        <tr>
                            <td><?php echo format_date($payment->receipt_date ?? $payment->payment_date); ?></td>
                            <td class="font-semibold text-success-600"><?php echo format_currency($payment->amount); ?></td>
                            <td><?php echo badge(ucfirst($payment->payment_method ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars($payment->notes ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php table_end(); ?>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Invoice Summary -->
        <?php card_start('Invoice Summary'); ?>
        <div class="space-y-4">
            <div class="text-center p-4 bg-success-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Grand Total</div>
                <div class="text-3xl font-bold text-success-600">
                    <?php echo format_currency($invoice->grand_total); ?>
                </div>
            </div>

            <?php
            // Calculate paid and outstanding
            $total_paid = 0;
            if (!empty($invoice->payments)) {
                foreach ($invoice->payments as $payment) {
                    $total_paid += $payment->amount;
                }
            }
            $outstanding = $invoice->grand_total - $total_paid;
            ?>

            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-primary-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Paid</div>
                    <div class="text-lg font-bold text-primary-600">
                        <?php echo format_currency($total_paid); ?>
                    </div>
                </div>

                <div class="text-center p-3 <?php echo $outstanding > 0 ? 'bg-danger-50' : 'bg-gray-50'; ?> rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Outstanding</div>
                    <div class="text-lg font-bold <?php echo $outstanding > 0 ? 'text-danger-600' : 'text-gray-600'; ?>">
                        <?php echo format_currency($outstanding); ?>
                    </div>
                </div>
            </div>

            <?php if ($outstanding > 0): ?>
                <div class="bg-warning-50 border border-warning-200 rounded-lg p-3 text-sm">
                    <i class="fas fa-exclamation-triangle text-warning-600"></i>
                    <span class="text-warning-800">Outstanding: <?php echo format_currency($outstanding); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Quick Actions -->
        <?php card_start('Quick Actions'); ?>
        <div class="space-y-2">
            <?php if ($payment_status != 'paid'): ?>
                <a href="<?php echo base_url('receipts/add?invoice_id=' . $invoice->invoice_id); ?>"
                   class="btn btn-block btn-success">
                    <i class="fas fa-money-bill"></i> Add Receipt
                </a>
                <a href="<?php echo base_url('sales/edit/' . $invoice->invoice_id); ?>"
                   class="btn btn-block btn-primary">
                    <i class="fas fa-edit"></i> Edit Invoice
                </a>
            <?php endif; ?>
            <a href="<?php echo base_url('sales/print_invoice/' . $invoice->invoice_id); ?>"
               class="btn btn-block btn-secondary" target="_blank">
                <i class="fas fa-print"></i> Print Invoice
            </a>
            <a href="<?php echo base_url('sales'); ?>" class="btn btn-block btn-outline">
                <i class="fas fa-list"></i> All Invoices
            </a>
        </div>
        <?php card_end(); ?>

        <!-- Stock Impact -->
        <?php card_start('Stock Impact'); ?>
        <div class="space-y-2 text-sm">
            <div class="bg-warning-50 border border-warning-200 rounded-lg p-3">
                <i class="fas fa-warehouse text-warning-600"></i>
                <span class="text-warning-800">Stock has been deducted for all items in this invoice</span>
            </div>

            <?php if (!empty($invoice->items)): ?>
                <div class="space-y-1 mt-3">
                    <?php foreach ($invoice->items as $item): ?>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span><?php echo htmlspecialchars($item->product_name); ?></span>
                            <span class="font-semibold text-danger-600">-<?php echo $item->quantity; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>
    </div>
</div>
