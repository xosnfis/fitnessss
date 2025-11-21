-- База данных для информационной системы "Фитнес-центр"
-- Создание базы данных
CREATE DATABASE IF NOT EXISTS fitness_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitness_center;

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
    name VARCHAR(100) NOT NULL,
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
    name VARCHAR(100) NOT NULL,
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

-- Вставка начальных данных
-- Пароль для admin: admin123 (захеширован)
INSERT INTO users (username, email, password, full_name, phone, role) VALUES
('admin', 'admin@fitness.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор', '+7 (999) 123-45-67', 'admin'),
('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Иван Иванов', '+7 (999) 111-22-33', 'user');

-- Тестовый пароль для обоих: password

INSERT INTO trainers (full_name, specialization, experience, phone, email, bio, schedule) VALUES
('Петров Алексей', 'Силовые тренировки', 5, '+7 (999) 200-10-20', 'petrov@fitness.ru', 'Сертифицированный тренер по бодибилдингу и фитнесу', 'Пн-Пт: 10:00-18:00'),
('Смирнова Мария', 'Йога и пилатес', 8, '+7 (999) 200-10-21', 'smirnova@fitness.ru', 'Мастер-тренер по йоге и пилатесу', 'Вт-Сб: 9:00-19:00'),
('Козлов Дмитрий', 'Кардио и функциональный тренинг', 4, '+7 (999) 200-10-22', 'kozlov@fitness.ru', 'Специалист по функциональным тренировкам', 'Пн-Ср-Пт: 8:00-16:00');

INSERT INTO services (name, description, price, duration, category, trainer_id, schedule, max_participants) VALUES
('Персональная тренировка с тренером', 'Индивидуальная программа тренировок', 2000.00, 60, 'Персональные тренировки', 1, 'По согласованию', 1),
('Групповая йога', 'Занятия йогой в группе', 800.00, 90, 'Групповые занятия', 2, 'Вт, Чт 18:00-19:30', 15),
('Функциональный тренинг', 'Групповая функциональная тренировка', 1000.00, 60, 'Групповые занятия', 3, 'Пн, Ср, Пт 19:00-20:00', 12),
('Кардио-тренировка', 'Интенсивная кардио-сессия', 1500.00, 45, 'Персональные тренировки', 3, 'По согласованию', 1),
('Растяжка и восстановление', 'Занятия по растяжке', 600.00, 45, 'Групповые занятия', 2, 'Сб 10:00-10:45', 20);

INSERT INTO subscriptions (name, description, price, duration_days, visits_count, services_included, is_active) VALUES
('Абонемент "Стандарт"', 'Безлимитный доступ в зал на месяц', 3000.00, 30, NULL, 'Тренажерный зал, Групповые занятия', TRUE),
('Абонемент "Премиум"', 'Безлимитный доступ + 2 персональные тренировки', 8000.00, 30, NULL, 'Тренажерный зал, Групповые занятия, 2 персональные тренировки', TRUE),
('Абонемент "Разовый"', 'Разовое посещение', 500.00, 1, 1, 'Тренажерный зал', TRUE),
('Абонемент "На 10 посещений"', 'Абонемент на 10 посещений зала', 4000.00, 60, 10, 'Тренажерный зал', TRUE),
('Абонемент "VIP"', 'Безлимитный доступ + персональный тренер', 15000.00, 30, NULL, 'Тренажерный зал, Групповые занятия, Персональный тренер', TRUE);

