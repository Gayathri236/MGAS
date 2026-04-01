<?php
require_once __DIR__ . '/../includes/config.php';

requireLogin();

$conn = connectDB();

$adminId = $_SESSION['admin_id'];

$stats = [];

$stats['total_revenue'] = $conn->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE payment_status = 'paid'")->fetch_assoc()['total'];
$stats['total_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$stats['total_customers'] = $conn->query("SELECT COUNT(*) as count FROM customers WHERE status = 'active'")->fetch_assoc()['count'];
$stats['total_products'] = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1")->fetch_assoc()['count'];
$stats['pending_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$stats['low_stock_count'] = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= low_stock_threshold AND is_active = 1")->fetch_assoc()['count'];

$today = date('Y-m-d');
$weekAgo = date('Y-m-d', strtotime('-7 days'));
$monthAgo = date('Y-m-d', strtotime('-30 days'));

$stats['today_sales'] = $conn->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE DATE(created_at) = '$today' AND payment_status = 'paid'")->fetch_assoc()['total'];
$stats['week_sales'] = $conn->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE DATE(created_at) BETWEEN '$weekAgo' AND '$today' AND payment_status = 'paid'")->fetch_assoc()['total'];
$stats['month_sales'] = $conn->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE DATE(created_at) BETWEEN '$monthAgo' AND '$today' AND payment_status = 'paid'")->fetch_assoc()['total'];

$stats['today_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = '$today'")->fetch_assoc()['count'];
$stats['week_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) BETWEEN '$weekAgo' AND '$today'")->fetch_assoc()['count'];

$recentOrders = $conn->query("
    SELECT o.*, c.first_name, c.last_name, c.email 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");

$topProducts = $conn->query("
    SELECT p.id, p.name, p.image, p.price, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'cancelled' AND o.status != 'refunded'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");

$lowStockProducts = $conn->query("
    SELECT id, name, image, stock_quantity, low_stock_threshold, price
    FROM products
    WHERE stock_quantity <= low_stock_threshold AND is_active = 1
    ORDER BY stock_quantity ASC
    LIMIT 10
");

$salesData = $conn->query("
    SELECT DATE(created_at) as date, SUM(total) as sales, COUNT(*) as orders
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND payment_status = 'paid'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

$salesChart = [];
while ($row = $salesData->fetch_assoc()) {
    $salesChart[] = $row;
}

$orderStatus = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
");

$statusData = [];
while ($row = $orderStatus->fetch_assoc()) {
    $statusData[] = $row;
}

$recentOrdersArray = [];
while ($row = $recentOrders->fetch_assoc()) {
    $recentOrdersArray[] = $row;
}

$topProductsArray = [];
while ($row = $topProducts->fetch_assoc()) {
    $topProductsArray[] = $row;
}

$lowStockArray = [];
while ($row = $lowStockProducts->fetch_assoc()) {
    $lowStockArray[] = $row;
}

jsonResponse(true, 'Dashboard data loaded', [
    'stats' => $stats,
    'recent_orders' => $recentOrdersArray,
    'top_products' => $topProductsArray,
    'low_stock' => $lowStockArray,
    'sales_chart' => $salesChart,
    'order_status' => $statusData
]);
