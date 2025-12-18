<?php
require_once '../config/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

class MejaModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($nomor, $kapasitas) {
        return pg_query_params($this->db->conn, 
            "INSERT INTO meja (nomor_meja, kapasitas, status) VALUES ($1, $2, 'kosong')", 
            [$nomor, $kapasitas]);
    }

    public function update($id, $nomor, $kapasitas, $status) {
        return pg_query_params($this->db->conn, 
            "UPDATE meja SET nomor_meja=$1, kapasitas=$2, status=$3 WHERE id=$4", 
            [$nomor, $kapasitas, $status, $id]);
    }

    public function delete($id) {
        return pg_query_params($this->db->conn, "DELETE FROM meja WHERE id=$1", [$id]);
    }

    public function getMejaWithStatus($tanggal, $jam) {
        $query = "SELECT m.*, 
                    (
                        SELECT p.nama_pelanggan 
                        FROM reservasi r 
                        LEFT JOIN pelanggan p ON r.pelanggan_id = p.id
                        WHERE r.meja_id = m.id 
                        AND r.status = 'aktif' 
                        AND r.tanggal_reservasi = $1
                        AND r.jam_reservasi >= ($2::time - INTERVAL '2 hours')
                        AND r.jam_reservasi <= ($2::time + INTERVAL '2 hours')
                        LIMIT 1
                    ) as reserved_by
                FROM meja m
                ORDER BY length(m.nomor_meja), m.nomor_meja";

        $result = pg_query_params($this->db->conn, $query, [$tanggal, $jam]);
        return pg_fetch_all($result) ?: [];
    }

    public function findOrCreatePelanggan($nama, $telp) {
        $cek = pg_query_params($this->db->conn, "SELECT id FROM pelanggan WHERE no_telepon = $1 LIMIT 1", [$telp]);
        if (pg_num_rows($cek) > 0) {
            return pg_fetch_result($cek, 0, 0);
        } else {
            $insert = pg_query_params($this->db->conn, "INSERT INTO pelanggan (nama_pelanggan, no_telepon) VALUES ($1, $2) RETURNING id", [$nama, $telp]);
            return pg_fetch_result($insert, 0, 0);
        }
    }

    public function getAllPelanggan() {
        $res = pg_query($this->db->conn, "SELECT id, nama_pelanggan, no_telepon FROM pelanggan ORDER BY nama_pelanggan ASC");
        return pg_fetch_all($res) ?: [];
    }

    public function addReservasi($data) {
        $cekFisik = pg_query_params($this->db->conn, "SELECT status FROM meja WHERE id = $1", [$data['meja_id']]);
        $statusSaatIni = pg_fetch_result($cekFisik, 0, 0);

        if ($statusSaatIni !== 'kosong') {
            return "MEJA_TIDAK_AVAILABLE"; 
        }

        $cekJadwal = pg_query_params($this->db->conn, 
            "SELECT id FROM reservasi 
            WHERE meja_id = $1 
            AND tanggal_reservasi = $2 
            AND status = 'aktif'
            AND jam_reservasi BETWEEN ($3::time - INTERVAL '1 hours 59 minutes') AND ($3::time + INTERVAL '1 hours 59 minutes')",
            [$data['meja_id'], $data['tanggal'], $data['jam']]
        );

        if (pg_num_rows($cekJadwal) > 0) return "BENTROK";

        $pelanggan_id = $this->findOrCreatePelanggan($data['nama'], $data['telp']);
        $query = "INSERT INTO reservasi (meja_id, pelanggan_id, tanggal_reservasi, jam_reservasi, status) VALUES ($1, $2, $3, $4, 'aktif')";
        return pg_query_params($this->db->conn, $query, [$data['meja_id'], $pelanggan_id, $data['tanggal'], $data['jam']]);
    }

    public function getUpcomingReservations() {
        $query = "SELECT r.*, m.nomor_meja, p.nama_pelanggan, p.no_telepon 
                FROM reservasi r 
                LEFT JOIN meja m ON r.meja_id = m.id 
                LEFT JOIN pelanggan p ON r.pelanggan_id = p.id
                WHERE r.status = 'aktif' 
                ORDER BY r.tanggal_reservasi ASC, r.jam_reservasi ASC 
                LIMIT 20";
        return pg_fetch_all(pg_query($this->db->conn, $query)) ?: [];
    }

    public function getHistoryReservasi() {
        $query = "SELECT r.*, m.nomor_meja, p.nama_pelanggan, p.no_telepon 
                FROM reservasi r 
                LEFT JOIN meja m ON r.meja_id = m.id 
                LEFT JOIN pelanggan p ON r.pelanggan_id = p.id
                ORDER BY r.tanggal_reservasi DESC, r.jam_reservasi DESC 
                LIMIT 50";
        return pg_fetch_all(pg_query($this->db->conn, $query)) ?: [];
    }

    public function cancelReservasi($id) {
        pg_query($this->db->conn, "BEGIN");
        try {
            $res = pg_query_params($this->db->conn, "SELECT meja_id, pelanggan_id FROM reservasi WHERE id=$1", [$id]);
            $data = pg_fetch_assoc($res);
            
            if ($data) {
                pg_query_params($this->db->conn, "UPDATE reservasi SET status='batal' WHERE id=$1", [$id]);
                $qOrder = "UPDATE pesanan SET status_order = 'dibatalkan' 
                            WHERE meja_id = $1 AND pelanggan_id = $2 AND status_order = 'pending'";
                pg_query_params($this->db->conn, $qOrder, [$data['meja_id'], $data['pelanggan_id']]);
                pg_query_params($this->db->conn, "UPDATE meja SET status = 'kosong' WHERE id = $1", [$data['meja_id']]);
            }

            pg_query($this->db->conn, "COMMIT");
            return true;
        } catch (Exception $e) {
            pg_query($this->db->conn, "ROLLBACK");
            return false;
        }
    }

    public function completeReservasi($id) {
        pg_query($this->db->conn, "BEGIN");
        try {
            $res = pg_query_params($this->db->conn, "SELECT meja_id FROM reservasi WHERE id=$1", [$id]);
            $data = pg_fetch_assoc($res);
            
            if ($data) {
                pg_query_params($this->db->conn, "UPDATE reservasi SET status='selesai' WHERE id=$1", [$id]);
                pg_query_params($this->db->conn, "UPDATE meja SET status = 'kosong' WHERE id = $1", [$data['meja_id']]);
            }

            pg_query($this->db->conn, "COMMIT");
            return true;
        } catch (Exception $e) {
            pg_query($this->db->conn, "ROLLBACK");
            return false;
        }
    }
}
?>