CREATE DATABASE IF NOT EXISTS cozycafe
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE cozycafe;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS promotion_menus;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS reservation_closures;
DROP TABLE IF EXISTS operating_hours;
DROP TABLE IF EXISTS tables;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menus (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_menus_category (category_id),
    INDEX idx_menus_available (is_available),
    INDEX idx_menus_name (name),
    CONSTRAINT fk_menus_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tables (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    table_number VARCHAR(20) NOT NULL,
    capacity INT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tables_number (table_number),
    INDEX idx_tables_active_capacity (is_active, capacity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE operating_hours (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    day_of_week TINYINT UNSIGNED NOT NULL,
    open_time TIME NOT NULL,
    close_time TIME NOT NULL,
    is_closed TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_operating_hours_day (day_of_week),
    CONSTRAINT chk_operating_hours_day CHECK (day_of_week <= 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservation_closures (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_reservation_closures_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    table_id INT UNSIGNED NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    guest_count INT UNSIGNED NOT NULL,
    notes TEXT NULL,
    status ENUM('Confirmed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Confirmed',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_table_date_status (table_id, booking_date, status),
    INDEX idx_bookings_date_time (booking_date, start_time, end_time),
    CONSTRAINT fk_bookings_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_table
        FOREIGN KEY (table_id) REFERENCES tables(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE carts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_carts_user (user_id),
    CONSTRAINT fk_carts_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cart_id INT UNSIGNED NOT NULL,
    menu_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    notes VARCHAR(255) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cart_items_cart_menu (cart_id, menu_id),
    INDEX idx_cart_items_menu (menu_id),
    CONSTRAINT fk_cart_items_cart
        FOREIGN KEY (cart_id) REFERENCES carts(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_menu
        FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_code VARCHAR(30) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    booking_id INT UNSIGNED NULL,
    order_type ENUM('Dine-in', 'Takeaway') NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Preparing', 'Ready', 'Completed', 'Cancelled')
        NOT NULL DEFAULT 'Pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_code (order_code),
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_booking (booking_id),
    INDEX idx_orders_status_created (status, created_at),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    menu_id INT UNSIGNED NOT NULL,
    menu_name_snapshot VARCHAR(150) NOT NULL,
    price_snapshot DECIMAL(10,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    notes VARCHAR(255) NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_order_items_order (order_id),
    INDEX idx_order_items_menu (menu_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_menu
        FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash',
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid',
    paid_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payments_order (order_id),
    INDEX idx_payments_status (status),
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE promotions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_promotions_active_dates (is_active, start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE promotion_menus (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    promotion_id INT UNSIGNED NOT NULL,
    menu_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_promotion_menus_pair (promotion_id, menu_id),
    INDEX idx_promotion_menus_menu (menu_id),
    CONSTRAINT fk_promotion_menus_promotion
        FOREIGN KEY (promotion_id) REFERENCES promotions(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_promotion_menus_menu
        FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    link VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_notifications_user_read (user_id, is_read),
    INDEX idx_notifications_created (created_at),
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, phone, password, role) VALUES
    ('CozyCafe Admin', 'admin@cozycafe.com', NULL,
     '$2y$10$dYdn4yjBby2/RDYq8Yo4mu690xYXESbpsijWf2jUfzRJw.D9vbVU2', 'admin'),
    ('CozyCafe Customer', 'customer@cozycafe.com', NULL,
     '$2y$10$dYdn4yjBby2/RDYq8Yo4mu690xYXESbpsijWf2jUfzRJw.D9vbVU2', 'customer');

INSERT INTO categories (name, description) VALUES
    ('Coffee', 'Minuman olahan kopi pilihan spesial, hangat dan dingin'),
    ('Non-Coffee', 'Teh, cokelat, dan aneka minuman non-kopi pilihan'),
    ('Foods', 'Hidangan utama dan makanan berat lezat'),
    ('Snacks', 'Camilan dan kudapan ringan renyah'),
    ('Desserts', 'Hidangan penutup dan pencuci mulut manis');

INSERT INTO operating_hours
    (day_of_week, open_time, close_time, is_closed)
VALUES
    (0, '08:00:00', '22:00:00', 0),
    (1, '08:00:00', '22:00:00', 0),
    (2, '08:00:00', '22:00:00', 0),
    (3, '08:00:00', '22:00:00', 0),
    (4, '08:00:00', '22:00:00', 0),
    (5, '08:00:00', '22:00:00', 0),
    (6, '08:00:00', '22:00:00', 0);

INSERT INTO tables (table_number, capacity, is_active) VALUES
    ('T01', 2, 1),
    ('T02', 2, 1),
    ('T03', 4, 1),
    ('T04', 4, 1),
    ('T05', 6, 1),
    ('T06', 6, 1);
