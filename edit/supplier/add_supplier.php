<?php
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем и фильтруем данные
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);

    // Проверяем, что все поля заполнены корректно
    if (empty($name) || empty($phone_number) || empty($product_id) || !is_numeric($product_id)) {
        $errorMessage = "Заповніть всі поля коректно!";
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone_number)) {
        // Проверка формата номера телефона
        $errorMessage = "Невірний формат номера телефону!";
    } else {
        // Проверка на существующего поставщика с таким номером телефона
        $check_query = $conn->prepare("SELECT supplier_id FROM supplier WHERE phone_number = ?");
        $check_query->bind_param("s", $phone_number);
        $check_query->execute();
        $check_query->store_result();

        if ($check_query->num_rows > 0) {
            $errorMessage = "Поставщик з таким номером телефону вже існує!";
        } else {
            // Получаем минимальный недостающий ID
            $result = $conn->query("SELECT MIN(t1.supplier_id + 1) AS next_id
                                    FROM supplier t1
                                    LEFT JOIN supplier t2 ON t1.supplier_id + 1 = t2.supplier_id
                                    WHERE t2.supplier_id IS NULL");
            $row = $result->fetch_assoc();
            $new_id = $row['next_id'] ?? 1;

            // Вставка нового поставщика с уникальным ID
            $insert_query = $conn->prepare("INSERT INTO supplier (supplier_id, name, phone_number, product_id) 
                                            VALUES (?, ?, ?, ?)");
            $insert_query->bind_param("issi", $new_id, $name, $phone_number, $product_id);

            // Используем транзакции для безопасности
            $conn->begin_transaction();
            try {
                if ($insert_query->execute()) {
                    $conn->commit();
                    header('Location: /Arh/supplier.php');
                    exit;
                } else {
                    $conn->rollback();
                    $errorMessage = "Помилка при додаванні поставщика: " . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                $errorMessage = "Помилка: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати нового поставщика</title>
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
        <h1>Додати нового поставщика</h1>

        <!-- Выводим ошибку, если она есть -->
        <?php if (isset($errorMessage)) { echo "<p>$errorMessage</p>"; } ?>

        <form action="add_supplier.php" method="POST">
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

            <button type="submit" class="button">Додати поставщика</button>
        </form>

        <br>
        <button class="button" onclick="window.location.href='/Arh/supplier.php'">Назад до поставщика</button>
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
