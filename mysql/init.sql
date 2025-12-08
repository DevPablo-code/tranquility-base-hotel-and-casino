
SET NAMES 'utf8mb4';
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS tranquility_db;
USE tranquility_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    google_2fa_secret VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password_hash, role) 
VALUES ('admin', '$2a$12$IMSZxWG91FvM66QmmioyYuaapwsOFtGfflUuw47/wA8egd33zmOPy', 'admin');

CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    position VARCHAR(50) NOT NULL,
    salary DECIMAL(10, 2) NOT NULL,
    employment_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO staff (full_name, position, salary, employment_date, is_active) VALUES
('Mark O. G. S.', 'Concierge/System Manager', 90000.00, '2025-01-01', TRUE),
('S. L. Thompson', 'Security Officer', 55000.00, '2024-11-15', TRUE),
('Alex A. P.', 'Maintenance Engineer', 62000.00, '2025-02-20', TRUE),
('Jane Doe', 'On Leave', 0.00, '2024-05-01', FALSE);

CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(5) NOT NULL UNIQUE, name VARCHAR(50) NOT NULL
);

INSERT INTO languages (code, name) VALUES ('en', 'English'), ('ua', 'Українська');

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(10) NOT NULL UNIQUE,
    price DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL DEFAULT 2,
    image VARCHAR(255) NULL,
    status ENUM('free', 'occupied', 'cleaning') DEFAULT 'free',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FULLTEXT(number)
);

CREATE TABLE IF NOT EXISTS room_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    language_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
        FULLTEXT(title, description)
);

CREATE TABLE IF NOT EXISTS features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS feature_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feature_id INT NOT NULL,
    language_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    FOREIGN KEY (feature_id) REFERENCES features(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_feature_trans (feature_id, language_id)
);

CREATE TABLE IF NOT EXISTS room_features (
    room_id INT NOT NULL,
    feature_id INT NOT NULL,
    PRIMARY KEY (room_id, feature_id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (feature_id) REFERENCES features(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    passport_no VARCHAR(20) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(50) NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    notes TEXT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS room_photos (
    room_id INT NOT NULL,
    photo_id INT NOT NULL,
    is_primary BOOLEAN DEFAULT 0,
    sort_order INT DEFAULT 0,
    
    PRIMARY KEY (room_id, photo_id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

DELIMITER //
CREATE TRIGGER after_room_insert 
AFTER INSERT ON rooms
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (action, details, created_at)
    VALUES ('SYSTEM_AUTO', CONCAT('New room created: ', NEW.number), NOW());
END;
//
DELIMITER ;


INSERT INTO features (id, name) VALUES 
(1, 'Wi-Fi'), (2, 'Moon View'), (3, 'Zero-G Bed'), (4, 'Retro Terminal'), (5, 'Mini-bar');

INSERT INTO feature_translations (feature_id, language_id, name) VALUES 
(1, 1, 'Wi-Fi'), (1, 2, 'Wi-Fi'),
(2, 1, 'Moon View'), (2, 2, 'Вид на Місяць'),
(3, 1, 'Zero-G Bed'), (3, 2, 'Ліжко невагомості'),
(4, 1, 'Retro Terminal'), (4, 2, 'Ретро термінал'),
(5, 1, 'Mini-bar'), (5, 2, 'Міні-бар');

INSERT INTO rooms (id, number, price, capacity, image, status) VALUES (1, '505', 450.00, 4, 'room_505.jpg', 'free');
INSERT INTO room_translations (room_id, language_id, title, description) VALUES 
(1, 1, 'Crater View Suite', 'A stunning suite overlooking the Mare Tranquillitatis impact crater. Features 70s decor.'),
(1, 2, 'Люкс з видом на кратер', "Розкішний люкс з видом на кратер Моря Спокою. Інтер'єр у стилі 70-х.");
INSERT INTO room_features (room_id, feature_id) VALUES (1, 1), (1, 2), (1, 4);

INSERT INTO rooms (id, number, price, capacity, image, status) VALUES (2, '101', 120.00, 1, 'room_101.jpg', 'free');
INSERT INTO room_translations (room_id, language_id, title, description) VALUES 
(2, 1, 'Standard Module', 'Compact living module. Perfect for short stays. Close to the taqueria.'),
(2, 2, 'Стандартний модуль', 'Компактний житловий модуль. Ідеально для коротких візитів. Близько до такером.');
INSERT INTO room_features (room_id, feature_id) VALUES (2, 1), (2, 5);

INSERT INTO rooms (id, number, price, capacity, image, status) VALUES (3, '303', 800.00, 2, 'room_303.jpg', 'occupied');
INSERT INTO room_translations (room_id, language_id, title, description) VALUES 
(3, 1, 'Presidential Suite', 'The most exclusive room on the dark side of the moon.'),
(3, 2, 'Президентський люкс', 'Найексклюзивніший номер на темному боці місяця.');
INSERT INTO room_features (room_id, feature_id) VALUES (3, 1), (3, 2), (3, 3), (3, 5);

INSERT INTO photos (id, filename, alt_text) VALUES 
(1, 'room_505.jpg', 'Crater View Suite Main'),
(2, 'room_101.jpg', 'Standard Module Main'),
(3, 'room_303.jpg', 'Presidential Suite Main'),
(4, 'bathroom_lux.jpg', 'Luxury Bathroom with Gold Faucets'),
(5, 'view_crater.jpg', 'View of Mare Tranquillitatis');

INSERT INTO room_photos (room_id, photo_id, is_primary, sort_order) VALUES 
(1, 1, 1, 1),
(1, 4, 0, 2),
(1, 5, 0, 3);

INSERT INTO room_photos (room_id, photo_id, is_primary) VALUES (2, 2, 1);

INSERT INTO room_photos (room_id, photo_id, is_primary) VALUES 
(3, 3, 1),
(3, 4, 0);

INSERT INTO rooms (id, number, price, capacity, image, status) VALUES (4, '202', 250.00, 2, 'room_202.jpg', 'free');

INSERT INTO room_translations (room_id, language_id, title, description) VALUES 
(4, 1, 'Hydroponic Garden Pod', 'Relax surrounded by lunar flora. Oxygen-rich atmosphere and soft green lighting.'),
(4, 2, 'Модуль Гідропонний Сад', 'Відпочивайте в оточенні місячної флори. Збагачена киснем атмосфера та м''яке зелене освітлення.');

INSERT INTO room_features (room_id, feature_id) VALUES (4, 1), (4, 5);

INSERT INTO rooms (id, number, price, capacity, image, status) VALUES (5, '777', 1500.00, 6, 'room_777.jpg', 'cleaning');

INSERT INTO room_translations (room_id, language_id, title, description) VALUES 
(5, 1, 'High Roller Penthouse', 'Direct access to the casino floor. Includes a private poker table and velvet interior.'),
(5, 2, 'Пентхаус Хайроллера', 'Прямий доступ до казино. Включає приватний стіл для покеру та оксамитовий інтер''єр.');

INSERT INTO room_features (room_id, feature_id) VALUES (5, 1), (5, 2), (5, 3), (5, 4), (5, 5);

INSERT INTO rooms (id, number, price, capacity, image, status) VALUES (6, '009', 55.00, 1, 'room_009.jpg', 'free');

INSERT INTO room_translations (room_id, language_id, title, description) VALUES 
(6, 1, 'Cryo-Sleep Capsule', 'Minimalist soundproof capsule for deep rest. No windows, just silence.'),
(6, 2, 'Капсула Кріо-Сну', 'Мінімалістична шумоізольована капсула для глибокого відпочинку. Без вікон, тільки тиша.');

INSERT INTO room_features (room_id, feature_id) VALUES (6, 1), (6, 4);

INSERT INTO photos (id, filename, alt_text) VALUES 
(6, 'room_202.jpg', 'Greenhouse Room View'),
(7, 'room_777.jpg', 'Golden Casino Penthouse Interior'),
(8, 'room_009.jpg', 'White Minimalist Capsule'),
(9, 'poker_table.jpg', 'Private Poker Table');

INSERT INTO room_photos (room_id, photo_id, is_primary, sort_order) VALUES (4, 6, 1, 1);

INSERT INTO room_photos (room_id, photo_id, is_primary, sort_order) VALUES 
(5, 7, 1, 1),
(5, 9, 0, 2), 
(5, 4, 0, 3),
(5, 5, 0, 4);

INSERT INTO room_photos (room_id, photo_id, is_primary, sort_order) VALUES (6, 8, 1, 1);



DELIMITER $$
CREATE FUNCTION levenshtein( s1 VARCHAR(255) CHARSET utf8mb4, s2 VARCHAR(255) CHARSET utf8mb4)
    RETURNS INT
    DETERMINISTIC
BEGIN
    DECLARE s1_len, s2_len, i, j, c, c_temp, cost INT;
    DECLARE s1_char CHAR(1) CHARSET utf8mb4;

    DECLARE cv0, cv1 VARBINARY(256);
    SET s1_len = CHAR_LENGTH(s1), s2_len = CHAR_LENGTH(s2), cv1 = 0x00, j = 1, i = 1, c = 0;
    IF s1 = s2 THEN
        RETURN 0;
    ELSEIF s1_len = 0 THEN
        RETURN s2_len;
    ELSEIF s2_len = 0 THEN
        RETURN s1_len;
    ELSE
        WHILE j <= s2_len DO
            SET cv1 = CONCAT(cv1, UNHEX(HEX(j))), j = j + 1;
        END WHILE;
        WHILE i <= s1_len DO
            SET s1_char = SUBSTRING(s1, i, 1), c = i, cv0 = UNHEX(HEX(i)), j = 1;
            WHILE j <= s2_len DO
                SET c = c + 1;
                IF s1_char = SUBSTRING(s2, j, 1) THEN SET cost = 0; ELSE SET cost = 1; END IF;
                SET c_temp = CONV(HEX(SUBSTRING(cv1, j, 1)), 16, 10);
                IF c > c_temp + cost THEN SET c = c_temp + cost; END IF;
                SET c_temp = CONV(HEX(SUBSTRING(cv1, j+1, 1)), 16, 10);
                IF c > c_temp + 1 THEN SET c = c_temp + 1; END IF;
                SET cv0 = CONCAT(cv0, UNHEX(HEX(c))), j = j + 1;
            END WHILE;
            SET cv1 = cv0, i = i + 1;
        END WHILE;
    END IF;
    RETURN c;
END$$
DELIMITER ;