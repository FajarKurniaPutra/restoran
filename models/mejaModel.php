<?php
require_once '../config/koneksi.php';

class MejaModel {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($nomor_meja, $kapasitas, $status_meja = 'Kosong') {
        $query = "INSERT INTO meja (nomor_meja, kapasitas, status_meja) VALUES ($1, $2, $3)";
        return pg_query_params($this->db->conn, $query, array($nomor_meja, $kapasitas, $status_meja));
    }
    
    public function update($id_meja, $nomor_meja, $kapasitas, $status_meja) {
        $query = "UPDATE meja SET nomor_meja = $1, kapasitas = $2, status_meja = $3 WHERE id_meja = $4";
        return pg_query_params($this->db->conn, $query, array($nomor_meja, $kapasitas, $status_meja, $id_meja));
    }

    public function delete($id_meja) {
        $queryReservasi = "DELETE FROM reservasi WHERE id_meja = $1";
        pg_query_params($this->db->conn, $queryReservasi, array($id_meja));

        $queryMeja = "DELETE FROM meja WHERE id_meja = $1";
        return pg_query_params($this->db->conn, $queryMeja, array($id_meja));
    }
    // --------------------------------------------------
    
    public function getStatusMejaLengkap($minKapasitas = 0) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $query = "SELECT m.id_meja, m.nomor_meja, m.kapasitas, m.status_meja as status_manual, r.jam_mulai as res_mulai, r.jam_selesai as res_selesai, r.tanggal_reservasi as res_tanggal, CASE WHEN r.id_reservasi IS NOT NULL THEN 'Reserved' ELSE 'Available' END as status_reservasi FROM meja m LEFT JOIN reservasi r ON m.id_meja = r.id_meja AND r.tanggal_reservasi = $1 AND r.jam_mulai <= $2 AND r.jam_selesai >= $2 AND r.status_reservasi != 'batal' WHERE m.kapasitas >= $3 ORDER BY m.nomor_meja ASC";
        $result = pg_query_params($this->db->conn, $query, array($currentDate, $currentTime, $minKapasitas));
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            if ($row['status_manual'] == 'Penuh') {
                $row['status_final'] = 'Penuh (Manual)'; $row['css_class'] = 'status-penuh-manual'; $row['info_waktu'] = '';
            } elseif ($row['status_reservasi'] == 'Reserved') {
                $row['status_final'] = 'Dipesan'; $row['css_class'] = 'status-dipakai bg-warning text-dark'; 
                $row['info_waktu'] = date('H:i', strtotime($row['res_mulai'])) . " - " . date('H:i', strtotime($row['res_selesai']));
            } else {
                $row['status_final'] = 'Kosong'; $row['css_class'] = 'status-tersedia'; $row['info_waktu'] = '';
            }
            $data[] = $row;
        }
        return $data;
    }
    public function findOrCreatePelanggan($nama, $no_hp) {
        $checkQuery = "SELECT id_pelanggan FROM pelanggan WHERE no_hp = $1";
        $checkResult = pg_query_params($this->db->conn, $checkQuery, array($no_hp));
        if (pg_num_rows($checkResult) > 0) { $row = pg_fetch_assoc($checkResult); return $row['id_pelanggan']; }
        else { $insertQuery = "INSERT INTO pelanggan (nama_pelanggan, no_hp, tanggal_daftar) VALUES ($1, $2, NOW()) RETURNING id_pelanggan"; $insertResult = pg_query_params($this->db->conn, $insertQuery, array($nama, $no_hp)); $row = pg_fetch_assoc($insertResult); return $row['id_pelanggan']; }
    }
    public function reservasiBaru($nama_pelanggan, $no_hp, $id_meja, $tanggal, $jam_mulai, $durasi_jam) {
        $id_pelanggan = $this->findOrCreatePelanggan($nama_pelanggan, $no_hp);
        $startTime = strtotime($jam_mulai);
        $endTimeStr = date("H:i", strtotime("+$durasi_jam hours", $startTime));
        $checkQuery = "SELECT id_reservasi FROM reservasi WHERE id_meja = $1 AND tanggal_reservasi = $2 AND status_reservasi != 'batal' AND (jam_mulai < $3 AND jam_selesai > $4)";
        $checkResult = pg_query_params($this->db->conn, $checkQuery, array($id_meja, $tanggal, $endTimeStr, $jam_mulai));
        if (pg_num_rows($checkResult) > 0) { return "BENTROK"; }
        $query = "INSERT INTO reservasi (id_pelanggan, id_meja, tanggal_reservasi, jam_mulai, jam_selesai, status_reservasi) VALUES ($1, $2, $3, $4, $5, 'dipesan')";
        $result = pg_query_params($this->db->conn, $query, array($id_pelanggan, $id_meja, $tanggal, $jam_mulai, $endTimeStr));
        return $result ? "SUKSES" : "GAGAL";
    }
    public function cancelReservasi($id_reservasi) {
        $query = "UPDATE reservasi SET status_reservasi = 'batal' WHERE id_reservasi = $1";
        return pg_query_params($this->db->conn, $query, array($id_reservasi));
    }
    public function getHistoryReservasi($sortOrder = 'DESC', $filterDate = '') {
        $params = [];
        $query = "SELECT r.*, m.nomor_meja, p.nama_pelanggan, p.no_hp FROM reservasi r JOIN meja m ON r.id_meja = m.id_meja LEFT JOIN pelanggan p ON r.id_pelanggan = p.id_pelanggan WHERE r.status_reservasi != 'batal' ";
        if (!empty($filterDate)) { $query .= " AND r.tanggal_reservasi = $1"; $params[] = $filterDate; }
        $sort = ($sortOrder === 'ASC') ? 'ASC' : 'DESC';
        $query .= " ORDER BY r.tanggal_reservasi $sort, r.jam_mulai $sort";
        $result = pg_query_params($this->db->conn, $query, $params);
        $data = []; while ($row = pg_fetch_assoc($result)) { $data[] = $row; } return $data;
    }
}
?>