<?php
require_once __DIR__ . '/config.php'; // Подключаем конфигурацию базы данных

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self';");

// Функция для выполнения SQL-запросов с защитой от SQL-инъекций
function executePreparedQuery($conn, $query, $types = "", $params = [])
{
    $stmt = $conn->prepare($query);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

// Обработка удаления клиента
if (isset($_GET['delete_id'])) {
    $id = filter_input(INPUT_GET, 'delete_id', FILTER_SANITIZE_NUMBER_INT);
    if ($id) {
        // Удаляем клиента безопасным способом
        $deleteStmt = executePreparedQuery($conn, "DELETE FROM client WHERE client_id = ?", "i", [$id]);

        if ($deleteStmt->affected_rows > 0) {
            // Пересчитываем ID клиентов
            $conn->query("SET @count = 0");
            $conn->query("UPDATE client SET client_id = @count:=@count + 1 ORDER BY client_id");
            $conn->query("ALTER TABLE client AUTO_INCREMENT = 1");

            header('Location: client.php');
            exit;
        } else {
            $deleteMessage = "Помилка при видаленні: клієнта не знайдено.";
        }
    } else {
        $deleteMessage = "Невірний ID для видалення.";
    }
}

// Обработка сортировки данных
$sort_column = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'client_id';
$sort_direction = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?? 'ASC';
$sort_direction = strtoupper($sort_direction) === 'ASC' ? 'ASC' : 'DESC';

$allowed_columns = ['client_id', 'name', 'phone_number', 'product_id', 'consultant_id'];
$allowed_directions = ['ASC', 'DESC'];

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'client_id';
}
if (!in_array($sort_direction, $allowed_directions)) {
    $sort_direction = 'ASC';
}

// Запрос клиентов
$query = "SELECT client_id, name, phone_number, product_id, consultant_id FROM client ORDER BY $sort_column $sort_direction";
$result = $conn->query($query);

if (!$result) {
    die("Помилка виконання запиту: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Клієнти</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts/theme.js" defer></script>
</head>
<body>
    <button id="theme-toggle">Темна тема</button>
    <h1>Клієнти</h1>

    <?php if (isset($deleteMessage)): ?>
        <p><?= htmlspecialchars($deleteMessage) ?></p>
    <?php endif; ?>

    <table class="styled-table">
        <tr>
            <th><a href="?sort=client_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">ID</a></th>
            <th><a href="?sort=name&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Ім'я</a></th>
            <th><a href="?sort=phone_number&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Контактний номер</a></th>
            <th><a href="?sort=product_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Продукт</a></th>
            <th><a href="?sort=consultant_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Консультант</a></th>
            <th>Дії</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['client_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['phone_number']) ?></td>
                <td><?= htmlspecialchars($row['product_id']) ?></td>
                <td><?= htmlspecialchars($row['consultant_id']) ?></td>
                <td>
                    <a href="edit/client/edit_client.php?id=<?= htmlspecialchars($row['client_id']) ?>">Редагувати</a> |
                    <a href="?delete_id=<?= htmlspecialchars($row['client_id']) ?>" onclick="return confirm('Ви впевнені, що хочете видалити цього клієнта?')">Видалити</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>

    <br>
    <button class="button" onclick="window.location.href='index.php'">Повернутися на головну</button>
    <button class="button" onclick="window.location.href='/Arh/edit/client/add_client.php'">Додати нового клієнта</button>
</body>
</html>
