<?php
include '../headers.php';
setcookie('session', 'admin_session_value', time() + 1800, '/level29/admin-view.php');
$dataFile = __DIR__ . '/comments.json';
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, '[]');
}
$raw = file_get_contents($dataFile);
$comments = json_decode($raw, true);
if (!is_array($comments)) $comments = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Victim Viewer</title>
    <style>
        :root { --bg:#ffffff; --text:#1f2937; --muted:#6b7280; }
        body { margin:0; background: var(--bg); color: var(--text); font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
        .wrap { max-width: 800px; margin: 0 auto; padding: 20px; }
        .muted { color: var(--muted); font-size: 13px; }
    </style>
</head>
<body>
    <div class="wrap">
        <h2>Victim is viewing comments</h2>
        <div class="muted">This page loads all comments. Any scripts inside comments run here.</div>
        <div style="margin-top:12px;">
            <?php foreach ($comments as $c): ?>
                <div style="border-top:1px solid #e5e7eb; padding:10px 0;">
                    <div style="font-weight:700;"><?php echo htmlspecialchars($c['author'], ENT_QUOTES); ?></div>
                    <div><?php echo $c['text']; ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($comments)): ?>
                <div class="muted">No comments yet.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
