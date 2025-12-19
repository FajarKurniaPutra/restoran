<?php
require_once '../config/koneksi.php';

class OrderModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function createOrderTransaction($pelanggan_id, $meja_id, $items, $tipe_order) {
        pg_query($this->db->conn, "BEGIN");

        try {
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
            $kode_pesanan = "ORD-" . time() . rand(100, 999);
            
            $final_meja_id = ($tipe_order === 'takeaway' || empty($meja_id)) ? null : $meja_id;

            $qHeader = "INSERT INTO pesanan (kode_pesanan, pelanggan_id, meja_id, status_order, user_id, tipe_order) 
                        VALUES ($1, $2, $3, 'pending', $4, $5) RETURNING id";
            
            $resHeader = pg_query_params($this->db->conn, $qHeader, 
                        [$kode_pesanan, $pelanggan_id, $final_meja_id, $userId, $tipe_order]);
            
            if (!$resHeader) throw new Exception("Gagal membuat header pesanan");
            $id_pesanan = pg_fetch_result($resHeader, 0, 0);

            foreach ($items as $item) {
                $qCek = "SELECT cek_menu_available($1) as available";
                $resCek = pg_query_params($this->db->conn, $qCek, [$item['menu_id']]);
                if (pg_fetch_result($resCek, 0, 0) === 'f') {
                    throw new Exception("Menu tidak tersedia!");
                }

                $harga = $this->getMenuPrice($item['menu_id']);
                $subtotal = $harga * $item['jumlah'];

                $qDetail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga_satuan, subtotal) 
                            VALUES ($1, $2, $3, $4, $5)";
                pg_query_params($this->db->conn, $qDetail, [$id_pesanan, $item['menu_id'], $item['jumlah'], $harga, $subtotal]);
            }

            pg_query_params($this->db->conn, "UPDATE pesanan SET total_tagihan = hitung_total_transaksi($1) WHERE id = $1", [$id_pesanan]);

            if ($tipe_order === 'dinein' && $final_meja_id) {
                pg_query_params($this->db->conn, "UPDATE meja SET status = 'terisi' WHERE id = $1", [$final_meja_id]);
            }

            pg_query($this->db->conn, "COMMIT");
            return true;
        } catch (Exception $e) {
            pg_query($this->db->conn, "ROLLBACK");
            return $e->getMessage();
        }
    }

    public function cancelOrder($id_pesanan) {
        pg_query($this->db->conn, "BEGIN");
        try {
            $q = pg_query_params($this->db->conn, "SELECT meja_id FROM pesanan WHERE id = $1", [$id_pesanan]);
            $meja_id = pg_fetch_result($q, 0, 0);

            pg_query_params($this->db->conn, "UPDATE pesanan SET status_order = 'dibatalkan' WHERE id = $1", [$id_pesanan]);

            if (!empty($meja_id)) {
                pg_query_params($this->db->conn, "UPDATE meja SET status = 'kosong' WHERE id = $1", [$meja_id]);
            }

            pg_query($this->db->conn, "COMMIT");
            return true;
        } catch (Exception $e) {
            pg_query($this->db->conn, "ROLLBACK");
            return false;
        }
    }

    public function bayarOrder($id_pesanan, $jumlah_bayar, $metode) {
        $qCek = pg_query_params($this->db->conn, "SELECT total_tagihan FROM pesanan WHERE id = $1", [$id_pesanan]);
        $tagihan = pg_fetch_result($qCek, 0, 0);

        if ($jumlah_bayar < $tagihan) {
            return ['status'=>'warning', 'message'=>"Uang Kurang!"];
        }

        $query = "CALL proses_pembayaran($1, $2, $3)";
        if (pg_query_params($this->db->conn, $query, [$id_pesanan, $jumlah_bayar, $metode])) {
            return ['status'=>'success', 'kembalian' => ($jumlah_bayar - $tagihan)];
        }
        return ['status'=>'error', 'message'=>"Gagal proses bayar"];
    }

    public function getMenuPrice($id) {
        $res = pg_query_params($this->db->conn, "SELECT harga FROM menu WHERE id=$1", [$id]);
        return pg_fetch_result($res, 0, 0);
    }

    public function getActiveMenus() {
        $res = pg_query($this->db->conn, "SELECT * FROM view_menu_aktif ORDER BY nama_menu");
        return pg_fetch_all($res) ?: [];
    }

    public function getPendingOrders() {
        $res = pg_query($this->db->conn, 
            "SELECT p.*, m.nomor_meja, pl.nama_pelanggan 
            FROM pesanan p 
            LEFT JOIN meja m ON p.meja_id=m.id 
            JOIN pelanggan pl ON p.pelanggan_id=pl.id 
            WHERE p.status_order IN ('pending','diproses') ORDER BY p.id DESC");
        return pg_fetch_all($res) ?: [];
    }

    public function getPelanggan() {
        $res = pg_query($this->db->conn, "SELECT * FROM pelanggan ORDER BY nama_pelanggan");
        return pg_fetch_all($res) ?: [];
    }

    public function getMejaKosong() {
        $res = pg_query($this->db->conn, "SELECT * FROM meja WHERE status='kosong' ORDER BY nomor_meja");
        return pg_fetch_all($res) ?: [];
    }

    public function getQueryPlan($keyword) {
        $query = "EXPLAIN ANALYZE SELECT * FROM menu WHERE nama_menu ILIKE $1";
        $result = pg_query_params($this->db->conn, $query, ["%$keyword%"]);
        $plan = "";
        while ($row = pg_fetch_assoc($result)) {
            $plan .= $row['QUERY PLAN'] . "\n";
        }
        return $plan;
    }
}
?>