<?php
require_once "../config/database.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$conn = $database->getConnection();

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->pseudo) && !empty($data->email) && !empty($data->password)) {
        $password_hash = password_hash($data->password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (pseudo, email, password) VALUES (:pseudo, :email, :password)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":pseudo", $data->pseudo);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":password", $password_hash);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Utilisateur créé avec succès"]);
        } else {
            echo json_encode(["message" => "Erreur lors de l'inscription"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes"]);
    }
} else {
    echo json_encode(["message" => "Méthode non autorisée"]);
}

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
                echo json_encode(["message" => "Connexion réussie", "user_id" => $user['id'], "pseudo" => $user['pseudo']]);
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
