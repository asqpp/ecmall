<?php card_start('Add Receipt'); ?>

<form method="post" action="<?php echo base_url('receipts/add'); ?>" id="receiptForm">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Selection -->
            <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Invoice Information</h3>

                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-invoice"></i> Select Invoice *
                    </label>
                    <select name="invoice_id" id="invoice_id" class="form-select w-full" required onchange="loadInvoiceDetails(this.value)">
                        <option value="">Select an invoice...</option>
                        <?php foreach ($invoices as $inv): ?>
                            <option value="<?php echo $inv->invoice_id; ?>"
                                    <?php echo ($selected_invoice && $selected_invoice->invoice_id == $inv->invoice_id) ? 'selected' : ''; ?>
                                    data-total="<?php echo $inv->grand_total; ?>"
                                    data-customer="<?php echo htmlspecialchars($inv->customer_name); ?>">
                                <?php echo $inv->invoice; ?> - <?php echo htmlspecialchars($inv->customer_name); ?> -
                                <?php echo format_currency($inv->grand_total); ?>
                                (<?php echo ucfirst($inv->payment_status); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="invoiceDetails" class="mt-4 <?php echo $selected_invoice ? '' : 'hidden'; ?>">
                    <div class="bg-white rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Customer:</span>
                            <span id="invoice-customer" class="font-semibold"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Invoice Total:</span>
                            <span id="invoice-total" class="font-semibold"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Already Paid:</span>
                            <span id="invoice-paid" class="font-semibold text-success-600"></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t">
                            <span class="text-gray-900 font-semibold">Outstanding:</span>
                            <span id="invoice-outstanding" class="font-bold text-danger-600"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Details -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Receipt Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo form_input_group(
                        'receipt_date',
                        'Receipt Date',
                        set_value('receipt_date', date('Y-m-d')),
                        true,
                        'date',
                        '',
                        'fas fa-calendar'
                    ); ?>

                    <?php echo form_input_group(
                        'amount',
                        'Amount Received',
                        set_value('amount'),
                        true,
                        'number',
                        '0.00',
                        'fas fa-money-bill',
                        ['step' => '0.01', 'min' => '0', 'id' => 'amount']
                    ); ?>

                    <?php echo form_input_group(
                        'discount',
                        'Discount Given',
                        set_value('discount', '0'),
                        false,
                        'number',
                        '0.00',
                        'fas fa-tags',
                        ['step' => '0.01', 'min' => '0']
                    ); ?>

                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-credit-card"></i> Payment Method *
                        </label>
                        <select name="payment_method" id="payment_method" class="form-select w-full" required onchange="toggleChequeFields()">
                            <option value="">Select method...</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="cheque">Cheque (incl. PDC)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Cheque Details (shown when cheque selected) -->
            <div id="chequeFields" class="bg-warning-50 border border-warning-200 rounded-lg p-4 hidden">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-money-check"></i> Cheque Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo form_input_group(
                        'cheque_no',
                        'Cheque Number',
                        set_value('cheque_no'),
                        false,
                        'text',
                        'Enter cheque number',
                        'fas fa-hashtag'
                    ); ?>

                    <?php echo form_input_group(
                        'cheque_date',
                        'Cheque Date',
                        set_value('cheque_date'),
                        false,
                        'date',
                        '',
                        'fas fa-calendar',
                        ['onchange' => 'checkPDC()']
                    ); ?>

                    <?php echo form_input_group(
                        'bank',
                        'Bank Name',
                        set_value('bank'),
                        false,
                        'text',
                        'Enter bank name',
                        'fas fa-university'
                    ); ?>

                    <div id="pdcWarning" class="col-span-2 hidden">
                        <div class="bg-warning-100 border border-warning-300 rounded-lg p-3">
                            <i class="fas fa-exclamation-triangle text-warning-600"></i>
                            <span class="text-warning-800 font-semibold">Post-Dated Cheque Detected!</span>
                            <p class="text-sm text-warning-700 mt-1">
                                This will be recorded as PDC and posted to PDC Receivable account.
                                Customer account will be reduced immediately, but cash will be received on clearance date.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div>
                <?php echo form_textarea_group(
                    'notes',
                    'Notes',
                    set_value('notes'),
                    false,
                    'Any additional notes...',
                    'fas fa-sticky-note'
                ); ?>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-success-50 border border-success-200 rounded-lg p-6 sticky top-4">
                <h3 class="text-lg font-semibold mb-4 text-success-900">Receipt Summary</h3>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Amount Received:</span>
                        <span id="summary-amount" class="font-semibold text-gray-900">AED 0.00</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount Given:</span>
                        <span id="summary-discount" class="text-danger-600">AED 0.00</span>
                    </div>

                    <div class="border-t pt-3"></div>

                    <div class="flex justify-between text-lg">
                        <span class="font-bold text-success-900">Net Receipt:</span>
                        <span id="summary-net" class="font-bold text-success-600">AED 0.00</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t">
                    <button type="submit" class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-check"></i> Record Receipt
                    </button>
                    <a href="<?php echo base_url('receipts'); ?>" class="btn btn-outline btn-block mt-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>

                <div class="mt-4 text-xs text-gray-600">
                    <i class="fas fa-info-circle"></i> Accounting entries will be posted automatically
                </div>
            </div>
        </div>
    </div>
</form>

<?php card_end(); ?>

<script>
// Load invoice details
function loadInvoiceDetails(invoiceId) {
    if (!invoiceId) {
        document.getElementById('invoiceDetails').classList.add('hidden');
        return;
    }

    fetch(`<?php echo base_url('receipts/get_invoice/'); ?>${invoiceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('invoiceDetails').classList.remove('hidden');
                document.getElementById('invoice-customer').textContent = data.invoice.customer_name;
                document.getElementById('invoice-total').textContent = `AED ${parseFloat(data.invoice.grand_total).toFixed(2)}`;
                document.getElementById('invoice-paid').textContent = `AED ${(parseFloat(data.invoice.grand_total) - parseFloat(data.outstanding)).toFixed(2)}`;
                document.getElementById('invoice-outstanding').textContent = `AED ${parseFloat(data.outstanding).toFixed(2)}`;

                // Set amount to outstanding
                document.getElementById('amount').value = parseFloat(data.outstanding).toFixed(2);
                updateSummary();
            }
        });
}

// Toggle cheque fields
function toggleChequeFields() {
    const method = document.getElementById('payment_method').value;
    const chequeFields = document.getElementById('chequeFields');

    if (method === 'cheque') {
        chequeFields.classList.remove('hidden');
    } else {
        chequeFields.classList.add('hidden');
        document.getElementById('pdcWarning').classList.add('hidden');
    }
}

// Check if PDC
function checkPDC() {
    const chequeDate = document.querySelector('input[name="cheque_date"]').value;
    const today = new Date().toISOString().split('T')[0];

    if (chequeDate && chequeDate > today) {
        document.getElementById('pdcWarning').classList.remove('hidden');
    } else {
        document.getElementById('pdcWarning').classList.add('hidden');
    }
}

// Update summary
function updateSummary() {
    const amount = parseFloat(document.getElementById('amount')?.value) || 0;
    const discount = parseFloat(document.querySelector('input[name="discount"]')?.value) || 0;
    const net = amount - discount;

    document.getElementById('summary-amount').textContent = `AED ${amount.toFixed(2)}`;
    document.getElementById('summary-discount').textContent = `AED ${discount.toFixed(2)}`;
    document.getElementById('summary-net').textContent = `AED ${net.toFixed(2)}`;
}

// Event listeners
document.getElementById('amount')?.addEventListener('input', updateSummary);
document.querySelector('input[name="discount"]')?.addEventListener('input', updateSummary);

// Load invoice if pre-selected
<?php if ($selected_invoice): ?>
    window.addEventListener('DOMContentLoaded', function() {
        loadInvoiceDetails(<?php echo $selected_invoice->invoice_id; ?>);
    });
<?php endif; ?>
</script>

<style>
.form-select {
    padding: 0.5rem 2.5rem 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
}

.form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
