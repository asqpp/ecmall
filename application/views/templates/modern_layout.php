<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: $persist(false) }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Insurance ERP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo base_url('assets/images/favicon.ico'); ?>">

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/output.css'); ?>">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- AOS CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

    <!-- Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css">

    <!-- Additional CSS -->
    <?php if(isset($additional_css)): ?>
        <?php foreach($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50 antialiased" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">

    <!-- Page Container -->
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside
            class="bg-gray-900 text-white w-64 flex-shrink-0 overflow-y-auto transition-all duration-300 fixed lg:static z-40"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            x-show="sidebarOpen"
            @click.away="if(window.innerWidth < 1024) sidebarOpen = false"
        >
            <!-- Logo -->
            <div class="p-6 border-b border-gray-800">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">
                    <i class="fas fa-shield-alt"></i> Insurance ERP
                </h1>
                <p class="text-sm text-gray-400 mt-1">NA-FIX Solutions</p>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <!-- Dashboard -->
                <a href="<?php echo base_url('dashboard'); ?>" class="sidebar-link <?php echo $this->uri->segment(1) == 'dashboard' ? 'sidebar-link-active' : ''; ?>">
                    <i class="fas fa-home w-5"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Masters -->
                <div x-data="{ open: <?php echo in_array($this->uri->segment(1), ['customers', 'suppliers', 'brokers', 'agents', 'products', 'accounts']) ? 'true' : 'false'; ?> }">
                    <button @click="open = !open" class="sidebar-link w-full justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-database w-5"></i>
                            <span>Masters</span>
                        </div>
                        <i class="fas fa-chevron-down transform transition-transform" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="<?php echo base_url('customers'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'customers' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-users w-4"></i> Customers
                        </a>
                        <a href="<?php echo base_url('suppliers'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'suppliers' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-truck w-4"></i> Suppliers
                        </a>
                        <a href="<?php echo base_url('brokers'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'brokers' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-handshake w-4"></i> Brokers
                        </a>
                        <a href="<?php echo base_url('agents'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'agents' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-user-tie w-4"></i> Agents
                        </a>
                        <a href="<?php echo base_url('products'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'products' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-box w-4"></i> Products
                        </a>
                        <a href="<?php echo base_url('accounts'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'accounts' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-chart-pie w-4"></i> Accounts
                        </a>
                    </div>
                </div>

                <!-- Transactions -->
                <div x-data="{ open: <?php echo in_array($this->uri->segment(1), ['sales', 'purchases', 'quotations', 'receipts', 'payments', 'journals']) ? 'true' : 'false'; ?> }">
                    <button @click="open = !open" class="sidebar-link w-full justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exchange-alt w-5"></i>
                            <span>Transactions</span>
                        </div>
                        <i class="fas fa-chevron-down transform transition-transform" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="<?php echo base_url('sales'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'sales' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-file-invoice w-4"></i> Sales/Invoices
                        </a>
                        <a href="<?php echo base_url('quotations'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'quotations' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-file-alt w-4"></i> Quotations
                        </a>
                        <a href="<?php echo base_url('purchases'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'purchases' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-shopping-cart w-4"></i> Purchases
                        </a>
                        <a href="<?php echo base_url('receipts'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'receipts' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-money-bill-wave w-4"></i> Receipts
                        </a>
                        <a href="<?php echo base_url('payments'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'payments' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-credit-card w-4"></i> Payments
                        </a>
                        <a href="<?php echo base_url('journals'); ?>" class="sidebar-link text-sm <?php echo $this->uri->segment(1) == 'journals' ? 'sidebar-link-active' : ''; ?>">
                            <i class="fas fa-book w-4"></i> Journal Entries
                        </a>
                    </div>
                </div>

                <!-- Reports -->
                <div x-data="{ open: <?php echo $this->uri->segment(1) == 'reports' ? 'true' : 'false'; ?> }">
                    <button @click="open = !open" class="sidebar-link w-full justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-bar w-5"></i>
                            <span>Reports</span>
                        </div>
                        <i class="fas fa-chevron-down transform transition-transform" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="<?php echo base_url('reports/books'); ?>" class="sidebar-link text-sm">
                            <i class="fas fa-book-open w-4"></i> Books of Accounts
                        </a>
                        <a href="<?php echo base_url('reports/financial'); ?>" class="sidebar-link text-sm">
                            <i class="fas fa-coins w-4"></i> Financial Reports
                        </a>
                        <a href="<?php echo base_url('reports/sales'); ?>" class="sidebar-link text-sm">
                            <i class="fas fa-chart-line w-4"></i> Sales Reports
                        </a>
                        <a href="<?php echo base_url('reports/customers'); ?>" class="sidebar-link text-sm">
                            <i class="fas fa-users w-4"></i> Customer Reports
                        </a>
                    </div>
                </div>

                <!-- Settings -->
                <a href="<?php echo base_url('settings'); ?>" class="sidebar-link <?php echo $this->uri->segment(1) == 'settings' ? 'sidebar-link-active' : ''; ?>">
                    <i class="fas fa-cog w-5"></i>
                    <span>Settings</span>
                </a>
            </nav>

            <!-- User Info (Bottom) -->
            <div class="p-4 border-t border-gray-800 mt-auto">
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-medium"><?php echo $this->session->userdata('username'); ?></div>
                        <div class="text-gray-400 text-xs"><?php echo $this->session->userdata('usertype'); ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Header -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <!-- Left: Menu Toggle & Breadcrumbs -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600 hover:text-gray-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Breadcrumbs -->
                    <?php if(isset($breadcrumbs) && !empty($breadcrumbs)): ?>
                    <nav class="hidden md:flex items-center gap-2 text-sm text-gray-600">
                        <a href="<?php echo base_url('dashboard'); ?>" class="hover:text-primary-600 transition-colors">
                            <i class="fas fa-home"></i>
                        </a>
                        <?php foreach($breadcrumbs as $crumb): ?>
                            <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                            <?php if(isset($crumb['url'])): ?>
                                <a href="<?php echo $crumb['url']; ?>" class="hover:text-primary-600 transition-colors">
                                    <?php echo $crumb['title']; ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-900"><?php echo $crumb['title']; ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>
                </div>

                <!-- Right: Search & User Menu -->
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <div class="relative hidden lg:block">
                        <input
                            type="search"
                            placeholder="Search..."
                            class="w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                        >
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>

                    <!-- Notifications -->
                    <button class="relative text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-danger-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                    </button>

                    <!-- User Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-gray-100 rounded-lg px-3 py-2 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center">
                                <?php echo strtoupper(substr($this->session->userdata('username'), 0, 1)); ?>
                            </div>
                            <i class="fas fa-chevron-down text-sm text-gray-600"></i>
                        </button>

                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition
                            class="dropdown"
                        >
                            <a href="<?php echo base_url('profile'); ?>" class="dropdown-item">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="<?php echo base_url('settings'); ?>" class="dropdown-item">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <hr class="my-1">
                            <a href="<?php echo base_url('logout'); ?>" class="dropdown-item text-danger-600">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                <?php
                // Display flash messages
                if($this->session->flashdata('success')):
                ?>
                    <div class="alert alert-success alert-auto-dismiss mb-6" data-aos="fade-down">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Success!</strong>
                            <p><?php echo $this->session->flashdata('success'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                if($this->session->flashdata('error')):
                ?>
                    <div class="alert alert-danger alert-auto-dismiss mb-6" data-aos="fade-down">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Error!</strong>
                            <p><?php echo $this->session->flashdata('error'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                // Load main content
                if(isset($main_content)) {
                    $this->load->view($main_content);
                } else {
                    echo '<div class="card"><div class="card-body">No content specified.</div></div>';
                }
                ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 px-6 py-4 text-sm text-gray-600">
                <div class="flex items-center justify-between">
                    <div>
                        &copy; <?php echo date('Y'); ?> NA-FIX ERP Solutions. All rights reserved.
                    </div>
                    <div class="flex items-center gap-4">
                        <span>Version 2.0.0</span>
                        <a href="#" class="hover:text-primary-600 transition-colors">Documentation</a>
                        <a href="#" class="hover:text-primary-600 transition-colors">Support</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div
        x-show="sidebarOpen && window.innerWidth < 1024"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <!-- Scripts -->
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- AOS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toastify -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js"></script>

    <!-- Main App JS -->
    <script src="<?php echo base_url('assets/js/app.js'); ?>"></script>

    <!-- Additional JS -->
    <?php if(isset($additional_js)): ?>
        <?php foreach($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
