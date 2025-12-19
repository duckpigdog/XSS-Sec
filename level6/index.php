<?php 
include '../headers.php'; 
setcookie("flag", "flag{1b6a3fb2-1c77-407f-9695-450121751f7e}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 6 - Quote Filtering</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 6: Quote Filtering">Level 6: Quote Filtering</h1>
        <form method="GET" action="">
            <?php
                $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
                // Vulnerability: Only double quotes are filtered
                // The input is placed into an attribute defined with SINGLE quotes
                $keyword_safe = str_replace('"', '&quot;', $keyword);
            ?>
            <label>Search:</label>
            <!-- Note the use of single quotes for the value attribute -->
            <input type="text" name="keyword" value='<?php echo $keyword_safe; ?>'>
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                // For display purposes, we might just show it. 
                // But the XSS vector is in the input field above.
                echo "Searched for: " . htmlspecialchars($keyword);
            }
            ?>
        </div>
    </div>
</body>
</html>

