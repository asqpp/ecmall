<?php card_start('Trial Balance', true); ?>

<!-- Date Filter -->
<div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
    <form method="get" action="<?php echo base_url('accounts/trial_balance'); ?>" class="flex gap-4 items-end">
        <div class="form-group">
            <label class="text-sm font-medium">As of Date</label>
            <input type="date"
                   name="as_of_date"
                   value="<?php echo $as_of_date; ?>"
                   class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Generate
        </button>

        <div class="flex-1"></div>

        <a href="<?php echo base_url('accounts'); ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Chart of Accounts
        </a>
    </form>
</div>

<!-- Date Header -->
<div class="text-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Trial Balance</h2>
    <p class="text-gray-600">As of <?php echo date('F d, Y', strtotime($as_of_date)); ?></p>
</div>

<!-- Trial Balance Table -->
<div class="overflow-x-auto">
    <?php table_start(['Account Code', 'Account Name', 'Type', 'Debit', 'Credit']); ?>
        <?php if (empty($accounts)): ?>
            <tr>
                <td colspan="5" class="text-center py-8 text-gray-500">
                    <i class="fas fa-balance-scale text-4xl mb-2"></i>
                    <p>No accounts found</p>
                </td>
            </tr>
        <?php else: ?>
            <?php
            $current_type = '';
            foreach ($accounts as $account):
                // Group by account type
                if ($current_type != $account->account_type):
                    $current_type = $account->account_type;
                    ?>
                    <tr class="bg-gray-100">
                        <td colspan="5" class="font-bold text-gray-900">
                            <?php echo strtoupper($current_type); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td><code><?php echo htmlspecialchars($account->account_code); ?></code></td>
                    <td><?php echo htmlspecialchars($account->account_name); ?></td>
                    <td>
                        <?php
                        $type_badges = [
                            'asset' => 'badge-primary',
                            'liability' => 'badge-danger',
                            'equity' => 'badge-success',
                            'income' => 'badge-success',
                            'expense' => 'badge-warning'
                        ];
                        $badge_class = $type_badges[$account->account_type] ?? 'badge-secondary';
                        ?>
                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($account->account_type); ?></span>
                    </td>
                    <td class="text-success-600 font-semibold">
                        <?php echo $account->debit_balance > 0 ? format_currency($account->debit_balance) : '-'; ?>
                    </td>
                    <td class="text-danger-600 font-semibold">
                        <?php echo $account->credit_balance > 0 ? format_currency($account->credit_balance) : '-'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Totals -->
            <tr class="bg-gray-900 text-white font-bold text-lg">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-success-300"><?php echo format_currency($total_debit); ?></td>
                <td class="text-danger-300"><?php echo format_currency($total_credit); ?></td>
            </tr>

            <!-- Balance Check -->
            <tr class="bg-<?php echo abs($total_debit - $total_credit) < 0.01 ? 'success' : 'danger'; ?>-100">
                <td colspan="3" class="text-right font-bold">DIFFERENCE:</td>
                <td colspan="2" class="font-bold text-<?php echo abs($total_debit - $total_credit) < 0.01 ? 'success' : 'danger'; ?>-600">
                    <?php
                    $difference = $total_debit - $total_credit;
                    if (abs($difference) < 0.01) {
                        echo '<i class="fas fa-check-circle"></i> Balanced';
                    } else {
                        echo '<i class="fas fa-exclamation-triangle"></i> ' . format_currency(abs($difference)) . ' (' . ($difference > 0 ? 'Debit' : 'Credit') . ' side higher)';
                    }
                    ?>
                </td>
            </tr>
        <?php endif; ?>
    <?php table_end(); ?>
</div>

<!-- Export Button -->
<?php if (!empty($accounts)): ?>
    <div class="mt-6 flex justify-end">
        <button onclick="exportTrialBalance()" class="btn btn-secondary">
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
function exportTrialBalance() {
    let csv = 'Trial Balance - As of <?php echo $as_of_date; ?>\n';
    csv += 'Account Code,Account Name,Type,Debit,Credit\n';

    <?php foreach ($accounts as $account): ?>
        csv += '<?php echo $account->account_code; ?>,';
        csv += '"<?php echo addslashes($account->account_name); ?>",';
        csv += '<?php echo ucfirst($account->account_type); ?>,';
        csv += '<?php echo $account->debit_balance; ?>,';
        csv += '<?php echo $account->credit_balance; ?>\n';
    <?php endforeach; ?>

    csv += ',TOTAL,,<?php echo $total_debit; ?>,<?php echo $total_credit; ?>\n';

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'trial_balance_<?php echo $as_of_date; ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
