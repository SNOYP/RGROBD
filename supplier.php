<?php
require_once __DIR__ . '/config.php'; // Подключение конфигурации

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self';");

// Удаление поставщика
if (isset($_GET['delete_id'])) {
    $id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            // Подготовленное выражение для удаления
            $stmt = $conn->prepare("DELETE FROM supplier WHERE supplier_id = ?");
            $stmt->bind_param('i', $id);

            if ($stmt->execute()) {
                // Пересчет ID
                $conn->query("SET @count = 0");
                $conn->query("UPDATE supplier SET supplier_id = @count:=@count + 1 ORDER BY supplier_id");
                $conn->query("ALTER TABLE supplier AUTO_INCREMENT = 1");

                header('Location: supplier.php');
                exit;
            } else {
                $deleteMessage = "Ошибка при удалении: " . $conn->error;
            }
        } catch (Exception $e) {
            $deleteMessage = "Ошибка: " . $e->getMessage();
        }
    } else {
        $deleteMessage = "Невірний ID для видалення.";
    }
}

// Сортировка
$allowed_columns = ['supplier_id', 'name', 'phone_number', 'product_id', 'consultant'];
$allowed_directions = ['ASC', 'DESC'];

$sort_column = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'supplier_id';
$sort_direction = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?? 'ASC';

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'supplier_id';
}
if (!in_array(strtoupper($sort_direction), $allowed_directions)) {
    $sort_direction = 'ASC';
}

// Получение данных поставщиков
try {
    $stmt = $conn->prepare("SELECT * FROM supplier ORDER BY $sort_column $sort_direction");
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    die("Ошибка выполнения запроса: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Поставщики</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts/theme.js" defer></script>
</head>
<body>
    <button id="theme-toggle">Темна тема</button>
    <h1>Поставщики</h1>

    <?php if (isset($deleteMessage)) { echo "<p>" . htmlspecialchars($deleteMessage) . "</p>"; } ?>

    <table class="styled-table">
        <tr>
            <th><a href="?sort=supplier_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">ID</a></th>
            <th><a href="?sort=name&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Ім'я</a></th>
            <th><a href="?sort=phone_number&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Контактний номер</a></th>
            <th><a href="?sort=product_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Продукт</a></th>
            <th>Дії</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['supplier_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['phone_number']) ?></td>
                <td><?= htmlspecialchars($row['product_id']) ?></td>
                <td>
                    <a href="edit/supplier/edit_supplier.php?id=<?= htmlspecialchars($row['supplier_id']) ?>">Редагувати</a> |
                    <a href="?delete_id=<?= htmlspecialchars($row['supplier_id']) ?>" onclick="return confirm('Ви впевнені, що хочете видалити цього постачальника?')">Видалити</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <?php $conn->close(); ?>

    <br>
    <button class="button" onclick="window.location.href='index.php'">Повернутися на головну</button>
    <button class="button" onclick="window.location.href='/Arh/edit/supplier/add_supplier.php'">Додати нового постачальника</button>
</body>
</html>
