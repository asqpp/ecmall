<?php card_start('Purchases', true); ?>

<!-- Header Actions -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div class="flex gap-4 w-full md:w-auto">
        <form method="get" action="<?php echo base_url('purchases'); ?>" class="flex gap-2 flex-wrap">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search purchases..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">

            <input type="date"
                   name="from_date"
                   value="<?php echo $filters['from_date'] ?? ''; ?>"
                   placeholder="From Date"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">

            <input type="date"
                   name="to_date"
                   value="<?php echo $filters['to_date'] ?? ''; ?>"
                   placeholder="To Date"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">

            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>

            <?php if ($search || $filters['from_date'] || $filters['to_date']): ?>
                <a href="<?php echo base_url('purchases'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="flex gap-2">
        <a href="<?php echo base_url('purchases/export'); ?>" class="btn btn-secondary">
            <i class="fas fa-download"></i> Export
        </a>
        <a href="<?php echo base_url('purchases/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Purchase
        </a>
    </div>
</div>

<!-- Purchases Table -->
<div class="overflow-x-auto">
    <?php table_start(['ID', 'Chalan No', 'Supplier', 'Date', 'Total', 'VAT', 'Grand Total', 'Payment Status', 'Actions']); ?>
        <?php if (empty($purchases)): ?>
            <tr>
                <td colspan="9" class="text-center py-8 text-gray-500">
                    <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                    <p>No purchases found</p>
                    <?php if ($search || $filters['from_date']): ?>
                        <a href="<?php echo base_url('purchases'); ?>" class="text-primary-600 hover:underline">View all purchases</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($purchases as $purchase): ?>
                <tr>
                    <td><?php echo $purchase->purchase_id; ?></td>
                    <td>
                        <code class="text-sm font-semibold"><?php echo htmlspecialchars($purchase->chalan_no); ?></code>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($purchase->supplier_name ?? 'N/A'); ?></strong>
                        <br>
                        <span class="text-sm text-gray-600"><?php echo htmlspecialchars($purchase->supplier_mobile ?? ''); ?></span>
                    </td>
                    <td><?php echo format_date($purchase->purchase_date); ?></td>
                    <td><?php echo format_currency($purchase->total_amount); ?></td>
                    <td><?php echo format_currency($purchase->vat ?? 0); ?></td>
                    <td class="font-semibold"><?php echo format_currency($purchase->grand_total_amount); ?></td>
                    <td>
                        <?php
                        $status = $purchase->payment_status ?? 'unpaid';
                        if ($status == 'paid') {
                            echo '<span class="badge badge-success">Paid</span>';
                        } elseif ($status == 'partial') {
                            echo '<span class="badge badge-warning">Partial</span>';
                        } else {
                            echo '<span class="badge badge-danger">Unpaid</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('purchases/view/' . $purchase->purchase_id); ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($status != 'paid'): ?>
                                <a href="<?php echo base_url('payments/add?purchase_id=' . $purchase->purchase_id); ?>"
                                   class="btn btn-sm btn-success" title="Add Payment">
                                    <i class="fas fa-money-bill"></i>
                                </a>
                            <?php endif; ?>
                            <button onclick="confirmDelete(<?php echo $purchase->purchase_id; ?>, '<?php echo addslashes($purchase->chalan_no); ?>')"
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
        <?php echo render_pagination($pagination, 'purchases'); ?>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<!-- Delete Confirmation Script -->
<script>
function confirmDelete(purchaseId, chalanNo) {
    Swal.fire({
        title: 'Delete Purchase?',
        text: `Are you sure you want to delete purchase "${chalanNo}"? This will also reverse accounting entries and stock changes.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('purchases/delete/'); ?>${purchaseId}`;
        }
    });
}
</script>
