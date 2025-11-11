<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h1 class="page-title">Customers</h1>
        <p class="text-gray-600 mt-1">Manage all your customers and their information</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?php echo base_url('customers/export?' . http_build_query(['search' => $search])); ?>" class="btn btn-outline">
            <i class="fas fa-download"></i>
            Export CSV
        </a>
        <a href="<?php echo base_url('customers/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Customer
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <form method="get" action="<?php echo base_url('customers'); ?>" class="flex items-center gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input
                        type="search"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search by name, mobile, email..."
                        class="form-input pl-10"
                    >
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
                Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?php echo base_url('customers'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($customers)): ?>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Outstanding</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="font-medium"><?php echo $customer->customer_id; ?></td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold">
                                            <?php echo strtoupper(substr($customer->customer_name, 0, 2)); ?>
                                        </div>
                                        <div>
                                            <div class="font-medium"><?php echo htmlspecialchars($customer->customer_name); ?></div>
                                            <?php if (!empty($customer->contact)): ?>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($customer->contact); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-phone text-gray-400 mr-1"></i>
                                    <?php echo htmlspecialchars($customer->customer_mobile); ?>
                                </td>
                                <td>
                                    <?php if (!empty($customer->customer_email)): ?>
                                        <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                        <?php echo htmlspecialchars($customer->customer_email); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($customer->city ?? '-'); ?></td>
                                <td>
                                    <?php
                                    $outstanding = $customer->outstanding ?? 0;
                                    if ($outstanding > 0): ?>
                                        <span class="badge badge-warning">
                                            <?php echo format_currency($outstanding); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-success-600 font-medium">
                                            <?php echo format_currency(0); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?php echo base_url('customers/view/' . $customer->customer_id); ?>"
                                           class="btn-icon btn-icon-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo base_url('customers/edit/' . $customer->customer_id); ?>"
                                           class="btn-icon btn-icon-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteCustomer(<?php echo $customer->customer_id; ?>, '<?php echo addslashes($customer->customer_name); ?>')"
                                                class="btn-icon btn-icon-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination->total_pages > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php
                    $pagination_params = ['search' => $search];
                    echo render_pagination($pagination, 'customers', $pagination_params);
                    ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="p-12 text-center">
                <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-700 mb-2">No customers found</h3>
                <p class="text-gray-500 mb-6">
                    <?php if (!empty($search)): ?>
                        No customers match your search criteria. Try different keywords.
                    <?php else: ?>
                        Get started by adding your first customer.
                    <?php endif; ?>
                </p>
                <a href="<?php echo base_url('customers/add'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Your First Customer
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Stats -->
<?php if (!empty($customers)): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Total Customers</div>
                        <div class="text-2xl font-bold"><?php echo number_format($pagination->total); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-success-100 text-success-600 flex items-center justify-center">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Current Page</div>
                        <div class="text-2xl font-bold"><?php echo $pagination->current_page; ?> of <?php echo $pagination->total_pages; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-warning-100 text-warning-600 flex items-center justify-center">
                        <i class="fas fa-list text-xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Showing</div>
                        <div class="text-2xl font-bold"><?php echo count($customers); ?> records</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Delete Confirmation Script -->
<script>
function deleteCustomer(customerId, customerName) {
    Swal.fire({
        title: 'Delete Customer?',
        html: `Are you sure you want to delete <strong>${customerName}</strong>?<br><br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo base_url('customers/delete/'); ?>' + customerId;
        }
    });
}
</script>
