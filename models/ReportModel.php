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

    public function getMenuPerformance($page = 1, $limit = 5, $kategori = '') {
        $offset = ($page - 1) * $limit;
        $params = [];
        $whereClause = "";
        $counter = 1;

        if (!empty($kategori)) {
            $whereClause = "WHERE nama_kategori = $" . $counter;
            $params[] = $kategori;
            $counter++;
        }

        $queryData = "SELECT nama_menu, nama_kategori, total_terjual, total_pendapatan 
                    FROM mv_analisis_menu_laris 
                    $whereClause
                    ORDER BY total_terjual DESC 
                    LIMIT $" . $counter . " OFFSET $" . ($counter + 1);
        
        $queryCount = "SELECT COUNT(*) FROM mv_analisis_menu_laris $whereClause";

        $paramsData = array_merge($params, [$limit, $offset]);
        $resData = pg_query_params($this->db->conn, $queryData, $paramsData);
        
        $resCount = (!empty($params)) 
            ? pg_query_params($this->db->conn, $queryCount, $params)
            : pg_query($this->db->conn, $queryCount);
            
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

    public function getAllKategori() {
        $query = "SELECT DISTINCT nama_kategori FROM mv_analisis_menu_laris ORDER BY nama_kategori ASC";
        return pg_fetch_all(pg_query($this->db->conn, $query)) ?: [];
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

    public function getLaporanPenjualan($page = 1, $limit = 10, $status = '', $sortBy = 'tanggal_pesanan', $sortOrder = 'DESC') {
        $offset = ($page - 1) * $limit;
        $params = [];
        $whereClause = "";
        $counter = 1;

        $allowedSorts = ['tanggal_pesanan', 'subtotal', 'jumlah', 'nama_menu'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'tanggal_pesanan';
        
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        if (!empty($status)) {
            $whereClause = "WHERE status_order = $" . $counter;
            $params[] = $status;
            $counter++;
        }

        $queryData = "SELECT * FROM view_laporan_lengkap 
                    $whereClause 
                    ORDER BY $sortBy $sortOrder 
                    LIMIT $" . $counter . " OFFSET $" . ($counter + 1);
        
        $queryCount = "SELECT COUNT(*) FROM view_laporan_lengkap $whereClause";

        $paramsData = array_merge($params, [$limit, $offset]);
        $resData = pg_query_params($this->db->conn, $queryData, $paramsData);
        
        $resCount = (!empty($params)) 
            ? pg_query_params($this->db->conn, $queryCount, $params)
            : pg_query($this->db->conn, $queryCount);

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

    public function getRiwayatReservasi($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $queryData = "SELECT r.*, p.nama_pelanggan, p.no_telepon, m.nomor_meja 
                    FROM reservasi r
                    LEFT JOIN pelanggan p ON r.pelanggan_id = p.id
                    LEFT JOIN meja m ON r.meja_id = m.id
                    ORDER BY r.tanggal_reservasi DESC, r.jam_reservasi DESC
                    LIMIT $1 OFFSET $2";
        
        $resData = pg_query_params($this->db->conn, $queryData, [$limit, $offset]);
        
        $resCount = pg_query($this->db->conn, "SELECT COUNT(*) FROM reservasi");
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