<?php
require_once __DIR__ . '/../config/config.php';

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'myspp_db';
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname}", 
                $this->user, 
                $this->pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}