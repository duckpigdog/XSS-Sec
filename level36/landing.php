<?php
$adid = isset($_GET['adid']) ? $_GET['adid'] : 'AD-2025-001';
setcookie("flag", "flag{98543697-884b-4794-bea5-2863adeedde2}", time() + 3600, "/", "", false, false);
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Level 36 - 活动领取页</title>
  <style>
    :root { color-scheme: light; }
    body { margin:0; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; background:#f8fafc; color:#111827; }
    header { background:#ffffff; border-bottom:1px solid #e5e7eb; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:18px; }
    a { text-decoration:none; }
    .nav a { display:inline-block; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#111; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .nav a:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    main { max-width:720px; margin:24px auto; padding:0 16px; }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .panel:hover { box-shadow:0 6px 18px rgba(0,0,0,.08); transform: translateY(-1px); transition: box-shadow .2s ease, transform .12s ease; }
    .row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .muted { color:#6b7280; font-size:12px; }
    .mono { font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace; }
    .btn { display:inline-block; border:1px solid #d1d5db; background:#fff; color:#111; border-radius:6px; padding:10px 14px; font-size:14px; width:140px; text-align:center; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .btn:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
  </style>
</head>
<body>
  <header>
    <h1>活动领取页</h1>
    <div class="nav">
      <a href="/level36/index.php">返回上一页</a>
      <a href="/index.php" style="margin-left:8px;">返回首页</a>
    </div>
  </header>
  <main>
    <section class="panel">
      <div class="muted">参与信息</div>
      <p style="margin-top:8px;">请核对下方广告编号，系统将为您分配权益礼包。</p>
      <div class="row" style="margin-top:8px;">
        <span class="muted">广告编号：</span>
        <span class="mono"><?php echo $adid; ?></span>
      </div>
      <p class="muted" style="margin-top:12px;">可更改链接中的 adid 参数并刷新页面以更新编号。</p>
      <div style="margin-top:12px;">
        <a class="btn" href="/level36/landing.php?adid=AD-2025-001">示例编号</a>
        <a class="btn" href="/level36/landing.php?adid=%3Cscript%3Ealert%28/xss/%29%3C/script%3E">示例 XSS</a>
      </div>
    </section>
  </main>
</body>
</html>
