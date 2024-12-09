<?php
// Подключаем файл конфигурации
require_once __DIR__ . '/../../config.php';

// Проверка, если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы и санитизируем их
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_NUMBER_INT);
    $suppliert_id = filter_input(INPUT_POST, 'suppliert_id', FILTER_SANITIZE_NUMBER_INT);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $consultant_id = filter_input(INPUT_POST, 'consultant_id', FILTER_SANITIZE_NUMBER_INT);

    // Проверка, что все поля заполнены
    if ($client_id && $suppliert_id && $address && $product_id && $consultant_id) {
        // Подготовка SQL-запроса для проверки существования данных
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM client WHERE client_id = ?");
        $stmt_check->bind_param("i", $client_id);
        $stmt_check->execute();
        $stmt_check->bind_result($client_exists);
        $stmt_check->fetch();
        $stmt_check->close();

        // Если клиент не найден, выводим ошибку
        if ($client_exists == 0) {
            $errorMessage = "Клієнт не знайдений!";
        } else {
            // Проверка существования поставщика
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM suppliert WHERE suppliert_id = ?");
            $stmt_check->bind_param("i", $suppliert_id);
            $stmt_check->execute();
            $stmt_check->bind_result($suppliert_exists);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($suppliert_exists == 0) {
                $errorMessage = "Доставщик не знайдений!";
            } else {
                // Проверка существования продукта
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM product WHERE product_id = ?");
                $stmt_check->bind_param("i", $product_id);
                $stmt_check->execute();
                $stmt_check->bind_result($product_exists);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($product_exists == 0) {
                    $errorMessage = "Продукт не знайдений!";
                } else {
                    // Проверка существования консультанта
                    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM consultant WHERE consultant_id = ?");
                    $stmt_check->bind_param("i", $consultant_id);
                    $stmt_check->execute();
                    $stmt_check->bind_result($consultant_exists);
                    $stmt_check->fetch();
                    $stmt_check->close();

                    if ($consultant_exists == 0) {
                        $errorMessage = "Консультант не знайдений!";
                    } else {
                        // Формируем запрос для добавления нового заказа
                        $insert_query = "INSERT INTO `order` (client_id, suppliert_id, address, product_id, consultant_id) 
                                         VALUES (?, ?, ?, ?, ?)";

                        // Подготовка запроса на добавление
                        if ($stmt = $conn->prepare($insert_query)) {
                            $stmt->bind_param("iissi", $client_id, $suppliert_id, $address, $product_id, $consultant_id);
                            if ($stmt->execute()) {
                                header('Location: /Arh/order.php');  // Редирект на страницу списка заказов
                                exit;
                            } else {
                                $errorMessage = "Ошибка при добавлении заказа: " . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            $errorMessage = "Ошибка подготовки запроса: " . $conn->error;
                        }
                    }
                }
            }
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
    <title>Додати замовлення</title>
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
        <h1>Додати замовлення</h1>

        <!-- Выводим ошибку, если она есть -->
        <?php if (isset($errorMessage)) { echo "<p>$errorMessage</p>"; } ?>

        <form action="add_order.php" method="POST">
            <div class="form-group">
                <label for="client_id">Клієнт ID:</label>
                <input type="number" id="client_id" name="client_id" required>
            </div>

            <div class="form-group">
                <label for="suppliert_id">Доставщик ID:</label>
                <input type="number" id="suppliert_id" name="suppliert_id" required>
            </div>

            <div class="form-group">
                <label for="address">Контактна адресса:</label>
                <input type="text" id="address" name="address" required>
            </div>

            <div class="form-group">
                <label for="product_id">Продукт ID:</label>
                <input type="number" id="product_id" name="product_id" required>
            </div>

            <div class="form-group">
                <label for="consultant_id">Консультант ID:</label>
                <input type="number" id="consultant_id" name="consultant_id" required>
            </div>

            <button type="submit" class="button">Додати замовлення</button>
        </form>

        <br>
        <button class="button" onclick="window.location.href='/Arh/order.php'">Назад до замовлень</button>
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
