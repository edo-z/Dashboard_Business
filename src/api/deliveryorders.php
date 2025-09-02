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
            $id = $_GET['id'] ?? ($data['DOID'] ?? null);
            if ($id) {
    // Ambil delivery order spesifik
    $stmt = $pdo->prepare("SELECT d.*, i.InvoiceCode, c.Name as CustomerName, d.Status as DOStatus 
                          FROM DeliveryOrders d 
                          LEFT JOIN Invoices i ON d.InvoiceID = i.InvoiceID 
                          LEFT JOIN Quotations q ON i.QuotationID = q.QuotationID 
                          LEFT JOIN Customers c ON q.CustomerID = c.CustomerID 
                          WHERE d.DOID = ?");
    $stmt->execute([$id]);
    $delivery = $stmt->fetch();
    
    if (!$delivery) {
        http_response_code(404);
        echo json_encode(["error" => "Delivery order tidak ditemukan"]);
        exit;
    }
    
    // Customer hanya boleh lihat delivery order miliknya
    if ($user['role'] !== 'admin') {
        $stmt = $pdo->prepare("SELECT CustomerID FROM Quotations q 
                              JOIN Invoices i ON q.QuotationID = i.QuotationID 
                              JOIN DeliveryOrders d ON i.InvoiceID = d.InvoiceID 
                              WHERE d.DOID = ?");
        $stmt->execute([$id]);
        $quotation = $stmt->fetch();
        if (!$quotation || $quotation['CustomerID'] != $user['id']) {
            http_response_code(403);
            echo json_encode(["error" => "Akses ditolak"]);
            exit;
        }
    }
    
    // Ambil items delivery order
    $stmt = $pdo->prepare("SELECT * FROM DO_Items WHERE DOID = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
    
    // Gabungkan data
    $delivery['items'] = $items;
    
    echo json_encode($delivery);
} else {
                // Ambil semua delivery orders
                if ($user['role'] === 'admin') {
                    // Admin bisa lihat semua delivery orders
                    $stmt = $pdo->query("SELECT d.*, i.InvoiceCode, c.Name as CustomerName, d.Status as DOStatus 
                                        FROM DeliveryOrders d 
                                        LEFT JOIN Invoices i ON d.InvoiceID = i.InvoiceID 
                                        LEFT JOIN Quotations q ON i.QuotationID = q.QuotationID 
                                        LEFT JOIN Customers c ON q.CustomerID = c.CustomerID 
                                        ORDER BY d.DODate DESC");
                    echo json_encode($stmt->fetchAll());
                } else {
                    // Customer hanya lihat delivery order miliknya
                    $stmt = $pdo->prepare("SELECT d.*, i.InvoiceCode, d.Status as DOStatus 
                                          FROM DeliveryOrders d 
                                          JOIN Invoices i ON d.InvoiceID = i.InvoiceID 
                                          JOIN Quotations q ON i.QuotationID = q.QuotationID 
                                          WHERE q.CustomerID = ?
                                          ORDER BY d.DODate DESC");
                    $stmt->execute([$user['id']]);
                    echo json_encode($stmt->fetchAll());
                }
            }
            break;
            
        case "POST":
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa membuat delivery order"]);
                exit;
            }
            
            $invoiceID = $data['InvoiceID'] ?? null;
            if (!$invoiceID) {
                http_response_code(400);
                echo json_encode(["error" => "InvoiceID harus disediakan"]);
                exit;
            }
            
            // cek apakah invoice valid
            $stmt = $pdo->prepare("SELECT * FROM Invoices WHERE InvoiceID = ?");
            $stmt->execute([$invoiceID]);
            $invoice = $stmt->fetch();
            
            if (!$invoice) {
                http_response_code(400);
                echo json_encode(["error" => "Invoice tidak valid"]);
                exit;
            }
            
            // buat delivery order
            $stmt = $pdo->prepare("INSERT INTO DeliveryOrders (InvoiceID, DODate, Status) VALUES (?, NOW(), 'Not Shipped')");
            $stmt->execute([$invoiceID]);
            $id = $pdo->lastInsertId();
            $code = generateCode("DO", $id);
            $stmt = $pdo->prepare("UPDATE DeliveryOrders SET DOCode=? WHERE DOID=?");
            $stmt->execute([$code, $id]);
            
            echo json_encode(["message" => "Delivery order dibuat", "DOID" => $id, "DOCode" => $code]);
            break;
            
        case "PUT":
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa update delivery order"]);
                exit;
            }
            
            $id = $_GET['id'] ?? ($data['DOID'] ?? null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Delivery order ID harus disediakan"]);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE DeliveryOrders SET Status=?, Notes=? WHERE DOID=?");
            $stmt->execute([$data['Status'], $data['Notes'] ?? null, $id]);
            echo json_encode(["message" => "Status delivery order diperbarui"]);
            break;
            
        case "DELETE":
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa menghapus delivery order"]);
                exit;
            }
            
            $id = $_GET['id'] ?? ($data['DOID'] ?? null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Delivery order ID harus disediakan"]);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM DeliveryOrders WHERE DOID = ?");
            $stmt->execute([$id]);
            echo json_encode(["message" => "Delivery order dihapus"]);
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