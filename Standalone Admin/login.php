<?php
require_once __DIR__ . '/bootstrap.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
    $p = isset($_POST['password']) ? (string)$_POST['password'] : '';
    if (isset($FIXED_CREDENTIALS[$u]) && $FIXED_CREDENTIALS[$u] === $p) {
        $_SESSION['auth'] = true;
        $_SESSION['principal'] = $u;
        header('Location: dashboard.php');
        exit;
    }
    $error = '用户名或密码错误';
}
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登录 - Standalone Admin</title>
    <link rel="stylesheet" href="assets/style.css">
<link rel="preconnect" href="" />
</head>
<body>
<header class="topbar">
    <div class="brand">Standalone Admin</div>
</header>
<div class="container narrow">
    <h1>登录</h1>
    <?php if ($error) { echo '<div class="alert">' . htmlspecialchars($error) . '</div>'; } ?>
    <form method="post" class="card">
        <label>用户名</label>
        <input type="text" name="username" required>
        <label>密码</label>
        <input type="password" name="password" required>
        <button type="submit">登录</button>
    </form>
    <div class="muted">使用固定凭据登录，例如 admin / Admin@123</div>
    <div class="muted">或 analyst / Analyst@123</div>
    <div class="footer">Standalone Admin</div>
    <script>
    document.querySelector('input[name="username"]').focus();
    </script>
</div>
</body>
</html>
