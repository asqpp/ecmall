/**
 * Insurance ERP - Main JavaScript Application
 * @version 2.0.0
 */

// =============================================================================
// ALPINE.JS COMPONENTS
// =============================================================================

document.addEventListener('alpine:init', () => {

  // Global App Store
  Alpine.store('app', {
    sidebarOpen: window.innerWidth >= 1024,
    darkMode: localStorage.getItem('darkMode') === 'true',

    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
    },

    toggleDarkMode() {
      this.darkMode = !this.darkMode;
      localStorage.setItem('darkMode', this.darkMode);
      document.documentElement.classList.toggle('dark', this.darkMode);
    }
  });

  // Modal Component
  Alpine.data('modal', () => ({
    show: false,

    open() {
      this.show = true;
      document.body.style.overflow = 'hidden';
    },

    close() {
      this.show = false;
      document.body.style.overflow = '';
    }
  }));

  // Dropdown Component
  Alpine.data('dropdown', () => ({
    open: false,

    toggle() {
      this.open = !this.open;
    },

    close() {
      this.open = false;
    }
  }));

  // Tabs Component
  Alpine.data('tabs', (defaultTab = 0) => ({
    activeTab: defaultTab,

    setTab(index) {
      this.activeTab = index;
    }
  }));

  // Form Validation
  Alpine.data('formValidation', () => ({
    errors: {},

    validate(field, rules) {
      // Simple validation logic
      this.errors[field] = [];

      if (rules.required && !this.$refs[field].value) {
        this.errors[field].push('This field is required');
      }

      if (rules.email && !this.isValidEmail(this.$refs[field].value)) {
        this.errors[field].push('Please enter a valid email');
      }

      if (rules.min && this.$refs[field].value.length < rules.min) {
        this.errors[field].push(`Minimum ${rules.min} characters required`);
      }

      return this.errors[field].length === 0;
    },

    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    hasError(field) {
      return this.errors[field] && this.errors[field].length > 0;
    },

    getError(field) {
      return this.errors[field] ? this.errors[field][0] : '';
    }
  }));

  // Data Table Component
  Alpine.data('dataTable', (data = []) => ({
    items: data,
    filteredItems: data,
    searchTerm: '',
    sortColumn: '',
    sortDirection: 'asc',
    currentPage: 1,
    itemsPerPage: 25,

    init() {
      this.filter();
    },

    filter() {
      this.filteredItems = this.items.filter(item => {
        if (!this.searchTerm) return true;

        return Object.values(item).some(value =>
          String(value).toLowerCase().includes(this.searchTerm.toLowerCase())
        );
      });

      this.sort();
    },

    sort(column = null) {
      if (column) {
        if (this.sortColumn === column) {
          this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortColumn = column;
          this.sortDirection = 'asc';
        }
      }

      if (this.sortColumn) {
        this.filteredItems.sort((a, b) => {
          let aVal = a[this.sortColumn];
          let bVal = b[this.sortColumn];

          if (this.sortDirection === 'asc') {
            return aVal > bVal ? 1 : -1;
          } else {
            return aVal < bVal ? 1 : -1;
          }
        });
      }
    },

    get paginatedItems() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredItems.slice(start, end);
    },

    get totalPages() {
      return Math.ceil(this.filteredItems.length / this.itemsPerPage);
    },

    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    }
  }));
});

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

const Utils = {

  /**
   * Format currency with symbol
   */
  formatCurrency(amount, currency = 'AED') {
    return new Intl.NumberFormat('en-AE', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 2
    }).format(amount);
  },

  /**
   * Format date
   */
  formatDate(date, format = 'DD/MM/YYYY') {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return format
      .replace('DD', day)
      .replace('MM', month)
      .replace('YYYY', year);
  },

  /**
   * Format number with thousand separator
   */
  formatNumber(number, decimals = 2) {
    return Number(number).toLocaleString('en-AE', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  },

  /**
   * Show toast notification
   */
  showToast(message, type = 'success') {
    if (typeof Toastify !== 'undefined') {
      Toastify({
        text: message,
        duration: 3000,
        gravity: 'top',
        position: 'right',
        stopOnFocus: true,
        className: `toast-${type}`,
        style: {
          background: type === 'success' ? '#22c55e' :
                     type === 'error' ? '#ef4444' :
                     type === 'warning' ? '#f59e0b' : '#3b82f6'
        }
      }).showToast();
    } else {
      alert(message);
    }
  },

  /**
   * Show confirmation dialog
   */
  async confirm(title, text) {
    if (typeof Swal !== 'undefined') {
      const result = await Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
      });

      return result.isConfirmed;
    } else {
      return confirm(text);
    }
  },

  /**
   * Show alert dialog
   */
  alert(title, text, icon = 'info') {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: title,
        text: text,
        icon: icon,
        confirmButtonColor: '#3b82f6'
      });
    } else {
      alert(text);
    }
  },

  /**
   * Copy text to clipboard
   */
  async copyToClipboard(text) {
    try {
      await navigator.clipboard.writeText(text);
      this.showToast('Copied to clipboard', 'success');
    } catch (err) {
      this.showToast('Failed to copy', 'error');
    }
  },

  /**
   * Download data as CSV
   */
  downloadCSV(data, filename = 'export.csv') {
    const csv = this.convertToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
  },

  /**
   * Convert array to CSV
   */
  convertToCSV(data) {
    if (!data || !data.length) return '';

    const headers = Object.keys(data[0]);
    const rows = data.map(row =>
      headers.map(header => `"${row[header] || ''}"`).join(',')
    );

    return [headers.join(','), ...rows].join('\n');
  },

  /**
   * Debounce function
   */
  debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  /**
   * AJAX helper
   */
  async ajax(url, options = {}) {
    try {
      const response = await fetch(url, {
        method: options.method || 'GET',
        headers: {
          'Content-Type': 'application/json',
          ...options.headers
        },
        body: options.data ? JSON.stringify(options.data) : null
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error('AJAX Error:', error);
      this.showToast('An error occurred', 'error');
      throw error;
    }
  }
};

// Make Utils available globally
window.Utils = Utils;

// =============================================================================
// CHART.JS HELPERS
// =============================================================================

const ChartHelpers = {

  defaultOptions: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'bottom'
      },
      tooltip: {
        mode: 'index',
        intersect: false
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return Utils.formatNumber(value, 0);
          }
        }
      }
    }
  },

  /**
   * Create line chart
   */
  createLineChart(ctx, labels, datasets, options = {}) {
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: datasets
      },
      options: {
        ...this.defaultOptions,
        ...options
      }
    });
  },

  /**
   * Create bar chart
   */
  createBarChart(ctx, labels, datasets, options = {}) {
    return new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: datasets
      },
      options: {
        ...this.defaultOptions,
        ...options
      }
    });
  },

  /**
   * Create pie chart
   */
  createPieChart(ctx, labels, data, options = {}) {
    return new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: data,
          backgroundColor: [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
            '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'
          ]
        }]
      },
      options: {
        ...this.defaultOptions,
        scales: undefined,
        ...options
      }
    });
  },

  /**
   * Create donut chart
   */
  createDonutChart(ctx, labels, data, options = {}) {
    return this.createPieChart(ctx, labels, data, {
      cutout: '60%',
      ...options
    });
  }
};

// Make ChartHelpers available globally
window.ChartHelpers = ChartHelpers;

// =============================================================================
// INITIALIZATION
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {

  // Initialize AOS (Animate On Scroll)
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 800,
      easing: 'ease-out',
      once: true,
      offset: 100
    });
  }

  // Initialize tooltips (if using Bootstrap or similar)
  const tooltips = document.querySelectorAll('[data-tooltip]');
  tooltips.forEach(el => {
    el.title = el.getAttribute('data-tooltip');
  });

  // Auto-dismiss alerts
  const alerts = document.querySelectorAll('.alert-auto-dismiss');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });

  // Print button handler
  document.querySelectorAll('[data-print]').forEach(btn => {
    btn.addEventListener('click', () => window.print());
  });

  // Back button handler
  document.querySelectorAll('[data-back]').forEach(btn => {
    btn.addEventListener('click', () => window.history.back());
  });

  console.log('Insurance ERP - Initialized successfully');
});

// =============================================================================
// FORM HELPERS
// =============================================================================

const FormHelpers = {

  /**
   * Serialize form data
   */
  serializeForm(form) {
    const formData = new FormData(form);
    const data = {};

    for (let [key, value] of formData.entries()) {
      data[key] = value;
    }

    return data;
  },

  /**
   * Validate form
   */
  validateForm(form) {
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
      if (!input.value.trim()) {
        isValid = false;
        input.classList.add('border-danger-500');
      } else {
        input.classList.remove('border-danger-500');
      }
    });

    return isValid;
  },

  /**
   * Reset form
   */
  resetForm(form) {
    form.reset();
    form.querySelectorAll('.border-danger-500').forEach(el => {
      el.classList.remove('border-danger-500');
    });
  }
};

window.FormHelpers = FormHelpers;
