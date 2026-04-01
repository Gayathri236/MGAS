<?php
require_once __DIR__ . '/../includes/config.php';

requireLogin();

$conn = connectDB();
$action = sanitize($_GET['action'] ?? 'list');

switch ($action) {
    case 'list':
        getOrders($conn);
        break;
    case 'get':
        getOrder($conn);
        break;
    case 'update_status':
        updateOrderStatus($conn);
        break;
    case 'update_tracking':
        updateTracking($conn);
        break;
    case 'generate_bill':
        generateBill($conn);
        break;
    case 'create':
        createOrder($conn);
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function getOrders($conn) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = sanitize($_GET['search'] ?? '');
    $status = sanitize($_GET['status'] ?? '');
    $payment = sanitize($_GET['payment'] ?? '');
    $offset = ($page - 1) * $limit;
    
    $where = ['1=1'];
    $params = [];
    $types = '';
    
    if ($search) {
        $where[] = "(o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }
    
    if ($status) {
        $where[] = "o.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    if ($payment) {
        $where[] = "o.payment_status = ?";
        $params[] = $payment;
        $types .= 's';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $countSql = "SELECT COUNT(*) as total FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE $whereClause";
    $stmt = $conn->prepare($countSql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    $sql = "SELECT o.*, c.first_name, c.last_name, c.email, c.phone,
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.id 
            WHERE $whereClause 
            ORDER BY o.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    jsonResponse(true, 'Orders loaded', [
        'orders' => $orders,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function getOrder($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Order ID required');
    }
    
    $stmt = $conn->prepare("
        SELECT o.*, c.first_name, c.last_name, c.email, c.phone, c.address as customer_address, c.city as customer_city, c.state as customer_state, c.zip_code
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        jsonResponse(false, 'Order not found');
    }
    
    $itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->bind_param("i", $id);
    $itemsStmt->execute();
    $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $itemsStmt->close();
    
    jsonResponse(true, 'Order loaded', [
        'order' => $order,
        'items' => $items
    ]);
}

function updateOrderStatus($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    
    if (!$id || !$status) {
        jsonResponse(false, 'Order ID and status are required');
    }
    
    $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    if (!in_array($status, $validStatuses)) {
        jsonResponse(false, 'Invalid status');
    }
    
    $deliveredAt = $status === 'delivered' ? date('Y-m-d H:i:s') : null;
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, delivered_at = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $deliveredAt, $id);
    
    if ($stmt->execute()) {
        $orderStmt = $conn->prepare("SELECT order_number FROM orders WHERE id = ?");
        $orderStmt->bind_param("i", $id);
        $orderStmt->execute();
        $orderNum = $orderStmt->get_result()->fetch_assoc()['order_number'];
        $orderStmt->close();
        
        logActivity($conn, $_SESSION['admin_id'], 'update', 'orders', $id, "Changed status to $status for order $orderNum");
        jsonResponse(true, 'Order status updated');
    } else {
        jsonResponse(false, 'Failed to update status');
    }
    
    $stmt->close();
}

function updateTracking($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $tracking_number = sanitize($_POST['tracking_number'] ?? '');
    $tracking_link = sanitize($_POST['tracking_link'] ?? '');
    
    if (!$id) {
        jsonResponse(false, 'Order ID required');
    }
    
    $stmt = $conn->prepare("UPDATE orders SET tracking_number = ?, tracking_link = ? WHERE id = ?");
    $stmt->bind_param("ssi", $tracking_number, $tracking_link, $id);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Tracking information updated');
    } else {
        jsonResponse(false, 'Failed to update tracking');
    }
    
    $stmt->close();
}

function generateBill($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(false, 'Order ID required');
    }
    
    $stmt = $conn->prepare("
        SELECT o.*, c.first_name, c.last_name, c.email, c.phone, c.address as customer_address, c.city as customer_city, c.state as customer_state, c.zip_code
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        jsonResponse(false, 'Order not found');
    }
    
    $itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->bind_param("i", $id);
    $itemsStmt->execute();
    $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $itemsStmt->close();
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Invoice <?php echo $order['order_number']; ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; padding: 40px; color: #333; }
            .invoice-header { display: flex; justify-content: space-between; margin-bottom: 40px; }
            .company h1 { color: #10B981; font-size: 28px; }
            .company p { color: #666; font-size: 14px; }
            .invoice-info { text-align: right; }
            .invoice-info h2 { font-size: 24px; color: #333; }
            .invoice-info p { font-size: 14px; color: #666; margin: 4px 0; }
            .billing { display: flex; justify-content: space-between; margin-bottom: 30px; }
            .billing div { width: 45%; }
            .billing h4 { color: #10B981; margin-bottom: 10px; font-size: 14px; }
            .billing p { font-size: 14px; margin: 4px 0; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            th { background: #10B981; color: white; padding: 12px; text-align: left; }
            td { padding: 12px; border-bottom: 1px solid #eee; }
            .text-right { text-align: right; }
            .totals { width: 300px; margin-left: auto; }
            .totals tr td { padding: 8px; }
            .totals tr:last-child { font-weight: bold; font-size: 18px; color: #10B981; }
            .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="invoice-header">
            <div class="company">
                <h1>Microgreens</h1>
                <p>Fresh Microgreens & Sprouts</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong><?php echo $order['order_number']; ?></strong></p>
                <p>Date: <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                <p>Status: <?php echo ucfirst($order['status']); ?></p>
            </div>
        </div>
        
        <div class="billing">
            <div>
                <h4>BILLED TO</h4>
                <p><strong><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></strong></p>
                <p><?php echo $order['email']; ?></p>
                <p><?php echo $order['phone']; ?></p>
                <p><?php echo $order['shipping_address']; ?></p>
                <p><?php echo $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_zip']; ?></p>
            </div>
            <div>
                <h4>SHIP TO</h4>
                <p><strong><?php echo $order['shipping_name'] ?: ($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                <p><?php echo $order['shipping_address']; ?></p>
                <p><?php echo $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_zip']; ?></p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['product_name']; ?></td>
                    <td class="text-right">$<?php echo number_format($item['product_price'], 2); ?></td>
                    <td class="text-right"><?php echo $item['quantity']; ?></td>
                    <td class="text-right">$<?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">$<?php echo number_format($order['subtotal'], 2); ?></td>
            </tr>
            <?php if ($order['tax'] > 0): ?>
            <tr>
                <td>Tax</td>
                <td class="text-right">$<?php echo number_format($order['tax'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($order['shipping_cost'] > 0): ?>
            <tr>
                <td>Shipping</td>
                <td class="text-right">$<?php echo number_format($order['shipping_cost'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($order['discount'] > 0): ?>
            <tr>
                <td>Discount</td>
                <td class="text-right">-$<?php echo number_format($order['discount'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>TOTAL</td>
                <td class="text-right">$<?php echo number_format($order['total'], 2); ?></td>
            </tr>
        </table>
        
        <?php if ($order['tracking_number']): ?>
        <div style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
            <p><strong>Tracking Number:</strong> <?php echo $order['tracking_number']; ?></p>
            <?php if ($order['tracking_link']): ?>
            <p><strong>Track at:</strong> <a href="<?php echo $order['tracking_link']; ?>"><?php echo $order['tracking_link']; ?></a></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Microgreens Admin Panel</p>
        </div>
    </body>
    </html>
    <?php
    
    $html = ob_get_clean();
    
    jsonResponse(true, 'Bill generated', ['html' => $html]);
}

function createOrder($conn) {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    
    if (!$customer_id) {
        jsonResponse(false, 'Customer is required');
    }
    
    $order_number = generateOrderNumber();
    $subtotal = (float)($_POST['subtotal'] ?? 0);
    $tax = (float)($_POST['tax'] ?? 0);
    $shipping_cost = (float)($_POST['shipping_cost'] ?? 0);
    $discount = (float)($_POST['discount'] ?? 0);
    $total = $subtotal + $tax + $shipping_cost - $discount;
    $payment_method = sanitize($_POST['payment_method'] ?? 'cash_on_delivery');
    $notes = sanitize($_POST['notes'] ?? '');
    
    $stmt = $conn->prepare("
        INSERT INTO orders (order_number, customer_id, subtotal, tax, shipping_cost, discount, total, payment_method, notes, payment_status, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
    ");
    $stmt->bind_param("siddddss", $order_number, $customer_id, $subtotal, $tax, $shipping_cost, $discount, $total, $payment_method, $notes);
    
    if ($stmt->execute()) {
        $orderId = $conn->insert_id;
        logActivity($conn, $_SESSION['admin_id'], 'create', 'orders', $orderId, "Created manual order: $order_number");
        jsonResponse(true, 'Order created successfully', ['order_id' => $orderId]);
    } else {
        jsonResponse(false, 'Failed to create order');
    }
    
    $stmt->close();
}
