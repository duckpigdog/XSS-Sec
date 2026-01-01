<?php
include '../headers.php';
$flag = 'flag{b7f3f9f2-1f1a-4e9a-9b2a-8c6c1e6a48d2}';
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
    $safe = preg_replace('/[()]/', '', $safe);
    $safe = str_replace('`', '', $safe);
    $safe = str_replace('"', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed)\b/i', '<blocked', $safe);
    $safe = preg_replace('/hasInstance/i', 'blockedInstance', $safe);
    $safe = preg_replace('/\b(onerror|throw|Function|constructor)\b/i', 'blocked', $safe);
    $safe = preg_replace("/'([^'\\\\]|\\\\.)*'\\s*instanceof\\s*\\{/i", "$0", $safe);
    $safe = preg_replace("/'([^']*)'\\s*instanceof\\s*\\{/", "'$1' instanceof ({", $safe);
    $safe = preg_replace("/\\}\\s*<\\s*\\/\\s*script\\s*>/i", "})</script>", $safe);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 48 - Symbol.hasInstance Bypass</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 48: Symbol.hasInstance Bypass">Level 48: Symbol.hasInstance Bypass</h1>
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
