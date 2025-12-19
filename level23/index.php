<?php
setcookie("flag", "flag{a1b2c3d4-e5f6-7890-1234-567890abcdef}", time() + 3600, "/", "", false, false);
include '../headers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 23 - Stored DOM XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        /* Inherit global styles, add specific ones for comments */
        .comments-section {
            background: rgba(0, 20, 40, 0.6);
            padding: 20px;
            border: 1px solid var(--neon-blue);
            margin-top: 30px;
        }
        .comment-item {
            border-bottom: 1px solid var(--neon-cyan);
            padding: 15px 0;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            color: var(--neon-pink);
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .comment-body {
            color: #eee;
        }
        textarea {
            width: 100%;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 23: Stored DOM XSS">Level 23: Stored DOM XSS</h1>
        
        <div class="message">
            <p>Welcome to the DevBlog. Leave your comments below.</p>
        </div>

        <div class="comments-section">
            <h3>Comments</h3>
            <div style="margin-bottom: 20px;">
                <input type="text" id="author" placeholder="Your Name" style="margin-bottom: 10px; width: 100%;">
                <textarea id="commentText" placeholder="Write a comment..."></textarea>
                <button onclick="postComment()">Post Comment</button>
                <button onclick="clearComments()" style="background: #ff3333; margin-left: 10px;">Clear All</button>
            </div>

            <div id="commentsList">
                <!-- Comments loaded here -->
            </div>
        </div>

        <div style="margin-top: 40px; border-top: 1px dashed var(--neon-cyan); padding-top: 20px;">
            <h3>Report to Admin</h3>
            <p>Found a vulnerability? The admin checks comments regularly.</p>
            <div style="margin-top: 10px; font-size: 12px; font-family: 'Share Tech Mono';">
                STATUS: <span id="bot-status" style="color: #666;">IDLE</span>
            </div>
        </div>
    </div>

    <script src="../assets/admin-bot.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', loadComments);

        // VULNERABLE FUNCTION
        function escapeHTML(html) {
            if (!html) return '';
            return html.replace('<', '&lt;').replace('>', '&gt;');
        }

        function loadComments() {
            fetch('comment.php')
                .then(response => response.json())
                .then(comments => {
                    const list = document.getElementById('commentsList');
                    list.innerHTML = ''; 
                    
                    comments.forEach(comment => {
                        const item = document.createElement('div');
                        item.className = 'comment-item';
                        
                        const safeAuthor = escapeHTML(comment.author);
                        const safeText = escapeHTML(comment.text);
                        
                        item.innerHTML = `
                            <div class="comment-header">
                                <span>${safeAuthor}</span>
                                <span>${comment.date}</span>
                            </div>
                            <div class="comment-body">${safeText}</div>
                        `;
                        list.appendChild(item);
                    });
                });
        }

        function postComment() {
            const author = document.getElementById('author').value;
            const text = document.getElementById('commentText').value;

            if (!author || !text) return;

            fetch('comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ author, text })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('author').value = '';
                    document.getElementById('commentText').value = '';
                    loadComments();
                }
            });
        }
        
        function clearComments() {
            if(confirm('Clear all comments?')) {
                fetch('comment.php?action=clear', { method: 'POST' })
                .then(() => {
                    loadComments();
                    alert('Cleared!');
                });
            }
        }
    </script>
</body>
</html>
