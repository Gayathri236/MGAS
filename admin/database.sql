-- Microgreens Admin Panel Database Schema
-- Run this SQL to set up the database

CREATE DATABASE IF NOT EXISTS microgreens_admin;
USE microgreens_admin;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    compare_price DECIMAL(10,2),
    cost_price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    unit VARCHAR(50) DEFAULT 'tray',
    image VARCHAR(255),
    gallery JSON,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sku VARCHAR(50) UNIQUE,
    weight DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0.00,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_name VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_zip VARCHAR(20),
    tracking_number VARCHAR(100),
    tracking_link VARCHAR(255),
    notes TEXT,
    admin_notes TEXT,
    delivery_date DATE,
    delivered_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Inventory Logs Table
CREATE TABLE IF NOT EXISTS inventory_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    quantity_change INT NOT NULL,
    reason VARCHAR(255),
    order_id INT,
    admin_id INT,
    previous_stock INT,
    new_stock INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Delivery Schedule Table
CREATE TABLE IF NOT EXISTS delivery_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    delivery_date DATE NOT NULL,
    time_slot VARCHAR(50),
    driver_name VARCHAR(100),
    driver_phone VARCHAR(20),
    vehicle_number VARCHAR(50),
    status ENUM('scheduled', 'out_for_delivery', 'delivered', 'failed') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Sales Reports Table
CREATE TABLE IF NOT EXISTS sales_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_type ENUM('daily', 'weekly', 'monthly', 'yearly', 'custom') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_sales DECIMAL(12,2) DEFAULT 0.00,
    total_orders INT DEFAULT 0,
    total_products_sold INT DEFAULT 0,
    average_order_value DECIMAL(10,2) DEFAULT 0.00,
    top_products JSON,
    report_data JSON,
    generated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Activity Logs Table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    record_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, email, password, full_name, role) VALUES 
('admin', 'admin@microgreens.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');

-- Insert sample categories
INSERT INTO categories (name, slug, description) VALUES 
('Leafy Greens', 'leafy-greens', 'Fresh leafy microgreens'),
('Herbs', 'herbs', 'Herb microgreens'),
('Brassicas', 'brassicas', 'Cabbage family microgreens'),
('Legumes', 'legumes', 'Legume microgreens'),
('Cereals', 'cereals', 'Grain microgreens');

-- Insert sample products
INSERT INTO products (category_id, name, slug, description, price, cost_price, stock_quantity, low_stock_threshold, unit, sku, is_featured) VALUES 
(1, 'Sunflower Shoots', 'sunflower-shoots', 'Nutty and crunchy sunflower microgreens, perfect for salads and sandwiches', 8.99, 4.50, 45, 15, 'tray', 'MG-SUN-001', 1),
(1, 'Pea Shoots', 'pea-shoots', 'Sweet and tender pea shoots, great for stir-fries', 6.99, 3.00, 32, 10, 'tray', 'MG-PEA-001', 1),
(2, 'Basil Microgreens', 'basil-microgreens', 'Aromatic basil microgreens with intense flavor', 10.99, 5.50, 28, 10, 'tray', 'MG-BAS-001', 1),
(2, 'Cilantro Microgreens', 'cilantro-microgreens', 'Fresh cilantro microgreens for Mexican and Asian dishes', 9.99, 4.75, 38, 12, 'tray', 'MG-CIL-001', 0),
(3, 'Broccoli Microgreens', 'broccoli-microgreens', 'High nutrition broccoli microgreens', 7.99, 3.75, 55, 15, 'tray', 'MG-BRO-001', 1),
(3, 'Radish Microgreens', 'radish-microgreens', 'Spicy radish microgreens with peppery flavor', 5.99, 2.50, 42, 12, 'tray', 'MG-RAD-001', 0),
(4, 'Mung Bean Sprouts', 'mung-bean-sprouts', 'Fresh mung bean sprouts for Asian cuisine', 4.99, 2.00, 60, 20, 'pack', 'MG-MUN-001', 0),
(5, 'Wheatgrass', 'wheatgrass', 'Organic wheatgrass for juices and smoothies', 12.99, 6.00, 25, 8, 'tray', 'MG-WHE-001', 1),
(1, 'Mixed Greens', 'mixed-greens', 'Assorted leafy microgreens mix', 11.99, 5.00, 35, 10, 'tray', 'MG-MIX-001', 1),
(2, 'Mint Microgreens', 'mint-microgreens', 'Refreshing mint microgreens', 9.99, 4.50, 22, 8, 'tray', 'MG-MIN-001', 0);

-- Insert sample customers
INSERT INTO customers (first_name, last_name, email, phone, password, address, city, state, zip_code, total_orders, total_spent) VALUES 
('John', 'Smith', 'john.smith@email.com', '555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Main St', 'New York', 'NY', '10001', 5, 245.50),
('Sarah', 'Johnson', 'sarah.j@email.com', '555-0102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '456 Oak Ave', 'Los Angeles', 'CA', '90001', 8, 412.30),
('Michael', 'Brown', 'm.brown@email.com', '555-0103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '789 Pine Rd', 'Chicago', 'IL', '60601', 3, 156.75),
('Emily', 'Davis', 'emily.d@email.com', '555-0104', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '321 Elm St', 'Houston', 'TX', '77001', 12, 589.90),
('David', 'Wilson', 'd.wilson@email.com', '555-0105', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '654 Maple Dr', 'Phoenix', 'AZ', '85001', 2, 89.50);

-- Insert sample orders
INSERT INTO orders (order_number, customer_id, subtotal, tax, shipping_cost, discount, total, status, payment_status, payment_method, tracking_number, delivery_date) VALUES 
('ORD-2026-001', 1, 35.96, 2.88, 5.00, 0.00, 43.84, 'delivered', 'paid', 'credit_card', 'TRK001234567', '2026-03-25'),
('ORD-2026-002', 2, 52.94, 4.24, 0.00, 5.00, 52.18, 'delivered', 'paid', 'paypal', 'TRK001234568', '2026-03-26'),
('ORD-2026-003', 3, 17.98, 1.44, 5.00, 0.00, 24.42, 'processing', 'paid', 'credit_card', NULL, '2026-03-28'),
('ORD-2026-004', 4, 89.92, 7.19, 0.00, 10.00, 87.11, 'shipped', 'paid', 'credit_card', 'TRK001234570', '2026-03-27'),
('ORD-2026-005', 5, 23.97, 1.92, 5.00, 0.00, 30.89, 'pending', 'pending', 'cash_on_delivery', NULL, '2026-03-29'),
('ORD-2026-006', 1, 44.95, 3.60, 0.00, 0.00, 48.55, 'delivered', 'paid', 'credit_card', 'TRK001234572', '2026-03-24'),
('ORD-2026-007', 2, 67.93, 5.43, 0.00, 0.00, 73.36, 'processing', 'paid', 'paypal', NULL, '2026-03-30');

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES 
(1, 1, 'Sunflower Shoots', 8.99, 2, 17.98),
(1, 2, 'Pea Shoots', 6.99, 1, 6.99),
(1, 5, 'Broccoli Microgreens', 7.99, 1, 7.99),
(2, 1, 'Sunflower Shoots', 8.99, 2, 17.98),
(2, 3, 'Basil Microgreens', 10.99, 2, 21.98),
(2, 9, 'Mixed Greens', 11.99, 1, 11.99),
(3, 6, 'Radish Microgreens', 5.99, 2, 11.98),
(3, 7, 'Mung Bean Sprouts', 4.99, 1, 4.99),
(4, 8, 'Wheatgrass', 12.99, 2, 25.98),
(4, 1, 'Sunflower Shoots', 8.99, 3, 26.97),
(4, 9, 'Mixed Greens', 11.99, 2, 23.98),
(4, 3, 'Basil Microgreens', 10.99, 1, 10.99),
(5, 2, 'Pea Shoots', 6.99, 2, 13.98),
(5, 4, 'Cilantro Microgreens', 9.99, 1, 9.99),
(6, 8, 'Wheatgrass', 12.99, 2, 25.98),
(6, 1, 'Sunflower Shoots', 8.99, 2, 17.98),
(7, 5, 'Broccoli Microgreens', 7.99, 3, 23.97),
(7, 9, 'Mixed Greens', 11.99, 2, 23.98),
(7, 10, 'Mint Microgreens', 9.99, 2, 19.98);
