<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h1 class="page-title">
            <?php echo isset($supplier) && $supplier ? 'Edit Supplier' : 'Add New Supplier'; ?>
        </h1>
        <p class="text-gray-600 mt-1">
            <?php echo isset($supplier) && $supplier ? 'Update supplier information' : 'Enter supplier details to create a new record'; ?>
        </p>
    </div>
    <div>
        <a href="<?php echo base_url('suppliers'); ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to List
        </a>
    </div>
</div>

<!-- Supplier Form -->
<form method="post" action="<?php echo $form_action; ?>" id="supplierForm">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main Information -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Basic Information</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Supplier Name -->
                        <div class="md:col-span-2">
                            <?php echo form_input_group(
                                'supplier_name',
                                'Supplier Name',
                                set_value('supplier_name', $supplier->supplier_name ?? ''),
                                true,
                                'text',
                                'Enter supplier/company name',
                                '<i class="fas fa-building"></i>'
                            ); ?>
                        </div>

                        <!-- Mobile -->
                        <div>
                            <?php echo form_input_group(
                                'supplier_mobile',
                                'Mobile Number',
                                set_value('supplier_mobile', $supplier->supplier_mobile ?? ''),
                                true,
                                'tel',
                                'Enter mobile number',
                                '<i class="fas fa-phone"></i>'
                            ); ?>
                        </div>

                        <!-- Email -->
                        <div>
                            <?php echo form_input_group(
                                'emailnumber',
                                'Email Address',
                                set_value('emailnumber', $supplier->emailnumber ?? ''),
                                false,
                                'email',
                                'Enter email address',
                                '<i class="fas fa-envelope"></i>'
                            ); ?>
                        </div>

                        <!-- Contact Person -->
                        <div class="md:col-span-2">
                            <?php echo form_input_group(
                                'contact',
                                'Contact Person',
                                set_value('contact', $supplier->contact ?? ''),
                                false,
                                'text',
                                'Enter contact person name',
                                '<i class="fas fa-user-tie"></i>'
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="card-title">Address Details</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Address -->
                        <div>
                            <?php echo form_textarea_group(
                                'address',
                                'Address',
                                set_value('address', $supplier->address ?? ''),
                                false,
                                'Enter complete address',
                                '<i class="fas fa-map-marker-alt"></i>',
                                ['rows' => 3]
                            ); ?>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- City -->
                            <div>
                                <?php echo form_input_group(
                                    'city',
                                    'City',
                                    set_value('city', $supplier->city ?? ''),
                                    false,
                                    'text',
                                    'Enter city',
                                    '<i class="fas fa-city"></i>'
                                ); ?>
                            </div>

                            <!-- State -->
                            <div>
                                <?php echo form_input_group(
                                    'state',
                                    'State/Province',
                                    set_value('state', $supplier->state ?? ''),
                                    false,
                                    'text',
                                    'Enter state or province',
                                    '<i class="fas fa-flag"></i>'
                                ); ?>
                            </div>

                            <!-- ZIP Code -->
                            <div>
                                <?php echo form_input_group(
                                    'zip',
                                    'ZIP/Postal Code',
                                    set_value('zip', $supplier->zip ?? ''),
                                    false,
                                    'text',
                                    'Enter ZIP or postal code',
                                    '<i class="fas fa-mailbox"></i>'
                                ); ?>
                            </div>

                            <!-- Country -->
                            <div>
                                <?php echo form_input_group(
                                    'country',
                                    'Country',
                                    set_value('country', $supplier->country ?? 'UAE'),
                                    false,
                                    'text',
                                    'Enter country',
                                    '<i class="fas fa-globe"></i>'
                                ); ?>
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div>
                            <?php echo form_textarea_group(
                                'details',
                                'Additional Details',
                                set_value('details', $supplier->details ?? ''),
                                false,
                                'Any additional notes or information',
                                '<i class="fas fa-info-circle"></i>',
                                ['rows' => 2]
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Info -->
            <?php if (isset($supplier) && $supplier): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Supplier Info</h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Supplier ID:</span>
                                <span class="font-medium">#<?php echo $supplier->supplier_id; ?></span>
                            </div>
                            <?php if (!empty($supplier->created_at)): ?>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Created:</span>
                                    <span class="font-medium"><?php echo format_date($supplier->created_at); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="card <?php echo isset($supplier) && $supplier ? 'mt-6' : ''; ?>">
                <div class="card-body">
                    <div class="space-y-3">
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-save"></i>
                            <?php echo isset($supplier) && $supplier ? 'Update Supplier' : 'Create Supplier'; ?>
                        </button>

                        <a href="<?php echo base_url('suppliers'); ?>" class="btn btn-outline w-full">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>

                        <?php if (isset($supplier) && $supplier): ?>
                            <hr class="my-4">
                            <button type="button"
                                    onclick="deleteSupplier(<?php echo $supplier->supplier_id; ?>, '<?php echo addslashes($supplier->supplier_name); ?>')"
                                    class="btn btn-danger w-full">
                                <i class="fas fa-trash"></i>
                                Delete Supplier
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Form Validation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('supplierForm');

    form.addEventListener('submit', function(e) {
        const supplierName = document.querySelector('[name="supplier_name"]');
        const supplierMobile = document.querySelector('[name="supplier_mobile"]');

        if (!supplierName.value.trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please enter supplier name'
            });
            supplierName.focus();
            return false;
        }

        if (!supplierMobile.value.trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please enter mobile number'
            });
            supplierMobile.focus();
            return false;
        }
    });
});

<?php if (isset($supplier) && $supplier): ?>
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
            window.location.href = '<?php echo base_url('suppliers/delete/' . $supplier->supplier_id); ?>';
        }
    });
}
<?php endif; ?>
</script>
