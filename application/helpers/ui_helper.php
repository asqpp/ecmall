<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * UI Components Helper
 * Reusable UI component functions for consistent design
 */

// =============================================================================
// CARD COMPONENTS
// =============================================================================

if (!function_exists('card_start')) {
    /**
     * Start a card component
     *
     * @param string $title Card title
     * @param bool $hover Add hover effect
     * @param string $extra_class Additional CSS classes
     */
    function card_start($title = '', $hover = false, $extra_class = '') {
        $hover_class = $hover ? 'card-hover' : '';
        echo '<div class="card ' . $hover_class . ' ' . $extra_class . '">';

        if ($title) {
            echo '<div class="card-header">';
            echo '<h3 class="card-title">' . $title . '</h3>';
            echo '</div>';
        }

        echo '<div class="card-body">';
    }
}

if (!function_exists('card_end')) {
    /**
     * End a card component
     */
    function card_end() {
        echo '</div></div>';
    }
}

// =============================================================================
// STAT CARD COMPONENT
// =============================================================================

if (!function_exists('render_stat_card')) {
    /**
     * Render a statistics card
     *
     * @param string $label Card label
     * @param string $value Main value
     * @param string $change Change indicator (optional)
     * @param string $icon Icon class
     * @param string $gradient Gradient class
     * @param int $aos_delay AOS animation delay
     */
    function render_stat_card($label, $value, $change = '', $icon = 'fas fa-chart-line', $gradient = 'bg-gradient-primary', $aos_delay = 0) {
        ?>
        <div class="stat-card <?php echo $gradient; ?>" data-aos="fade-up" data-aos-delay="<?php echo $aos_delay; ?>">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-label"><?php echo $label; ?></p>
                    <h3 class="stat-value"><?php echo $value; ?></h3>
                    <?php if ($change): ?>
                        <p class="text-sm mt-2 opacity-90"><?php echo $change; ?></p>
                    <?php endif; ?>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="<?php echo $icon; ?> text-2xl"></i>
                </div>
            </div>
        </div>
        <?php
    }
}

// =============================================================================
// BUTTON COMPONENTS
// =============================================================================

if (!function_exists('render_button')) {
    /**
     * Render a button
     *
     * @param string $text Button text
     * @param string $type Button type (primary, secondary, success, danger, warning, outline, ghost)
     * @param string $icon Icon class (optional)
     * @param string $size Size (sm, regular, lg)
     * @param array $attributes Additional HTML attributes
     */
    function render_button($text, $type = 'primary', $icon = '', $size = '', $attributes = []) {
        $size_class = $size ? 'btn-' . $size : '';
        $attr_string = '';

        foreach ($attributes as $key => $value) {
            $attr_string .= ' ' . $key . '="' . $value . '"';
        }

        echo '<button class="btn btn-' . $type . ' ' . $size_class . '"' . $attr_string . '>';
        if ($icon) {
            echo '<i class="' . $icon . '"></i>';
        }
        echo $text;
        echo '</button>';
    }
}

// =============================================================================
// BADGE COMPONENTS
// =============================================================================

if (!function_exists('badge')) {
    /**
     * Render a badge
     *
     * @param string $text Badge text
     * @param string $type Badge type (primary, success, warning, danger, info, gray)
     */
    function badge($text, $type = 'primary') {
        return '<span class="badge badge-' . $type . '">' . $text . '</span>';
    }
}

if (!function_exists('status_badge')) {
    /**
     * Render a status badge with automatic coloring
     *
     * @param string $status Status text
     */
    function status_badge($status) {
        $status_lower = strtolower($status);

        $type = 'gray';
        if (in_array($status_lower, ['active', 'paid', 'approved', 'completed', 'success'])) {
            $type = 'success';
        } elseif (in_array($status_lower, ['pending', 'review', 'partial'])) {
            $type = 'warning';
        } elseif (in_array($status_lower, ['inactive', 'unpaid', 'rejected', 'cancelled', 'overdue'])) {
            $type = 'danger';
        } elseif (in_array($status_lower, ['draft', 'new'])) {
            $type = 'info';
        }

        return badge(ucfirst($status), $type);
    }
}

// =============================================================================
// ALERT COMPONENTS
// =============================================================================

if (!function_exists('alert')) {
    /**
     * Render an alert message
     *
     * @param string $message Alert message
     * @param string $type Alert type (success, warning, danger, info)
     * @param bool $dismissible Make alert dismissible
     */
    function alert($message, $type = 'info', $dismissible = true) {
        $icons = [
            'success' => 'fa-check-circle',
            'warning' => 'fa-exclamation-triangle',
            'danger' => 'fa-times-circle',
            'info' => 'fa-info-circle'
        ];

        echo '<div class="alert alert-' . $type . '" role="alert">';
        echo '<i class="fas ' . $icons[$type] . '"></i>';
        echo '<div class="flex-1">' . $message . '</div>';

        if ($dismissible) {
            echo '<button type="button" class="text-current opacity-50 hover:opacity-100" onclick="this.parentElement.remove()">';
            echo '<i class="fas fa-times"></i>';
            echo '</button>';
        }

        echo '</div>';
    }
}

// =============================================================================
// TABLE COMPONENTS
// =============================================================================

if (!function_exists('table_start')) {
    /**
     * Start a table
     *
     * @param array $headers Table headers
     * @param bool $striped Use striped rows
     * @param string $extra_class Additional CSS classes
     */
    function table_start($headers, $striped = false, $extra_class = '') {
        $striped_class = $striped ? 'table-striped' : '';

        echo '<div class="overflow-x-auto">';
        echo '<table class="table ' . $striped_class . ' ' . $extra_class . '">';
        echo '<thead><tr>';

        foreach ($headers as $header) {
            echo '<th>' . $header . '</th>';
        }

        echo '</tr></thead>';
        echo '<tbody>';
    }
}

if (!function_exists('table_end')) {
    /**
     * End a table
     */
    function table_end() {
        echo '</tbody></table></div>';
    }
}

// =============================================================================
// FORM COMPONENTS
// =============================================================================

if (!function_exists('form_input_group')) {
    /**
     * Render a form input group
     *
     * @param string $name Input name
     * @param string $label Input label
     * @param string $type Input type
     * @param bool $required Is required
     * @param mixed $value Input value
     * @param string $placeholder Placeholder text
     * @param string $help_text Help text
     * @param array $attributes Additional attributes
     */
    function form_input_group($name, $label, $type = 'text', $required = false, $value = '', $placeholder = '', $help_text = '', $attributes = []) {
        $required_class = $required ? 'form-label-required' : '';
        $attr_string = '';

        foreach ($attributes as $key => $val) {
            $attr_string .= ' ' . $key . '="' . $val . '"';
        }

        echo '<div class="form-group">';
        echo '<label for="' . $name . '" class="form-label ' . $required_class . '">' . $label . '</label>';
        echo '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" class="form-input" ';
        echo 'value="' . htmlspecialchars($value) . '" ';
        echo 'placeholder="' . $placeholder . '" ';
        echo ($required ? 'required ' : '');
        echo $attr_string . '>';

        if ($help_text) {
            echo '<p class="form-help">' . $help_text . '</p>';
        }

        echo '</div>';
    }
}

if (!function_exists('form_select_group')) {
    /**
     * Render a form select group
     *
     * @param string $name Select name
     * @param string $label Select label
     * @param array $options Select options
     * @param bool $required Is required
     * @param mixed $selected Selected value
     * @param string $help_text Help text
     * @param array $attributes Additional attributes
     */
    function form_select_group($name, $label, $options, $required = false, $selected = '', $help_text = '', $attributes = []) {
        $required_class = $required ? 'form-label-required' : '';
        $attr_string = '';

        foreach ($attributes as $key => $val) {
            $attr_string .= ' ' . $key . '="' . $val . '"';
        }

        echo '<div class="form-group">';
        echo '<label for="' . $name . '" class="form-label ' . $required_class . '">' . $label . '</label>';
        echo '<select id="' . $name . '" name="' . $name . '" class="form-select" ';
        echo ($required ? 'required ' : '');
        echo $attr_string . '>';

        echo '<option value="">Select...</option>';

        foreach ($options as $value => $text) {
            $selected_attr = ($value == $selected) ? 'selected' : '';
            echo '<option value="' . $value . '" ' . $selected_attr . '>' . $text . '</option>';
        }

        echo '</select>';

        if ($help_text) {
            echo '<p class="form-help">' . $help_text . '</p>';
        }

        echo '</div>';
    }
}

if (!function_exists('form_textarea_group')) {
    /**
     * Render a form textarea group
     *
     * @param string $name Textarea name
     * @param string $label Textarea label
     * @param bool $required Is required
     * @param mixed $value Textarea value
     * @param string $placeholder Placeholder text
     * @param string $help_text Help text
     * @param int $rows Number of rows
     */
    function form_textarea_group($name, $label, $required = false, $value = '', $placeholder = '', $help_text = '', $rows = 3) {
        $required_class = $required ? 'form-label-required' : '';

        echo '<div class="form-group">';
        echo '<label for="' . $name . '" class="form-label ' . $required_class . '">' . $label . '</label>';
        echo '<textarea id="' . $name . '" name="' . $name . '" class="form-textarea" rows="' . $rows . '" ';
        echo 'placeholder="' . $placeholder . '" ';
        echo ($required ? 'required' : '') . '>';
        echo htmlspecialchars($value);
        echo '</textarea>';

        if ($help_text) {
            echo '<p class="form-help">' . $help_text . '</p>';
        }

        echo '</div>';
    }
}

// =============================================================================
// TAB COMPONENTS
// =============================================================================

if (!function_exists('tabs_start')) {
    /**
     * Start tabs component
     *
     * @param array $tabs Tab titles
     */
    function tabs_start($tabs) {
        echo '<div x-data="tabs()">';
        echo '<div class="tabs flex gap-2 mb-6">';

        foreach ($tabs as $index => $tab) {
            echo '<button @click="setTab(' . $index . ')" ';
            echo 'class="tab-button" ';
            echo ':class="activeTab === ' . $index . ' && \'tab-button-active\'">';
            echo $tab;
            echo '</button>';
        }

        echo '</div>';
    }
}

if (!function_exists('tab_content_start')) {
    /**
     * Start tab content
     *
     * @param int $index Tab index
     */
    function tab_content_start($index) {
        echo '<div x-show="activeTab === ' . $index . '" x-transition>';
    }
}

if (!function_exists('tab_content_end')) {
    /**
     * End tab content
     */
    function tab_content_end() {
        echo '</div>';
    }
}

if (!function_exists('tabs_end')) {
    /**
     * End tabs component
     */
    function tabs_end() {
        echo '</div>';
    }
}

// =============================================================================
// BREADCRUMB COMPONENT
// =============================================================================

if (!function_exists('render_breadcrumb')) {
    /**
     * Render breadcrumb navigation
     *
     * @param array $items Breadcrumb items
     */
    function render_breadcrumb($items) {
        $ci =& get_instance();

        echo '<nav class="breadcrumb">';
        echo '<a href="' . base_url('dashboard') . '" class="breadcrumb-link">';
        echo '<i class="fas fa-home"></i> Home';
        echo '</a>';

        foreach ($items as $item) {
            echo '<i class="fas fa-chevron-right text-xs breadcrumb-separator"></i>';

            if (isset($item['url'])) {
                echo '<a href="' . $item['url'] . '" class="breadcrumb-link">' . $item['title'] . '</a>';
            } else {
                echo '<span class="text-gray-900">' . $item['title'] . '</span>';
            }
        }

        echo '</nav>';
    }
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

if (!function_exists('format_currency')) {
    /**
     * Format currency
     *
     * @param float $amount Amount
     * @param string $currency Currency code
     */
    function format_currency($amount, $currency = 'AED') {
        return $currency . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date
     *
     * @param string $date Date string
     * @param string $format Date format
     */
    function format_date($date, $format = 'd/m/Y') {
        if (!$date || $date == '0000-00-00') return '-';
        return date($format, strtotime($date));
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime
     *
     * @param string $datetime Datetime string
     * @param string $format Datetime format
     */
    function format_datetime($datetime, $format = 'd/m/Y H:i') {
        if (!$datetime || $datetime == '0000-00-00 00:00:00') return '-';
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('percentage')) {
    /**
     * Calculate and format percentage
     *
     * @param float $value Value
     * @param float $total Total
     * @param int $decimals Decimal places
     */
    function percentage($value, $total, $decimals = 2) {
        if ($total == 0) return '0%';
        return number_format(($value / $total) * 100, $decimals) . '%';
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncate text
     *
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @param string $suffix Suffix
     */
    function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . $suffix;
    }
}

// =============================================================================
// PAGINATION COMPONENT
// =============================================================================

if (!function_exists('render_pagination')) {
    /**
     * Render pagination
     *
     * @param int $total_items Total items
     * @param int $per_page Items per page
     * @param int $current_page Current page
     * @param string $base_url Base URL
     */
    function render_pagination($total_items, $per_page, $current_page, $base_url) {
        $total_pages = ceil($total_items / $per_page);

        if ($total_pages <= 1) return;

        echo '<div class="flex items-center justify-between mt-6">';
        echo '<div class="text-sm text-gray-600">';
        $start = ($current_page - 1) * $per_page + 1;
        $end = min($current_page * $per_page, $total_items);
        echo 'Showing ' . $start . ' to ' . $end . ' of ' . $total_items . ' results';
        echo '</div>';

        echo '<div class="flex items-center gap-2">';

        // Previous
        if ($current_page > 1) {
            echo '<a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="btn btn-outline btn-sm">';
            echo '<i class="fas fa-chevron-left"></i>';
            echo '</a>';
        }

        // Pages
        for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
            if ($i == $current_page) {
                echo '<span class="btn btn-primary btn-sm">' . $i . '</span>';
            } else {
                echo '<a href="' . $base_url . '?page=' . $i . '" class="btn btn-outline btn-sm">' . $i . '</a>';
            }
        }

        // Next
        if ($current_page < $total_pages) {
            echo '<a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="btn btn-outline btn-sm">';
            echo '<i class="fas fa-chevron-right"></i>';
            echo '</a>';
        }

        echo '</div>';
        echo '</div>';
    }
}
