<?php card_start('Add Payment'); ?>

<form method="post" action="<?php echo base_url('payments/add'); ?>" id="paymentForm">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Purchase Selection -->
            <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Purchase Information</h3>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-shopping-cart"></i> Select Purchase *
                    </label>
                    <select name="purchase_id" id="purchase_id" class="form-select w-full" required onchange="loadPurchaseDetails(this.value)">
                        <option value="">Select a purchase...</option>
                        <?php foreach ($purchases as $purch): ?>
                            <option value="<?php echo $purch->purchase_id; ?>"
                                    <?php echo ($selected_purchase && $selected_purchase->purchase_id == $purch->purchase_id) ? 'selected' : ''; ?>>
                                <?php echo $purch->chalan_no; ?> - <?php echo htmlspecialchars($purch->supplier_name); ?> -
                                <?php echo format_currency($purch->grand_total_amount); ?>
                                (<?php echo ucfirst($purch->payment_status); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="purchaseDetails" class="mt-4 <?php echo $selected_purchase ? '' : 'hidden'; ?>">
                    <div class="bg-white rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Supplier:</span>
                            <span id="purchase-supplier" class="font-semibold"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Purchase Total:</span>
                            <span id="purchase-total" class="font-semibold"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Already Paid:</span>
                            <span id="purchase-paid" class="font-semibold text-success-600"></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t">
                            <span class="text-gray-900 font-semibold">Outstanding:</span>
                            <span id="purchase-outstanding" class="font-bold text-danger-600"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Payment Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Payment Date *</label>
                        <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-money-bill"></i> Amount Paid *</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" class="form-control" required oninput="updateSummary()">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tags"></i> Discount Received</label>
                        <input type="number" name="discount" value="0" step="0.01" min="0" class="form-control" oninput="updateSummary()">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-credit-card"></i> Payment Method *</label>
                        <select name="payment_method" id="payment_method" class="form-select" required onchange="toggleChequeFields()">
                            <option value="">Select method...</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="cheque">Cheque (incl. PDC)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Cheque Details -->
            <div id="chequeFields" class="bg-warning-50 border border-warning-200 rounded-lg p-4 hidden">
                <h3 class="text-lg font-semibold mb-4"><i class="fas fa-money-check"></i> Cheque Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label>Cheque Number</label>
                        <input type="text" name="cheque_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Cheque Date</label>
                        <input type="date" name="cheque_date" class="form-control" onchange="checkPDC()">
                    </div>
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="bank" class="form-control">
                    </div>
                    <div id="pdcWarning" class="col-span-2 hidden">
                        <div class="bg-warning-100 border border-warning-300 rounded-lg p-3">
                            <i class="fas fa-exclamation-triangle text-warning-600"></i>
                            <span class="text-warning-800 font-semibold">Post-Dated Cheque Detected!</span>
                            <p class="text-sm text-warning-700 mt-1">
                                This will be recorded as PDC and posted to PDC Payable account.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="details" rows="3" class="form-control"></textarea>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-danger-50 border border-danger-200 rounded-lg p-6 sticky top-4">
                <h3 class="text-lg font-semibold mb-4 text-danger-900">Payment Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Amount Paid:</span>
                        <span id="summary-amount" class="font-semibold text-gray-900">AED 0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount Received:</span>
                        <span id="summary-discount" class="text-success-600">AED 0.00</span>
                    </div>
                    <div class="border-t pt-3"></div>
                    <div class="flex justify-between text-lg">
                        <span class="font-bold text-danger-900">Net Payment:</span>
                        <span id="summary-net" class="font-bold text-danger-600">AED 0.00</span>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t">
                    <button type="submit" class="btn btn-danger btn-block btn-lg">
                        <i class="fas fa-check"></i> Record Payment
                    </button>
                    <a href="<?php echo base_url('payments'); ?>" class="btn btn-outline btn-block mt-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php card_end(); ?>

<script>
function loadPurchaseDetails(purchaseId) {
    if (!purchaseId) {
        document.getElementById('purchaseDetails').classList.add('hidden');
        return;
    }
    fetch('<?php echo base_url('payments/get_purchase/'); ?>' + purchaseId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('purchaseDetails').classList.remove('hidden');
                document.getElementById('purchase-supplier').textContent = data.purchase.supplier_name;
                document.getElementById('purchase-total').textContent = 'AED ' + parseFloat(data.purchase.grand_total_amount).toFixed(2);
                document.getElementById('purchase-paid').textContent = 'AED ' + (parseFloat(data.purchase.grand_total_amount) - parseFloat(data.outstanding)).toFixed(2);
                document.getElementById('purchase-outstanding').textContent = 'AED ' + parseFloat(data.outstanding).toFixed(2);
                document.getElementById('amount').value = parseFloat(data.outstanding).toFixed(2);
                updateSummary();
            }
        });
}

function toggleChequeFields() {
    const method = document.getElementById('payment_method').value;
    document.getElementById('chequeFields').classList.toggle('hidden', method !== 'cheque');
    if (method !== 'cheque') document.getElementById('pdcWarning').classList.add('hidden');
}

function checkPDC() {
    const chequeDate = document.querySelector('input[name="cheque_date"]').value;
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('pdcWarning').classList.toggle('hidden', !chequeDate || chequeDate <= today);
}

function updateSummary() {
    const amount = parseFloat(document.getElementById('amount')?.value) || 0;
    const discount = parseFloat(document.querySelector('input[name="discount"]')?.value) || 0;
    document.getElementById('summary-amount').textContent = 'AED ' + amount.toFixed(2);
    document.getElementById('summary-discount').textContent = 'AED ' + discount.toFixed(2);
    document.getElementById('summary-net').textContent = 'AED ' + (amount - discount).toFixed(2);
}

<?php if ($selected_purchase): ?>
window.addEventListener('DOMContentLoaded', () => loadPurchaseDetails(<?php echo $selected_purchase->purchase_id; ?>));
<?php endif; ?>
</script>

<style>
.form-control, .form-select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
}
.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
</style>
