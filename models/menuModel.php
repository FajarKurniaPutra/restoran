<?php
require_once '../config/koneksi.php';

class MenuModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function create($nama_menu, $harga, $id_kategori, $statusmenu, $deskripsi, $foto_url)
    {
        $query = "INSERT INTO menu (nama_menu, harga, id_kategori, statusmenu, deskripsi, foto_url) 
                    VALUES ($1, $2, $3, $4, $5, $6)";

        return pg_query_params(
            $this->db->conn,
            $query,
            array($nama_menu, $harga, $id_kategori, $statusmenu, $deskripsi, $foto_url)
        );
    }

    public function read()
    {
        $query = "SELECT * FROM menu ORDER BY id_menu ASC";
        $result = pg_query($this->db->conn, $query);

        $data = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function readById($id_menu)
    {
        $query = "SELECT * FROM menu WHERE id_menu = $1";
        $result = pg_query_params($this->db->conn, $query, array($id_menu));

        if ($result && pg_num_rows($result) == 1) {
            return pg_fetch_assoc($result);
        }

        return null;
    }

    public function update($id_menu, $nama_menu, $harga, $id_kategori, $statusmenu, $deskripsi, $foto_url)
    {
        $query = "UPDATE menu 
                    SET nama_menu = $1,
                        harga = $2,
                        id_kategori = $3,
                        statusmenu = $4,
                        deskripsi = $5,
                        foto_url = $6
                    WHERE id_menu = $7";

        return pg_query_params(
            $this->db->conn,
            $query,
            array($nama_menu, $harga, $id_kategori, $statusmenu, $deskripsi, $foto_url, $id_menu)
        );
    }

    public function delete($id_menu)
    {
        $query = "DELETE FROM menu WHERE id_menu = $1";
        return pg_query_params($this->db->conn, $query, array($id_menu));
    }
}
?>
