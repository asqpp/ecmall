<?php card_start('Add Purchase'); ?>

<form method="post" action="<?php echo base_url('purchases/add'); ?>" id="purchaseForm">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Purchase Details -->
        <div class="lg:col-span-2">
            <div class="space-y-6">
                <!-- Basic Info -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Purchase Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <?php echo form_select_group(
                            'supplier_id',
                            'Supplier',
                            $suppliers,
                            set_value('supplier_id'),
                            'supplier_id',
                            'supplier_name',
                            true,
                            'fas fa-truck'
                        ); ?>

                        <?php echo form_input_group(
                            'purchase_date',
                            'Purchase Date',
                            set_value('purchase_date', date('Y-m-d')),
                            true,
                            'date',
                            '',
                            'fas fa-calendar'
                        ); ?>
                    </div>
                </div>

                <!-- Purchase Items -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Purchase Items</h3>
                        <button type="button" onclick="addItemRow()" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full" id="itemsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Product</th>
                                    <th class="px-4 py-2 text-left" style="width: 120px;">Quantity</th>
                                    <th class="px-4 py-2 text-left" style="width: 120px;">Rate</th>
                                    <th class="px-4 py-2 text-left" style="width: 120px;">Total</th>
                                    <th class="px-4 py-2 text-center" style="width: 60px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Items will be added here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
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

                <!-- Hidden field for items JSON -->
                <input type="hidden" name="items" id="itemsJson">
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-primary-50 border border-primary-200 rounded-lg p-6 sticky top-4">
                <h3 class="text-lg font-semibold mb-4 text-primary-900">Purchase Summary</h3>

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
                        <span id="summary-subtotal" class="font-semibold text-gray-900">AED 0.00</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">VAT (5%):</span>
                        <span id="summary-vat" class="font-semibold">AED 0.00</span>
                    </div>

                    <div class="border-t pt-3"></div>

                    <div class="flex justify-between text-lg">
                        <span class="font-bold text-primary-900">Grand Total:</span>
                        <span id="summary-grand-total" class="font-bold text-primary-600">AED 0.00</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-save"></i> Record Purchase
                    </button>
                    <a href="<?php echo base_url('purchases'); ?>" class="btn btn-outline btn-block mt-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>

                <div class="mt-4 text-xs text-gray-600">
                    <i class="fas fa-info-circle"></i> Stock will be updated automatically
                </div>
            </div>
        </div>
    </div>
</form>

<?php card_end(); ?>

<script>
// Products data
const products = <?php echo json_encode($products); ?>;
let itemCounter = 0;
let items = [];

// Add item row
function addItemRow() {
    itemCounter++;
    const row = document.createElement('tr');
    row.id = `item-${itemCounter}`;
    row.innerHTML = `
        <td class="px-4 py-2">
            <select class="form-control" onchange="selectProduct(${itemCounter}, this.value)" required>
                <option value="">Select Product</option>
                ${products.map(p => `<option value="${p.product_id}">${p.product_name} ${p.product_model ? '(' + p.product_model + ')' : ''}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="number" class="form-control" id="qty-${itemCounter}" min="1" step="1" value="1" oninput="updateItemTotal(${itemCounter})" required>
        </td>
        <td class="px-4 py-2">
            <input type="number" class="form-control" id="rate-${itemCounter}" min="0" step="0.01" value="0" oninput="updateItemTotal(${itemCounter})" required>
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
}

// Select product and auto-fill rate
function selectProduct(itemId, productId) {
    const product = products.find(p => p.product_id == productId);
    if (product) {
        document.getElementById(`rate-${itemId}`).value = product.supplier_price || product.price || 0;
        updateItemTotal(itemId);
    }
}

// Update item total
function updateItemTotal(itemId) {
    const qty = parseFloat(document.getElementById(`qty-${itemId}`).value) || 0;
    const rate = parseFloat(document.getElementById(`rate-${itemId}`).value) || 0;
    const total = qty * rate;

    document.getElementById(`total-${itemId}`).textContent = `AED ${total.toFixed(2)}`;
    updateSummary();
}

// Remove item row
function removeItemRow(itemId) {
    document.getElementById(`item-${itemId}`).remove();
    updateSummary();
}

// Update summary
function updateSummary() {
    const rows = document.querySelectorAll('#itemsBody tr');
    let itemCount = 0;
    let totalQty = 0;
    let subtotal = 0;

    rows.forEach((row, index) => {
        const rowId = parseInt(row.id.split('-')[1]);
        const qty = parseFloat(document.getElementById(`qty-${rowId}`)?.value) || 0;
        const rate = parseFloat(document.getElementById(`rate-${rowId}`)?.value) || 0;

        if (qty > 0 && rate > 0) {
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

// Submit form
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Collect items
    const rows = document.querySelectorAll('#itemsBody tr');
    const items = [];

    rows.forEach(row => {
        const rowId = parseInt(row.id.split('-')[1]);
        const productSelect = row.querySelector('select');
        const productId = productSelect.value;

        if (!productId) return;

        const qty = parseFloat(document.getElementById(`qty-${rowId}`).value) || 0;
        const rate = parseFloat(document.getElementById(`rate-${rowId}`).value) || 0;

        if (qty > 0 && rate > 0) {
            items.push({
                product_id: productId,
                quantity: qty,
                rate: rate
            });
        }
    });

    if (items.length === 0) {
        Swal.fire('Error', 'Please add at least one item to the purchase.', 'error');
        return;
    }

    // Set items JSON
    document.getElementById('itemsJson').value = JSON.stringify(items);

    // Submit form
    this.submit();
});

// Add first item row on load
window.addEventListener('DOMContentLoaded', function() {
    addItemRow();
});
</script>

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
</style>
