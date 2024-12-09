<?php
require_once __DIR__ . '/../../config.php';

// Получаем ID продукта из GET запроса
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    header('Location: /Arh/product.php');
    exit;
}

// Запрос для получения текущих данных продукта
$query = "SELECT * FROM product WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: /Arh/product.php');
    exit;
}

$product = $result->fetch_assoc();

// Проверка, если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы с дополнительной проверкой
    $product_name = filter_input(INPUT_POST, 'product_name', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $manufacturer = filter_input(INPUT_POST, 'manufacturer', FILTER_SANITIZE_STRING);
    $consultant_id = filter_input(INPUT_POST, 'consultant_id', FILTER_SANITIZE_NUMBER_INT);

    // Проверка, что все поля заполнены
    if ($product_name && $price && $manufacturer && $consultant_id) {
        // Формируем запрос на обновление данных продукта с подготовленным запросом
        $update_query = "UPDATE product SET name=?, price=?, manufacturer=?, consultant_id=? WHERE product_id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssi", $product_name, $price, $manufacturer, $consultant_id, $id);

        // Выполняем запрос на обновление
        if ($stmt->execute()) {
            header('Location: /Arh/product.php');  // Редирект на страницу продуктов
            exit;
        } else {
            // Выводим сообщение об ошибке
            $errorMessage = "Ошибка при обновлении данных продукта: " . $conn->error;
        }
    } else {
        $errorMessage = "Заполните все поля!";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагувати продукт</title>
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
        input[type="text"], input[type="number"], input[type="float"] {
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
        <h1>Редагувати продукт</h1>

        <!-- Выводим ошибку, если она есть -->
        <?php if (isset($errorMessage)) { echo "<p>$errorMessage</p>"; } ?>

        <form action="edit_product.php?id=<?= $product['product_id'] ?>" method="POST">
            <div class="form-group">
                <label for="product_name">Назва продукту:</label>
                <input type="text" id="product_name" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Ціна:</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>

            <div class="form-group">
                <label for="manufacturer">Виробник:</label>
                <input type="text" id="manufacturer" name="manufacturer" value="<?= htmlspecialchars($product['manufacturer']) ?>" required>
            </div>

            <div class="form-group">
                <label for="consultant_id">Консультант:</label>
                <input type="number" id="consultant_id" name="consultant_id" value="<?= htmlspecialchars($product['consultant_id']) ?>" required>
            </div>

            <button type="submit" class="button">Зберегти зміни</button>
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
