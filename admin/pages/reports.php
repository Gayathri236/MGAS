<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .report-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 10px;
        }
        
        .report-tab {
            padding: 10px 20px;
            border: none;
            background: none;
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-500);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -12px;
            transition: all 0.2s;
        }
        
        .report-tab:hover {
            color: var(--primary);
        }
        
        .report-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .report-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .filter-group label {
            font-size: 12px;
            font-weight: 500;
            color: var(--gray-600);
        }
        
        .filter-group input,
        .filter-group select {
            padding: 10px 14px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .report-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .summary-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 22px;
        }
        
        .summary-card .icon.green { background: rgba(16, 185, 129, 0.1); color: #10B981; }
        .summary-card .icon.blue { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
        .summary-card .icon.yellow { background: rgba(245, 158, 11, 0.1); color: #F59E0B; }
        .summary-card .icon.purple { background: rgba(139, 92, 246, 0.1); color: #8B5CF6; }
        
        .summary-card h3 {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 5px;
        }
        
        .summary-card p {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .report-section {
            display: none;
        }
        
        .report-section.active {
            display: block;
        }
        
        .top-products-table {
            width: 100%;
        }
        
        .top-product-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .top-product-row:last-child {
            border-bottom: none;
        }
        
        .product-rank {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .product-details {
            flex: 1;
        }
        
        .product-details strong {
            display: block;
            color: var(--gray-800);
            margin-bottom: 4px;
        }
        
        .product-details span {
            font-size: 12px;
            color: var(--gray-500);
        }
        
        .product-stats {
            text-align: right;
        }
        
        .product-stats strong {
            display: block;
            color: var(--primary);
            font-size: 16px;
        }
        
        .product-stats span {
            font-size: 12px;
            color: var(--gray-500);
        }
        
        .progress-bar-container {
            margin-top: 10px;
        }
        
        .progress-bar {
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-bar-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        @media (max-width: 1024px) {
            .report-summary {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .chart-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .report-summary {
                grid-template-columns: 1fr;
            }
            
            .report-tabs {
                overflow-x: auto;
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
                        <h1>Reports & Analytics</h1>
                        <p>Business insights and performance metrics</p>
                    </div>
                </div>

                <div class="report-tabs">
                    <button class="report-tab active" onclick="showSection('sales')">
                        <i class="fas fa-chart-line"></i> Sales Overview
                    </button>
                    <button class="report-tab" onclick="showSection('products')">
                        <i class="fas fa-fire"></i> Top Products
                    </button>
                    <button class="report-tab" onclick="showSection('customers')">
                        <i class="fas fa-users"></i> Top Customers
                    </button>
                    <button class="report-tab" onclick="showSection('monthly')">
                        <i class="fas fa-calendar"></i> Monthly Report
                    </button>
                    <button class="report-tab" onclick="showSection('inventory')">
                        <i class="fas fa-boxes"></i> Inventory Report
                    </button>
                </div>

                <div id="salesSection" class="report-section active">
                    <div class="report-filters">
                        <div class="filter-group">
                            <label>Start Date</label>
                            <input type="date" id="salesStartDate" value="<?php echo date('Y-m-01'); ?>">
                        </div>
                        <div class="filter-group">
                            <label>End Date</label>
                            <input type="date" id="salesEndDate" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button class="btn btn-primary" onclick="loadSalesReport()">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button class="btn btn-outline" onclick="exportReport('sales')">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>

                    <div class="report-summary" id="salesSummary">
                        <div class="summary-card">
                            <div class="icon green"><i class="fas fa-dollar-sign"></i></div>
                            <h3 id="totalSales">$0.00</h3>
                            <p>Total Sales</p>
                        </div>
                        <div class="summary-card">
                            <div class="icon blue"><i class="fas fa-shopping-cart"></i></div>
                            <h3 id="totalOrders">0</h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="summary-card">
                            <div class="icon yellow"><i class="fas fa-chart-line"></i></div>
                            <h3 id="avgOrderValue">$0.00</h3>
                            <p>Avg Order Value</p>
                        </div>
                        <div class="summary-card">
                            <div class="icon purple"><i class="fas fa-check-circle"></i></div>
                            <h3 id="paidAmount">$0.00</h3>
                            <p>Paid Amount</p>
                        </div>
                    </div>

                    <div class="chart-grid">
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-chart-area"></i> Sales Trend</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="salesTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-chart-pie"></i> Order Status</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="productsSection" class="report-section">
                    <div class="report-filters">
                        <div class="filter-group">
                            <label>Start Date</label>
                            <input type="date" id="productsStartDate" value="<?php echo date('Y-m-01'); ?>">
                        </div>
                        <div class="filter-group">
                            <label>End Date</label>
                            <input type="date" id="productsEndDate" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button class="btn btn-primary" onclick="loadTopProducts()">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button class="btn btn-outline" onclick="exportReport('products')">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-fire"></i> Best Selling Products</h3>
                        </div>
                        <div class="card-body" id="topProductsList">
                            <div class="text-center text-muted p-5">Loading...</div>
                        </div>
                    </div>
                </div>

                <div id="customersSection" class="report-section">
                    <div class="report-filters">
                        <button class="btn btn-outline" onclick="exportReport('customers')">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-body" style="padding: 0;">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Contact</th>
                                            <th>Orders</th>
                                            <th>Total Spent</th>
                                            <th>Last Order</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topCustomersTable">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted p-5">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="monthlySection" class="report-section">
                    <div class="report-filters">
                        <div class="filter-group">
                            <label>Year</label>
                            <select id="reportYear" onchange="loadMonthlyReport()">
                                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $y == date('Y') ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="report-summary" id="monthlySummary">
                        <div class="summary-card">
                            <div class="icon green"><i class="fas fa-dollar-sign"></i></div>
                            <h3 id="yearTotalRevenue">$0.00</h3>
                            <p>Total Revenue</p>
                        </div>
                        <div class="summary-card">
                            <div class="icon blue"><i class="fas fa-shopping-cart"></i></div>
                            <h3 id="yearTotalOrders">0</h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="summary-card">
                            <div class="icon yellow"><i class="fas fa-chart-line"></i></div>
                            <h3 id="yearAvgMonthly">$0.00</h3>
                            <p>Avg Monthly</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-bar"></i> Monthly Revenue</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="inventorySection" class="report-section">
                    <div class="report-filters">
                        <button class="btn btn-outline" onclick="exportReport('inventory')">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>

                    <div class="report-summary" id="inventorySummary">
                        <div class="summary-card">
                            <div class="icon green"><i class="fas fa-boxes"></i></div>
                            <h3 id="invTotalProducts">0</h3>
                            <p>Total Products</p>
                        </div>
                        <div class="summary-card">
                            <div class="icon blue"><i class="fas fa-dollar-sign"></i></div>
                            <h3 id="invTotalValue">$0.00</h3>
                            <p>Stock Value</p>
                        </div>
                        <div class="summary-card">
                            <div class="icon yellow"><i class="fas fa-exclamation-triangle"></i></div>
                            <h3 id="invLowStock">0</h3>
                            <p>Low Stock</p>
                        </div>
                        <div class="summary-card">
                            <div class="icon red"><i class="fas fa-times-circle"></i></div>
                            <h3 id="invOutOfStock">0</h3>
                            <p>Out of Stock</p>
                        </div>
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
                                            <th>Stock</th>
                                            <th>Stock Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inventoryTable">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted p-5">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        let salesTrendChart, statusChart, monthlyChart;
        
        document.addEventListener('DOMContentLoaded', function() {
            loadSalesReport();
            loadTopCustomers();
            loadMonthlyReport();
            loadInventoryReport();
        });

        function showSection(section) {
            document.querySelectorAll('.report-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.report-tab').forEach(t => t.classList.remove('active'));
            
            document.getElementById(section + 'Section').classList.add('active');
            event.target.classList.add('active');
        }

        async function loadSalesReport() {
            const startDate = document.getElementById('salesStartDate').value;
            const endDate = document.getElementById('salesEndDate').value;
            
            try {
                const params = new URLSearchParams({
                    action: 'sales_overview',
                    start_date: startDate,
                    end_date: endDate
                });
                
                const response = await fetch(`api/reports.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    updateSalesSummary(result.data.summary);
                    renderSalesTrend(result.data.daily_sales);
                    renderStatusChart(result.data.status_breakdown);
                }
            } catch (error) {
                showToast('Error loading sales report', 'error');
            }
        }

        function updateSalesSummary(summary) {
            document.getElementById('totalSales').textContent = formatCurrency(summary.total_sales || 0);
            document.getElementById('totalOrders').textContent = summary.total_orders || 0;
            document.getElementById('avgOrderValue').textContent = formatCurrency(summary.avg_order_value || 0);
            document.getElementById('paidAmount').textContent = formatCurrency(summary.paid_amount || 0);
        }

        function renderSalesTrend(data) {
            const ctx = document.getElementById('salesTrendChart').getContext('2d');
            const labels = data.map(d => formatDate(d.date));
            const sales = data.map(d => parseFloat(d.sales));
            
            if (salesTrendChart) salesTrendChart.destroy();
            
            salesTrendChart = new Chart(ctx, {
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
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => '$' + v } }
                    }
                }
            });
        }

        function renderStatusChart(data) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            const labels = data.map(d => capitalize(d.status));
            const values = data.map(d => d.count);
            const colors = data.map(d => getStatusColor(d.status));
            
            if (statusChart) statusChart.destroy();
            
            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: { labels, datasets: [{ data: values, backgroundColor: colors }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        async function loadTopProducts() {
            const startDate = document.getElementById('productsStartDate').value;
            const endDate = document.getElementById('productsEndDate').value;
            
            try {
                const params = new URLSearchParams({
                    action: 'top_products',
                    start_date: startDate,
                    end_date: endDate
                });
                
                const response = await fetch(`api/reports.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    renderTopProducts(result.data.products);
                }
            } catch (error) {
                showToast('Error loading top products', 'error');
            }
        }

        function renderTopProducts(products) {
            const container = document.getElementById('topProductsList');
            
            if (products.length === 0) {
                container.innerHTML = '<div class="text-center text-muted p-5">No products found</div>';
                return;
            }
            
            container.innerHTML = products.map((p, i) => `
                <div class="top-product-row">
                    <div class="product-rank">${i + 1}</div>
                    <div class="product-details">
                        <strong>${p.name}</strong>
                        <span>${p.total_quantity} units sold | ${p.order_count} orders</span>
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: ${p.percentage}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="product-stats">
                        <strong>${formatCurrency(p.total_revenue)}</strong>
                        <span>${p.percentage}% of total</span>
                    </div>
                </div>
            `).join('');
        }

        async function loadTopCustomers() {
            try {
                const response = await fetch('api/reports.php?action=top_customers');
                const result = await response.json();
                
                if (result.success) {
                    renderTopCustomers(result.data);
                }
            } catch (error) {
                showToast('Error loading customers', 'error');
            }
        }

        function renderTopCustomers(customers) {
            const tbody = document.getElementById('topCustomersTable');
            
            if (customers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-5">No customers found</td></tr>';
                return;
            }
            
            tbody.innerHTML = customers.map(c => `
                <tr>
                    <td><strong>${c.first_name} ${c.last_name}</strong></td>
                    <td>
                        <div>${c.email}</div>
                        <span class="text-muted">${c.phone || 'N/A'}</span>
                    </td>
                    <td>${c.total_orders}</td>
                    <td><strong>${formatCurrency(c.total_spent)}</strong></td>
                    <td>${c.last_order_date ? formatDate(c.last_order_date) : 'N/A'}</td>
                </tr>
            `).join('');
        }

        async function loadMonthlyReport() {
            const year = document.getElementById('reportYear').value;
            
            try {
                const params = new URLSearchParams({ action: 'monthly_report', year });
                const response = await fetch(`api/reports.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('yearTotalRevenue').textContent = formatCurrency(result.data.total_revenue);
                    document.getElementById('yearTotalOrders').textContent = result.data.total_orders;
                    document.getElementById('yearAvgMonthly').textContent = formatCurrency(result.data.avg_monthly);
                    renderMonthlyChart(result.data.months);
                }
            } catch (error) {
                showToast('Error loading monthly report', 'error');
            }
        }

        function renderMonthlyChart(months) {
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            const labels = months.map(m => m.month_name);
            const revenue = months.map(m => parseFloat(m.revenue));
            
            if (monthlyChart) monthlyChart.destroy();
            
            monthlyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenue,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => '$' + v } }
                    }
                }
            });
        }

        async function loadInventoryReport() {
            try {
                const response = await fetch('api/reports.php?action=inventory_report');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('invTotalProducts').textContent = result.data.summary.total_products;
                    document.getElementById('invTotalValue').textContent = formatCurrency(result.data.summary.total_value);
                    document.getElementById('invLowStock').textContent = result.data.summary.low_stock_count;
                    document.getElementById('invOutOfStock').textContent = result.data.summary.out_of_stock_count;
                    renderInventoryTable(result.data.products);
                }
            } catch (error) {
                showToast('Error loading inventory report', 'error');
            }
        }

        function renderInventoryTable(products) {
            const tbody = document.getElementById('inventoryTable');
            
            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-5">No products found</td></tr>';
                return;
            }
            
            tbody.innerHTML = products.map(p => `
                <tr>
                    <td><strong>${p.name}</strong></td>
                    <td><code>${p.sku || 'N/A'}</code></td>
                    <td>${p.category || 'Uncategorized'}</td>
                    <td>
                        <span class="stock-badge ${p.stock_quantity <= p.low_stock_threshold ? 'low' : ''}">
                            ${p.stock_quantity}
                        </span>
                    </td>
                    <td>${formatCurrency(p.stock_value)}</td>
                </tr>
            `).join('');
        }

        function exportReport(type) {
            window.location.href = `api/reports.php?action=export&type=${type}&format=csv`;
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

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }
    </script>
</body>
</html>
