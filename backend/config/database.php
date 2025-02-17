<?php
class Database {
    private $host = "localhost:3306";
    private $db_name = "ecoride";
    private $username = "root";  // Remplace par ton utilisateur MySQL
    private $password = "";      // Remplace par ton mot de passe MySQL
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
