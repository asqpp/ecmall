<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="text-gray-600 mt-1">Welcome back, <?php echo $this->session->userdata('username'); ?>!</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="btn btn-outline">
            <i class="fas fa-download"></i>
            Export Report
        </button>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            New Invoice
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <?php
    render_stat_card(
        'Total Sales (YTD)',
        format_currency($stats['total_sales']),
        '<i class="fas fa-arrow-up"></i> ' . number_format($stats['sales_growth'], 1) . '% from last month',
        'fas fa-chart-line',
        'bg-gradient-primary',
        0
    );
    ?>

    <?php
    render_stat_card(
        'Total Customers',
        number_format($stats['total_customers']),
        '<i class="fas fa-users"></i> Active customers',
        'fas fa-users',
        'bg-gradient-success',
        100
    );
    ?>

    <?php
    render_stat_card(
        'Outstanding',
        format_currency($stats['outstanding']),
        '<i class="fas fa-exclamation-circle"></i> Pending collection',
        'fas fa-money-bill-wave',
        'bg-gradient-warning',
        200
    );
    ?>

    <?php
    render_stat_card(
        'Today\'s Sales',
        format_currency($stats['today_sales']),
        '<i class="fas fa-calendar-day"></i> Today\'s revenue',
        'fas fa-cash-register',
        'bg-gradient-info',
        300
    );
    ?>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Sales vs Purchases Chart -->
    <div class="card" data-aos="fade-up">
        <div class="card-header">
            <h3 class="card-title">Sales vs Purchases (Last 6 Months)</h3>
        </div>
        <div class="card-body">
            <div class="h-80">
                <canvas id="salesPurchasesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Product Distribution Chart -->
    <div class="card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header">
            <h3 class="card-title">Product/Policy Distribution</h3>
        </div>
        <div class="card-body">
            <div class="h-80">
                <canvas id="policyDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Chart -->
<div class="card mb-8" data-aos="fade-up">
    <div class="card-header">
        <h3 class="card-title">Monthly Revenue Analysis</h3>
    </div>
    <div class="card-body">
        <div class="h-80">
            <canvas id="monthlyRevenueChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activities & Top Customers -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Invoices -->
    <div class="card" data-aos="fade-up">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title">Recent Invoices</h3>
            <a href="<?php echo base_url('sales'); ?>" class="text-primary-600 hover:text-primary-700 text-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-gray-200">
                <?php if (!empty($recent_invoices)): ?>
                    <?php foreach ($recent_invoices as $invoice): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-sm"><?php echo $invoice->invoice; ?></span>
                                <?php echo status_badge($invoice->payment_status); ?>
                            </div>
                            <div class="text-sm text-gray-600 mb-1">
                                <?php echo $invoice->customer_name; ?>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span><?php echo format_date($invoice->date); ?></span>
                                <span class="font-medium text-gray-900">
                                    <?php echo format_currency($invoice->grand_total); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>No recent invoices</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pending Payments -->
    <div class="card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title">Pending Payments</h3>
            <a href="<?php echo base_url('sales?status=partial'); ?>" class="text-warning-600 hover:text-warning-700 text-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-gray-200">
                <?php if (!empty($pending_payments)): ?>
                    <?php foreach ($pending_payments as $payment): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-sm"><?php echo $payment->invoice; ?></span>
                                <span class="text-warning-600 font-medium text-sm">
                                    <?php echo format_currency($payment->due_amount); ?>
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-1">
                                <?php echo $payment->customer_name; ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                Due: <?php echo format_date($payment->date); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-check-circle text-3xl mb-2 text-success-500"></i>
                        <p>All payments cleared!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="card" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title">Top Customers</h3>
            <a href="<?php echo base_url('customers'); ?>" class="text-primary-600 hover:text-primary-700 text-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-gray-200">
                <?php if (!empty($top_customers)): ?>
                    <?php $rank = 1; ?>
                    <?php foreach ($top_customers as $customer): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-sm">
                                    <?php echo $rank++; ?>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-sm mb-1">
                                        <?php echo $customer->customer_name; ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mb-2">
                                        <?php echo $customer->invoice_count; ?> invoices
                                    </div>
                                    <div class="text-sm font-medium text-success-600">
                                        <?php echo format_currency($customer->total_purchase); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-user-friends text-3xl mb-2"></i>
                        <p>No data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions (Floating Action Button) -->
<div class="fixed bottom-8 right-8 z-50" x-data="{ open: false }">
    <button @click="open = !open" class="w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all">
        <i class="fas" :class="open ? 'fa-times' : 'fa-plus'"></i>
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        class="absolute bottom-16 right-0 bg-white rounded-lg shadow-xl p-2 min-w-[200px]"
    >
        <a href="<?php echo base_url('sales/add'); ?>" class="dropdown-item">
            <i class="fas fa-file-invoice"></i> New Invoice
        </a>
        <a href="<?php echo base_url('quotations/add'); ?>" class="dropdown-item">
            <i class="fas fa-file-alt"></i> New Quotation
        </a>
        <a href="<?php echo base_url('receipts/add'); ?>" class="dropdown-item">
            <i class="fas fa-money-bill-wave"></i> New Receipt
        </a>
        <a href="<?php echo base_url('customers/add'); ?>" class="dropdown-item">
            <i class="fas fa-user-plus"></i> New Customer
        </a>
    </div>
</div>

<!-- Chart Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Sales vs Purchases Chart
    const salesPurchasesCtx = document.getElementById('salesPurchasesChart');
    if (salesPurchasesCtx) {
        new Chart(salesPurchasesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($sales_chart_data['months']); ?>,
                datasets: [
                    {
                        label: 'Sales',
                        data: <?php echo json_encode($sales_chart_data['sales']); ?>,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Purchases',
                        data: <?php echo json_encode($sales_chart_data['purchases']); ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': AED ' +
                                    context.parsed.y.toLocaleString('en-AE', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'AED ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Policy Distribution Chart
    const policyDistributionCtx = document.getElementById('policyDistributionChart');
    if (policyDistributionCtx) {
        new Chart(policyDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($policy_distribution['labels']); ?>,
                datasets: [{
                    data: <?php echo json_encode($policy_distribution['data']); ?>,
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': AED ' +
                                    context.parsed.toLocaleString('en-AE', {
                                        minimumFractionDigits: 2
                                    }) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Revenue Chart
    const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart');
    if (monthlyRevenueCtx) {
        new Chart(monthlyRevenueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($monthly_revenue['months']); ?>,
                datasets: [
                    {
                        label: 'Revenue',
                        data: <?php echo json_encode($monthly_revenue['revenue']); ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)'
                    },
                    {
                        label: 'Collected',
                        data: <?php echo json_encode($monthly_revenue['collected']); ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)'
                    },
                    {
                        label: 'Pending',
                        data: <?php echo json_encode($monthly_revenue['pending']); ?>,
                        backgroundColor: 'rgba(245, 158, 11, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': AED ' +
                                    context.parsed.y.toLocaleString('en-AE', {
                                        minimumFractionDigits: 2
                                    });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: false
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'AED ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
