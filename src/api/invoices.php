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
            $id = $_GET['id'] ?? ($data['InvoiceID'] ?? null);
            if ($id) {
    // Ambil invoice spesifik
    $stmt = $pdo->prepare("SELECT i.*, q.QuotationCode, c.Name as CustomerName, i.Status as InvoiceStatus 
                          FROM Invoices i 
                          LEFT JOIN Quotations q ON i.QuotationID = q.QuotationID 
                          LEFT JOIN Customers c ON q.CustomerID = c.CustomerID 
                          WHERE i.InvoiceID = ?");
    $stmt->execute([$id]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        http_response_code(404);
        echo json_encode(["error" => "Invoice tidak ditemukan"]);
        exit;
    }
    
    // Customer hanya boleh lihat invoice miliknya
    if ($user['role'] !== 'admin') {
        $stmt = $pdo->prepare("SELECT CustomerID FROM Quotations WHERE QuotationID = ?");
        $stmt->execute([$invoice['QuotationID']]);
        $quotation = $stmt->fetch();
        if (!$quotation || $quotation['CustomerID'] != $user['id']) {
            http_response_code(403);
            echo json_encode(["error" => "Akses ditolak"]);
            exit;
        }
    }
    
    // Ambil items invoice
    $stmt = $pdo->prepare("SELECT * FROM Invoice_Items WHERE InvoiceID = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
    
    // Gabungkan data
    $invoice['items'] = $items;
    
    echo json_encode($invoice);
} else {
                // Ambil semua invoice
                if ($user['role'] === 'admin') {
                    // Admin bisa lihat semua invoice
                    $stmt = $pdo->query("SELECT i.*, q.QuotationCode, c.Name as CustomerName, i.Status as InvoiceStatus 
                                        FROM Invoices i 
                                        LEFT JOIN Quotations q ON i.QuotationID = q.QuotationID 
                                        LEFT JOIN Customers c ON q.CustomerID = c.CustomerID 
                                        ORDER BY i.InvoiceDate DESC");
                    echo json_encode($stmt->fetchAll());
                } else {
                    // Customer hanya lihat invoice miliknya
                    $stmt = $pdo->prepare("SELECT i.*, q.QuotationCode, i.Status as InvoiceStatus 
                                          FROM Invoices i 
                                          JOIN Quotations q ON i.QuotationID = q.QuotationID
                                          WHERE q.CustomerID = ?
                                          ORDER BY i.InvoiceDate DESC");
                    $stmt->execute([$user['id']]);
                    echo json_encode($stmt->fetchAll());
                }
            }
            break;
            
        case "POST":
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa membuat invoice"]);
                exit;
            }
            
            $quotationID = $data['QuotationID'] ?? null;
            if (!$quotationID) {
                http_response_code(400);
                echo json_encode(["error" => "QuotationID harus disediakan"]);
                exit;
            }
            
            // cek apakah quotation valid dan sudah Approved
            $stmt = $pdo->prepare("SELECT * FROM Quotations WHERE QuotationID = ? AND Status = 'Approved'");
            $stmt->execute([$quotationID]);
            $quotation = $stmt->fetch();
            
            if (!$quotation) {
                http_response_code(400);
                echo json_encode(["error" => "Quotation tidak valid atau belum Approved"]);
                exit;
            }
            
            // buat invoice
            $stmt = $pdo->prepare("INSERT INTO Invoices (QuotationID, InvoiceDate, TotalAmount, Status) VALUES (?, NOW(), ?, 'Unpaid')");
            $stmt->execute([$quotationID, $data['TotalAmount'] ?? 0]);
            $id = $pdo->lastInsertId();
            $code = generateCode("INV", $id);
            $stmt = $pdo->prepare("UPDATE Invoices SET InvoiceCode=? WHERE InvoiceID=?");
            $stmt->execute([$code, $id]);
            
            echo json_encode(["message" => "Invoice dibuat", "InvoiceID" => $id, "InvoiceCode" => $code]);
            break;
            
        case "PUT":
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa update invoice"]);
                exit;
            }
            
            $id = $_GET['id'] ?? ($data['InvoiceID'] ?? null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Invoice ID harus disediakan"]);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE Invoices SET Status=? WHERE InvoiceID=?");
            $stmt->execute([$data['Status'], $id]);
            echo json_encode(["message" => "Status invoice diperbarui"]);
            break;
            
        case "DELETE":
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa menghapus invoice"]);
                exit;
            }
            
            $id = $_GET['id'] ?? ($data['InvoiceID'] ?? null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Invoice ID harus disediakan"]);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM Invoices WHERE InvoiceID = ?");
            $stmt->execute([$id]);
            echo json_encode(["message" => "Invoice dihapus"]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(["error" => "Metode tidak didukung"]);
            break;
    }
} catch (PDOException $e) {
    // Tangani error database dengan lebih spesifik
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    // Tangani error umum
    http_response_code(500);
    echo json_encode(["error" => "Terjadi kesalahan: " . $e->getMessage()]);
}
?>