<?php
setcookie("flag", "flag{a1b2c3d4-e5f6-7890-1234-567890abcdef}", time() + 3600, "/", "", false, false);
include '../headers.php'; 

// Get current URL parts to construct canonical link
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$path = strtok($_SERVER["REQUEST_URI"], '?');

// VULNERABILITY: 
// The developer tries to create a canonical link tag to avoid duplicate content issues.
// However, they directly append the query string (GET parameters) to the href attribute.
// They use htmlspecialchars to escape the URL, BUT they use single quotes for the attribute value.
// And by default htmlspecialchars DOES NOT escape single quotes unless ENT_QUOTES is specified.
// This allows an attacker to break out of the href attribute using a single quote.

// Construct the full URL including query parameters
$raw_request_uri = $_SERVER['REQUEST_URI'];
$decoded_request_uri = urldecode($raw_request_uri);
$current_url = $protocol . "://" . $host . $decoded_request_uri;

// Vulnerable sanitation: Default behavior does not escape single quotes '
$safe_url = htmlspecialchars($current_url); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 26 - Canonical Link XSS</title>
    
    <!-- VULNERABLE CANONICAL TAG -->
    <!-- The href attribute is enclosed in single quotes -->
    <link rel="canonical" href='<?php echo $safe_url; ?>'>
    
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 26: Canonical Link XSS">Level 26: Canonical Link XSS</h1>
        
        <div class="message">
            <p>This page uses a canonical link tag to optimize SEO.</p>
            <p>Try to exploit it!</p>
            <p style="color: #666; font-size: 0.8em;">Hint: View Page Source to see the &lt;link&gt; tag.</p>
        </div>
    </div>
    <script>
        (function(){
            var link = document.querySelector('link[rel="canonical"]');
            if (link && link.getAttribute('onclick')) {
                setTimeout(function(){ link.click(); }, 50);
            }
        })();
    </script>
</body>
</html>
