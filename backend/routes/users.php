<?php
require_once __DIR__ . "/../config/database.php";

require_once "../config/database.php"; // 🔹 Charger la connexion
require_once "../config/jwt.php"; // 🔹 Charger JWT

$database = new Database();
$conn = $database->getConnection(); // 🔹 Récupérer la connexion

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Connexion utilisateur avec JWT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->email) && !empty($data->password)) {
        $query = "SELECT id, pseudo, password FROM users WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $data->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($data->password, $user['password'])) {
                $token = JWTHandler::generateToken($user['id']);
                echo json_encode(["message" => "Connexion réussie", "token" => $token]);
            } else {
                echo json_encode(["message" => "Mot de passe incorrect"]);
            }
        } else {
            echo json_encode(["message" => "Utilisateur introuvable"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes"]);
    }
}
?>
