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

$action = $_GET['action'] ?? 'stats';

switch ($action) {
    case 'stats':
        $today = date('Y-m-d');
        $startOfWeek = date('Y-m-d', strtotime('-' . (date('N') - 1) . ' days'));
        $startOfMonth = date('Y-m-01');
        
        $stats = [];
        
        $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as today_sales FROM orders WHERE DATE(created_at) = ? AND payment_status = 'paid'");
        $stmt->execute([$today]);
        $stats['today_sales'] = (float)$stmt->fetch()['today_sales'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as today_orders FROM orders WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $stats['today_orders'] = (int)$stmt->fetch()['today_orders'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as total_customers FROM customers WHERE is_blocked = 0");
        $stmt->execute();
        $stats['total_customers'] = (int)$stmt->fetch()['total_customers'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity <= low_stock_threshold AND is_active = 1");
        $stmt->execute();
        $stats['low_stock'] = (int)$stmt->fetch()['low_stock'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
        $stmt->execute();
        $stats['pending_orders'] = (int)$stmt->fetch()['pending_orders'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as total_products FROM products WHERE is_active = 1");
        $stmt->execute();
        $stats['total_products'] = (int)$stmt->fetch()['total_products'];
        
        $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as weekly_sales FROM orders WHERE DATE(created_at) >= ? AND payment_status = 'paid'");
        $stmt->execute([$startOfWeek]);
        $stats['weekly_sales'] = (float)$stmt->fetch()['weekly_sales'];
        
        $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as monthly_sales FROM orders WHERE DATE(created_at) >= ? AND payment_status = 'paid'");
        $stmt->execute([$startOfMonth]);
        $stats['monthly_sales'] = (float)$stmt->fetch()['monthly_sales'];
        
        response(['success' => true, 'stats' => $stats]);
        break;
        
    case 'recent_orders':
        $stmt = $db->prepare("
            SELECT o.*, c.name as customer_name, c.email as customer_email 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.id 
            ORDER BY o.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll();
        
        response(['success' => true, 'orders' => $orders]);
        break;
        
    case 'sales_chart':
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, COALESCE(SUM(total), 0) as sales 
            FROM orders 
            WHERE DATE(created_at) >= ? AND payment_status = 'paid'
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$startDate]);
        $salesData = $stmt->fetchAll();
        
        $chartData = [];
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartData[$date] = 0;
        }
        
        foreach ($salesData as $row) {
            $chartData[$row['date']] = (float)$row['sales'];
        }
        
        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M d', strtotime($date));
            $data[] = $chartData[$date];
        }
        
        response(['success' => true, 'labels' => $labels, 'data' => $data]);
        break;
        
    case 'top_products':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.image, p.price, 
                   SUM(oi.quantity) as total_sold,
                   SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $products = $stmt->fetchAll();
        
        response(['success' => true, 'products' => $products]);
        break;
        
    case 'low_stock_alerts':
        $stmt = $db->prepare("
            SELECT id, name, stock_quantity, low_stock_threshold, price, image
            FROM products 
            WHERE stock_quantity <= low_stock_threshold AND is_active = 1
            ORDER BY stock_quantity ASC
        ");
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        response(['success' => true, 'products' => $products]);
        break;
        
    default:
        response(['error' => 'Invalid action'], 400);
}
?>
