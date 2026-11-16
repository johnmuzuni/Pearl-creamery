-- Add support for multi-product orders.
-- Run this in phpMyAdmin (select your DB first) if you already imported the old schema.

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

-- Make the legacy single-product columns optional (multi-item orders use order_items).
ALTER TABLE orders MODIFY product_id INT NULL;
ALTER TABLE orders MODIFY quantity INT NULL;