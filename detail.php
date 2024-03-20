<?php
// Kết nối CSDL
$pdo = new PDO("mysql:host=localhost;dbname=mydb;charset=utf8","root","");

// Lấy ID công việc từ tham số truyền vào
$id = $_GET['id'];

// Truy vấn thông tin chi tiết của công việc
$sth = $pdo->prepare("SELECT todos.*, categories.name AS category_name FROM todos INNER JOIN categories ON todos.category_id = categories.id WHERE todos.id = :id");
$sth->bindValue(':id', $id, PDO::PARAM_INT);
$sth->execute();
$task = $sth->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE HTML>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Công Việc</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Chi Tiết Công Việc</h1>
        <div class="todo-item">
                <h3><?= htmlspecialchars($task['name']) ?></h3>
            <p><strong>Danh mục:</strong> <?= htmlspecialchars($task['category_name']) ?></p>
            <p><strong>Người nhận:</strong> <?= htmlspecialchars($task['assignee']) ?></p>
            <p><strong>Trạng thái:</strong> <?= $task['completed'] ? 'Hoàn thành' : 'Chưa hoàn thành' ?></p>
        </div>
        <a href="index.php" class="btn btn-primary">Quay lại</a>
    </div>
</body>
</html>
