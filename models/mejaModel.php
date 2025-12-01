<?php
require_once '../config/koneksi.php';

class MejaModel {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // --- CRUD MEJA ---
    
    // Update Create: Tambah status (default biasanya Kosong dari DB, tapi kita bisa handle disini)
    public function create($nomor_meja, $kapasitas, $status_meja = 'Kosong') {
        $query = "INSERT INTO meja (nomor_meja, kapasitas, status_meja) VALUES ($1, $2, $3)";
        return pg_query_params($this->db->conn, $query, array($nomor_meja, $kapasitas, $status_meja));
    }
    
    public function read() {
        $query = "SELECT * FROM meja ORDER BY nomor_meja ASC";
        $result = pg_query($this->db->conn, $query);
        $data = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
    
    // Update: Tambahkan parameter status_meja
    public function update($id_meja, $nomor_meja, $kapasitas, $status_meja) {
        $query = "UPDATE meja SET nomor_meja = $1, kapasitas = $2, status_meja = $3 WHERE id_meja = $4";
        return pg_query_params($this->db->conn, $query, array($nomor_meja, $kapasitas, $status_meja, $id_meja));
    }
    
    public function delete($id_meja) {
        $query = "DELETE FROM meja WHERE id_meja = $1";
        return pg_query_params($this->db->conn, $query, array($id_meja));
    }

    // --- STATUS MEJA (Logic Gabungan) ---
    public function getStatusMejaLengkap() {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        // Logic: 
        // 1. Ambil status manual dari kolom 'status_meja'
        // 2. Cek juga apakah ada reservasi aktif (status sistem)
        // Kita tampilkan prioritas: Jika Manual 'Penuh', maka Penuh. Jika Manual 'Kosong' tapi ada reservasi, maka 'Dipakai (Reservasi)'
        
        $query = "
            SELECT 
                m.id_meja,
                m.nomor_meja,
                m.kapasitas,
                m.status_meja as status_manual,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM reservasi r 
                        WHERE r.id_meja = m.id_meja 
                        AND r.tanggal_reservasi = $1
                        AND r.jam_mulai <= $2 
                        AND r.jam_selesai >= $2
                        AND r.status_reservasi != 'batal'
                    ) THEN 'Reserved'
                    ELSE 'Available'
                END as status_reservasi
            FROM meja m
            ORDER BY m.nomor_meja ASC
        ";
        
        $result = pg_query_params($this->db->conn, $query, array($currentDate, $currentTime));
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            // Logic Gabungan untuk Tampilan Final
            if ($row['status_manual'] == 'Penuh') {
                $row['status_final'] = 'Penuh (Manual)';
                $row['css_class'] = 'status-penuh-manual'; // Merah Gelap
            } elseif ($row['status_reservasi'] == 'Reserved') {
                $row['status_final'] = 'Dipakai (Rsrv)';
                $row['css_class'] = 'status-dipakai'; // Merah Terang
            } else {
                $row['status_final'] = 'Kosong';
                $row['css_class'] = 'status-tersedia'; // Hijau
            }
            $data[] = $row;
        }
        return $data;
    }

    // --- PELANGGAN ---
    public function findOrCreatePelanggan($nama, $no_hp) {
        $checkQuery = "SELECT id_pelanggan FROM pelanggan WHERE no_hp = $1";
        $checkResult = pg_query_params($this->db->conn, $checkQuery, array($no_hp));

        if (pg_num_rows($checkResult) > 0) {
            $row = pg_fetch_assoc($checkResult);
            return $row['id_pelanggan'];
        } else {
            $insertQuery = "INSERT INTO pelanggan (nama_pelanggan, no_hp) VALUES ($1, $2) RETURNING id_pelanggan";
            $insertResult = pg_query_params($this->db->conn, $insertQuery, array($nama, $no_hp));
            $row = pg_fetch_assoc($insertResult);
            return $row['id_pelanggan'];
        }
    }

    // --- RESERVASI ---
    public function reservasiBaru($nama_pelanggan, $no_hp, $id_meja, $tanggal, $jam_mulai, $durasi_jam) {
        $id_pelanggan = $this->findOrCreatePelanggan($nama_pelanggan, $no_hp);

        $startTime = strtotime($jam_mulai);
        $endTime = date("H:i", strtotime("+$durasi_jam hours", $startTime));

        $query = "INSERT INTO reservasi (id_pelanggan, namaPelanggan, id_meja, tanggal_reservasi, jam_mulai, jam_selesai, status_reservasi) 
                  VALUES ($1, $2, $3, $4, $5, $6, 'dipesan')";
        
        return pg_query_params($this->db->conn, $query, 
            array($id_pelanggan, $nama_pelanggan, $id_meja, $tanggal, $jam_mulai, $endTime));
    }

    // Fungsi History
    public function getHistoryReservasi($sortOrder = 'DESC', $filterDate = '') {
        $params = [];
        $query = "
            SELECT r.*, m.nomor_meja, p.nama_pelanggan, p.no_hp 
            FROM reservasi r
            JOIN meja m ON r.id_meja = m.id_meja
            LEFT JOIN pelanggan p ON r.id_pelanggan = p.id_pelanggan
        ";

        if (!empty($filterDate)) {
            $query .= " WHERE r.tanggal_reservasi = $1";
            $params[] = $filterDate;
        }

        $sort = ($sortOrder === 'ASC') ? 'ASC' : 'DESC';
        $query .= " ORDER BY r.tanggal_reservasi $sort, r.jam_mulai $sort";

        $result = pg_query_params($this->db->conn, $query, $params);
        
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
}
?>