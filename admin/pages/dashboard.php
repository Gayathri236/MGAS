<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include __DIR__ . '/header.php'; ?>
            
            <div class="dashboard-content">
                <div class="page-header">
                    <div>
                        <h1>Dashboard</h1>
                        <p>Welcome back, <?php echo $_SESSION['admin_name']; ?>!</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="stats-grid" id="statsGrid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #059669);">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalRevenue">$0.00</h3>
                            <p>Total Revenue</p>
                            <span class="stat-change positive"><i class="fas fa-arrow-up"></i> This month</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalOrders">0</h3>
                            <p>Total Orders</p>
                            <span class="stat-change" id="ordersChange"><i class="fas fa-arrow-up"></i> 0 this week</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalCustomers">0</h3>
                            <p>Customers</p>
                            <span class="stat-change positive"><i class="fas fa-arrow-up"></i> Active</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED);">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalProducts">0</h3>
                            <p>Products</p>
                            <span class="stat-change"><i class="fas fa-box"></i> Active</span>
                        </div>
                    </div>
                </div>

                <div class="alert-cards">
                    <div class="alert-card warning">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong id="pendingOrders">0</strong> Pending Orders
                        </div>
                        <a href="orders.php" class="alert-link">View <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="alert-card danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong id="lowStockCount">0</strong> Low Stock Items
                        </div>
                        <a href="inventory.php" class="alert-link">Restock <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="card sales-chart-card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-line"></i> Sales Overview (Last 7 Days)</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="card order-status-card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-pie"></i> Order Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="orderStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid-2">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-shopping-bag"></i> Recent Orders</h3>
                            <a href="orders.php" class="btn-link">View All <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentOrdersTable">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-fire"></i> Top Products</h3>
                            <a href="products.php" class="btn-link">View All <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="card-body">
                            <div class="top-products-list" id="topProductsList">
                                <div class="text-center text-muted p-3">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-circle"></i> Low Stock Alert</h3>
                        <a href="inventory.php" class="btn-link">Manage Inventory <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="low-stock-grid" id="lowStockGrid">
                            <div class="text-center text-muted p-3">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboard();
        });

        async function loadDashboard() {
            try {
                const response = await fetch('api/dashboard.php', {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                });
                const result = await response.json();
                
                if (result.success) {
                    updateStats(result.data.stats);
                    updateRecentOrders(result.data.recent_orders);
                    updateTopProducts(result.data.top_products);
                    updateLowStock(result.data.low_stock);
                    renderSalesChart(result.data.sales_chart);
                    renderOrderStatusChart(result.data.order_status);
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        function updateStats(stats) {
            document.getElementById('totalRevenue').textContent = formatCurrency(stats.total_revenue);
            document.getElementById('totalOrders').textContent = stats.total_orders;
            document.getElementById('totalCustomers').textContent = stats.total_customers;
            document.getElementById('totalProducts').textContent = stats.total_products;
            document.getElementById('pendingOrders').textContent = stats.pending_orders;
            document.getElementById('lowStockCount').textContent = stats.low_stock_count;
            document.getElementById('ordersChange').innerHTML = `<i class="fas fa-arrow-up"></i> ${stats.week_orders} this week`;
        }

        function updateRecentOrders(orders) {
            const tbody = document.getElementById('recentOrdersTable');
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No orders found</td></tr>';
                return;
            }
            
            tbody.innerHTML = orders.map(order => `
                <tr>
                    <td><strong>${order.order_number}</strong></td>
                    <td>${order.first_name} ${order.last_name}</td>
                    <td>${formatCurrency(order.total)}</td>
                    <td><span class="badge badge-${order.status}">${capitalize(order.status)}</span></td>
                    <td>${formatDate(order.created_at)}</td>
                </tr>
            `).join('');
        }

        function updateTopProducts(products) {
            const container = document.getElementById('topProductsList');
            if (products.length === 0) {
                container.innerHTML = '<div class="text-center text-muted p-3">No products found</div>';
                return;
            }
            
            container.innerHTML = products.map((product, index) => `
                <div class="top-product-item">
                    <div class="product-rank">${index + 1}</div>
                    <div class="product-info">
                        <strong>${product.name}</strong>
                        <span>${product.total_sold} sold</span>
                    </div>
                    <div class="product-revenue">${formatCurrency(product.revenue)}</div>
                </div>
            `).join('');
        }

        function updateLowStock(products) {
            const container = document.getElementById('lowStockGrid');
            if (products.length === 0) {
                container.innerHTML = '<div class="text-center text-success p-3"><i class="fas fa-check-circle"></i> All products are well stocked!</div>';
                return;
            }
            
            container.innerHTML = products.map(product => `
                <div class="low-stock-item">
                    <div class="low-stock-info">
                        <strong>${product.name}</strong>
                        <span class="text-muted">SKU: ${product.sku || 'N/A'}</span>
                    </div>
                    <div class="low-stock-qty">
                        <span class="qty-badge ${product.stock_quantity <= 5 ? 'critical' : 'warning'}">${product.stock_quantity} left</span>
                    </div>
                </div>
            `).join('');
        }

        let salesChart, statusChart;

        function renderSalesChart(data) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            const labels = [];
            const sales = [];
            
            for (let i = 6; i >= 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i);
                const dateStr = date.toISOString().split('T')[0];
                labels.push(date.toLocaleDateString('en-US', { weekday: 'short' }));
                
                const dayData = data.find(d => d.date === dateStr);
                sales.push(dayData ? parseFloat(dayData.sales) : 0);
            }
            
            if (salesChart) salesChart.destroy();
            
            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Sales',
                        data: sales,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => '$' + value
                            }
                        }
                    }
                }
            });
        }

        function renderOrderStatusChart(data) {
            const ctx = document.getElementById('orderStatusChart').getContext('2d');
            const labels = data.map(d => capitalize(d.status));
            const values = data.map(d => d.count);
            const colors = data.map(d => getStatusColor(d.status));
            
            if (statusChart) statusChart.destroy();
            
            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function getStatusColor(status) {
            const colors = {
                'pending': '#FFC107',
                'confirmed': '#17A2B8',
                'processing': '#6610F2',
                'shipped': '#007BFF',
                'delivered': '#28A745',
                'cancelled': '#DC3545',
                'refunded': '#6C757D'
            };
            return colors[status] || '#6C757D';
        }

        function formatCurrency(amount) {
            return '$' + parseFloat(amount).toFixed(2);
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    </script>
</body>
</html>
