<?php
$uploadsDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0777, true);
}

setcookie("flag", "flag{078676ab-9384-4248-ba02-95ed3c93c791}", time() + 3600, "/", "", false, false);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $del = isset($_POST['file']) ? $_POST['file'] : '';
    if (preg_match('/^[0-9a-f]{16}\.html$/i', $del)) {
        $target = $uploadsDir . DIRECTORY_SEPARATOR . $del;
        if (is_file($target)) {
            @unlink($target);
            $msg = 'Deleted ' . htmlspecialchars($del);
        } else {
            $msg = 'File not found.';
        }
    } else {
        $msg = 'Invalid filename.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $f = $_FILES['file'];
    if ($f['error'] === UPLOAD_ERR_OK) {
        $name = $f['name'];
        $tmp = $f['tmp_name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext !== 'html') {
            $msg = 'Only .html files are allowed.';
        } else {
            $newName = bin2hex(random_bytes(8)) . '.html';
            $target = $uploadsDir . DIRECTORY_SEPARATOR . $newName;
            if (move_uploaded_file($tmp, $target)) {
                $msg = 'Upload successful. Renamed to ' . htmlspecialchars($newName);
            } else {
                $msg = 'Upload failed.';
            }
        }
    } else {
        $msg = 'Upload error code: ' . (int)$f['error'];
    }
}

$files = [];
$dh = @opendir($uploadsDir);
if ($dh) {
    while (($file = readdir($dh)) !== false) {
        if ($file === '.' || $file === '..') continue;
        if (substr($file, -5) !== '.html') continue;
        $files[] = [
            'name' => $file,
            'mtime' => @filemtime($uploadsDir . DIRECTORY_SEPARATOR . $file),
        ];
    }
    closedir($dh);
}
usort($files, function ($a, $b) { return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0); });

$download = isset($_GET['download']) ? $_GET['download'] : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Level 35 - Upload & Download Lab</title>
  <style>
    :root {
      color-scheme: light;
    }
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
      background: #f7f7fa;
      color: #111;
    }
    header {
      background: #ffffff;
      border-bottom: 1px solid #e5e7eb;
      padding: 16px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    header h1 { margin: 0; font-size: 20px; }
    header .nav { display: flex; gap: 8px; }
    main { max-width: 920px; margin: 24px auto; padding: 0 16px; }
    section.card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 16px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.04);
      transition: box-shadow .2s ease, transform .12s ease;
    }
    section.card:hover {
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      transform: translateY(-1px);
    }
    .muted { color: #6b7280; font-size: 12px; }
    input[type="file"] { display: inline-block; }
    button, .btn {
      display: inline-block;
      border: 1px solid #d1d5db;
      background: #fff;
      color: #111;
      border-radius: 6px;
      padding: 8px 12px;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
      transition: transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease;
    }
    .btn-primary {
      border-color: #2563eb;
      background: #2563eb;
      color: #fff;
      box-shadow: 0 1px 2px rgba(37,99,235,0.2);
    }
    .btn-danger {
      border-color: #ef4444;
      background: #ef4444;
      color: #fff;
      box-shadow: 0 1px 2px rgba(239,68,68,0.2);
    }
    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .list { margin: 0; padding: 0; list-style: none; }
    .list li {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #f0f2f6;
      transition: background .2s ease;
    }
    .list li:hover {
      background: #f9fafb;
    }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    .row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .input { border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 10px; min-width: 220px; }
    a { text-decoration: none; }
    a.link { color: #2563eb; }
    a.link:hover { opacity: .9; }
    .msg { margin-top: 8px; color: #374151; }
  </style>
</head>
<body>
  <header>
    <h1>Level 35 — Upload & Download</h1>
    <div class="muted">Only .html files are accepted. Files are randomly renamed after upload.</div>
    <div class="nav">
      <a class="btn" href="/index.php">Home</a>
    </div>
  </header>
  <main>
    <section class="card">
      <h2>Upload Attachment</h2>
      <form method="post" enctype="multipart/form-data">
        <div class="row">
          <input type="file" name="file" accept=".html">
          <button class="btn-primary" type="submit">Upload</button>
        </div>
      </form>
      <?php if ($msg): ?>
      <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>
    </section>

    <section class="card">
      <h2>Upload History</h2>
      <ul class="list">
        <?php if (!$files): ?>
          <li><span class="muted">No uploads yet.</span></li>
        <?php else: foreach ($files as $f): 
          $path = '/level35/uploads/' . $f['name']; // absolute web path
        ?>
          <li>
            <div>
              <div class="mono"><?php echo htmlspecialchars($f['name']); ?></div>
              <div class="muted">Saved at: <?php echo date('Y-m-d H:i:s', $f['mtime'] ?? time()); ?></div>
            </div>
            <div class="row">
              <a class="btn" href="<?php echo $path; ?>" target="_blank">View</a>
              <a class="btn" href="<?php echo $path; ?>" download>Download</a>
              <input class="input mono" value="<?php echo $path; ?>" readonly>
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($f['name']); ?>">
                <button type="submit" class="btn-danger delete-btn">Delete</button>
              </form>
            </div>
          </li>
        <?php endforeach; endif; ?>
      </ul>
    </section>

    
  </main>
  <script>
    document.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('delete-btn')) {
        if (!confirm('确认删除该文件吗？')) {
          e.preventDefault();
        }
      }
    });
  </script>
</body>
</html>
