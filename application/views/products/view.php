<?php
$profit_margin = 0;
if ($product->price > 0 && $product->supplier_price > 0) {
    $profit_margin = (($product->price - $product->supplier_price) / $product->price) * 100;
}
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="<?php echo base_url('products'); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($product->product_name); ?></h1>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('products/edit/' . $product->product_id); ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Product Information -->
        <?php card_start('Product Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <?php if ($product->image): ?>
                <div class="col-span-2">
                    <img src="<?php echo $product->image; ?>"
                         alt="<?php echo htmlspecialchars($product->product_name); ?>"
                         class="w-full max-w-md h-64 object-cover rounded-lg shadow-md mx-auto">
                </div>
            <?php endif; ?>

            <div>
                <label class="text-sm text-gray-600">Product ID</label>
                <p class="font-semibold text-gray-900">#<?php echo $product->product_id; ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Model/SKU</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($product->product_model ?? '-'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Category</label>
                <p class="font-semibold text-gray-900">
                    <?php echo badge(htmlspecialchars($product->category_name ?? 'N/A'), 'primary'); ?>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Unit of Measure</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($product->unit_name ?? 'N/A'); ?></p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Status</label>
                <p class="font-semibold">
                    <?php if ($product->status == 1): ?>
                        <?php echo status_badge('active', 'Active'); ?>
                    <?php else: ?>
                        <?php echo status_badge('inactive', 'Inactive'); ?>
                    <?php endif; ?>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Tax Rate</label>
                <p class="font-semibold text-gray-900"><?php echo $product->tax ?? 0; ?>%</p>
            </div>

            <?php if ($product->product_details): ?>
                <div class="col-span-2">
                    <label class="text-sm text-gray-600">Description</label>
                    <p class="text-gray-900 mt-1"><?php echo nl2br(htmlspecialchars($product->product_details)); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Stock Movements -->
        <?php if (!empty($stock_movements)): ?>
            <?php card_start('Recent Stock Movements'); ?>
            <div class="overflow-x-auto">
                <?php table_start(['Date', 'Document', 'Type', 'In', 'Out', 'Rate', 'Balance']); ?>
                    <?php
                    $running_balance = 0;
                    foreach ($stock_movements as $movement):
                        $running_balance += ($movement->qtyin - $movement->qtyout);
                    ?>
                        <tr>
                            <td><?php echo format_date($movement->tdate); ?></td>
                            <td><code class="text-xs"><?php echo $movement->docno; ?></code></td>
                            <td><?php echo badge(ucfirst($movement->ttype)); ?></td>
                            <td class="text-success-600 font-semibold">
                                <?php echo $movement->qtyin > 0 ? '+' . $movement->qtyin : '-'; ?>
                            </td>
                            <td class="text-danger-600 font-semibold">
                                <?php echo $movement->qtyout > 0 ? '-' . $movement->qtyout : '-'; ?>
                            </td>
                            <td><?php echo format_currency($movement->rate); ?></td>
                            <td class="font-semibold"><?php echo $running_balance; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php table_end(); ?>
            </div>
            <?php card_end(); ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Stock Stats -->
        <?php card_start('Stock & Pricing'); ?>
        <div class="space-y-4">
            <div class="text-center p-4 bg-primary-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Current Stock</div>
                <div class="text-4xl font-bold <?php echo ($product->quantity < 10) ? 'text-danger-600' : 'text-primary-600'; ?>">
                    <?php echo $product->quantity ?? 0; ?>
                </div>
                <div class="text-sm text-gray-600 mt-1"><?php echo $product->unit_name ?? 'units'; ?></div>
                <?php if ($product->quantity < 10): ?>
                    <div class="mt-2">
                        <span class="badge badge-danger">Low Stock</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-success-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Selling Price</div>
                    <div class="text-lg font-bold text-success-600">
                        <?php echo format_currency($product->price); ?>
                    </div>
                </div>

                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Purchase Price</div>
                    <div class="text-lg font-bold text-gray-600">
                        <?php echo format_currency($product->supplier_price ?? 0); ?>
                    </div>
                </div>
            </div>

            <div class="text-center p-3 bg-info-50 rounded-lg">
                <div class="text-xs text-gray-600 mb-1">Profit Margin</div>
                <div class="text-xl font-bold text-info-600">
                    <?php echo number_format($profit_margin, 2); ?>%
                </div>
                <div class="text-xs text-gray-600 mt-1">
                    Profit: <?php echo format_currency($product->price - ($product->supplier_price ?? 0)); ?>
                </div>
            </div>

            <?php if ($product->tax > 0): ?>
                <div class="text-center p-3 bg-warning-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Price with Tax</div>
                    <div class="text-lg font-bold text-warning-600">
                        <?php echo format_currency($product->price * (1 + $product->tax / 100)); ?>
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        Includes <?php echo $product->tax; ?>% tax
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Quick Actions -->
        <?php card_start('Quick Actions'); ?>
        <div class="space-y-2">
            <a href="<?php echo base_url('purchases/add?product_id=' . $product->product_id); ?>"
               class="btn btn-block btn-success">
                <i class="fas fa-cart-plus"></i> Record Purchase
            </a>
            <a href="<?php echo base_url('sales/add?product_id=' . $product->product_id); ?>"
               class="btn btn-block btn-primary">
                <i class="fas fa-receipt"></i> Create Invoice
            </a>
            <a href="<?php echo base_url('products/edit/' . $product->product_id); ?>"
               class="btn btn-block btn-secondary">
                <i class="fas fa-edit"></i> Edit Product
            </a>
        </div>
        <?php card_end(); ?>

        <!-- Product Stats -->
        <?php card_start('Statistics'); ?>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Total Value:</span>
                <span class="font-semibold text-gray-900">
                    <?php echo format_currency($product->quantity * $product->price); ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Cost Value:</span>
                <span class="font-semibold text-gray-900">
                    <?php echo format_currency($product->quantity * ($product->supplier_price ?? 0)); ?>
                </span>
            </div>
            <div class="flex justify-between pt-3 border-t">
                <span class="text-gray-600">Potential Profit:</span>
                <span class="font-semibold text-success-600">
                    <?php echo format_currency($product->quantity * ($product->price - ($product->supplier_price ?? 0))); ?>
                </span>
            </div>
        </div>
        <?php card_end(); ?>
    </div>
</div>
