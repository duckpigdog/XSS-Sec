<?php 
include '../headers.php'; 
setcookie("flag", "flag{srcdoc_entity_bypass_level18}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 18 - Attribute Injection</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 18: Attribute Injection">Level 18: Attribute Injection</h1>
        <p>Your task: The input is put into an iframe's <code>srcdoc</code> attribute.</p>
        <p>The server escapes <code>&lt;</code>, <code>&gt;</code>, <code>"</code>, <code>'</code> and removes "script".</p>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Vulnerability: 
                // 1. Manual escaping that forgets '&'.
                // 2. Keyword filtering of 'script'.
                // 3. Output into 'srcdoc', which supports HTML Entities AND creates a new HTML context.
                
                // Step 1: Manual Escape (Flawed - misses '&')
                $str = str_replace('<', '&lt;', $str);
                $str = str_replace('>', '&gt;', $str);
                $str = str_replace('"', '&quot;', $str);
                $str = str_replace("'", '&#39;', $str);
                
                // Step 2: Keyword Filter
                $str = str_ireplace("script", "", $str);
                $str = str_ireplace("on", "", $str); // Let's filter 'on' events too
                
                // Output to srcdoc
                // srcdoc takes HTML content.
                // <iframe srcdoc="<b>Hi</b>"></iframe>
                echo '<iframe srcdoc="' . $str . '" width="100%" height="100"></iframe>';
            }
            ?>
        </div>
    </div>
</body>
</html>
