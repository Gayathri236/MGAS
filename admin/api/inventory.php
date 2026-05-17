<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
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

switch ($method) {
    case 'GET':
        if ($action === 'list' || empty($action)) {
            $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
            $filter = isset($_GET['filter']) ? sanitize($_GET['filter']) : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            
            $pagination = paginate($page, $perPage);
            
            $where = "WHERE p.is_active = 1";
            $params = [];
            
            if (!empty($search)) {
                $where .= " AND (p.name LIKE ? OR c.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($filter === 'low') {
                $where .= " AND p.stock_quantity <= p.low_stock_threshold";
            } elseif ($filter === 'out') {
                $where .= " AND p.stock_quantity = 0";
            } elseif ($filter === 'good') {
                $where .= " AND p.stock_quantity > p.low_stock_threshold";
            }
            
            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id $where");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch()['total'];
            
            $stmt = $db->prepare("
                SELECT p.*, c.name as category_name,
                       CASE 
                           WHEN p.stock_quantity <= 0 THEN 'out'
                           WHEN p.stock_quantity <= p.low_stock_threshold THEN 'low'
                           ELSE 'good'
                       END as stock_status
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $where 
                ORDER BY p.stock_quantity ASC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute(array_merge($params, [$pagination['limit'], $pagination['offset']]));
            $inventory = $stmt->fetchAll();
            
            response([
                'success' => true,
                'inventory' => $inventory,
                'pagination' => [
                    'total' => $total,
                    'page' => $pagination['page'],
                    'per_page' => $pagination['per_page'],
                    'total_pages' => ceil($total / $pagination['per_page'])
                ]
            ]);
        } elseif ($action === 'history' && isset($_GET['product_id'])) {
            $stmt = $db->prepare("
                SELECT i.*, a.name as admin_name, p.name as product_name
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                LEFT JOIN admins a ON i.admin_id = a.id
                WHERE i.product_id = ?
                ORDER BY i.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([(int)$_GET['product_id']]);
            $history = $stmt->fetchAll();
            
            response(['success' => true, 'history' => $history]);
        } elseif ($action === 'stats') {
            $stmt = $db->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
            $total = (int)$stmt->fetch()['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as low FROM products WHERE stock_quantity <= low_stock_threshold AND stock_quantity > 0 AND is_active = 1");
            $low = (int)$stmt->fetch()['low'];
            
            $stmt = $db->query("SELECT COUNT(*) as out_of_stock FROM products WHERE stock_quantity = 0 AND is_active = 1");
            $outOfStock = (int)$stmt->fetch()['out_of_stock'];
            
            $stmt = $db->query("SELECT COALESCE(SUM(stock_quantity * price), 0) as total_value FROM products WHERE is_active = 1");
            $totalValue = (float)$stmt->fetch()['total_value'];
            
            response([
                'success' => true,
                'stats' => [
                    'total' => $total,
                    'low_stock' => $low,
                    'out_of_stock' => $outOfStock,
                    'good_stock' => $total - $low - $outOfStock,
                    'total_value' => $totalValue
                ]
            ]);
        }
        break;
        
    case 'PUT':
        if ($action === 'update' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $newQuantity = (int)($data['quantity'] ?? 0);
            $reason = sanitize($data['reason'] ?? 'adjustment');
            $notes = sanitize($data['notes'] ?? '');
            
            $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                response(['error' => 'Product not found'], 404);
            }
            
            $oldQuantity = (int)$product['stock_quantity'];
            $change = $newQuantity - $oldQuantity;
            
            $validReasons = ['restock', 'sale', 'damage', 'return', 'adjustment'];
            if (!in_array($reason, $validReasons)) {
                $reason = 'adjustment';
            }
            
            $db->beginTransaction();
            
            try {
                $updateStmt = $db->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                $updateStmt->execute([$newQuantity, $id]);
                
                $logStmt = $db->prepare("
                    INSERT INTO inventory (product_id, quantity_change, change_type, notes, admin_id)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $logStmt->execute([$id, $change, $reason, $notes, $_SESSION['admin_id']]);
                
                $db->commit();
                
                response([
                    'success' => true,
                    'message' => 'Stock updated successfully',
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'change' => $change
                ]);
            } catch (Exception $e) {
                $db->rollBack();
                response(['error' => 'Failed to update stock'], 500);
            }
        } elseif ($action === 'batch') {
            $data = json_decode(file_get_contents('php://input'), true);
            $updates = $data['updates'] ?? [];
            
            if (empty($updates)) {
                response(['error' => 'No updates provided'], 400);
            }
            
            $db->beginTransaction();
            
            try {
                foreach ($updates as $update) {
                    $id = (int)$update['id'];
                    $quantity = (int)$update['quantity'];
                    
                    $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $product = $stmt->fetch();
                    
                    if ($product) {
                        $change = $quantity - (int)$product['stock_quantity'];
                        
                        $updateStmt = $db->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                        $updateStmt->execute([$quantity, $id]);
                        
                        $logStmt = $db->prepare("
                            INSERT INTO inventory (product_id, quantity_change, change_type, notes, admin_id)
                            VALUES (?, ?, 'restock', 'Batch update', ?)
                        ");
                        $logStmt->execute([$id, $change, $_SESSION['admin_id']]);
                    }
                }
                
                $db->commit();
                response(['success' => true, 'message' => 'Batch update successful']);
            } catch (Exception $e) {
                $db->rollBack();
                response(['error' => 'Batch update failed'], 500);
            }
        }
        break;
}

response(['error' => 'Invalid request'], 400);
?>
