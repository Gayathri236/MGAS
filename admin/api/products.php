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

switch ($method) {
    case 'GET':
        if ($action === 'list' || empty($action)) {
            $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
            $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            
            $pagination = paginate($page, $perPage);
            
            $where = "WHERE p.is_active = 1";
            $params = [];
            
            if (!empty($search)) {
                $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($category) {
                $where .= " AND p.category_id = ?";
                $params[] = $category;
            }
            
            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM products p $where");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch()['total'];
            
            $stmt = $db->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $where 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute(array_merge($params, [$pagination['limit'], $pagination['offset']]));
            $products = $stmt->fetchAll();
            
            response([
                'success' => true,
                'products' => $products,
                'pagination' => [
                    'total' => $total,
                    'page' => $pagination['page'],
                    'per_page' => $pagination['per_page'],
                    'total_pages' => ceil($total / $pagination['per_page'])
                ]
            ]);
        } elseif ($action === 'get' && isset($_GET['id'])) {
            $stmt = $db->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?
            ");
            $stmt->execute([(int)$_GET['id']]);
            $product = $stmt->fetch();
            
            if ($product) {
                response(['success' => true, 'product' => $product]);
            } else {
                response(['error' => 'Product not found'], 404);
            }
        } elseif ($action === 'categories') {
            $stmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
            $categories = $stmt->fetchAll();
            response(['success' => true, 'categories' => $categories]);
        }
        break;
        
    case 'POST':
        if ($action === 'create') {
            $name = sanitize($_POST['name'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $unit = sanitize($_POST['unit'] ?? 'tray');
            $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
            $low_stock_threshold = (int)($_POST['low_stock_threshold'] ?? 10);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            if (empty($name) || $price <= 0) {
                response(['error' => 'Name and price are required'], 400);
            }
            
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . time();
            
            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadImage($_FILES['image']);
                if (isset($upload['error'])) {
                    response(['error' => $upload['error']], 400);
                }
                $image = $upload['path'];
            }
            
            $stmt = $db->prepare("
                INSERT INTO products (category_id, name, slug, description, price, unit, image, stock_quantity, low_stock_threshold, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$category_id, $name, $slug, $description, $price, $unit, $image, $stock_quantity, $low_stock_threshold, $is_featured])) {
                $productId = $db->lastInsertId();
                
                if ($stock_quantity > 0) {
                    $logStmt = $db->prepare("INSERT INTO inventory (product_id, quantity_change, change_type, notes, admin_id) VALUES (?, ?, 'restock', 'Initial stock', ?)");
                    $logStmt->execute([$productId, $stock_quantity, $_SESSION['admin_id']]);
                }
                
                response(['success' => true, 'message' => 'Product created successfully', 'id' => $productId], 201);
            } else {
                response(['error' => 'Failed to create product'], 500);
            }
        } elseif ($action === 'update' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            
            $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch();
            
            if (!$existing) {
                response(['error' => 'Product not found'], 404);
            }
            
            $name = sanitize($_POST['name'] ?? $existing['name']);
            $description = sanitize($_POST['description'] ?? $existing['description']);
            $price = isset($_POST['price']) ? (float)$_POST['price'] : $existing['price'];
            $unit = sanitize($_POST['unit'] ?? $existing['unit']);
            $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : $existing['category_id'];
            $stock_quantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : $existing['stock_quantity'];
            $low_stock_threshold = isset($_POST['low_stock_threshold']) ? (int)$_POST['low_stock_threshold'] : $existing['low_stock_threshold'];
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            $image = $existing['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadImage($_FILES['image']);
                if (!isset($upload['error'])) {
                    $image = $upload['path'];
                    if ($existing['image']) {
                        $oldPath = dirname(__DIR__) . '/' . $existing['image'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                }
            }
            
            $stmt = $db->prepare("
                UPDATE products 
                SET category_id = ?, name = ?, description = ?, price = ?, unit = ?, 
                    image = ?, stock_quantity = ?, low_stock_threshold = ?, is_featured = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$category_id, $name, $description, $price, $unit, $image, $stock_quantity, $low_stock_threshold, $is_featured, $id])) {
                response(['success' => true, 'message' => 'Product updated successfully']);
            } else {
                response(['error' => 'Failed to update product'], 500);
            }
        }
        break;
        
    case 'PUT':
        if ($action === 'update' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch();

            if (!$existing) {
                response(['error' => 'Product not found'], 404);
            }

            $name = sanitize($data['name'] ?? $existing['name']);
            $description = sanitize($data['description'] ?? $existing['description']);
            $price = isset($data['price']) ? (float)$data['price'] : $existing['price'];
            $unit = sanitize($data['unit'] ?? $existing['unit']);
            $category_id = isset($data['category_id']) ? (int)$data['category_id'] : $existing['category_id'];
            $stock_quantity = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : $existing['stock_quantity'];
            $low_stock_threshold = isset($data['low_stock_threshold']) ? (int)$data['low_stock_threshold'] : $existing['low_stock_threshold'];
            $is_featured = isset($data['is_featured']) ? (int)$data['is_featured'] : 0;

            $image = $existing['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadImage($_FILES['image']);
                if (!isset($upload['error'])) {
                    $image = $upload['path'];
                    if ($existing['image'] && file_exists('../' . $existing['image'])) {
                        unlink('../' . $existing['image']);
                    }
                }
            }

            $stmt = $db->prepare("
                UPDATE products 
                SET category_id = ?, name = ?, description = ?, price = ?, unit = ?, 
                    image = ?, stock_quantity = ?, low_stock_threshold = ?, is_featured = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$category_id, $name, $description, $price, $unit, $image, $stock_quantity, $low_stock_threshold, $is_featured, $id])) {
                response(['success' => true, 'message' => 'Product updated successfully']);
            } else {
                response(['error' => 'Failed to update product'], 500);
            }
        }
        break;
        
  case 'DELETE':
    if ($action === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        
        $stmt = $db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product) {
            
            if (!empty($product['image'])) {
                
                $fullPath = dirname(__DIR__) . '/' . $product['image'];
                
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            
            $deleteStmt = $db->prepare("DELETE FROM products WHERE id = ?");
            if ($deleteStmt->execute([$id])) {
                response([
                    'success' => true, 
                    'message' => 'Product and image deleted successfully'
                ]);
                exit; 
            }
        }
    }
    break;
}

response(['error' => 'Invalid request'], 400);
?>
