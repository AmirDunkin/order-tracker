-- =============================================================================
-- OrderTrack — MySQL dump (schema + seed data)
-- =============================================================================
-- Target: cPanel shared hosting / PHP 8.x + MySQL 5.7+ or MariaDB 10.3+
--
-- cPanel import steps:
--   1. Create database in cPanel → MySQL Databases (e.g. cpaneluser_order_tracker)
--   2. Create a MySQL user and assign ALL PRIVILEGES to that database
--   3. Open phpMyAdmin → select your database → Import → choose this file
--   4. Do NOT run CREATE DATABASE on cPanel — import into the DB you created
--
-- Local XAMPP import:
--   mysql -u root -e "CREATE DATABASE IF NOT EXISTS order_tracker"
--   mysql -u root order_tracker < database/order_tracker.sql
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_status_logs;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------------
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('customer', 'shopper') NOT NULL DEFAULT 'customer',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- orders
-- ---------------------------------------------------------------------------
CREATE TABLE orders (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number     VARCHAR(20)   NOT NULL,
    customer_id      INT UNSIGNED  NOT NULL,
    title            VARCHAR(200)  NOT NULL,
    description      TEXT          NULL,
    items            JSON          NOT NULL,
    status           ENUM('pending', 'confirmed', 'shopping', 'ready', 'delivered', 'cancelled')
                     NOT NULL DEFAULT 'pending',
    priority         ENUM('normal', 'urgent') NOT NULL DEFAULT 'normal',
    delivery_address VARCHAR(500)  NOT NULL,
    notes            TEXT          NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_orders_order_number (order_number),
    INDEX idx_orders_customer_id (customer_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_priority (priority),
    INDEX idx_orders_created_at (created_at),
    INDEX idx_orders_status_priority (status, priority),

    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES users (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- order_status_logs
-- ---------------------------------------------------------------------------
CREATE TABLE order_status_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED  NOT NULL,
    changed_by  INT UNSIGNED  NOT NULL,
    old_status  VARCHAR(20)   NULL,
    new_status  VARCHAR(20)   NOT NULL,
    note        TEXT          NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_status_logs_order_id (order_id),
    INDEX idx_status_logs_changed_by (changed_by),
    INDEX idx_status_logs_created_at (created_at),

    CONSTRAINT fk_status_logs_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_status_logs_user
        FOREIGN KEY (changed_by) REFERENCES users (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Seed data
-- Password for ALL demo accounts: Password123!
-- ---------------------------------------------------------------------------

INSERT INTO users (id, name, email, password, role, created_at) VALUES
(1, 'Alice Johnson', 'alice@example.com', '$2y$10$F9Kl.bLwWlQxWwoI.oXZ1eigEIgLM/eQz.89AqXOmb9l7pIFESey.', 'customer', '2026-06-01 09:00:00'),
(2, 'Bob Smith',     'bob@example.com',   '$2y$10$F9Kl.bLwWlQxWwoI.oXZ1eigEIgLM/eQz.89AqXOmb9l7pIFESey.', 'customer', '2026-06-02 10:30:00'),
(3, 'Sarah Shopper', 'sarah@example.com', '$2y$10$F9Kl.bLwWlQxWwoI.oXZ1eigEIgLM/eQz.89AqXOmb9l7pIFESey.', 'shopper',  '2026-06-01 08:00:00');

INSERT INTO orders (id, order_number, customer_id, title, description, items, status, priority, delivery_address, notes, created_at, updated_at) VALUES
(1, 'ORD-2026-0001', 1, 'Weekly Groceries',
 'Standard weekly grocery run',
 '[{"name":"Milk 2L","qty":2,"unit":"bottle"},{"name":"Bread","qty":1,"unit":"loaf"},{"name":"Eggs","qty":12,"unit":"pcs"}]',
 'pending', 'normal',
 '12 Maple Street, Apt 4B, Springfield', 'Please call on arrival', '2026-06-20 08:00:00', '2026-06-20 08:00:00'),

(2, 'ORD-2026-0002', 1, 'Party Supplies',
 'Birthday party essentials needed by Friday',
 '[{"name":"Paper plates","qty":50,"unit":"pcs"},{"name":"Balloons","qty":20,"unit":"pcs"},{"name":"Cake candles","qty":1,"unit":"pack"}]',
 'confirmed', 'urgent',
 '12 Maple Street, Apt 4B, Springfield', 'Need before 5 PM Friday', '2026-06-21 14:30:00', '2026-06-22 09:15:00'),

(3, 'ORD-2026-0003', 2, 'Office Snacks',
 'Restock break room snacks',
 '[{"name":"Granola bars","qty":24,"unit":"pcs"},{"name":"Bottled water","qty":12,"unit":"bottle"},{"name":"Mixed nuts","qty":3,"unit":"bag"}]',
 'shopping', 'normal',
 '88 Commerce Blvd, Suite 200, Springfield', NULL, '2026-06-22 11:00:00', '2026-06-23 10:45:00'),

(4, 'ORD-2026-0004', 2, 'Pharmacy Pickup',
 'Prescription and OTC items',
 '[{"name":"Vitamin D","qty":1,"unit":"bottle"},{"name":"Hand sanitizer","qty":2,"unit":"bottle"}]',
 'ready', 'urgent',
 '88 Commerce Blvd, Suite 200, Springfield', 'Prescription under name Bob Smith', '2026-06-23 09:00:00', '2026-06-24 15:30:00'),

(5, 'ORD-2026-0005', 1, 'Pet Supplies',
 'Monthly pet food and treats',
 '[{"name":"Dog food 15kg","qty":1,"unit":"bag"},{"name":"Cat litter","qty":2,"unit":"bag"},{"name":"Dog treats","qty":1,"unit":"pack"}]',
 'delivered', 'normal',
 '12 Maple Street, Apt 4B, Springfield', 'Leave at front door if not home', '2026-06-15 07:30:00', '2026-06-16 16:00:00');

INSERT INTO order_status_logs (order_id, changed_by, old_status, new_status, note, created_at) VALUES
(1, 1, NULL,          'pending',   'Order placed by customer',           '2026-06-20 08:00:00'),
(2, 1, NULL,          'pending',   'Order placed by customer',           '2026-06-21 14:30:00'),
(2, 3, 'pending',     'confirmed', 'Order confirmed and assigned',       '2026-06-22 09:15:00'),
(3, 2, NULL,          'pending',   'Order placed by customer',           '2026-06-22 11:00:00'),
(3, 3, 'pending',     'confirmed', 'Order confirmed',                    '2026-06-22 16:00:00'),
(3, 3, 'confirmed',   'shopping',  'Shopper started collecting items',   '2026-06-23 10:45:00'),
(4, 2, NULL,          'pending',   'Order placed by customer',           '2026-06-23 09:00:00'),
(4, 3, 'pending',     'confirmed', 'Urgent order prioritised',           '2026-06-23 10:00:00'),
(4, 3, 'confirmed',   'shopping',  'Items collected from pharmacy',      '2026-06-24 14:00:00'),
(4, 3, 'shopping',    'ready',     'Ready for delivery',                 '2026-06-24 15:30:00'),
(5, 1, NULL,          'pending',   'Order placed by customer',           '2026-06-15 07:30:00'),
(5, 3, 'pending',     'confirmed', 'Order confirmed',                    '2026-06-15 09:00:00'),
(5, 3, 'confirmed',   'shopping',  'Shopping in progress',               '2026-06-15 11:00:00'),
(5, 3, 'shopping',    'ready',     'Packed and ready',                   '2026-06-16 10:00:00'),
(5, 3, 'ready',       'delivered', 'Delivered to customer',              '2026-06-16 16:00:00');
