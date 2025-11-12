<?php card_start('Low Stock Products', true); ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <p class="text-gray-600">Products with stock below threshold</p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('products'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        <a href="<?php echo base_url('products/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
</div>

<div class="overflow-x-auto">
    <?php table_start(['ID', 'Product', 'Category', 'Current Stock', 'Unit', 'Price', 'Status', 'Actions']); ?>
        <?php if (empty($products)): ?>
            <tr>
                <td colspan="8" class="text-center py-8 text-gray-500">
                    <i class="fas fa-check-circle text-4xl mb-2 text-success-500"></i>
                    <p>All products have sufficient stock!</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <tr class="<?php echo $product->quantity == 0 ? 'bg-danger-50' : 'bg-warning-50'; ?>">
                    <td><?php echo $product->product_id; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($product->product_name); ?></strong>
                        <br>
                        <span class="text-sm text-gray-600"><?php echo htmlspecialchars($product->product_model ?? ''); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($product->category_name ?? 'N/A'); ?></td>
                    <td>
                        <span class="text-2xl font-bold <?php echo $product->quantity == 0 ? 'text-danger-600' : 'text-warning-600'; ?>">
                            <?php echo $product->quantity; ?>
                        </span>
                        <?php if ($product->quantity == 0): ?>
                            <br><span class="badge badge-danger">Out of Stock</span>
                        <?php else: ?>
                            <br><span class="badge badge-warning">Low Stock</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $product->unit_name ?? 'units'; ?></td>
                    <td><?php echo format_currency($product->price); ?></td>
                    <td>
                        <?php if ($product->status == 1): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('purchases/add?product_id=' . $product->product_id); ?>"
                               class="btn btn-sm btn-success" title="Add Purchase">
                                <i class="fas fa-cart-plus"></i>
                            </a>
                            <a href="<?php echo base_url('products/view/' . $product->product_id); ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo base_url('products/edit/' . $product->product_id); ?>"
                               class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php table_end(); ?>
</div>

<?php card_end(); ?>
