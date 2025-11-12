<?php
$net_amount = $receipt->amount - ($receipt->discount ?? 0);
$is_pdc = ($receipt->payment_method == 'cheque' && !empty($receipt->cheque_date) && $receipt->cheque_date > date('Y-m-d'));
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="<?php echo base_url('receipts'); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Receipts
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Receipt #<?php echo $receipt->receipt_id; ?></h1>
    </div>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<?php if ($is_pdc && $pdc && $pdc->pend == 1): ?>
    <div class="bg-warning-100 border border-warning-300 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-3">
            <i class="fas fa-calendar-check text-warning-600 text-2xl"></i>
            <div>
                <h3 class="font-semibold text-warning-900">Post-Dated Cheque (Pending Clearance)</h3>
                <p class="text-warning-700 text-sm mt-1">
                    This receipt is a PDC. Cheque clearance date: <?php echo format_date($receipt->cheque_date); ?>
                </p>
            </div>
        </div>
    </div>
<?php elseif ($is_pdc && $pdc && $pdc->pend == 0): ?>
    <div class="bg-success-100 border border-success-300 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-success-600 text-2xl"></i>
            <div>
                <h3 class="font-semibold text-success-900">PDC Cleared</h3>
                <p class="text-success-700 text-sm mt-1">This post-dated cheque has been cleared and posted to bank account.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Receipt Information -->
        <?php card_start('Receipt Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Receipt ID</label>
                <p class="font-semibold text-gray-900">#<?php echo $receipt->receipt_id; ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Receipt Date</label>
                <p class="font-semibold text-gray-900"><?php echo format_date($receipt->receipt_date); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Payment Method</label>
                <p>
                    <?php
                    if ($is_pdc) {
                        echo '<span class="badge badge-warning">Post-Dated Cheque</span>';
                    } else {
                        echo badge(ucfirst($receipt->payment_method));
                    }
                    ?>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Amount Received</label>
                <p class="font-semibold text-success-600 text-xl"><?php echo format_currency($receipt->amount); ?></p>
            </div>

            <?php if ($receipt->discount > 0): ?>
                <div>
                    <label class="text-sm text-gray-600">Discount Given</label>
                    <p class="font-semibold text-danger-600"><?php echo format_currency($receipt->discount); ?></p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Net Amount</label>
                    <p class="font-semibold text-gray-900 text-xl"><?php echo format_currency($net_amount); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($receipt->payment_method == 'cheque'): ?>
                <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold mb-3">Cheque Details</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <?php if ($receipt->cheque_no): ?>
                            <div>
                                <label class="text-sm text-gray-600">Cheque Number</label>
                                <p class="font-semibold"><?php echo htmlspecialchars($receipt->cheque_no); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($receipt->cheque_date): ?>
                            <div>
                                <label class="text-sm text-gray-600">Cheque Date</label>
                                <p class="font-semibold"><?php echo format_date($receipt->cheque_date); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($receipt->bank): ?>
                            <div>
                                <label class="text-sm text-gray-600">Bank</label>
                                <p class="font-semibold"><?php echo htmlspecialchars($receipt->bank); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($receipt->notes): ?>
                <div class="col-span-2">
                    <label class="text-sm text-gray-600">Notes</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($receipt->notes)); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Invoice Information -->
        <?php if ($invoice): ?>
            <?php card_start('Invoice Information'); ?>
            <div class="grid grid-cols-2 gap-6">
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
                    <label class="text-sm text-gray-600">Invoice Total</label>
                    <p class="font-semibold text-gray-900"><?php echo format_currency($invoice->grand_total); ?></p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Payment Status</label>
                    <p>
                        <?php
                        if ($invoice->payment_status == 'paid') {
                            echo '<span class="badge badge-success">Paid</span>';
                        } elseif ($invoice->payment_status == 'partial') {
                            echo '<span class="badge badge-warning">Partially Paid</span>';
                        } else {
                            echo '<span class="badge badge-danger">Unpaid</span>';
                        }
                        ?>
                    </p>
                </div>

                <div class="col-span-2">
                    <a href="<?php echo base_url('sales/view/' . $invoice->invoice_id); ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-file-invoice"></i> View Invoice
                    </a>
                </div>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>

        <!-- Customer Information -->
        <?php if ($customer): ?>
            <?php card_start('Customer Information'); ?>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-gray-600">Customer Name</label>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($customer->customer_name); ?></p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Mobile</label>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($customer->customer_mobile ?? 'N/A'); ?></p>
                </div>

                <div class="col-span-2">
                    <a href="<?php echo base_url('customers/view/' . $customer->customer_id); ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-user"></i> View Customer
                    </a>
                </div>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Amount Summary -->
        <?php card_start('Amount Summary'); ?>
        <div class="space-y-4">
            <div class="text-center p-4 bg-success-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Amount Received</div>
                <div class="text-3xl font-bold text-success-600">
                    <?php echo format_currency($receipt->amount); ?>
                </div>
            </div>

            <?php if ($receipt->discount > 0): ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-danger-50 rounded-lg">
                        <div class="text-xs text-gray-600 mb-1">Discount</div>
                        <div class="text-lg font-bold text-danger-600">
                            <?php echo format_currency($receipt->discount); ?>
                        </div>
                    </div>

                    <div class="text-center p-3 bg-primary-50 rounded-lg">
                        <div class="text-xs text-gray-600 mb-1">Net</div>
                        <div class="text-lg font-bold text-primary-600">
                            <?php echo format_currency($net_amount); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Accounting Impact -->
        <?php card_start('Accounting Impact'); ?>
        <div class="space-y-2 text-sm">
            <h4 class="font-semibold text-gray-900 mb-3">Journal Entries Posted:</h4>

            <?php if ($is_pdc): ?>
                <div class="bg-gray-50 p-3 rounded">
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">Dr: PDC Receivable</span>
                        <span class="font-semibold"><?php echo format_currency($receipt->amount); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cr: Customer A/c</span>
                        <span class="font-semibold"><?php echo format_currency($receipt->amount); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 p-3 rounded">
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">Dr: <?php echo $receipt->payment_method == 'cash' ? 'Cash' : 'Bank'; ?></span>
                        <span class="font-semibold"><?php echo format_currency($receipt->amount); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cr: Customer A/c</span>
                        <span class="font-semibold"><?php echo format_currency($receipt->amount); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($receipt->discount > 0): ?>
                <div class="bg-gray-50 p-3 rounded mt-2">
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">Dr: Discount Allowed</span>
                        <span class="font-semibold"><?php echo format_currency($receipt->discount); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cr: Customer A/c</span>
                        <span class="font-semibold"><?php echo format_currency($receipt->discount); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-3 text-xs text-gray-600">
                <i class="fas fa-info-circle"></i> Collection entry also created
            </div>
        </div>
        <?php card_end(); ?>

        <!-- Quick Actions -->
        <?php card_start('Quick Actions'); ?>
        <div class="space-y-2">
            <button onclick="window.print()" class="btn btn-block btn-secondary">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <?php if ($invoice): ?>
                <a href="<?php echo base_url('sales/view/' . $invoice->invoice_id); ?>" class="btn btn-block btn-outline">
                    <i class="fas fa-file-invoice"></i> View Invoice
                </a>
            <?php endif; ?>
            <?php if ($customer): ?>
                <a href="<?php echo base_url('customers/view/' . $customer->customer_id); ?>" class="btn btn-block btn-outline">
                    <i class="fas fa-user"></i> View Customer
                </a>
            <?php endif; ?>
            <a href="<?php echo base_url('receipts'); ?>" class="btn btn-block btn-outline">
                <i class="fas fa-list"></i> All Receipts
            </a>
        </div>
        <?php card_end(); ?>
    </div>
</div>
