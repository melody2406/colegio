<?php
class Database {
    private $host = "mysql";  
    private $db_name = "colegio";
    private $username = "melo07";  
    private $password = "123456";  
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            $this->conn->exec("SET NAMES utf8mb4");

        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }

        return $this->conn;
    }
}
?>
