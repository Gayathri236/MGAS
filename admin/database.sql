-- Microgreens Admin Panel Database Schema
-- Run this SQL to set up your database

CREATE DATABASE IF NOT EXISTS microgreens_admin;
USE microgreens_admin;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admins (name, email, password, role) VALUES 
('Admin User', 'admin@microgreens.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO categories (name, slug, description) VALUES 
('Microgreens', 'microgreens', 'Young vegetable greens harvested after 7-21 days'),
('Sprouts', 'sprouts', 'Germinated seeds ready to eat'),
('Edible Flowers', 'edible-flowers', 'Beautiful flowers that are safe to eat'),
('Mixes', 'mixes', 'Pre-mixed combinations of microgreens');

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50) DEFAULT 'tray',
    image VARCHAR(255) DEFAULT NULL,
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert sample products
INSERT INTO products (category_id, name, slug, description, price, unit, stock_quantity, is_featured) VALUES 
(1, 'Sunflower Microgreens', 'sunflower-microgreens', 'Nutty and crunchy microgreens packed with vitamins A, B, C, and E', 12.99, 'tray', 45, 1),
(1, 'Pea Shoots', 'pea-shoots', 'Sweet and tender shoots perfect for salads and sandwiches', 10.99, 'tray', 38, 1),
(1, 'Radish Microgreens', 'radish-microgreens', 'Spicy and peppery microgreens with a vibrant flavor', 11.99, 'tray', 52, 0),
(1, 'Broccoli Microgreens', 'broccoli-microgreens', 'Mild and nutritious greens rich in sulforaphane', 13.99, 'tray', 28, 1),
(1, 'Alfalfa Sprouts', 'alfalfa-sprouts', 'Light and refreshing sprouts great for snacking', 8.99, 'pack', 65, 0),
(2, 'Wheatgrass', 'wheatgrass', 'Energizing green juice base, rich in chlorophyll', 14.99, 'tray', 22, 1),
(2, 'Mung Bean Sprouts', 'mung-bean-sprouts', 'Crisp and crunchy bean sprouts for stir-fries', 7.99, 'pack', 58, 0),
(3, 'Microgreen Mix', 'microgreen-mix', 'Assortment of our best microgreens for variety', 15.99, 'tray', 35, 1),
(4, 'Spicy Mix', 'spicy-mix', 'Blend of radish, mustard, and arugula microgreens', 13.99, 'tray', 18, 0),
(4, 'Mild Mix', 'mild-mix', 'Gentle blend of sunflower, pea, and broccoli', 13.99, 'tray', 8, 0);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    is_blocked TINYINT(1) DEFAULT 0,
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    last_order_date DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample customers
INSERT INTO customers (name, email, phone, password, address, city, postal_code, total_orders, total_spent) VALUES 
('John Smith', 'john@example.com', '+1 555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Oak Street', 'Portland', '97201', 15, 245.85),
('Sarah Johnson', 'sarah@example.com', '+1 555-0102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '456 Maple Ave', 'Seattle', '98101', 8, 132.50),
('Mike Davis', 'mike@example.com', '+1 555-0103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '789 Pine Road', 'San Francisco', '94102', 22, 389.75),
('Emily Brown', 'emily@example.com', '+1 555-0104', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '321 Cedar Lane', 'Los Angeles', '90001', 5, 78.95),
('David Wilson', 'david@example.com', '+1 555-0105', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '654 Birch Blvd', 'Denver', '80201', 12, 198.50);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    subtotal DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0.00,
    delivery_fee DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    tracking_number VARCHAR(100),
    tracking_link VARCHAR(255),
    notes TEXT,
    delivery_date DATE,
    delivery_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- Insert sample orders
INSERT INTO orders (order_number, customer_id, subtotal, tax, delivery_fee, total, status, payment_status, payment_method, delivery_date, delivery_address) VALUES 
('ORD-2024-001', 1, 35.97, 2.88, 5.00, 43.85, 'delivered', 'paid', 'card', '2024-01-15', '123 Oak Street, Portland, OR 97201'),
('ORD-2024-002', 2, 28.98, 2.32, 5.00, 36.30, 'delivered', 'paid', 'card', '2024-01-16', '456 Maple Ave, Seattle, WA 98101'),
('ORD-2024-003', 3, 54.95, 4.40, 5.00, 64.35, 'shipped', 'paid', 'card', '2024-01-18', '789 Pine Road, San Francisco, CA 94102'),
('ORD-2024-004', 4, 19.98, 1.60, 5.00, 26.58, 'processing', 'paid', 'cash', '2024-01-19', '321 Cedar Lane, Los Angeles, CA 90001'),
('ORD-2024-005', 1, 41.97, 3.36, 5.00, 50.33, 'pending', 'pending', 'card', '2024-01-20', '123 Oak Street, Portland, OR 97201'),
('ORD-2024-006', 5, 27.98, 2.24, 5.00, 35.22, 'pending', 'paid', 'card', '2024-01-21', '654 Birch Blvd, Denver, CO 80201'),
('ORD-2024-007', 2, 15.99, 1.28, 0.00, 17.27, 'delivered', 'paid', 'card', '2024-01-12', '456 Maple Ave, Seattle, WA 98101'),
('ORD-2024-008', 3, 68.95, 5.52, 5.00, 79.47, 'processing', 'paid', 'card', '2024-01-22', '789 Pine Road, San Francisco, CA 94102');

-- Order Items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES 
(1, 1, 'Sunflower Microgreens', 2, 12.99, 25.98),
(1, 2, 'Pea Shoots', 1, 10.99, 10.99),
(2, 3, 'Radish Microgreens', 2, 11.99, 23.98),
(2, 5, 'Alfalfa Sprouts', 1, 5.00, 5.00),
(3, 1, 'Sunflower Microgreens', 3, 12.99, 38.97),
(3, 4, 'Broccoli Microgreens', 1, 13.99, 13.99),
(3, 6, 'Wheatgrass', 1, 14.99, 14.99),
(4, 2, 'Pea Shoots', 2, 10.99, 21.98),
(5, 4, 'Broccoli Microgreens', 2, 13.99, 27.98),
(5, 8, 'Microgreen Mix', 1, 15.99, 15.99),
(6, 3, 'Radish Microgreens', 2, 11.99, 23.98),
(7, 8, 'Microgreen Mix', 1, 15.99, 15.99),
(8, 1, 'Sunflower Microgreens', 3, 12.99, 38.97),
(8, 2, 'Pea Shoots', 2, 10.99, 21.98),
(8, 4, 'Broccoli Microgreens', 1, 13.99, 13.99),
(8, 6, 'Wheatgrass', 2, 14.99, 29.98);

-- Inventory table (for detailed tracking)
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity_change INT NOT NULL,
    change_type ENUM('sale', 'restock', 'adjustment', 'damage', 'return') NOT NULL,
    notes TEXT,
    admin_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Insert sample inventory logs
INSERT INTO inventory (product_id, quantity_change, change_type, notes, admin_id) VALUES 
(1, -2, 'sale', 'Order ORD-2024-001', 1),
(2, -1, 'sale', 'Order ORD-2024-001', 1),
(3, -2, 'sale', 'Order ORD-2024-002', 1),
(5, -1, 'sale', 'Order ORD-2024-002', 1),
(1, -3, 'sale', 'Order ORD-2024-003', 1),
(4, -1, 'sale', 'Order ORD-2024-003', 1),
(6, -1, 'sale', 'Order ORD-2024-003', 1),
(2, -2, 'sale', 'Order ORD-2024-004', 1),
(4, -2, 'sale', 'Order ORD-2024-005', 1),
(8, -1, 'sale', 'Order ORD-2024-005', 1),
(3, -2, 'sale', 'Order ORD-2024-006', 1),
(8, -1, 'sale', 'Order ORD-2024-007', 1),
(1, -3, 'sale', 'Order ORD-2024-008', 1),
(2, -2, 'sale', 'Order ORD-2024-008', 1),
(4, -1, 'sale', 'Order ORD-2024-008', 1),
(6, -2, 'sale', 'Order ORD-2024-008', 1),
(1, 20, 'restock', 'Weekly restock', 1),
(2, 15, 'restock', 'Weekly restock', 1),
(3, 25, 'restock', 'Weekly restock', 1);

-- Deliveries table
CREATE TABLE IF NOT EXISTS deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    delivery_date DATE NOT NULL,
    time_slot VARCHAR(50),
    status ENUM('scheduled', 'in_transit', 'delivered', 'failed', 'cancelled') DEFAULT 'scheduled',
    driver_name VARCHAR(100),
    driver_phone VARCHAR(20),
    delivery_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert sample deliveries
INSERT INTO deliveries (order_id, delivery_date, time_slot, status, driver_name) VALUES 
(1, '2024-01-15', 'Morning (9AM-12PM)', 'delivered', 'Alex Turner'),
(2, '2024-01-16', 'Afternoon (1PM-5PM)', 'delivered', 'Jordan Lee'),
(3, '2024-01-18', 'Morning (9AM-12PM)', 'in_transit', 'Alex Turner'),
(4, '2024-01-19', 'Afternoon (1PM-5PM)', 'scheduled', 'Jordan Lee'),
(5, '2024-01-20', 'Morning (9AM-12PM)', 'scheduled', 'Alex Turner'),
(6, '2024-01-21', 'Afternoon (1PM-5PM)', 'scheduled', 'Jordan Lee'),
(7, '2024-01-12', 'Morning (9AM-12PM)', 'delivered', 'Alex Turner'),
(8, '2024-01-22', 'Morning (9AM-12PM)', 'scheduled', 'Alex Turner');

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Sales table (for analytics)
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    sale_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert sample sales records
INSERT INTO sales (order_id, amount, sale_date) VALUES 
(1, 43.85, '2024-01-15'),
(2, 36.30, '2024-01-16'),
(3, 64.35, '2024-01-18'),
(4, 26.58, '2024-01-19'),
(5, 50.33, '2024-01-20'),
(6, 35.22, '2024-01-21'),
(7, 17.27, '2024-01-12'),
(8, 79.47, '2024-01-22');
