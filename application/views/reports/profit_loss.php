<?php card_start('Profit & Loss Statement', true); ?>

<!-- Date Filter -->
<div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
    <form method="get" action="<?php echo base_url('reports/profit_loss'); ?>" class="flex gap-4 items-end">
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
    <h2 class="text-2xl font-bold text-gray-900">Profit & Loss Statement</h2>
    <p class="text-gray-600">
        For the period from <?php echo date('F d, Y', strtotime($from_date)); ?>
        to <?php echo date('F d, Y', strtotime($to_date)); ?>
    </p>
</div>

<!-- P&L Statement -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Income Section -->
    <div>
        <?php card_start('INCOME'); ?>
        <div class="overflow-x-auto">
            <?php table_start(['Account', 'Amount']); ?>
                <?php if (empty($income_accounts)): ?>
                    <tr>
                        <td colspan="2" class="text-center text-gray-500">No income accounts</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($income_accounts as $account): ?>
                        <?php if ($account->amount > 0): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($account->account_name); ?></strong>
                                    <br><small class="text-gray-600"><?php echo htmlspecialchars($account->account_code); ?></small>
                                </td>
                                <td class="text-success-600 font-semibold text-right">
                                    <?php echo format_currency($account->amount); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <tr class="bg-success-50 font-bold text-lg">
                        <td>TOTAL INCOME</td>
                        <td class="text-success-600 text-right">
                            <?php echo format_currency($total_income); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php table_end(); ?>
        </div>
        <?php card_end(); ?>
    </div>

    <!-- Expenses Section -->
    <div>
        <?php card_start('EXPENSES'); ?>
        <div class="overflow-x-auto">
            <?php table_start(['Account', 'Amount']); ?>
                <?php if (empty($expense_accounts)): ?>
                    <tr>
                        <td colspan="2" class="text-center text-gray-500">No expense accounts</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expense_accounts as $account): ?>
                        <?php if ($account->amount > 0): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($account->account_name); ?></strong>
                                    <br><small class="text-gray-600"><?php echo htmlspecialchars($account->account_code); ?></small>
                                </td>
                                <td class="text-danger-600 font-semibold text-right">
                                    <?php echo format_currency($account->amount); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <tr class="bg-danger-50 font-bold text-lg">
                        <td>TOTAL EXPENSES</td>
                        <td class="text-danger-600 text-right">
                            <?php echo format_currency($total_expenses); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php table_end(); ?>
        </div>
        <?php card_end(); ?>
    </div>
</div>

<!-- Net Profit/Loss -->
<div class="mt-6">
    <?php
    $is_profit = $net_profit >= 0;
    $bg_color = $is_profit ? 'bg-success-50' : 'bg-danger-50';
    $border_color = $is_profit ? 'border-success-200' : 'border-danger-200';
    $text_color = $is_profit ? 'text-success-600' : 'text-danger-600';
    ?>
    <div class="<?php echo $bg_color; ?> border <?php echo $border_color; ?> rounded-lg p-6">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">
                    <?php echo $is_profit ? 'NET PROFIT' : 'NET LOSS'; ?>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    Total Income - Total Expenses
                </p>
            </div>
            <div class="text-right">
                <div class="text-4xl font-bold <?php echo $text_color; ?>">
                    <?php echo format_currency(abs($net_profit)); ?>
                </div>
                <?php if ($total_income > 0): ?>
                    <div class="text-sm text-gray-600 mt-2">
                        Profit Margin: <?php echo number_format(($net_profit / $total_income) * 100, 2); ?>%
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
    <div class="bg-success-50 border border-success-200 rounded-lg p-4">
        <div class="text-sm text-gray-600 mb-1">Total Income</div>
        <div class="text-2xl font-bold text-success-600"><?php echo format_currency($total_income); ?></div>
    </div>

    <div class="bg-danger-50 border border-danger-200 rounded-lg p-4">
        <div class="text-sm text-gray-600 mb-1">Total Expenses</div>
        <div class="text-2xl font-bold text-danger-600"><?php echo format_currency($total_expenses); ?></div>
    </div>

    <div class="<?php echo $bg_color; ?> border <?php echo $border_color; ?> rounded-lg p-4">
        <div class="text-sm text-gray-600 mb-1"><?php echo $is_profit ? 'Net Profit' : 'Net Loss'; ?></div>
        <div class="text-2xl font-bold <?php echo $text_color; ?>"><?php echo format_currency(abs($net_profit)); ?></div>
    </div>
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
    let csv = 'Profit & Loss Statement\n';
    csv += 'Period: <?php echo $from_date; ?> to <?php echo $to_date; ?>\n\n';

    csv += 'INCOME\n';
    csv += 'Account,Amount\n';
    <?php foreach ($income_accounts as $account): ?>
        <?php if ($account->amount > 0): ?>
            csv += '"<?php echo addslashes($account->account_name); ?>",<?php echo $account->amount; ?>\n';
        <?php endif; ?>
    <?php endforeach; ?>
    csv += 'TOTAL INCOME,<?php echo $total_income; ?>\n\n';

    csv += 'EXPENSES\n';
    csv += 'Account,Amount\n';
    <?php foreach ($expense_accounts as $account): ?>
        <?php if ($account->amount > 0): ?>
            csv += '"<?php echo addslashes($account->account_name); ?>",<?php echo $account->amount; ?>\n';
        <?php endif; ?>
    <?php endforeach; ?>
    csv += 'TOTAL EXPENSES,<?php echo $total_expenses; ?>\n\n';

    csv += '<?php echo $net_profit >= 0 ? 'NET PROFIT' : 'NET LOSS'; ?>,<?php echo abs($net_profit); ?>\n';

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'profit_loss_<?php echo $from_date; ?>_to_<?php echo $to_date; ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
