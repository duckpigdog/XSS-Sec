<?php
require_once __DIR__ . '/auth_guard.php';
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>仪表盘 - Standalone Admin</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">Standalone Admin</div>
    <nav>
        <a href="dashboard.php">仪表盘</a>
        <a href="users.php">用户</a>
        <a href="logout.php">退出</a>
    </nav>
</header>
<main class="container">
    <h1>欢迎</h1>
    <div class="grid">
        <div class="card stat">
            <div class="stat-label">当前用户</div>
            <div class="stat-value"><?php echo htmlspecialchars($_SESSION['principal'] ?? ''); ?></div>
        </div>
        <div class="card stat">
            <div class="stat-label">用户数量</div>
            <div class="stat-value"><?php echo count($_SESSION['users']); ?></div>
        </div>
    </div>
    <div class="card">
        <h2>快速导航</h2>
        <div class="actions">
            <a class="btn" href="users.php">查看用户</a>
            <a class="btn" href="logout.php">退出登录</a>
        </div>
    </div>
</main>
</body>
</html>
