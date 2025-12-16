<?php 
include '../headers.php'; 
setcookie("flag", "flag{js_decode_bypass_level11}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 11 - URL Encoding</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 11: URL Encoding">Level 11: URL Encoding</h1>
        <p>Your task: The server encodes your input. Can you still execute it?</p>
        
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
                // The input is put into a JS variable string.
                // The server performs URL Encoding (urlencode) to "sanitize" it.
                // urlencode converts characters like <, >, ", ' to %3C, %3E, %22, %27 etc.
                // This usually prevents breaking out of the JS string.
                
                // HOWEVER, if the developer mistakenly decodes it on the client side 
                // OR if the input is used in a sink that decodes it (like setTimeout, or DOM writes), XSS is possible.
                
                // Let's simulate a scenario where the dev wants to "display" the search term in JS
                // but realizes URL encoding makes it look ugly, so they decode it in JS for display.
                
                $encoded_str = urlencode($str);
                
                echo "var searchTerm = '$encoded_str';";
                echo "\n            // Dev Note: Decode for display";
                echo "\n            var decodedTerm = decodeURIComponent(searchTerm);";
                echo "\n            document.write('Current Search: ' + decodedTerm);";
            }
            ?>
            </script>
        </div>
    </div>
</body>
</html>
