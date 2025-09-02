<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "../config/security.php"; // 🔒 cek X-API-KEY
require_once "auth.php";               // 🔑 cek JWT
require_once "../config/db.php";

$user = authenticate(); // ambil info user dari JWT
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== "GET") {
        http_response_code(405);
        echo json_encode(["error" => "Metode tidak didukung"]);
        exit;
    }

    // Ambil data customer
    $stmt = $pdo->prepare("SELECT * FROM Customers WHERE CustomerID = ?");
    $stmt->execute([$user['id']]);
    $customer = $stmt->fetch();

    if (!$customer) {
        http_response_code(404);
        echo json_encode(["error" => "Customer tidak ditemukan"]);
        exit;
    }

    // Ambil data quotations customer
    $stmt = $pdo->prepare("SELECT *, Status as QuotationStatus FROM Quotations WHERE CustomerID = ? ORDER BY QuotationDate DESC LIMIT 5");
    $stmt->execute([$user['id']]);
    $quotations = $stmt->fetchAll();

    // Ambil data invoices customer
    $stmt = $pdo->prepare("SELECT i.*, q.QuotationCode, i.Status as InvoiceStatus FROM Invoices i 
                           JOIN Quotations q ON i.QuotationID = q.QuotationID 
                           WHERE q.CustomerID = ? ORDER BY i.InvoiceDate DESC LIMIT 5");
    $stmt->execute([$user['id']]);
    $invoices = $stmt->fetchAll();

    // Ambil data delivery orders customer
    $stmt = $pdo->prepare("SELECT d.*, i.InvoiceCode, d.Status as DOStatus FROM DeliveryOrders d 
                           JOIN Invoices i ON d.InvoiceID = i.InvoiceID 
                           JOIN Quotations q ON i.QuotationID = q.QuotationID 
                           WHERE q.CustomerID = ? ORDER BY d.DODate DESC LIMIT 5");
    $stmt->execute([$user['id']]);
    $deliveries = $stmt->fetchAll();

    // Hitung statistik
    $stmt = $pdo->prepare("SELECT 
                            COUNT(*) as total_quotations,
                            SUM(CASE WHEN Status = 'Approved' THEN 1 ELSE 0 END) as approved_quotations
                            FROM Quotations WHERE CustomerID = ?");
    $stmt->execute([$user['id']]);
    $quotationStats = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT 
                            COUNT(*) as total_invoices,
                            SUM(CASE WHEN i.Status = 'Paid' THEN 1 ELSE 0 END) as paid_invoices
                            FROM Invoices i
                            JOIN Quotations q ON i.QuotationID = q.QuotationID
                            WHERE q.CustomerID = ?");
    $stmt->execute([$user['id']]);
    $invoiceStats = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT 
                            COUNT(*) as total_deliveries,
                            SUM(CASE WHEN d.Status = 'Delivered' THEN 1 ELSE 0 END) as delivered_orders
                            FROM DeliveryOrders d
                            JOIN Invoices i ON d.InvoiceID = i.InvoiceID
                            JOIN Quotations q ON i.QuotationID = q.QuotationID
                            WHERE q.CustomerID = ?");
    $stmt->execute([$user['id']]);
    $deliveryStats = $stmt->fetch();

    // Gabungkan semua data
    $profileData = array_merge($customer, [
        'quotations' => $quotations,
        'invoices' => $invoices,
        'deliveries' => $deliveries,
        'stats' => [
            'quotations' => $quotationStats,
            'invoices' => $invoiceStats,
            'deliveries' => $deliveryStats
        ]
    ]);

    echo json_encode($profileData);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Terjadi kesalahan: " . $e->getMessage()]);
}
?>