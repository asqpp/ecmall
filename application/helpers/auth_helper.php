<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Check if user is logged in
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        $CI =& get_instance();
        return ($CI->session->userdata('logged_in') === TRUE);
    }
}

/**
 * Require login - redirect if not logged in
 */
if (!function_exists('require_login')) {
    function require_login() {
        $CI =& get_instance();
        if (!is_logged_in()) {
            $CI->session->set_flashdata('error', 'Please login to continue');
            redirect('login');
        }
    }
}

/**
 * Check if user has admin role
 */
if (!function_exists('is_admin')) {
    function is_admin() {
        $CI =& get_instance();
        return ($CI->session->userdata('usertype') === 'ADMIN');
    }
}

/**
 * Require admin - redirect if not admin
 */
if (!function_exists('require_admin')) {
    function require_admin() {
        $CI =& get_instance();
        require_login();

        if (!is_admin()) {
            $CI->session->set_flashdata('error', 'Access denied. Admin rights required.');
            redirect('dashboard');
        }
    }
}

/**
 * Get current user ID
 */
if (!function_exists('get_user_id')) {
    function get_user_id() {
        $CI =& get_instance();
        return $CI->session->userdata('user_id');
    }
}

/**
 * Get current username
 */
if (!function_exists('get_username')) {
    function get_username() {
        $CI =& get_instance();
        return $CI->session->userdata('username');
    }
}

/**
 * Get user full name
 */
if (!function_exists('get_user_fullname')) {
    function get_user_fullname() {
        $CI =& get_instance();
        $first = $CI->session->userdata('first_name');
        $last = $CI->session->userdata('last_name');
        return trim($first . ' ' . $last);
    }
}

/**
 * Check permission for module
 */
if (!function_exists('has_permission')) {
    function has_permission($module, $action = 'view') {
        $CI =& get_instance();

        if (!is_logged_in()) {
            return FALSE;
        }

        if (is_admin()) {
            return TRUE;
        }

        $CI->load->model('User_model');
        return $CI->User_model->check_permission(get_user_id(), $module, $action);
    }
}
