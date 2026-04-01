<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .customer-avatar {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }
        
        .customer-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .customer-info strong {
            display: block;
            color: var(--gray-800);
        }
        
        .customer-info span {
            font-size: 12px;
            color: var(--gray-500);
        }
        
        .customer-stats {
            display: flex;
            gap: 20px;
        }
        
        .stat-mini {
            text-align: center;
        }
        
        .stat-mini strong {
            display: block;
            font-size: 16px;
            color: var(--gray-800);
        }
        
        .stat-mini span {
            font-size: 11px;
            color: var(--gray-500);
        }
        
        .badge-active { background: #D1FAE5; color: #059669; }
        .badge-inactive { background: #F3F4F6; color: #6B7280; }
        .badge-blocked { background: #FEE2E2; color: #DC2626; }
        
        .modal-body .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .modal-body .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .modal-body .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .modal-body .form-group label {
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .modal-body .form-group input,
        .modal-body .form-group textarea {
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .modal-body .form-group input:focus,
        .modal-body .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .customer-orders {
            margin-top: 20px;
        }
        
        .orders-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--gray-50);
            border-radius: 8px;
            margin-bottom: 8px;
        }
        
        .order-item-info strong {
            display: block;
            font-size: 13px;
            color: var(--gray-800);
        }
        
        .order-item-info span {
            font-size: 11px;
            color: var(--gray-500);
        }
        
        @media (max-width: 768px) {
            .modal-body .form-grid {
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
                        <h1>Customers</h1>
                        <p>Manage your registered customers</p>
                    </div>
                </div>

                <div class="filters-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search customers..." onkeyup="debouncedSearch()">
                    </div>
                    <select id="statusFilter" onchange="loadCustomers()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>

                <div class="card">
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Orders</th>
                                        <th>Total Spent</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="customersTable">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted p-5">Loading customers...</td>
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

    <div class="modal-overlay" id="customerModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 id="modalTitle">Customer Details</h3>
                <button class="modal-close" onclick="closeCustomerModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="customerForm" onsubmit="saveCustomer(event)">
                <div class="modal-body">
                    <input type="hidden" id="customerId" name="id">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" id="customerFirstName" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" id="customerLastName" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" id="customerEmail" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" id="customerPhone">
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Address</label>
                            <input type="text" name="address" id="customerAddress">
                        </div>
                        
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" id="customerCity">
                        </div>
                        
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" id="customerState">
                        </div>
                        
                        <div class="form-group">
                            <label>ZIP Code</label>
                            <input type="text" name="zip_code" id="customerZip">
                        </div>
                    </div>
                    
                    <div class="customer-orders" id="customerOrdersSection" style="display: none;">
                        <div class="orders-title">
                            <i class="fas fa-shopping-bag"></i> Recent Orders
                        </div>
                        <div id="customerOrdersList"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCustomerModal()">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        let currentPage = 1;
        
        document.addEventListener('DOMContentLoaded', function() {
            loadCustomers();
        });

        const debouncedSearch = debounce(() => {
            currentPage = 1;
            loadCustomers();
        }, 300);

        async function loadCustomers() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            
            try {
                const params = new URLSearchParams({
                    action: 'list',
                    page: currentPage,
                    limit: 10,
                    search,
                    status
                });
                
                const response = await fetch(`api/customers.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    renderCustomers(result.data.customers);
                    renderPagination(result.data.pagination);
                }
            } catch (error) {
                showToast('Error loading customers', 'error');
            }
        }

        function renderCustomers(customers) {
            const tbody = document.getElementById('customersTable');
            
            if (customers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-5">No customers found</td></tr>';
                return;
            }
            
            tbody.innerHTML = customers.map(c => `
                <tr>
                    <td>
                        <div class="customer-cell">
                            <div class="customer-avatar">${c.first_name.charAt(0)}${c.last_name.charAt(0)}</div>
                            <div class="customer-info">
                                <strong>${c.first_name} ${c.last_name}</strong>
                                <span>Since ${formatDate(c.created_at)}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="customer-info">
                            <strong>${c.email}</strong>
                            <span>${c.phone || 'N/A'}</span>
                        </div>
                    </td>
                    <td>${c.city && c.state ? `${c.city}, ${c.state}` : '<span class="text-muted">N/A</span>'}</td>
                    <td>${c.total_orders}</td>
                    <td><strong>${formatCurrency(c.total_spent)}</strong></td>
                    <td>
                        <span class="badge badge-${c.status}">${capitalize(c.status)}</span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" onclick="viewCustomer(${c.id})" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" onclick="editCustomer(${c.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div class="status-dropdown">
                                <button class="btn-icon" onclick="toggleStatusMenu(${c.id})">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu" id="statusMenu${c.id}">
                                    <a href="#" onclick="changeStatus(${c.id}, 'active')"><i class="fas fa-check"></i> Active</a>
                                    <a href="#" onclick="changeStatus(${c.id}, 'inactive')"><i class="fas fa-pause"></i> Inactive</a>
                                    <a href="#" onclick="changeStatus(${c.id}, 'blocked')"><i class="fas fa-ban"></i> Blocked</a>
                                </div>
                            </div>
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
            loadCustomers();
        }

        async function viewCustomer(id) {
            try {
                const response = await fetch(`api/customers.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    openCustomerModal(result.data.customer, result.data.orders, true);
                }
            } catch (error) {
                showToast('Error loading customer', 'error');
            }
        }

        async function editCustomer(id) {
            try {
                const response = await fetch(`api/customers.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    openCustomerModal(result.data.customer, result.data.orders, false);
                }
            } catch (error) {
                showToast('Error loading customer', 'error');
            }
        }

        function openCustomerModal(customer, orders, isView) {
            const modal = document.getElementById('customerModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('customerForm');
            const ordersSection = document.getElementById('customerOrdersSection');
            
            document.getElementById('customerId').value = customer.id;
            document.getElementById('customerFirstName').value = customer.first_name;
            document.getElementById('customerLastName').value = customer.last_name;
            document.getElementById('customerEmail').value = customer.email;
            document.getElementById('customerPhone').value = customer.phone || '';
            document.getElementById('customerAddress').value = customer.address || '';
            document.getElementById('customerCity').value = customer.city || '';
            document.getElementById('customerState').value = customer.state || '';
            document.getElementById('customerZip').value = customer.zip_code || '';
            
            if (isView) {
                title.textContent = 'Customer Details';
                form.style.display = 'none';
                ordersSection.style.display = 'block';
                
                document.querySelector('#customerModal .modal-footer').innerHTML = 
                    '<button type="button" class="btn btn-outline" onclick="closeCustomerModal()">Close</button>';
                
                document.getElementById('customerOrdersList').innerHTML = orders.length ? 
                    orders.map(o => `
                        <div class="order-item">
                            <div class="order-item-info">
                                <strong>${o.order_number}</strong>
                                <span>${formatDate(o.created_at)} - ${o.items_count} items</span>
                            </div>
                            <div>
                                <span class="badge badge-${o.status}">${capitalize(o.status)}</span>
                                <strong style="margin-left: 10px;">${formatCurrency(o.total)}</strong>
                            </div>
                        </div>
                    `).join('') :
                    '<p class="text-muted">No orders yet</p>';
            } else {
                title.textContent = 'Edit Customer';
                form.style.display = 'block';
                ordersSection.style.display = 'none';
                
                document.querySelector('#customerModal .modal-footer').innerHTML = 
                    '<button type="button" class="btn btn-outline" onclick="closeCustomerModal()">Cancel</button>' +
                    '<button type="submit" class="btn btn-primary" id="saveBtn">Save Changes</button>';
            }
            
            modal.classList.add('active');
        }

        function closeCustomerModal() {
            document.getElementById('customerModal').classList.remove('active');
        }

        async function saveCustomer(e) {
            e.preventDefault();
            
            const form = document.getElementById('customerForm');
            const formData = new FormData(form);
            formData.append('action', 'update');
            
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner"></span> Saving...';
            
            try {
                const response = await fetch('api/customers.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    closeCustomerModal();
                    loadCustomers();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error saving customer', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Changes';
            }
        }

        function toggleStatusMenu(id) {
            const menus = document.querySelectorAll('.dropdown-menu');
            menus.forEach(m => {
                if (m.id !== `statusMenu${id}`) {
                    m.classList.remove('active');
                }
            });
            
            const menu = document.getElementById(`statusMenu${id}`);
            menu.classList.toggle('active');
            
            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    if (!e.target.closest(`#statusMenu${id}`)) {
                        menu.classList.remove('active');
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 0);
        }

        async function changeStatus(id, status) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                
                const response = await fetch('api/customers.php?action=toggle_status', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    loadCustomers();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error updating status', 'error');
            }
        }
    </script>
</body>
</html>
