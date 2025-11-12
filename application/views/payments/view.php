<?php
$net_amount = $payment->amount - ($payment->discount ?? 0);
$is_pdc = ($payment->payment_method == 'cheque' && !empty($payment->cheque_date) && $payment->cheque_date > date('Y-m-d'));
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="<?php echo base_url('payments'); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Payments
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Payment #<?php echo $payment->payment_id; ?></h1>
    </div>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <?php card_start('Payment Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Payment ID</label>
                <p class="font-semibold text-gray-900">#<?php echo $payment->payment_id; ?></p>
            </div>
            <div>
                <label class="text-sm text-gray-600">Payment Date</label>
                <p class="font-semibold text-gray-900"><?php echo format_date($payment->payment_date); ?></p>
            </div>
            <div>
                <label class="text-sm text-gray-600">Payment Method</label>
                <p><?php echo $is_pdc ? '<span class="badge badge-warning">PDC</span>' : badge(ucfirst($payment->payment_method)); ?></p>
            </div>
            <div>
                <label class="text-sm text-gray-600">Amount Paid</label>
                <p class="font-semibold text-danger-600 text-xl"><?php echo format_currency($payment->amount); ?></p>
            </div>
            <?php if ($payment->discount > 0): ?>
                <div>
                    <label class="text-sm text-gray-600">Discount Received</label>
                    <p class="font-semibold text-success-600"><?php echo format_currency($payment->discount); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <?php if ($purchase): ?>
            <?php card_start('Purchase Information'); ?>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-gray-600">Purchase No</label>
                    <p class="font-semibold"><code><?php echo $purchase->chalan_no; ?></code></p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Purchase Date</label>
                    <p class="font-semibold"><?php echo format_date($purchase->purchase_date); ?></p>
                </div>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>

        <?php if ($supplier): ?>
            <?php card_start('Supplier Information'); ?>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-gray-600">Supplier Name</label>
                    <p class="font-semibold"><?php echo htmlspecialchars($supplier->supplier_name); ?></p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Mobile</label>
                    <p class="font-semibold"><?php echo htmlspecialchars($supplier->supplier_mobile ?? 'N/A'); ?></p>
                </div>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>
    </div>

    <div class="lg:col-span-1 space-y-6">
        <?php card_start('Amount Summary'); ?>
        <div class="text-center p-4 bg-danger-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-1">Amount Paid</div>
            <div class="text-3xl font-bold text-danger-600"><?php echo format_currency($payment->amount); ?></div>
        </div>
        <?php card_end(); ?>

        <?php card_start('Quick Actions'); ?>
        <div class="space-y-2">
            <button onclick="window.print()" class="btn btn-block btn-secondary">
                <i class="fas fa-print"></i> Print Payment
            </button>
            <a href="<?php echo base_url('payments'); ?>" class="btn btn-block btn-outline">
                <i class="fas fa-list"></i> All Payments
            </a>
        </div>
        <?php card_end(); ?>
    </div>
</div>
