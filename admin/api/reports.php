<?php
require_once __DIR__ . '/../includes/config.php';

requireLogin();

$conn = connectDB();
$action = sanitize($_GET['action'] ?? 'sales_overview');

switch ($action) {
    case 'sales_overview':
        getSalesOverview($conn);
        break;
    case 'top_products':
        getTopProducts($conn);
        break;
    case 'top_customers':
        getTopCustomers($conn);
        break;
    case 'monthly_report':
        getMonthlyReport($conn);
        break;
    case 'inventory_report':
        getInventoryReport($conn);
        break;
    case 'export':
        exportReport($conn);
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function getSalesOverview($conn) {
    $start_date = sanitize($_GET['start_date'] ?? date('Y-m-01'));
    $end_date = sanitize($_GET['end_date'] ?? date('Y-m-d'));
    
    $summarySql = "SELECT 
        COUNT(*) as total_orders,
        SUM(total) as total_sales,
        AVG(total) as avg_order_value,
        SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as paid_amount,
        SUM(CASE WHEN payment_status = 'pending' THEN total ELSE 0 END) as pending_amount
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($summarySql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $dailySalesSql = "SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total) as sales
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ? AND payment_status = 'paid'
        GROUP BY DATE(created_at)
        ORDER BY date ASC";
    
    $stmt = $conn->prepare($dailySalesSql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $dailySales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $statusBreakdownSql = "SELECT 
        status,
        COUNT(*) as count,
        SUM(total) as total
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY status";
    
    $stmt = $conn->prepare($statusBreakdownSql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $statusBreakdown = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    jsonResponse(true, 'Sales overview loaded', [
        'summary' => $summary,
        'daily_sales' => $dailySales,
        'status_breakdown' => $statusBreakdown,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
}

function getTopProducts($conn) {
    $limit = (int)($_GET['limit'] ?? 10);
    $start_date = sanitize($_GET['start_date'] ?? date('Y-m-01'));
    $end_date = sanitize($_GET['end_date'] ?? date('Y-m-d'));
    
    $sql = "SELECT 
        p.id,
        p.name,
        p.image,
        p.price,
        p.sku,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.subtotal) as total_revenue,
        COUNT(DISTINCT oi.order_id) as order_count
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE DATE(o.created_at) BETWEEN ? AND ?
        AND o.status NOT IN ('cancelled', 'refunded')
        GROUP BY p.id
        ORDER BY total_quantity DESC
        LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $start_date, $end_date, $limit);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $totalRevenue = array_sum(array_column($products, 'total_revenue'));
    
    foreach ($products as &$product) {
        $product['percentage'] = $totalRevenue > 0 ? round(($product['total_revenue'] / $totalRevenue) * 100, 1) : 0;
    }
    
    jsonResponse(true, 'Top products loaded', [
        'products' => $products,
        'total_revenue' => $totalRevenue
    ]);
}

function getTopCustomers($conn) {
    $limit = (int)($_GET['limit'] ?? 10);
    
    $sql = "SELECT 
        c.id,
        c.first_name,
        c.last_name,
        c.email,
        c.phone,
        COUNT(o.id) as total_orders,
        SUM(o.total) as total_spent,
        MAX(o.created_at) as last_order_date
        FROM customers c
        LEFT JOIN orders o ON c.id = o.customer_id
        WHERE o.status NOT IN ('cancelled', 'refunded')
        GROUP BY c.id
        HAVING total_orders > 0
        ORDER BY total_spent DESC
        LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    jsonResponse(true, 'Top customers loaded', $customers);
}

function getMonthlyReport($conn) {
    $year = (int)($_GET['year'] ?? date('Y'));
    
    $monthlySql = "SELECT 
        MONTH(created_at) as month,
        COUNT(*) as orders,
        SUM(total) as revenue
        FROM orders
        WHERE YEAR(created_at) = ? AND payment_status = 'paid'
        GROUP BY MONTH(created_at)
        ORDER BY month ASC";
    
    $stmt = $conn->prepare($monthlySql);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $monthlyData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $months = [];
    for ($i = 1; $i <= 12; $i++) {
        $found = array_filter($monthlyData, fn($m) => $m['month'] == $i);
        if ($found) {
            $data = array_values($found)[0];
            $months[] = [
                'month' => $i,
                'month_name' => date('M', mktime(0, 0, 0, $i, 1)),
                'orders' => $data['orders'],
                'revenue' => $data['revenue']
            ];
        } else {
            $months[] = [
                'month' => $i,
                'month_name' => date('M', mktime(0, 0, 0, $i, 1)),
                'orders' => 0,
                'revenue' => 0
            ];
        }
    }
    
    $totalRevenue = array_sum(array_column($monthlyData, 'revenue'));
    $totalOrders = array_sum(array_column($monthlyData, 'orders'));
    
    jsonResponse(true, 'Monthly report loaded', [
        'year' => $year,
        'months' => $months,
        'total_revenue' => $totalRevenue,
        'total_orders' => $totalOrders,
        'avg_monthly' => $totalRevenue / 12
    ]);
}

function getInventoryReport($conn) {
    $sql = "SELECT 
        p.name,
        p.sku,
        c.name as category,
        p.stock_quantity,
        p.low_stock_threshold,
        p.price,
        p.cost_price,
        (p.stock_quantity * p.cost_price) as stock_value
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1
        ORDER BY stock_value DESC";
    
    $products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    
    $summary = [
        'total_products' => count($products),
        'total_value' => array_sum(array_column($products, 'stock_value')),
        'low_stock_count' => count(array_filter($products, fn($p) => $p['stock_quantity'] <= $p['low_stock_threshold'])),
        'out_of_stock_count' => count(array_filter($products, fn($p) => $p['stock_quantity'] == 0))
    ];
    
    jsonResponse(true, 'Inventory report loaded', [
        'products' => $products,
        'summary' => $summary
    ]);
}

function exportReport($conn) {
    $type = sanitize($_GET['type'] ?? 'sales');
    $format = sanitize($_GET['format'] ?? 'csv');
    
    switch ($type) {
        case 'sales':
            $data = getSalesData($conn);
            break;
        case 'products':
            $data = getProductsData($conn);
            break;
        case 'customers':
            $data = getCustomersData($conn);
            break;
        default:
            jsonResponse(false, 'Invalid report type');
    }
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report_' . $type . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    jsonResponse(true, 'Report data', $data);
}

function getSalesData($conn) {
    $sql = "SELECT 
        o.order_number,
        CONCAT(c.first_name, ' ', c.last_name) as customer,
        o.total,
        o.status,
        o.payment_status,
        o.payment_method,
        o.created_at
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC
        LIMIT 1000";
    
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getProductsData($conn) {
    $sql = "SELECT 
        p.name,
        p.sku,
        c.name as category,
        p.price,
        p.stock_quantity,
        p.low_stock_threshold,
        p.is_active
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.name";
    
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getCustomersData($conn) {
    $sql = "SELECT 
        CONCAT(c.first_name, ' ', c.last_name) as name,
        c.email,
        c.phone,
        c.city,
        c.state,
        c.total_orders,
        c.total_spent,
        c.status,
        c.created_at
        FROM customers c
        ORDER BY c.total_spent DESC";
    
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
