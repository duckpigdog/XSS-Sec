<?php
$adurl = isset($_GET['adurl']) ? $_GET['adurl'] : '';
$adid = isset($_GET['adid']) ? $_GET['adid'] : '';
if (isset($_GET['go']) && $_GET['go'] === '1' && $adurl) {
    header('Location: ' . $adurl);
    exit;
}
setcookie("flag", "flag{98543697-884b-4794-bea5-2863adeedde2}", time() + 3600, "/", "", false, false);
$defaultDest = '/promo/landing?campaign=summer&from=ad';
$dest = $adurl !== '' ? $adurl : $defaultDest;
$partner = parse_url($dest, PHP_URL_HOST);
$partnerDisplay = $partner ? $partner : 'partner.example';
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Level 36 - 客服中心与广告联动</title>
  <style>
    :root { color-scheme: light; }
    body { margin:0; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; background:#f8fafc; color:#111827; }
    header { background:#ffffff; border-bottom:1px solid #e5e7eb; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:18px; }
    a { text-decoration:none; }
    .nav a { display:inline-block; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#111; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .nav a:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    main { max-width:960px; margin:24px auto; padding:0 16px; display:grid; grid-template-columns: 1fr 320px; gap:16px; }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .panel:hover { box-shadow:0 6px 18px rgba(0,0,0,.08); transform: translateY(-1px); transition: box-shadow .2s ease, transform .12s ease; }
    .row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .muted { color:#6b7280; font-size:12px; }
    .btn { display:inline-block; border:1px solid #d1d5db; background:#fff; color:#111; border-radius:6px; padding:10px 14px; font-size:14px; width:140px; text-align:center; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .btn:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    .mono { font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace; }
    .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border:0; }
    .sponsor-banner { position:absolute; left:-9999px; top:auto; width:1px; height:1px; overflow:hidden; }
    .sponsor-banner img { width:1px; height:1px; }
  </style>
</head>
<body>
  <header>
    <h1>客服中心 · 工单状态</h1>
    <div class="nav">
      <a href="/index.php">返回首页</a>
    </div>
  </header>
  <main>
    <section class="panel">
      <div class="muted">工单信息</div>
      <p style="margin-top:8px;">您的问题已被受理，客服将于 24 小时内联系您。为确保活动权益，请保持手机号畅通。</p>
      <div class="row" style="margin-top:8px;">
        <span class="muted">合作方：</span>
        <span class="mono"><?php echo htmlspecialchars($partnerDisplay, ENT_QUOTES); ?></span>
      </div>
      <div class="row" style="margin-top:6px;">
        <a class="btn" href="/level36/index.php">刷新</a>
      </div>
    </section>
    <aside class="panel">
      <div class="muted">活动权益</div>
      <p style="margin-top:8px;">参与最新促销可享包邮及满减，点击广告后将跳转到合作方活动页面完成领取。</p>
      <div class="row" style="margin-top:12px;">
        <a class="btn" href="/level36/landing.php?adid=<?php echo urlencode($adid ?: 'AD-2025-001'); ?>">立即参与</a>
        <a class="btn" href="/level36/index.php">刷新</a>
      </div>
    </aside>
  </main>
  <a class="sponsor-banner" href="/level36/landing.php?adid=<?php echo urlencode($adid ?: 'AD-2025-001'); ?>">
    <img alt="赞助广告" src="https://dummyimage.com/640x240/f0f4f8/111827&text=%E4%BC%98%E6%83%A0%E6%B4%BB%E5%8A%A8">
  </a>
  <div class="sr-only">
    <div class="mono"><?php echo $adurl; ?></div>
    <div class="mono"><?php echo $adid; ?></div>
  </div>
</body>
</html>
