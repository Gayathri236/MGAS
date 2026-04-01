<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/products.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include __DIR__ . '/header.php'; ?>
            
            <div class="dashboard-content">
                <div class="page-header">
                    <div>
                        <h1>Products</h1>
                        <p>Manage your microgreens inventory</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openProductModal()">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </div>
                </div>

                <div class="filters-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search products..." onkeyup="debouncedSearch()">
                    </div>
                    <select id="categoryFilter" onchange="loadProducts()">
                        <option value="">All Categories</option>
                    </select>
                    <select id="statusFilter" onchange="loadProducts()">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="card">
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Featured</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTable">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted p-5">Loading products...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="pagination" id="pagination">
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="productModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 id="modalTitle">Add Product</h3>
                <button class="modal-close" onclick="closeProductModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="productForm" onsubmit="saveProduct(event)">
                <div class="modal-body">
                    <input type="hidden" id="productId" name="id">
                    
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Product Name *</label>
                            <input type="text" name="name" id="productName" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" id="productCategory">
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>SKU</label>
                            <input type="text" name="sku" id="productSku">
                        </div>
                        
                        <div class="form-group">
                            <label>Price *</label>
                            <div class="input-group">
                                <span class="input-prefix">$</span>
                                <input type="number" step="0.01" name="price" id="productPrice" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Compare Price</label>
                            <div class="input-group">
                                <span class="input-prefix">$</span>
                                <input type="number" step="0.01" name="compare_price" id="productComparePrice">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Cost Price</label>
                            <div class="input-group">
                                <span class="input-prefix">$</span>
                                <input type="number" step="0.01" name="cost_price" id="productCostPrice">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="productStock" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label>Low Stock Alert</label>
                            <input type="number" name="low_stock_threshold" id="productLowStock" min="1" value="10">
                        </div>
                        
                        <div class="form-group">
                            <label>Unit</label>
                            <select name="unit" id="productUnit">
                                <option value="tray">Tray</option>
                                <option value="pack">Pack</option>
                                <option value="lb">Pound</option>
                                <option value="oz">Ounce</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Image</label>
                            <div class="image-upload" id="imageUpload">
                                <input type="file" name="image" id="productImage" accept="image/*" onchange="previewImage(this)">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Click to upload</span>
                                </div>
                                <img id="imagePreview" src="" alt="" style="display: none;">
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="description" id="productDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_featured" id="productFeatured">
                                <span>Featured Product</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeProductModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        let currentPage = 1;
        let categories = [];
        
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
        });

        const debouncedSearch = debounce(() => {
            currentPage = 1;
            loadProducts();
        }, 300);

        async function loadProducts() {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            try {
                const params = new URLSearchParams({
                    action: 'list',
                    page: currentPage,
                    limit: 10,
                    search,
                    category,
                    status
                });
                
                const response = await fetch(`api/products.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    categories = result.data.categories;
                    updateCategoryFilter();
                    renderProducts(result.data.products);
                    renderPagination(result.data.pagination);
                }
            } catch (error) {
                showToast('Error loading products', 'error');
            }
        }

        function updateCategoryFilter() {
            const filterSelect = document.getElementById('categoryFilter');
            const formSelect = document.getElementById('productCategory');
            
            const filterOptions = '<option value="">All Categories</option>' + 
                categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            filterSelect.innerHTML = filterOptions;
            formSelect.innerHTML = '<option value="">Select Category</option>' + filterOptions;
        }

        function renderProducts(products) {
            const tbody = document.getElementById('productsTable');
            
            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted p-5">No products found</td></tr>';
                return;
            }
            
            tbody.innerHTML = products.map(p => `
                <tr>
                    <td>
                        <div class="product-cell">
                            <div class="product-thumb">
                                ${p.image ? `<img src="../assets/uploads/${p.image}" alt="">` : '<i class="fas fa-leaf"></i>'}
                            </div>
                            <div class="product-info">
                                <strong>${p.name}</strong>
                            </div>
                        </div>
                    </td>
                    <td><code>${p.sku || 'N/A'}</code></td>
                    <td>${p.category_name || 'Uncategorized'}</td>
                    <td>
                        <strong>${formatCurrency(p.price)}</strong>
                        ${p.compare_price ? `<span class="compare-price">${formatCurrency(p.compare_price)}</span>` : ''}
                    </td>
                    <td>
                        <span class="stock-badge ${p.stock_quantity <= p.low_stock_threshold ? 'low' : ''}">
                            ${p.stock_quantity} ${p.unit}
                        </span>
                    </td>
                    <td>
                        <label class="switch">
                            <input type="checkbox" ${p.is_featured ? 'checked' : ''} onchange="toggleFeatured(${p.id})">
                            <span class="slider"></span>
                        </label>
                    </td>
                    <td>
                        <span class="badge ${p.is_active ? 'badge-delivered' : 'badge-cancelled'}">
                            ${p.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" onclick="editProduct(${p.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon danger" onclick="deleteProduct(${p.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function renderPagination(pagination) {
            const container = document.getElementById('pagination');
            if (pagination.pages <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '';
            
            if (currentPage > 1) {
                html += `<button class="page-btn" onclick="goToPage(${currentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;
            }
            
            for (let i = 1; i <= pagination.pages; i++) {
                if (i === 1 || i === pagination.pages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += '<span class="page-ellipsis">...</span>';
                }
            }
            
            if (currentPage < pagination.pages) {
                html += `<button class="page-btn" onclick="goToPage(${currentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;
            }
            
            container.innerHTML = html;
        }

        function goToPage(page) {
            currentPage = page;
            loadProducts();
        }

        function openProductModal(product = null) {
            const modal = document.getElementById('productModal');
            const form = document.getElementById('productForm');
            const title = document.getElementById('modalTitle');
            
            form.reset();
            document.getElementById('productId').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('uploadPlaceholder').style.display = 'flex';
            
            if (product) {
                title.textContent = 'Edit Product';
                document.getElementById('productId').value = product.id;
                document.getElementById('productName').value = product.name;
                document.getElementById('productCategory').value = product.category_id || '';
                document.getElementById('productSku').value = product.sku || '';
                document.getElementById('productPrice').value = product.price;
                document.getElementById('productComparePrice').value = product.compare_price || '';
                document.getElementById('productCostPrice').value = product.cost_price || '';
                document.getElementById('productStock').value = product.stock_quantity;
                document.getElementById('productLowStock').value = product.low_stock_threshold;
                document.getElementById('productUnit').value = product.unit;
                document.getElementById('productDescription').value = product.description || '';
                document.getElementById('productFeatured').checked = product.is_featured;
                
                if (product.image) {
                    document.getElementById('imagePreview').src = `../assets/uploads/${product.image}`;
                    document.getElementById('imagePreview').style.display = 'block';
                    document.getElementById('uploadPlaceholder').style.display = 'none';
                }
            } else {
                title.textContent = 'Add Product';
            }
            
            modal.classList.add('active');
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                    document.getElementById('uploadPlaceholder').style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        async function editProduct(id) {
            try {
                const response = await fetch(`api/products.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    openProductModal(result.data);
                }
            } catch (error) {
                showToast('Error loading product', 'error');
            }
        }

        async function saveProduct(e) {
            e.preventDefault();
            
            const form = document.getElementById('productForm');
            const formData = new FormData(form);
            const isEdit = formData.get('id');
            
            formData.set('action', isEdit ? 'update' : 'create');
            
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner"></span> Saving...';
            
            try {
                const response = await fetch('api/products.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    closeProductModal();
                    loadProducts();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error saving product', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Product';
            }
        }

        async function deleteProduct(id) {
            if (!confirmDelete('Are you sure you want to delete this product?')) return;
            
            try {
                const response = await fetch(`api/products.php?action=delete&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    loadProducts();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error deleting product', 'error');
            }
        }

        async function toggleFeatured(id) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                
                const response = await fetch('api/products.php?action=toggle_featured', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    loadProducts();
                }
            } catch (error) {
                showToast('Error updating product', 'error');
            }
        }
    </script>
</body>
</html>
