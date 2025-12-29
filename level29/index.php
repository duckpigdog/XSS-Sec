<?php
include '../headers.php';
if (!isset($_COOKIE['session'])) {
    $sid = 'user_' . bin2hex(random_bytes(4));
    setcookie('session', $sid, time() + 3600, '/');
}
$dataFile = __DIR__ . '/comments.json';
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, '[]');
}
function load_comments($file) {
    $raw = file_get_contents($file);
    $arr = json_decode($raw, true);
    if (!is_array($arr)) $arr = [];
    return $arr;
}
function save_comment($file, $comment) {
    $arr = load_comments($file);
    $arr[] = $comment;
    file_put_contents($file, json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'clear') {
        file_put_contents($dataFile, '[]');
        header('Location: index.php');
        exit;
    }
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    if ($author !== '' && $text !== '') {
        save_comment($dataFile, [
            'author' => $author,
            'text' => $text,
            'time' => time()
        ]);
        header('Location: index.php');
        exit;
    }
}
$comments = load_comments($dataFile);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 29 - Cookie Exfiltration</title>
    <style>
        :root { --bg:#f6f8fb; --card:#ffffff; --accent:#2563eb; --text:#1f2937; --muted:#6b7280; --border:#e5e7eb; }
        * { box-sizing: border-box; }
        body { margin:0; background: var(--bg); color: var(--text); font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
        .container { max-width: 960px; margin: 0 auto; padding: 28px 20px; }
        .top { display:flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .brand { font-weight: 800; }
        .brand span { color: var(--accent); }
        .card { background: var(--card); border:1px solid var(--border); border-radius:10px; box-shadow:0 8px 20px rgba(17,24,39,.06); padding:20px; }
        .comments { margin-top: 18px; }
        .comment { border-top:1px solid var(--border); padding:14px 0; }
        .comment:first-child { border-top:none; }
        .author { font-weight:700; }
        .text { margin-top:6px; color:#374151; }
        .form { margin-top: 18px; }
        .row { display:grid; grid-template-columns: 1fr; gap:12px; }
        label { color: var(--muted); font-size: 13px; }
        input, textarea { width:100%; background:#fff; border:1px solid #d1d5db; color: var(--text); padding:10px 12px; border-radius:8px; outline:none; }
        input:focus, textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
        textarea { min-height:120px; resize:vertical; }
        .actions { display:flex; gap:10px; margin-top:10px; }
        .btn { background: var(--accent); color:#fff; border:none; padding:10px 16px; border-radius:8px; font-weight:700; cursor:pointer; box-shadow:0 6px 12px rgba(37,99,235,.25); }
        .link { color: var(--accent); text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="top">
            <div class="brand">Lite<span>Blog</span></div>
            <div><a class="link" href="admin-view.php" target="_blank">Open Victim Viewer</a> • <a class="link" href="my-account.php" target="_blank">My Account</a></div>
        </div>
        <div class="card form">
            <form method="POST" action="">
                <div class="row">
                    <div>
                        <label>Author</label>
                        <input type="text" name="author" placeholder="Your name">
                    </div>
                    <div>
                        <label>Comment</label>
                        <textarea name="text" placeholder="Write a comment..."></textarea>
                    </div>
                </div>
                <div class="actions">
                    <button class="btn" type="submit">Post Comment</button>
                </div>
            </form>
            <form method="POST" action="" style="margin-top:10px;" onsubmit="return confirm('Clear all comments?');">
                <input type="hidden" name="action" value="clear">
                <button class="btn" type="submit" style="background:#ef4444; box-shadow:0 6px 12px rgba(239,68,68,.25);">Clear Comments</button>
            </form>
        </div>
        <div class="card comments">
            <h3 style="margin:0 0 10px;">Comments</h3>
            <?php foreach ($comments as $c): ?>
                <div class="comment">
                    <div class="author"><?php echo htmlspecialchars($c['author'], ENT_QUOTES); ?></div>
                    <div class="text"><?php echo $c['text']; ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($comments)): ?>
                <div style="color:var(--muted);">No comments yet.</div>
            <?php endif; ?>
        </div>
        <iframe src="admin-view.php" style="width:0;height:0;border:0;position:absolute;left:-9999px;top:-9999px;" aria-hidden="true"></iframe>
    </div>
</body>
</html>
