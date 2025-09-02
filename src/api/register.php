<?php
header("Content-Type: application/json");

require_once "../config/db.php";
require_once "../config/security.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$name     = trim($data['Name'] ?? "");
$email    = trim($data['Email'] ?? "");
$password = trim($data['Password'] ?? "");

if (empty($name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["error" => "Data tidak lengkap"]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO Users (Name, Email, PasswordHash, Role) VALUES (?, ?, ?, 'customer')");
    $stmt->execute([$name, $email, $hashedPassword]);

    echo json_encode([
        "message" => "Registrasi berhasil",
        "name"    => $name,
        "role"    => "customer"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Gagal registrasi"]);
}
