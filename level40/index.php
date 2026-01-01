<?php include '../headers.php'; ?>
<?php
$flag = 'flag{3620b714-4510-4902-874d-5b010022f1c1}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
$url = isset($_GET['url']) ? $_GET['url'] : '';
$safe = $url;
$safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
$safe = preg_replace('/window\s*\.\s*alert/i', 'window.blocked', $safe);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 40 - Bracket String Bypass</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 40: Bracket String Bypass">Level 40: Bracket String Bypass</h1>
        <form method="GET" action="">
            <input type="text" name="url" placeholder="Enter URL for href">
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        <div class="message" style="margin-top:16px;">
            <?php if ($url !== ''): ?>
                <a id="go" href="<?php echo $safe; ?>">Open Link</a>
            <?php else: ?>
                <div>Try: javascript:window['al'+'ert']('xss')</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
