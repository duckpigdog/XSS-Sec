<?php
include '../headers.php';
setcookie("flag", "flag{14f21ca0-a13e-416e-a7b1-f93cb69df341}", time() + 3600, "/", "", false, false);
$file = '../data/comments.json';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear Comments
    if (isset($_POST['clear'])) {
        if (file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }
        // Redirect to prevent resubmission
        header("Location: index.php");
        exit;
    }

    // Add Comment
    if (isset($_POST['comment'])) {
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Comments</h3>
                <form method="POST" action="" style="margin: 0;">
                    <button type="submit" name="clear" value="1" style="background: #ff3333; font-size: 0.8em; padding: 5px 10px;">Clear All</button>
                </form>
            </div>
            <?php foreach (array_reverse($comments) as $c): ?>
                <div class="comment">
                    <small><?php echo htmlspecialchars($c['time']); ?></small><br>
                    <!-- Vulnerability: No sanitization on output -->
                    <?php echo $c['text']; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
    </div>
</body>
</html>