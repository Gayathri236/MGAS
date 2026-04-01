<?php
require_once __DIR__ . '/../includes/config.php';

requireLogin();

$conn = connectDB();
$action = sanitize($_GET['action'] ?? 'list');

switch ($action) {
    case 'list':
        getProducts($conn);
        break;
    case 'get':
        getProduct($conn);
        break;
    case 'create':
        createProduct($conn);
        break;
    case 'update':
        updateProduct($conn);
        break;
    case 'delete':
        deleteProduct($conn);
        break;
    case 'toggle_featured':
        toggleFeatured($conn);
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function getProducts($conn) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = sanitize($_GET['search'] ?? '');
    $category = sanitize($_GET['category'] ?? '');
    $status = sanitize($_GET['status'] ?? '');
    $offset = ($page - 1) * $limit;
    
    $where = ['1=1'];
    $params = [];
    $types = '';
    
    if ($search) {
        $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    if ($category) {
        $where[] = "p.category_id = ?";
        $params[] = $category;
        $types .= 'i';
    }
    
    if ($status !== '') {
        $where[] = "p.is_active = ?";
        $params[] = $status;
        $types .= 'i';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $countSql = "SELECT COUNT(*) as total FROM products p WHERE $whereClause";
    $stmt = $conn->prepare($countSql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE $whereClause 
            ORDER BY p.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $categories = $conn->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
    
    jsonResponse(true, 'Products loaded', [
        'products' => $products,
        'categories' => $categories,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function getProduct($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Product ID required');
    }
    
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        jsonResponse(false, 'Product not found');
    }
    
    jsonResponse(true, 'Product loaded', $product);
}

function createProduct($conn) {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $compare_price = !empty($_POST['compare_price']) ? (float)$_POST['compare_price'] : null;
    $cost_price = !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null;
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $low_stock_threshold = (int)($_POST['low_stock_threshold'] ?? 10);
    $unit = sanitize($_POST['unit'] ?? 'tray');
    $sku = sanitize($_POST['sku'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (empty($name) || empty($price)) {
        jsonResponse(false, 'Name and price are required');
    }
    
    if ($sku) {
        $check = $conn->prepare("SELECT id FROM products WHERE sku = ?");
        $check->bind_param("s", $sku);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            jsonResponse(false, 'SKU already exists');
        }
        $check->close();
    }
    
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . time();
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image'], 'product');
    }
    
    $sql = "INSERT INTO products (category_id, name, slug, description, price, compare_price, cost_price, stock_quantity, low_stock_threshold, unit, sku, image, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdddiissssi", $category_id, $name, $slug, $description, $price, $compare_price, $cost_price, $stock_quantity, $low_stock_threshold, $unit, $sku, $image, $is_featured);
    
    if ($stmt->execute()) {
        $productId = $conn->insert_id;
        logActivity($conn, $_SESSION['admin_id'], 'create', 'products', $productId, "Created product: $name");
        
        if ($stock_quantity > 0) {
            $logStmt = $conn->prepare("INSERT INTO inventory_logs (product_id, quantity_change, reason, admin_id, previous_stock, new_stock) VALUES (?, ?, 'Initial stock', ?, 0, ?)");
            $logStmt->bind_param("iiii", $productId, $stock_quantity, $_SESSION['admin_id'], $stock_quantity);
            $logStmt->execute();
            $logStmt->close();
        }
        
        jsonResponse(true, 'Product created successfully');
    } else {
        jsonResponse(false, 'Failed to create product');
    }
    
    $stmt->close();
}

function updateProduct($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Product ID required');
    }
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        jsonResponse(false, 'Product not found');
    }
    
    $name = sanitize($_POST['name'] ?? $product['name']);
    $category_id = (int)($_POST['category_id'] ?? $product['category_id']);
    $description = sanitize($_POST['description'] ?? $product['description']);
    $price = (float)($_POST['price'] ?? $product['price']);
    $compare_price = !empty($_POST['compare_price']) ? (float)$_POST['compare_price'] : null;
    $cost_price = !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null;
    $stock_quantity = (int)($_POST['stock_quantity'] ?? $product['stock_quantity']);
    $low_stock_threshold = (int)($_POST['low_stock_threshold'] ?? $product['low_stock_threshold']);
    $unit = sanitize($_POST['unit'] ?? $product['unit']);
    $sku = sanitize($_POST['sku'] ?? $product['sku']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    if ($sku && $sku !== $product['sku']) {
        $check = $conn->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $check->bind_param("si", $sku, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            jsonResponse(false, 'SKU already exists');
        }
        $check->close();
    }
    
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $newImage = uploadImage($_FILES['image'], 'product');
        if ($newImage) {
            $image = $newImage;
        }
    }
    
    $sql = "UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, compare_price = ?, cost_price = ?, stock_quantity = ?, low_stock_threshold = ?, unit = ?, sku = ?, image = ?, is_featured = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdddiissssii", $category_id, $name, $description, $price, $compare_price, $cost_price, $stock_quantity, $low_stock_threshold, $unit, $sku, $image, $is_featured, $id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'update', 'products', $id, "Updated product: $name");
        jsonResponse(true, 'Product updated successfully');
    } else {
        jsonResponse(false, 'Failed to update product');
    }
    
    $stmt->close();
}

function deleteProduct($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Product ID required');
    }
    
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        jsonResponse(false, 'Product not found');
    }
    
    $stmt = $conn->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'delete', 'products', $id, "Deleted product: {$product['name']}");
        jsonResponse(true, 'Product deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete product');
    }
    
    $stmt->close();
}

function toggleFeatured($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Product ID required');
    }
    
    $stmt = $conn->prepare("UPDATE products SET is_featured = NOT is_featured WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Product featured status updated');
    } else {
        jsonResponse(false, 'Failed to update product');
    }
    
    $stmt->close();
}
