<?php
require_once '../config/koneksi.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = $1";
        $result = pg_query_params($this->db->conn, $query, [$username]);
        
        if (pg_num_rows($result) > 0) {
            $user = pg_fetch_assoc($result);
            
            if ($user['password'] === $password) {
                return $user; 
            }
        }
        return false; 
    }
}
?>