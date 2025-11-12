<?php card_start('Chart of Accounts', true); ?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div class="flex gap-4 w-full md:w-auto">
        <form method="get" action="<?php echo base_url('accounts'); ?>" class="flex gap-2 flex-wrap">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search accounts..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            <select name="account_type" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Types</option>
                <?php foreach ($account_types as $type => $label): ?>
                    <option value="<?php echo $type; ?>" <?php echo ($account_type == $type) ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
            <?php if ($search || $account_type): ?>
                <a href="<?php echo base_url('accounts'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('accounts/trial_balance'); ?>" class="btn btn-secondary">
            <i class="fas fa-balance-scale"></i> Trial Balance
        </a>
        <a href="<?php echo base_url('accounts/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Account
        </a>
    </div>
</div>

<div class="overflow-x-auto">
    <?php table_start(['Code', 'Account Name', 'Type', 'Current Balance', 'Actions']); ?>
        <?php if (empty($accounts)): ?>
            <tr>
                <td colspan="5" class="text-center py-8 text-gray-500">
                    <i class="fas fa-book text-4xl mb-2"></i>
                    <p>No accounts found</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($accounts as $account): ?>
                <tr>
                    <td><code class="font-semibold"><?php echo htmlspecialchars($account->account_code); ?></code></td>
                    <td>
                        <strong><?php echo htmlspecialchars($account->account_name); ?></strong>
                        <?php if ($account->is_system ?? false): ?>
                            <span class="badge badge-info ml-2">System</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $type_badges = [
                            'asset' => 'badge-primary',
                            'liability' => 'badge-danger',
                            'equity' => 'badge-success',
                            'income' => 'badge-success',
                            'expense' => 'badge-warning'
                        ];
                        $badge_class = $type_badges[$account->account_type] ?? 'badge-secondary';
                        ?>
                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($account->account_type); ?></span>
                    </td>
                    <td>
                        <?php
                        $balance = $account->balance ?? 0;
                        $color = $balance >= 0 ? 'text-success-600' : 'text-danger-600';
                        ?>
                        <span class="font-semibold <?php echo $color; ?>">
                            <?php echo format_currency(abs($balance)); ?>
                            <?php echo $balance >= 0 ? 'Dr' : 'Cr'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('accounts/view/' . $account->account_id); ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo base_url('accounts/ledger/' . $account->account_id); ?>"
                               class="btn btn-sm btn-secondary" title="Ledger">
                                <i class="fas fa-book"></i>
                            </a>
                            <?php if (!($account->is_system ?? false)): ?>
                                <a href="<?php echo base_url('accounts/edit/' . $account->account_id); ?>"
                                   class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete(<?php echo $account->account_id; ?>)"
                                        class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
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
        <?php echo render_pagination($pagination, 'accounts'); ?>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<script>
function confirmDelete(accountId) {
    Swal.fire({
        title: 'Delete Account?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('accounts/delete/'); ?>${accountId}`;
        }
    });
}
</script>
