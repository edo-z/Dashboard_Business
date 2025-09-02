<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "../config/security.php"; // 🔒 cek X-API-KEY
require_once "auth.php";               // 🔑 cek JWT
require_once "../config/db.php";
require_once "../config/helpers.php";

$user = authenticate(); // ambil info user dari JWT

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    switch ($method) {
        
        case "GET":
            $id = $_GET['id'] ?? ($data['CustomerID'] ?? null);

            if ($id) {
                if ($user['role'] !== 'admin' && $id != $user['id']) {
                    http_response_code(403);
                    echo json_encode(["error" => "Akses ditolak"]);
                    exit;
                }

                $stmt = $pdo->prepare("SELECT * FROM Customers WHERE CustomerID = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch());
            } else {
                if ($user['role'] === 'admin') {
                    $stmt = $pdo->query("SELECT * FROM Customers");
                    echo json_encode($stmt->fetchAll());
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM Customers WHERE UserID = ?");
                    $stmt->execute([$user['id']]);
                    echo json_encode($stmt->fetchAll());
                }
            }
            if (isset($_GET['search'])) {
                $search = "%" . $_GET['search'] . "%";
                $stmt = $pdo->prepare("SELECT CustomerID, Name, Phone FROM Customers WHERE Name LIKE ? OR Phone LIKE ?");
                $stmt->execute([$search, $search]);
                echo json_encode($stmt->fetchAll());
                exit;
            }
            break;

       case "POST":
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Hanya admin bisa menambahkan customer"]);
        exit;
    }

    // Validasi input
    $name    = trim($data['Name']    ?? '');
    $email   = trim($data['Email']   ?? '');
    $phone   = trim($data['Phone']   ?? '');
    $address = trim($data['Address'] ?? '');

    if ($name === '') {
        http_response_code(400);
        echo json_encode(["error" => "Nama wajib diisi"]);
        exit;
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["error" => "Format email tidak valid"]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Insert tanpa CustomerCode (NULL dulu)
        $stmt = $pdo->prepare("
    INSERT INTO Customers (Name, Address, Phone, Email, CustomerCode)
    VALUES (?, ?, ?, ?, NULL)
");
$stmt->execute([$name, $address, $phone, $email]);


        $id   = $pdo->lastInsertId();
        $code = generateCode("CST", $id);

        // Update CustomerCode
        $stmt = $pdo->prepare("UPDATE Customers SET CustomerCode=? WHERE CustomerID=?");
        $stmt->execute([$code, $id]);

        $pdo->commit();

        echo json_encode([
            "message"      => "Customer ditambahkan",
            "CustomerID"   => $id,
            "CustomerCode" => $code
        ]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        // Log ke error_log di cPanel (file: error_log)
        error_log("POST api/customers -> ".$e->getMessage());
        http_response_code(500);
        echo json_encode(["error" => "Gagal menambah customer"]);
    }
    break;


        case "PUT":
            $id = $_GET['id'] ?? ($data['CustomerID'] ?? null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Customer ID harus disediakan"]);
                exit;
            }

            if ($user['role'] !== 'admin' && $id != $user['id']) {
                http_response_code(403);
                echo json_encode(["error" => "Akses ditolak"]);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE Customers SET Name=?, Address=?, Phone=?, Email=? WHERE CustomerID=?");
            $stmt->execute([$data['Name'], $data['Address'], $data['Phone'], $data['Email'], $id]);

            echo json_encode(["message" => "Customer diperbarui"]);
            break;

        case "DELETE":
            $id = $_GET['id'] ?? ($data['CustomerID'] ?? null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Customer ID harus disediakan"]);
                exit;
            }

            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa menghapus customer"]);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM Customers WHERE CustomerID = ?");
            $stmt->execute([$id]);

            echo json_encode(["message" => "Customer dihapus"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Metode tidak didukung"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Terjadi kesalahan, silakan coba lagi"]);
}
