<?php
include '../headers.php';
$flag = 'flag{f1cb6d4b-7f1a-4c6b-b1a4-4a9b3b2c7e91}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
$input = isset($_GET['content']) ? $_GET['content'] : '';
$render = $input;
for ($i = 0; $i < 2; $i++) {
    $dec = rawurldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
$safe = $render;
if ($safe !== '') {
    $sources = [];
    $i = 0;
    if (preg_match_all('/<\s*source\b[^>]*>/i', $safe, $ms)) {
        foreach ($ms[0] as $block) {
            $token = "%%SOURCE_BLOCK_" . $i . "%%";
            $sources[$token] = $block;
            $safe = str_replace($block, $token, $safe);
            $i++;
        }
    }
    $safe = preg_replace('/<\s*script\b[\s\S]*?<\s*\/\s*script\s*>/i', '', $safe);
    $safe = preg_replace('/\b(onload|onclick|onmouseover|onfocus|onanimationend|onerror)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed|a)\b/i', '<blocked', $safe);
    foreach ($sources as $token => $block) {
        $safe = str_replace($token, $block, $safe);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 49 - Video Source onerror XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 49: Video Source onerror XSS">Level 49: Video Source onerror XSS</h1>
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
