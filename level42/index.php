<?php
$flag = 'flag{fec9b4d6-a21a-425c-ae3c-c6917480d120}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
$error = '';
$sqlShow = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = isset($_POST['username']) ? $_POST['username'] : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';
  $dsn = 'sqlite::memory:';
  try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT id FROM admin_users WHERE username = '$username' AND password = '$password'";
    $sqlShow = $sql;
    $pdo->query($sql);
  } catch (Throwable $e) {
    $error = $e->getMessage();
  }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Level 42 - 后台登录</title>
  <style>
    :root { color-scheme: light; }
    body { margin:0; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; background:#f8fafc; color:#111827; }
    header { background:#ffffff; border-bottom:1px solid #e5e7eb; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:18px; }
    a { text-decoration:none; }
    .nav a { display:inline-block; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#111; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .nav a:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    main { max-width:420px; margin:48px auto; padding:0 16px; }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .panel:hover { box-shadow:0 6px 18px rgba(0,0,0,.08); transform: translateY(-1px); transition: box-shadow .2s ease, transform .12s ease; }
    .row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .field { display:flex; flex-direction:column; gap:6px; margin-top:10px; }
    .input { border:1px solid #d1d5db; border-radius:6px; padding:10px 12px; font-size:14px; outline:none; transition:border-color .2s ease, box-shadow .2s ease; }
    .input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .btn { display:inline-block; border:1px solid #d1d5db; background:#fff; color:#111; border-radius:6px; padding:10px 14px; font-size:14px; text-align:center; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .btn:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    .muted { color:#6b7280; font-size:12px; }
    .err { color:#dc2626; }
    .mono { font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace; }
  </style>
</head>
<body>
  <header>
    <h1>后台登录 · 管理中心</h1>
    <div class="nav">
      <a href="/index.php">返回首页</a>
    </div>
  </header>
  <main>
    <section class="panel">
      <div class="muted">请输入管理员账号和密码登录</div>
      <form method="post" style="margin-top:12px;">
        <div class="field">
          <label class="muted">用户名</label>
          <input class="input" type="text" name="username" placeholder="admin">
        </div>
        <div class="field">
          <label class="muted">密码</label>
          <input class="input" type="password" name="password" placeholder="••••••••">
        </div>
        <div class="row" style="margin-top:12px;">
          <button class="btn" type="submit">登录</button>
          <a class="btn" href="/level42/index.php">刷新</a>
        </div>
      </form>
    </section>
    <?php if ($error !== '' || $sqlShow !== ''): ?>
    <section class="panel" style="margin-top:16px;">
      <div class="err">数据库错误</div>
      <div class="mono" style="margin-top:6px;"><?php echo $error; ?></div>
      <div class="muted" style="margin-top:8px;">SQL：</div>
      <div class="mono" style="margin-top:4px;"><?php echo $sqlShow; ?></div>
    </section>
    <?php endif; ?>
  </main>
</body>
</html>
