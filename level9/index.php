<?php 
include '../headers.php'; 
setcookie("flag", "flag{6a9f1e24-a7a2-4149-884d-497e6376a2b3}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 9 - URL Validation</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 9: URL Validation">Level 9: URL Validation</h1>
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter URL here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Add Link</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Security Check 1: Keyword Removal (replaces with empty string)
                // Vulnerability: Only replaces once, allowing double write bypass.
                $str = str_ireplace("script", "", $str); 
                
                // Security Check 2: Must contain "http://"
                if (strpos($str, 'http://') === false) {
                    echo '<p style="color:red;">Error: Invalid URL. Must contain http://</p>';
                } else {
                    // Output
                    echo '<a href="' . $str . '">Your Link</a>';
                }
            }
            ?>
        </div>
    </div>
</body>
</html>

