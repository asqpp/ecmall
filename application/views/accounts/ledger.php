<div class="mb-6">
    <a href="<?php echo base_url('accounts/view/' . $account->account_id); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
        <i class="fas fa-arrow-left"></i> Back to Account Details
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Account Ledger</h1>
    <p class="text-gray-600">
        <strong><?php echo htmlspecialchars($account->account_name); ?></strong>
        (<code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($account->account_code); ?></code>)
    </p>
</div>

<?php card_start('Account Ledger'); ?>

<!-- Filters -->
<div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
    <form method="get" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="form-group">
            <label class="text-sm font-medium">From Date</label>
            <input type="date"
                   name="from_date"
                   value="<?php echo $filters['from_date'] ?? ''; ?>"
                   class="form-control">
        </div>

        <div class="form-group">
            <label class="text-sm font-medium">To Date</label>
            <input type="date"
                   name="to_date"
                   value="<?php echo $filters['to_date'] ?? ''; ?>"
                   class="form-control">
        </div>

        <div class="form-group flex items-end">
            <button type="submit" class="btn btn-primary w-full">
                <i class="fas fa-search"></i> Filter
            </button>
            <?php if ($filters['from_date'] || $filters['to_date']): ?>
                <a href="<?php echo base_url('accounts/ledger/' . $account->account_id); ?>" class="btn btn-outline ml-2">
                    Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Opening Balance -->
<?php if ($opening_balance != 0): ?>
    <div class="mb-4 p-3 bg-info-50 border border-info-200 rounded-lg">
        <div class="flex justify-between items-center">
            <span class="font-semibold">Opening Balance:</span>
            <span class="font-semibold text-primary-600">
                <?php echo format_currency(abs($opening_balance)); ?>
                <?php echo $opening_balance >= 0 ? 'Dr' : 'Cr'; ?>
            </span>
        </div>
    </div>
<?php endif; ?>

<!-- Ledger Entries -->
<?php if (empty($ledger_entries)): ?>
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-receipt text-4xl mb-2"></i>
        <p>No transactions found for selected period</p>
    </div>
<?php else: ?>
    <div class="overflow-x-auto">
        <?php table_start(['Date', 'Description', 'Ref', 'Debit', 'Credit', 'Balance']); ?>
            <?php
            $total_debit = 0;
            $total_credit = 0;
            ?>
            <?php foreach ($ledger_entries as $entry): ?>
                <?php
                $total_debit += $entry->debit;
                $total_credit += $entry->credit;
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
                        $balance = $entry->running_balance;
                        $color = $balance >= 0 ? 'text-success-600' : 'text-danger-600';
                        ?>
                        <span class="<?php echo $color; ?>">
                            <?php echo format_currency(abs($balance)); ?>
                            <?php echo $balance >= 0 ? 'Dr' : 'Cr'; ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Totals -->
            <tr class="bg-gray-100 font-bold">
                <td colspan="3" class="text-right">TOTALS:</td>
                <td class="text-success-600"><?php echo format_currency($total_debit); ?></td>
                <td class="text-danger-600"><?php echo format_currency($total_credit); ?></td>
                <td>
                    <?php
                    $closing_balance = ($opening_balance + $total_debit - $total_credit);
                    $color = $closing_balance >= 0 ? 'text-success-600' : 'text-danger-600';
                    ?>
                    <span class="<?php echo $color; ?>">
                        <?php echo format_currency(abs($closing_balance)); ?>
                        <?php echo $closing_balance >= 0 ? 'Dr' : 'Cr'; ?>
                    </span>
                </td>
            </tr>
        <?php table_end(); ?>
    </div>

    <!-- Export Button -->
    <div class="mt-6 flex justify-end">
        <button onclick="exportLedger()" class="btn btn-secondary">
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
function exportLedger() {
    let csv = 'Date,Description,Debit,Credit,Balance\n';

    <?php if ($opening_balance != 0): ?>
        csv += ',Opening Balance,<?php echo $opening_balance > 0 ? $opening_balance : 0; ?>,<?php echo $opening_balance < 0 ? abs($opening_balance) : 0; ?>,<?php echo $opening_balance; ?>\n';
    <?php endif; ?>

    <?php foreach ($ledger_entries as $entry): ?>
        csv += '<?php echo $entry->date; ?>,';
        csv += '"<?php echo addslashes($entry->narration); ?>",';
        csv += '<?php echo $entry->debit; ?>,';
        csv += '<?php echo $entry->credit; ?>,';
        csv += '<?php echo $entry->running_balance; ?>\n';
    <?php endforeach; ?>

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'ledger_<?php echo $account->account_code; ?>_<?php echo date('Y-m-d'); ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
