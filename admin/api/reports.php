<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../config/functions.php';

requireAuth();

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'sales';

switch ($action) {
    case 'sales':
        $period = isset($_GET['period']) ? sanitize($_GET['period']) : 'daily';
        $date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : date('Y-m-01');
        $date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : date('Y-m-d');
        
        $dateFormat = '%Y-%m-%d';
        
        if ($period === 'monthly') {
            $dateFormat = '%Y-%m';
        } elseif ($period === 'yearly') {
            $dateFormat = '%Y';
        }
        
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(created_at, '$dateFormat') as date,
                COUNT(*) as orders,
                COALESCE(SUM(subtotal), 0) as revenue,
                COALESCE(SUM(tax), 0) as tax,
                COALESCE(SUM(delivery_fee), 0) as delivery,
                COALESCE(SUM(total), 0) as total
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ? AND payment_status = 'paid'
            GROUP BY DATE_FORMAT(created_at, '$dateFormat')
            ORDER BY date ASC
        ");
        $stmt->execute([$date_from, $date_to]);
        $daily = $stmt->fetchAll();
        
        $summary = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'total_tax' => 0,
            'total_delivery' => 0,
            'total_amount' => 0,
            'avg_order_value' => 0
        ];
        
        foreach ($daily as $row) {
            $summary['total_orders'] += (int)$row['orders'];
            $summary['total_revenue'] += (float)$row['revenue'];
            $summary['total_tax'] += (float)$row['tax'];
            $summary['total_delivery'] += (float)$row['delivery'];
            $summary['total_amount'] += (float)$row['total'];
        }
        
        if ($summary['total_orders'] > 0) {
            $summary['avg_order_value'] = $summary['total_amount'] / $summary['total_orders'];
        }
        
        response([
            'success' => true,
            'data' => $daily,
            'summary' => $summary,
            'period' => $period,
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);
        break;
        
    case 'products':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : date('Y-m-01');
        $date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : date('Y-m-d');
        
        $stmt = $db->prepare("
            SELECT 
                p.id,
                p.name,
                p.price,
                p.image,
                c.name as category,
                SUM(oi.quantity) as units_sold,
                SUM(oi.subtotal) as revenue,
                COUNT(DISTINCT oi.order_id) as order_count
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            JOIN orders o ON oi.order_id = o.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY p.id
            ORDER BY revenue DESC
            LIMIT ?
        ");
        $stmt->execute([$date_from, $date_to, $limit]);
        $products = $stmt->fetchAll();
        
        $totalRevenue = 0;
        $totalUnits = 0;
        foreach ($products as $p) {
            $totalRevenue += (float)$p['revenue'];
            $totalUnits += (int)$p['units_sold'];
        }
        
        foreach ($products as &$p) {
            $p['percentage'] = $totalRevenue > 0 ? round(($p['revenue'] / $totalRevenue) * 100, 1) : 0;
        }
        
        response([
            'success' => true,
            'products' => $products,
            'total_revenue' => $totalRevenue,
            'total_units' => $totalUnits,
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);
        break;
        
    case 'customers':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $stmt = $db->prepare("
            SELECT 
                c.id,
                c.name,
                c.email,
                c.total_orders,
                c.total_spent,
                c.last_order_date,
                c.created_at
            FROM customers c
            WHERE c.is_blocked = 0
            ORDER BY c.total_spent DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $customers = $stmt->fetchAll();
        
        $totalRevenue = 0;
        $totalCustomers = count($customers);
        foreach ($customers as $c) {
            $totalRevenue += (float)$c['total_spent'];
        }
        
        response([
            'success' => true,
            'customers' => $customers,
            'total_revenue' => $totalRevenue,
            'total_customers' => $totalCustomers
        ]);
        break;
        
    case 'summary':
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $monthStart = date('Y-m-01');
        
        $stats = [];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM orders");
        $stats['total_orders'] = (int)$stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE payment_status = 'paid'");
        $stats['total_revenue'] = (float)$stmt->fetch()['revenue'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM customers");
        $stats['total_customers'] = (int)$stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
        $stats['total_products'] = (int)$stmt->fetch()['total'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue FROM orders WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $todayData = $stmt->fetch();
        $stats['today_orders'] = (int)$todayData['orders'];
        $stats['today_revenue'] = (float)$todayData['revenue'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$weekAgo, $today]);
        $weekData = $stmt->fetch();
        $stats['week_orders'] = (int)$weekData['orders'];
        $stats['week_revenue'] = (float)$weekData['revenue'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue FROM orders WHERE DATE(created_at) >= ?");
        $stmt->execute([$monthStart]);
        $monthData = $stmt->fetch();
        $stats['month_orders'] = (int)$monthData['orders'];
        $stats['month_revenue'] = (float)$monthData['revenue'];
        
        $stmt = $db->query("SELECT COALESCE(AVG(total), 0) as avg FROM orders WHERE payment_status = 'paid'");
        $stats['avg_order_value'] = (float)$stmt->fetch()['avg'];
        
        response(['success' => true, 'stats' => $stats]);
        break;
        
    case 'export':
        $type = isset($_GET['type']) ? sanitize($_GET['type']) : 'sales';
        $date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : date('Y-m-01');
        $date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : date('Y-m-d');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if ($type === 'sales') {
            fputcsv($output, ['Date', 'Orders', 'Revenue', 'Tax', 'Delivery', 'Total']);
            
            $stmt = $db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as orders,
                    COALESCE(SUM(subtotal), 0) as revenue,
                    COALESCE(SUM(tax), 0) as tax,
                    COALESCE(SUM(delivery_fee), 0) as delivery,
                    COALESCE(SUM(total), 0) as total
                FROM orders
                WHERE DATE(created_at) BETWEEN ? AND ? AND payment_status = 'paid'
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
            $stmt->execute([$date_from, $date_to]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, $row);
            }
        } elseif ($type === 'products') {
            fputcsv($output, ['Product', 'Category', 'Units Sold', 'Revenue', 'Orders']);
            
            $stmt = $db->prepare("
                SELECT 
                    p.name,
                    c.name as category,
                    SUM(oi.quantity) as units,
                    SUM(oi.subtotal) as revenue,
                    COUNT(DISTINCT oi.order_id) as orders
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                JOIN orders o ON oi.order_id = o.id
                WHERE DATE(o.created_at) BETWEEN ? AND ?
                GROUP BY p.id
                ORDER BY revenue DESC
            ");
            $stmt->execute([$date_from, $date_to]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
        
    default:
        response(['error' => 'Invalid action'], 400);
}
?>
