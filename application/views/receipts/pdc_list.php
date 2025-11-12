<?php card_start('Post-Dated Cheques (Customer Receipts)', true); ?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Manage post-dated cheques from customers</p>
    <a href="<?php echo base_url('receipts'); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Receipts
    </a>
</div>

<!-- Due for Clearance -->
<?php if (!empty($due_clearance)): ?>
    <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-warning-900 mb-3">
            <i class="fas fa-exclamation-triangle"></i> PDCs Due for Clearance (<?php echo count($due_clearance); ?>)
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-warning-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Cheque Date</th>
                        <th class="px-4 py-2 text-left">Customer</th>
                        <th class="px-4 py-2 text-left">Cheque No</th>
                        <th class="px-4 py-2 text-left">Bank</th>
                        <th class="px-4 py-2 text-left">Amount</th>
                        <th class="px-4 py-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($due_clearance as $pdc): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?php echo format_date($pdc->chqdate); ?></td>
                            <td class="px-4 py-2">
                                <strong><?php echo htmlspecialchars($pdc->customer_name ?? 'N/A'); ?></strong>
                            </td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($pdc->chqno); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($pdc->bank); ?></td>
                            <td class="px-4 py-2 font-semibold"><?php echo format_currency($pdc->amount); ?></td>
                            <td class="px-4 py-2 text-center">
                                <button onclick="clearPDC(<?php echo $pdc->slno; ?>, '<?php echo addslashes($pdc->chqno); ?>')"
                                        class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i> Clear
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- All Pending PDCs -->
<h3 class="text-lg font-semibold mb-4">All Pending PDCs</h3>
<div class="overflow-x-auto">
    <?php table_start(['Date', 'Cheque Date', 'Customer', 'Cheque No', 'Bank', 'Amount', 'Particulars', 'Status']); ?>
        <?php if (empty($pending_pdcs)): ?>
            <tr>
                <td colspan="8" class="text-center py-8 text-gray-500">
                    <i class="fas fa-check-circle text-4xl mb-2 text-success-500"></i>
                    <p>No pending PDCs!</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($pending_pdcs as $pdc): ?>
                <?php
                $is_due = ($pdc->chqdate <= date('Y-m-d'));
                ?>
                <tr class="<?php echo $is_due ? 'bg-warning-50' : ''; ?>">
                    <td><?php echo format_date($pdc->tdate); ?></td>
                    <td class="font-semibold"><?php echo format_date($pdc->chqdate); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($pdc->customer_name ?? 'N/A'); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($pdc->chqno); ?></td>
                    <td><?php echo htmlspecialchars($pdc->bank); ?></td>
                    <td class="font-semibold"><?php echo format_currency($pdc->amount); ?></td>
                    <td class="text-sm text-gray-600"><?php echo htmlspecialchars($pdc->particulars); ?></td>
                    <td>
                        <?php if ($is_due): ?>
                            <span class="badge badge-warning">Due</span>
                        <?php else: ?>
                            <span class="badge badge-info">Future</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php table_end(); ?>
</div>

<?php card_end(); ?>

<script>
function clearPDC(pdcId, chequeNo) {
    Swal.fire({
        title: 'Clear PDC?',
        html: `
            <p>Cheque: <strong>${chequeNo}</strong></p>
            <p class="text-sm text-gray-600 mt-2">This will:</p>
            <ul class="text-sm text-left mt-2">
                <li>• Post Dr: Bank Account</li>
                <li>• Post Cr: PDC Receivable</li>
                <li>• Mark PDC as cleared</li>
            </ul>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Clearance Date:</label>
                <input type="date" id="clearance_date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border rounded">
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, clear it!',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            return document.getElementById('clearance_date').value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?php echo base_url('receipts/clear_pdc/'); ?>${pdcId}`;

            const dateInput = document.createElement('input');
            dateInput.type = 'hidden';
            dateInput.name = 'clearance_date';
            dateInput.value = result.value;

            form.appendChild(dateInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
