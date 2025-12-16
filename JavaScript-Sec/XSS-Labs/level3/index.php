<?php
include '../headers.php';
$file = '../data/comments.json';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = $_POST['comment'];
    $data = [];
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (!is_array($data)) $data = [];
    }
    
    // Add new comment
    $data[] = [
        'time' => date('Y-m-d H:i:s'),
        'text' => $comment
    ];
    
    file_put_contents($file, json_encode($data));
}

// Read comments
$comments = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $comments = json_decode($json, true);
    if (!is_array($comments)) $comments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 3 - Stored XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 3: Stored XSS">Level 3: Stored XSS</h1>
        <p>Your task: Leave a message that executes JavaScript for anyone who visits this page.</p>
        
        <form method="POST" action="">
            <textarea name="comment" rows="4" placeholder="Leave a comment..."></textarea>
            <button type="submit">Post Comment</button>
        </form>
        
        <div class="comments-section">
            <h3>Comments</h3>
            <?php foreach (array_reverse($comments) as $c): ?>
                <div class="comment">
                    <small><?php echo htmlspecialchars($c['time']); ?></small><br>
                    <!-- Vulnerability: No sanitization on output -->
                    <?php echo $c['text']; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 40px; border-top: 1px dashed var(--neon-cyan); padding-top: 20px;">
            <h3>Report to Admin</h3>
            <p>Found a vulnerability? Notify the admin to check the page.</p>
            <div>
                <input type="text" id="payload-input" placeholder="Type anything to confirm..." value="Please check the comments.">
                <button onclick="AdminBot.send('level3', 'payload-input')">Send to Admin Bot</button>
            </div>
            <div style="margin-top: 10px; font-size: 12px; font-family: 'Share Tech Mono';">
                STATUS: <span id="bot-status" style="color: #666;">IDLE</span>
            </div>
        </div>
    </div>
    <script src="../assets/admin-bot.js"></script>
</body>
</html>