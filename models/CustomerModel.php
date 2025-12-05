<?php
require_once '../config/koneksi.php';

class CustomerModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllCustomers() {
        $query = "SELECT * FROM pelanggan WHERE deleted_at IS NULL ORDER BY created_at DESC";
        $result = pg_query($this->db->conn, $query);
        return pg_fetch_all($result) ?: [];
    }

    public function addCustomer($nama, $telepon) {
        $query = "INSERT INTO pelanggan (nama_pelanggan, no_telepon, created_at) VALUES ($1, $2, NOW())";
        return pg_query_params($this->db->conn, $query, [$nama, $telepon]);
    }

    public function updateCustomer($id, $nama, $telepon) {
        $query = "UPDATE pelanggan SET nama_pelanggan = $1, no_telepon = $2 WHERE id = $3";
        return pg_query_params($this->db->conn, $query, [$nama, $telepon, $id]);
    }

    public function deleteCustomer($id) {
        $query = "UPDATE pelanggan SET deleted_at = NOW() WHERE id = $1";
        return pg_query_params($this->db->conn, $query, [$id]);
    }
}
?>