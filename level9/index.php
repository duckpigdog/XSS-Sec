<?php 
include '../headers.php'; 
setcookie("flag", "flag{check_url_bypass_level9}", time() + 3600, "/", "", false, false);
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
        <p>Your task: The system now checks if your input is a valid URL (must contain http://).</p>
        
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
                $str = str_replace("script", "scr_ipt", $str); // Let's block script directly to force encoding or just handle validation
                // Wait, user asked for "Double Write" bypass.
                // Let's use str_replace('', ...) logic again but maybe slightly different?
                // Actually, let's focus on the URL validation bypass + keywords.
                
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
