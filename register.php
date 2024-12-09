<?php
session_start();
require_once __DIR__ . '/config.php'; // Подключаем файл конфигурации с подключением к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $confirmPassword = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

    if ($firstName && $lastName && $email && $password && $confirmPassword) {
        // Проверка совпадения паролей
        if ($password !== $confirmPassword) {
            $errorMessage = 'Пароли не совпадают!';
        } else {
            // Проверка существования пользователя
            $query = "SELECT id FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errorMessage = 'Пользователь с таким email уже существует!';
            } else {
                // Хешируем пароль
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Вставка нового пользователя в базу
                $query = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
                if ($stmt->execute()) {
                    header('Location: login.php');
                    exit;
                } else {
                    $errorMessage = 'Ошибка регистрации. Попробуйте позже.';
                }
            }
        }
    } else {
        $errorMessage = 'Заполните все поля!';
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>
    <style>
        /* Общие стили */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333; /* Цвет текста по умолчанию */
        }

        .register-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* Стили для формы */
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #4CAF50;
            outline: none;
        }

        /* Кнопки */
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        p {
            font-size: 14px;
            color: #555;
        }

        a {
            color: #4CAF50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Темная тема */
        body.dark-theme {
            background-color: #121212;
            color: #f4f4f4; /* Белый текст в тёмной теме */
        }

        body.dark-theme .register-container {
            background-color: #333;
            box-shadow: 0 4px 8px rgba(255, 255, 255, 0.2);
        }

        body.dark-theme h2 {
            color: #fff;
        }

        body.dark-theme .error-message {
            color: #ff6666;
        }

        body.dark-theme .form-group label {
            color: #f4f4f4; /* Белый цвет для текста меток в тёмной теме */
        }

        body.dark-theme button {
            background-color: #555;
        }

        body.dark-theme button:hover {
            background-color: #444;
        }

        body.dark-theme p, body.dark-theme a {
            color: #f4f4f4; /* Белый текст для параграфов и ссылок в тёмной теме */
        }

        /* Кнопка переключения темы */
        #theme-toggle {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Реєстрація</h2>

        <?php if (isset($errorMessage)): ?>
            <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="first_name">Ім'я:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Прізвище:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="email">Електронна пошта:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Підтвердьте пароль:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Зареєструватися</button>
        </form>

        <p>Уже є обліковий запис? <a href="login.php">Увійти</a></p>

        <!-- Кнопка для переключения темы -->
        <button id="theme-toggle">Темна тема</button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const themeToggleButton = document.getElementById("theme-toggle");

            // Проверка на сохраненную тему
            if (localStorage.getItem("theme") === "dark") {
                document.body.classList.add("dark-theme");
                themeToggleButton.textContent = "Світла тема"; // Текст кнопки
            } else {
                document.body.classList.remove("dark-theme");
                themeToggleButton.textContent = "Темна тема"; // Текст кнопки
            }

            // Обработчик клика для смены темы
            themeToggleButton.addEventListener("click", function () {
                document.body.classList.toggle("dark-theme");

                if (document.body.classList.contains("dark-theme")) {
                    localStorage.setItem("theme", "dark");
                    themeToggleButton.textContent = "Світла тема";
                } else {
                    localStorage.setItem("theme", "light");
                    themeToggleButton.textContent = "Темна тема";
                }
            });
        });
    </script>
</body>
</html>
