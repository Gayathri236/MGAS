<header class="main-header">
    <div class="header-left">
        <button class="mobile-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search orders, products, customers...">
        </div>
    </div>
    
    <div class="header-right">
        <div class="header-notifications">
            <button class="notification-btn" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge">3</span>
            </button>
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="notifications-header">
                    <h4>Notifications</h4>
                    <a href="#">Mark all read</a>
                </div>
                <div class="notifications-list">
                    <div class="notification-item unread">
                        <div class="notification-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="notification-content">
                            <p>Low stock alert for Sunflower Shoots</p>
                            <span>5 minutes ago</span>
                        </div>
                    </div>
                    <div class="notification-item unread">
                        <div class="notification-icon success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="notification-content">
                            <p>New order #ORD-2026-007 received</p>
                            <span>15 minutes ago</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon info">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="notification-content">
                            <p>Delivery completed for order #ORD-2026-002</p>
                            <span>1 hour ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-user">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo $_SESSION['admin_name']; ?></span>
                <span class="user-role"><?php echo ucfirst($_SESSION['admin_role']); ?></span>
            </div>
            <button class="user-menu-btn" onclick="toggleUserMenu()">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
    </div>
</header>
