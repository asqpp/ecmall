<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="<?php echo base_url('accounts'); ?>" class="text-primary-600 hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Chart of Accounts
        </a>
        <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($account->account_name); ?></h1>
        <p class="text-gray-600">
            <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($account->account_code); ?></code>
        </p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('accounts/ledger/' . $account->account_id); ?>" class="btn btn-secondary">
            <i class="fas fa-book"></i> View Full Ledger
        </a>
        <?php if (!($account->is_system ?? false)): ?>
            <a href="<?php echo base_url('accounts/edit/' . $account->account_id); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Account Information -->
        <?php card_start('Account Information'); ?>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Account Code</label>
                <p class="font-semibold text-gray-900">
                    <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($account->account_code); ?></code>
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Account Type</label>
                <p>
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
                </p>
            </div>

            <div class="col-span-2">
                <label class="text-sm text-gray-600">Account Name</label>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($account->account_name); ?></p>
            </div>

            <?php if (!empty($account->description)): ?>
                <div class="col-span-2">
                    <label class="text-sm text-gray-600">Description</label>
                    <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($account->description)); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($account->is_system ?? false): ?>
                <div class="col-span-2">
                    <div class="bg-info-50 border border-info-200 rounded-lg p-3 text-sm">
                        <i class="fas fa-lock text-info-600"></i>
                        <span class="text-info-800">This is a system account and cannot be modified or deleted.</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php card_end(); ?>

        <!-- Recent Ledger Entries -->
        <?php card_start('Recent Transactions'); ?>
        <?php if (empty($ledger_entries)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-receipt text-4xl mb-2"></i>
                <p>No transactions found</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <?php table_start(['Date', 'Description', 'Debit', 'Credit', 'Balance']); ?>
                    <?php foreach ($ledger_entries as $entry): ?>
                        <tr>
                            <td><?php echo format_date($entry->date); ?></td>
                            <td><?php echo htmlspecialchars($entry->narration); ?></td>
                            <td class="text-success-600"><?php echo $entry->debit > 0 ? format_currency($entry->debit) : '-'; ?></td>
                            <td class="text-danger-600"><?php echo $entry->credit > 0 ? format_currency($entry->credit) : '-'; ?></td>
                            <td class="font-semibold">
                                <?php
                                $balance = $entry->running_balance;
                                $color = $balance >= 0 ? 'text-success-600' : 'text-danger-600';
                                ?>
                                <span class="<?php echo $color; ?>">
                                    <?php echo format_currency(abs($balance)); ?>
                                    <?php echo $balance >= 0 ? 'Dr' : 'Cr'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php table_end(); ?>
            </div>

            <?php if ($total_entries > 25): ?>
                <div class="text-center mt-4">
                    <p class="text-gray-600 mb-2">Showing last 25 of <?php echo $total_entries; ?> transactions</p>
                    <a href="<?php echo base_url('accounts/ledger/' . $account->account_id); ?>" class="btn btn-secondary">
                        <i class="fas fa-book"></i> View Full Ledger
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php card_end(); ?>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Balance Summary -->
        <?php card_start('Balance'); ?>
        <div class="text-center p-6 bg-primary-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-2">Current Balance</div>
            <div class="text-4xl font-bold text-primary-600">
                <?php
                $balance = $account->balance ?? 0;
                echo format_currency(abs($balance));
                ?>
            </div>
            <div class="text-sm text-gray-600 mt-2">
                <?php echo $balance >= 0 ? 'Debit Balance' : 'Credit Balance'; ?>
            </div>
        </div>
        <?php card_end(); ?>

        <!-- Quick Actions -->
        <?php card_start('Quick Actions'); ?>
        <div class="space-y-2">
            <a href="<?php echo base_url('accounts/ledger/' . $account->account_id); ?>"
               class="btn btn-block btn-secondary">
                <i class="fas fa-book"></i> View Full Ledger
            </a>
            <?php if (!($account->is_system ?? false)): ?>
                <a href="<?php echo base_url('accounts/edit/' . $account->account_id); ?>"
                   class="btn btn-block btn-primary">
                    <i class="fas fa-edit"></i> Edit Account
                </a>
            <?php endif; ?>
            <a href="<?php echo base_url('accounts'); ?>" class="btn btn-block btn-outline">
                <i class="fas fa-list"></i> All Accounts
            </a>
        </div>
        <?php card_end(); ?>
    </div>
</div>
