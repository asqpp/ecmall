<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function index() {
        // If already logged in, redirect to dashboard
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
        }

        // Load login view
        $this->load->view('login');
    }

    public function authenticate() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        if (empty($username) || empty($password)) {
            $this->session->set_flashdata('error', 'Please enter username and password');
            redirect('login');
        }

        $user = $this->User_model->get_by_username($username);

        if ($user && password_verify($password, $user->password)) {
            if ($user->status != 'active') {
                $this->session->set_flashdata('error', 'Your account is not active');
                redirect('login');
            }

            // Set session data
            $session_data = array(
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role_id' => $user->role_id,
                'usertype' => $user->usertype,
                'logged_in' => TRUE
            );

            $this->session->set_userdata($session_data);

            // Update last login
            $this->User_model->update_last_login($user->id);

            // Log audit
            $this->log_audit($user->id, 'User logged in');

            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Invalid username or password');
            redirect('login');
        }
    }

    public function logout() {
        $user_id = $this->session->userdata('user_id');

        if ($user_id) {
            $this->log_audit($user_id, 'User logged out');
        }

        $this->session->sess_destroy();
        redirect('login');
    }

    private function log_audit($user_id, $action) {
        $data = array(
            'user_id' => $user_id,
            'action' => $action,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        );

        $this->db->insert('audit_logs', $data);
    }
}
