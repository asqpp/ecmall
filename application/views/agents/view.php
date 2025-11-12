<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="<?php echo base_url('agents'); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Agents
        </a>
        <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($agent->agent_name); ?></h1>
        <p class="text-gray-600"><?php echo htmlspecialchars($agent->agent_code ?? 'N/A'); ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('agents/edit/' . $agent->agent_id); ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Agent Information -->
        <?php card_start('Agent Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Agent Code</label>
                <p class="font-semibold text-gray-900">
                    <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($agent->agent_code ?? 'N/A'); ?></code>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Agent Name</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($agent->agent_name); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Contact Person</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($agent->contact_person ?? 'N/A'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Mobile</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($agent->mobile); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Email</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($agent->email ?? 'N/A'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Commission Rate</label>
                <p class="text-gray-900">
                    <span class="badge badge-success text-lg"><?php echo number_format($agent->commission_rate ?? 0, 2); ?>%</span>
                </p>
            </div>

            <?php if (!empty($agent->address)): ?>
                <div class="col-span-2">
                    <label class="text-sm text-gray-600">Address</label>
                    <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($agent->address)); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Commission History -->
        <?php card_start('Commission History'); ?>
        <?php if (empty($commissions)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-money-bill text-4xl mb-2"></i>
                <p>No commission records found</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <?php table_start(['Invoice', 'Date', 'Customer', 'Sale Amount', 'Commission', 'Paid', 'Status']); ?>
                    <?php foreach ($commissions as $commission): ?>
                        <tr>
                            <td>
                                <a href="<?php echo base_url('sales/view/' . $commission->invoice_id); ?>"
                                   class="text-primary-600 hover:underline">
                                    <code><?php echo htmlspecialchars($commission->invoice_no ?? 'N/A'); ?></code>
                                </a>
                            </td>
                            <td><?php echo format_date($commission->invoice_date); ?></td>
                            <td><?php echo htmlspecialchars($commission->customer_name ?? 'N/A'); ?></td>
                            <td><?php echo format_currency($commission->grand_total); ?></td>
                            <td class="font-semibold"><?php echo format_currency($commission->commission_amount); ?></td>
                            <td class="text-success-600"><?php echo format_currency($commission->paid_amount ?? 0); ?></td>
                            <td>
                                <?php
                                $status = $commission->payment_status ?? 'unpaid';
                                if ($status == 'paid') {
                                    echo '<span class="badge badge-success">Paid</span>';
                                } elseif ($status == 'partial') {
                                    echo '<span class="badge badge-warning">Partial</span>';
                                } else {
                                    echo '<span class="badge badge-danger">Unpaid</span>';
                                }
                                ?>
                                <?php if ($status != 'paid'): ?>
                                    <button onclick="payCommission(<?php echo $commission->commission_id; ?>, <?php echo $commission->commission_amount - ($commission->paid_amount ?? 0); ?>)"
                                            class="btn btn-xs btn-success ml-2">
                                        <i class="fas fa-money-bill"></i> Pay
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php table_end(); ?>
            </div>
        <?php endif; ?>
        <?php card_end(); ?>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Commission Summary -->
        <?php card_start('Commission Summary'); ?>
        <div class="space-y-4">
            <div class="text-center p-4 bg-primary-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Total Sales</div>
                <div class="text-2xl font-bold text-primary-600">
                    <?php echo format_currency($agent->total_sales ?? 0); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div class="text-center p-3 bg-success-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Total Commission</div>
                    <div class="text-lg font-bold text-success-600">
                        <?php echo format_currency($agent->total_commission ?? 0); ?>
                    </div>
                </div>

                <div class="text-center p-3 bg-primary-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Commission Paid</div>
                    <div class="text-lg font-bold text-primary-600">
                        <?php echo format_currency($agent->commission_paid ?? 0); ?>
                    </div>
                </div>

                <div class="text-center p-3 bg-danger-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Commission Due</div>
                    <div class="text-lg font-bold text-danger-600">
                        <?php echo format_currency($agent->commission_due ?? 0); ?>
                    </div>
                </div>
            </div>

            <div class="border-t pt-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total Invoices:</span>
                    <span class="font-semibold"><?php echo $agent->total_invoices ?? 0; ?></span>
                </div>
            </div>
        </div>
        <?php card_end(); ?>

        <!-- Quick Actions -->
        <?php card_start('Quick Actions'); ?>
        <div class="space-y-2">
            <a href="<?php echo base_url('agents/edit/' . $agent->agent_id); ?>"
               class="btn btn-block btn-primary">
                <i class="fas fa-edit"></i> Edit Agent
            </a>
            <a href="<?php echo base_url('agents/commission_report?agent_id=' . $agent->agent_id); ?>"
               class="btn btn-block btn-secondary">
                <i class="fas fa-chart-line"></i> Commission Report
            </a>
            <a href="<?php echo base_url('agents'); ?>" class="btn btn-block btn-outline">
                <i class="fas fa-list"></i> All Agents
            </a>
        </div>
        <?php card_end(); ?>
    </div>
</div>

<script>
function payCommission(commissionId, outstandingAmount) {
    Swal.fire({
        title: 'Pay Commission',
        html: `
            <div class="text-left space-y-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Amount (AED)</label>
                    <input type="number" id="paid_amount" class="swal2-input" value="${outstandingAmount}" min="0" max="${outstandingAmount}" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Date</label>
                    <input type="date" id="payment_date" class="swal2-input" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Method</label>
                    <select id="payment_method" class="swal2-input">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Notes</label>
                    <textarea id="payment_notes" class="swal2-textarea"></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Pay Commission',
        confirmButtonColor: '#10B981',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            return {
                paid_amount: document.getElementById('paid_amount').value,
                payment_date: document.getElementById('payment_date').value,
                payment_method: document.getElementById('payment_method').value,
                notes: document.getElementById('payment_notes').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo base_url('agents/pay_commission/'); ?>' + commissionId;

            for (const key in result.value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = result.value[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
