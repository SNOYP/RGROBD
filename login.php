<?php
session_start();
require_once __DIR__ . '/config.php'; // Подключаем файл конфигурации с подключением к базе данных

// Если пользователь уже вошел, перенаправляем на главную страницу
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы и выполняем их санитарную очистку
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Проверяем, что оба поля заполнены
    if ($email && $password) {
        // Подготавливаем SQL запрос для поиска пользователя
        $query = "SELECT id, first_name, last_name, email, password FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $email); // связываем параметр с запросом
            $stmt->execute();
            $result = $stmt->get_result();

            // Если пользователь найден
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Проверяем, совпадает ли введённый пароль с хешом из базы данных
                if (password_verify($password, $user['password'])) {
                    // Устанавливаем сессии для пользователя
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];

                    // Редирект на главную страницу или страницу панели пользователя
                    header('Location: index.php');
                    exit;
                } else {
                    $errorMessage = 'Неверный пароль!';
                }
            } else {
                $errorMessage = 'Пользователь не найден!';
            }
        } else {
            $errorMessage = 'Ошибка подключения к базе данных. Попробуйте позже.';
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
    <title>Вхід</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Общие стили */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #ff9800;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .theme-toggle:hover {
            background-color: #e68900;
        }

        .register-link {
            display: block;
            margin-top: 15px;
            font-size: 14px;
            color: #4CAF50;
            text-decoration: none;
        }

        .register-link:hover {
            text-decoration: underline;
        }

        /* Темная тема */
        body.dark-theme {
            background-color: #333;
            color: #fff;
        }

        body.dark-theme .login-container {
            background-color: #444;
            box-shadow: 0 4px 8px rgba(255, 255, 255, 0.2);
        }

        body.dark-theme h2 {
            color: #fff;
        }

        body.dark-theme button {
            background-color: #555;
        }

        body.dark-theme button:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
    <div class="theme-toggle" id="theme-toggle">Темна тема</div>

    <div class="login-container">
        <h2>Вхід в систему</h2>
        
        <?php if (isset($errorMessage)): ?>
            <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <form id="login-form" method="POST">
            <div class="form-group">
                <label for="email">Електронна пошта:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Увійти</button>
        </form>

        <a href="register.php" class="register-link">Не маєте облікового запису? Зареєструйтесь тут.</a>
    </div>

    <script>
        // Проверка сохраненной темы и установка при загрузке страницы
        if (localStorage.getItem("theme") === "dark") {
            document.body.classList.add("dark-theme");
            document.getElementById("theme-toggle").textContent = "Світла тема";
        }

        // Переключение темы и сохранение выбора
        const themeToggle = document.getElementById("theme-toggle");
        themeToggle.addEventListener("click", () => {
            document.body.classList.toggle("dark-theme");

            if (document.body.classList.contains("dark-theme")) {
                localStorage.setItem("theme", "dark");
                themeToggle.textContent = "Світла тема";
            } else {
                localStorage.setItem("theme", "light");
                themeToggle.textContent = "Темна тема";
            }
        });
    </script>
</body>
</html>
