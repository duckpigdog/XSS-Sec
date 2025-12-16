<?php 
include '../headers.php'; 
setcookie("flag", "flag{post_message_iframe_level16}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 16 - PostMessage XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 16: PostMessage XSS">Level 16: PostMessage XSS</h1>
        <p>Your task: The system loads your input into an <code>iframe</code>.</p>
        <p>But <code>javascript:</code> is blocked. Can you talk to the parent?</p>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter URL (e.g. data:...)" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Load</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message" id="message-output">
            Waiting for messages from iframe...
        </div>

        <div style="margin-top: 20px; border: 1px solid #333; padding: 10px;">
            <h4>Iframe Container</h4>
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Vulnerability: 
                // 1. Input flows into iframe src.
                // 2. "javascript" protocol is blocked to prevent direct XSS in iframe context.
                // 3. User can use "data:" protocol.
                // 4. "data:" iframe has Opaque Origin (null), so cannot access parent DOM directly.
                // 5. BUT parent has a 'message' event listener that trusts data from iframes.
                
                $str = str_ireplace("javascript", "", $str);
                
                // We use htmlspecialchars to prevent breaking out of the src attribute.
                // This forces the user to exploit the URL content itself.
                echo '<iframe src="' . htmlspecialchars($str) . '" width="100%" height="100"></iframe>';
            } else {
                echo '<iframe src="about:blank" width="100%" height="100"></iframe>';
            }
            ?>
        </div>

        <script>
            // Vulnerable Event Listener
            // It trusts any message sent to it and writes it to innerHTML.
            window.addEventListener('message', function(e) {
                // In a real scenario, there should be an origin check here:
                // if (e.origin !== "https://trusted.com") return;
                
                console.log("Received message:", e.data);
                document.getElementById('message-output').innerHTML = "Received: " + e.data;
            });
        </script>
    </div>
</body>
</html>
