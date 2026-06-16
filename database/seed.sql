-- =====================================================================
--  5VITO — seed data
--  Run AFTER schema.sql:   mysql -u root -p fvito < database/seed.sql
-- =====================================================================
--  Demo credentials (passwords are bcrypt-hashed below):
--    admin@5vito.ru    / admin123      (role: admin)
--    ivan@example.com  / password123   (role: user)
--    maria@example.com / password123   (role: user)
-- =====================================================================

USE fvito;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE admin_logs;
TRUNCATE reports;
TRUNCATE favorites;
TRUNCATE car_images;
TRUNCATE cars;
TRUNCATE models;
TRUNCATE brands;
TRUNCATE users;
TRUNCATE roles;
SET FOREIGN_KEY_CHECKS = 1;

-- roles ---------------------------------------------------------------
INSERT INTO roles (id, name) VALUES
  (1, 'admin'),
  (2, 'user');

-- users (bcrypt $2y$ hashes) ------------------------------------------
INSERT INTO users (id, role_id, name, email, phone, password_hash, is_blocked) VALUES
  (1, 1, 'Администратор', 'admin@5vito.ru',   '+79000000000', '$2y$10$I.xQhRONH8dBwRus92aUveGrRTELnC1yC/ES4BNgvHk458LIjeQ6C', 0),
  (2, 2, 'Иван Петров',   'ivan@example.com', '+79001111111', '$2y$10$IS2j2qXQBFUChuQU6XA.VepLzMTdWbZp0cYgcNG0PGmmV4Wos6S8G', 0),
  (3, 2, 'Мария Сидорова','maria@example.com','+79002222222', '$2y$10$IS2j2qXQBFUChuQU6XA.VepLzMTdWbZp0cYgcNG0PGmmV4Wos6S8G', 0);

-- brands --------------------------------------------------------------
INSERT INTO brands (id, name, origin) VALUES
  (1, 'BMW',           'foreign'),
  (2, 'Mercedes-Benz', 'foreign'),
  (3, 'ВАЗ (LADA)',    'domestic'),
  (4, 'Toyota',        'foreign'),
  (5, 'УАЗ',           'domestic');

-- models --------------------------------------------------------------
INSERT INTO models (id, brand_id, name) VALUES
  (1, 1, 'M5'),
  (2, 1, 'X2'),
  (3, 2, 'E-класс'),
  (4, 3, 'Granta'),
  (5, 3, 'Vesta'),
  (6, 3, 'Niva Legend'),
  (7, 4, 'Camry');

-- cars ----------------------------------------------------------------
INSERT INTO cars
  (id, user_id, brand_id, model_id, title, description, price, year, mileage,
   fuel_type, transmission, drive_type, body_type, engine_volume, power_hp,
   color, owners_count, vin, status, is_active)
VALUES
  (1, 2, 1, 1, 'BMW M5 4.4 AT, 2026',
   'Новый автомобиль в максимальной комплектации. Без пробега по РФ.',
   21000000.00, 2026, 0, 'petrol', 'automatic', 'full', 'Седан', 4.4, 625,
   'Чёрный', 1, 'WBSXXXXXXXXXXXXX1', 'approved', 1),

  (2, 2, 2, 3, 'Mercedes-Benz E-класс AMG 3.0 AT, 2017',
   'НОВИНКА В НАШЕМ АВТОСАЛОНЕ!!! Автомобиль с пробегом 66 000 км. В редкой комплектации AMG. Эксплуатировался в городе Краснодар, соли и реагентов не видал!',
   6100000.00, 2017, 66000, 'petrol', 'automatic', 'full', 'Седан', 3.0, 435,
   'Чёрный', 2, 'WDD2XXXXXXXXXXXXX', 'approved', 1),

  (3, 3, 3, 4, 'ВАЗ (LADA) Granta 1.6 MT, 2026',
   'Новая Гранта в комплектации Comfort. Гарантия завода.',
   995000.00, 2026, 0, 'petrol', 'manual', 'front', 'Седан', 1.6, 90,
   'Белый', 1, 'XTAXXXXXXXXXXXXX2', 'approved', 1),

  (4, 3, 1, 2, 'BMW X2 2.0 AMT, 2025',
   'Кроссовер в отличном состоянии, на гарантии.',
   6499000.00, 2025, 12000, 'petrol', 'robot', 'full', 'Кроссовер', 2.0, 192,
   'Синий', 1, 'WBAXXXXXXXXXXXXX3', 'approved', 1),

  (5, 3, 3, 6, 'ВАЗ (LADA) Niva Legend 1.7 MT, 2026',
   'Классическая Нива, полный привод, готова к бездорожью.',
   1804000.00, 2026, 0, 'petrol', 'manual', 'full', 'Внедорожник', 1.7, 83,
   'Зелёный', 1, 'XTAXXXXXXXXXXXXX4', 'approved', 1),

  (6, 2, 3, 5, 'ВАЗ (LADA) Vesta 1.8 MT, 2026',
   'Веста SW Cross, увеличенный клиренс, богатая комплектация.',
   1185000.00, 2026, 0, 'petrol', 'manual', 'front', 'Универсал', 1.8, 122,
   'Серый', 1, 'XTAXXXXXXXXXXXXX5', 'pending', 1);

-- car_images (placeholder URLs served by frontend) --------------------
INSERT INTO car_images (car_id, url, is_primary, sort_order) VALUES
  (1, '/placeholder-car.svg', 1, 0),
  (2, '/placeholder-car.svg', 1, 0),
  (3, '/placeholder-car.svg', 1, 0),
  (4, '/placeholder-car.svg', 1, 0),
  (5, '/placeholder-car.svg', 1, 0),
  (6, '/placeholder-car.svg', 1, 0);

-- favorites -----------------------------------------------------------
INSERT INTO favorites (user_id, car_id) VALUES
  (2, 3),
  (3, 1);

-- reports -------------------------------------------------------------
INSERT INTO reports (car_id, user_id, reason, status) VALUES
  (4, 2, 'Подозрительно низкая цена', 'open');
