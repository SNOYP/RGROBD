<?php
// Подключаем файл конфигурации
require_once __DIR__ . '../config.php';

// Устанавливаем Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self';");

// Функция для обработки ошибок
function handleError($message, $conn = null) {
    if ($conn) {
        error_log("Ошибка: $message. SQL Error: " . $conn->error);
    } else {
        error_log("Ошибка: $message");
    }
    echo "<p class='error'>Виникла помилка: $message</p>";
}

// Удаление заказа
if (isset($_GET['delete_id'])) {
    $id = filter_input(INPUT_GET, 'delete_id', FILTER_SANITIZE_NUMBER_INT);
    if ($id) {
        $stmt = $conn->prepare("DELETE FROM `order` WHERE order_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // Перенумеровка заказов
            $conn->query("SET @count = 0");
            $conn->query("UPDATE `order` SET order_id = @count:=@count + 1 ORDER BY order_id");
            $conn->query("ALTER TABLE `order` AUTO_INCREMENT = 1");
            header('Location: order.php');
            exit;
        } else {
            handleError("Не вдалося видалити замовлення", $conn);
        }
        $stmt->close();
    } else {
        handleError("Невірний ID для видалення");
    }
}

// Добавление нового заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
    $suppliert_id = filter_input(INPUT_POST, 'suppliert_id', FILTER_VALIDATE_INT);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $consultant_id = filter_input(INPUT_POST, 'consultant_id', FILTER_VALIDATE_INT);

    if ($client_id && $suppliert_id && $address && $product_id && $consultant_id) {
        $stmt = $conn->prepare("INSERT INTO `order` (client_id, suppliert_id, address, product_id, consultant_id) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $client_id, $suppliert_id, $address, $product_id, $consultant_id);
        if ($stmt->execute()) {
            header('Location: order.php');
            exit;
        } else {
            handleError("Не вдалося додати замовлення", $conn);
        }
        $stmt->close();
    } else {
        handleError("Будь ласка, заповніть усі поля коректно.");
    }
}

// Сортировка данных
$sort_column = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'order_id';
$sort_direction = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?? 'ASC';
$sort_column = in_array($sort_column, ['order_id', 'client_id', 'suppliert_id', 'address', 'product_id', 'consultant_id']) ? $sort_column : 'order_id';
$sort_direction = strtoupper($sort_direction) === 'DESC' ? 'DESC' : 'ASC';

// Получение данных из базы
$stmt = $conn->prepare("SELECT * FROM `order` ORDER BY $sort_column $sort_direction");
if (!$stmt->execute()) {
    handleError("Не вдалося отримати дані замовлень", $conn);
}
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Замовлення</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts/theme.js" defer></script>
</head>
<body>
    <button id="theme-toggle">Темна тема</button>
    <h1>Замовлення</h1>

    <table class="styled-table">
        <tr>
            <th><a href="?sort=order_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Замовлення ID</a></th>
            <th><a href="?sort=client_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Клієнт ID</a></th>
            <th><a href="?sort=suppliert_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Доставщик</a></th>
            <th><a href="?sort=address&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Контактна адреса</a></th>
            <th><a href="?sort=product_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Продукт</a></th>
            <th><a href="?sort=consultant_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Консультант</a></th>
            <th>Дії</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['order_id']) ?></td>
                <td><?= htmlspecialchars($row['client_id']) ?></td>
                <td><?= htmlspecialchars($row['suppliert_id']) ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= htmlspecialchars($row['product_id']) ?></td>
                <td><?= htmlspecialchars($row['consultant_id']) ?></td>
                <td>
                    <a href="edit/order/edit_order.php?id=<?= htmlspecialchars($row['order_id']) ?>">Редагувати</a> |
                    <a href="?delete_id=<?= htmlspecialchars($row['order_id']) ?>" onclick="return confirm('Ви впевнені, що хочете видалити це замовлення?')">Видалити</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <button class="button" onclick="window.location.href='index.php'">Повернутися на головну</button>
    <button class="button" onclick="window.location.href='edit/order/add_order.php'">Додати нове замовлення</button>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
