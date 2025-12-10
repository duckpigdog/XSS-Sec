<?php
require_once __DIR__ . '/auth_guard.php';
$users = $_SESSION['users'];
$error = null;
$info = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? (string)$_POST['action'] : '';
    if ($action === 'add') {
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $role = trim((string)($_POST['role'] ?? 'viewer'));
        if ($username === '' || $email === '') {
            $error = '请输入用户名与邮箱';
        } else {
            $id = 1;
            foreach ($users as $u) { if ($u['id'] >= $id) { $id = $u['id'] + 1; } }
            $users[] = ['id' => $id, 'username' => $username, 'email' => $email, 'role' => $role];
            $_SESSION['users'] = $users;
            $info = '已添加';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $filtered = [];
        foreach ($users as $u) { if ((int)$u['id'] !== $id) { $filtered[] = $u; } }
        $_SESSION['users'] = $filtered;
        $users = $filtered;
        $info = '已删除';
    }
}
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>用户 - Standalone Admin</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">Standalone Admin</div>
    <nav>
        <a href="dashboard.php">仪表盘</a>
        <a href="users.php" class="active">用户</a>
        <a href="logout.php">退出</a>
    </nav>
</header>
<main class="container">
    <h1>用户</h1>
    <?php if ($error) { echo '<div class="alert">' . htmlspecialchars($error) . '</div>'; } ?>
    <?php if ($info) { echo '<div class="notice">' . htmlspecialchars($info) . '</div>'; } ?>
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>邮箱</th>
                    <th>角色</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo (int)$u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['role']); ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                            <button type="submit" class="btn danger">删除</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h2>添加用户</h2>
        <form method="post" class="grid-2">
            <input type="hidden" name="action" value="add">
            <div>
                <label>用户名</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>邮箱</label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label>角色</label>
                <select name="role">
                    <option value="admin">admin</option>
                    <option value="editor">editor</option>
                    <option value="viewer" selected>viewer</option>
                </select>
            </div>
            <div class="align-end">
                <button type="submit" class="btn">添加</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>
