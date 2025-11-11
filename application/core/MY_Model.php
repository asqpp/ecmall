<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Model Class
 * Provides common CRUD operations for all models
 */
class MY_Model extends CI_Model {

    protected $table;
    protected $primary_key = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all records
     *
     * @param array $filters Filter conditions
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array
     */
    public function get_all($filters = [], $limit = null, $offset = 0) {
        $this->db->select('*');
        $this->db->from($this->table);

        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (is_array($value)) {
                    $this->db->where_in($key, $value);
                } else {
                    $this->db->where($key, $value);
                }
            }
        }

        // Apply limit and offset
        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get record by ID
     *
     * @param mixed $id Primary key value
     * @return object|null
     */
    public function get_by_id($id) {
        $query = $this->db->get_where($this->table, [
            $this->primary_key => $id
        ]);

        return $query->row();
    }

    /**
     * Get record by field
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return object|null
     */
    public function get_by($field, $value) {
        $query = $this->db->get_where($this->table, [
            $field => $value
        ]);

        return $query->row();
    }

    /**
     * Get records where condition matches
     *
     * @param array $where Where conditions
     * @return array
     */
    public function get_where($where = []) {
        $query = $this->db->get_where($this->table, $where);
        return $query->result();
    }

    /**
     * Insert new record
     *
     * @param array $data Data to insert
     * @return int|bool Insert ID or false
     */
    public function insert($data) {
        // Filter fillable fields
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        // Add timestamps
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Update record
     *
     * @param mixed $id Primary key value
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data) {
        // Filter fillable fields
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        // Add updated timestamp
        if ($this->timestamps && !isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->update(
            $this->table,
            $data,
            [$this->primary_key => $id]
        );
    }

    /**
     * Delete record
     *
     * @param mixed $id Primary key value
     * @param bool $soft_delete Use soft delete
     * @return bool
     */
    public function delete($id, $soft_delete = false) {
        if ($soft_delete) {
            return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        }

        return $this->db->delete($this->table, [
            $this->primary_key => $id
        ]);
    }

    /**
     * Count records
     *
     * @param array $where Where conditions
     * @return int
     */
    public function count($where = []) {
        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db->count_all_results($this->table);
    }

    /**
     * Check if record exists
     *
     * @param mixed $id Primary key value
     * @return bool
     */
    public function exists($id) {
        return $this->count([$this->primary_key => $id]) > 0;
    }

    /**
     * Search records
     *
     * @param string $term Search term
     * @param array $fields Fields to search
     * @return array
     */
    public function search($term, $fields = []) {
        if (empty($fields)) {
            return [];
        }

        $this->db->select('*');
        $this->db->from($this->table);

        $this->db->group_start();
        foreach ($fields as $field) {
            $this->db->or_like($field, $term);
        }
        $this->db->group_end();

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get paginated results
     *
     * @param int $per_page Items per page
     * @param int $page Current page
     * @param array $where Where conditions
     * @return object
     */
    public function paginate($per_page = 25, $page = 1, $where = []) {
        $offset = ($page - 1) * $per_page;

        // Get total count
        if (!empty($where)) {
            $this->db->where($where);
        }
        $total = $this->db->count_all_results($this->table, FALSE);

        // Get results
        $this->db->limit($per_page, $offset);
        $query = $this->db->get();

        return (object) [
            'data' => $query->result(),
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => ceil($total / $per_page),
            'from' => $offset + 1,
            'to' => min($offset + $per_page, $total)
        ];
    }

    /**
     * Begin database transaction
     */
    public function begin_transaction() {
        $this->db->trans_start();
    }

    /**
     * Commit database transaction
     */
    public function commit() {
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Rollback database transaction
     */
    public function rollback() {
        $this->db->trans_rollback();
    }
}
