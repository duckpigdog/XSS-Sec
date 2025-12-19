<?php 
include '../headers.php'; 
setcookie("flag", "flag{c806ce06-d997-4871-a267-4f71f6872664}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 14 - Double Encoding</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 14: Double Encoding">Level 14: Double Encoding</h1>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div id="result" class="message"></div>

        <script>
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Security Check 1 (WAF Layer):
                // We strictly forbid literal quotes and angle brackets.
                // This happens AFTER the first automatic URL decode (by PHP $_GET).
                // If user sends '%27', PHP sees "'".
                // If user sends "'", PHP sees "'".
                // Both are caught here.
                
                if (preg_match("/['\"<>]/", $str)) {
                    // WAF Blocked: Sanitize dangerous chars
                    $str = str_replace(['\'', '"', '<', '>'], '_', $str);
                }
                
                // Vulnerability Implementation (Double Encoding Logic):
                
                // 1. Backend Encoding
                // The backend THEN proceeds to output the "cleaned" string into a JS variable.
                $encoded = urlencode($str);
                
                // 2. Frontend Logic
                echo "\n            var rawInput = '$encoded';";
                
                echo "\n            // Legacy System: Two rounds of decoding";
                echo "\n            var step1 = decodeURIComponent(rawInput);";
                echo "\n            var step2 = decodeURIComponent(step1);";
                
                // Correct sink: We need to make sure the breakout syntax is valid.
                echo "\n            setTimeout('console.log(\"Log: ' + step2 + '\")', 100);";
            }
            ?>
        </script>
    </div>
</body>
</html>
