<?php
$status = $quotation->status ?? 'pending';
$is_pending = ($status == 'pending');
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="<?php echo base_url('quotations'); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Quotations
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Quotation #<?php echo htmlspecialchars($quotation->quotation_no); ?></h1>
    </div>
    <div class="flex gap-2">
        <?php if ($is_pending): ?>
            <a href="<?php echo base_url('quotations/convert_to_invoice/' . $quotation->quotation_id); ?>"
               class="btn btn-success"
               onclick="return confirm('Convert this quotation to invoice?')">
                <i class="fas fa-exchange-alt"></i> Convert to Invoice
            </a>
        <?php endif; ?>
        <a href="<?php echo base_url('quotations/print_quotation/' . $quotation->quotation_id); ?>"
           class="btn btn-secondary" target="_blank">
            <i class="fas fa-print"></i> Print
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Quotation Info -->
        <?php card_start('Quotation Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Quotation ID</label>
                <p class="font-semibold text-gray-900">#<?php echo $quotation->quotation_id; ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Quotation Number</label>
                <p class="font-semibold text-gray-900">
                    <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($quotation->quotation_no); ?></code>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Quotation Date</label>
                <p class="font-semibold text-gray-900"><?php echo format_date($quotation->quotation_date); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Valid Until</label>
                <p class="font-semibold text-gray-900">
                    <?php if ($quotation->valid_until): ?>
                        <?php echo format_date($quotation->valid_until); ?>
                        <?php if (strtotime($quotation->valid_until) < time() && $is_pending): ?>
                            <span class="badge badge-danger ml-2">Expired</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-gray-500">N/A</span>
                    <?php endif; ?>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Status</label>
                <p>
                    <?php
                    if ($status == 'converted') {
                        echo '<span class="badge badge-success">Converted to Invoice</span>';
                    } elseif ($status == 'rejected') {
                        echo '<span class="badge badge-danger">Rejected</span>';
                    } else {
                        echo '<span class="badge badge-warning">Pending</span>';
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
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($quotation->customer_name ?? 'N/A'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Mobile</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($quotation->customer_mobile ?? 'N/A'); ?></p>
            </div>

            <div class="col-span-2">
                <label class="text-sm text-gray-600">Email</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($quotation->customer_email ?? 'N/A'); ?></p>
            </div>

            <div class="col-span-2">
                <a href="<?php echo base_url('customers/view/' . $quotation->customer_id); ?>" class="btn btn-sm btn-outline">
                    <i class="fas fa-user"></i> View Customer
                </a>
            </div>
        </div>
        <?php card_end(); ?>

        <!-- Quotation Items -->
        <?php card_start('Quotation Items'); ?>
        <div class="overflow-x-auto">
            <?php table_start(['#', 'Product', 'Quantity', 'Rate', 'Total']); ?>
                <?php if (!empty($quotation->items)): ?>
                    <?php foreach ($quotation->items as $index => $item): ?>
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
                        <td><?php echo format_currency($quotation->subtotal); ?></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="text-right">VAT (5%):</td>
                        <td><?php echo format_currency($quotation->vat); ?></td>
                    </tr>
                    <tr class="bg-warning-50 font-bold text-lg">
                        <td colspan="4" class="text-right">Grand Total:</td>
                        <td class="text-warning-600"><?php echo format_currency($quotation->grand_total); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500">No items found</td>
                    </tr>
                <?php endif; ?>
            <?php table_end(); ?>
        </div>
        <?php card_end(); ?>

        <!-- Notes -->
        <?php if (!empty($quotation->notes)): ?>
            <?php card_start('Additional Notes'); ?>
            <div class="text-gray-700 whitespace-pre-wrap">
                <?php echo nl2br(htmlspecialchars($quotation->notes)); ?>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Quotation Summary -->
        <?php card_start('Quotation Summary'); ?>
        <div class="space-y-4">
            <div class="text-center p-4 bg-warning-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Grand Total</div>
                <div class="text-3xl font-bold text-warning-600">
                    <?php echo format_currency($quotation->grand_total); ?>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-semibold"><?php echo format_currency($quotation->subtotal); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">VAT (5%):</span>
                    <span class="font-semibold"><?php echo format_currency($quotation->vat); ?></span>
                </div>
            </div>

            <?php if ($status == 'pending' && $quotation->valid_until && strtotime($quotation->valid_until) < time()): ?>
                <div class="bg-danger-50 border border-danger-200 rounded-lg p-3 text-sm">
                    <i class="fas fa-exclamation-triangle text-danger-600"></i>
                    <span class="text-danger-800">This quotation has expired</span>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Quick Actions -->
        <?php card_start('Quick Actions'); ?>
        <div class="space-y-2">
            <?php if ($is_pending): ?>
                <a href="<?php echo base_url('quotations/convert_to_invoice/' . $quotation->quotation_id); ?>"
                   class="btn btn-block btn-success"
                   onclick="return confirm('Convert to invoice?')">
                    <i class="fas fa-exchange-alt"></i> Convert to Invoice
                </a>
            <?php endif; ?>
            <a href="<?php echo base_url('quotations/print_quotation/' . $quotation->quotation_id); ?>"
               class="btn btn-block btn-secondary" target="_blank">
                <i class="fas fa-print"></i> Print Quotation
            </a>
            <?php if ($is_pending): ?>
                <button onclick="rejectQuotation()" class="btn btn-block btn-danger">
                    <i class="fas fa-times"></i> Reject Quotation
                </button>
            <?php endif; ?>
            <a href="<?php echo base_url('quotations'); ?>" class="btn btn-block btn-outline">
                <i class="fas fa-list"></i> All Quotations
            </a>
        </div>
        <?php card_end(); ?>

        <!-- Stock Notice -->
        <?php card_start('Stock Notice'); ?>
        <div class="bg-info-50 border border-info-200 rounded-lg p-3 text-sm">
            <i class="fas fa-info-circle text-info-600"></i>
            <span class="text-info-800">No stock has been deducted for this quotation. Stock will be deducted when converted to invoice.</span>
        </div>
        <?php card_end(); ?>
    </div>
</div>

<script>
function rejectQuotation() {
    Swal.fire({
        title: 'Reject Quotation?',
        text: 'This quotation will be marked as rejected.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, reject it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // In a real implementation, you would have a reject endpoint
            window.location.href = '<?php echo base_url('quotations'); ?>';
        }
    });
}
</script>
