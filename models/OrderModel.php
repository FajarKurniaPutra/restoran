<?php
require_once '../config/koneksi.php';

class OrderModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function createOrderTransaction($pelanggan_id, $meja_id, $items) {
        pg_query($this->db->conn, "BEGIN");

        try {
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

            $kode_pesanan = "ORD-" . time() . rand(100, 999);

            $qHeader = "INSERT INTO pesanan (kode_pesanan, pelanggan_id, meja_id, status_order, user_id) 
                        VALUES ($1, $2, $3, 'pending', $4) RETURNING id";
            
            $resHeader = pg_query_params($this->db->conn, $qHeader, 
                        [$kode_pesanan, $pelanggan_id, $meja_id, $userId]);
            
            if (!$resHeader) throw new Exception("Gagal membuat header pesanan");
            $id_pesanan = pg_fetch_result($resHeader, 0, 0);

            foreach ($items as $item) {
                $qCek = "SELECT cek_menu_available($1) as available";
                $resCek = pg_query_params($this->db->conn, $qCek, [$item['menu_id']]);
                if (pg_fetch_result($resCek, 0, 0) === 'f') {
                    throw new Exception("Menu ID " . $item['menu_id'] . " tidak tersedia!");
                }

                $harga = $this->getMenuPrice($item['menu_id']);
                $subtotal = $harga * $item['jumlah'];

                $qDetail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga_satuan, subtotal) 
                            VALUES ($1, $2, $3, $4, $5)";
                $resDetail = pg_query_params($this->db->conn, $qDetail, 
                    [$id_pesanan, $item['menu_id'], $item['jumlah'], $harga, $subtotal]);
                
                if (!$resDetail) throw new Exception("Gagal input item.");
            }

            $qUpdate = "UPDATE pesanan SET total_tagihan = hitung_total_transaksi($1) WHERE id = $1";
            pg_query_params($this->db->conn, $qUpdate, [$id_pesanan]);

            $qMeja = "UPDATE meja SET status = 'terisi' WHERE id = $1";
            pg_query_params($this->db->conn, $qMeja, [$meja_id]);

            pg_query($this->db->conn, "COMMIT");
            return true;

        } catch (Exception $e) {
            pg_query($this->db->conn, "ROLLBACK");
            return $e->getMessage();
        }
    }

    public function bayarOrder($id_pesanan, $jumlah_bayar, $metode) {
        $qCek = pg_query_params($this->db->conn, "SELECT total_tagihan FROM pesanan WHERE id = $1", [$id_pesanan]);
        if(pg_num_rows($qCek) == 0) return ['status'=>'error', 'message'=>"Pesanan tidak ditemukan"];

        $tagihan = pg_fetch_result($qCek, 0, 0);
        if ($jumlah_bayar < $tagihan) {
            return ['status'=>'warning', 'message'=>"Uang Kurang! Total: " . number_format($tagihan)];
        }

        $query = "CALL proses_pembayaran($1, $2, $3)";
        if (pg_query_params($this->db->conn, $query, [$id_pesanan, $jumlah_bayar, $metode])) {
            return ['status'=>'success', 'message'=>"Lunas!", 'kembalian' => ($jumlah_bayar - $tagihan)];
        }
        return ['status'=>'error', 'message'=>"DB Error"];
    }

    public function getMenuPrice($id) {
        $res = pg_query_params($this->db->conn, "SELECT harga FROM menu WHERE id=$1", [$id]);
        return pg_fetch_result($res, 0, 0);
    }
    public function getActiveMenus() {
        return pg_fetch_all(pg_query($this->db->conn, "SELECT * FROM view_menu_aktif ORDER BY nama_menu")) ?: [];
    }
    public function getPendingOrders() {
        return pg_fetch_all(pg_query($this->db->conn, 
            "SELECT p.*, m.nomor_meja, pl.nama_pelanggan 
            FROM pesanan p 
            JOIN meja m ON p.meja_id=m.id 
            JOIN pelanggan pl ON p.pelanggan_id=pl.id 
            WHERE p.status_order IN ('pending','diproses') ORDER BY p.id DESC")) ?: [];
    }
    public function getPelanggan() {
        return pg_fetch_all(pg_query($this->db->conn, "SELECT * FROM pelanggan ORDER BY nama_pelanggan")) ?: [];
    }
    public function getMejaKosong() {
        return pg_fetch_all(pg_query($this->db->conn, "SELECT * FROM meja WHERE status='kosong' ORDER BY nomor_meja")) ?: [];
    }
    public function getExplainAnalyze($kw) {
        $res = pg_query($this->db->conn, "EXPLAIN ANALYZE SELECT * FROM menu WHERE nama_menu = '$kw'");
        $out = []; while($r = pg_fetch_row($res)) $out[] = $r[0];
        return implode("\n", $out);
    }
}
?>