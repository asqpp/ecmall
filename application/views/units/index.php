<?php card_start('Units of Measurement', true); ?>

<div class="flex justify-between items-center mb-6">
    <div class="flex gap-4">
        <form method="get" action="<?php echo base_url('units'); ?>" class="flex gap-2">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search units..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search): ?>
                <a href="<?php echo base_url('units'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('units/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Unit
        </a>
    </div>
</div>

<div class="overflow-x-auto">
    <?php table_start(['ID', 'Unit Name', 'Short Name', 'Products', 'Actions']); ?>
        <?php if (empty($units)): ?>
            <tr>
                <td colspan="5" class="text-center py-8 text-gray-500">
                    <i class="fas fa-ruler text-4xl mb-2"></i>
                    <p>No units found</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($units as $unit): ?>
                <?php
                // Count products using this unit
                $this->db->where('unit_id', $unit->unit_id);
                $product_count = $this->db->count_all_results('product_information');
                ?>
                <tr>
                    <td><?php echo $unit->unit_id; ?></td>
                    <td><strong><?php echo htmlspecialchars($unit->unit_name); ?></strong></td>
                    <td><code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($unit->unit_short_name); ?></code></td>
                    <td>
                        <?php if ($product_count > 0): ?>
                            <span class="badge badge-info"><?php echo $product_count; ?> products</span>
                        <?php else: ?>
                            <span class="text-gray-500">No products</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('units/edit/' . $unit->unit_id); ?>"
                               class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $unit->unit_id; ?>, '<?php echo addslashes($unit->unit_name); ?>', <?php echo $product_count; ?>)"
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
        <?php echo render_pagination($pagination, 'units'); ?>
    </div>
<?php endif; ?>

<!-- Common Units Reference -->
<div class="mt-6 bg-info-50 border border-info-200 rounded-lg p-4">
    <h4 class="font-semibold mb-2 text-info-900"><i class="fas fa-info-circle"></i> Common Units of Measurement</h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-info-800">
        <div><strong>Piece:</strong> pc, pcs</div>
        <div><strong>Set:</strong> set</div>
        <div><strong>Box:</strong> box</div>
        <div><strong>Pack:</strong> pack</div>
        <div><strong>Kilogram:</strong> kg</div>
        <div><strong>Liter:</strong> ltr, L</div>
        <div><strong>Meter:</strong> m</div>
        <div><strong>Unit:</strong> unit</div>
    </div>
</div>

<?php card_end(); ?>

<script>
function confirmDelete(unitId, unitName, productCount) {
    if (productCount > 0) {
        Swal.fire({
            title: 'Cannot Delete',
            text: `This unit "${unitName}" is used by ${productCount} product(s). Please reassign or delete the products first.`,
            icon: 'error',
            confirmButtonColor: '#3B82F6'
        });
        return;
    }

    Swal.fire({
        title: 'Delete Unit?',
        text: `Are you sure you want to delete "${unitName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('units/delete/'); ?>${unitId}`;
        }
    });
}
</script>
