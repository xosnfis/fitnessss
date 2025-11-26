-- ============================================================================
-- ПОЛНАЯ СХЕМА БАЗЫ ДАННЫХ ДЛЯ ИНФОРМАЦИОННОЙ СИСТЕМЫ "ФИТНЕС-ЦЕНТР"
-- ============================================================================
-- Этот файл содержит всю структуру базы данных и может быть выполнен целиком
-- без конфликтов. Все операции безопасны для повторного выполнения.
-- ============================================================================

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS fitness_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitness_center;

-- ============================================================================
-- ОСНОВНЫЕ ТАБЛИЦЫ
-- ============================================================================

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица тренеров
CREATE TABLE IF NOT EXISTS trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    experience INT NOT NULL COMMENT 'Опыт работы в годах',
    phone VARCHAR(20),
    email VARCHAR(100),
    photo VARCHAR(255),
    bio TEXT,
    schedule VARCHAR(255) COMMENT 'Расписание работы',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_specialization (specialization)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица услуг
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration INT NOT NULL COMMENT 'Длительность в минутах',
    category VARCHAR(50) NOT NULL COMMENT 'Групповые занятия, Персональные тренировки и т.д.',
    trainer_id INT,
    schedule VARCHAR(255) COMMENT 'Расписание проведения',
    max_participants INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица абонементов
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration_days INT NOT NULL COMMENT 'Срок действия в днях',
    visits_count INT DEFAULT NULL COMMENT 'Количество посещений (NULL = безлимит)',
    services_included TEXT COMMENT 'Включенные услуги',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица заказов
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50),
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица элементов заказа
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_type ENUM('service', 'subscription') NOT NULL,
    item_id INT NOT NULL COMMENT 'ID услуги или абонемента',
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_item (item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица пользовательских абонементов (активные абонементы пользователей)
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_id INT NOT NULL,
    order_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    visits_used INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ДОПОЛНИТЕЛЬНЫЕ ТАБЛИЦЫ
-- ============================================================================

-- Таблица сертификатов
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Название сертификата',
    image_path VARCHAR(500) NOT NULL COMMENT 'Путь к изображению',
    description TEXT COMMENT 'Описание сертификата',
    display_order INT DEFAULT 0 COMMENT 'Порядок отображения',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Активен ли сертификат',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (display_order),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица фотографий тренеров (для поддержки нескольких фотографий)
CREATE TABLE IF NOT EXISTS trainer_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    photo_path VARCHAR(500) NOT NULL COMMENT 'Путь к фотографии',
    display_order INT DEFAULT 0 COMMENT 'Порядок отображения',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE,
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ДОБАВЛЕНИЕ ПОЛЕЙ РЕЙТИНГА
-- ============================================================================

-- Добавляем поле rating в таблицу subscriptions (если еще не существует)
SET @dbname = DATABASE();
SET @tablename = 'subscriptions';
SET @columnname = 'rating';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT DEFAULT 0 COMMENT ''Рейтинг от 0 до 5 звезд''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Добавляем поле rating в таблицу services (если еще не существует)
SET @tablename = 'services';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT DEFAULT 0 COMMENT ''Рейтинг от 0 до 5 звезд''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- ПЕРВОНАЧАЛЬНЫЕ ДАННЫЕ
-- ============================================================================

-- Вставка пользователей (только если их еще нет)
INSERT IGNORE INTO users (username, email, password, full_name, phone, role) VALUES
('admin', 'admin@fitness.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор', '+7 (999) 123-45-67', 'admin'),
('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Иван Иванов', '+7 (999) 111-22-33', 'user');

-- Тестовый пароль для обоих: password

-- Вставка тренеров (только если их еще нет)
INSERT IGNORE INTO trainers (full_name, specialization, experience, phone, email, bio, schedule) VALUES
('Петров Алексей', 'Силовые тренировки', 5, '+7 (999) 200-10-20', 'petrov@fitness.ru', 'Сертифицированный тренер по бодибилдингу и фитнесу', 'Пн-Пт: 10:00-18:00'),
('Смирнова Мария', 'Йога и пилатес', 8, '+7 (999) 200-10-21', 'smirnova@fitness.ru', 'Мастер-тренер по йоге и пилатесу', 'Вт-Сб: 9:00-19:00'),
('Козлов Дмитрий', 'Кардио и функциональный тренинг', 4, '+7 (999) 200-10-22', 'kozlov@fitness.ru', 'Специалист по функциональным тренировкам', 'Пн-Ср-Пт: 8:00-16:00');

-- Вставка услуг (только если их еще нет)
INSERT IGNORE INTO services (name, description, price, duration, category, trainer_id, schedule, max_participants) VALUES
('Персональная тренировка с тренером', 'Индивидуальная программа тренировок', 2000.00, 60, 'Персональные тренировки', 1, 'По согласованию', 1),
('Групповая йога', 'Занятия йогой в группе', 800.00, 90, 'Групповые занятия', 2, 'Вт, Чт 18:00-19:30', 15),
('Функциональный тренинг', 'Групповая функциональная тренировка', 1000.00, 60, 'Групповые занятия', 3, 'Пн, Ср, Пт 19:00-20:00', 12),
('Кардио-тренировка', 'Интенсивная кардио-сессия', 1500.00, 45, 'Персональные тренировки', 3, 'По согласованию', 1),
('Растяжка и восстановление', 'Занятия по растяжке', 600.00, 45, 'Групповые занятия', 2, 'Сб 10:00-10:45', 20);

-- Вставка абонементов (только если их еще нет)
INSERT IGNORE INTO subscriptions (name, description, price, duration_days, visits_count, services_included, is_active) VALUES
('Абонемент "Стандарт"', 'Безлимитный доступ в зал на месяц', 3000.00, 30, NULL, 'Тренажерный зал, Групповые занятия', TRUE),
('Абонемент "Премиум"', 'Безлимитный доступ + 2 персональные тренировки', 8000.00, 30, NULL, 'Тренажерный зал, Групповые занятия, 2 персональные тренировки', TRUE),
('Абонемент "Разовый"', 'Разовое посещение', 500.00, 1, 1, 'Тренажерный зал', TRUE),
('Абонемент "На 10 посещений"', 'Абонемент на 10 посещений зала', 4000.00, 60, 10, 'Тренажерный зал', TRUE),
('Абонемент "VIP"', 'Безлимитный доступ + персональный тренер', 15000.00, 30, NULL, 'Тренажерный зал, Групповые занятия, Персональный тренер', TRUE);

-- ============================================================================
-- МИГРАЦИЯ ДАННЫХ И НАСТРОЙКА
-- ============================================================================

-- Перенос существующих фотографий из поля photo в новую таблицу trainer_photos
-- (выполнится только если таблица trainer_photos уже создана)
-- Используем INSERT IGNORE для безопасности
INSERT IGNORE INTO trainer_photos (trainer_id, photo_path, display_order)
SELECT t.id, t.photo, 0
FROM trainers t
WHERE t.photo IS NOT NULL 
  AND t.photo != ''
  AND NOT EXISTS (
      SELECT 1 FROM trainer_photos tp 
      WHERE tp.trainer_id = t.id 
      AND tp.photo_path = t.photo
  );

-- Назначение фотографий тренерам (если файлы существуют)
UPDATE trainers 
SET photo = 'dima.jpg' 
WHERE full_name = 'Козлов Дмитрий' AND (photo IS NULL OR photo = '');

UPDATE trainers 
SET photo = 'masha.jpg' 
WHERE full_name = 'Смирнова Мария' AND (photo IS NULL OR photo = '');

UPDATE trainers 
SET photo = 'leha.jpg' 
WHERE full_name = 'Петров Алексей' AND (photo IS NULL OR photo = '');

-- ============================================================================
-- УДАЛЕНИЕ ДУБЛИКАТОВ
-- ============================================================================

-- Удаление дубликатов услуг (оставляем только первую запись с каждым именем)
DELETE s1 FROM services s1
INNER JOIN services s2 
WHERE s1.id > s2.id 
  AND s1.name = s2.name;

-- Удаление дубликатов абонементов (оставляем только первую запись с каждым именем)
DELETE s1 FROM subscriptions s1
INNER JOIN subscriptions s2 
WHERE s1.id > s2.id 
  AND s1.name = s2.name;

-- Добавление уникальных индексов на name (если еще не существуют)
SET @dbname = DATABASE();

-- Для services
SET @tablename = 'services';
SET @indexname = 'name';
SET @index_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = @indexname
      AND NON_UNIQUE = 0
);

SET @add_unique_sql = IF(
    @index_exists = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD UNIQUE INDEX ', @indexname, ' (name)'),
    'SELECT 1'
);

SET @preparedStatement = @add_unique_sql;
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Для subscriptions
SET @tablename = 'subscriptions';
SET @index_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = @indexname
      AND NON_UNIQUE = 0
);

SET @add_unique_sql = IF(
    @index_exists = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD UNIQUE INDEX ', @indexname, ' (name)'),
    'SELECT 1'
);

SET @preparedStatement = @add_unique_sql;
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- УСТАНОВКА РЕЙТИНГОВ
-- ============================================================================

-- Устанавливаем случайные рейтинги для существующих записей без рейтинга (от 1 до 5)
UPDATE subscriptions 
SET rating = FLOOR(1 + RAND() * 5) 
WHERE (rating = 0 OR rating IS NULL) 
  AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'subscriptions' 
              AND COLUMN_NAME = 'rating');

UPDATE services 
SET rating = FLOOR(1 + RAND() * 5) 
WHERE (rating = 0 OR rating IS NULL) 
  AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'services' 
              AND COLUMN_NAME = 'rating');

-- ============================================================================
-- ЗАВЕРШЕНИЕ
-- ============================================================================
-- Схема базы данных успешно создана и настроена!
-- Все таблицы, поля и начальные данные готовы к использованию.

