-- ============================================================
--  Pearl Creamery - FRESH import for InfinityFree phpMyAdmin
-- ============================================================
--  HOW TO USE
--  1. Log in to InfinityFree > Control Panel > phpMyAdmin.
--  2. In the LEFT sidebar, click your database  if0_43175797_creamery
--     (make sure a database is selected - do NOT create a new one).
--  3. Click the "SQL" tab, paste EVERYTHING below, and press Go.
--
--  Do NOT add "CREATE DATABASE" or "USE" lines - InfinityFree returns
--  error #1044 for those. Just select the database and run this.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items, payments, orders, products, admins, store_settings;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  category VARCHAR(60) NOT NULL,
  description TEXT,
  price DECIMAL(12,0) NOT NULL,            -- UGX, no decimals
  size VARCHAR(40) NOT NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(12) NOT NULL UNIQUE,  -- PC-XXXXXX
  customer_name VARCHAR(120) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  customer_email VARCHAR(150) NULL,
  total_price DECIMAL(12,0) NOT NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  formatted_address VARCHAR(255) NOT NULL,
  delivery_notes VARCHAR(255) NULL,
  order_status ENUM('Pending','Processing','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Lets a guest order several products in one order
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(12,0) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  transaction_ref VARCHAR(80) NULL,
  gateway VARCHAR(30) NULL,                -- flutterwave | pesapal | none
  payment_method ENUM('cod','mobile_money') NOT NULL,
  status ENUM('Unpaid','Pending','Paid','Failed') NOT NULL DEFAULT 'Unpaid',
  amount DECIMAL(12,0) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE store_settings (
  setting_key VARCHAR(60) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB;

INSERT INTO store_settings VALUES
 ('contact_phone','+256780457866'),
 ('admin_email','orders@example.com'),
 ('smtp_host',''),('smtp_port','587'),('smtp_user',''),('smtp_pass','');

-- Placeholder prices (UGX): edit in the admin Inventory Manager
INSERT INTO products (name, category, description, price, size) VALUES
 ('Strawberry Yoghurt','Yoghurt','Real strawberry pieces folded into thick, creamy yoghurt.',3500,'250 ml'),
 ('Plain Yoghurt','Yoghurt','Just fresh milk and live cultures. Mild, tangy and smooth.',3000,'250 ml'),
 ('Vanilla Yoghurt','Yoghurt','Soft vanilla sweetness with no artificial flavour.',3500,'250 ml'),
 ('Blueberry Swirl','Yoghurt','Ripples of blueberry through plain creamy yoghurt.',4000,'250 ml'),
 ('Yoghurt Parfait','Parfait','Layers of yoghurt, fruit and crunchy granola in a cup.',6000,'200 g'),
 ('Probiotic Yoghurt Drink','Drinks','Cold, pourable and packed with live probiotics.',2500,'300 ml'),
 ('Bulk Event Tub','Bulk','Plain or flavoured yoghurt for parties, weddings and offices.',45000,'5 L');
