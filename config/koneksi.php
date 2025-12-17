<?php
date_default_timezone_set('Asia/Jakarta');

class Database {
    private $host = "localhost";
    private $port = "5433";
    private $db_name = "db_restoran_remake";
    private $username = "postgres";
    private $password = "PWDpwd";
    public $conn;

    public function __construct() {
        $connection_string = "host={$this->host} port={$this->port} dbname={$this->db_name} user={$this->username} password={$this->password}";
        $this->conn = pg_connect($connection_string);

        if (!$this->conn) {
            die("Connection failed: " . pg_last_error());
        }
        return $this->conn;
    }
}

$database = new Database();
$db = $database->conn;
?>