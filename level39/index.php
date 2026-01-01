<?php include '../headers.php'; ?>
<?php
$flag = 'flag{ba01cb39-1b87-4191-a8f1-f86175145b8a}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 39 - Regex WAF Bypass via Attribute Slash</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 39: Regex WAF Bypass">Level 39: Regex WAF Bypass</h1>
        <form method="GET" action="">
            <input type="text" name="html" placeholder='Enter payload here'>
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['html'])) {
                $html = $_GET['html'];
                $decoded = null;
                if (preg_match('/<iframe[^>]*\bsrc\s*\/?\s*=\s*(?:"|\')?data:text\/html;base64,([^"\'\s>]+)(?:"|\')?[^>]*>/i', $html, $m)) {
                    $decoded = base64_decode($m[1]);
                } elseif (preg_match('/\bdata:text\/html;base64,([A-Za-z0-9+\/=]+)/i', $html, $m)) {
                    $decoded = base64_decode($m[1]);
                }
                if ($decoded !== null) {
                    echo $decoded;
                } else {
                    $pattern = '/(src|href)\s*=\s*["\']?data:/i';
                    $sanitized = preg_replace($pattern, '$1=blocked:', $html);
                    $sanitized = preg_replace('/\b(src|href)\s*\/\s*=/i', '$1=', $sanitized);
                    echo $sanitized;
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
