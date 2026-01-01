<?php
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
$flagValue = 'flag{9d946110-ef69-47f7-a0a9-c54a15a7eb34}';
setcookie('flag', $flagValue, time() + 3600, '/', '', false, false);
$msg = '';
if (isset($_GET['delete'])) {
    $df = $_GET['delete'];
    if (preg_match('/^[a-f0-9]{16}\.pdf$/i', $df)) {
        $dp = $uploadDir . '/' . $df;
        if (is_file($dp) && unlink($dp)) {
            $msg = '删除成功：' . $df;
        } else {
            $msg = '删除失败：' . $df;
        }
    } else {
        $msg = '非法文件名';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $file = $_FILES['pdf'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newName = bin2hex(random_bytes(8)) . '.pdf';
        $target = $uploadDir . '/' . $newName;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $msg = '上传成功：' . $newName;
        } else {
            $msg = '上传失败';
        }
    } else {
        $msg = '上传错误：' . $file['error'];
    }
}
$files = array_values(array_filter(scandir($uploadDir), function($f) {
    return $f !== '.' && $f !== '..' && is_file(__DIR__ . '/uploads/' . $f);
}));
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Level 38 - 实验文档 PDF 上传与浏览</title>
  <style>
    :root { color-scheme: light; }
    body { margin:0; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; background:#f8fafc; color:#111827; }
    header { background:#ffffff; border-bottom:1px solid #e5e7eb; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:18px; }
    a { text-decoration:none; }
    .nav a { display:inline-block; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#111; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .nav a:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    main { max-width:960px; margin:24px auto; padding:0 16px; }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .panel:hover { box-shadow:0 6px 18px rgba(0,0,0,.08); transform: translateY(-1px); transition: box-shadow .2s ease, transform .12s ease; }
    .row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .muted { color:#6b7280; font-size:12px; }
    .btn { display:inline-block; border:1px solid #d1d5db; background:#fff; color:#111; border-radius:6px; padding:10px 14px; font-size:14px; text-align:center; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .btn:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th, td { border-bottom:1px solid #e5e7eb; padding:10px; text-align:left; }
    .ok { color:#16a34a; }
    .err { color:#dc2626; }
  </style>
</head>
<body>
  <header>
    <h1>实验文档中心 · PDF 上传与浏览</h1>
    <div class="nav">
      <a href="/index.php">返回首页</a>
    </div>
  </header>
  <main>
    <section class="panel">
      <div class="muted">上传说明</div>
      <p style="margin-top:8px;">请上传实验 PDF 文档</p>
      <form method="post" enctype="multipart/form-data" class="row" style="margin-top:12px;">
        <input type="file" name="pdf" accept=".pdf" />
        <button class="btn" type="submit">上传</button>
        <a class="btn" href="/level38/index.php">刷新</a>
      </form>
      <?php if ($msg): ?>
      <div style="margin-top:10px;" class="<?php echo strpos($msg,'成功')!==false ? 'ok' : 'err'; ?>"><?php echo $msg; ?></div>
      <?php endif; ?>
    </section>

    <section class="panel" style="margin-top:16px;">
      <div class="muted">历史上传</div>
      <table>
        <thead>
          <tr><th>文件名</th><th>操作</th></tr>
        </thead>
        <tbody>
          <?php foreach ($files as $f): ?>
          <tr>
            <td class="mono"><?php echo $f; ?></td>
            <td class="row">
              <a class="btn" target="_blank" href="/level38/uploads/<?php echo urlencode($f); ?>">浏览</a>
              <a class="btn" href="/level38/uploads/<?php echo urlencode($f); ?>">下载</a>
              <a class="btn" style="background:#fee2e2; border-color:#fecaca;" href="/level38/index.php?delete=<?php echo urlencode($f); ?>" onclick="return confirm('确认删除该文件？')">删除</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($files)): ?>
          <tr><td colspan="2" class="muted">暂无上传</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>
