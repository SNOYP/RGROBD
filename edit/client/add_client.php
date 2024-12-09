<?php
require_once __DIR__ . '/../../config.php';

$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $consultant_id = filter_input(INPUT_POST, 'consultant_id', FILTER_SANITIZE_NUMBER_INT);

    if ($name && $phone_number && $product_id && $consultant_id) {
        // Проверка на существование клиента с таким номером телефона
        $check_query = $conn->prepare("SELECT client_id FROM client WHERE phone_number = ?");
        $check_query->bind_param("s", $phone_number);
        $check_query->execute();
        $check_query->store_result();

        if ($check_query->num_rows > 0) {
            $errorMessage = "Клієнт з таким номером телефону вже існує!";
        } else {
            // Находим минимальный недостающий ID
            $result = $conn->query("
                SELECT MIN(t1.client_id + 1) AS next_id
                FROM client t1
                LEFT JOIN client t2 ON t1.client_id + 1 = t2.client_id
                WHERE t2.client_id IS NULL
            ");
            $row = $result->fetch_assoc();
            $new_id = $row['next_id'] ?? 1;

            // Вставляем нового клиента
            $insert_query = $conn->prepare("
                INSERT INTO client (client_id, name, phone_number, product_id, consultant_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert_query->bind_param("issii", $new_id, $name, $phone_number, $product_id, $consultant_id);

            try {
                if ($insert_query->execute()) {
                    header('Location: /Arh/client.php');
                    exit;
                } else {
                    $errorMessage = "Помилка при додаванні клієнта: " . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                $errorMessage = "Помилка: " . $e->getMessage();
            }
        }
    } else {
        $errorMessage = "Заповніть всі поля!";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати нового клієнта</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body.light-mode { background-color: #ffffff; color: #333333; }
        body.dark-mode { background-color: #333333; color: #ffffff; }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: inherit;
        }
        h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;
        }
        .button {
            display: inline-block; padding: 10px 20px;
            background-color: #4CAF50; color: #fff; border: none; border-radius: 4px; cursor: pointer;
        }
        .button:hover { background-color: #45a049; }
        #theme-toggle {
            position: fixed; top: 20px; right: 20px;
            padding: 10px; cursor: pointer; background-color: #ff9800; color: #fff; border-radius: 5px;
        }
        #theme-toggle:hover { background-color: #e68900; }
    </style>
</head>
<body class="light-mode">
    <div id="theme-toggle">Темна тема</div>
    <div class="container">
        <h1>Додати нового клієнта</h1>

        <!-- Выводим ошибку, если она есть -->
        <?php if ($errorMessage) { echo "<p>$errorMessage</p>"; } ?>

        <form action="add_client.php" method="POST">
            <div class="form-group">
                <label for="name">Ім'я:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="phone_number">Контактний номер:</label>
                <input type="text" id="phone_number" name="phone_number" required>
            </div>

            <div class="form-group">
                <label for="product_id">Продукт:</label>
                <input type="number" id="product_id" name="product_id" required>
            </div>

            <div class="form-group">
                <label for="consultant_id">Консультант:</label>
                <input type="number" id="consultant_id" name="consultant_id" required>
            </div>

            <button type="submit" class="button">Додати клієнта</button>
        </form>

        <br>
        <button class="button" onclick="window.location.href='/Arh/client.php'">Назад до клієнтів</button>
    </div>

    <script>
        // Проверка текущей темы на основе локального хранилища
        if (localStorage.getItem("theme") === "dark") {
            document.body.classList.replace("light-mode", "dark-mode");
            document.getElementById("theme-toggle").textContent = "Світла тема";
        }

        const themeToggle = document.getElementById("theme-toggle");
        themeToggle.addEventListener("click", () => {
            // Переключение темы между светлой и тёмной
            document.body.classList.toggle("dark-mode");
            document.body.classList.toggle("light-mode");
            const isDarkMode = document.body.classList.contains("dark-mode");
            themeToggle.textContent = isDarkMode ? "Світла тема" : "Темна тема";
            localStorage.setItem("theme", isDarkMode ? "dark" : "light");
        });
    </script>
</body>
</html>
