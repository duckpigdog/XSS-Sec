<?php 
include '../headers.php'; 
setcookie("flag", "flag{frontend_regex_bypass_level13}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 13 - Frontend Filter</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 13: Frontend Filter">Level 13: Frontend Filter</h1>
        <p>Your task: The backend is lazy and relies on JavaScript to filter XSS.</p>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div id="result" class="message"></div>

        <script>
            // Vulnerability: The filtering happens in the browser (Frontend)
            // It uses a Regex to check for malicious content.
            
            function processInput() {
                const urlParams = new URLSearchParams(window.location.search);
                const keyword = urlParams.get('keyword');
                
                if (keyword) {
                    // Filter: Block <script> tags and 'javascript:' protocol
                    const blacklist = /<script|javascript:/i;
                    
                    if (blacklist.test(keyword)) {
                        document.getElementById('result').innerText = "🚫 Malicious content detected!";
                        document.getElementById('result').style.color = "red";
                    } else {
                        // Sink: innerHTML
                        // If we pass the regex, we get into innerHTML
                        document.getElementById('result').innerHTML = "Results: " + keyword;
                    }
                }
            }
            
            processInput();
        </script>
    </div>
</body>
</html>
