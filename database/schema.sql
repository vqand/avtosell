-- =====================================================================
--  5VITO — Car Sales Marketplace
--  MySQL schema (normalized, InnoDB, utf8mb4)
-- =====================================================================
--  Run with:  mysql -u root -p < database/schema.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS fvito
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fvito;

-- Drop in FK-safe order (useful when re-running) ----------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS car_images;
DROP TABLE IF EXISTS cars;
DROP TABLE IF EXISTS models;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  roles
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(32)      NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  users
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    role_id       TINYINT UNSIGNED  NOT NULL DEFAULT 2,
    name          VARCHAR(120)      NOT NULL,
    email         VARCHAR(190)      NOT NULL,
    phone         VARCHAR(32)       DEFAULT NULL,
    password_hash VARCHAR(255)      NOT NULL,
    is_blocked    TINYINT(1)        NOT NULL DEFAULT 0,
    created_at    TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    KEY idx_users_phone (phone),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  brands  (BMW, Mercedes-Benz, ВАЗ (LADA) ...)
-- ---------------------------------------------------------------------
CREATE TABLE brands (
    id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(80)       NOT NULL,
    -- origin: 'domestic' (Отечественные) | 'foreign' (Иномарки)
    origin      ENUM('domestic','foreign') NOT NULL DEFAULT 'foreign',
    PRIMARY KEY (id),
    UNIQUE KEY uq_brands_name (name),
    KEY idx_brands_origin (origin)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  models  (each model belongs to a brand)
-- ---------------------------------------------------------------------
CREATE TABLE models (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    brand_id    SMALLINT UNSIGNED NOT NULL,
    name        VARCHAR(120)      NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_models_brand_name (brand_id, name),
    KEY idx_models_brand (brand_id),
    CONSTRAINT fk_models_brand FOREIGN KEY (brand_id) REFERENCES brands (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  cars  (the listing)
-- ---------------------------------------------------------------------
CREATE TABLE cars (
    id              INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED      NOT NULL,      -- seller
    brand_id        SMALLINT UNSIGNED NOT NULL,
    model_id        INT UNSIGNED      DEFAULT NULL,
    title           VARCHAR(200)      NOT NULL,      -- "Mercedes-Benz E-класс AMG 3.0 AT, 2017"
    description     TEXT,
    price           DECIMAL(12,2)     NOT NULL,
    year            SMALLINT UNSIGNED NOT NULL,
    mileage         INT UNSIGNED      NOT NULL DEFAULT 0,
    fuel_type       ENUM('petrol','diesel','hybrid','electric','gas') NOT NULL DEFAULT 'petrol',
    transmission    ENUM('manual','automatic','robot','variator') NOT NULL DEFAULT 'automatic',
    drive_type      ENUM('rear','front','full') NOT NULL DEFAULT 'front',
    body_type       VARCHAR(60)       DEFAULT NULL,
    engine_volume   DECIMAL(4,1)      DEFAULT NULL,  -- litres, e.g. 3.0
    power_hp        SMALLINT UNSIGNED DEFAULT NULL,
    color           VARCHAR(40)       DEFAULT NULL,
    owners_count    TINYINT UNSIGNED  DEFAULT NULL,
    vin             VARCHAR(40)       DEFAULT NULL,
    -- moderation
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    is_active       TINYINT(1)        NOT NULL DEFAULT 1,
    created_at      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cars_user (user_id),
    KEY idx_cars_brand (brand_id),
    KEY idx_cars_model (model_id),
    KEY idx_cars_price (price),
    KEY idx_cars_year (year),
    KEY idx_cars_status_active (status, is_active),
    KEY idx_cars_filter (brand_id, price, year),   -- composite for common filter
    FULLTEXT KEY ft_cars_search (title, description),
    CONSTRAINT fk_cars_user  FOREIGN KEY (user_id)  REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cars_brand FOREIGN KEY (brand_id) REFERENCES brands (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_cars_model FOREIGN KEY (model_id) REFERENCES models (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  car_images  (one car -> many images)
-- ---------------------------------------------------------------------
CREATE TABLE car_images (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    car_id      INT UNSIGNED  NOT NULL,
    url         VARCHAR(255)  NOT NULL,
    is_primary  TINYINT(1)    NOT NULL DEFAULT 0,
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_car_images_car (car_id),
    CONSTRAINT fk_car_images_car FOREIGN KEY (car_id) REFERENCES cars (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  favorites  (user <-> car, many-to-many)
-- ---------------------------------------------------------------------
CREATE TABLE favorites (
    user_id     INT UNSIGNED  NOT NULL,
    car_id      INT UNSIGNED  NOT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, car_id),
    KEY idx_favorites_car (car_id),
    CONSTRAINT fk_fav_user FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_fav_car  FOREIGN KEY (car_id)  REFERENCES cars (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  reports  (user reports a listing — "Пожаловаться на объявление")
-- ---------------------------------------------------------------------
CREATE TABLE reports (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    car_id      INT UNSIGNED  NOT NULL,
    user_id     INT UNSIGNED  DEFAULT NULL,   -- reporter (nullable if anonymous)
    reason      VARCHAR(255)  NOT NULL,
    status      ENUM('open','reviewed','dismissed') NOT NULL DEFAULT 'open',
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_reports_car (car_id),
    KEY idx_reports_status (status),
    CONSTRAINT fk_reports_car  FOREIGN KEY (car_id)  REFERENCES cars (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  admin_logs  (audit trail of admin actions)
-- ---------------------------------------------------------------------
CREATE TABLE admin_logs (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    admin_id    INT UNSIGNED  NOT NULL,
    action      VARCHAR(120)  NOT NULL,
    entity_type VARCHAR(60)   DEFAULT NULL,
    entity_id   INT UNSIGNED  DEFAULT NULL,
    details     TEXT,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_logs_admin (admin_id),
    CONSTRAINT fk_admin_logs_admin FOREIGN KEY (admin_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
