<?php
// auth.php
require_once "../config/config.php";
require_once "../config/db.php";
require_once "../config/security.php"; // tetap cek X-API-KEY
require "vendor/autoload.php"; // JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function authenticate() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized: Token tidak ada"]);
        exit;
    }

    $authHeader = $headers['Authorization'];
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized: Format token salah"]);
        exit;
    }

    $jwt = $matches[1];

    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
        return (array) $decoded; // berisi id, name, email, role
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized: Token tidak valid"]);
        exit;
    }
}
