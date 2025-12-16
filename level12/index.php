<?php 
include '../headers.php'; 
setcookie("flag", "flag{location_hash_xss_level12}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 12 - DOM XSS via Hash</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 12: DOM XSS via Hash">Level 12: DOM XSS via Hash</h1>
        <p>Your task: The backend code is perfectly safe. But what about the frontend?</p>
        <p>Hint: Look at the URL hash (#).</p>
        
        <!-- No form submission needed for this level -->
        <div style="margin-bottom: 20px;">
             <button onclick="window.location.hash=''; window.location.reload();" style="background: #ff3333;">Clear Hash</button>
        </div>
        
        <div id="message-container" class="message">
            Waiting for input...
        </div>

        <script>
            // Vulnerability: Client-side logic reads location.hash and writes it to innerHTML
            // This bypasses any server-side WAF because the fragment identifier (#) is NOT sent to the server.
            
            function checkHash() {
                var hash = window.location.hash;
                if (hash) {
                    // Remove the '#' character
                    var content = decodeURIComponent(hash.substring(1));
                    
                    // Dangerous Sink: innerHTML
                    // Note: As seen in Level 2, modern browsers don't execute <script> added via innerHTML.
                    // BUT they DO execute <img onerror=...> and similar vectors.
                    
                    document.getElementById('message-container').innerHTML = "Welcome back, " + content;
                }
            }
            
            // Run on load and hash change
            window.addEventListener('load', checkHash);
            window.addEventListener('hashchange', checkHash);
        </script>
    </div>
</body>
</html>
