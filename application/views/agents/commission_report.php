<?php card_start('Agent Commission Report', true); ?>

<!-- Filters -->
<div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
    <form method="get" action="<?php echo base_url('agents/commission_report'); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="form-group">
            <label class="text-sm font-medium">Agent</label>
            <select name="agent_id" class="form-control">
                <option value="">All Agents</option>
                <?php foreach ($agents as $agent): ?>
                    <option value="<?php echo $agent->agent_id; ?>"
                            <?php echo ($filters['agent_id'] == $agent->agent_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($agent->agent_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

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
                <i class="fas fa-search"></i> Generate Report
            </button>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<?php
$total_sales = 0;
$total_commission = 0;
$total_paid = 0;
$total_due = 0;

foreach ($report as $row) {
    $total_sales += $row->total_sales;
    $total_commission += $row->total_commission;
    $total_paid += $row->commission_paid;
    $total_due += $row->commission_due;
}
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
        <div class="text-sm text-gray-600 mb-1">Total Sales</div>
        <div class="text-2xl font-bold text-primary-600"><?php echo format_currency($total_sales); ?></div>
    </div>

    <div class="bg-success-50 border border-success-200 rounded-lg p-4">
        <div class="text-sm text-gray-600 mb-1">Total Commission</div>
        <div class="text-2xl font-bold text-success-600"><?php echo format_currency($total_commission); ?></div>
    </div>

    <div class="bg-info-50 border border-info-200 rounded-lg p-4">
        <div class="text-sm text-gray-600 mb-1">Commission Paid</div>
        <div class="text-2xl font-bold text-info-600"><?php echo format_currency($total_paid); ?></div>
    </div>

    <div class="bg-danger-50 border border-danger-200 rounded-lg p-4">
        <div class="text-sm text-gray-600 mb-1">Commission Due</div>
        <div class="text-2xl font-bold text-danger-600"><?php echo format_currency($total_due); ?></div>
    </div>
</div>

<!-- Report Table -->
<div class="overflow-x-auto">
    <?php table_start(['Agent', 'Commission Rate', 'Invoices', 'Total Sales', 'Commission', 'Paid', 'Due', 'Actions']); ?>
        <?php if (empty($report)): ?>
            <tr>
                <td colspan="8" class="text-center py-8 text-gray-500">
                    <i class="fas fa-chart-line text-4xl mb-2"></i>
                    <p>No commission data found for selected filters</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($report as $row): ?>
                <tr>
                    <td>
                        <a href="<?php echo base_url('agents/view/' . $row->agent_id); ?>"
                           class="text-primary-600 hover:underline font-semibold">
                            <?php echo htmlspecialchars($row->agent_name); ?>
                        </a>
                    </td>
                    <td><span class="badge badge-success"><?php echo number_format($row->commission_rate, 2); ?>%</span></td>
                    <td class="text-center"><?php echo $row->total_invoices; ?></td>
                    <td class="font-semibold"><?php echo format_currency($row->total_sales); ?></td>
                    <td class="font-semibold text-success-600"><?php echo format_currency($row->total_commission); ?></td>
                    <td class="text-info-600"><?php echo format_currency($row->commission_paid); ?></td>
                    <td class="font-semibold text-danger-600"><?php echo format_currency($row->commission_due); ?></td>
                    <td>
                        <a href="<?php echo base_url('agents/view/' . $row->agent_id); ?>"
                           class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Totals Row -->
            <tr class="bg-gray-100 font-bold">
                <td colspan="2" class="text-right">TOTALS:</td>
                <td class="text-center"><?php echo array_sum(array_column($report, 'total_invoices')); ?></td>
                <td><?php echo format_currency($total_sales); ?></td>
                <td class="text-success-600"><?php echo format_currency($total_commission); ?></td>
                <td class="text-info-600"><?php echo format_currency($total_paid); ?></td>
                <td class="text-danger-600"><?php echo format_currency($total_due); ?></td>
                <td></td>
            </tr>
        <?php endif; ?>
    <?php table_end(); ?>
</div>

<!-- Export Button -->
<?php if (!empty($report)): ?>
    <div class="mt-6 flex justify-end">
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
    // Simple CSV export
    let csv = 'Agent,Commission Rate,Invoices,Total Sales,Commission,Paid,Due\n';

    <?php foreach ($report as $row): ?>
        csv += '<?php echo addslashes($row->agent_name); ?>,';
        csv += '<?php echo number_format($row->commission_rate, 2); ?>%,';
        csv += '<?php echo $row->total_invoices; ?>,';
        csv += '<?php echo $row->total_sales; ?>,';
        csv += '<?php echo $row->total_commission; ?>,';
        csv += '<?php echo $row->commission_paid; ?>,';
        csv += '<?php echo $row->commission_due; ?>\n';
    <?php endforeach; ?>

    // Add totals
    csv += 'TOTALS,,<?php echo array_sum(array_column($report, 'total_invoices')); ?>,';
    csv += '<?php echo $total_sales; ?>,<?php echo $total_commission; ?>,';
    csv += '<?php echo $total_paid; ?>,<?php echo $total_due; ?>\n';

    // Download
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'agent_commission_report_<?php echo date('Y-m-d'); ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
