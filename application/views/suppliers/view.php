<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h1 class="page-title"><?php echo htmlspecialchars($supplier->supplier_name); ?></h1>
        <p class="text-gray-600 mt-1">Supplier ID: #<?php echo $supplier->supplier_id; ?></p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?php echo base_url('suppliers'); ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to List
        </a>
        <a href="<?php echo base_url('suppliers/edit/' . $supplier->supplier_id); ?>" class="btn btn-secondary">
            <i class="fas fa-edit"></i>
            Edit Supplier
        </a>
        <a href="<?php echo base_url('purchases/add?supplier_id=' . $supplier->supplier_id); ?>" class="btn btn-primary">
            <i class="fas fa-shopping-cart"></i>
            Create Purchase
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2">
        <!-- Supplier Ledger -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="card-title">Supplier Ledger</h3>
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
                <form method="get" action="<?php echo base_url('suppliers/view/' . $supplier->supplier_id); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                            <a href="<?php echo base_url('suppliers/view/' . $supplier->supplier_id); ?>" class="btn btn-outline">
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
                                            <?php if ($entry->type == 'Purchase'): ?>
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-shopping-cart"></i> Purchase
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-money-bill-wave"></i> Payment
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
                                        <td class="text-right font-bold <?php echo $entry->running_balance >= 0 ? 'text-danger-600' : 'text-success-600'; ?>">
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
                                        <span class="<?php echo $final_balance >= 0 ? 'text-danger-600' : 'text-success-600'; ?>">
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
                        <p class="text-gray-500 mb-6">This supplier has no transaction history yet.</p>
                        <a href="<?php echo base_url('purchases/add?supplier_id=' . $supplier->supplier_id); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create First Purchase
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
                            <i class="fas fa-phone text-blue-600"></i>
                            <a href="tel:<?php echo htmlspecialchars($supplier->supplier_mobile); ?>" class="font-medium hover:text-blue-600">
                                <?php echo htmlspecialchars($supplier->supplier_mobile); ?>
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($supplier->emailnumber)): ?>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">Email</div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-envelope text-blue-600"></i>
                                <a href="mailto:<?php echo htmlspecialchars($supplier->emailnumber); ?>" class="font-medium hover:text-blue-600 break-all">
                                    <?php echo htmlspecialchars($supplier->emailnumber); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($supplier->contact)): ?>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">Contact Person</div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-tie text-blue-600"></i>
                                <span class="font-medium"><?php echo htmlspecialchars($supplier->contact); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Address -->
        <?php if (!empty($supplier->address) || !empty($supplier->city)): ?>
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="card-title">Address</h3>
                </div>
                <div class="card-body">
                    <div class="flex items-start gap-2 text-gray-700">
                        <i class="fas fa-map-marker-alt text-blue-600 mt-1"></i>
                        <div>
                            <?php if (!empty($supplier->address)): ?>
                                <div><?php echo nl2br(htmlspecialchars($supplier->address)); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($supplier->city) || !empty($supplier->state) || !empty($supplier->zip)): ?>
                                <div class="mt-1">
                                    <?php echo htmlspecialchars($supplier->city ?? ''); ?>
                                    <?php echo !empty($supplier->state) ? ', ' . htmlspecialchars($supplier->state) : ''; ?>
                                    <?php echo !empty($supplier->zip) ? ' ' . htmlspecialchars($supplier->zip) : ''; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($supplier->country)): ?>
                                <div class="mt-1"><?php echo htmlspecialchars($supplier->country); ?></div>
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
                    <a href="<?php echo base_url('purchases/add?supplier_id=' . $supplier->supplier_id); ?>" class="btn btn-primary w-full">
                        <i class="fas fa-shopping-cart"></i>
                        New Purchase
                    </a>
                    <a href="<?php echo base_url('payments/add?supplier_id=' . $supplier->supplier_id); ?>" class="btn btn-success w-full">
                        <i class="fas fa-money-bill-wave"></i>
                        Make Payment
                    </a>
                    <hr class="my-2">
                    <a href="<?php echo base_url('suppliers/edit/' . $supplier->supplier_id); ?>" class="btn btn-outline w-full">
                        <i class="fas fa-edit"></i>
                        Edit Supplier
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
            <title>Supplier Ledger - <?php echo htmlspecialchars($supplier->supplier_name); ?></title>
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
                <h1 class="text-2xl font-bold">Supplier Ledger</h1>
                <h2 class="text-xl"><?php echo htmlspecialchars($supplier->supplier_name); ?></h2>
                <p class="text-gray-600">Supplier ID: #<?php echo $supplier->supplier_id; ?></p>
                <?php if (!empty($from_date) || !empty($to_date)): ?>
                    <p class="text-sm text-gray-500">
                        Period: <?php echo $from_date ? format_date($from_date) : 'Beginning'; ?> to <?php echo $to_date ? format_date($to_date) : 'Today'; ?>
                    </p>
                <?php endif; ?>
            </div>
            ${ledgerTable}
            <div class="mt-8 text-sm text-gray-600">
                <p>Printed on: <?php echo date('d M Y h:i A'); ?></p>
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
