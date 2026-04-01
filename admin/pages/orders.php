<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .order-number {
            font-weight: 600;
            color: var(--primary);
        }
        
        .customer-cell {
            display: flex;
            flex-direction: column;
        }
        
        .customer-cell strong {
            color: var(--gray-800);
        }
        
        .customer-cell span {
            font-size: 12px;
            color: var(--gray-500);
        }
        
        .order-total {
            font-weight: 600;
            font-size: 15px;
        }
        
        .tracking-input {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .tracking-input input {
            flex: 1;
            padding: 10px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 13px;
        }
        
        .tracking-input button {
            padding: 10px 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .bill-preview {
            background: white;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        
        .order-details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .order-details-section h4 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .order-details-section p {
            font-size: 14px;
            margin: 6px 0;
            color: var(--gray-600);
        }
        
        .order-details-section strong {
            color: var(--gray-800);
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .order-items-table th {
            background: var(--gray-100);
            padding: 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            color: var(--gray-500);
        }
        
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid var(--gray-100);
            font-size: 14px;
        }
        
        .order-summary {
            background: var(--gray-50);
            padding: 20px;
            border-radius: 10px;
            max-width: 300px;
            margin-left: auto;
        }
        
        .order-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .order-summary-row.total {
            border-top: 2px solid var(--gray-200);
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .status-select {
            padding: 8px 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            min-width: 140px;
        }
        
        .status-select:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        @media (max-width: 768px) {
            .order-details-grid {
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
                        <h1>Orders</h1>
                        <p>Manage customer orders</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openCreateOrderModal()">
                            <i class="fas fa-plus"></i> Create Order
                        </button>
                    </div>
                </div>

                <div class="filters-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search orders..." onkeyup="debouncedSearch()">
                    </div>
                    <select id="statusFilter" onchange="loadOrders()">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select id="paymentFilter" onchange="loadOrders()">
                        <option value="">All Payments</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div class="card">
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="ordersTable">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted p-5">Loading orders...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="pagination" id="pagination"></div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="orderModal">
        <div class="modal modal-xl">
            <div class="modal-header">
                <h3>Order Details</h3>
                <button class="modal-close" onclick="closeOrderModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="orderModalContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeOrderModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadBill()">
                    <i class="fas fa-download"></i> Download Bill
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="billModal">
        <div class="modal modal-xl">
            <div class="modal-header">
                <h3>Invoice Preview</h3>
                <button class="modal-close" onclick="closeBillModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="billFrame" style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeBillModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="printBill()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        let currentPage = 1;
        let currentOrderId = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
        });

        const debouncedSearch = debounce(() => {
            currentPage = 1;
            loadOrders();
        }, 300);

        async function loadOrders() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const payment = document.getElementById('paymentFilter').value;
            
            try {
                const params = new URLSearchParams({
                    action: 'list',
                    page: currentPage,
                    limit: 10,
                    search,
                    status,
                    payment
                });
                
                const response = await fetch(`api/orders.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    renderOrders(result.data.orders);
                    renderPagination(result.data.pagination);
                }
            } catch (error) {
                showToast('Error loading orders', 'error');
            }
        }

        function renderOrders(orders) {
            const tbody = document.getElementById('ordersTable');
            
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted p-5">No orders found</td></tr>';
                return;
            }
            
            tbody.innerHTML = orders.map(o => `
                <tr>
                    <td><span class="order-number">${o.order_number}</span></td>
                    <td>
                        <div class="customer-cell">
                            <strong>${o.first_name} ${o.last_name}</strong>
                            <span>${o.email}</span>
                        </div>
                    </td>
                    <td>${o.items_count} items</td>
                    <td><span class="order-total">${formatCurrency(o.total)}</span></td>
                    <td>
                        <select class="status-select" onchange="updateStatus(${o.id}, this.value)" style="border-color: ${getStatusColor(o.status)};">
                            <option value="pending" ${o.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="confirmed" ${o.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                            <option value="processing" ${o.status === 'processing' ? 'selected' : ''}>Processing</option>
                            <option value="shipped" ${o.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                            <option value="delivered" ${o.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                            <option value="cancelled" ${o.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            <option value="refunded" ${o.status === 'refunded' ? 'selected' : ''}>Refunded</option>
                        </select>
                    </td>
                    <td>
                        <span class="badge badge-${o.payment_status}">${capitalize(o.payment_status)}</span>
                    </td>
                    <td>${formatDate(o.created_at)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" onclick="viewOrder(${o.id})" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" onclick="updateTrackingModal(${o.id}, '${o.tracking_number || ''}')" title="Tracking">
                                <i class="fas fa-truck"></i>
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
            loadOrders();
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

        async function viewOrder(id) {
            currentOrderId = id;
            
            try {
                const response = await fetch(`api/orders.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    openOrderModal(result.data);
                }
            } catch (error) {
                showToast('Error loading order', 'error');
            }
        }

        function openOrderModal(data) {
            const modal = document.getElementById('orderModal');
            const content = document.getElementById('orderModalContent');
            
            const order = data.order;
            const items = data.items;
            
            content.innerHTML = `
                <div class="order-details-grid">
                    <div class="order-details-section">
                        <h4>Order Information</h4>
                        <p><strong>Order #:</strong> ${order.order_number}</p>
                        <p><strong>Date:</strong> ${formatDateTime(order.created_at)}</p>
                        <p><strong>Payment:</strong> ${order.payment_method || 'N/A'}</p>
                        <p><strong>Payment Status:</strong> <span class="badge badge-${order.payment_status}">${capitalize(order.payment_status)}</span></p>
                        ${order.notes ? `<p><strong>Notes:</strong> ${order.notes}</p>` : ''}
                        ${order.admin_notes ? `<p><strong>Admin Notes:</strong> ${order.admin_notes}</p>` : ''}
                    </div>
                    <div class="order-details-section">
                        <h4>Customer</h4>
                        <p><strong>${order.first_name} ${order.last_name}</strong></p>
                        <p>${order.email}</p>
                        <p>${order.phone || 'N/A'}</p>
                        <p>${order.shipping_address || order.customer_address || 'N/A'}</p>
                        <p>${order.shipping_city || order.customer_city || ''}, ${order.shipping_state || order.customer_state || ''} ${order.shipping_zip || order.zip_code || ''}</p>
                    </div>
                </div>
                
                ${order.tracking_number ? `
                    <div style="background: #E8F5E9; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <p><strong><i class="fas fa-truck"></i> Tracking Number:</strong> ${order.tracking_number}</p>
                        ${order.tracking_link ? `<p><strong>Track at:</strong> <a href="${order.tracking_link}" target="_blank">${order.tracking_link}</a></p>` : ''}
                    </div>
                ` : ''}
                
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(item => `
                            <tr>
                                <td>${item.product_name}</td>
                                <td style="text-align: right;">${formatCurrency(item.product_price)}</td>
                                <td style="text-align: center;">${item.quantity}</td>
                                <td style="text-align: right;">${formatCurrency(item.subtotal)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <div class="order-summary">
                    <div class="order-summary-row">
                        <span>Subtotal</span>
                        <span>${formatCurrency(order.subtotal)}</span>
                    </div>
                    ${order.tax > 0 ? `
                        <div class="order-summary-row">
                            <span>Tax</span>
                            <span>${formatCurrency(order.tax)}</span>
                        </div>
                    ` : ''}
                    ${order.shipping_cost > 0 ? `
                        <div class="order-summary-row">
                            <span>Shipping</span>
                            <span>${formatCurrency(order.shipping_cost)}</span>
                        </div>
                    ` : ''}
                    ${order.discount > 0 ? `
                        <div class="order-summary-row">
                            <span>Discount</span>
                            <span>-${formatCurrency(order.discount)}</span>
                        </div>
                    ` : ''}
                    <div class="order-summary-row total">
                        <span>Total</span>
                        <span>${formatCurrency(order.total)}</span>
                    </div>
                </div>
            `;
            
            modal.classList.add('active');
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        async function updateStatus(id, status) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                
                const response = await fetch('api/orders.php?action=update_status', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    loadOrders();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error updating status', 'error');
            }
        }

        function updateTrackingModal(id, currentTracking) {
            const tracking = prompt('Enter tracking number:', currentTracking);
            if (tracking === null) return;
            
            const link = prompt('Enter tracking link (optional):', '');
            
            updateTracking(id, tracking, link);
        }

        async function updateTracking(id, tracking_number, tracking_link) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('tracking_number', tracking_number);
                formData.append('tracking_link', tracking_link || '');
                
                const response = await fetch('api/orders.php?action=update_tracking', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error updating tracking', 'error');
            }
        }

        async function downloadBill() {
            if (!currentOrderId) return;
            
            try {
                const response = await fetch(`api/orders.php?action=generate_bill&id=${currentOrderId}`);
                const result = await response.json();
                
                if (result.success) {
                    const billWindow = window.open('', '_blank');
                    billWindow.document.write(result.data.html);
                    billWindow.document.close();
                    billWindow.print();
                }
            } catch (error) {
                showToast('Error generating bill', 'error');
            }
        }

        function printBill() {
            const frame = document.getElementById('billFrame');
            frame.contentWindow.print();
        }

        function closeBillModal() {
            document.getElementById('billModal').classList.remove('active');
        }

        function openCreateOrderModal() {
            showToast('Create order feature - Coming soon!', 'info');
        }
    </script>
</body>
</html>
