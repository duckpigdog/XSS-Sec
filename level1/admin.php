<?php
// admin.php - Headless Admin Bot for Level 1
include '../headers.php';
// Only accepts POST requests.
// Invisible to the user (no UI output unless in debug mode, but we hide it via JS/CSS).

// Security Check: Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    die("Access Denied: Admin Bot only accepts dispatch orders.");
}

// Set the flag cookie (HttpOnly = false for XSS)
setcookie("flag", "flag{949703b0-facb-4492-b44a-093dcb0ad1b1}", time() + 3600, "/", "", false, false);

$target_url = isset($_POST['url']) ? $_POST['url'] : '';

if (empty($target_url)) {
    die("Error: No target URL provided.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>body{display:none;}</style>
</head>
<body>
    <!-- The Bot "Visits" the URL here -->
    <iframe src="<?php echo htmlspecialchars($target_url); ?>" width="0" height="0" frameborder="0"></iframe>
    <script>
        // Notify the parent window (the user's browser) that the bot has started visiting
        // This is just for UI feedback
        // Use json_encode to safely output the URL into JavaScript context
        // Ensure $target_url is treated as a string and handled correctly
        var targetUrl = <?php echo json_encode((string)$target_url); ?>;
        
        try {
            window.parent.postMessage({ type: 'bot_visit_start', url: targetUrl }, '*');
        } catch(e) {
            console.error('Bot Error:', e);
        }
        
        // Simulate "Reading" time
        setTimeout(() => {
            try {
                window.parent.postMessage({ type: 'bot_visit_end' }, '*');
            } catch(e) {}
        }, 2000);
    </script>
</body>
</html>
