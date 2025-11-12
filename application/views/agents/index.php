<?php card_start('Agents', true); ?>

<div class="flex justify-between items-center mb-6">
    <div class="flex gap-4">
        <form method="get" action="<?php echo base_url('agents'); ?>" class="flex gap-2">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search agents..."
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search): ?>
                <a href="<?php echo base_url('agents'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo base_url('agents/commission_report'); ?>" class="btn btn-secondary">
            <i class="fas fa-chart-line"></i> Commission Report
        </a>
        <a href="<?php echo base_url('agents/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Agent
        </a>
    </div>
</div>

<div class="overflow-x-auto">
    <?php table_start(['ID', 'Code', 'Agent Name', 'Contact Person', 'Mobile', 'Email', 'Commission Rate', 'Actions']); ?>
        <?php if (empty($agents)): ?>
            <tr>
                <td colspan="8" class="text-center py-8 text-gray-500">
                    <i class="fas fa-user-tie text-4xl mb-2"></i>
                    <p>No agents found</p>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($agents as $agent): ?>
                <tr>
                    <td><?php echo $agent->agent_id; ?></td>
                    <td><code class="text-sm"><?php echo htmlspecialchars($agent->agent_code ?? 'N/A'); ?></code></td>
                    <td><strong><?php echo htmlspecialchars($agent->agent_name); ?></strong></td>
                    <td><?php echo htmlspecialchars($agent->contact_person ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($agent->mobile); ?></td>
                    <td><?php echo htmlspecialchars($agent->email ?? 'N/A'); ?></td>
                    <td><span class="badge badge-success"><?php echo number_format($agent->commission_rate ?? 0, 2); ?>%</span></td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('agents/view/' . $agent->agent_id); ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo base_url('agents/edit/' . $agent->agent_id); ?>"
                               class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $agent->agent_id; ?>)"
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
        <?php echo render_pagination($pagination, 'agents'); ?>
    </div>
<?php endif; ?>

<?php card_end(); ?>

<script>
function confirmDelete(agentId) {
    Swal.fire({
        title: 'Delete Agent?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url('agents/delete/'); ?>${agentId}`;
        }
    });
}
</script>
