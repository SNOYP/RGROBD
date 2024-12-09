<?php
session_start();
require_once __DIR__ . '/config.php'; // Подключаем файл конфигурации с абсолютным путём

// Проверяем, нужно ли выйти из аккаунта
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Уничтожаем все сессионные данные
    session_unset();
    session_destroy();
    // Перенаправляем на страницу входа
    header('Location: login.php');
    exit;
}

// Устанавливаем Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self';");

// Функция для безопасного получения параметров из сессии
function getSessionValue($key)
{
    return isset($_SESSION[$key]) ? htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8') : '';
}

// Получение данных пользователя
$userId = getSessionValue('user_id');
$firstName = getSessionValue('first_name');
$lastName = getSessionValue('last_name');

// Полное имя пользователя с ограничением длины
$fullName = trim($firstName . ' ' . $lastName);
if (mb_strlen($fullName) > 20) {
    $fullName = mb_substr($fullName, 0, 17) . '...';
}
?>
<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arh - Arhetecture PC</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts/theme.js" defer></script>
</head>

<body>
    <h1>Arhetecture PC</h1>
    <button id="theme-toggle">Темна тема</button>

    <div class="user-info">
        <?php if ($userId): ?>
            <span class="welcome-text"><?= $fullName ?></span>
            <!-- Кнопка выхода из аккаунта -->
            <button class="button logout-button" onclick="window.location.href='index.php?action=logout'">Вийти</button>
        <?php else: ?>
            <!-- Отображение кнопок для входа и регистрации, если пользователь не авторизован -->
            <button class="button" onclick="window.location.href='login.php'">Вхід</button>
            <button class="button" onclick="window.location.href='register.php'">Реєстрація</button>
        <?php endif; ?>
    </div>

    <nav>
        <button class="menu-button" onclick="window.location.href='consultant.php'">Консультанти</button>
        <button class="menu-button" onclick="window.location.href='client.php'">Клієнти</button>
        <button class="menu-button" onclick="window.location.href='order.php'">Замовлення</button>
        <button class="menu-button" onclick="window.location.href='product.php'">Продукти</button>
        <button class="menu-button" onclick="window.location.href='supplier.php'">Доставщик</button>
    </nav>
    
    <footer>
        <div class="version">Версія 1.9.0</div>
    </footer>
</body>
</html>
