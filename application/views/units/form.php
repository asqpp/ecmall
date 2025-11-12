<?php
$is_edit = isset($unit) && $unit;
$form_title = $is_edit ? 'Edit Unit' : 'Add New Unit';
?>

<?php card_start($form_title); ?>

<form method="post" action="">
    <div class="max-w-2xl mx-auto">
        <div class="space-y-6">
            <div class="form-group">
                <label><i class="fas fa-ruler"></i> Unit Name *</label>
                <input type="text"
                       name="unit_name"
                       value="<?php echo $is_edit ? htmlspecialchars($unit->unit_name) : ''; ?>"
                       class="form-control"
                       placeholder="e.g., Piece, Kilogram, Liter, Box"
                       required>
                <small class="text-gray-600">Full name of the unit</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-tag"></i> Short Name / Abbreviation *</label>
                <input type="text"
                       name="unit_short_name"
                       value="<?php echo $is_edit ? htmlspecialchars($unit->unit_short_name) : ''; ?>"
                       class="form-control"
                       placeholder="e.g., pc, kg, ltr, box"
                       required>
                <small class="text-gray-600">Short abbreviation used in invoices and labels</small>
            </div>

            <?php if ($is_edit): ?>
                <?php
                // Count products using this unit
                $this->db->where('unit_id', $unit->unit_id);
                $product_count = $this->db->count_all_results('product_information');
                ?>
                <div class="bg-info-50 border border-info-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-info-600 mt-1"></i>
                        <div>
                            <p class="font-semibold text-info-900">Unit Usage</p>
                            <p class="text-sm text-info-800 mt-1">
                                This unit is currently used by <strong><?php echo $product_count; ?></strong> product(s).
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Examples -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold mb-3">Examples of Units:</h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <strong>Quantity Units:</strong>
                        <ul class="list-disc list-inside text-gray-700 mt-1">
                            <li>Piece (pc)</li>
                            <li>Set (set)</li>
                            <li>Dozen (dz)</li>
                            <li>Gross (gr)</li>
                        </ul>
                    </div>
                    <div>
                        <strong>Weight Units:</strong>
                        <ul class="list-disc list-inside text-gray-700 mt-1">
                            <li>Kilogram (kg)</li>
                            <li>Gram (g)</li>
                            <li>Ton (ton)</li>
                            <li>Pound (lb)</li>
                        </ul>
                    </div>
                    <div>
                        <strong>Volume Units:</strong>
                        <ul class="list-disc list-inside text-gray-700 mt-1">
                            <li>Liter (ltr)</li>
                            <li>Milliliter (ml)</li>
                            <li>Gallon (gal)</li>
                        </ul>
                    </div>
                    <div>
                        <strong>Length Units:</strong>
                        <ul class="list-disc list-inside text-gray-700 mt-1">
                            <li>Meter (m)</li>
                            <li>Centimeter (cm)</li>
                            <li>Foot (ft)</li>
                            <li>Inch (in)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
            <a href="<?php echo base_url('units'); ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Unit' : 'Add Unit'; ?>
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
.form-group small {
    display: block;
    margin-top: 0.25rem;
}
</style>
