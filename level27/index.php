<?php
include '../headers.php';
setcookie("flag", "flag{2a31fe22-ef3c-4647-a0ec-d996ce0a1387}", time() + 3600, "/", "", false, false);

$dataFile = __DIR__ . '/comments.json';
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, "[]");
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
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $website = isset($_POST['website']) ? trim($_POST['website']) : '';
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    if ($author !== '' && $website !== '' && $text !== '') {
        save_comment($dataFile, [
            'author' => $author,
            'website' => $website,
            'text' => $text,
            'time' => time()
        ]);
        header('Location: index.php');
        exit;
    }
}

$comments = load_comments($dataFile);

function encode_for_onclick_js_single_quoted($s) {
    $s = str_replace("\\", "\\\\", $s);
    $s = str_replace("'", "\\'", $s);
    $s = str_replace('"', '&quot;', $s);
    $s = str_replace("<", "&lt;", $s);
    $s = str_replace(">", "&gt;", $s);
    return $s;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 27 - Blog Comments</title>
    <style>
        :root { --bg:#f6f8fb; --card:#ffffff; --accent:#2563eb; --muted:#6b7280; --text:#1f2937; --danger:#e11d48; }
        * { box-sizing: border-box; }
        body { margin:0; background: var(--bg); color: var(--text); font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
        .container { max-width: 960px; margin: 0 auto; padding: 30px 20px; }
        .header { display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .brand { font-weight: 800; letter-spacing: .5px; }
        .brand span { color: var(--accent); }
        .post { background: var(--card); border: 1px solid #e5e7eb; padding: 24px; border-radius: 10px; box-shadow: 0 6px 18px rgba(17,24,39,0.06); }
        .post h1 { margin:0 0 10px; font-size: 26px; }
        .post .meta { color: var(--muted); font-size: 14px; margin-bottom: 16px; }
        .post p { color: #374151; line-height: 1.8; }
        .comments { margin-top: 24px; background: var(--card); border: 1px solid #e5e7eb; padding: 20px; border-radius: 10px; box-shadow: 0 6px 18px rgba(17,24,39,0.04); }
        .comments h3 { margin:0 0 16px; }
        .comment { border-top: 1px solid #f3f4f6; padding: 14px 0; }
        .comment:first-child { border-top: none; }
        .comment .author { font-weight: 700; }
        .comment .author a { color: var(--accent); text-decoration: none; cursor: pointer; }
        .comment .text { color: #374151; margin-top: 6px; white-space: pre-wrap; }
        .form { margin-top: 24px; background: var(--card); border: 1px solid #e5e7eb; padding: 20px; border-radius: 10px; box-shadow: 0 6px 18px rgba(17,24,39,0.04); }
        .form label { display:block; color: var(--muted); margin-bottom: 6px; }
        .form input, .form textarea { width: 100%; background: #ffffff; border: 1px solid #d1d5db; color: var(--text); padding: 10px 12px; border-radius: 8px; outline: none; }
        .form input:focus, .form textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .form textarea { min-height: 120px; resize: vertical; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .actions { display:flex; gap: 10px; margin-top: 12px; }
        .btn { background: var(--accent); border:none; color:#fff; padding:10px 16px; border-radius:8px; cursor:pointer; font-weight:700; box-shadow: 0 6px 12px rgba(37,99,235,0.25); }
        .btn.secondary { background: #eef2ff; color: #1f2937; border: 1px solid #c7d2fe; }
        .note { margin-top:10px; color: var(--muted); font-size: 13px; }
        .footer { margin-top: 24px; color: var(--muted); font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">Sec<span>Blog</span></div>
            <div><a href="../index.php" style="color: var(--muted); text-decoration: none;">Lab Home</a></div>
        </div>
        <div class="post">
            <h1>Understanding Client-Side Security Pitfalls</h1>
            <div class="meta">Published by Editorial • Security • Today</div>
            <p>In this article, we review common mistakes in client-side handling of user-supplied data and how subtle encoding differences can lead to exploitable conditions.</p>
        </div>
        <div class="comments">
            <h3>Comments</h3>
            <?php foreach ($comments as $c): ?>
                <div class="comment">
                    <?php 
                        $author = htmlspecialchars($c['author'], ENT_QUOTES);
                        $website_raw = $c['website'];
                        $website_for_js = encode_for_onclick_js_single_quoted($website_raw);
                        $text = htmlspecialchars($c['text']);
                    ?>
                    <div class="author">
                        <a href="#" onclick="window.location.href='<?php echo $website_for_js; ?>'; return false;" title="Visit author's website"><?php echo $author; ?></a>
                    </div>
                    <div class="text"><?php echo $text; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form">
            <form method="POST" action="index.php">
                <div class="row">
                    <div>
                        <label>Author</label>
                        <input type="text" name="author" placeholder="Your name">
                    </div>
                    <div>
                        <label>Website</label>
                        <input type="text" name="website" placeholder="https://example.com">
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <label>Comment</label>
                    <textarea name="text" placeholder="Share your thoughts"></textarea>
                </div>
                <div class="actions">
                    <button class="btn" type="submit">Post Comment</button>
                    <button class="btn secondary" type="button" onclick="location.href='index.php'">Refresh</button>
                </div>
                <div class="note">Tip: The website is used when clicking the author name.</div>
            </form>
        </div>
        <div class="footer">© SecBlog</div>
    </div>
</body>
</html>
