<?php
include '../headers.php';
$flag = 'flag{2591da1c-f57f-4120-af9f-91314f3d0676}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
$theme = isset($_GET['theme']) ? $_GET['theme'] : '';
$render = $theme;
for ($i = 0; $i < 2; $i++) {
    $dec = urldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
$safe_js = $render;
if ($safe_js !== '') {
    $lc = strtolower($safe_js);
    $allow_chain = preg_match('/;\s*eval\s*\(\s*myundefvar\s*\)\s*;\s*var\s+myundefvar\s*;\s*alert\s*\(\s*(?:1|document\s*\.\s*cookie)\s*\)\s*;\s*\/\//', $lc);
    if (!$allow_chain) {
        $safe_js = preg_replace('/<\s*script\b/i', '', $safe_js);
        $safe_js = preg_replace('/\bon\w+\s*=/i', '', $safe_js);
        $safe_js = preg_replace('/javascript\s*:/i', '', $safe_js);
        $safe_js = preg_replace('/document\s*\.\s*cookie/i', 'document.blocked', $safe_js);
        $safe_js = preg_replace('/eval\s*\(/i', 'blocked(', $safe_js);
        $safe_js = preg_replace('/alert\s*\(/i', 'blocked(', $safe_js);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 46 - JS String Escape (eval)</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 46: JS String Escape (eval)">Level 46: JS String Escape (eval)</h1>
        <form method="GET" action="">
            <input type="text" name="theme" placeholder='Enter theme, e.g. dark' value="<?php echo htmlspecialchars($theme, ENT_QUOTES); ?>">
            <button type="submit">Apply</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear</button>
        </form>
        <div class="message">
            <div id="content">Theme applied: <span id="t-val"></span></div>
        </div>
    </div>
    <script>
    var theme = "<?php echo $safe_js; ?>";
    document.getElementById('t-val').textContent = theme;
    </script>
</body>
</html>
