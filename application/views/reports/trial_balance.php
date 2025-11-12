<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h1 class="page-title">Trial Balance</h1>
        <p class="text-gray-600 mt-1">As of <?php echo format_date($as_of_date); ?></p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i>
            Print
        </button>
        <button onclick="exportToExcel()" class="btn btn-success">
            <i class="fas fa-file-excel"></i>
            Export Excel
        </button>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-6">
    <div class="card-body">
        <form method="get" action="<?php echo base_url('reports/trial_balance'); ?>" class="flex items-center gap-4">
            <div class="flex-1">
                <?php echo form_input_group(
                    'as_of_date',
                    'As of Date',
                    $as_of_date,
                    false,
                    'date'
                ); ?>
            </div>
            <button type="submit" class="btn btn-primary mt-6">
                <i class="fas fa-search"></i>
                Generate Report
            </button>
        </form>
    </div>
</div>

<!-- Balance Status -->
<?php if ($is_balanced): ?>
    <div class="alert alert-success mb-6">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>Trial Balance is Balanced!</strong>
            <p>Total Debits = Total Credits (<?php echo format_currency($total_debit); ?>)</p>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger mb-6">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Trial Balance NOT Balanced!</strong>
            <p>Difference: <?php echo format_currency(abs($total_debit - $total_credit)); ?></p>
        </div>
    </div>
<?php endif; ?>

<!-- Trial Balance Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Account Balances</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table" id="trialBalanceTable">
                <thead>
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th>Account Type</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $account_types = ['asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity', 'income' => 'Income', 'expense' => 'Expenses'];
                    $current_type = '';

                    foreach ($accounts as $account):
                        // Add section header when type changes
                        if ($account->account_type != $current_type):
                            $current_type = $account->account_type;
                    ?>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="5" class="text-lg">
                                <?php echo strtoupper($account_types[$current_type] ?? $current_type); ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($account->debit_balance > 0 || $account->credit_balance > 0): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="font-mono"><?php echo htmlspecialchars($account->account_code); ?></td>
                            <td><?php echo htmlspecialchars($account->account_name); ?></td>
                            <td>
                                <span class="badge badge-<?php
                                    echo $account->account_type == 'asset' ? 'primary' :
                                         ($account->account_type == 'liability' ? 'danger' :
                                         ($account->account_type == 'income' ? 'success' : 'secondary'));
                                ?>">
                                    <?php echo ucfirst($account->account_type); ?>
                                </span>
                            </td>
                            <td class="text-right font-medium">
                                <?php echo $account->debit_balance > 0 ? format_currency($account->debit_balance) : '-'; ?>
                            </td>
                            <td class="text-right font-medium">
                                <?php echo $account->credit_balance > 0 ? format_currency($account->credit_balance) : '-'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-100 font-bold text-lg">
                    <tr>
                        <td colspan="3" class="text-right">TOTAL:</td>
                        <td class="text-right <?php echo $is_balanced ? 'text-success-600' : 'text-danger-600'; ?>">
                            <?php echo format_currency($total_debit); ?>
                        </td>
                        <td class="text-right <?php echo $is_balanced ? 'text-success-600' : 'text-danger-600'; ?>">
                            <?php echo format_currency($total_credit); ?>
                        </td>
                    </tr>
                    <?php if (!$is_balanced): ?>
                    <tr class="text-danger-600">
                        <td colspan="3" class="text-right">DIFFERENCE:</td>
                        <td colspan="2" class="text-right">
                            <?php echo format_currency(abs($total_debit - $total_credit)); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="card">
        <div class="card-body">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                    <i class="fas fa-list text-xl"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Total Accounts</div>
                    <div class="text-2xl font-bold"><?php echo count($accounts); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-success-100 text-success-600 flex items-center justify-center">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Total Debits</div>
                    <div class="text-2xl font-bold"><?php echo format_currency($total_debit); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-info-100 text-info-600 flex items-center justify-center">
                    <i class="fas fa-minus text-xl"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Total Credits</div>
                    <div class="text-2xl font-bold"><?php echo format_currency($total_credit); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    // Simple Excel export
    const table = document.getElementById('trialBalanceTable');
    const html = table.outerHTML;
    const url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    const downloadLink = document.createElement("a");
    downloadLink.href = url;
    downloadLink.download = 'trial_balance_<?php echo $as_of_date; ?>.xls';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
