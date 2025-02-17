<?php
require_once __DIR__ . "/../../vendor/autoload.php"; // Charger les dépendances Composer

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private static $secret_key = "secret123"; // 🔐 Change ce secret !
    private static $algorithm = "HS256";

    public static function generateToken($user_id) {
        $payload = [
            "iss" => "EcoRide", // Émetteur du token
            "iat" => time(), // Date d'émission
            "exp" => time() + 3600, // Expiration (1 heure)
            "user_id" => $user_id
        ];

        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
            return $decoded->user_id;
        } catch (Exception $e) {
            return null; // Token invalide
        }
    }
}
?>
