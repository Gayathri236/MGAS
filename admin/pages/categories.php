<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .category-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            transition: all 0.2s;
        }
        
        .category-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .category-icon i {
            font-size: 28px;
            color: white;
        }
        
        .category-info {
            flex: 1;
        }
        
        .category-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 5px;
        }
        
        .category-info p {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .category-stats {
            display: flex;
            gap: 20px;
            margin-right: 20px;
        }
        
        .category-stat {
            text-align: center;
        }
        
        .category-stat strong {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-800);
        }
        
        .category-stat span {
            font-size: 11px;
            color: var(--gray-500);
        }
        
        .modal-body .form-group {
            margin-bottom: 20px;
        }
        
        .modal-body .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 8px;
        }
        
        .modal-body .form-group input,
        .modal-body .form-group textarea,
        .modal-body .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .modal-body .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .modal-body .form-group input:focus,
        .modal-body .form-group textarea:focus,
        .modal-body .form-group select:focus {
            border-color: var(--primary);
            outline: none;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include __DIR__ . '/header.php'; ?>
            
            <div class="dashboard-content">
                <div class="page-header">
                    <div>
                        <h1>Categories</h1>
                        <p>Manage product categories</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openCategoryModal()">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                </div>

                <div id="categoriesContainer">
                    <div class="text-center text-muted p-5">Loading categories...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="categoryModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Add Category</h3>
                <button class="modal-close" onclick="closeCategoryModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="categoryForm" onsubmit="saveCategory(event)">
                <div class="modal-body">
                    <input type="hidden" id="categoryId" name="id">
                    
                    <div class="form-group">
                        <label>Category Name *</label>
                        <input type="text" name="name" id="categoryName" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="categoryDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="categoryStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCategoryModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
        });

        async function loadCategories() {
            try {
                const response = await fetch('api/categories.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    renderCategories(result.data);
                }
            } catch (error) {
                showToast('Error loading categories', 'error');
            }
        }

        function renderCategories(categories) {
            const container = document.getElementById('categoriesContainer');
            
            if (categories.length === 0) {
                container.innerHTML = `
                    <div class="category-card" style="text-align: center; justify-content: center; padding: 40px;">
                        <div>
                            <i class="fas fa-tags" style="font-size: 48px; color: var(--gray-300); margin-bottom: 15px;"></i>
                            <p style="color: var(--gray-500);">No categories found. Add your first category!</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            const icons = ['leaf', 'seedling', 'carrot', 'apple-alt', 'lemon', 'pepper-hot', 'wheat-awn', 'crown'];
            
            container.innerHTML = categories.map((c, i) => `
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-${icons[i % icons.length]}"></i>
                    </div>
                    <div class="category-info">
                        <h3>${c.name}</h3>
                        <p>${c.description || 'No description'}</p>
                    </div>
                    <span class="badge badge-${c.status === 'active' ? 'delivered' : 'cancelled'}">
                        ${c.status === 'active' ? 'Active' : 'Inactive'}
                    </span>
                    <div class="action-buttons">
                        <button class="btn-icon" onclick="editCategory(${c.id}, '${c.name}', '${c.description || ''}', '${c.status}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon danger" onclick="deleteCategory(${c.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function openCategoryModal(category = null) {
            const modal = document.getElementById('categoryModal');
            const title = document.getElementById('modalTitle');
            
            if (category) {
                title.textContent = 'Edit Category';
                document.getElementById('categoryId').value = category.id;
                document.getElementById('categoryName').value = category.name;
                document.getElementById('categoryDescription').value = category.description || '';
                document.getElementById('categoryStatus').value = category.status;
            } else {
                title.textContent = 'Add Category';
                document.getElementById('categoryForm').reset();
                document.getElementById('categoryId').value = '';
            }
            
            modal.classList.add('active');
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').classList.remove('active');
        }

        function editCategory(id, name, description, status) {
            openCategoryModal({ id, name, description, status });
        }

        async function saveCategory(e) {
            e.preventDefault();
            
            const form = document.getElementById('categoryForm');
            const formData = new FormData(form);
            const isEdit = formData.get('id');
            
            formData.set('action', isEdit ? 'update' : 'create');
            
            try {
                const response = await fetch('api/categories.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    closeCategoryModal();
                    loadCategories();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error saving category', 'error');
            }
        }

        async function deleteCategory(id) {
            if (!confirmDelete('Are you sure you want to delete this category?')) return;
            
            try {
                const response = await fetch(`api/categories.php?action=delete&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    loadCategories();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error deleting category', 'error');
            }
        }
    </script>
</body>
</html>
