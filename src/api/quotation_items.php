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
            $quotationId = $_GET['quotation_id'] ?? null;
            
            if (!$quotationId) {
                http_response_code(400);
                echo json_encode(["error" => "Quotation ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah quotation ada dan user memiliki akses
            if ($user['role'] !== 'admin') {
                $stmt = $pdo->prepare("SELECT CustomerID FROM Quotations WHERE QuotationID = ?");
                $stmt->execute([$quotationId]);
                $quotation = $stmt->fetch();
                
                if (!$quotation || $quotation['CustomerID'] != $user['id']) {
                    http_response_code(403);
                    echo json_encode(["error" => "Akses ditolak"]);
                    exit;
                }
            }
            
            // Ambil semua item quotation
            $stmt = $pdo->prepare("SELECT * FROM Quotation_Items WHERE QuotationID = ? ORDER BY ItemID");
            $stmt->execute([$quotationId]);
            $items = $stmt->fetchAll();
            
            echo json_encode($items);
            break;
            
        case "POST":
            $quotationId = $data['QuotationID'] ?? null;
            
            if (!$quotationId) {
                http_response_code(400);
                echo json_encode(["error" => "Quotation ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah quotation ada dan user memiliki akses
            if ($user['role'] !== 'admin') {
                $stmt = $pdo->prepare("SELECT CustomerID, Status FROM Quotations WHERE QuotationID = ?");
                $stmt->execute([$quotationId]);
                $quotation = $stmt->fetch();
                
                if (!$quotation || $quotation['CustomerID'] != $user['id']) {
                    http_response_code(403);
                    echo json_encode(["error" => "Akses ditolak"]);
                    exit;
                }
                
                // Customer hanya bisa menambah item jika quotation masih draft
                if ($quotation['Status'] !== 'Draft') {
                    http_response_code(403);
                    echo json_encode(["error" => "Hanya quotation dengan status Draft yang bisa ditambah item"]);
                    exit;
                }
            }
            
            // Validasi input
            if (empty($data['ItemName']) || empty($data['Quantity']) || empty($data['Price'])) {
                http_response_code(400);
                echo json_encode(["error" => "ItemName, Quantity, dan Price harus diisi"]);
                exit;
            }
            
            // Tambah item quotation
            $stmt = $pdo->prepare("INSERT INTO Quotation_Items (QuotationID, ItemName, Quantity, Price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$quotationId, $data['ItemName'], $data['Quantity'], $data['Price']]);
            $itemId = $pdo->lastInsertId();
            
            // Update total quotation
            updateQuotationTotal($quotationId);
            
            echo json_encode([
                "message" => "Item quotation berhasil ditambahkan", 
                "ItemID" => $itemId
            ]);
            break;
            
        case "PUT":
            $itemId = $_GET['id'] ?? ($data['ItemID'] ?? null);
            
            if (!$itemId) {
                http_response_code(400);
                echo json_encode(["error" => "Item ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah item ada
            $stmt = $pdo->prepare("SELECT qi.*, q.CustomerID, q.Status FROM Quotation_Items qi JOIN Quotations q ON qi.QuotationID = q.QuotationID WHERE qi.ItemID = ?");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch();
            
            if (!$item) {
                http_response_code(404);
                echo json_encode(["error" => "Item tidak ditemukan"]);
                exit;
            }
            
            // Cek akses user
            if ($user['role'] !== 'admin') {
                if ($item['CustomerID'] != $user['id']) {
                    http_response_code(403);
                    echo json_encode(["error" => "Akses ditolak"]);
                    exit;
                }
                
                // Customer hanya bisa edit item jika quotation masih draft
                if ($item['Status'] !== 'Draft') {
                    http_response_code(403);
                    echo json_encode(["error" => "Hanya quotation dengan status Draft yang bisa diubah item"]);
                    exit;
                }
            }
            
            // Validasi input
            if (empty($data['ItemName']) || empty($data['Quantity']) || empty($data['Price'])) {
                http_response_code(400);
                echo json_encode(["error" => "ItemName, Quantity, dan Price harus diisi"]);
                exit;
            }
            
            // Update item quotation
            $stmt = $pdo->prepare("UPDATE Quotation_Items SET ItemName = ?, Quantity = ?, Price = ? WHERE ItemID = ?");
            $stmt->execute([$data['ItemName'], $data['Quantity'], $data['Price'], $itemId]);
            
            // Update total quotation
            updateQuotationTotal($item['QuotationID']);
            
            echo json_encode(["message" => "Item quotation berhasil diperbarui"]);
            break;
            
        case "DELETE":
            $itemId = $_GET['id'] ?? null;
            
            if (!$itemId) {
                http_response_code(400);
                echo json_encode(["error" => "Item ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah item ada
            $stmt = $pdo->prepare("SELECT qi.*, q.CustomerID, q.Status FROM Quotation_Items qi JOIN Quotations q ON qi.QuotationID = q.QuotationID WHERE qi.ItemID = ?");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch();
            
            if (!$item) {
                http_response_code(404);
                echo json_encode(["error" => "Item tidak ditemukan"]);
                exit;
            }
            
            // Cek akses user
            if ($user['role'] !== 'admin') {
                if ($item['CustomerID'] != $user['id']) {
                    http_response_code(403);
                    echo json_encode(["error" => "Akses ditolak"]);
                    exit;
                }
                
                // Customer hanya bisa hapus item jika quotation masih draft
                if ($item['Status'] !== 'Draft') {
                    http_response_code(403);
                    echo json_encode(["error" => "Hanya quotation dengan status Draft yang bisa dihapus item"]);
                    exit;
                }
            }
            
            // Hapus item quotation
            $stmt = $pdo->prepare("DELETE FROM Quotation_Items WHERE ItemID = ?");
            $stmt->execute([$itemId]);
            
            // Update total quotation
            updateQuotationTotal($item['QuotationID']);
            
            echo json_encode(["message" => "Item quotation berhasil dihapus"]);
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

// Helper function untuk update total quotation
function updateQuotationTotal($quotationId) {
    global $pdo;
    
    // Hitung total dari semua item
    $stmt = $pdo->prepare("SELECT SUM(Quantity * Price) as Total FROM Quotation_Items WHERE QuotationID = ?");
    $stmt->execute([$quotationId]);
    $result = $stmt->fetch();
    
    $total = $result['Total'] ?? 0;
    
    // Update total quotation
    $stmt = $pdo->prepare("UPDATE Quotations SET TotalAmount = ? WHERE QuotationID = ?");
    $stmt->execute([$total, $quotationId]);
}
?>