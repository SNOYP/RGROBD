<?php
require_once __DIR__ . '/../../config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    header('Location: /Arh/supplier.php');
    exit;
}

// Подготовленный запрос для получения текущих данных поставщика
$query = "SELECT * FROM supplier WHERE supplier_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: /Arh/supplier.php');
    exit;
}

$supplier = $result->fetch_assoc();

// Проверка, если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем и фильтруем данные из формы
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);  // Используем FILTER_SANITIZE_STRING для телефона
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);

    // Проверка, что все поля заполнены и корректны
    if (empty($name) || empty($phone_number) || empty($product_id)) {
        $errorMessage = "Заповніть всі поля!";
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone_number)) {
        $errorMessage = "Невірний формат номера телефону!";
    } else {
        // Формируем запрос на обновление данных поставщика
        $update_query = "UPDATE supplier SET name = ?, phone_number = ?, product_id = ? WHERE supplier_id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("ssii", $name, $phone_number, $product_id, $id);

        // Выполняем запрос на обновление
        if ($stmt_update->execute()) {
            header('Location: /Arh/supplier.php');
            exit;
        } else {
            $errorMessage = "Ошибка при обновлении данных поставщика: " . $stmt_update->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагувати постачальника</title>
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
        <h1>Редагувати постачальника</h1>

        <!-- Выводим ошибку, если она есть -->
        <?php if (isset($errorMessage)) { echo "<p>$errorMessage</p>"; } ?>

        <form action="edit_supplier.php?id=<?= $supplier['supplier_id'] ?>" method="POST">
            <div class="form-group">
                <label for="name">Ім'я:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($supplier['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="phone_number">Контактний номер:</label>
                <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($supplier['phone_number']) ?>" required>
            </div>

            <div class="form-group">
                <label for="product_id">Продукт:</label>
                <input type="number" id="product_id" name="product_id" value="<?= htmlspecialchars($supplier['product_id']) ?>" required>
            </div>

            <button type="submit" class="button">Зберегти зміни</button>
        </form>

        <br>
        <button class="button" onclick="window.location.href='/Arh/supplier.php'">Назад до постачальників</button>
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
