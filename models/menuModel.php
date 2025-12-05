<?php
require_once '../config/koneksi.php';

class MenuModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAll($limit = 10, $offset = 0, $search = "", $kategori_id = null) {
        $query = "SELECT m.*, k.nama_kategori 
                FROM menu m 
                JOIN kategori_menu k ON m.kategori_id = k.id 
                WHERE m.deleted_at IS NULL";
        
        $params = [];
        $counter = 1;

        if (!empty($search)) {
            $query .= " AND (m.nama_menu ILIKE $" . $counter++ . ")";
            $params[] = "%$search%";
        }

        if (!empty($kategori_id)) {
            $query .= " AND m.kategori_id = $" . $counter++;
            $params[] = $kategori_id;
        }

        $query .= " ORDER BY m.nama_menu ASC LIMIT $" . $counter++ . " OFFSET $" . $counter++;
        
        $params[] = $limit;
        $params[] = $offset;

        $result = pg_query_params($this->db->conn, $query, $params);
        return pg_fetch_all($result) ?: [];
    }

    public function countAll($search = "", $kategori_id = null) {
        $query = "SELECT COUNT(*) as total FROM menu WHERE deleted_at IS NULL";
        $params = [];
        $counter = 1;

        if (!empty($search)) {
            $query .= " AND nama_menu ILIKE $" . $counter++;
            $params[] = "%$search%";
        }

        if (!empty($kategori_id)) {
            $query .= " AND kategori_id = $" . $counter++;
            $params[] = $kategori_id;
        }

        $result = pg_query_params($this->db->conn, $query, $params);
        $row = pg_fetch_assoc($result);
        return $row['total'];
    }

    public function getCategories() {
        $res = pg_query($this->db->conn, "SELECT * FROM kategori_menu ORDER BY id");
        return pg_fetch_all($res) ?: [];
    }

    public function create($data) {
        $query = "INSERT INTO menu (nama_menu, deskripsi, harga, kategori_id, gambar, is_available) 
                VALUES ($1, $2, $3, $4, $5, $6)";
        return pg_query_params($this->db->conn, $query, [
            $data['nama'], $data['deskripsi'], $data['harga'], 
            $data['kategori_id'], $data['gambar'], $data['is_available']
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE menu SET nama_menu=$1, deskripsi=$2, harga=$3, kategori_id=$4, 
                gambar=$5, is_available=$6 WHERE id=$7";
        return pg_query_params($this->db->conn, $query, [
            $data['nama'], $data['deskripsi'], $data['harga'], 
            $data['kategori_id'], $data['gambar'], $data['is_available'], $id
        ]);
    }

    public function delete($id) {
        $query = "UPDATE menu SET deleted_at = NOW() WHERE id = $1";
        return pg_query_params($this->db->conn, $query, [$id]);
    }

    public function getById($id) {
        $query = "SELECT * FROM menu WHERE id = $1";
        $result = pg_query_params($this->db->conn, $query, [$id]);
        return pg_fetch_assoc($result);
    }
}
?>