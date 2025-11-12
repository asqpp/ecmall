<?php
$is_edit = isset($category) && $category;
$form_title = $is_edit ? 'Edit Category' : 'Add New Category';
?>

<?php card_start($form_title); ?>

<form method="post" action="">
    <div class="max-w-2xl mx-auto">
        <div class="space-y-6">
            <div class="form-group">
                <label><i class="fas fa-folder"></i> Category Name *</label>
                <input type="text"
                       name="category_name"
                       value="<?php echo $is_edit ? htmlspecialchars($category->category_name) : ''; ?>"
                       class="form-control"
                       placeholder="e.g., Electronics, Insurance Products, Accessories"
                       required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Description</label>
                <textarea name="description"
                          class="form-control"
                          rows="4"
                          placeholder="Optional description of this category"><?php echo $is_edit ? htmlspecialchars($category->description ?? '') : ''; ?></textarea>
            </div>

            <?php if ($is_edit): ?>
                <?php
                // Count products in this category
                $this->db->where('category_id', $category->category_id);
                $product_count = $this->db->count_all_results('product_information');
                ?>
                <div class="bg-info-50 border border-info-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-info-600 mt-1"></i>
                        <div>
                            <p class="font-semibold text-info-900">Category Usage</p>
                            <p class="text-sm text-info-800 mt-1">
                                This category is currently used by <strong><?php echo $product_count; ?></strong> product(s).
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
            <a href="<?php echo base_url('categories'); ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Category' : 'Add Category'; ?>
            </button>
        </div>
    </div>
</form>

<?php card_end(); ?>

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
.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}
</style>
