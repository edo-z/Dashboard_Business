<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "../config/security.php";
require_once "auth.php";
require_once "../config/db.php";

$user = $_SESSION['user'];
try {
    $summary = [];
    
    if ($user['role'] === 'admin') {
        // ✅ Admin: lihat semua data
        
        // 1. Counts data
        $summary['counts'] = [
            "customers"      => $pdo->query("SELECT COUNT(*) FROM Customers")->fetchColumn(),
            "quotations"     => $pdo->query("SELECT COUNT(*) FROM Quotations WHERE QuotationDate IS NOT NULL")->fetchColumn(),
            "invoices"       => $pdo->query("SELECT COUNT(*) FROM Invoices WHERE InvoiceDate IS NOT NULL")->fetchColumn(),
            "deliveryorders" => $pdo->query("SELECT COUNT(*) FROM DeliveryOrders WHERE DODate IS NOT NULL")->fetchColumn()
        ];
        error_log("Admin Counts: " . print_r($summary['counts'], true));
        
        // 2. Monthly statistics
        $monthlyQuotations = [];
        $monthlyInvoices = [];
        $monthLabels = [];
        $firstDate = null;
        $lastDate = null;
        
        // Cek apakah ada quotations
        $quotationCount = $pdo->query("SELECT COUNT(*) FROM Quotations WHERE QuotationDate IS NOT NULL")->fetchColumn();
        error_log("Admin Quotation count (non-null): $quotationCount");
        
        if ($quotationCount > 0) {
            // Cari tanggal terawal dan terakhir quotations
            $firstQuotation = $pdo->query("SELECT MIN(QuotationDate) as first_date FROM Quotations WHERE QuotationDate IS NOT NULL")->fetchColumn();
            $lastQuotation = $pdo->query("SELECT MAX(QuotationDate) as last_date FROM Quotations WHERE QuotationDate IS NOT NULL")->fetchColumn();
            
            error_log("Admin Quotation first date: " . ($firstQuotation ?? 'NULL'));
            error_log("Admin Quotation last date: " . ($lastQuotation ?? 'NULL'));
            
            // Coba parsing tanggal
            $current = $firstQuotation ? DateTime::createFromFormat('Y-m-d', $firstQuotation) : false;
            $end = $lastQuotation ? DateTime::createFromFormat('Y-m-d', $lastQuotation) : false;
            
            if ($current && $end) {
                $firstDate = $firstQuotation;
                $lastDate = $lastQuotation;
                error_log("Admin Starting loop from " . $current->format('Y-m-d') . " to " . $end->format('Y-m-d'));
                
                while ($current <= $end) {
                    $month = $current->format('m');
                    $year = $current->format('Y');
                    $monthName = $current->format('M Y');
                    
                    // Get quotations count
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Quotations WHERE MONTH(QuotationDate) = ? AND YEAR(QuotationDate) = ? AND QuotationDate IS NOT NULL");
                    $stmt->execute([$month, $year]);
                    $count = $stmt->fetchColumn();
                    $monthlyQuotations[] = $count;
                    error_log("Admin Month $monthName: Quotations = $count");
                    
                    // Get invoices count
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Invoices WHERE MONTH(InvoiceDate) = ? AND YEAR(InvoiceDate) = ? AND InvoiceDate IS NOT NULL");
                    $stmt->execute([$month, $year]);
                    $count = $stmt->fetchColumn();
                    $monthlyInvoices[] = $count;
                    error_log("Admin Month $monthName: Invoices = $count");
                    
                    $monthLabels[] = $monthName;
                    $current->modify('+1 month');
                }
            } else {
                error_log("Admin Invalid date format for quotations: first=$firstQuotation, last=$lastQuotation");
            }
        }
        
        // Jika tidak ada quotations, cek invoices
        if (empty($monthLabels)) {
            $invoiceCount = $pdo->query("SELECT COUNT(*) FROM Invoices WHERE InvoiceDate IS NOT NULL")->fetchColumn();
            error_log("Admin Invoice count (non-null): $invoiceCount");
            
            if ($invoiceCount > 0) {
                $firstInvoice = $pdo->query("SELECT MIN(InvoiceDate) as first_date FROM Invoices WHERE InvoiceDate IS NOT NULL")->fetchColumn();
                $lastInvoice = $pdo->query("SELECT MAX(InvoiceDate) as last_date FROM Invoices WHERE InvoiceDate IS NOT NULL")->fetchColumn();
                
                error_log("Admin Invoice first date: " . ($firstInvoice ?? 'NULL'));
                error_log("Admin Invoice last date: " . ($lastInvoice ?? 'NULL'));
                
                $current = $firstInvoice ? DateTime::createFromFormat('Y-m-d', $firstInvoice) : false;
                $end = $lastInvoice ? DateTime::createFromFormat('Y-m-d', $lastInvoice) : false;
                
                if ($current && $end) {
                    $firstDate = $firstInvoice;
                    $lastDate = $lastInvoice;
                    error_log("Admin Starting invoice loop from " . $current->format('Y-m-d') . " to " . $end->format('Y-m-d'));
                    
                    while ($current <= $end) {
                        $month = $current->format('m');
                        $year = $current->format('Y');
                        $monthName = $current->format('M Y');
                        
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Quotations WHERE MONTH(QuotationDate) = ? AND YEAR(QuotationDate) = ? AND QuotationDate IS NOT NULL");
                        $stmt->execute([$month, $year]);
                        $count = $stmt->fetchColumn();
                        $monthlyQuotations[] = $count;
                        
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Invoices WHERE MONTH(InvoiceDate) = ? AND YEAR(InvoiceDate) = ? AND InvoiceDate IS NOT NULL");
                        $stmt->execute([$month, $year]);
                        $count = $stmt->fetchColumn();
                        $monthlyInvoices[] = $count;
                        
                        $monthLabels[] = $monthName;
                        $current->modify('+1 month');
                    }
                } else {
                    error_log("Admin Invalid date format for invoices: first=$firstInvoice, last=$lastInvoice");
                }
            }
        }
        
        // Jika masih kosong, gunakan default 12 bulan terakhir
        if (empty($monthLabels)) {
            error_log("Admin No data found, using default 12 months");
            for ($i = 11; $i >= 0; $i--) {
                $monthName = date('M Y', strtotime("-$i months"));
                $monthLabels[] = $monthName;
                $monthlyQuotations[] = 0;
                $monthlyInvoices[] = 0;
            }
            $firstDate = date('Y-m-d', strtotime('-11 months'));
            $lastDate = date('Y-m-d');
        }
        
        $summary['monthly_statistics'] = [
            'labels' => $monthLabels,
            'quotations' => $monthlyQuotations,
            'invoices' => $monthlyInvoices,
            'date_range' => [
                'first_date' => $firstDate,
                'last_date' => $lastDate
            ]
        ];
        error_log("Admin Final monthly statistics: " . print_r($summary['monthly_statistics'], true));
        
        // 3. Status distribution
        $statusData = $pdo->query("SELECT Status, COUNT(*) as count FROM Quotations WHERE QuotationDate IS NOT NULL GROUP BY Status")->fetchAll(PDO::FETCH_KEY_PAIR);
        error_log("Admin Status data: " . print_r($statusData, true));
        
        $summary['status_distribution'] = [
            'labels' => array_keys($statusData ?: ['No Data' => 0]),
            'values' => array_values($statusData ?: [0])
        ];
        
        // 4. Latest activities
        $summary['latest_quotations'] = $pdo->query("SELECT QuotationID, QuotationDate, Status FROM Quotations WHERE QuotationDate IS NOT NULL ORDER BY QuotationDate DESC LIMIT 5")->fetchAll();
        $summary['latest_invoices'] = $pdo->query("SELECT InvoiceID, InvoiceDate, Status FROM Invoices WHERE InvoiceDate IS NOT NULL ORDER BY InvoiceDate DESC LIMIT 5")->fetchAll();
        $summary['latest_deliveries'] = $pdo->query("SELECT DOID, DODate, Status FROM DeliveryOrders WHERE DODate IS NOT NULL ORDER BY DODate DESC LIMIT 5")->fetchAll();
        error_log("Admin Latest quotations: " . print_r($summary['latest_quotations'], true));
        
    } else {
        // ✅ Customer: lihat data miliknya
        $uid = $user['id'];
        error_log("Customer ID: $uid");
        
        // 1. Counts data
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Customers WHERE CustomerID = ?");
        $stmt->execute([$uid]);
        $counts['customers'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Quotations WHERE CustomerID = ? AND QuotationDate IS NOT NULL");
        $stmt->execute([$uid]);
        $counts['quotations'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Invoices i JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND i.InvoiceDate IS NOT NULL");
        $stmt->execute([$uid]);
        $counts['invoices'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM DeliveryOrders d JOIN Invoices i ON d.InvoiceID = i.InvoiceID JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND d.DODate IS NOT NULL");
        $stmt->execute([$uid]);
        $counts['deliveryorders'] = $stmt->fetchColumn();
        
        $summary['counts'] = $counts;
        error_log("Customer $uid Counts: " . print_r($counts, true));
        
        // 2. Monthly statistics
        $monthlyQuotations = [];
        $monthlyInvoices = [];
        $monthLabels = [];
        $firstDate = null;
        $lastDate = null;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Quotations WHERE CustomerID = ? AND QuotationDate IS NOT NULL");
        $stmt->execute([$uid]);
        $quotationCount = $stmt->fetchColumn();
        error_log("Customer $uid Quotation count (non-null): $quotationCount");
        
        if ($quotationCount > 0) {
            $stmt = $pdo->prepare("SELECT MIN(QuotationDate) as first_date FROM Quotations WHERE CustomerID = ? AND QuotationDate IS NOT NULL");
            $stmt->execute([$uid]);
            $firstQuotation = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT MAX(QuotationDate) as last_date FROM Quotations WHERE CustomerID = ? AND QuotationDate IS NOT NULL");
            $stmt->execute([$uid]);
            $lastQuotation = $stmt->fetchColumn();
            
            error_log("Customer $uid Quotation first date: " . ($firstQuotation ?? 'NULL'));
            error_log("Customer $uid Quotation last date: " . ($lastQuotation ?? 'NULL'));
            
            $current = $firstQuotation ? DateTime::createFromFormat('Y-m-d', $firstQuotation) : false;
            $end = $lastQuotation ? DateTime::createFromFormat('Y-m-d', $lastQuotation) : false;
            
            if ($current && $end) {
                $firstDate = $firstQuotation;
                $lastDate = $lastQuotation;
                error_log("Customer $uid Starting loop from " . $current->format('Y-m-d') . " to " . $end->format('Y-m-d'));
                
                while ($current <= $end) {
                    $month = $current->format('m');
                    $year = $current->format('Y');
                    $monthName = $current->format('M Y');
                    
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Quotations WHERE CustomerID = ? AND MONTH(QuotationDate) = ? AND YEAR(QuotationDate) = ? AND QuotationDate IS NOT NULL");
                    $stmt->execute([$uid, $month, $year]);
                    $count = $stmt->fetchColumn();
                    $monthlyQuotations[] = $count;
                    error_log("Customer $uid Month $monthName: Quotations = $count");
                    
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Invoices i JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND MONTH(i.InvoiceDate) = ? AND YEAR(i.InvoiceDate) = ? AND i.InvoiceDate IS NOT NULL");
                    $stmt->execute([$uid, $month, $year]);
                    $count = $stmt->fetchColumn();
                    $monthlyInvoices[] = $count;
                    error_log("Customer $uid Month $monthName: Invoices = $count");
                    
                    $monthLabels[] = $monthName;
                    $current->modify('+1 month');
                }
            } else {
                error_log("Customer $uid Invalid date format for quotations: first=$firstQuotation, last=$lastQuotation");
            }
        }
        
        // Jika tidak ada quotations, cek invoices
        if (empty($monthLabels)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Invoices i JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND i.InvoiceDate IS NOT NULL");
            $stmt->execute([$uid]);
            $invoiceCount = $stmt->fetchColumn();
            error_log("Customer $uid Invoice count (non-null): $invoiceCount");
            
            if ($invoiceCount > 0) {
                $stmt = $pdo->prepare("SELECT MIN(i.InvoiceDate) as first_date FROM Invoices i JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND i.InvoiceDate IS NOT NULL");
                $stmt->execute([$uid]);
                $firstInvoice = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("SELECT MAX(i.InvoiceDate) as last_date FROM Invoices i JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND i.InvoiceDate IS NOT NULL");
                $stmt->execute([$uid]);
                $lastInvoice = $stmt->fetchColumn();
                
                error_log("Customer $uid Invoice first date: " . ($firstInvoice ?? 'NULL'));
                error_log("Customer $uid Invoice last date: " . ($lastInvoice ?? 'NULL'));
                
                $current = $firstInvoice ? DateTime::createFromFormat('Y-m-d', $firstInvoice) : false;
                $end = $lastInvoice ? DateTime::createFromFormat('Y-m-d', $lastInvoice) : false;
                
                if ($current && $end) {
                    $firstDate = $firstInvoice;
                    $lastDate = $lastInvoice;
                    error_log("Customer $uid Starting invoice loop from " . $current->format('Y-m-d') . " to " . $end->format('Y-m-d'));
                    
                    while ($current <= $end) {
                        $month = $current->format('m');
                        $year = $current->format('Y');
                        $monthName = $current->format('M Y');
                        
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Quotations WHERE CustomerID = ? AND MONTH(QuotationDate) = ? AND YEAR(QuotationDate) = ? AND QuotationDate IS NOT NULL");
                        $stmt->execute([$uid, $month, $year]);
                        $count = $stmt->fetchColumn();
                        $monthlyQuotations[] = $count;
                        
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Invoices i JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND MONTH(i.InvoiceDate) = ? AND YEAR(i.InvoiceDate) = ? AND i.InvoiceDate IS NOT NULL");
                        $stmt->execute([$uid, $month, $year]);
                        $count = $stmt->fetchColumn();
                        $monthlyInvoices[] = $count;
                        
                        $monthLabels[] = $monthName;
                        $current->modify('+1 month');
                    }
                } else {
                    error_log("Customer $uid Invalid date format for invoices: first=$firstInvoice, last=$lastInvoice");
                }
            }
        }
        
        // Jika masih kosong, gunakan default 12 bulan terakhir
        if (empty($monthLabels)) {
            error_log("Customer $uid No data found, using default 12 months");
            for ($i = 11; $i >= 0; $i--) {
                $monthName = date('M Y', strtotime("-$i months"));
                $monthLabels[] = $monthName;
                $monthlyQuotations[] = 0;
                $monthlyInvoices[] = 0;
            }
            $firstDate = date('Y-m-d', strtotime('-11 months'));
            $lastDate = date('Y-m-d');
        }
        
        $summary['monthly_statistics'] = [
            'labels' => $monthLabels,
            'quotations' => $monthlyQuotations,
            'invoices' => $monthlyInvoices,
            'date_range' => [
                'first_date' => $firstDate,
                'last_date' => $lastDate
            ]
        ];
        error_log("Customer $uid Final monthly statistics: " . print_r($summary['monthly_statistics'], true));
        
        // 3. Status distribution
        $stmt = $pdo->prepare("SELECT Status, COUNT(*) as count FROM Quotations WHERE CustomerID = ? AND QuotationDate IS NOT NULL GROUP BY Status");
        $stmt->execute([$uid]);
        $statusData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        error_log("Customer $uid Status data: " . print_r($statusData, true));
        
        $summary['status_distribution'] = [
            'labels' => array_keys($statusData ?: ['No Data' => 0]),
            'values' => array_values($statusData ?: [0])
        ];
        
        // 4. Latest activities
        $stmt = $pdo->prepare("SELECT QuotationID, QuotationDate, Status FROM Quotations WHERE CustomerID = ? AND QuotationDate IS NOT NULL ORDER BY QuotationDate DESC LIMIT 5");
        $stmt->execute([$uid]);
        $summary['latest_quotations'] = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT i.InvoiceID, i.InvoiceDate, i.Status FROM Invoices i JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND i.InvoiceDate IS NOT NULL ORDER BY i.InvoiceDate DESC LIMIT 5");
        $stmt->execute([$uid]);
        $summary['latest_invoices'] = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT d.DOID, d.DODate, d.Status FROM DeliveryOrders d JOIN Invoices i ON d.InvoiceID = i.InvoiceID JOIN Quotations q ON i.QuotationID = q.QuotationID WHERE q.CustomerID = ? AND d.DODate IS NOT NULL ORDER BY d.DODate DESC LIMIT 5");
        $stmt->execute([$uid]);
        $summary['latest_deliveries'] = $stmt->fetchAll();
        error_log("Customer $uid Latest quotations: " . print_r($summary['latest_quotations'], true));
    }
    
    // Debug: Tambahkan info debug
    $summary['debug'] = [
        'user_role' => $user['role'],
        'user_id' => $user['id'] ?? null,
        'timestamp' => date('Y-m-d H:i:s'),
        'data_range' => $firstDate . ' to ' . $lastDate,
        'total_months' => count($monthLabels),
        'monthly_quotations_count' => count($monthlyQuotations),
        'monthly_invoices_count' => count($monthlyInvoices)
    ];
    error_log("Final summary for {$user['role']}: " . print_r($summary, true));
    
    echo json_encode($summary);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in summary.php: " . $e->getMessage());
    echo json_encode([
        "error" => "Gagal mengambil summary",
        "details" => $e->getMessage()
    ]);
}
?>