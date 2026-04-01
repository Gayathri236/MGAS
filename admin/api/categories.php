<?php
require_once __DIR__ . '/../includes/config.php';

requireLogin();

$conn = connectDB();
$action = sanitize($_GET['action'] ?? 'list');

switch ($action) {
    case 'list':
        $categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
        jsonResponse(true, 'Categories loaded', $categories);
        break;
    case 'create':
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        if (empty($name)) {
            jsonResponse(false, 'Category name is required');
        }
        
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $slug, $description);
        
        if ($stmt->execute()) {
            jsonResponse(true, 'Category created successfully');
        } else {
            jsonResponse(false, 'Failed to create category');
        }
        break;
    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $status = sanitize($_POST['status'] ?? 'active');
        
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $description, $status, $id);
        
        if ($stmt->execute()) {
            jsonResponse(true, 'Category updated successfully');
        } else {
            jsonResponse(false, 'Failed to update category');
        }
        break;
    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        
        $stmt = $conn->prepare("UPDATE categories SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            jsonResponse(true, 'Category deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete category');
        }
        break;
}
