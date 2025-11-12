<?php card_start('Supplier Payments', true); ?>

<!-- Header Actions -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div class="flex gap-4 w-full md:w-auto">
        <form method="get" action="<?php echo base_url('payments'); ?>" class="flex gap-2 flex-wrap">
            <input type="date" name="from_date" value="<?php echo $filters['from_date'] ?? ''; ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            <input type="date" name="to_date" value="<?php echo $filters['to_date'] ?? ''; ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            <select name="payment_method" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Methods</option>
                <option value="cash" <?php echo ($filters['payment_method'] ?? '') == 'cash' ? 'selected' : ''; ?>>Cash</option>
                <option value="bank" <?php echo ($filters['payment_method'] ?? '') == 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
                <option value="cheque" <?php echo ($filters['payment_method'] ?? '') == 'cheque' ? 'selected' : ''; ?>>Cheque</option>
            </select>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
            <?php if ($filters['from_date'] || $filters['payment_method']): ?>
                <a href="<?php echo base_url('payments'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('payments/pdc_list'); ?>" class="btn btn-warning">
            <i class="fas fa-calendar-check"></i> PDC List
        </a>
        <a href="<?php echo base_url('payments/export'); ?>" class="btn btn-secondary">
            <i class="fas fa-download"></i> Export
        </a>
        <a href="<?php echo base_url('payments/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Payment
        </a>
    </div>
</div>

<!-- Payments Table -->
<div class="overflow-x-auto">
    <?php table_start(['ID', 'Date', 'Purchase', 'Supplier', 'Amount', 'Discount', 'Net', 'Method', 'Cheque Details', 'Actions']); ?>
        <?php if (empty($payments)): ?>
            <tr>
                <td colspan="10" class="text-center py-8 text-gray-500">
                    <i class="fas fa-money-bill text-4xl mb-2"></i>
                    <p>No payments found</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($payments as $payment): ?>
                <?php
                $net_amount = $payment->amount - ($payment->discount ?? 0);
                $is_pdc = ($payment->payment_method == 'cheque' && !empty($payment->cheque_date) && $payment->cheque_date > date('Y-m-d'));
                ?>
                <tr class="<?php echo $is_pdc ? 'bg-warning-50' : ''; ?>">
                    <td><?php echo $payment->payment_id; ?></td>
                    <td><?php echo format_date($payment->payment_date); ?></td>
                    <td><code class="text-sm"><?php echo htmlspecialchars($payment->purchase_reference ?? 'N/A'); ?></code></td>
                    <td><strong><?php echo htmlspecialchars($payment->supplier_name ?? 'N/A'); ?></strong></td>
                    <td><?php echo format_currency($payment->amount); ?></td>
                    <td class="text-success-600"><?php echo format_currency($payment->discount ?? 0); ?></td>
                    <td class="font-semibold text-danger-600"><?php echo format_currency($net_amount); ?></td>
                    <td>
                        <?php echo $is_pdc ? '<span class="badge badge-warning">PDC</span>' : badge(ucfirst($payment->payment_method)); ?>
                    </td>
                    <td>
                        <?php if ($payment->cheque_no): ?>
                            <div class="text-sm">
                                <div>Cheque: <?php echo htmlspecialchars($payment->cheque_no); ?></div>
                                <?php if ($payment->cheque_date): ?>
                                    <div class="text-gray-600">Date: <?php echo format_date($payment->cheque_date); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('payments/view/' . $payment->payment_id); ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $payment->payment_id; ?>)"
                                    class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php table_end(); ?>
</div>

<!-- Pagination -->
<?php if ($pagination->total_pages > 1): ?>
    <div class="mt-6">
        <?php echo render_pagination($pagination, 'payments'); ?>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<script>
function confirmDelete(paymentId) {
    Swal.fire({
        title: 'Delete Payment?',
        text: 'This will reverse all accounting entries. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('payments/delete/'); ?>${paymentId}`;
        }
    });
}
</script>
