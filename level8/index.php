<?php 
include '../headers.php'; 
setcookie("flag", "flag{html_entity_encoding_level8}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 8 - Encoding Bypass</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 8: Encoding Bypass">Level 8: Encoding Bypass</h1>
        <p>Your task: The filter is strict, but maybe the browser can help decode?</p>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Add Link</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                $str = strtolower($_GET['keyword']);
                
                // Vulnerability: 
                // 1. Strict Keyword Filter (replaces with underscores or blocks)
                // 2. Output is placed inside an <a href="..."> attribute
                
                $str = str_replace("script", "scr_ipt", $str);
                $str = str_replace("on", "o_n", $str);
                $str = str_replace("src", "sr_c", $str);
                $str = str_replace("data", "da_ta", $str);
                $str = str_replace("href", "hr_ef", $str);
                // Also double quotes are encoded by htmlspecialchars in the input above, 
                // but let's see how we output it below.
                
                // IMPORTANT: We output the variable directly into href attribute.
                // We do NOT use htmlspecialchars here on the $str, but we did handle quotes?
                // Actually, let's assume quotes are safe (double quotes filtered/encoded)
                // The key is that the filter blocks "javascript".
                // Wait, I didn't filter "javascript" above. Let's add it.
                $str = str_replace("javascript", "java_script", $str);
                
                // THE BYPASS: HTML Entity Encoding
                // Browsers decode HTML entities in attribute values (like href).
                // So &#106;avascript:alert(1) -> javascript:alert(1)
                
                // We need to output it safely inside double quotes.
                // If user sends ", it might break out. Let's assume we want them to stay INSIDE the href.
                $str = htmlspecialchars($str); 
                // htmlspecialchars encodes ", <, >, &, etc.
                // BUT it does NOT encode &#...; entities if they are already there?
                // Actually, htmlspecialchars encodes '&' to '&amp;'.
                // So if user sends &#106;, it becomes &amp;#106; which breaks the entity.
                
                // CORRECTION:
                // To allow HTML Entity Bypass, the backend must NOT encode the ampersand '&'.
                // OR, the backend encodes quotes " but leaves & alone?
                // Let's implement a custom escape that only escapes quotes but leaves entities working.
                
                $str = $_GET['keyword'];
                $str = str_replace("script", "scr_ipt", $str);
                $str = str_replace("on", "o_n", $str);
                $str = str_replace("src", "sr_c", $str);
                $str = str_replace("data", "da_ta", $str);
                $str = str_replace("href", "hr_ef", $str);
                $str = str_replace("javascript", "\"java_script\"", $str); // Break it with quotes
                $str = str_replace('"', '&quot;', $str); // Escape double quotes to prevent breakout
                
                echo '<a href="' . $str . '">Your Link</a>';
            }
            ?>
        </div>
    </div>
</body>
</html>
