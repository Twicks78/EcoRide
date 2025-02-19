require_once '../config/database.php';
require_once '../config/jwt.php';

$headers = getallheaders();
$token = $headers["Authorization"] ?? '';

$user = getUserFromToken($token);
if (!$user || $user["role"] !== "admin") {
    echo json_encode(["message" => "Accès refusé, vous devez être administrateur"]);
    http_response_code(403);
    exit;
}