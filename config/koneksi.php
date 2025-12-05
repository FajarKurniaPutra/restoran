<?php
date_default_timezone_set('Asia/Jakarta');

class Database
{
    private $host = "localhost";
    private $username = "postgres";
    private $password = "PWDpwd";
    private $database = "db_restoran_remake";
    private $port = "5433";
    public $conn;

    public function __construct()
    {
        $connection_string = "host={$this->host} port={$this->port} dbname={$this->database} user={$this->username} password={$this->password}";
        $this->conn = pg_connect($connection_string);

        if (!$this->conn) {
            die("Connection failed: " . pg_last_error());
        }
    }
}
?>