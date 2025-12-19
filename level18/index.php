<?php 
include '../headers.php'; 
setcookie("flag", "flag{1a7b5cd7-f083-40c2-886c-3300609e0bf8}", time() + 3600, "/", "", false, false);

$db_file = 'comments.json';
if (!file_exists($db_file)) {
    file_put_contents($db_file, json_encode([]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = $_POST['author'] ?? 'Anonymous';
    $website = $_POST['website'] ?? '';
    $comment = $_POST['comment'] ?? '';
    
    $website_safe = htmlspecialchars($website, ENT_QUOTES); 
    $author_safe = htmlspecialchars($author);
    $comment_safe = htmlspecialchars($comment);
    
    $new_comment = [
        'author' => $author_safe,
        'website' => $website_safe, 
        'comment' => $comment_safe,
        'time' => date('Y-m-d H:i:s')
    ];
    
    $current_data = json_decode(file_get_contents($db_file), true);
    $current_data[] = $new_comment;
    file_put_contents($db_file, json_encode($current_data));
    
    header("Location: index.php");
    exit;
}

if (isset($_GET['clear'])) {
    file_put_contents($db_file, json_encode([]));
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechBlog - Web Security Insights</title>
    <!-- Use standard fonts and clean layout instead of cyberpunk theme -->
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 0; }
        .header { background: #333; color: #fff; padding: 20px; text-align: center; }
        .container { max-width: 800px; margin: 20px auto; background: #fff; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .nav-link { color: #fff; text-decoration: none; margin-right: 15px; }
        .post-title { color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .post-meta { color: #7f8c8d; font-size: 0.9em; margin-bottom: 20px; }
        .post-content { line-height: 1.6; }
        
        .comment-section { margin-top: 50px; border-top: 1px solid #eee; padding-top: 20px; }
        .comment { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f9f9f9; }
        .comment-header { display: flex; align-items: center; margin-bottom: 10px; }
        .avatar { width: 40px; height: 40px; background: #ddd; border-radius: 50%; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #666; }
        .author-name { font-weight: bold; margin-right: 10px; }
        .author-name a { color: #2980b9; text-decoration: none; }
        .author-name a:hover { text-decoration: underline; }
        .comment-date { color: #aaa; font-size: 0.85em; }
        
        .comment-form { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn { background: #2980b9; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #3498db; }
        .reset-link { color: #e74c3c; font-size: 0.8em; text-decoration: none; float: right; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>TechBlog</h1>
        <p>Insights on Web Security & Development</p>
    </div>
    
    <div class="container">
        <div style="margin-bottom: 20px;">
            <a href="../index.php" style="color: #666; text-decoration: none;">&larr; Back to Labs</a>
        </div>
        
        <article>
            <h1 class="post-title">Understanding Stored XSS Vulnerabilities</h1>
            <div class="post-meta">Posted on December 29, 2023 by Admin</div>
            <div class="post-content">
                <p>Cross-Site Scripting (XSS) remains one of the most prevalent web vulnerabilities. In this post, we'll discuss how improper handling of user input in comments can lead to Stored XSS.</p>
                <p>Always remember to sanitize your inputs! But also remember, sanitizing HTML entities might not be enough if you allow unsafe protocols.</p>
                <p>Feel free to leave a comment below and share your own blog!</p>
            </div>
        </article>

        <div class="comment-section">
            <h3>Comments</h3>
            
            <?php
            $comments = json_decode(file_get_contents($db_file), true);
            if (!empty($comments)) {
                foreach ($comments as $c) {
                    $initial = strtoupper(substr($c['author'], 0, 1));
                    echo '<div class="comment">';
                    echo '<div class="comment-header">';
                    echo '<div class="avatar">' . $initial . '</div>';
                    echo '<div>';
                    
                    if (!empty($c['website'])) {
                        echo '<span class="author-name"><a href="' . $c['website'] . '">' . $c['author'] . '</a></span>';
                    } else {
                        echo '<span class="author-name">' . $c['author'] . '</span>';
                    }
                    
                    echo '<div class="comment-date">' . $c['time'] . '</div>';
                    echo '</div>'; // end div
                    echo '</div>'; // end header
                    echo '<div class="comment-body">' . $c['comment'] . '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p style="color:#777; font-style:italic;">No comments yet. Be the first to share your thoughts!</p>';
            }
            ?>
            
            <div class="comment-form">
                <h3>Leave a Reply</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="author" class="form-control" required placeholder="John Doe">
                    </div>
                    
                    <div class="form-group">
                        <label>Website (Optional)</label>
                        <input type="text" name="website" class="form-control" placeholder="http://yoursite.com">
                        <small style="color: #666;">Your name will link to this URL.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea name="comment" class="form-control" rows="4" required placeholder="Join the discussion..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Post Comment</button>
                    <a href="?clear=1" class="reset-link">Reset Comments</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
