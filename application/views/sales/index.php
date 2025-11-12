<?php card_start('Sales Invoices', true); ?>

<!-- Header Actions -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div class="flex gap-4 w-full md:w-auto">
        <form method="get" action="<?php echo base_url('sales'); ?>" class="flex gap-2 flex-wrap">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search invoices..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">

            <input type="date"
                   name="from_date"
                   value="<?php echo $from_date; ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg">

            <input type="date"
                   name="to_date"
                   value="<?php echo $to_date; ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg">

            <select name="payment_status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="unpaid" <?php echo $payment_status == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                <option value="partial" <?php echo $payment_status == 'partial' ? 'selected' : ''; ?>>Partial</option>
                <option value="paid" <?php echo $payment_status == 'paid' ? 'selected' : ''; ?>>Paid</option>
            </select>

            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>

            <?php if ($search || $payment_status || $from_date): ?>
                <a href="<?php echo base_url('sales'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="flex gap-2">
        <a href="<?php echo base_url('sales/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Invoice
        </a>
    </div>
</div>

<!-- Invoices Table -->
<div class="overflow-x-auto">
    <?php table_start(['ID', 'Invoice#', 'Date', 'Customer', 'Total', 'VAT', 'Grand Total', 'Payment', 'Actions']); ?>
        <?php if (empty($invoices)): ?>
            <tr>
                <td colspan="9" class="text-center py-8 text-gray-500">
                    <i class="fas fa-file-invoice text-4xl mb-2"></i>
                    <p>No invoices found</p>
                    <?php if ($search || $payment_status): ?>
                        <a href="<?php echo base_url('sales'); ?>" class="text-primary-600 hover:underline">View all invoices</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><?php echo $inv->invoice_id; ?></td>
                    <td>
                        <code class="text-sm font-semibold bg-gray-100 px-2 py-1 rounded">
                            <?php echo htmlspecialchars($inv->invoice); ?>
                        </code>
                    </td>
                    <td><?php echo format_date($inv->date); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($inv->customer_name ?? 'N/A'); ?></strong>
                        <br>
                        <span class="text-sm text-gray-600"><?php echo htmlspecialchars($inv->customer_mobile ?? ''); ?></span>
                    </td>
                    <td><?php echo format_currency($inv->total); ?></td>
                    <td><?php echo format_currency($inv->vat); ?></td>
                    <td class="font-semibold text-lg"><?php echo format_currency($inv->grand_total); ?></td>
                    <td>
                        <?php
                        $status = $inv->payment_status ?? 'unpaid';
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
                            <a href="<?php echo base_url('sales/view/' . $inv->invoice_id); ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo base_url('sales/print_invoice/' . $inv->invoice_id); ?>"
                               class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                            <?php if ($status != 'paid'): ?>
                                <a href="<?php echo base_url('sales/edit/' . $inv->invoice_id); ?>"
                                   class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo base_url('receipts/add?invoice_id=' . $inv->invoice_id); ?>"
                                   class="btn btn-sm btn-success" title="Add Receipt">
                                    <i class="fas fa-money-bill"></i>
                                </a>
                                <button onclick="confirmDelete(<?php echo $inv->invoice_id; ?>, '<?php echo addslashes($inv->invoice); ?>')"
                                        class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
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
        <?php echo render_pagination($pagination, 'sales'); ?>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<script>
function confirmDelete(invoiceId, invoiceNo) {
    Swal.fire({
        title: 'Delete Invoice?',
        text: `Are you sure you want to delete invoice "${invoiceNo}"? This will also reverse stock and accounting entries.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('sales/delete/'); ?>${invoiceId}`;
        }
    });
}
</script>
