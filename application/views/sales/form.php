<?php
$is_edit = isset($invoice) && $invoice;
$form_title = $is_edit ? 'Edit Invoice' : 'Create New Invoice';
?>

<?php card_start($form_title); ?>

<form method="post" action="<?php echo $form_action; ?>" id="invoiceForm">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Details -->
            <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Invoice Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Customer *</label>
                        <select name="customer_id" id="customer_id" class="form-select w-full" required>
                            <option value="">Select customer...</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer->customer_id; ?>"
                                        <?php echo ($is_edit && $invoice->customer_id == $customer->customer_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer->customer_name); ?> -
                                    <?php echo htmlspecialchars($customer->customer_mobile ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Invoice Date *</label>
                        <input type="date"
                               name="date"
                               value="<?php echo $is_edit ? $invoice->date : date('Y-m-d'); ?>"
                               class="form-control"
                               required>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Invoice Items</h3>
                    <button type="button" onclick="addItemRow()" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full" id="itemsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Product</th>
                                <th class="px-4 py-2 text-left" style="width: 100px;">Qty</th>
                                <th class="px-4 py-2 text-left" style="width: 120px;">Rate</th>
                                <th class="px-4 py-2 text-left" style="width: 120px;">Total</th>
                                <th class="px-4 py-2 text-center" style="width: 60px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Items will be added by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-success-50 border border-success-200 rounded-lg p-6 sticky top-4">
                <h3 class="text-lg font-semibold mb-4 text-success-900">Invoice Summary</h3>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Items:</span>
                        <span id="summary-items" class="font-semibold">0</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Quantity:</span>
                        <span id="summary-quantity" class="font-semibold">0</span>
                    </div>

                    <div class="border-t pt-3"></div>

                    <div class="flex justify-between">
                        <span class="text-gray-900">Subtotal:</span>
                        <span id="summary-subtotal" class="font-semibold">AED 0.00</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">VAT (5%):</span>
                        <span id="summary-vat" class="font-semibold">AED 0.00</span>
                    </div>

                    <div class="border-t pt-3"></div>

                    <div class="flex justify-between text-lg">
                        <span class="font-bold text-success-900">Grand Total:</span>
                        <span id="summary-grand-total" class="font-bold text-success-600">AED 0.00</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t">
                    <button type="submit" class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Invoice' : 'Create Invoice'; ?>
                    </button>
                    <a href="<?php echo base_url('sales'); ?>" class="btn btn-outline btn-block mt-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>

                <div class="mt-4 text-xs text-gray-600">
                    <i class="fas fa-info-circle"></i> Stock will be deducted automatically
                </div>
            </div>
        </div>
    </div>
</form>

<?php card_end(); ?>

<script>
// Products data - fetch via AJAX or embed
const products = <?php
    $this->load->model('Product_model');
    echo json_encode($this->Product_model->get_all());
?>;

let itemCounter = 0;

// Add item row
function addItemRow(productId = null, quantity = 1, rate = 0) {
    itemCounter++;
    const row = document.createElement('tr');
    row.id = `item-${itemCounter}`;
    row.innerHTML = `
        <td class="px-4 py-2">
            <select name="items[${itemCounter}][product_id]" class="form-control" onchange="selectProduct(${itemCounter}, this.value)" required>
                <option value="">Select Product</option>
                ${products.map(p => `<option value="${p.product_id}" ${productId == p.product_id ? 'selected' : ''}>${p.product_name} ${p.product_model ? '(' + p.product_model + ')' : ''} - Stock: ${p.quantity || 0}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${itemCounter}][quantity]" class="form-control" id="qty-${itemCounter}"
                   min="0.01" step="0.01" value="${quantity}" oninput="updateItemTotal(${itemCounter})" required>
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${itemCounter}][rate]" class="form-control" id="rate-${itemCounter}"
                   min="0" step="0.01" value="${rate}" oninput="updateItemTotal(${itemCounter})" required>
        </td>
        <td class="px-4 py-2">
            <span id="total-${itemCounter}" class="font-semibold">AED 0.00</span>
        </td>
        <td class="px-4 py-2 text-center">
            <button type="button" onclick="removeItemRow(${itemCounter})" class="text-danger-600 hover:text-danger-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    document.getElementById('itemsBody').appendChild(row);

    if (productId) {
        selectProduct(itemCounter, productId);
    }

    updateItemTotal(itemCounter);
}

// Select product and auto-fill rate
function selectProduct(itemId, productId) {
    const product = products.find(p => p.product_id == productId);
    if (product) {
        document.getElementById(`rate-${itemId}`).value = product.price || 0;
        updateItemTotal(itemId);
    }
}

// Update item total
function updateItemTotal(itemId) {
    const qty = parseFloat(document.getElementById(`qty-${itemId}`)?.value) || 0;
    const rate = parseFloat(document.getElementById(`rate-${itemId}`)?.value) || 0;
    const total = qty * rate;

    const totalEl = document.getElementById(`total-${itemId}`);
    if (totalEl) {
        totalEl.textContent = `AED ${total.toFixed(2)}`;
    }
    updateSummary();
}

// Remove item row
function removeItemRow(itemId) {
    const row = document.getElementById(`item-${itemId}`);
    if (row) {
        row.remove();
        updateSummary();
    }
}

// Update summary
function updateSummary() {
    const rows = document.querySelectorAll('#itemsBody tr');
    let itemCount = 0;
    let totalQty = 0;
    let subtotal = 0;

    rows.forEach((row) => {
        const rowId = parseInt(row.id.split('-')[1]);
        const qty = parseFloat(document.getElementById(`qty-${rowId}`)?.value) || 0;
        const rate = parseFloat(document.getElementById(`rate-${rowId}`)?.value) || 0;

        if (qty > 0 && rate >= 0) {
            itemCount++;
            totalQty += qty;
            subtotal += (qty * rate);
        }
    });

    const vat = subtotal * 0.05; // 5% VAT
    const grandTotal = subtotal + vat;

    document.getElementById('summary-items').textContent = itemCount;
    document.getElementById('summary-quantity').textContent = totalQty.toFixed(2);
    document.getElementById('summary-subtotal').textContent = `AED ${subtotal.toFixed(2)}`;
    document.getElementById('summary-vat').textContent = `AED ${vat.toFixed(2)}`;
    document.getElementById('summary-grand-total').textContent = `AED ${grandTotal.toFixed(2)}`;
}

// Initialize
window.addEventListener('DOMContentLoaded', function() {
    <?php if ($is_edit && !empty($invoice->items)): ?>
        // Load existing items
        <?php foreach ($invoice->items as $item): ?>
            addItemRow(<?php echo $item->product_id; ?>, <?php echo $item->quantity; ?>, <?php echo $item->rate; ?>);
        <?php endforeach; ?>
    <?php else: ?>
        // Add one empty row
        addItemRow();
    <?php endif; ?>
});
</script>

<style>
.form-control, .form-select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}
.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
</style>
