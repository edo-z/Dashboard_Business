<?php
header("Content-Type: application/json");

require_once "../config/security.php";
require_once "../config/db.php";
require "vendor/autoload.php"; // JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$jwt_secret = "3e5a8838a64ea85757d53a597571b7d133c850b60ee70e815902306cef6f94945a331f1c1d69403ce7ac143c6a4f84b84b03c8421e9791acece8cfe04afa6ec6"; // ganti dengan yang aman

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$email    = trim($data['Email'] ?? "");
$password = trim($data['Password'] ?? "");

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["error" => "Email & Password harus diisi"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['PasswordHash'])) {
        $payload = [
            "id"    => $user['UserID'],
            "name"  => $user['Name'],
            "email" => $user['Email'],
            "role"  => $user['Role'],
            "iat"   => time(),
            "exp"   => time() + (60 * 60)
        ];

        $jwt = JWT::encode($payload, $jwt_secret, 'HS256');

        echo json_encode([
            "message" => "Login berhasil",
            "token"   => $jwt,
            "role"    => $user['Role'],
            "name"    => $user['Name']
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Email atau password salah"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Gagal login"]);
}
