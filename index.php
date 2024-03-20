<?php
// Thiết lập kết nối với cơ sở dữ liệu
$pdo = new PDO("mysql:host=localhost;dbname=mydb;charset=utf8","root","");

// Xử lý khi người dùng thêm công việc mới hoặc sửa công việc
if(isset($_POST['submit']) ){
    $name = $_POST['name'];
    $category_id = $_POST['category'];
    $assignee = $_POST['assignee']; // Lấy tên người nhận công việc
    $task_id = $_POST['task_id']; // Lấy ID của công việc nếu có
    if(!empty($name) && !empty($category_id)) {
        if (!empty($task_id)) {
            // Nếu có ID công việc, thực hiện cập nhật
            $sth = $pdo->prepare("UPDATE todos SET name = :name, category_id = :category_id, assignee = :assignee WHERE id = :task_id");
            $sth->bindValue(':task_id', $task_id, PDO::PARAM_INT);
        } else {
            // Nếu không có ID công việc, thực hiện thêm mới
            $sth = $pdo->prepare("INSERT INTO todos (name, category_id, assignee) VALUES (:name, :category_id, :assignee)");
        }
        $sth->bindValue(':name', $name, PDO::PARAM_STR);
        $sth->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $sth->bindValue(':assignee', $assignee, PDO::PARAM_STR); // Bind giá trị của tên người nhận công việc
        $sth->execute();
        // Thông báo cho người dùng biết công việc đã được thêm hoặc cập nhật thành công
        if (!empty($task_id)) {
            echo '<div class="alert alert-success" role="alert">Công việc đã được cập nhật thành công!</div>';
        } else {
            echo '<div class="alert alert-success" role="alert">Công việc đã được thêm thành công!</div>';
        }
    } else {
        // Thông báo cho người dùng biết nếu có lỗi xảy ra
        echo '<div class="alert alert-danger" role="alert">Vui lòng điền đầy đủ thông tin cho công việc!</div>';
    }
}

// Xử lý khi người dùng xóa công việc
elseif(isset($_POST['delete'])){
    $id = $_POST['id'];
    $sth = $pdo->prepare("DELETE FROM todos WHERE id = :id");
    $sth->bindValue(':id', $id, PDO::PARAM_INT);
    $sth->execute();
    // Thông báo cho người dùng biết công việc đã được xóa thành công
    echo '<div class="alert alert-success" role="alert">Công việc đã được xóa thành công!</div>';
}

// Xử lý khi người dùng đánh dấu công việc đã hoàn thành
elseif(isset($_POST['complete'])){
    $id = $_POST['id'];
    $sth = $pdo->prepare("UPDATE todos SET completed = 1 WHERE id = :id");
    $sth->bindValue(':id', $id, PDO::PARAM_INT);
    $sth->execute();
    // Thông báo cho người dùng biết công việc đã được đánh dấu hoàn thành
    echo '<div class="alert alert-success" role="alert">Công việc đã được đánh dấu hoàn thành!</div>';
}

// Xử lý khi người dùng muốn sửa công việc
elseif(isset($_POST['edit'])){
    $id = $_POST['id'];
    $sth = $pdo->prepare("SELECT * FROM todos WHERE id = :id");
    $sth->bindValue(':id', $id, PDO::PARAM_INT);
    $sth->execute();
    $task = $sth->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE HTML>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Công Việc</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .container {
            max-width: 800px;
        }
        .todo-item {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            padding: 15px;
        }
        .delete-btn, .complete-btn, .edit-btn {
            margin-left: 5px;
        }
        .category-dropdown {
            width: 200px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center mb-4">Danh Sách Công Việc</h1>
        <!-- Form thêm hoặc sửa công việc mới -->
        <form method="post" action="">
            <?php if(isset($task)): ?>
                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Tên công việc" required <?php if(isset($task)) echo 'value="' . $task['name'] . '"'; ?>>
            </div>
            <div class="form-group">
                <select name="category" class="form-control category-dropdown" required>
                    <option value="">Chọn danh mục</option>
                    <?php
                        // Truy vấn danh mục công việc
                        $sth = $pdo->prepare("SELECT * FROM categories");
                        $sth->execute();
                        foreach($sth as $row) {
                            $selected = '';
                            if(isset($task) && $task['category_id'] == $row['id']) {
                                $selected = 'selected';
                            }
                            echo "<option value='".$row['id']."' $selected>".$row['name']."</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="assignee" class="form-control" placeholder="Người nhận công việc" required <?php if(isset($task)) echo 'value="' . $task['assignee'] . '"'; ?>>
            </div>
            <button type="submit" name="submit" class="btn btn-primary"><?php if(isset($task)) echo 'Cập Nhật'; else echo 'Thêm'; ?></button>
        </form>
        <!-- Danh sách công việc hiện tại -->
        <div class="todo-list">
            <?php
                // Truy vấn công việc từ cơ sở dữ liệu và hiển thị
                $sth = $pdo->prepare("SELECT todos.*, categories.name AS category_name FROM todos INNER JOIN categories ON todos.category_id = categories.id ORDER BY todos.id DESC");
                $sth->execute();
                
                foreach($sth as $row) {
                    $completed_class = ($row['completed']) ? 'text-success' : '';
            ?>
                    <div class="todo-item">
                        <span class="<?= $completed_class ?>"><?= htmlspecialchars($row['name']) ?></span>
                        <?php if(!$row['completed']): ?>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="complete" class="btn btn-success complete-btn">Hoàn Thành</button>
                        </form>
                        <?php else: ?>
                        <span class="text-success ml-3">Đã hoàn thành</span>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete" class="btn btn-danger delete-btn">Xóa</button>
                        </form>
                            <!-- Thêm nút Xem Chi Tiết -->
    <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-primary">Xem Chi Tiết</a>
    <br>
                        <!-- Thêm nút sửa công việc -->
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="edit" class="btn btn-warning edit-btn">Sửa</button>
                        </form>
                        <br>
                        <small class="text-muted">Danh mục: <?= htmlspecialchars($row['category_name']) ?></small><br>
                        <small class="text-muted">Người nhận: <?= htmlspecialchars($row['assignee']) ?></small>
                    </div>


</div>

            <?php
                }
            ?>
        </div>
    </div>
     <script>
        function completeTask(taskId) {
            fetch('complete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: taskId}),
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('todoList').innerHTML = data;
            });
        }

        function deleteTask(taskId) {
            fetch('delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: taskId}),
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('todoList').innerHTML = data;
            });
        }

        function editTask(taskId) {
            fetch('edit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: taskId}),
            })
            .then(response => response.text())
            .then(data => {
                let task = JSON.parse(data);
                document.getElementById('task_id').value = task.id;
                document.getElementById('name').value = task.name;
                document.getElementById('category').value = task.category_id;
                document.getElementById('assignee').value = task.assignee;
                document.getElementById('submitBtn').innerText = 'Cập Nhật';
            });
        }
    </script>
</body>
</html>
