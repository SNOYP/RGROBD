<?php
require_once __DIR__ . '/../../config.php';

// Получаем ID заказа из GET запроса
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    header('Location: /Arh/order.php');  // Перенаправляем на страницу заказов, если ID не указан
    exit;
}

// Запрос для получения текущих данных заказа
$query = "SELECT * FROM `order` WHERE order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: /Arh/order.php');  // Перенаправляем, если заказ с таким ID не найден
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Проверка, если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_NUMBER_INT);
    $suppliert_id = filter_input(INPUT_POST, 'suppliert_id', FILTER_SANITIZE_NUMBER_INT);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $consultant_id = filter_input(INPUT_POST, 'consultant_id', FILTER_SANITIZE_NUMBER_INT);

    // Проверка, что все поля заполнены
    if ($client_id && $suppliert_id && $address && $product_id && $consultant_id) {
        // Проверка на существование клиента, поставщика, продукта и консультанта
        $check_exists_query = "SELECT COUNT(*) FROM client WHERE client_id = ?";
        $stmt = $conn->prepare($check_exists_query);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $stmt->bind_result($client_exists);
        $stmt->fetch();
        $stmt->close();

        if ($client_exists == 0) {
            $errorMessage = "Клієнт не знайдений!";
        } else {
            $check_exists_query = "SELECT COUNT(*) FROM suppliert WHERE suppliert_id = ?";
            $stmt = $conn->prepare($check_exists_query);
            $stmt->bind_param("i", $suppliert_id);
            $stmt->execute();
            $stmt->bind_result($suppliert_exists);
            $stmt->fetch();
            $stmt->close();

            if ($suppliert_exists == 0) {
                $errorMessage = "Доставщик не знайдений!";
            } else {
                $check_exists_query = "SELECT COUNT(*) FROM product WHERE product_id = ?";
                $stmt = $conn->prepare($check_exists_query);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $stmt->bind_result($product_exists);
                $stmt->fetch();
                $stmt->close();

                if ($product_exists == 0) {
                    $errorMessage = "Продукт не знайдений!";
                } else {
                    $check_exists_query = "SELECT COUNT(*) FROM consultant WHERE consultant_id = ?";
                    $stmt = $conn->prepare($check_exists_query);
                    $stmt->bind_param("i", $consultant_id);
                    $stmt->execute();
                    $stmt->bind_result($consultant_exists);
                    $stmt->fetch();
                    $stmt->close();

                    if ($consultant_exists == 0) {
                        $errorMessage = "Консультант не знайдений!";
                    } else {
                        // Формируем запрос на обновление данных заказа
                        $update_query = "UPDATE `order` 
                                         SET client_id=?, suppliert_id=?, address=?, product_id=?, consultant_id=? 
                                         WHERE order_id=?";
                        $stmt = $conn->prepare($update_query);
                        $stmt->bind_param("iissii", $client_id, $suppliert_id, $address, $product_id, $consultant_id, $id);

                        if ($stmt->execute()) {
                            header('Location: /Arh/order.php');  // Редирект на страницу заказов
                            exit;
                        } else {
                            $errorMessage = "Ошибка при обновлении данных заказа: " . $stmt->error;
                        }
                        $stmt->close();
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
    <title>Редагувати замовлення</title>
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
        <h1>Редагувати замовлення</h1>

        <!-- Выводим ошибку, если она есть -->
        <?php if (isset($errorMessage)) { echo "<p>$errorMessage</p>"; } ?>

        <form action="edit_order.php?id=<?= $order['order_id'] ?>" method="POST">
            <div class="form-group">
                <label for="client_id">Клієнт ID:</label>
                <input type="number" id="client_id" name="client_id" value="<?= htmlspecialchars($order['client_id']) ?>" required>
            </div>

            <div class="form-group">
                <label for="suppliert_id">Доставщик:</label>
                <input type="number" id="suppliert_id" name="suppliert_id" value="<?= htmlspecialchars($order['suppliert_id']) ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Контактна адресса:</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($order['address']) ?>" required>
            </div>

            <div class="form-group">
                <label for="product_id">Продукт:</label>
                <input type="number" id="product_id" name="product_id" value="<?= htmlspecialchars($order['product_id']) ?>" required>
            </div>

            <div class="form-group">
                <label for="consultant_id">Консультант:</label>
                <input type="number" id="consultant_id" name="consultant_id" value="<?= htmlspecialchars($order['consultant_id']) ?>" required>
            </div>

            <button type="submit" class="button">Зберегти зміни</button>
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
