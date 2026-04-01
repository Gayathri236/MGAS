<?php
require_once __DIR__ . '/../includes/config.php';

requireLogin();

$conn = connectDB();
$action = sanitize($_GET['action'] ?? 'list');

switch ($action) {
    case 'list':
        getInventory($conn);
        break;
    case 'update_stock':
        updateStock($conn);
        break;
    case 'get_logs':
        getLogs($conn);
        break;
    case 'get_low_stock':
        getLowStock($conn);
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function getInventory($conn) {
    $search = sanitize($_GET['search'] ?? '');
    $alert = sanitize($_GET['alert'] ?? '');
    
    $where = ['p.is_active = 1'];
    $params = [];
    $types = '';
    
    if ($search) {
        $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    if ($alert === 'low') {
        $where[] = "p.stock_quantity <= p.low_stock_threshold";
    } elseif ($alert === 'out') {
        $where[] = "p.stock_quantity = 0";
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT p.*, c.name as category_name,
            CASE 
                WHEN p.stock_quantity = 0 THEN 'out_of_stock'
                WHEN p.stock_quantity <= p.low_stock_threshold THEN 'low_stock'
                ELSE 'in_stock'
            END as stock_status
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE $whereClause 
            ORDER BY p.stock_quantity ASC";
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $summary = [
        'total_products' => count($products),
        'in_stock' => count(array_filter($products, fn($p) => $p['stock_status'] === 'in_stock')),
        'low_stock' => count(array_filter($products, fn($p) => $p['stock_status'] === 'low_stock')),
        'out_of_stock' => count(array_filter($products, fn($p) => $p['stock_status'] === 'out_of_stock')),
        'total_value' => array_sum(array_map(fn($p) => $p['stock_quantity'] * $p['cost_price'], $products))
    ];
    
    jsonResponse(true, 'Inventory loaded', [
        'products' => $products,
        'summary' => $summary
    ]);
}

function updateStock($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $reason = sanitize($_POST['reason'] ?? '');
    
    if (!$id) {
        jsonResponse(false, 'Product ID required');
    }
    
    $stmt = $conn->prepare("SELECT stock_quantity, name FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        jsonResponse(false, 'Product not found');
    }
    
    $previousStock = $product['stock_quantity'];
    $newStock = $quantity;
    $change = $newStock - $previousStock;
    
    $updateStmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $newStock, $id);
    
    if ($updateStmt->execute()) {
        $logStmt = $conn->prepare("INSERT INTO inventory_logs (product_id, quantity_change, reason, admin_id, previous_stock, new_stock) VALUES (?, ?, ?, ?, ?, ?)");
        $logStmt->bind_param("iissii", $id, $change, $reason, $_SESSION['admin_id'], $previousStock, $newStock);
        $logStmt->execute();
        $logStmt->close();
        
        logActivity($conn, $_SESSION['admin_id'], 'update', 'inventory', $id, "Updated stock for {$product['name']}: $previousStock -> $newStock");
        
        jsonResponse(true, 'Stock updated successfully');
    } else {
        jsonResponse(false, 'Failed to update stock');
    }
    
    $updateStmt->close();
}

function getLogs($conn) {
    $product_id = (int)($_GET['product_id'] ?? 0);
    $limit = (int)($_GET['limit'] ?? 50);
    
    $sql = "SELECT il.*, p.name as product_name, a.full_name as admin_name 
            FROM inventory_logs il 
            LEFT JOIN products p ON il.product_id = p.id 
            LEFT JOIN admins a ON il.admin_id = a.id 
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($product_id) {
        $sql .= " AND il.product_id = ?";
        $params[] = $product_id;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY il.created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= 'i';
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    jsonResponse(true, 'Logs loaded', $logs);
}

function getLowStock($conn) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1 AND p.stock_quantity <= p.low_stock_threshold 
            ORDER BY p.stock_quantity ASC";
    
    $products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    
    jsonResponse(true, 'Low stock products loaded', $products);
}
