<?php
$is_edit = isset($product) && $product;
$form_title = $is_edit ? 'Edit Product' : 'Add New Product';
$form_action = $is_edit ? base_url('products/edit/' . $product->product_id) : base_url('products/add');
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Form -->
    <div class="lg:col-span-2">
        <?php card_start($form_title); ?>

        <form method="post" action="<?php echo $form_action; ?>" id="productForm">
            <!-- Basic Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Basic Information</h3>

                <?php echo form_input_group(
                    'product_name',
                    'Product Name',
                    set_value('product_name', $product->product_name ?? ''),
                    true,
                    'text',
                    'Enter product name',
                    'fas fa-box'
                ); ?>

                <?php echo form_input_group(
                    'product_model',
                    'Model/SKU',
                    set_value('product_model', $product->product_model ?? ''),
                    false,
                    'text',
                    'Enter model or SKU',
                    'fas fa-barcode'
                ); ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo form_select_group(
                        'category_id',
                        'Category',
                        $categories,
                        set_value('category_id', $product->category_id ?? ''),
                        'category_id',
                        'category_name',
                        true,
                        'fas fa-tags'
                    ); ?>

                    <?php echo form_select_group(
                        'unit_id',
                        'Unit of Measure',
                        $units,
                        set_value('unit_id', $product->unit_id ?? ''),
                        'unit_id',
                        'unit_name',
                        true,
                        'fas fa-balance-scale'
                    ); ?>
                </div>

                <?php echo form_textarea_group(
                    'product_details',
                    'Description',
                    set_value('product_details', $product->product_details ?? ''),
                    false,
                    'Enter product description',
                    'fas fa-align-left'
                ); ?>
            </div>

            <!-- Pricing -->
            <div class="space-y-4 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Pricing</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php echo form_input_group(
                        'price',
                        'Selling Price',
                        set_value('price', $product->price ?? ''),
                        true,
                        'number',
                        '0.00',
                        'fas fa-dollar-sign',
                        ['step' => '0.01', 'min' => '0']
                    ); ?>

                    <?php echo form_input_group(
                        'supplier_price',
                        'Purchase Price',
                        set_value('supplier_price', $product->supplier_price ?? ''),
                        false,
                        'number',
                        '0.00',
                        'fas fa-shopping-cart',
                        ['step' => '0.01', 'min' => '0']
                    ); ?>

                    <?php echo form_input_group(
                        'tax',
                        'Tax %',
                        set_value('tax', $product->tax ?? '0'),
                        false,
                        'number',
                        '0',
                        'fas fa-percent',
                        ['step' => '0.01', 'min' => '0', 'max' => '100']
                    ); ?>
                </div>

                <?php if ($is_edit): ?>
                    <div class="bg-info-50 border border-info-200 rounded-lg p-4">
                        <p class="text-info-800 text-sm">
                            <i class="fas fa-info-circle"></i>
                            <strong>Current Stock:</strong> <?php echo $product->quantity ?? 0; ?> units
                            <br><span class="text-xs">Stock is updated automatically through purchases and sales.</span>
                        </p>
                    </div>
                <?php else: ?>
                    <?php echo form_input_group(
                        'quantity',
                        'Initial Stock Quantity',
                        set_value('quantity', '0'),
                        false,
                        'number',
                        '0',
                        'fas fa-warehouse',
                        ['step' => '1', 'min' => '0']
                    ); ?>
                <?php endif; ?>
            </div>

            <!-- Image & Status -->
            <div class="space-y-4 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Image & Status</h3>

                <?php echo form_input_group(
                    'image',
                    'Image URL',
                    set_value('image', $product->image ?? ''),
                    false,
                    'url',
                    'https://example.com/image.jpg',
                    'fas fa-image'
                ); ?>

                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                               name="status"
                               value="1"
                               <?php echo set_checkbox('status', '1', ($product->status ?? 1) == 1); ?>
                               class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
                        <span class="text-gray-700">Active (Available for sale)</span>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
                <a href="<?php echo base_url('products'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Product' : 'Add Product'; ?>
                </button>
            </div>
        </form>

        <?php card_end(); ?>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <?php card_start('Quick Tips'); ?>
        <div class="space-y-4 text-sm">
            <div>
                <h4 class="font-semibold text-gray-900 mb-2">
                    <i class="fas fa-lightbulb text-warning-500"></i> Product Name
                </h4>
                <p class="text-gray-600">Use clear, descriptive names that customers will recognize.</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-2">
                    <i class="fas fa-lightbulb text-warning-500"></i> Model/SKU
                </h4>
                <p class="text-gray-600">Unique identifier for inventory tracking and reporting.</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-2">
                    <i class="fas fa-lightbulb text-warning-500"></i> Pricing
                </h4>
                <p class="text-gray-600">
                    Selling price is what customers pay.<br>
                    Purchase price helps track profit margins.
                </p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-2">
                    <i class="fas fa-lightbulb text-warning-500"></i> Stock Management
                </h4>
                <p class="text-gray-600">
                    Stock quantities are automatically updated when you:
                    <ul class="list-disc ml-5 mt-2">
                        <li>Record purchases (increases stock)</li>
                        <li>Create sales invoices (decreases stock)</li>
                    </ul>
                </p>
            </div>
        </div>
        <?php card_end(); ?>

        <?php if ($is_edit): ?>
            <?php card_start('Quick Actions', true); ?>
            <div class="space-y-2">
                <a href="<?php echo base_url('products/view/' . $product->product_id); ?>"
                   class="btn btn-block btn-info">
                    <i class="fas fa-eye"></i> View Details
                </a>
                <a href="<?php echo base_url('purchases/add?product_id=' . $product->product_id); ?>"
                   class="btn btn-block btn-success">
                    <i class="fas fa-cart-plus"></i> Add Purchase
                </a>
                <a href="<?php echo base_url('sales/add?product_id=' . $product->product_id); ?>"
                   class="btn btn-block btn-primary">
                    <i class="fas fa-receipt"></i> Create Invoice
                </a>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-calculate profit margin
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.querySelector('input[name="price"]');
    const costInput = document.querySelector('input[name="supplier_price"]');

    function showMargin() {
        const price = parseFloat(priceInput.value) || 0;
        const cost = parseFloat(costInput.value) || 0;

        if (price > 0 && cost > 0) {
            const margin = ((price - cost) / price * 100).toFixed(2);
            const profit = (price - cost).toFixed(2);

            // You could display this somewhere
            console.log(`Profit Margin: ${margin}%, Profit: AED ${profit}`);
        }
    }

    priceInput?.addEventListener('input', showMargin);
    costInput?.addEventListener('input', showMargin);
});
</script>
