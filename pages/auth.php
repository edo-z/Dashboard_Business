<?php
session_start();
require "vendor/autoload.php"; // pastikan path benar

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$jwt_secret = "3e5a8838a64ea85757d53a597571b7d133c850b60ee70e815902306cef6f94945a331f1c1d69403ce7ac143c6a4f84b84b03c8421e9791acece8cfe04afa6ec6";

if (empty($_SESSION['jwt'])) {
    header("Location: login.php");
    exit;
}

$token = $_SESSION['jwt'];

try {
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));

    $_SESSION['user'] = [
        "id"    => $decoded->id ?? null,
        "name"  => $decoded->name ?? "",
        "email" => $decoded->email ?? "",
        "role"  => $decoded->role ?? "customer"
    ];

} catch (Exception $e) {
    error_log("JWT Error: " . $e->getMessage()); // simpan ke log server
    session_destroy();
    header("Location: login.php?error=expired");
    exit;
}
