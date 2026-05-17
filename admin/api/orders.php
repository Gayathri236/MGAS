<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Helper functions
if (!function_exists('generateOrderNumber')) {
    function generateOrderNumber() {
        return 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    }
}

if (!function_exists('generateTrackingNumber')) {
    function generateTrackingNumber() {
        return 'TRK' . date('Ymd') . rand(10000, 99999);
    }
}

switch ($method) {
    case 'GET':
        if ($action === 'list' || empty($action)) {
            $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
            $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
            $date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
            $date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            
            $pagination = paginate($page, $perPage);
            
            $where = "WHERE 1=1";
            $params = [];
            
            if (!empty($search)) {
                $where .= " AND (o.order_number LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($status)) {
                $where .= " AND o.status = ?";
                $params[] = $status;
            }
            
            if (!empty($date_from)) {
                $where .= " AND DATE(o.created_at) >= ?";
                $params[] = $date_from;
            }
            
            if (!empty($date_to)) {
                $where .= " AND DATE(o.created_at) <= ?";
                $params[] = $date_to;
            }
            
            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM orders o LEFT JOIN customers c ON o.customer_id = c.id $where");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch()['total'];
            
            $stmt = $db->prepare("
                SELECT o.*, 
                       c.name as customer_name, 
                       c.email as customer_email, 
                       c.phone as customer_phone,
                       c.address as customer_address
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                $where 
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute(array_merge($params, [$pagination['limit'], $pagination['offset']]));
            $orders = $stmt->fetchAll();
            
            foreach ($orders as &$order) {
                $itemsStmt = $db->prepare("
                    SELECT oi.*, p.name as product_name, p.price as current_price 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ");
                $itemsStmt->execute([$order['id']]);
                $order['items'] = $itemsStmt->fetchAll();
            }
            
            response([
                'success' => true,
                'orders' => $orders,
                'pagination' => [
                    'total' => $total,
                    'page' => $pagination['page'],
                    'per_page' => $pagination['per_page'],
                    'total_pages' => ceil($total / $pagination['per_page'])
                ]
            ]);
        } 
        elseif ($action === 'get' && isset($_GET['id'])) {
            $stmt = $db->prepare("
                SELECT o.*, 
                       c.name as customer_name, 
                       c.email as customer_email, 
                       c.phone as customer_phone,
                       c.address as customer_address,
                       c.city as customer_city,
                       c.postal_code
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                WHERE o.id = ?
            ");
            $stmt->execute([(int)$_GET['id']]);
            $order = $stmt->fetch();
            
            if ($order) {
                $itemsStmt = $db->prepare("
                    SELECT oi.*, p.name as product_name, p.price as current_price 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ");
                $itemsStmt->execute([$order['id']]);
                $order['items'] = $itemsStmt->fetchAll();
                
                response(['success' => true, 'order' => $order]);
            } else {
                response(['error' => 'Order not found'], 404);
            }
        } 
        elseif ($action === 'stats') {
            $stmt = $db->query("SELECT COUNT(*) as total FROM orders");
            $total = (int)$stmt->fetch()['total'];
            
            $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            $statusCounts = [];
            foreach ($statuses as $status) {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE status = ?");
                $stmt->execute([$status]);
                $statusCounts[$status] = (int)$stmt->fetch()['count'];
            }
            
            $stmt = $db->query("SELECT COALESCE(SUM(total), 0) as total_revenue FROM orders WHERE payment_status = 'paid'");
            $totalRevenue = (float)$stmt->fetch()['total_revenue'];
            
            response([
                'success' => true,
                'stats' => [
                    'total' => $total,
                    'status_counts' => $statusCounts,
                    'total_revenue' => $totalRevenue
                ]
            ]);
        }
        break;
        
    case 'POST':
        if ($action === 'create') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
            $delivery_address = sanitize($data['delivery_address'] ?? '');
            $notes = sanitize($data['notes'] ?? '');
            $items = $data['items'] ?? [];
            
            if (!$customer_id) {
                response(['error' => 'Customer is required'], 400);
            }
            
            if (empty($items)) {
                response(['error' => 'At least one product is required'], 400);
            }
            
            // Verify customer exists
            $custCheck = $db->prepare("SELECT id, name, email, phone, address FROM customers WHERE id = ?");
            $custCheck->execute([$customer_id]);
            $customer = $custCheck->fetch();
            
            if (!$customer) {
                response(['error' => 'Customer not found'], 400);
            }
            
            // Calculate totals
            $subtotal = 0;
            $items_data = [];
            
            foreach ($items as $item) {
                $product_id = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];
                $price = isset($item['price']) ? (float)$item['price'] : 0;
                
                if ($quantity <= 0) continue;
                
                // If price not provided, get from database
                if ($price == 0) {
                    $priceStmt = $db->prepare("SELECT name, price FROM products WHERE id = ?");
                    $priceStmt->execute([$product_id]);
                    $product = $priceStmt->fetch();
                    if ($product) {
                        $price = (float)$product['price'];
                        $product_name = $product['name'];
                    } else {
                        response(['error' => "Product with ID $product_id not found"], 400);
                    }
                } else {
                    $nameStmt = $db->prepare("SELECT name FROM products WHERE id = ?");
                    $nameStmt->execute([$product_id]);
                    $product = $nameStmt->fetch();
                    $product_name = $product ? $product['name'] : "Product #$product_id";
                }
                
                $item_subtotal = $price * $quantity;
                $subtotal += $item_subtotal;
                $items_data[] = [
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'subtotal' => $item_subtotal
                ];
            }
            
            if (empty($items_data)) {
                response(['error' => 'No valid items found'], 400);
            }
            
            $tax = $subtotal * 0.05; // 5% tax
            $delivery_fee = ($subtotal > 5000) ? 0 : 250;
            $total = $subtotal + $tax + $delivery_fee;
            $orderNumber = generateOrderNumber();
            $delivery_date = date('Y-m-d', strtotime('+2 days'));
            
            $db->beginTransaction();
            
            try {
                // Check if delivery_address column exists, if not, use a default or skip
                $stmt = $db->prepare("
                    INSERT INTO orders (
                        order_number, customer_id, subtotal, tax, delivery_fee, total, 
                        payment_method, delivery_date, delivery_address, notes, 
                        status, payment_status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, 'cash', ?, ?, ?, 'pending', 'pending', NOW())
                ");
                
                $finalAddress = $delivery_address ?: ($customer['address'] ?? '');
                
                $stmt->execute([
                    $orderNumber, $customer_id, $subtotal, $tax, $delivery_fee, $total,
                    $delivery_date, $finalAddress, $notes
                ]);
                
                $orderId = $db->lastInsertId();
                
                // Insert order items (skip stock update if column doesn't exist)
                foreach ($items_data as $item) {
                    $itemStmt = $db->prepare("
                        INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $itemStmt->execute([
                        $orderId, $item['product_id'], $item['product_name'], 
                        $item['quantity'], $item['unit_price'], $item['subtotal']
                    ]);
                    
                    // Try to update stock only if column exists
                    try {
                        $stockStmt = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                        $stockStmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                    } catch (Exception $e) {
                        // Stock column doesn't exist, skip silently
                    }
                }
                
                // Update customer stats if columns exist
                try {
                    $custStmt = $db->prepare("
                        UPDATE customers SET total_orders = total_orders + 1, last_order_date = NOW() 
                        WHERE id = ?
                    ");
                    $custStmt->execute([$customer_id]);
                } catch (Exception $e) {
                    // Columns might not exist, skip
                }
                
                // Generate tracking number
                $trackingNumber = generateTrackingNumber();
                $trackingLink = "https://track.microgreens.com/" . $trackingNumber;
                $trackStmt = $db->prepare("UPDATE orders SET tracking_number = ?, tracking_link = ? WHERE id = ?");
                $trackStmt->execute([$trackingNumber, $trackingLink, $orderId]);
                
                $db->commit();
                
                // Return the complete order data for UI update
                $orderStmt = $db->prepare("
                    SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone
                    FROM orders o 
                    LEFT JOIN customers c ON o.customer_id = c.id 
                    WHERE o.id = ?
                ");
                $orderStmt->execute([$orderId]);
                $newOrder = $orderStmt->fetch();
                
                $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemsStmt->execute([$orderId]);
                $newOrder['items'] = $itemsStmt->fetchAll();
                
                response([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'tracking_number' => $trackingNumber,
                    'order' => $newOrder
                ], 201);
                
            } catch (Exception $e) {
                $db->rollBack();
                response(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
            }
        } 
        elseif ($action === 'tracking' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            
            $trackingNumber = generateTrackingNumber();
            $trackingLink = "https://track.microgreens.com/" . $trackingNumber;
            
            $stmt = $db->prepare("UPDATE orders SET tracking_number = ?, tracking_link = ? WHERE id = ?");
            if ($stmt->execute([$trackingNumber, $trackingLink, $id])) {
                response([
                    'success' => true,
                    'message' => 'Tracking number generated',
                    'tracking_number' => $trackingNumber,
                    'tracking_link' => $trackingLink
                ]);
            } else {
                response(['error' => 'Failed to generate tracking'], 500);
            }
        }
        break;
        
    case 'PUT':
        $rawInput = file_get_contents('php://input');
        $putData = json_decode($rawInput, true);

        if ($action === 'status' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $status = isset($putData['status']) ? sanitize($putData['status']) : '';
            
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            
            if (empty($status) || !in_array($status, $validStatuses)) {
                response(['success' => false, 'error' => 'Invalid status'], 400);
            }
            
            $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            
            if ($stmt->execute([$status, $id])) {
                if ($status === 'delivered') {
                    try {
                        $paymentStmt = $db->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ? AND payment_status = 'pending'");
                        $paymentStmt->execute([$id]);
                    } catch (Exception $e) {
                        // Payment status column might not exist, skip
                    }
                }
                
                // Get updated order
                $orderStmt = $db->prepare("
                    SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone
                    FROM orders o 
                    LEFT JOIN customers c ON o.customer_id = c.id 
                    WHERE o.id = ?
                ");
                $orderStmt->execute([$id]);
                $order = $orderStmt->fetch();
                
                response([
                    'success' => true, 
                    'message' => 'Order status updated successfully',
                    'order' => $order
                ]);
            } else {
                response(['success' => false, 'error' => 'Database update failed'], 500);
            }
        } 
        elseif ($action === 'payment' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $paymentStatus = isset($putData['payment_status']) ? sanitize($putData['payment_status']) : '';
            
            $validPaymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
            
            if (empty($paymentStatus) || !in_array($paymentStatus, $validPaymentStatuses)) {
                response(['success' => false, 'error' => 'Invalid payment status'], 400);
            }

            $stmt = $db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
            
            if ($stmt->execute([$paymentStatus, $id])) {
                response(['success' => true, 'message' => 'Payment status updated successfully']);
            } else {
                response(['success' => false, 'error' => 'Failed to update payment status'], 500);
            }
        } 
        else {
            response(['success' => false, 'error' => 'Invalid action or missing ID'], 400);
        }
        break;
        
    default:
        response(['error' => 'Invalid request method'], 400);
        break;
}
?>