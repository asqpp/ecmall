<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h1 class="page-title">
            <?php echo isset($customer) && $customer ? 'Edit Customer' : 'Add New Customer'; ?>
        </h1>
        <p class="text-gray-600 mt-1">
            <?php echo isset($customer) && $customer ? 'Update customer information' : 'Enter customer details to create a new record'; ?>
        </p>
    </div>
    <div>
        <a href="<?php echo base_url('customers'); ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to List
        </a>
    </div>
</div>

<!-- Customer Form -->
<form method="post" action="<?php echo $form_action; ?>" id="customerForm">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main Information -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Basic Information</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Customer Name -->
                        <div class="md:col-span-2">
                            <?php echo form_input_group(
                                'customer_name',
                                'Customer Name',
                                set_value('customer_name', $customer->customer_name ?? ''),
                                true,
                                'text',
                                'Enter full customer name',
                                '<i class="fas fa-user"></i>'
                            ); ?>
                        </div>

                        <!-- Mobile -->
                        <div>
                            <?php echo form_input_group(
                                'customer_mobile',
                                'Mobile Number',
                                set_value('customer_mobile', $customer->customer_mobile ?? ''),
                                true,
                                'tel',
                                'Enter mobile number',
                                '<i class="fas fa-phone"></i>'
                            ); ?>
                        </div>

                        <!-- Email -->
                        <div>
                            <?php echo form_input_group(
                                'customer_email',
                                'Email Address',
                                set_value('customer_email', $customer->customer_email ?? ''),
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
                                set_value('contact', $customer->contact ?? ''),
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
                        <!-- Address Line 1 -->
                        <div>
                            <?php echo form_input_group(
                                'customer_address_1',
                                'Address Line 1',
                                set_value('customer_address_1', $customer->customer_address_1 ?? ''),
                                false,
                                'text',
                                'Street address, P.O. box, company name',
                                '<i class="fas fa-map-marker-alt"></i>'
                            ); ?>
                        </div>

                        <!-- Address Line 2 -->
                        <div>
                            <?php echo form_input_group(
                                'customer_address_2',
                                'Address Line 2',
                                set_value('customer_address_2', $customer->customer_address_2 ?? ''),
                                false,
                                'text',
                                'Apartment, suite, unit, building, floor',
                                '<i class="fas fa-map-marker-alt"></i>'
                            ); ?>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- City -->
                            <div>
                                <?php echo form_input_group(
                                    'city',
                                    'City',
                                    set_value('city', $customer->city ?? ''),
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
                                    set_value('state', $customer->state ?? ''),
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
                                    set_value('zip', $customer->zip ?? ''),
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
                                    set_value('country', $customer->country ?? 'UAE'),
                                    false,
                                    'text',
                                    'Enter country',
                                    '<i class="fas fa-globe"></i>'
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Financial Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Financial Settings</h3>
                </div>
                <div class="card-body">
                    <!-- Credit Limit -->
                    <?php echo form_input_group(
                        'credit_limit',
                        'Credit Limit',
                        set_value('credit_limit', $customer->credit_limit ?? 0),
                        false,
                        'number',
                        '0.00',
                        '<i class="fas fa-money-bill-wave"></i>',
                        ['step' => '0.01', 'min' => '0']
                    ); ?>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Note:</strong>
                            <p class="text-sm">Set 0 for unlimited credit limit. This controls the maximum outstanding amount allowed for this customer.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Info -->
            <?php if (isset($customer) && $customer): ?>
                <div class="card mt-6">
                    <div class="card-header">
                        <h3 class="card-title">Customer Info</h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Customer ID:</span>
                                <span class="font-medium">#<?php echo $customer->customer_id; ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Purchases:</span>
                                <span class="font-medium text-primary-600">
                                    <?php echo format_currency($customer->total_purchase ?? 0); ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Outstanding:</span>
                                <span class="font-medium <?php echo ($customer->outstanding ?? 0) > 0 ? 'text-warning-600' : 'text-success-600'; ?>">
                                    <?php echo format_currency($customer->outstanding ?? 0); ?>
                                </span>
                            </div>
                            <?php if (!empty($customer->created_at)): ?>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Member Since:</span>
                                    <span class="font-medium"><?php echo format_date($customer->created_at); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="card mt-6">
                <div class="card-body">
                    <div class="space-y-3">
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-save"></i>
                            <?php echo isset($customer) && $customer ? 'Update Customer' : 'Create Customer'; ?>
                        </button>

                        <a href="<?php echo base_url('customers'); ?>" class="btn btn-outline w-full">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>

                        <?php if (isset($customer) && $customer): ?>
                            <hr class="my-4">
                            <button type="button"
                                    onclick="deleteCustomer(<?php echo $customer->customer_id; ?>, '<?php echo addslashes($customer->customer_name); ?>')"
                                    class="btn btn-danger w-full">
                                <i class="fas fa-trash"></i>
                                Delete Customer
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
    const form = document.getElementById('customerForm');

    form.addEventListener('submit', function(e) {
        // Basic client-side validation
        const customerName = document.querySelector('[name="customer_name"]');
        const customerMobile = document.querySelector('[name="customer_mobile"]');

        if (!customerName.value.trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please enter customer name'
            });
            customerName.focus();
            return false;
        }

        if (!customerMobile.value.trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please enter mobile number'
            });
            customerMobile.focus();
            return false;
        }
    });
});

<?php if (isset($customer) && $customer): ?>
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
            window.location.href = '<?php echo base_url('customers/delete/' . $customer->customer_id); ?>';
        }
    });
}
<?php endif; ?>
</script>
