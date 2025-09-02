<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "../config/security.php"; // 🔒 cek X-API-KEY
require_once "auth.php";               // 🔑 cek JWT
require_once "../config/db.php";

$user = authenticate(); // ambil info user dari JWT
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    switch ($method) {
        case "GET":
            $doId = $_GET['do_id'] ?? null;
            
            if (!$doId) {
                http_response_code(400);
                echo json_encode(["error" => "Delivery Order ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah DO ada dan user memiliki akses
            if ($user['role'] !== 'admin') {
                $stmt = $pdo->prepare("SELECT q.CustomerID FROM DeliveryOrders d JOIN Invoices i ON d.InvoiceID = i.InvoiceID JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE d.DOID = ?");
                $stmt->execute([$doId]);
                $do = $stmt->fetch();
                
                if (!$do || $do['CustomerID'] != $user['id']) {
                    http_response_code(403);
                    echo json_encode(["error" => "Akses ditolak"]);
                    exit;
                }
            }
            
            // Ambil semua item DO
            $stmt = $pdo->prepare("SELECT * FROM DO_Items WHERE DOID = ? ORDER BY DOItemID");
            $stmt->execute([$doId]);
            $items = $stmt->fetchAll();
            
            echo json_encode($items);
            break;
            
        case "POST":
            // Hanya admin yang bisa menambah DO item
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa menambah item delivery order"]);
                exit;
            }
            
            $doId = $data['DOID'] ?? null;
            
            if (!$doId) {
                http_response_code(400);
                echo json_encode(["error" => "Delivery Order ID harus disediakan"]);
                exit;
            }
            
            // Validasi input
            if (empty($data['ItemName']) || empty($data['Quantity']) || empty($data['Price'])) {
                http_response_code(400);
                echo json_encode(["error" => "ItemName, Quantity, dan Price harus diisi"]);
                exit;
            }
            
            // Tambah item DO
            $stmt = $pdo->prepare("INSERT INTO DO_Items (DOID, ItemName, Quantity, Price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$doId, $data['ItemName'], $data['Quantity'], $data['Price']]);
            $itemId = $pdo->lastInsertId();
            
            echo json_encode([
                "message" => "Item delivery order berhasil ditambahkan", 
                "DOItemID" => $itemId
            ]);
            break;
            
        case "PUT":
            // Hanya admin yang bisa mengubah DO item
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa mengubah item delivery order"]);
                exit;
            }
            
            $itemId = $_GET['id'] ?? ($data['DOItemID'] ?? null);
            
            if (!$itemId) {
                http_response_code(400);
                echo json_encode(["error" => "Item ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah item ada
            $stmt = $pdo->prepare("SELECT * FROM DO_Items WHERE DOItemID = ?");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch();
            
            if (!$item) {
                http_response_code(404);
                echo json_encode(["error" => "Item tidak ditemukan"]);
                exit;
            }
            
            // Validasi input
            if (empty($data['ItemName']) || empty($data['Quantity']) || empty($data['Price'])) {
                http_response_code(400);
                echo json_encode(["error" => "ItemName, Quantity, dan Price harus diisi"]);
                exit;
            }
            
            // Update item DO
            $stmt = $pdo->prepare("UPDATE DO_Items SET ItemName = ?, Quantity = ?, Price = ? WHERE DOItemID = ?");
            $stmt->execute([$data['ItemName'], $data['Quantity'], $data['Price'], $itemId]);
            
            echo json_encode(["message" => "Item delivery order berhasil diperbarui"]);
            break;
            
        case "DELETE":
            // Hanya admin yang bisa menghapus DO item
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa menghapus item delivery order"]);
                exit;
            }
            
            $itemId = $_GET['id'] ?? null;
            
            if (!$itemId) {
                http_response_code(400);
                echo json_encode(["error" => "Item ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah item ada
            $stmt = $pdo->prepare("SELECT * FROM DO_Items WHERE DOItemID = ?");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch();
            
            if (!$item) {
                http_response_code(404);
                echo json_encode(["error" => "Item tidak ditemukan"]);
                exit;
            }
            
            // Hapus item DO
            $stmt = $pdo->prepare("DELETE FROM DO_Items WHERE DOItemID = ?");
            $stmt->execute([$itemId]);
            
            echo json_encode(["message" => "Item delivery order berhasil dihapus"]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(["error" => "Metode tidak didukung"]);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Terjadi kesalahan: " . $e->getMessage()]);
}
?>