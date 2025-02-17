<?php
require_once __DIR__ . "/../vendor/autoload.php"; // Charger les dépendances Composer
require_once __DIR__ . "/config/jwt.php"; // 🔹 Vérifier le chemin

$token = JWTHandler::generateToken(1);
echo "Token généré : " . $token;
?>
