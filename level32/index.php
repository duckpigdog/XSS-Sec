<?php
include '../headers.php';
setcookie("flag", "flag{c2f1e3b6-32f0-4f3f-9a7b-7b6c32a4f932}", time() + 3600, "/", "", false, false);
$search = isset($_GET['search']) ? $_GET['search'] : '';
$render = $search;
for ($i = 0; $i < 3; $i++) {
    $decoded = urldecode($render);
    if ($decoded === $render) break;
    $render = $decoded;
}
if ($render) {
    $s = strtolower($render);
    if (preg_match('/\bon\w+\s*=/i', $s)) {
        http_response_code(400);
        die('Blocked: event handlers not allowed');
    }
    if (preg_match('/\bhref\s*=/i', $s)) {
        http_response_code(400);
        die('Blocked: href attribute not allowed');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 32 - Reflected XSS (href/events blocked)</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 32: Reflected XSS (href/events blocked)">Level 32: Reflected XSS (href/events blocked)</h1>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Enter payload here" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear</button>
        </form>
        <div class="message">
            <div id="content"><?php echo $render; ?></div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('content');
        if (!container) return;
        var anims = container.querySelectorAll('animate[attributeName="href"],animate[attributeName="xlink:href"],set[attributeName="href"],set[attributeName="xlink:href"]');
        anims.forEach(function (an) {
            var val = (an.getAttribute('values') || an.getAttribute('to') || '').trim();
            if (!val) return;
            var a = an.closest('a');
            if (!a) return;
            if (/^javascript:/i.test(val)) {
                try {
                    a.setAttribute('href', val);
                    a.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', val);
                } catch (e) {
                    a.setAttribute('href', val);
                }
                var text = a.querySelector('text');
                if (text) {
                    text.setAttribute('style', 'cursor: pointer; pointer-events: auto;');
                }
            }
        });
    });
    </script>
</body>
</html>
