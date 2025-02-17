<?php
require_once __DIR__ . "/../../vendor/autoload.php"; // Charger Composer

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private static $secret_key = "secret123"; // 🔐 Change ce secret !
    private static $algorithm = "HS256";

    public static function generateToken($user_id) {
        $payload = [
            "iss" => "EcoRide",
            "iat" => time(),
            "exp" => time() + 3600, // Expire après 1 heure
            "user_id" => $user_id
        ];
        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
            return $decoded->user_id;
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
