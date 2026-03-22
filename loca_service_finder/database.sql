-- ============================================================
-- Local Service Finder - Database Schema
-- Import this file via phpMyAdmin or MySQL CLI:
--   mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS local_service_finder
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE local_service_finder;

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('customer','worker','admin') NOT NULL DEFAULT 'customer',
    `phone`      VARCHAR(20)  DEFAULT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: services
-- ============================================================
CREATE TABLE IF NOT EXISTS `services` (
    `id`          INT            NOT NULL AUTO_INCREMENT,
    `user_id`     INT            NOT NULL,
    `title`       VARCHAR(150)   NOT NULL,
    `category`    VARCHAR(100)   NOT NULL,
    `description` TEXT           DEFAULT NULL,
    `price`       DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `phone`       VARCHAR(20)    DEFAULT NULL,
    `location`    VARCHAR(150)   DEFAULT NULL,
    `image`       VARCHAR(255)   DEFAULT NULL,
    `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id`  (`user_id`),
    KEY `idx_category` (`category`),
    CONSTRAINT `fk_services_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`           INT  NOT NULL AUTO_INCREMENT,
    `service_id`   INT  NOT NULL,
    `customer_id`  INT  NOT NULL,
    `status`       ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    `booking_date` DATE DEFAULT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_service_id`  (`service_id`),
    KEY `idx_customer_id` (`customer_id`),
    CONSTRAINT `fk_bookings_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_bookings_customer`
        FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
