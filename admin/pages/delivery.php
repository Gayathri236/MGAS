<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .delivery-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 24px;
        }
        
        .del-stat-card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .del-stat-card:hover,
        .del-stat-card.active {
            border-color: var(--primary);
        }
        
        .del-stat-card .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-800);
        }
        
        .del-stat-card .stat-label {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 5px;
        }
        
        .delivery-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
        }
        
        .delivery-card.scheduled { border-left-color: #3B82F6; }
        .delivery-card.out_for_delivery { border-left-color: #F59E0B; }
        .delivery-card.delivered { border-left-color: #10B981; }
        .delivery-card.failed { border-left-color: #EF4444; }
        
        .delivery-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .delivery-order {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .delivery-order strong {
            font-size: 18px;
            color: var(--primary);
        }
        
        .delivery-order span {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .delivery-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .delivery-status.scheduled { background: #DBEAFE; color: #2563EB; }
        .delivery-status.out_for_delivery { background: #FEF3C7; color: #D97706; }
        .delivery-status.delivered { background: #D1FAE5; color: #059669; }
        .delivery-status.failed { background: #FEE2E2; color: #DC2626; }
        
        .delivery-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .delivery-detail {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .delivery-detail label {
            font-size: 11px;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .delivery-detail span {
            font-size: 14px;
            color: var(--gray-800);
        }
        
        .delivery-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid var(--gray-100);
        }
        
        .driver-info {
            background: var(--gray-50);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .driver-info strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .driver-info span {
            font-size: 13px;
            color: var(--gray-600);
        }
        
        @media (max-width: 1024px) {
            .delivery-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .delivery-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .delivery-details {
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
                        <h1>Delivery Management</h1>
                        <p>Manage delivery schedules and assignments</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openScheduleModal()">
                            <i class="fas fa-plus"></i> Schedule Delivery
                        </button>
                    </div>
                </div>

                <div class="filters-bar">
                    <div class="search-box">
                        <i class="fas fa-calendar"></i>
                        <input type="date" id="dateFilter" value="<?php echo date('Y-m-d'); ?>" onchange="loadDeliveries()">
                    </div>
                </div>

                <div class="delivery-stats" id="deliveryStats">
                    <div class="del-stat-card active" onclick="filterByStatus('')">
                        <div class="stat-number" id="statTotal">0</div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="del-stat-card" onclick="filterByStatus('scheduled')">
                        <div class="stat-number" id="statScheduled">0</div>
                        <div class="stat-label">Scheduled</div>
                    </div>
                    <div class="del-stat-card" onclick="filterByStatus('out_for_delivery')">
                        <div class="stat-number" id="statOut">0</div>
                        <div class="stat-label">Out for Delivery</div>
                    </div>
                    <div class="del-stat-card" onclick="filterByStatus('delivered')">
                        <div class="stat-number" id="statDelivered">0</div>
                        <div class="stat-label">Delivered</div>
                    </div>
                    <div class="del-stat-card" onclick="filterByStatus('failed')">
                        <div class="stat-number" id="statFailed">0</div>
                        <div class="stat-label">Failed</div>
                    </div>
                </div>

                <div id="deliveriesContainer">
                    <div class="text-center text-muted p-5">Loading deliveries...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="scheduleModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Schedule Delivery</h3>
                <button class="modal-close" onclick="closeScheduleModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="scheduleForm" onsubmit="saveDelivery(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Order *</label>
                        <select name="order_id" id="orderSelect" required>
                            <option value="">Loading orders...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Delivery Date *</label>
                        <input type="date" name="delivery_date" id="deliveryDate" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Time Slot</label>
                        <select name="time_slot" id="timeSlot">
                            <option value="9AM-12PM">9:00 AM - 12:00 PM</option>
                            <option value="12PM-3PM">12:00 PM - 3:00 PM</option>
                            <option value="3PM-6PM">3:00 PM - 6:00 PM</option>
                            <option value="6PM-9PM">6:00 PM - 9:00 PM</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Driver Name</label>
                        <input type="text" name="driver_name" id="driverName" placeholder="Enter driver name">
                    </div>
                    
                    <div class="form-group">
                        <label>Driver Phone</label>
                        <input type="tel" name="driver_phone" id="driverPhone" placeholder="Enter driver phone">
                    </div>
                    
                    <div class="form-group">
                        <label>Vehicle Number</label>
                        <input type="text" name="vehicle_number" id="vehicleNumber" placeholder="Enter vehicle number">
                    </div>
                    
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" id="deliveryNotes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeScheduleModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editDeliveryModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Update Delivery</h3>
                <button class="modal-close" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" onsubmit="updateDelivery(event)">
                <div class="modal-body">
                    <input type="hidden" id="editDeliveryId" name="id">
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="editStatus">
                            <option value="scheduled">Scheduled</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Driver Name</label>
                        <input type="text" name="driver_name" id="editDriverName">
                    </div>
                    
                    <div class="form-group">
                        <label>Driver Phone</label>
                        <input type="tel" name="driver_phone" id="editDriverPhone">
                    </div>
                    
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" id="editNotes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        let currentStatus = '';
        
        document.addEventListener('DOMContentLoaded', function() {
            loadDeliveries();
        });

        async function loadDeliveries() {
            const date = document.getElementById('dateFilter').value;
            
            try {
                const params = new URLSearchParams({
                    action: 'list',
                    date,
                    status: currentStatus
                });
                
                const response = await fetch(`api/delivery.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    updateStats(result.data.stats);
                    renderDeliveries(result.data.deliveries);
                }
            } catch (error) {
                showToast('Error loading deliveries', 'error');
            }
        }

        function updateStats(stats) {
            document.getElementById('statTotal').textContent = stats.total;
            document.getElementById('statScheduled').textContent = stats.scheduled;
            document.getElementById('statOut').textContent = stats.out_for_delivery;
            document.getElementById('statDelivered').textContent = stats.delivered;
            document.getElementById('statFailed').textContent = stats.failed;
            
            document.querySelectorAll('.del-stat-card').forEach(card => card.classList.remove('active'));
            if (currentStatus === '') {
                document.querySelector('.del-stat-card').classList.add('active');
            } else {
                document.querySelector(`.del-stat-card[onclick="filterByStatus('${currentStatus}')"]`)?.classList.add('active');
            }
        }

        function filterByStatus(status) {
            currentStatus = status;
            loadDeliveries();
        }

        function renderDeliveries(deliveries) {
            const container = document.getElementById('deliveriesContainer');
            
            if (deliveries.length === 0) {
                container.innerHTML = `
                    <div class="delivery-card" style="text-align: center; padding: 40px;">
                        <i class="fas fa-truck" style="font-size: 48px; color: var(--gray-300); margin-bottom: 15px;"></i>
                        <p style="color: var(--gray-500);">No deliveries found for this date</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = deliveries.map(d => `
                <div class="delivery-card ${d.status}">
                    <div class="delivery-header">
                        <div class="delivery-order">
                            <strong>${d.order_number}</strong>
                            <span>${d.first_name} ${d.last_name}</span>
                        </div>
                        <span class="delivery-status ${d.status}">${formatStatus(d.status)}</span>
                    </div>
                    
                    <div class="delivery-details">
                        <div class="delivery-detail">
                            <label>Delivery Date</label>
                            <span>${formatDate(d.delivery_date)}</span>
                        </div>
                        <div class="delivery-detail">
                            <label>Time Slot</label>
                            <span>${d.time_slot || 'Not set'}</span>
                        </div>
                        <div class="delivery-detail">
                            <label>Order Total</label>
                            <span>${formatCurrency(d.order_total)}</span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 11px; color: var(--gray-500); text-transform: uppercase;">Delivery Address</label>
                        <p style="font-size: 14px; color: var(--gray-800);">${d.shipping_address || 'N/A'}</p>
                        <p style="font-size: 13px; color: var(--gray-600);">${d.shipping_city || ''}, ${d.shipping_state || ''} ${d.shipping_zip || ''}</p>
                    </div>
                    
                    ${d.driver_name ? `
                        <div class="driver-info">
                            <strong><i class="fas fa-user"></i> ${d.driver_name}</strong>
                            <span><i class="fas fa-phone"></i> ${d.driver_phone || 'N/A'}</span>
                        </div>
                    ` : ''}
                    
                    ${d.notes ? `<p style="font-size: 13px; color: var(--gray-600); margin-bottom: 15px;"><i class="fas fa-sticky-note"></i> ${d.notes}</p>` : ''}
                    
                    <div class="delivery-actions">
                        <button class="btn btn-outline" style="padding: 8px 15px; font-size: 13px;" onclick="editDelivery(${d.id})">
                            <i class="fas fa-edit"></i> Update
                        </button>
                        <button class="btn btn-outline" style="padding: 8px 15px; font-size: 13px; color: var(--danger);" onclick="deleteDelivery(${d.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function formatStatus(status) {
            const labels = {
                'scheduled': 'Scheduled',
                'out_for_delivery': 'Out for Delivery',
                'delivered': 'Delivered',
                'failed': 'Failed'
            };
            return labels[status] || status;
        }

        async function openScheduleModal() {
            document.getElementById('scheduleModal').classList.add('active');
            
            try {
                const response = await fetch('api/delivery.php?action=get_pending_orders');
                const result = await response.json();
                
                if (result.success) {
                    const select = document.getElementById('orderSelect');
                    if (result.data.length === 0) {
                        select.innerHTML = '<option value="">No pending orders</option>';
                    } else {
                        select.innerHTML = '<option value="">Select an order</option>' +
                            result.data.map(o => `
                                <option value="${o.id}">
                                    ${o.order_number} - ${o.first_name} ${o.last_name} (${formatCurrency(o.total)})
                                </option>
                            `).join('');
                    }
                }
            } catch (error) {
                showToast('Error loading orders', 'error');
            }
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('active');
        }

        async function saveDelivery(e) {
            e.preventDefault();
            
            const form = document.getElementById('scheduleForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('api/delivery.php?action=create', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    closeScheduleModal();
                    form.reset();
                    loadDeliveries();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error saving delivery', 'error');
            }
        }

        async function editDelivery(id) {
            try {
                const response = await fetch(`api/delivery.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const d = result.data;
                    document.getElementById('editDeliveryId').value = d.id;
                    document.getElementById('editStatus').value = d.status;
                    document.getElementById('editDriverName').value = d.driver_name || '';
                    document.getElementById('editDriverPhone').value = d.driver_phone || '';
                    document.getElementById('editNotes').value = d.notes || '';
                    
                    document.getElementById('editDeliveryModal').classList.add('active');
                }
            } catch (error) {
                showToast('Error loading delivery', 'error');
            }
        }

        function closeEditModal() {
            document.getElementById('editDeliveryModal').classList.remove('active');
        }

        async function updateDelivery(e) {
            e.preventDefault();
            
            const form = document.getElementById('editForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('api/delivery.php?action=update', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    closeEditModal();
                    loadDeliveries();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error updating delivery', 'error');
            }
        }

        async function deleteDelivery(id) {
            if (!confirmDelete('Are you sure you want to delete this delivery?')) return;
            
            try {
                const response = await fetch(`api/delivery.php?action=delete&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    loadDeliveries();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error deleting delivery', 'error');
            }
        }
    </script>
</body>
</html>
