-- Pearl Creamery tables for InfinityFree import.
-- Run this inside phpMyAdmin AFTER selecting your existing database.
-- (CREATE DATABASE / USE are removed because shared hosting users
--  do not have permission to create databases.)

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  category VARCHAR(50) NOT NULL,
  description TEXT,
  price INT NOT NULL,                -- UGX, whole shillings
  size VARCHAR(20),
  is_available TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(12) NOT NULL UNIQUE,
  customer_name VARCHAR(100) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  customer_email VARCHAR(120) NULL,
  product_id INT NULL,
  quantity INT NULL,
  total_price INT NOT NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  formatted_address VARCHAR(255) NULL,
  delivery_notes VARCHAR(255) NULL,
  order_status ENUM('Pending','Processing','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  transaction_ref VARCHAR(100) NULL,
  gateway VARCHAR(30) NULL,
  payment_method ENUM('COD','MobileMoney') NOT NULL,
  status ENUM('Unpaid','Paid','Failed') NOT NULL DEFAULT 'Unpaid',
  amount INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE IF NOT EXISTS store_settings (
  setting_key VARCHAR(50) PRIMARY KEY,
  setting_value VARCHAR(255) NOT NULL
);

INSERT IGNORE INTO store_settings (setting_key, setting_value) VALUES
 ('contact_phone','+256 780 457866'),
 ('admin_email','admin@example.com'),
 ('smtp_host','smtp.gmail.com'),
 ('smtp_port','587'),
 ('smtp_user',''),
 ('smtp_pass',''),
 ('smtp_from_name','Pearl Creamery');

INSERT INTO products (name, category, description, price, size) VALUES
 ('Strawberry Yoghurt','Yoghurt','Real strawberry pieces in thick, creamy yoghurt.',3500,'250 ml'),
 ('Plain Yoghurt','Yoghurt','Natural, unsweetened and smooth.',3000,'250 ml'),
 ('Vanilla Yoghurt','Yoghurt','Soft vanilla, lightly sweet.',3500,'250 ml'),
 ('Blueberry Swirl','Yoghurt','Blueberry ribbon through creamy yoghurt.',4000,'250 ml'),
 ('Yoghurt Parfait','Parfait','Yoghurt layered with fruit and crunchy granola.',6000,'300 ml'),
 ('Probiotic Yoghurt Drink','Drink','Drinkable yoghurt with live cultures.',2500,'250 ml'),
 ('Bulk Event Tub','Bulk','For parties, weddings and events. Order a day ahead.',45000,'5 L');

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(100) NOT NULL,
  quantity INT NOT NULL,
  unit_price INT NOT NULL,
  line_total INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
);