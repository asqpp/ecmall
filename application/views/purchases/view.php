<?php
$payment_status = $purchase->payment_status ?? 'unpaid';
$total_paid = 0;
if (!empty($purchase->payments)) {
    foreach ($purchase->payments as $payment) {
        $total_paid += $payment->amount;
    }
}
$outstanding = $purchase->grand_total_amount - $total_paid;
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="<?php echo base_url('purchases'); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Purchases
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Purchase #<?php echo htmlspecialchars($purchase->chalan_no); ?></h1>
    </div>
    <div class="flex gap-2">
        <?php if ($payment_status != 'paid'): ?>
            <a href="<?php echo base_url('payments/add?purchase_id=' . $purchase->purchase_id); ?>"
               class="btn btn-success">
                <i class="fas fa-money-bill"></i> Add Payment
            </a>
        <?php endif; ?>
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Purchase Info -->
        <?php card_start('Purchase Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Purchase ID</label>
                <p class="font-semibold text-gray-900">#<?php echo $purchase->purchase_id; ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Chalan Number</label>
                <p class="font-semibold text-gray-900">
                    <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($purchase->chalan_no); ?></code>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Purchase Date</label>
                <p class="font-semibold text-gray-900"><?php echo format_date($purchase->purchase_date); ?></p>
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

            <?php if ($purchase->purchase_details): ?>
                <div class="col-span-2">
                    <label class="text-sm text-gray-600">Notes</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($purchase->purchase_details)); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Supplier Info -->
        <?php card_start('Supplier Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Supplier Name</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($purchase->supplier_name ?? 'N/A'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Mobile</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($purchase->supplier_mobile ?? 'N/A'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Email</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($purchase->supplier_email_address ?? 'N/A'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Address</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($purchase->supplier_address ?? 'N/A'); ?></p>
            </div>
        </div>
        <?php card_end(); ?>

        <!-- Purchase Items -->
        <?php card_start('Purchase Items'); ?>
        <div class="overflow-x-auto">
            <?php table_start(['#', 'Product', 'Quantity', 'Rate', 'Total']); ?>
                <?php if (!empty($purchase->items)): ?>
                    <?php foreach ($purchase->items as $index => $item): ?>
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
                        <td><?php echo format_currency($purchase->total_amount); ?></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="text-right">VAT (5%):</td>
                        <td><?php echo format_currency($purchase->vat ?? 0); ?></td>
                    </tr>
                    <tr class="bg-primary-50 font-bold text-lg">
                        <td colspan="4" class="text-right">Grand Total:</td>
                        <td class="text-primary-600"><?php echo format_currency($purchase->grand_total_amount); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500">No items found</td>
                    </tr>
                <?php endif; ?>
            <?php table_end(); ?>
        </div>
        <?php card_end(); ?>

        <!-- Payments -->
        <?php if (!empty($purchase->payments)): ?>
            <?php card_start('Payment History'); ?>
            <div class="overflow-x-auto">
                <?php table_start(['Date', 'Amount', 'Method', 'Details']); ?>
                    <?php foreach ($purchase->payments as $payment): ?>
                        <tr>
                            <td><?php echo format_date($payment->payment_date); ?></td>
                            <td class="font-semibold text-success-600"><?php echo format_currency($payment->amount); ?></td>
                            <td><?php echo badge(ucfirst($payment->payment_method ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars($payment->details ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php table_end(); ?>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Payment Summary -->
        <?php card_start('Payment Summary'); ?>
        <div class="space-y-4">
            <div class="text-center p-4 bg-primary-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Grand Total</div>
                <div class="text-3xl font-bold text-primary-600">
                    <?php echo format_currency($purchase->grand_total_amount); ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-success-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Paid</div>
                    <div class="text-lg font-bold text-success-600">
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
                    <span class="text-warning-800">Outstanding balance of <?php echo format_currency($outstanding); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Quick Actions -->
        <?php card_start('Quick Actions'); ?>
        <div class="space-y-2">
            <?php if ($payment_status != 'paid'): ?>
                <a href="<?php echo base_url('payments/add?purchase_id=' . $purchase->purchase_id); ?>"
                   class="btn btn-block btn-success">
                    <i class="fas fa-money-bill"></i> Add Payment
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-block btn-secondary">
                <i class="fas fa-print"></i> Print Purchase
            </button>
            <a href="<?php echo base_url('purchases'); ?>" class="btn btn-block btn-outline">
                <i class="fas fa-list"></i> All Purchases
            </a>
        </div>
        <?php card_end(); ?>

        <!-- Stock Impact -->
        <?php card_start('Stock Impact'); ?>
        <div class="space-y-2 text-sm">
            <div class="bg-success-50 border border-success-200 rounded-lg p-3">
                <i class="fas fa-warehouse text-success-600"></i>
                <span class="text-success-800">Stock has been increased for all items in this purchase</span>
            </div>

            <?php if (!empty($purchase->items)): ?>
                <div class="space-y-1 mt-3">
                    <?php foreach ($purchase->items as $item): ?>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span><?php echo htmlspecialchars($item->product_name); ?></span>
                            <span class="font-semibold text-success-600">+<?php echo $item->quantity; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>
    </div>
</div>
