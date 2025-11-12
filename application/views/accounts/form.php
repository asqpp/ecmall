<?php
$is_edit = isset($account) && $account;
$form_title = $is_edit ? 'Edit Account' : 'Add New Account';
?>

<?php card_start($form_title); ?>

<form method="post" action="">
    <div class="max-w-3xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group">
                <label><i class="fas fa-barcode"></i> Account Code *</label>
                <input type="text"
                       name="account_code"
                       value="<?php echo $is_edit ? htmlspecialchars($account->account_code) : ''; ?>"
                       class="form-control"
                       placeholder="e.g., CASH, BANK01, EXP-001"
                       required>
                <small class="text-gray-600">Unique code for this account</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-tag"></i> Account Type *</label>
                <select name="account_type" class="form-control" required>
                    <option value="">Select Type...</option>
                    <?php foreach ($account_types as $type => $label): ?>
                        <option value="<?php echo $type; ?>"
                                <?php echo ($is_edit && $account->account_type == $type) ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group md:col-span-2">
                <label><i class="fas fa-file-alt"></i> Account Name *</label>
                <input type="text"
                       name="account_name"
                       value="<?php echo $is_edit ? htmlspecialchars($account->account_name) : ''; ?>"
                       class="form-control"
                       placeholder="e.g., Cash on Hand, Bank Account - ABC Bank"
                       required>
            </div>

            <div class="form-group md:col-span-2">
                <label><i class="fas fa-align-left"></i> Description</label>
                <textarea name="description"
                          class="form-control"
                          rows="4"
                          placeholder="Optional description or notes about this account"><?php echo $is_edit ? htmlspecialchars($account->description ?? '') : ''; ?></textarea>
            </div>
        </div>

        <!-- Account Type Guide -->
        <div class="mt-6 bg-info-50 border border-info-200 rounded-lg p-4">
            <h4 class="font-semibold mb-2 text-info-900"><i class="fas fa-info-circle"></i> Account Type Guide</h4>
            <ul class="text-sm text-info-800 space-y-1">
                <li><strong>Asset:</strong> Cash, Bank, Receivables, Inventory, Fixed Assets</li>
                <li><strong>Liability:</strong> Payables, Loans, Accrued Expenses</li>
                <li><strong>Equity:</strong> Capital, Retained Earnings, Drawings</li>
                <li><strong>Income:</strong> Sales Revenue, Service Income, Other Income</li>
                <li><strong>Expense:</strong> Operating Expenses, Cost of Goods Sold</li>
            </ul>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
            <a href="<?php echo base_url('accounts'); ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Account' : 'Add Account'; ?>
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
