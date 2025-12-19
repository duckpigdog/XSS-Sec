<?php 
include '../headers.php'; 
setcookie("flag", "flag{fb6abe6c-0278-4961-ad54-e8b28bd53eb9}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 5 - Filter Bypass</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 5: Filter Bypass">Level 5: Filter Bypass</h1>
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Vulnerability: Simple blacklist filter
                // Only <script> is banned, forcing the use of other tags/attributes
                
                $str = str_ireplace("<script", "<scr_ipt", $str);
                
                // on events are ALLOWED in this level to demonstrate alternate vectors
                
                echo "Result: " . $str;
            }
            ?>
        </div>
    </div>
</body>
</html>

