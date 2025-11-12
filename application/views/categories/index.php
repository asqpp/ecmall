<?php card_start('Product Categories', true); ?>

<div class="flex justify-between items-center mb-6">
    <div class="flex gap-4">
        <form method="get" action="<?php echo base_url('categories'); ?>" class="flex gap-2">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search categories..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search): ?>
                <a href="<?php echo base_url('categories'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('categories/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>
</div>

<div class="overflow-x-auto">
    <?php table_start(['ID', 'Category Name', 'Description', 'Products', 'Actions']); ?>
        <?php if (empty($categories)): ?>
            <tr>
                <td colspan="5" class="text-center py-8 text-gray-500">
                    <i class="fas fa-folder text-4xl mb-2"></i>
                    <p>No categories found</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($categories as $category): ?>
                <?php
                // Count products in this category
                $this->db->where('category_id', $category->category_id);
                $product_count = $this->db->count_all_results('product_information');
                ?>
                <tr>
                    <td><?php echo $category->category_id; ?></td>
                    <td><strong><?php echo htmlspecialchars($category->category_name); ?></strong></td>
                    <td><?php echo htmlspecialchars($category->description ?? '-'); ?></td>
                    <td>
                        <?php if ($product_count > 0): ?>
                            <span class="badge badge-info"><?php echo $product_count; ?> products</span>
                        <?php else: ?>
                            <span class="text-gray-500">No products</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('categories/edit/' . $category->category_id); ?>"
                               class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $category->category_id; ?>, '<?php echo addslashes($category->category_name); ?>', <?php echo $product_count; ?>)"
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
        <?php echo render_pagination($pagination, 'categories'); ?>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<script>
function confirmDelete(categoryId, categoryName, productCount) {
    if (productCount > 0) {
        Swal.fire({
            title: 'Cannot Delete',
            text: `This category "${categoryName}" has ${productCount} product(s). Please reassign or delete the products first.`,
            icon: 'error',
            confirmButtonColor: '#3B82F6'
        });
        return;
    }

    Swal.fire({
        title: 'Delete Category?',
        text: `Are you sure you want to delete "${categoryName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('categories/delete/'); ?>${categoryId}`;
        }
    });
}
</script>
