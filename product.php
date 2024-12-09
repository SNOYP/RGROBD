<?php
require_once __DIR__ . '/config.php'; // Подключение конфигурации

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self';");

// Удаление продукта
if (isset($_GET['delete_id'])) {
    $id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            // Удаляем запись продукта
            $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
            $stmt->bind_param('i', $id);

            if ($stmt->execute()) {
                // Пересчет ID
                $conn->query("SET @count = 0");
                $conn->query("UPDATE product SET product_id = @count:=@count + 1 ORDER BY product_id");
                $conn->query("ALTER TABLE product AUTO_INCREMENT = 1");

                header('Location: product.php');
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
$allowed_columns = ['product_id', 'name', 'price', 'manufacturer', 'consultant_id'];
$allowed_directions = ['ASC', 'DESC'];

$sort_column = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'product_id';
$sort_direction = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?? 'ASC';

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'product_id';
}
if (!in_array(strtoupper($sort_direction), $allowed_directions)) {
    $sort_direction = 'ASC';
}

try {
    $stmt = $conn->prepare("SELECT * FROM product ORDER BY $sort_column $sort_direction");
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
    <title>Продукти</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts/theme.js" defer></script>
</head>
<body>
    <button id="theme-toggle">Темна тема</button>
    <h1>Продукти</h1>

    <?php if (isset($deleteMessage)) { echo "<p>" . htmlspecialchars($deleteMessage) . "</p>"; } ?>

    <table class="styled-table">
        <tr>
            <th><a href="?sort=product_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">ID</a></th>
            <th><a href="?sort=name&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Назва</a></th>
            <th><a href="?sort=price&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Ціна</a></th>
            <th><a href="?sort=manufacturer&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Виробник</a></th>
            <th><a href="?sort=consultant_id&dir=<?= $sort_direction === 'ASC' ? 'DESC' : 'ASC' ?>">Консультант</a></th>
            <th>Дії</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['product_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
                <td><?= htmlspecialchars($row['manufacturer']) ?></td>
                <td><?= htmlspecialchars($row['consultant_id']) ?></td>
                <td>
                    <a href="edit/product/edit_product.php?id=<?= htmlspecialchars($row['product_id']) ?>">Редагувати</a> |
                    <a href="?delete_id=<?= htmlspecialchars($row['product_id']) ?>" onclick="return confirm('Ви впевнені, що хочете видалити цей продукт?')">Видалити</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <?php $conn->close(); ?>

    <br>
    <button class="button" onclick="window.location.href='index.php'">Повернутися на головну</button>
    <button class="button" onclick="window.location.href='/Arh/edit/product/add_product.php'">Додати новий продукт</button>
</body>
</html>
