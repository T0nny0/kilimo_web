-- KilimoSafi Database Schema
-- Version: 1.0
-- Created: 2024

-- Create Database
CREATE DATABASE IF NOT EXISTS kilimosafi;
USE kilimosafi;

-- Users table (for farmers and admins)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('farmer', 'admin') DEFAULT 'farmer',
    profile_image VARCHAR(255),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table (agricultural inputs)
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    buy_price DECIMAL(10,2),
    rent_price DECIMAL(10,2) DEFAULT NULL,
    quantity_available INT DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'kg',
    is_available BOOLEAN DEFAULT TRUE,
    min_rent_days INT DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_availability (is_available),
    INDEX idx_name (name),
    INDEX idx_price (buy_price, rent_price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    contact_phone VARCHAR(20),
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    type ENUM('buy', 'rent') NOT NULL,
    rent_days INT DEFAULT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('order', 'product', 'system') DEFAULT 'system',
    reference_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cart table (for temporary cart storage)
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    type ENUM('buy', 'rent') DEFAULT 'buy',
    rent_days INT DEFAULT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id, type, rent_days),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order status history table
CREATE TABLE order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') NOT NULL,
    notes TEXT,
    changed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert admin user (password: admin123)
-- Note: The default password for demo users is 'password'
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('admin', 'admin@kilimosafi.com', '$2y$10$OGdM..x/Im0gjxQx1xJc9u65yYHCILOR0212s5ff3oO8A5x.a/b/W', 'Administrator', 'admin');

-- Insert categories
INSERT INTO categories (name, description, icon) VALUES 
('Seeds', 'Various types of seeds for different crops', '🌱'),
('Fertilizers', 'Chemical and organic fertilizers', '🧪'),
('Pesticides', 'Pesticides and insecticides', '🐛'),
('Tools', 'Farming tools and equipment', '🛠️'),
('Irrigation', 'Irrigation systems and equipment', '💧'),
('Equipment', 'Large farming equipment and machinery', '🚜'),
('Livestock', 'Animal feeds and supplements', '🐄'),
('Greenhouse', 'Greenhouse materials and equipment', '🏭');

-- Insert sample products
INSERT INTO products (name, category_id, description, image_url, buy_price, rent_price, quantity_available, unit, min_rent_days) VALUES
('Hybrid Maize Seeds', 1, 'High-yield hybrid maize seeds suitable for various climates. Drought resistant and high germination rate.', 'assets/images/maize-seeds.jpg', 1500.00, NULL, 100, 'kg', 1),
('NPK Fertilizer', 2, 'Balanced NPK fertilizer for all crops. Contains Nitrogen, Phosphorus, and Potassium in optimal ratios.', 'assets/images/npk-fertilizer.jpg', 3500.00, NULL, 50, '50kg bag', 1),
('Hand Tractor', 4, 'Motorized hand tractor for small farms. Easy to operate and maintain. Perfect for land preparation.', 'assets/images/tractor.jpg', 250000.00, 5000.00, 10, 'unit', 3),
('Insecticide Spray', 3, 'Effective insecticide for crop protection against common pests. Safe for most crops when used as directed.', 'assets/images/insecticide.jpg', 1200.00, NULL, 30, 'liter', 1),
('Irrigation Pump', 5, 'Electric water pump for irrigation systems. High efficiency with low power consumption.', 'assets/images/pump.jpg', 45000.00, 3000.00, 15, 'unit', 2),
('Drip Irrigation Kit', 5, 'Complete drip irrigation system for 1/4 acre. Includes pipes, drippers, filters, and connectors.', 'assets/images/drip-kit.jpg', 15000.00, 2000.00, 20, 'set', 7),
('Wheat Seeds', 1, 'High quality wheat seeds for bread production. Disease resistant variety with high protein content.', 'assets/images/wheat-seeds.jpg', 1800.00, NULL, 80, 'kg', 1),
('Organic Fertilizer', 2, '100% organic fertilizer from animal waste and plant compost. Improves soil structure and fertility.', 'assets/images/organic-fertilizer.jpg', 2800.00, NULL, 60, '50kg bag', 1),
('Sprayer', 4, 'Manual sprayer for pesticides and fertilizers. 20L capacity with adjustable nozzle.', 'assets/images/sprayer.jpg', 4500.00, 500.00, 25, 'unit', 1),
('Greenhouse Kit', 6, 'Complete greenhouse setup for controlled farming. 10m x 20m size with UV-resistant cover.', 'assets/images/greenhouse.jpg', 120000.00, 15000.00, 5, 'set', 30),
('Tomato Seeds', 1, 'Hybrid tomato seeds for high yield. Resistant to common tomato diseases.', 'assets/images/tomato-seeds.jpg', 800.00, NULL, 120, 'pack', 1),
('Water Tank', 5, '5000L plastic water tank for rainwater harvesting and irrigation storage.', 'assets/images/water-tank.jpg', 25000.00, 2000.00, 8, 'unit', 7),
('Plough', 4, 'Traditional animal-drawn plough for land preparation. Durable steel construction.', 'assets/images/plough.jpg', 8000.00, 1000.00, 15, 'unit', 3),
('Herbicide', 3, 'Selective herbicide for weed control in maize and wheat fields.', 'assets/images/herbicide.jpg', 950.00, NULL, 40, 'liter', 1);

-- Insert sample farmer user (password: password) 
INSERT INTO users (username, email, password, full_name, phone, address, role) 
VALUES ('farmer_john', 'john@example.com', '$2y$10$OGdM..x/Im0gjxQx1xJc9u65yYHCILOR0212s5ff3oO8A5x.a/b/W', 'John Kamau', '+254712345678', 'Nakuru County, Kenya', 'farmer');

-- Insert sample orders
INSERT INTO orders (order_number, user_id, total_amount, status, delivery_address, contact_phone) 
VALUES ('ORD-20240101-ABC123', 2, 5300.00, 'completed', 'Nakuru County, Kenya', '+254712345678');

INSERT INTO order_items (order_id, product_id, quantity, price, type, total_price) 
VALUES (1, 1, 2, 1500.00, 'buy', 3000.00),
       (1, 2, 1, 3500.00, 'buy', 3500.00);

-- Insert order status history
INSERT INTO order_status_history (order_id, status, notes) 
VALUES (1, 'pending', 'Order placed by customer'),
       (1, 'approved', 'Order approved by admin'),
       (1, 'completed', 'Order delivered successfully');

-- Insert sample notifications
INSERT INTO notifications (user_id, title, message, type, reference_id) 
VALUES (1, 'New Order', 'Order #ORD-20240101-ABC123 has been placed by John Kamau', 'order', 1),
       (2, 'Order Approved', 'Your order #ORD-20240101-ABC123 has been approved', 'order', 1),
       (2, 'Welcome', 'Welcome to KilimoSafi! Start exploring our agricultural inputs.', 'system', NULL);

-- Create views for reporting
CREATE VIEW v_order_summary AS
SELECT 
    o.id,
    o.order_number,
    o.user_id,
    u.full_name as customer_name,
    o.total_amount,
    o.status,
    o.order_date,
    COUNT(oi.id) as item_count
FROM orders o
JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

CREATE VIEW v_product_sales AS
SELECT 
    p.id,
    p.name,
    c.name as category,
    p.buy_price,
    p.rent_price,
    COALESCE(SUM(CASE WHEN oi.type = 'buy' THEN oi.quantity ELSE 0 END), 0) as total_bought,
    COALESCE(SUM(CASE WHEN oi.type = 'rent' THEN oi.quantity ELSE 0 END), 0) as total_rented,
    COALESCE(SUM(CASE WHEN oi.type = 'buy' THEN oi.total_price ELSE 0 END), 0) as buy_revenue,
    COALESCE(SUM(CASE WHEN oi.type = 'rent' THEN oi.total_price ELSE 0 END), 0) as rent_revenue
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
GROUP BY p.id;

-- Create stored procedure for getting user statistics
DELIMITER $$
CREATE PROCEDURE sp_get_user_statistics(IN user_id INT)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM orders WHERE user_id = user_id) as total_orders,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = user_id AND status = 'completed') as total_spent,
        (SELECT COUNT(*) FROM orders WHERE user_id = user_id AND status = 'pending') as pending_orders,
        (SELECT COUNT(*) FROM cart WHERE user_id = user_id) as cart_items;
END$$
DELIMITER ;

-- Create trigger for order status update
DELIMITER $$
CREATE TRIGGER tr_order_status_update
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO order_status_history (order_id, status, notes)
        VALUES (NEW.id, NEW.status, CONCAT('Status changed from ', OLD.status, ' to ', NEW.status));
    END IF;
END$$
DELIMITER ;

-- Create event for cleaning old cart items (runs daily)
DELIMITER $$
CREATE EVENT ev_clean_old_cart
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    DELETE FROM cart WHERE added_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
END$$
DELIMITER ;
