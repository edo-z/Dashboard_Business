<?php
require_once "config.php";

// Atur CORS agar hanya domain kamu yang bisa akses
header("Access-Control-Allow-Origin: https://mokkoproject.biz.id");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, X-API-KEY");

// Nonaktifkan error detail
ini_set('display_errors', 0);
error_reporting(0);

// Validasi API Key
$headers = getallheaders();
$apiKey = $headers['X-API-KEY'] ?? null;

if ($apiKey !== API_KEY) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}
?>
