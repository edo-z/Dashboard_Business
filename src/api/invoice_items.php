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
            $invoiceId = $_GET['invoice_id'] ?? null;
            
            if (!$invoiceId) {
                http_response_code(400);
                echo json_encode(["error" => "Invoice ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah invoice ada dan user memiliki akses
            if ($user['role'] !== 'admin') {
                $stmt = $pdo->prepare("SELECT q.CustomerID FROM Invoices i JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE i.InvoiceID = ?");
                $stmt->execute([$invoiceId]);
                $invoice = $stmt->fetch();
                
                if (!$invoice || $invoice['CustomerID'] != $user['id']) {
                    http_response_code(403);
                    echo json_encode(["error" => "Akses ditolak"]);
                    exit;
                }
            }
            
            // Ambil semua item invoice
            $stmt = $pdo->prepare("SELECT * FROM Invoice_Items WHERE InvoiceID = ? ORDER BY InvoiceItemID");
            $stmt->execute([$invoiceId]);
            $items = $stmt->fetchAll();
            
            echo json_encode($items);
            break;
            
        case "POST":
            // Hanya admin yang bisa menambah invoice item
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa menambah item invoice"]);
                exit;
            }
            
            $invoiceId = $data['InvoiceID'] ?? null;
            
            if (!$invoiceId) {
                http_response_code(400);
                echo json_encode(["error" => "Invoice ID harus disediakan"]);
                exit;
            }
            
            // Validasi input
            if (empty($data['ItemName']) || empty($data['Quantity']) || empty($data['Price'])) {
                http_response_code(400);
                echo json_encode(["error" => "ItemName, Quantity, dan Price harus diisi"]);
                exit;
            }
            
            // Tambah item invoice
            $stmt = $pdo->prepare("INSERT INTO Invoice_Items (InvoiceID, ItemName, Quantity, Price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$invoiceId, $data['ItemName'], $data['Quantity'], $data['Price']]);
            $itemId = $pdo->lastInsertId();
            
            // Update total invoice
            updateInvoiceTotal($invoiceId);
            
            echo json_encode([
                "message" => "Item invoice berhasil ditambahkan", 
                "InvoiceItemID" => $itemId
            ]);
            break;
            
        case "PUT":
            // Hanya admin yang bisa mengubah invoice item
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa mengubah item invoice"]);
                exit;
            }
            
            $itemId = $_GET['id'] ?? ($data['InvoiceItemID'] ?? null);
            
            if (!$itemId) {
                http_response_code(400);
                echo json_encode(["error" => "Item ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah item ada
            $stmt = $pdo->prepare("SELECT * FROM Invoice_Items WHERE InvoiceItemID = ?");
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
            
            // Update item invoice
            $stmt = $pdo->prepare("UPDATE Invoice_Items SET ItemName = ?, Quantity = ?, Price = ? WHERE InvoiceItemID = ?");
            $stmt->execute([$data['ItemName'], $data['Quantity'], $data['Price'], $itemId]);
            
            // Update total invoice
            updateInvoiceTotal($item['InvoiceID']);
            
            echo json_encode(["message" => "Item invoice berhasil diperbarui"]);
            break;
            
        case "DELETE":
            // Hanya admin yang bisa menghapus invoice item
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa menghapus item invoice"]);
                exit;
            }
            
            $itemId = $_GET['id'] ?? null;
            
            if (!$itemId) {
                http_response_code(400);
                echo json_encode(["error" => "Item ID harus disediakan"]);
                exit;
            }
            
            // Cek apakah item ada
            $stmt = $pdo->prepare("SELECT * FROM Invoice_Items WHERE InvoiceItemID = ?");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch();
            
            if (!$item) {
                http_response_code(404);
                echo json_encode(["error" => "Item tidak ditemukan"]);
                exit;
            }
            
            // Hapus item invoice
            $stmt = $pdo->prepare("DELETE FROM Invoice_Items WHERE InvoiceItemID = ?");
            $stmt->execute([$itemId]);
            
            // Update total invoice
            updateInvoiceTotal($item['InvoiceID']);
            
            echo json_encode(["message" => "Item invoice berhasil dihapus"]);
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

// Helper function untuk update total invoice
function updateInvoiceTotal($invoiceId) {
    global $pdo;
    
    // Hitung total dari semua item
    $stmt = $pdo->prepare("SELECT SUM(Quantity * Price) as Total FROM Invoice_Items WHERE InvoiceID = ?");
    $stmt->execute([$invoiceId]);
    $result = $stmt->fetch();
    
    $total = $result['Total'] ?? 0;
    
    // Update total invoice
    $stmt = $pdo->prepare("UPDATE Invoices SET TotalAmount = ? WHERE InvoiceID = ?");
    $stmt->execute([$total, $invoiceId]);
}
?>