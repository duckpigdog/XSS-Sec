<?php
session_start();
$flag = 'flag{8b1945c1-70bb-4289-9545-2513283b62cf}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
if (!isset($_SESSION['level43_messages'])) {
  $_SESSION['level43_messages'] = [];
}
$botReply = '对不起，您的问题我暂时无法解答，请您点击右边【转人工】客服按钮获得帮助';
if (isset($_GET['clear']) && $_GET['clear'] === '1') {
  $_SESSION['level43_messages'] = [];
  header('Location: /level43/index.php');
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $msg = isset($_POST['message']) ? $_POST['message'] : '';
  $_SESSION['level43_messages'][] = ['role' => 'user', 'content' => $msg, 'time' => time()];
  $_SESSION['level43_messages'][] = ['role' => 'bot', 'content' => $botReply, 'time' => time()];
  header('Location: /level43/index.php');
  exit;
}
$messages = $_SESSION['level43_messages'];
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Level 43 - 在线客服</title>
  <style>
    :root { color-scheme: light; }
    body { margin:0; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif; background:#f8fafc; color:#111827; }
    header { background:#ffffff; border-bottom:1px solid #e5e7eb; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:18px; }
    a { text-decoration:none; }
    .nav a { display:inline-block; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#111; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .nav a:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    main { max-width:960px; margin:24px auto; padding:0 16px; display:grid; grid-template-columns: 1fr 280px; gap:16px; }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .panel:hover { box-shadow:0 6px 18px rgba(0,0,0,.08); transform: translateY(-1px); transition: box-shadow .2s ease, transform .12s ease; }
    .chat { height:420px; overflow:auto; display:flex; flex-direction:column; gap:10px; }
    .msg { display:flex; gap:10px; align-items:flex-start; }
    .bubble { border:1px solid #e5e7eb; background:#fff; border-radius:8px; padding:10px 12px; max-width:70%; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .user .bubble { background:#eef2ff; border-color:#c7d2fe; }
    .bot .bubble { background:#f9fafb; }
    .tools { display:flex; gap:8px; align-items:center; }
    .input { border:1px solid #d1d5db; border-radius:6px; padding:10px 12px; font-size:14px; outline:none; flex:1; transition:border-color .2s ease, box-shadow .2s ease; }
    .input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .btn { display:inline-block; border:1px solid #d1d5db; background:#fff; color:#111; border-radius:6px; padding:10px 14px; font-size:14px; text-align:center; transition:transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease; }
    .btn:hover { background:#f3f4f6; transform: translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
    .muted { color:#6b7280; font-size:12px; }
  </style>
</head>
<body>
  <header>
    <h1>在线客服 · 智能助手</h1>
    <div class="nav">
      <a href="/index.php">返回首页</a>
    </div>
  </header>
  <main>
    <section class="panel">
      <div class="chat" id="chat">
        <?php foreach ($messages as $m): ?>
          <div class="msg <?php echo $m['role']; ?>">
            <div class="bubble"><?php echo $m['content']; ?></div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?>
          <div class="muted">欢迎使用在线客服，请输入您的问题。所有问题均可通过“转人工”获得帮助</div>
        <?php endif; ?>
      </div>
      <form method="post" class="tools" style="margin-top:12px;">
        <input class="input" type="text" name="message" placeholder="请输入消息，例如包含链接的内容">
        <button class="btn" type="submit">发送</button>
        <a class="btn" href="/level43/index.php?clear=1">清空</a>
      </form>
    </section>
    <aside class="panel">
      <div class="muted">人工客服</div>
      <p style="margin-top:8px;">如果智能客服无法解答，请点击下方按钮转人工</p>
      <div class="tools" style="margin-top:12px;">
        <a class="btn" href="/level43/agent.php" target="_blank">转人工</a>
      </div>
      <div class="muted" style="margin-top:12px;">说明：人工客服将查看您的原始消息内容，消息中的链接可被点击</div>
    </aside>
  </main>
</body>
</html>

