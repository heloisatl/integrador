<?php
class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    public $conn;

    public function __construct($host, $user, $pass, $dbname = null) {
        $this->host   = $host;
        $this->user   = $user;
        $this->pass   = $pass;
        $this->dbname = $dbname;
    }

    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};charset=utf8mb4";
            if ($this->dbname) {
                $dsn .= ";dbname={$this->dbname}";
            }
            $this->conn = new PDO($dsn, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            return null;
        }
    }
}