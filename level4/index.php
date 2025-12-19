<?php 
include '../headers.php'; 
setcookie("flag", "flag{64145663-3bf8-49aa-a2ac-95a13ee84e96}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 4 - Attribute Breakout</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 4: Attribute Breakout">Level 4: Attribute Breakout</h1>
        <form method="GET" action="">
            <?php
                $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : 'Try to break me';
                // Vulnerability: No sanitization of quotes
                // The input is placed directly into the value attribute
            ?>
            <label>Search:</label>
            <input type="text" name="keyword" value="<?php echo $keyword; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                echo "Results for: " . htmlspecialchars($_GET['keyword']);
            }
            ?>
        </div>
    </div>
</body>
</html>

