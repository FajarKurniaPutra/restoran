<?php
require_once '../config/koneksi.php';

class ReportModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getTopMenuChart() {
        $query = "SELECT nama_menu, total_terjual 
                FROM mv_analisis_menu_laris 
                ORDER BY total_terjual DESC LIMIT 10";
        return pg_fetch_all(pg_query($this->db->conn, $query)) ?: [];
    }

    public function refreshMaterializedView() {
        return pg_query($this->db->conn, "REFRESH MATERIALIZED VIEW mv_analisis_menu_laris");
    }

    public function getMenuPerformance($page = 1, $limit = 5) {
        $offset = ($page - 1) * $limit;

        $queryData = "SELECT nama_menu, total_terjual, total_pendapatan 
                    FROM mv_analisis_menu_laris 
                    ORDER BY total_terjual DESC 
                    LIMIT $1 OFFSET $2";
        
        $resData = pg_query_params($this->db->conn, $queryData, [$limit, $offset]);

        $resCount = pg_query($this->db->conn, "SELECT COUNT(*) FROM mv_analisis_menu_laris");
        $totalRows = pg_fetch_result($resCount, 0, 0);

        return [
            'data' => pg_fetch_all($resData) ?: [],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalRows / $limit),
                'total_rows' => $totalRows
            ]
        ];
    }

    public function getLaporanShift() {
        $query = "SELECT 
                    CASE 
                        WHEN EXTRACT(HOUR FROM p.tanggal_pesanan) BETWEEN 7 AND 15 THEN 'Shift Pagi'
                        ELSE 'Shift Malam'
                    END as nama_shift,
                    COUNT(DISTINCT p.id) as jumlah_transaksi, 
                    SUM(dp.subtotal) as total_pendapatan 
                FROM pesanan p
                JOIN detail_pesanan dp ON p.id = dp.pesanan_id
                WHERE p.status_order = 'selesai'
                GROUP BY nama_shift 
                ORDER BY nama_shift ASC";
        return pg_fetch_all(pg_query($this->db->conn, $query)) ?: [];
    }

    public function getLaporanServer() {
        $query = "SELECT u.fullname as nama_server, 
                        COUNT(DISTINCT p.id) as jumlah_transaksi, 
                        SUM(dp.subtotal) as total_pendapatan 
                FROM pesanan p
                JOIN detail_pesanan dp ON p.id = dp.pesanan_id
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.status_order = 'selesai'
                GROUP BY u.fullname 
                ORDER BY total_pendapatan DESC";
        return pg_fetch_all(pg_query($this->db->conn, $query)) ?: [];
    }

    public function getLaporanPenjualan($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $queryData = "SELECT * FROM view_laporan_lengkap ORDER BY tanggal_pesanan DESC LIMIT $1 OFFSET $2";
        $resData = pg_query_params($this->db->conn, $queryData, [$limit, $offset]);
        
        $resCount = pg_query($this->db->conn, "SELECT COUNT(*) FROM view_laporan_lengkap");
        $totalRows = pg_fetch_result($resCount, 0, 0);
        
        return [
            'data' => pg_fetch_all($resData) ?: [],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalRows / $limit),
                'total_rows' => $totalRows
            ]
        ];
    }
}
?>