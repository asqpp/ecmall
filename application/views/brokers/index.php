<?php card_start('Brokers', true); ?>

<div class="flex justify-between items-center mb-6">
    <div class="flex gap-4">
        <form method="get" action="<?php echo base_url('brokers'); ?>" class="flex gap-2">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search brokers..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search): ?>
                <a href="<?php echo base_url('brokers'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('brokers/commission_report'); ?>" class="btn btn-secondary">
            <i class="fas fa-chart-line"></i> Commission Report
        </a>
        <a href="<?php echo base_url('brokers/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Broker
        </a>
    </div>
</div>

<div class="overflow-x-auto">
    <?php table_start(['ID', 'Code', 'Broker Name', 'Contact Person', 'Mobile', 'Email', 'Commission Rate', 'Actions']); ?>
        <?php if (empty($brokers)): ?>
            <tr>
                <td colspan="8" class="text-center py-8 text-gray-500">
                    <i class="fas fa-users text-4xl mb-2"></i>
                    <p>No brokers found</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($brokers as $broker): ?>
                <tr>
                    <td><?php echo $broker->broker_id; ?></td>
                    <td><code class="text-sm"><?php echo htmlspecialchars($broker->broker_code ?? 'N/A'); ?></code></td>
                    <td><strong><?php echo htmlspecialchars($broker->broker_name); ?></strong></td>
                    <td><?php echo htmlspecialchars($broker->contact_person ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($broker->mobile); ?></td>
                    <td><?php echo htmlspecialchars($broker->email ?? 'N/A'); ?></td>
                    <td><span class="badge badge-success"><?php echo number_format($broker->commission_rate ?? 0, 2); ?>%</span></td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('brokers/view/' . $broker->broker_id); ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo base_url('brokers/edit/' . $broker->broker_id); ?>"
                               class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $broker->broker_id; ?>)"
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
        <?php echo render_pagination($pagination, 'brokers'); ?>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<script>
function confirmDelete(brokerId) {
    Swal.fire({
        title: 'Delete Broker?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('brokers/delete/'); ?>${brokerId}`;
        }
    });
}
</script>
