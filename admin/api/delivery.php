<?php
require_once __DIR__ . '/../includes/config.php';

requireLogin();

$conn = connectDB();
$action = sanitize($_GET['action'] ?? 'list');

switch ($action) {
    case 'list':
        getDeliveries($conn);
        break;
    case 'get':
        getDelivery($conn);
        break;
    case 'create':
        createDelivery($conn);
        break;
    case 'update':
        updateDelivery($conn);
        break;
    case 'delete':
        deleteDelivery($conn);
        break;
    case 'get_pending_orders':
        getPendingOrders($conn);
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function getDeliveries($conn) {
    $date = sanitize($_GET['date'] ?? date('Y-m-d'));
    $status = sanitize($_GET['status'] ?? '');
    
    $where = ['1=1'];
    $params = [];
    $types = '';
    
    if ($date) {
        $where[] = "ds.delivery_date = ?";
        $params[] = $date;
        $types .= 's';
    }
    
    if ($status) {
        $where[] = "ds.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT ds.*, o.order_number, o.total as order_total, o.shipping_address, o.shipping_city, o.shipping_state, o.shipping_zip, o.shipping_phone,
            c.first_name, c.last_name
            FROM delivery_schedule ds
            LEFT JOIN orders o ON ds.order_id = o.id
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE $whereClause
            ORDER BY ds.delivery_date ASC, ds.time_slot ASC";
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $deliveries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $stats = [
        'total' => count($deliveries),
        'scheduled' => count(array_filter($deliveries, fn($d) => $d['status'] === 'scheduled')),
        'out_for_delivery' => count(array_filter($deliveries, fn($d) => $d['status'] === 'out_for_delivery')),
        'delivered' => count(array_filter($deliveries, fn($d) => $d['status'] === 'delivered')),
        'failed' => count(array_filter($deliveries, fn($d) => $d['status'] === 'failed'))
    ];
    
    jsonResponse(true, 'Deliveries loaded', [
        'deliveries' => $deliveries,
        'stats' => $stats
    ]);
}

function getDelivery($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Delivery ID required');
    }
    
    $stmt = $conn->prepare("
        SELECT ds.*, o.order_number, o.total as order_total, o.shipping_address, o.shipping_city, o.shipping_state, o.shipping_zip, o.shipping_phone, o.shipping_name,
        c.first_name, c.last_name, c.email, c.phone
        FROM delivery_schedule ds
        LEFT JOIN orders o ON ds.order_id = o.id
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE ds.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $delivery = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$delivery) {
        jsonResponse(false, 'Delivery not found');
    }
    
    jsonResponse(true, 'Delivery loaded', $delivery);
}

function createDelivery($conn) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $delivery_date = sanitize($_POST['delivery_date'] ?? '');
    $time_slot = sanitize($_POST['time_slot'] ?? '');
    $driver_name = sanitize($_POST['driver_name'] ?? '');
    $driver_phone = sanitize($_POST['driver_phone'] ?? '');
    $vehicle_number = sanitize($_POST['vehicle_number'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (!$order_id || !$delivery_date) {
        jsonResponse(false, 'Order and delivery date are required');
    }
    
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        jsonResponse(false, 'Order not found');
    }
    $stmt->close();
    
    $checkStmt = $conn->prepare("SELECT id FROM delivery_schedule WHERE order_id = ?");
    $checkStmt->bind_param("i", $order_id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        jsonResponse(false, 'Delivery already scheduled for this order');
    }
    $checkStmt->close();
    
    $sql = "INSERT INTO delivery_schedule (order_id, delivery_date, time_slot, driver_name, driver_phone, vehicle_number, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $order_id, $delivery_date, $time_slot, $driver_name, $driver_phone, $vehicle_number, $notes);
    
    if ($stmt->execute()) {
        $deliveryId = $conn->insert_id;
        logActivity($conn, $_SESSION['admin_id'], 'create', 'delivery', $deliveryId, "Created delivery schedule");
        jsonResponse(true, 'Delivery scheduled successfully');
    } else {
        jsonResponse(false, 'Failed to schedule delivery');
    }
    
    $stmt->close();
}

function updateDelivery($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    $driver_name = sanitize($_POST['driver_name'] ?? '');
    $driver_phone = sanitize($_POST['driver_phone'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (!$id) {
        jsonResponse(false, 'Delivery ID required');
    }
    
    $sql = "UPDATE delivery_schedule SET status = ?, driver_name = ?, driver_phone = ?, notes = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $status, $driver_name, $driver_phone, $notes, $id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'update', 'delivery', $id, "Updated delivery status to $status");
        jsonResponse(true, 'Delivery updated successfully');
    } else {
        jsonResponse(false, 'Failed to update delivery');
    }
    
    $stmt->close();
}

function deleteDelivery($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Delivery ID required');
    }
    
    $stmt = $conn->prepare("DELETE FROM delivery_schedule WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Delivery deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete delivery');
    }
    
    $stmt->close();
}

function getPendingOrders($conn) {
    $sql = "SELECT o.id, o.order_number, o.total, o.shipping_address, o.shipping_city, o.shipping_state, c.first_name, c.last_name
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.status IN ('confirmed', 'processing') 
            AND o.id NOT IN (SELECT order_id FROM delivery_schedule)
            ORDER BY o.created_at ASC";
    
    $orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    
    jsonResponse(true, 'Pending orders loaded', $orders);
}
