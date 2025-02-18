<?php
class Database {
    private $host = "127.0.0.1";  // 🔹 Utilise "127.0.0.1" au lieu de "localhost"
    private $db_name = "ecoride"; // 🔹 Vérifie que la base existe
    private $username = "root";    // 🔹 Ton utilisateur MySQL
    private $password = "";        // 🔹 Ton mot de passe MySQL (vide si XAMPP)
    private $port = "3306";        // 🔹 Vérifie le port MySQL
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage()); // 🔥 Arrête le script si erreur
        }
        return $this->conn;
    }
}
?>
