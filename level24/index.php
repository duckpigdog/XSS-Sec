<?php
setcookie("flag", "flag{bf38249a-5e17-4861-8321-468213612345}", time() + 3600, "/", "", false, false);
include '../headers.php';

$query = isset($_GET['search']) ? $_GET['search'] : '';

if ($query) {
    // WAF Logic
    $blocked_tags = [
        'script', 'img', 'svg', 'iframe', 'object', 'embed', 'style', 'meta', 
        'link', 'div', 'p', 'a', 'input', 'button', 'form', 'details', 'summary', 
        'select', 'option', 'textarea', 'video', 'audio', 'table', 'td', 'tr',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'strong', 'em', 'i', 'b'
    ];
    
    $blocked_attributes = [
        'onload', 'onerror', 'onclick', 'onmouseover', 'onfocus', 'onblur', 
        'onkeydown', 'onkeyup', 'onkeypress', 'onchange', 'onsubmit',
        'onmousedown', 'onmouseup', 'onmousemove', 'onmouseout', 'onmouseenter', 'onmouseleave',
        'ondblclick', 'oncontextmenu', 'onwheel', 'onscroll', 'oncopy', 'oncut', 'onpaste'
    ];

    $input_lower = strtolower($query);
    
    foreach ($blocked_tags as $tag) {
        if (preg_match("/<\s*$tag\b/i", $input_lower)) {
            http_response_code(400);
            die("Tag Not Allowed: " . htmlspecialchars($tag));
        }
    }
    
    foreach ($blocked_attributes as $attr) {
        if (strpos($input_lower, $attr) !== false) {
            if ($attr === 'onresize') continue;
            http_response_code(400);
            die("Attribute Not Allowed: " . htmlspecialchars($attr));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 24 - WAF Bypass</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 24: WAF Bypass">Level 24: WAF Bypass</h1>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($query); ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear</button>
        </form>
        
        <div class="message">
            <?php if ($query): ?>
                <h2>Results for: <?php echo $query; // VULNERABLE ?></h2>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 40px; border-top: 1px dashed var(--neon-cyan); padding-top: 20px;">
            <h3>Report to Admin</h3>
            <p>Found a vulnerability? Enter your payload below to notify the admin.</p>
            <div>
                <input type="text" id="payload-input">
                <button onclick="AdminBot.send('level24', 'payload-input')">Send to Admin Bot</button>
            </div>
            <div style="margin-top: 10px; font-size: 12px; font-family: 'Share Tech Mono';">
                STATUS: <span id="bot-status" style="color: #666;">IDLE</span>
            </div>
        </div>
    </div>
    <script src="../assets/admin-bot.js"></script>
</body>
</html>
