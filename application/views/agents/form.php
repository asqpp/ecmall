<?php
$is_edit = isset($agent) && $agent;
$form_title = $is_edit ? 'Edit Agent' : 'Add New Agent';
?>

<?php card_start($form_title); ?>

<form method="post" action="">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column -->
        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

                <div class="form-group">
                    <label><i class="fas fa-user-tie"></i> Agent Name *</label>
                    <input type="text"
                           name="agent_name"
                           value="<?php echo $is_edit ? htmlspecialchars($agent->agent_name) : ''; ?>"
                           class="form-control"
                           required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Contact Person</label>
                    <input type="text"
                           name="contact_person"
                           value="<?php echo $is_edit ? htmlspecialchars($agent->contact_person ?? '') : ''; ?>"
                           class="form-control">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Mobile *</label>
                    <input type="text"
                           name="mobile"
                           value="<?php echo $is_edit ? htmlspecialchars($agent->mobile) : ''; ?>"
                           class="form-control"
                           required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email"
                           name="email"
                           value="<?php echo $is_edit ? htmlspecialchars($agent->email ?? '') : ''; ?>"
                           class="form-control">
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Commission Settings -->
            <div class="bg-success-50 border border-success-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Commission Settings</h3>

                <div class="form-group">
                    <label><i class="fas fa-percentage"></i> Commission Rate (%) *</label>
                    <input type="number"
                           name="commission_rate"
                           value="<?php echo $is_edit ? $agent->commission_rate : '0'; ?>"
                           class="form-control"
                           min="0"
                           max="100"
                           step="0.01"
                           required>
                    <small class="text-gray-600">Percentage of sales as commission</small>
                </div>

                <?php if ($is_edit && isset($agent->agent_code)): ?>
                    <div class="form-group">
                        <label><i class="fas fa-barcode"></i> Agent Code</label>
                        <input type="text"
                               value="<?php echo htmlspecialchars($agent->agent_code); ?>"
                               class="form-control"
                               readonly
                               disabled>
                        <small class="text-gray-600">Auto-generated code</small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Address Information -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Address Information</h3>

                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea name="address"
                              class="form-control"
                              rows="4"><?php echo $is_edit ? htmlspecialchars($agent->address ?? '') : ''; ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
        <a href="<?php echo base_url('agents'); ?>" class="btn btn-outline">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Agent' : 'Add Agent'; ?>
        </button>
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
