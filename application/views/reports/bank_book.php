<?php card_start('Bank Book', true); ?>

<!-- Date Filter -->
<div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
    <form method="get" action="<?php echo base_url('reports/bank_book'); ?>" class="flex gap-4 items-end">
        <div class="form-group">
            <label class="text-sm font-medium">Bank Account</label>
            <input type="text"
                   name="bank_account"
                   value="<?php echo htmlspecialchars($bank_account); ?>"
                   class="form-control"
                   placeholder="e.g., BANK">
        </div>

        <div class="form-group">
            <label class="text-sm font-medium">From Date</label>
            <input type="date"
                   name="from_date"
                   value="<?php echo $from_date; ?>"
                   class="form-control">
        </div>

        <div class="form-group">
            <label class="text-sm font-medium">To Date</label>
            <input type="date"
                   name="to_date"
                   value="<?php echo $to_date; ?>"
                   class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Generate
        </button>
    </form>
</div>

<!-- Report Header -->
<div class="text-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Bank Book</h2>
    <p class="text-gray-600">
        Account: <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($bank_account); ?></code>
    </p>
    <p class="text-gray-600">
        From <?php echo date('F d, Y', strtotime($from_date)); ?>
        to <?php echo date('F d, Y', strtotime($to_date)); ?>
    </p>
</div>

<!-- Bank Book Entries -->
<?php if (empty($entries)): ?>
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-university text-4xl mb-2"></i>
        <p>No bank transactions found for selected period</p>
    </div>
<?php else: ?>
    <div class="overflow-x-auto">
        <?php table_start(['Date', 'Description', 'Reference', 'Deposit (Dr)', 'Withdrawal (Cr)', 'Balance']); ?>
            <?php
            $running_balance = 0;
            $total_deposits = 0;
            $total_withdrawals = 0;
            ?>
            <?php foreach ($entries as $entry): ?>
                <?php
                $running_balance += ($entry->debit - $entry->credit);
                $total_deposits += $entry->debit;
                $total_withdrawals += $entry->credit;
                ?>
                <tr>
                    <td><?php echo format_date($entry->date); ?></td>
                    <td><?php echo htmlspecialchars($entry->narration); ?></td>
                    <td>
                        <?php if ($entry->reference_type && $entry->reference_id): ?>
                            <code class="text-xs"><?php echo htmlspecialchars($entry->reference_type . '#' . $entry->reference_id); ?></code>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-success-600 font-semibold">
                        <?php echo $entry->debit > 0 ? format_currency($entry->debit) : '-'; ?>
                    </td>
                    <td class="text-danger-600 font-semibold">
                        <?php echo $entry->credit > 0 ? format_currency($entry->credit) : '-'; ?>
                    </td>
                    <td class="font-bold">
                        <?php
                        $color = $running_balance >= 0 ? 'text-success-600' : 'text-danger-600';
                        ?>
                        <span class="<?php echo $color; ?>">
                            <?php echo format_currency(abs($running_balance)); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Totals -->
            <tr class="bg-gray-900 text-white font-bold text-lg">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-success-300"><?php echo format_currency($total_deposits); ?></td>
                <td class="text-danger-300"><?php echo format_currency($total_withdrawals); ?></td>
                <td class="text-white">
                    <?php echo format_currency(abs($running_balance)); ?>
                </td>
            </tr>
        <?php table_end(); ?>
    </div>

    <!-- Export Button -->
    <div class="mt-6 flex justify-end gap-4">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> Print
        </button>
        <button onclick="exportToCSV()" class="btn btn-secondary">
            <i class="fas fa-download"></i> Export to CSV
        </button>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<style>
.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}
.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.form-group {
    margin-bottom: 0;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
}
</style>

<script>
function exportToCSV() {
    let csv = 'Bank Book - <?php echo $bank_account; ?> - <?php echo $from_date; ?> to <?php echo $to_date; ?>\n';
    csv += 'Date,Description,Deposit,Withdrawal,Balance\n';

    <?php foreach ($entries as $entry): ?>
        csv += '<?php echo $entry->date; ?>,';
        csv += '"<?php echo addslashes($entry->narration); ?>",';
        csv += '<?php echo $entry->debit; ?>,';
        csv += '<?php echo $entry->credit; ?>,';
        csv += '<?php echo ($entry->debit - $entry->credit); ?>\n';
    <?php endforeach; ?>

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'bank_book_<?php echo $from_date; ?>_to_<?php echo $to_date; ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
