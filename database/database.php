<?php
class database {
    private $host = 'localhost'; 
    private $port = '3307';      
    private $db_name = 'conpay';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    public function connect() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?>