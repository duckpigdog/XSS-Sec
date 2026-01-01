<?php
include '../headers.php';
$flag = 'flag{7defdc4c-de46-4235-a01b-ecc48944b4e3}';
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
    $scripts = [];
    $i = 0;
    if (preg_match_all('/<\s*script\b[^>]*>[\s\S]*?<\s*\/\s*script\s*>/i', $safe, $ms)) {
        foreach ($ms[0] as $block) {
            $token = "%%SCRIPT_BLOCK_" . $i . "%%";
            $scripts[$token] = $block;
            $safe = str_replace($block, $token, $safe);
            $i++;
        }
    }
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed)\b/i', '<blocked', $safe);
    foreach ($scripts as $token => $block) {
        $safe = str_replace($token, $block, $safe);
    }
    $safe = preg_replace('/eval\s*\(/i', 'blocked(', $safe);
    $safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 47 - Throw onerror comma XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 47: Throw onerror comma XSS">Level 47: Throw onerror comma XSS</h1>
        <form method="GET" action="">
            <input type="text" name="content" placeholder="Enter payload here" value="<?php echo htmlspecialchars($input, ENT_QUOTES); ?>">
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear</button>
        </form>
        <div class="message">
            <div id="content"><?php echo $safe; ?></div>
        </div>
    </div>
</body>
</html>
