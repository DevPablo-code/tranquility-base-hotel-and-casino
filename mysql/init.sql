
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
VALUES ('admin', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRps.9cGLcZEiGDMVr5yUP1KUOYTa', 'admin');

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
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
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