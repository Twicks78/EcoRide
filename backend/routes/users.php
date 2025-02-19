<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

error_log("📡 API appelée : " . $_SERVER['REQUEST_METHOD'] . " " . $_GET['action']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {
    // Lire les données reçues
    $data = json_decode(file_get_contents("php://input"));

    // Debug : Vérifier si les données sont bien reçues
    error_log("📥 Données reçues : " . json_encode($data));

    if (!empty($data->pseudo) && !empty($data->email) && !empty($data->password)) {
        echo json_encode(["message" => "✅ Données reçues"]);
    } else {
        echo json_encode(["message" => "❌ Données incomplètes"]);
        http_response_code(400);
    }
} else {
    echo json_encode(["message" => "❌ Méthode non autorisée"]);
    http_response_code(405);
}
