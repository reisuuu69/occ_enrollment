<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "occ_enrollment";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect() {
        $this->conn = null;

        try {
            // First attempt: connect directly to target database
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            @file_put_contents(__DIR__ . '/../email_log.txt', "[" . date('Y-m-d H:i:s') . "] DB CONNECT (phase 1) ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            // If database might not exist, try to create it, then reconnect
            try {
                $rootDsn = "mysql:host=" . $this->host . ";charset=utf8mb4";
                $root = new PDO($rootDsn, $this->username, $this->password);
                $root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $this->db_name);
                $root->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                // Reconnect to the newly ensured database
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $this->conn;
            } catch (PDOException $inner) {
                @file_put_contents(__DIR__ . '/../email_log.txt', "[" . date('Y-m-d H:i:s') . "] DB CONNECT (phase 2) ERROR: " . $inner->getMessage() . "\n", FILE_APPEND);
                // Rethrow the original connection error if creation fails as well
                throw $inner;
            }
        }
    }
}