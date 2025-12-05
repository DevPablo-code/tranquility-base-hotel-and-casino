<?php
// Налаштування підключення (мають співпадати з docker-compose.yml)
$host = 'db';               // Ім'я сервісу в Docker (НЕ localhost!)
$db   = 'tranquility_db';   // Назва бази
$user = 'hotel_user';       // Користувач
$pass = 'hotel_password';   // Пароль
$charset = 'utf8mb4';       // Правильне кодування (підтримує смайлики та спецсимволи)

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    // Вмикаємо викидання виключень при помилках (замість мовчазних помилок)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // За замовчуванням повертати дані як асоціативний масив ($row['name'])
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Вимикаємо емуляцію підготовлених запитів (для справжньої безпеки)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Якщо підключення не вдалося - зупиняємо скрипт
    // Для курсової можна виводити текст помилки, щоб швидко знайти проблему
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>