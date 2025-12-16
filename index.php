<?php include 'headers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Labs</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1 data-text="XSS Vulnerability Labs">XSS Vulnerability Labs</h1>
        <p>Welcome to the XSS practice arena. Choose your level.</p>
        
        <ul class="level-list">
            <li><a href="level1/index.php">Level 1: Reflected XSS</a> - The basics.</li>
            <li><a href="level2/index.php">Level 2: DOM-based XSS</a> - Client-side manipulation.</li>
            <li><a href="level3/index.php">Level 3: Stored XSS</a> - Persistent payloads.</li>
        </ul>
        
        <div style="text-align: center; margin-top: 50px; font-size: 0.8em; opacity: 0.7;">
            <p>Created for educational purposes only.</p>
        </div>
    </div>
</body>
</html>