<?php include '../headers.php';
setcookie("flag", "flag{795f47f1-0007-41ac-bba5-cd4645bc1417}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 37 - Data URL Base64 XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 37: Data URL Base64 XSS">Level 37: Data URL Base64 XSS</h1>
        <form method="GET" action="">
            <input type="text" name="content" placeholder="Enter payload here">
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['content'])) {
                $content = $_GET['content'];
                $decoded = null;
                if (preg_match('/<object[^>]*\bdata\s*=\s*(?:"|\')?data:text\/html;base64,([^"\'\s>]+)(?:"|\')?[^>]*>/i', $content, $m)) {
                    $decoded = base64_decode($m[1]);
                } elseif (preg_match('/data:text\/html;base64,([A-Za-z0-9+\/=]+)/i', $content, $m)) {
                    $decoded = base64_decode($m[1]);
                }
                if ($decoded !== null) {
                    echo $decoded;
                } else {
                    $blacklist = [
                        '<script', '</script>', 'script', 'onerror', 'onload', 'onmouseover', 'onfocus', 'onmouseenter',
                        'onclick', 'onmouse', 'onkey', 'oninput', 'onchange', 'onpaste',
                        'href="javascript', 'href=javascript', 'javascript:',
                        '<img', '<iframe', '<svg', '<math', '<embed', '<video', '<audio', '<link', '<style'
                    ];
                    $sanitized = str_ireplace($blacklist, '', $content);
                    echo $sanitized;
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
