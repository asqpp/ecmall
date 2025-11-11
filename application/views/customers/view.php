<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h1 class="page-title"><?php echo htmlspecialchars($customer->customer_name); ?></h1>
        <p class="text-gray-600 mt-1">Customer ID: #<?php echo $customer->customer_id; ?></p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?php echo base_url('customers'); ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to List
        </a>
        <a href="<?php echo base_url('customers/edit/' . $customer->customer_id); ?>" class="btn btn-secondary">
            <i class="fas fa-edit"></i>
            Edit Customer
        </a>
        <a href="<?php echo base_url('sales/add?customer_id=' . $customer->customer_id); ?>" class="btn btn-primary">
            <i class="fas fa-file-invoice"></i>
            Create Invoice
        </a>
    </div>
</div>

<!-- Customer Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <?php
    render_stat_card(
        'Total Purchases',
        format_currency($customer->total_purchase ?? 0),
        '<i class="fas fa-shopping-cart"></i> ' . ($customer->invoice_count ?? 0) . ' invoices',
        'fas fa-chart-line',
        'bg-gradient-primary',
        0
    );
    ?>

    <?php
    render_stat_card(
        'Total Paid',
        format_currency($customer->total_paid ?? 0),
        '<i class="fas fa-check-circle"></i> Received amount',
        'fas fa-check-double',
        'bg-gradient-success',
        100
    );
    ?>

    <?php
    render_stat_card(
        'Outstanding',
        format_currency($customer->outstanding ?? 0),
        '<i class="fas fa-exclamation-circle"></i> Amount due',
        'fas fa-money-bill-wave',
        'bg-gradient-warning',
        200
    );
    ?>

    <?php
    render_stat_card(
        'Credit Limit',
        format_currency($customer->credit_limit ?? 0),
        '<i class="fas fa-info-circle"></i> ' . (($customer->credit_limit ?? 0) == 0 ? 'Unlimited' : 'Limited'),
        'fas fa-credit-card',
        'bg-gradient-info',
        300
    );
    ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2">
        <!-- Customer Ledger -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="card-title">Customer Ledger</h3>
                <div class="flex items-center gap-2">
                    <button onclick="document.getElementById('filterForm').classList.toggle('hidden')" class="btn btn-sm btn-outline">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                    <button onclick="printLedger()" class="btn btn-sm btn-secondary">
                        <i class="fas fa-print"></i>
                        Print
                    </button>
                </div>
            </div>

            <!-- Filter Form -->
            <div id="filterForm" class="<?php echo (empty($from_date) && empty($to_date)) ? 'hidden' : ''; ?> border-b border-gray-200 px-6 py-4 bg-gray-50">
                <form method="get" action="<?php echo base_url('customers/view/' . $customer->customer_id); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <?php echo form_input_group(
                            'from_date',
                            'From Date',
                            $from_date ?? '',
                            false,
                            'date'
                        ); ?>
                    </div>
                    <div>
                        <?php echo form_input_group(
                            'to_date',
                            'To Date',
                            $to_date ?? '',
                            false,
                            'date'
                        ); ?>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-1">
                            <i class="fas fa-search"></i>
                            Apply
                        </button>
                        <?php if (!empty($from_date) || !empty($to_date)): ?>
                            <a href="<?php echo base_url('customers/view/' . $customer->customer_id); ?>" class="btn btn-outline">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <?php if (!empty($ledger)): ?>
                    <div class="overflow-x-auto">
                        <table class="table" id="ledgerTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ledger as $entry): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="whitespace-nowrap"><?php echo format_date($entry->date); ?></td>
                                        <td>
                                            <?php if ($entry->type == 'Invoice'): ?>
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-file-invoice"></i> Invoice
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-money-bill-wave"></i> Receipt
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="font-medium"><?php echo htmlspecialchars($entry->reference); ?></td>
                                        <td><?php echo htmlspecialchars($entry->description); ?></td>
                                        <td class="text-right font-medium">
                                            <?php echo $entry->debit > 0 ? format_currency($entry->debit) : '-'; ?>
                                        </td>
                                        <td class="text-right font-medium">
                                            <?php echo $entry->credit > 0 ? format_currency($entry->credit) : '-'; ?>
                                        </td>
                                        <td class="text-right font-bold <?php echo $entry->running_balance >= 0 ? 'text-warning-600' : 'text-success-600'; ?>">
                                            <?php echo format_currency(abs($entry->running_balance)); ?>
                                            <?php echo $entry->running_balance >= 0 ? ' Dr' : ' Cr'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-gray-50 font-bold">
                                <tr>
                                    <td colspan="4" class="text-right">Final Balance:</td>
                                    <td class="text-right">
                                        <?php
                                        $total_debit = array_sum(array_column($ledger, 'debit'));
                                        echo format_currency($total_debit);
                                        ?>
                                    </td>
                                    <td class="text-right">
                                        <?php
                                        $total_credit = array_sum(array_column($ledger, 'credit'));
                                        echo format_currency($total_credit);
                                        ?>
                                    </td>
                                    <td class="text-right text-lg">
                                        <?php
                                        $final_balance = end($ledger)->running_balance;
                                        ?>
                                        <span class="<?php echo $final_balance >= 0 ? 'text-warning-600' : 'text-success-600'; ?>">
                                            <?php echo format_currency(abs($final_balance)); ?>
                                            <?php echo $final_balance >= 0 ? ' Dr' : ' Cr'; ?>
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">No transactions found</h3>
                        <p class="text-gray-500 mb-6">This customer has no transaction history yet.</p>
                        <a href="<?php echo base_url('sales/add?customer_id=' . $customer->customer_id); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create First Invoice
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Contact Information</h3>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Mobile</div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-phone text-primary-600"></i>
                            <a href="tel:<?php echo htmlspecialchars($customer->customer_mobile); ?>" class="font-medium hover:text-primary-600">
                                <?php echo htmlspecialchars($customer->customer_mobile); ?>
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($customer->customer_email)): ?>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">Email</div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-envelope text-primary-600"></i>
                                <a href="mailto:<?php echo htmlspecialchars($customer->customer_email); ?>" class="font-medium hover:text-primary-600 break-all">
                                    <?php echo htmlspecialchars($customer->customer_email); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($customer->contact)): ?>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">Contact Person</div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-tie text-primary-600"></i>
                                <span class="font-medium"><?php echo htmlspecialchars($customer->contact); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Address -->
        <?php if (!empty($customer->customer_address_1) || !empty($customer->city)): ?>
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="card-title">Address</h3>
                </div>
                <div class="card-body">
                    <div class="flex items-start gap-2 text-gray-700">
                        <i class="fas fa-map-marker-alt text-primary-600 mt-1"></i>
                        <div>
                            <?php if (!empty($customer->customer_address_1)): ?>
                                <div><?php echo htmlspecialchars($customer->customer_address_1); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($customer->customer_address_2)): ?>
                                <div><?php echo htmlspecialchars($customer->customer_address_2); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($customer->city) || !empty($customer->state) || !empty($customer->zip)): ?>
                                <div>
                                    <?php echo htmlspecialchars($customer->city ?? ''); ?>
                                    <?php echo !empty($customer->state) ? ', ' . htmlspecialchars($customer->state) : ''; ?>
                                    <?php echo !empty($customer->zip) ? ' ' . htmlspecialchars($customer->zip) : ''; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($customer->country)): ?>
                                <div><?php echo htmlspecialchars($customer->country); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="card mt-6">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="space-y-2">
                    <a href="<?php echo base_url('sales/add?customer_id=' . $customer->customer_id); ?>" class="btn btn-primary w-full">
                        <i class="fas fa-file-invoice"></i>
                        New Invoice
                    </a>
                    <a href="<?php echo base_url('quotations/add?customer_id=' . $customer->customer_id); ?>" class="btn btn-secondary w-full">
                        <i class="fas fa-file-alt"></i>
                        New Quotation
                    </a>
                    <a href="<?php echo base_url('receipts/add?customer_id=' . $customer->customer_id); ?>" class="btn btn-success w-full">
                        <i class="fas fa-money-bill-wave"></i>
                        Record Receipt
                    </a>
                    <hr class="my-2">
                    <a href="<?php echo base_url('customers/edit/' . $customer->customer_id); ?>" class="btn btn-outline w-full">
                        <i class="fas fa-edit"></i>
                        Edit Customer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Script -->
<script>
function printLedger() {
    const printWindow = window.open('', '_blank');
    const ledgerTable = document.getElementById('ledgerTable').outerHTML;

    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Customer Ledger - <?php echo htmlspecialchars($customer->customer_name); ?></title>
            <link rel="stylesheet" href="<?php echo base_url('assets/css/output.css'); ?>">
            <style>
                @media print {
                    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body class="p-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold">Customer Ledger</h1>
                <h2 class="text-xl"><?php echo htmlspecialchars($customer->customer_name); ?></h2>
                <p class="text-gray-600">Customer ID: #<?php echo $customer->customer_id; ?></p>
                <?php if (!empty($from_date) || !empty($to_date)): ?>
                    <p class="text-sm text-gray-500">
                        Period: <?php echo $from_date ? format_date($from_date) : 'Beginning'; ?> to <?php echo $to_date ? format_date($to_date) : 'Today'; ?>
                    </p>
                <?php endif; ?>
            </div>
            ${ledgerTable}
            <div class="mt-8 text-sm text-gray-600">
                <p>Printed on: <?php echo date('d M Y h:i A'); ?></p>
                <p>Printed by: <?php echo $this->session->userdata('username'); ?></p>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();

    printWindow.onload = function() {
        printWindow.print();
    };
}
</script>
