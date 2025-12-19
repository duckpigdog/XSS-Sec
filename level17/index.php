<?php 
// 1. JSONP Endpoint Simulation
// If 'callback' parameter is present, we act as an API returning JSONP.
// This is commonly found in the same domain or subdomains.
if (isset($_GET['callback'])) {
    header('Content-Type: application/javascript');
    $cb = $_GET['callback'];
    // Simple check to prevent basic XSS in callback (optional, but let's allow it for the lab)
    // In real world, callbacks should be validated (e.g. ^[a-zA-Z0-9_]+$).
    // Here we leave it open to demonstrate the vulnerability.
    echo $cb . '({"status": "ok", "time": "' . date('Y-m-d H:i:s') . '"});';
    exit;
}

// 2. CSP Header
// Strict CSP: Only allow scripts from 'self'. No inline scripts, no 'unsafe-inline'.
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");

include '../headers.php'; 
setcookie("flag", "flag{8d2a43e4-7157-4349-8cbe-192c3c3a08a1}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 17 - CSP Bypass</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 17: CSP Bypass">Level 17: CSP Bypass</h1>
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Vulnerability: Reflected XSS
                // We echo the input directly.
                // Normally, you would use <script>alert(1)</script>.
                // BUT CSP blocks it because it's an inline script.
                // The browser will refuse to execute it.
                // Check your browser console for CSP violation errors!
                
                echo "Results for: " . $str;
            }
            ?>
        </div>
        
        <div style="margin-top:20px; font-size:0.8em; color:#666;">
            Debug: API available at <a href="?callback=test">?callback=test</a>
        </div>
    </div>
</body>
</html>

