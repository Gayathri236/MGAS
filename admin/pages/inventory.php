<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .inventory-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .inv-stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .inv-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        
        .inv-stat-icon.green { background: rgba(16, 185, 129, 0.1); color: #10B981; }
        .inv-stat-icon.yellow { background: rgba(245, 158, 11, 0.1); color: #F59E0B; }
        .inv-stat-icon.red { background: rgba(239, 68, 68, 0.1); color: #EF4444; }
        .inv-stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
        
        .inv-stat-info h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--gray-800);
        }
        
        .inv-stat-info p {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .stock-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .stock-status.in-stock {
            background: #D1FAE5;
            color: #059669;
        }
        
        .stock-status.low-stock {
            background: #FEF3C7;
            color: #D97706;
        }
        
        .stock-status.out-of-stock {
            background: #FEE2E2;
            color: #DC2626;
        }
        
        .stock-progress {
            width: 100px;
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .stock-progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        .stock-progress-bar.high { background: #10B981; }
        .stock-progress-bar.medium { background: #F59E0B; }
        .stock-progress-bar.low { background: #EF4444; }
        
        .inventory-row {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .inventory-thumb {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .inventory-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .inventory-thumb i {
            color: var(--primary);
            font-size: 20px;
        }
        
        .update-stock-form {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .update-stock-form input {
            width: 80px;
            padding: 8px 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 14px;
            text-align: center;
        }
        
        .update-stock-form button {
            padding: 8px 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }
        
        .update-stock-form button:hover {
            background: var(--primary-dark);
        }
        
        .logs-section {
            margin-top: 24px;
        }
        
        @media (max-width: 1024px) {
            .inventory-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .inventory-stats {
                grid-template-columns: 1fr;
            }
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
                        <h1>Inventory</h1>
                        <p>Track and manage product stock levels</p>
                    </div>
                </div>

                <div class="inventory-stats" id="inventoryStats">
                    <div class="inv-stat-card">
                        <div class="inv-stat-icon green">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="inv-stat-info">
                            <h3 id="totalProducts">0</h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    <div class="inv-stat-card">
                        <div class="inv-stat-icon blue">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="inv-stat-info">
                            <h3 id="inStock">0</h3>
                            <p>In Stock</p>
                        </div>
                    </div>
                    <div class="inv-stat-card">
                        <div class="inv-stat-icon yellow">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="inv-stat-info">
                            <h3 id="lowStock">0</h3>
                            <p>Low Stock</p>
                        </div>
                    </div>
                    <div class="inv-stat-card">
                        <div class="inv-stat-icon red">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="inv-stat-info">
                            <h3 id="outOfStock">0</h3>
                            <p>Out of Stock</p>
                        </div>
                    </div>
                </div>

                <div class="filters-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search products..." onkeyup="debouncedSearch()">
                    </div>
                    <select id="alertFilter" onchange="loadInventory()">
                        <option value="">All Products</option>
                        <option value="low">Low Stock Only</option>
                        <option value="out">Out of Stock Only</option>
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
                                        <th>Current Stock</th>
                                        <th>Status</th>
                                        <th>Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryTable">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted p-5">Loading inventory...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card logs-section">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Recent Stock Changes</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Change</th>
                                        <th>Reason</th>
                                        <th>Admin</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="logsTable">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-3">Loading logs...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        const debouncedSearch = debounce(() => {
            loadInventory();
        }, 300);

        document.addEventListener('DOMContentLoaded', function() {
            loadInventory();
            loadLogs();
        });

        async function loadInventory() {
            const search = document.getElementById('searchInput').value;
            const alert = document.getElementById('alertFilter').value;
            
            try {
                const params = new URLSearchParams({
                    action: 'list',
                    search,
                    alert
                });
                
                const response = await fetch(`api/inventory.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    updateStats(result.data.summary);
                    renderInventory(result.data.products);
                }
            } catch (error) {
                showToast('Error loading inventory', 'error');
            }
        }

        function updateStats(summary) {
            document.getElementById('totalProducts').textContent = summary.total_products;
            document.getElementById('inStock').textContent = summary.in_stock;
            document.getElementById('lowStock').textContent = summary.low_stock;
            document.getElementById('outOfStock').textContent = summary.out_of_stock;
        }

        function renderInventory(products) {
            const tbody = document.getElementById('inventoryTable');
            
            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-5">No products found</td></tr>';
                return;
            }
            
            tbody.innerHTML = products.map(p => {
                const statusClass = p.stock_status === 'in_stock' ? 'in-stock' : 
                                   p.stock_status === 'low_stock' ? 'low-stock' : 'out-of-stock';
                const statusText = p.stock_status === 'in_stock' ? 'In Stock' : 
                                  p.stock_status === 'low_stock' ? 'Low Stock' : 'Out of Stock';
                const maxStock = Math.max(p.stock_quantity, p.low_stock_threshold * 2);
                const progressPercent = (p.stock_quantity / maxStock) * 100;
                const progressClass = progressPercent > 50 ? 'high' : progressPercent > 20 ? 'medium' : 'low';
                
                return `
                    <tr>
                        <td>
                            <div class="inventory-row">
                                <div class="inventory-thumb">
                                    ${p.image ? `<img src="../assets/uploads/${p.image}" alt="">` : '<i class="fas fa-leaf"></i>'}
                                </div>
                                <div>
                                    <strong>${p.name}</strong>
                                    <div style="font-size: 12px; color: var(--gray-500);">${formatCurrency(p.price)}</div>
                                </div>
                            </div>
                        </td>
                        <td><code>${p.sku || 'N/A'}</code></td>
                        <td>${p.category_name || 'Uncategorized'}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div>
                                    <strong style="font-size: 18px;">${p.stock_quantity}</strong>
                                    <span style="color: var(--gray-500); font-size: 12px;">${p.unit}</span>
                                </div>
                                <div class="stock-progress">
                                    <div class="stock-progress-bar ${progressClass}" style="width: ${Math.min(progressPercent, 100)}%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="stock-status ${statusClass}">
                                <i class="fas ${statusClass === 'in-stock' ? 'fa-check' : statusClass === 'low-stock' ? 'fa-exclamation' : 'fa-times'}"></i>
                                ${statusText}
                            </span>
                        </td>
                        <td>${formatCurrency(p.stock_quantity * p.cost_price)}</td>
                        <td>
                            <button class="btn btn-outline" style="padding: 8px 12px; font-size: 12px;" onclick="openUpdateModal(${p.id}, '${p.name}', ${p.stock_quantity})">
                                <i class="fas fa-edit"></i> Update
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function loadLogs() {
            try {
                const response = await fetch('api/inventory.php?action=get_logs&limit=20');
                const result = await response.json();
                
                if (result.success) {
                    renderLogs(result.data);
                }
            } catch (error) {
                console.error('Error loading logs:', error);
            }
        }

        function renderLogs(logs) {
            const tbody = document.getElementById('logsTable');
            
            if (logs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-3">No logs found</td></tr>';
                return;
            }
            
            tbody.innerHTML = logs.map(log => {
                const changeClass = log.quantity_change > 0 ? 'text-success' : log.quantity_change < 0 ? 'text-danger' : '';
                const changeText = log.quantity_change > 0 ? `+${log.quantity_change}` : log.quantity_change;
                
                return `
                    <tr>
                        <td><strong>${log.product_name}</strong></td>
                        <td><span class="${changeClass}" style="font-weight: 600;">${changeText} units</span></td>
                        <td>${log.reason || 'N/A'}</td>
                        <td>${log.admin_name || 'System'}</td>
                        <td>${formatDateTime(log.created_at)}</td>
                    </tr>
                `;
            }).join('');
        }

        function openUpdateModal(id, name, currentStock) {
            const newStock = prompt(`Update stock for "${name}"\nCurrent stock: ${currentStock}\n\nEnter new stock quantity:`, currentStock);
            
            if (newStock === null) return;
            
            const quantity = parseInt(newStock);
            if (isNaN(quantity) || quantity < 0) {
                showToast('Please enter a valid quantity', 'error');
                return;
            }
            
            const reason = prompt('Reason for stock update (optional):', '');
            
            updateStock(id, quantity, reason);
        }

        async function updateStock(id, quantity, reason) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('quantity', quantity);
                formData.append('reason', reason || 'Manual update');
                
                const response = await fetch('api/inventory.php?action=update_stock', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    loadInventory();
                    loadLogs();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error updating stock', 'error');
            }
        }
    </script>
</body>
</html>
