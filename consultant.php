<?php
require_once __DIR__ . '/config.php'; // Подключаем файл конфигурации базы данных

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self';");

// Функция для выполнения SQL-запросов с подготовкой
function executePreparedQuery($conn, $query, $types = "", $params = [])
{
    $stmt = $conn->prepare($query);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

// Обработка удаления консультанта
if (isset($_GET['delete_id'])) {
    $id = filter_input(INPUT_GET, 'delete_id', FILTER_SANITIZE_NUMBER_INT);
    if ($id) {
        // Удаляем запись консультанта безопасным способом
        $deleteStmt = executePreparedQuery($conn, "DELETE FROM consultant WHERE consultant_id = ?", "i", [$id]);

        if ($deleteStmt->affected_rows > 0) {
            // Пересчитываем ID оставшихся записей
            $conn->query("SET @count = 0");
            $conn->query("UPDATE consultant SET consultant_id = @count:=@count + 1 ORDER BY consultant_id");
            $conn->query("ALTER TABLE consultant AUTO_INCREMENT = 1");

            header('Location: consultant.php');
            exit;
        } else {
            $deleteMessage = "Помилка при видаленні: консультанта не знайдено.";
        }
    } else {
        $deleteMessage = "Невірний ID для видалення.";
    }
}

// Обработка сортировки данных
$sort_column = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'consultant_id';
$sort_direction = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?? 'ASC';
$sort_direction = strtoupper($sort_direction) === 'ASC' ? 'ASC' : 'DESC';

// Список разрешенных столбцов для сортировки
$allowed_columns = ['consultant_id', 'name', 'phone_number'];
$allowed_directions = ['ASC', 'DESC'];

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'consultant_id';
}
if (!in_array($sort_direction, $allowed_directions)) {
    $sort_direction = 'ASC';
}

// Запрос консультантов
$query = "SELECT consultant_id, name, phone_number FROM consultant ORDER BY $sort_column $sort_direction";
$result = $conn->query($query);

if (!$result) {
    die("Помилка виконання запиту: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Консультанти</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts/theme.js" defer></script>
</head>
<body>
    <button id="theme-toggle">Темна тема</button>
    <h1>Консультанти</h1>

    <?php if (isset($deleteMessage)): ?>
        <p><?= htmlspecialchars($deleteMessage) ?></p>
    <?php endif; ?>

    <table class="styled-table">
        <tr>
            <th><a href="?sort=consultant_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">ID</a></th>
            <th><a href="?sort=name&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Ім'я</a></th>
            <th><a href="?sort=phone_number&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Контактний номер</a></th>
            <th>Дії</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['consultant_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['phone_number']) ?></td>
                <td>
                    <a href="edit/consultant/edit_consultant.php?id=<?= htmlspecialchars($row['consultant_id']) ?>">Редагувати</a> |
                    <a href="?delete_id=<?= htmlspecialchars($row['consultant_id']) ?>" onclick="return confirm('Ви впевнені, що хочете видалити цього консультанта?')">Видалити</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>

    <br>
    <button class="button" onclick="window.location.href='index.php'">Повернутися на головну</button>
    <button class="button" onclick="window.location.href='edit/consultant/add_consultant.php'">Додати нового консультанта</button>
</body>
</html>
