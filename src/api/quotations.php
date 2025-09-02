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
            $id = $_GET['id'] ?? ($data['QuotationID'] ?? null);
            if ($id) {
    // Ambil quotation spesifik
    $stmt = $pdo->prepare("SELECT q.*, c.Name as CustomerName, q.Status as QuotationStatus 
                          FROM Quotations q 
                          LEFT JOIN Customers c ON q.CustomerID = c.CustomerID 
                          WHERE q.QuotationID = ?");
    $stmt->execute([$id]);
    $quotation = $stmt->fetch();
    
    if (!$quotation) {
        http_response_code(404);
        echo json_encode(["error" => "Quotation tidak ditemukan"]);
        exit;
    }
    
    // Customer hanya boleh lihat quotation miliknya
    if ($user['role'] !== 'admin' && $quotation['CustomerID'] != $user['id']) {
        http_response_code(403);
        echo json_encode(["error" => "Akses ditolak"]);
        exit;
    }
    
    // Ambil items quotation
    $stmt = $pdo->prepare("SELECT * FROM Quotation_Items WHERE QuotationID = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
    
    // Gabungkan data
    $quotation['items'] = $items;
    
    echo json_encode($quotation);
} else {
                // Ambil semua quotations
                if ($user['role'] === 'admin') {
                    // Admin bisa lihat semua quotations
                    $stmt = $pdo->query("SELECT q.*, c.Name as CustomerName, q.Status as QuotationStatus 
                                        FROM Quotations q 
                                        LEFT JOIN Customers c ON q.CustomerID = c.CustomerID 
                                        ORDER BY q.QuotationDate DESC");
                    echo json_encode($stmt->fetchAll());
                } else {
                    // Customer hanya lihat quotation miliknya
                    $stmt = $pdo->prepare("SELECT q.*, q.Status as QuotationStatus 
                                          FROM Quotations q 
                                          WHERE q.CustomerID = ?
                                          ORDER BY q.QuotationDate DESC");
                    $stmt->execute([$user['id']]);
                    echo json_encode($stmt->fetchAll());
                }
            }
            break;
            
        case "POST":
            $customerID = $user['role'] === 'admin' ? ($data['CustomerID'] ?? null) : $user['id'];
            if (!$customerID) {
                http_response_code(400);
                echo json_encode(["error" => "CustomerID harus disediakan"]);
                exit;
            }
            
            // buat quotation
            $stmt = $pdo->prepare("INSERT INTO Quotations (CustomerID, QuotationDate, Status, Notes) VALUES (?, NOW(), 'Draft', ?)");
            $stmt->execute([$customerID, $data['Notes'] ?? null]);
            $id = $pdo->lastInsertId();
            $code = generateCode("QTN", $id);
            $stmt = $pdo->prepare("UPDATE Quotations SET QuotationCode=? WHERE QuotationID=?");
            $stmt->execute([$code, $id]);
            
            echo json_encode(["message" => "Quotation dibuat", "QuotationID" => $id, "QuotationCode" => $code]);
            break;
            
        case "PUT":
            $id = $_GET['id'] ?? ($data['QuotationID'] ?? null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Quotation ID harus disediakan"]);
                exit;
            }
            
            // Customer hanya bisa update quotation miliknya sendiri
            if ($user['role'] !== 'admin') {
                $stmt = $pdo->prepare("SELECT CustomerID FROM Quotations WHERE QuotationID = ?");
                $stmt->execute([$id]);
                $quotation = $stmt->fetch();
                if (!$quotation || $quotation['CustomerID'] != $user['id']) {
                    http_response_code(403);
                    echo json_encode(["error" => "Akses ditolak"]);
                    exit;
                }
            }
            
            $updateFields = [];
            $params = [];
            
            if (isset($data['Status'])) {
                $updateFields[] = "Status = ?";
                $params[] = $data['Status'];
            }
            
            if (isset($data['Notes'])) {
                $updateFields[] = "Notes = ?";
                $params[] = $data['Notes'];
            }
            
            if (empty($updateFields)) {
                http_response_code(400);
                echo json_encode(["error" => "Tidak ada data yang diupdate"]);
                exit;
            }
            
            $params[] = $id;
            $query = "UPDATE Quotations SET " . implode(', ', $updateFields) . " WHERE QuotationID = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            
            echo json_encode(["message" => "Quotation diperbarui"]);
            break;
            
        case "DELETE":
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(["error" => "Hanya admin bisa menghapus quotation"]);
                exit;
            }
            
            $id = $_GET['id'] ?? ($data['QuotationID'] ?? null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Quotation ID harus disediakan"]);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM Quotations WHERE QuotationID = ?");
            $stmt->execute([$id]);
            echo json_encode(["message" => "Quotation dihapus"]);
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