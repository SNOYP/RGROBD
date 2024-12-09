<?php
// Подключаем файл конфигурации
require_once __DIR__ . '/../../config.php';

// Проверка, если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $manufacturer = filter_input(INPUT_POST, 'manufacturer', FILTER_SANITIZE_STRING);
    $consultant_id = filter_input(INPUT_POST, 'consultant_id', FILTER_SANITIZE_NUMBER_INT);

    // Проверка, что все поля заполнены
    if ($name && $price && $manufacturer && $consultant_id) {
        // Проверяем, существует ли консультант
        $consultant_check_query = "SELECT COUNT(*) FROM consultant WHERE consultant_id = ?";
        $stmt = $conn->prepare($consultant_check_query);
        $stmt->bind_param("i", $consultant_id);
        $stmt->execute();
        $stmt->bind_result($consultant_exists);
        $stmt->fetch();
        $stmt->close();

        if ($consultant_exists == 0) {
            $errorMessage = "Консультант не знайдений!";
        } else {
            // Формируем запрос для добавления нового продукта
            $insert_query = "INSERT INTO product (name, price, manufacturer, consultant_id) 
                             VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sdsi", $name, $price, $manufacturer, $consultant_id);

            // Выполняем запрос на добавление
            if ($stmt->execute()) {
                header('Location: /Arh/product.php');  // Редирект на страницу списка продуктов
                exit;
            } else {
                // Выводим сообщение об ошибке
                $errorMessage = "Ошибка при добавлении продукта: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $errorMessage = "Заполните все поля!";
    }
}

// Получаем список консультантов для выпадающего списка
$consultants_query = "SELECT consultant_id, name FROM consultant";
$consultants_result = $conn->query($consultants_query);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати продукт</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Стили для светлой и темной темы */
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
        input[type="text"], input[type="number"], input[type="float"], select {
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
        <h1>Додати продукт</h1>

        <!-- Выводим ошибку, если она есть -->
        <?php if (isset($errorMessage)) { echo "<p>$errorMessage</p>"; } ?>

        <form action="add_product.php" method="POST">
            <div class="form-group">
                <label for="name">Назва продукту:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="price">Ціна:</label>
                <input type="number" step="0.01" id="price" name="price" required>
            </div>

            <div class="form-group">
                <label for="manufacturer">Виробник:</label>
                <input type="text" id="manufacturer" name="manufacturer" required>
            </div>

            <div class="form-group">
                <label for="consultant_id">Консультант:</label>
                <select id="consultant_id" name="consultant_id" required>
                    <?php while ($row = $consultants_result->fetch_assoc()) { ?>
                        <option value="<?= $row['consultant_id'] ?>"><?= $row['name'] ?></option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit" class="button">Додати продукт</button>
        </form>

        <br>
        <button class="button" onclick="window.location.href='/Arh/product.php'">Назад до продуктів</button>
    </div>

    <script>
        // Проверка сохраненной темы и установка при загрузке страницы
        if (localStorage.getItem("theme") === "dark") {
            document.body.classList.replace("light-mode", "dark-mode");
            document.getElementById("theme-toggle").textContent = "Світла тема";
        }

        // Переключение темы и сохранение выбора
        const themeToggle = document.getElementById("theme-toggle");
        themeToggle.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");
            document.body.classList.toggle("light-mode");
            const isDarkMode = document.body.classList.contains("dark-mode");
            themeToggle.textContent = isDarkMode ? "Світла тема" : "Темна тема";
            localStorage.setItem("theme", isDarkMode ? "dark" : "light");
        });
    </script>
</body>
</html>
