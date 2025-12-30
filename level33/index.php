<?php
include '../headers.php';
setcookie("flag", "flag{d656bae7-0b2e-4093-a1d8-380c25779a84}", time() + 3600, "/", "", false, false);
$q = isset($_GET['q']) ? $_GET['q'] : '';
$blocked = false;
if ($q !== '') {
    if (preg_match('/[\s]/', $q)) $blocked = true;
    if (preg_match('/[()]/', $q)) $blocked = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 33 - JS URL XSS (chars blocked)</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 33: JS URL XSS">Level 33: JS URL XSS</h1>
        <form method="GET" action="">
            <input type="text" name="q" placeholder="Enter payload here" value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear</button>
        </form>
        <div class="message">
            <?php if ($q !== ''): ?>
                <?php if ($blocked): ?>
                    <h2>Blocked: some characters are not allowed (spaces, parentheses)</h2>
                <?php else: ?>
                    <div>
                        <a class="is-linkback" href="javascript:fetch('/analytics',{method:'post',body:'/post?postId=5&<?php echo $q; ?>'}).finally(_=>window.location='/')">Back to Blog</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div>
                    <a class="is-linkback" href="javascript:fetch('/analytics',{method:'post',body:'/post?postId=5&666'}).finally(_=>window.location='/')">Back to Blog</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
