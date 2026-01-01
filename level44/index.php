<?php
include '../headers.php';
$flag = 'flag{c9b87a01-c917-4923-a871-20726db044ae}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
$input = isset($_GET['content']) ? $_GET['content'] : '';
$render = $input;
for ($i = 0; $i < 2; $i++) {
    $dec = urldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
$safe = $render;
if ($safe !== '') {
    $safe = preg_replace('/<\s*script\b[\s\S]*?<\s*\/\s*script\s*>/i', '', $safe);
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/<\s*(iframe|img|object|embed)\b/i', '<blocked', $safe);
    // Do NOT block style or onanimationend to allow CSS animation payload to work
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 44 - CSS Animation Event XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        * { animation-duration: 1s; animation-iteration-count: 1; }
        xss { display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 44: CSS Animation Event XSS">Level 44: CSS Animation Event XSS</h1>
        <form method="GET" action="">
            <input type="text" name="content" placeholder="Enter payload here" value="<?php echo htmlspecialchars($input, ENT_QUOTES); ?>">
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear</button>
        </form>
        <div class="message">
            <div id="output"><?php echo $safe; ?></div>
        </div>
    </div>
</body>
</html>
