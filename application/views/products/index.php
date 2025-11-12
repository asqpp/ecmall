<?php card_start('Products', true); ?>

<!-- Header Actions -->
<div class="flex justify-between items-center mb-6">
    <div class="flex gap-4">
        <form method="get" action="<?php echo base_url('products'); ?>" class="flex gap-2">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search products..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search): ?>
                <a href="<?php echo base_url('products'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="flex gap-2">
        <a href="<?php echo base_url('products/low_stock'); ?>" class="btn btn-warning">
            <i class="fas fa-exclamation-triangle"></i> Low Stock
        </a>
        <a href="<?php echo base_url('products/export'); ?>" class="btn btn-secondary">
            <i class="fas fa-download"></i> Export
        </a>
        <a href="<?php echo base_url('products/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
</div>

<!-- Products Table -->
<div class="overflow-x-auto">
    <?php table_start(['ID', 'Name', 'Model', 'Category', 'Price', 'Supplier Price', 'Stock', 'Status', 'Actions']); ?>
        <?php if (empty($products)): ?>
            <tr>
                <td colspan="9" class="text-center py-8 text-gray-500">
                    <i class="fas fa-box-open text-4xl mb-2"></i>
                    <p>No products found</p>
                    <?php if ($search): ?>
                        <a href="<?php echo base_url('products'); ?>" class="text-primary-600 hover:underline">View all products</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product->product_id; ?></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <?php if ($product->image): ?>
                                <img src="<?php echo $product->image; ?>" alt="Product" class="w-10 h-10 rounded object-cover">
                            <?php endif; ?>
                            <strong><?php echo htmlspecialchars($product->product_name); ?></strong>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($product->product_model ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($product->category_name ?? 'N/A'); ?></td>
                    <td><?php echo format_currency($product->price); ?></td>
                    <td><?php echo format_currency($product->supplier_price ?? 0); ?></td>
                    <td>
                        <?php
                        $stock = $product->quantity ?? 0;
                        $stock_class = $stock < 10 ? 'text-danger-600 font-bold' : 'text-gray-900';
                        ?>
                        <span class="<?php echo $stock_class; ?>">
                            <?php echo $stock; ?> <?php echo $product->unit_name ?? 'units'; ?>
                        </span>
                        <?php if ($stock < 10): ?>
                            <span class="badge badge-danger">Low</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product->status == 1): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('products/view/' . $product->product_id); ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo base_url('products/edit/' . $product->product_id); ?>"
                               class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $product->product_id; ?>, '<?php echo addslashes($product->product_name); ?>')"
                                    class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php table_end(); ?>
</div>

<!-- Pagination -->
<?php if ($pagination->total_pages > 1): ?>
    <div class="mt-6">
        <?php echo render_pagination($pagination, 'products'); ?>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<!-- Delete Confirmation Script -->
<script>
function confirmDelete(productId, productName) {
    Swal.fire({
        title: 'Delete Product?',
        text: `Are you sure you want to delete "${productName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('products/delete/'); ?>${productId}`;
        }
    });
}
</script>
