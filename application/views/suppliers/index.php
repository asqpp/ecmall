<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h1 class="page-title">Suppliers</h1>
        <p class="text-gray-600 mt-1">Manage all your suppliers and vendor information</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?php echo base_url('suppliers/export?' . http_build_query(['search' => $search])); ?>" class="btn btn-outline">
            <i class="fas fa-download"></i>
            Export CSV
        </a>
        <a href="<?php echo base_url('suppliers/add'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Supplier
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <form method="get" action="<?php echo base_url('suppliers'); ?>" class="flex items-center gap-4">
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
                <a href="<?php echo base_url('suppliers'); ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Suppliers Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($suppliers)): ?>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>City</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="font-medium"><?php echo $supplier->supplier_id; ?></td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                            <?php echo strtoupper(substr($supplier->supplier_name, 0, 2)); ?>
                                        </div>
                                        <div>
                                            <div class="font-medium"><?php echo htmlspecialchars($supplier->supplier_name); ?></div>
                                            <?php if (!empty($supplier->contact)): ?>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($supplier->contact); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-phone text-gray-400 mr-1"></i>
                                    <?php echo htmlspecialchars($supplier->supplier_mobile); ?>
                                </td>
                                <td>
                                    <?php if (!empty($supplier->emailnumber)): ?>
                                        <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                        <?php echo htmlspecialchars($supplier->emailnumber); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($supplier->city ?? '-'); ?></td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?php echo base_url('suppliers/view/' . $supplier->supplier_id); ?>"
                                           class="btn-icon btn-icon-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo base_url('suppliers/edit/' . $supplier->supplier_id); ?>"
                                           class="btn-icon btn-icon-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteSupplier(<?php echo $supplier->supplier_id; ?>, '<?php echo addslashes($supplier->supplier_name); ?>')"
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
                    echo render_pagination($pagination, 'suppliers', $pagination_params);
                    ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="p-12 text-center">
                <i class="fas fa-truck text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-700 mb-2">No suppliers found</h3>
                <p class="text-gray-500 mb-6">
                    <?php if (!empty($search)): ?>
                        No suppliers match your search criteria. Try different keywords.
                    <?php else: ?>
                        Get started by adding your first supplier.
                    <?php endif; ?>
                </p>
                <a href="<?php echo base_url('suppliers/add'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Your First Supplier
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Stats -->
<?php if (!empty($suppliers)): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-truck text-xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Total Suppliers</div>
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
                        <div class="text-2xl font-bold"><?php echo count($suppliers); ?> records</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Delete Confirmation Script -->
<script>
function deleteSupplier(supplierId, supplierName) {
    Swal.fire({
        title: 'Delete Supplier?',
        html: `Are you sure you want to delete <strong>${supplierName}</strong>?<br><br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo base_url('suppliers/delete/'); ?>' + supplierId;
        }
    });
}
</script>
