<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_by_username($username) {
        return $this->db->where('username', $username)
                        ->get('users')
                        ->row();
    }

    public function get_by_id($id) {
        return $this->db->where('id', $id)
                        ->get('users')
                        ->row();
    }

    public function get_by_email($email) {
        return $this->db->where('email', $email)
                        ->get('users')
                        ->row();
    }

    public function get_all() {
        return $this->db->select('users.*, roles.name as role_name')
                        ->join('roles', 'roles.id = users.role_id', 'left')
                        ->where('users.status', 'active')
                        ->get('users')
                        ->result();
    }

    public function create($data) {
        // Generate user code
        $data['code'] = $this->generate_user_code();

        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->db->insert('users', $data);
    }

    public function update($id, $data) {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        return $this->db->where('id', $id)
                        ->update('users', $data);
    }

    public function delete($id) {
        // Soft delete - set status to inactive
        return $this->db->where('id', $id)
                        ->update('users', array('status' => 'inactive'));
    }

    public function update_last_login($id) {
        return $this->db->where('id', $id)
                        ->update('users', array('last_login' => date('Y-m-d H:i:s')));
    }

    private function generate_user_code() {
        $year = date('Y');
        $last_user = $this->db->select('code')
                              ->like('code', 'USR-' . $year, 'after')
                              ->order_by('id', 'DESC')
                              ->limit(1)
                              ->get('users')
                              ->row();

        if ($last_user) {
            $last_number = (int)substr($last_user->code, -4);
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }

        return 'USR-' . $year . '-' . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    public function check_permission($user_id, $module, $action = 'view') {
        $user = $this->get_by_id($user_id);

        if (!$user) {
            return FALSE;
        }

        // Admin has all permissions
        if ($user->usertype == 'ADMIN') {
            return TRUE;
        }

        // Check role permissions
        $role = $this->db->where('id', $user->role_id)->get('roles')->row();

        if ($role && $role->permissions) {
            $permissions = json_decode($role->permissions, TRUE);

            if (isset($permissions['all']) && $permissions['all']) {
                return TRUE;
            }

            if (isset($permissions[$module])) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
