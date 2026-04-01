<?php
require_once __DIR__ . '/../includes/config.php';

requireLogin();

$conn = connectDB();
$action = sanitize($_GET['action'] ?? 'list');

switch ($action) {
    case 'list':
        getCustomers($conn);
        break;
    case 'get':
        getCustomer($conn);
        break;
    case 'update':
        updateCustomer($conn);
        break;
    case 'toggle_status':
        toggleStatus($conn);
        break;
    case 'get_orders':
        getCustomerOrders($conn);
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function getCustomers($conn) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = sanitize($_GET['search'] ?? '');
    $status = sanitize($_GET['status'] ?? '');
    $offset = ($page - 1) * $limit;
    
    $where = ['1=1'];
    $params = [];
    $types = '';
    
    if ($search) {
        $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }
    
    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $countSql = "SELECT COUNT(*) as total FROM customers WHERE $whereClause";
    $stmt = $conn->prepare($countSql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    $sql = "SELECT * FROM customers WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    jsonResponse(true, 'Customers loaded', [
        'customers' => $customers,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function getCustomer($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Customer ID required');
    }
    
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$customer) {
        jsonResponse(false, 'Customer not found');
    }
    
    $ordersStmt = $conn->prepare("
        SELECT o.*, COUNT(oi.id) as items_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.customer_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $ordersStmt->bind_param("i", $id);
    $ordersStmt->execute();
    $orders = $ordersStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $ordersStmt->close();
    
    jsonResponse(true, 'Customer loaded', [
        'customer' => $customer,
        'orders' => $orders
    ]);
}

function updateCustomer($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Customer ID required');
    }
    
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $zip_code = sanitize($_POST['zip_code'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        jsonResponse(false, 'First name, last name, and email are required');
    }
    
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        jsonResponse(false, 'Email already exists');
    }
    $stmt->close();
    
    $sql = "UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, zip_code = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $first_name, $last_name, $email, $phone, $address, $city, $state, $zip_code, $id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'update', 'customers', $id, "Updated customer: $first_name $last_name");
        jsonResponse(true, 'Customer updated successfully');
    } else {
        jsonResponse(false, 'Failed to update customer');
    }
    
    $stmt->close();
}

function toggleStatus($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    
    if (!$id) {
        jsonResponse(false, 'Customer ID required');
    }
    
    if (!in_array($status, ['active', 'inactive', 'blocked'])) {
        jsonResponse(false, 'Invalid status');
    }
    
    $stmt = $conn->prepare("UPDATE customers SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'update', 'customers', $id, "Changed status to $status");
        jsonResponse(true, 'Customer status updated');
    } else {
        jsonResponse(false, 'Failed to update status');
    }
    
    $stmt->close();
}

function getCustomerOrders($conn) {
    $customer_id = (int)($_GET['customer_id'] ?? 0);
    
    if (!$customer_id) {
        jsonResponse(false, 'Customer ID required');
    }
    
    $stmt = $conn->prepare("
        SELECT o.*, COUNT(oi.id) as items_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.customer_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    jsonResponse(true, 'Orders loaded', $orders);
}
