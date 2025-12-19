<?php 
include '../headers.php'; 
setcookie("flag", "flag{9b93b9f9-e9e2-4168-9871-4540c20c97a7}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 11 - JS Context</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 11: JS Context">Level 11: JS Context</h1>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <script>
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Vulnerability: 
                // Input is echoed directly into a JavaScript string variable.
                // It is NOT encoded for JavaScript context (no json_encode).
                // It might be htmlspecialchars() encoded, but that doesn't escape single quotes by default (unless ENT_QUOTES is set).
                // Or simply, we just echo it.
                
                // For this level, we simulate a common mistake: 
                // Developer uses user input inside a JS string without escaping quotes.
                
                // Context: var t_str = 'USER_INPUT';
                // Goal: ';alert(1);//
                
                // We deliberately DO NOT escape single quotes here.
                // We only escape < and > to prevent direct tag injection, forcing the user to use JS context breakout.
                $str_safe = str_replace(['<', '>'], ['&lt;', '&gt;'], $str);
                
                echo "var t_str = '$str_safe';";
                echo "\n            document.write('Current Search: ' + t_str);";
            } else {
                echo "var t_str = 'Guest';";
            }
            ?>
            </script>
        </div>
    </div>
</body>
</html>

