<?php card_start('Day Book', true); ?>

<!-- Date Filter -->
<div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
    <form method="get" action="<?php echo base_url('reports/day_book'); ?>" class="flex gap-4 items-end">
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
    <h2 class="text-2xl font-bold text-gray-900">Day Book</h2>
    <p class="text-gray-600">
        From <?php echo date('F d, Y', strtotime($from_date)); ?>
        to <?php echo date('F d, Y', strtotime($to_date)); ?>
    </p>
</div>

<!-- Day Book Entries -->
<?php if (empty($entries)): ?>
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-book text-4xl mb-2"></i>
        <p>No entries found for selected period</p>
    </div>
<?php else: ?>
    <div class="overflow-x-auto">
        <?php table_start(['Entry#', 'Date', 'Account Code', 'Description', 'Debit', 'Credit']); ?>
            <?php
            $total_debit = 0;
            $total_credit = 0;
            $current_date = '';
            ?>
            <?php foreach ($entries as $entry): ?>
                <?php
                $total_debit += $entry->debit;
                $total_credit += $entry->credit;

                // Show date separator
                $entry_date = date('Y-m-d', strtotime($entry->date));
                if ($current_date != $entry_date) {
                    $current_date = $entry_date;
                    ?>
                    <tr class="bg-gray-100">
                        <td colspan="6" class="font-bold text-gray-900">
                            <?php echo date('l, F d, Y', strtotime($entry->date)); ?>
                        </td>
                    </tr>
                <?php } ?>

                <tr>
                    <td><?php echo $entry->id; ?></td>
                    <td><?php echo date('H:i', strtotime($entry->date)); ?></td>
                    <td><code><?php echo htmlspecialchars($entry->account_code); ?></code></td>
                    <td>
                        <?php echo htmlspecialchars($entry->narration); ?>
                        <?php if ($entry->reference_type && $entry->reference_id): ?>
                            <br><small class="text-gray-500"><?php echo htmlspecialchars($entry->reference_type . ' #' . $entry->reference_id); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-success-600 font-semibold">
                        <?php echo $entry->debit > 0 ? format_currency($entry->debit) : '-'; ?>
                    </td>
                    <td class="text-danger-600 font-semibold">
                        <?php echo $entry->credit > 0 ? format_currency($entry->credit) : '-'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Totals -->
            <tr class="bg-gray-900 text-white font-bold text-lg">
                <td colspan="4" class="text-right">TOTAL:</td>
                <td class="text-success-300"><?php echo format_currency($total_debit); ?></td>
                <td class="text-danger-300"><?php echo format_currency($total_credit); ?></td>
            </tr>

            <!-- Balance Check -->
            <tr class="bg-<?php echo abs($total_debit - $total_credit) < 0.01 ? 'success' : 'danger'; ?>-100">
                <td colspan="4" class="text-right font-bold">DIFFERENCE:</td>
                <td colspan="2" class="font-bold text-<?php echo abs($total_debit - $total_credit) < 0.01 ? 'success' : 'danger'; ?>-600">
                    <?php
                    $difference = $total_debit - $total_credit;
                    if (abs($difference) < 0.01) {
                        echo '<i class="fas fa-check-circle"></i> Balanced';
                    } else {
                        echo '<i class="fas fa-exclamation-triangle"></i> ' . format_currency(abs($difference));
                    }
                    ?>
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
    let csv = 'Day Book - <?php echo $from_date; ?> to <?php echo $to_date; ?>\n';
    csv += 'Entry#,Date,Account Code,Description,Debit,Credit\n';

    <?php foreach ($entries as $entry): ?>
        csv += '<?php echo $entry->id; ?>,';
        csv += '<?php echo $entry->date; ?>,';
        csv += '<?php echo $entry->account_code; ?>,';
        csv += '"<?php echo addslashes($entry->narration); ?>",';
        csv += '<?php echo $entry->debit; ?>,';
        csv += '<?php echo $entry->credit; ?>\n';
    <?php endforeach; ?>

    csv += ',,TOTAL,,<?php echo $total_debit; ?>,<?php echo $total_credit; ?>\n';

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'day_book_<?php echo $from_date; ?>_to_<?php echo $to_date; ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
