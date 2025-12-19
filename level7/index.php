<?php 
include '../headers.php'; 
setcookie("flag", "flag{4fb8d6c8-7c1d-446c-8dd8-49a5f87d37e8}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 7 - Keyword Removal</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 7: Keyword Removal">Level 7: Keyword Removal</h1>
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                $str = strtolower($_GET['keyword']);
                
                // Vulnerability: Words are removed (replaced with empty string) ONCE.
                // This allows for "Double Write" bypass (e.g. scrscriptipt -> script)
                
                $bad_words = ['script', 'on', 'src', 'data', 'href'];
                foreach ($bad_words as $word) {
                    $str = str_replace($word, '', $str);
                }
                
                // Note: We are using str_replace (case-sensitive) but we converted input to lower case first?
                // Actually, converting to lower case makes the output lowercase, but the browser handles mixed case tags fine usually.
                // Wait, if I convert input to lowercase, the payload <SCRIPT> becomes <script> and gets removed.
                // But the browser needs valid tags. 
                // Let's stick to the classic "remove once" logic without forcing lowercase output, 
                // but checking case-insensitively for the removal.
                
                // Re-implementation for better educational value:
                $str = $_GET['keyword']; // Get original case
                $str = str_ireplace($bad_words, '', $str); // Case-insensitive replace with empty string
                
                echo "Result: <input value=\"" . $str . "\">";
            }
            ?>
        </div>
    </div>
</body>
</html>

