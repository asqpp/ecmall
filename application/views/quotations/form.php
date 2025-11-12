<?php
$is_edit = isset($quotation) && $quotation;
$form_title = $is_edit ? 'Edit Quotation' : 'Create New Quotation';
?>

<?php card_start($form_title); ?>

<form method="post" action="" id="quotationForm">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Quotation Details -->
            <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Quotation Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Customer *</label>
                        <select name="customer_id" id="customer_id" class="form-select w-full" required>
                            <option value="">Select customer...</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer->customer_id; ?>"
                                        <?php echo ($is_edit && $quotation->customer_id == $customer->customer_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer->customer_name); ?> -
                                    <?php echo htmlspecialchars($customer->customer_mobile ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Quotation Date *</label>
                        <input type="date"
                               name="quotation_date"
                               value="<?php echo $is_edit ? $quotation->quotation_date : date('Y-m-d'); ?>"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group col-span-2">
                        <label><i class="fas fa-calendar-check"></i> Valid Until</label>
                        <input type="date"
                               name="valid_until"
                               value="<?php echo $is_edit ? ($quotation->valid_until ?? '') : date('Y-m-d', strtotime('+30 days')); ?>"
                               class="form-control">
                        <small class="text-gray-600">Default: 30 days from quotation date</small>
                    </div>
                </div>
            </div>

            <!-- Quotation Items -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Quotation Items</h3>
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

            <!-- Notes Section -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Additional Notes</h3>
                <div class="form-group">
                    <textarea name="notes"
                              class="form-control"
                              rows="4"
                              placeholder="Enter any additional terms, conditions, or notes..."><?php echo $is_edit ? htmlspecialchars($quotation->notes ?? '') : ''; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-warning-50 border border-warning-200 rounded-lg p-6 sticky top-4">
                <h3 class="text-lg font-semibold mb-4 text-warning-900">Quotation Summary</h3>

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
                        <span class="font-bold text-warning-900">Grand Total:</span>
                        <span id="summary-grand-total" class="font-bold text-warning-600">AED 0.00</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t">
                    <button type="submit" class="btn btn-warning btn-block btn-lg">
                        <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Quotation' : 'Create Quotation'; ?>
                    </button>
                    <a href="<?php echo base_url('quotations'); ?>" class="btn btn-outline btn-block mt-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>

                <div class="mt-4 text-xs text-gray-600">
                    <i class="fas fa-info-circle"></i> Quotations do not affect stock until converted to invoice
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden field for items JSON -->
    <input type="hidden" name="items" id="itemsJson">
</form>

<?php card_end(); ?>

<script>
// Products data
const products = <?php
    $this->load->model('Product_model');
    echo json_encode($this->Product_model->get_all());
?>;

let itemCounter = 0;
let itemsData = {};

// Add item row
function addItemRow(productId = null, quantity = 1, rate = 0) {
    itemCounter++;
    const row = document.createElement('tr');
    row.id = `item-${itemCounter}`;
    row.innerHTML = `
        <td class="px-4 py-2">
            <select class="form-control" onchange="selectProduct(${itemCounter}, this.value)" required>
                <option value="">Select Product</option>
                ${products.map(p => `<option value="${p.product_id}" ${productId == p.product_id ? 'selected' : ''}>${p.product_name} ${p.product_model ? '(' + p.product_model + ')' : ''}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="number" class="form-control" id="qty-${itemCounter}"
                   min="0.01" step="0.01" value="${quantity}" oninput="updateItemTotal(${itemCounter})" required>
        </td>
        <td class="px-4 py-2">
            <input type="number" class="form-control" id="rate-${itemCounter}"
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

    // Initialize item data
    itemsData[itemCounter] = {
        product_id: productId || '',
        quantity: quantity,
        rate: rate
    };

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
        itemsData[itemId].product_id = productId;
        updateItemTotal(itemId);
    }
}

// Update item total
function updateItemTotal(itemId) {
    const qty = parseFloat(document.getElementById(`qty-${itemId}`)?.value) || 0;
    const rate = parseFloat(document.getElementById(`rate-${itemId}`)?.value) || 0;
    const total = qty * rate;

    itemsData[itemId].quantity = qty;
    itemsData[itemId].rate = rate;

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
        delete itemsData[itemId];
        updateSummary();
    }
}

// Update summary
function updateSummary() {
    let itemCount = 0;
    let totalQty = 0;
    let subtotal = 0;

    for (const itemId in itemsData) {
        const item = itemsData[itemId];
        if (item.quantity > 0 && item.rate >= 0 && item.product_id) {
            itemCount++;
            totalQty += parseFloat(item.quantity);
            subtotal += (parseFloat(item.quantity) * parseFloat(item.rate));
        }
    }

    const vat = subtotal * 0.05; // 5% VAT
    const grandTotal = subtotal + vat;

    document.getElementById('summary-items').textContent = itemCount;
    document.getElementById('summary-quantity').textContent = totalQty.toFixed(2);
    document.getElementById('summary-subtotal').textContent = `AED ${subtotal.toFixed(2)}`;
    document.getElementById('summary-vat').textContent = `AED ${vat.toFixed(2)}`;
    document.getElementById('summary-grand-total').textContent = `AED ${grandTotal.toFixed(2)}`;
}

// Form submission
document.getElementById('quotationForm').addEventListener('submit', function(e) {
    // Prepare items array
    const items = [];
    for (const itemId in itemsData) {
        const item = itemsData[itemId];
        if (item.product_id && item.quantity > 0) {
            items.push({
                product_id: item.product_id,
                quantity: item.quantity,
                rate: item.rate
            });
        }
    }

    if (items.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the quotation.');
        return false;
    }

    // Set items JSON
    document.getElementById('itemsJson').value = JSON.stringify(items);
});

// Initialize
window.addEventListener('DOMContentLoaded', function() {
    <?php if ($is_edit && !empty($quotation->items)): ?>
        // Load existing items
        <?php foreach ($quotation->items as $item): ?>
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
