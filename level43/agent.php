<?php
session_start();
$messages = isset($_SESSION['level43_messages']) ? $_SESSION['level43_messages'] : [];
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Level 43 - 客服工作台</title>
  <style>
    :root { color-scheme: light; }
    body { margin:0; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; background:#f8fafc; color:#111827; }
    header { background:#ffffff; border-bottom:1px solid #e5e7eb; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:18px; }
    a { text-decoration:none; }
    main { max-width:960px; margin:24px auto; padding:0 16px; display:grid; grid-template-columns: 1fr 280px; gap:16px; }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .panel:hover { box-shadow:0 6px 18px rgba(0,0,0,.08); transform: translateY(-1px); transition: box-shadow .2s ease, transform .12s ease; }
    .chat { display:flex; flex-direction:column; gap:10px; }
    .bubble { border:1px solid #e5e7eb; background:#fff; border-radius:8px; padding:10px 12px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .muted { color:#6b7280; font-size:12px; }
  </style>
</head>
<body>
  <header>
    <h1>客服工作台 · 用户留言</h1>
    <div class="nav">
      <a href="/level43/index.php">返回聊天</a>
    </div>
  </header>
  <main>
    <section class="panel">
      <div class="muted">点击用户消息中的链接进行处理</div>
      <div class="chat" style="margin-top:12px;">
        <?php
        $hasUser = false;
        foreach ($messages as $m) {
          if ($m['role'] === 'user') {
            $hasUser = true;
            echo '<div class="bubble">' . $m['content'] . '</div>';
          }
        }
        if (!$hasUser) {
          echo '<div class="muted">暂无用户留言</div>';
        }
        ?>
      </div>
    </section>
    <aside class="panel">
      <div class="muted">注意事项</div>
      <p style="margin-top:8px;">请谨慎点击用户提供的外部链接。如果链接为 JavaScript 伪协议，点击后可能会在当前页面执行脚本</p>
    </aside>
  </main>
</body>
</html>

