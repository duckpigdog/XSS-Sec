<?php 
include '../headers.php'; 
setcookie("flag", "flag{b1e49ef8-e15c-4e1e-b82f-04660ed96a98}", time() + 3600, "/", "", false, false);
?>
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
        <div style="margin-bottom: 20px;">
            <button onclick="window.location.href='index.php'" style="background: #ff3333;">Clear Payload</button>
        </div>
        
        <div id="result" class="message"></div>

        <script>
            // Vulnerability: Reading from URL and writing to innerHTML
            const urlParams = new URLSearchParams(window.location.search);
            const keyword = urlParams.get('keyword');
            
            if (keyword) {
                // document.getElementById('result').innerHTML = "Search results for: " + keyword;
                
                // Fix: Manually execute scripts if they are injected via innerHTML
                // This simulates a more "naive" framework or jQuery-like behavior where scripts might be executed
                const container = document.getElementById('result');
                container.innerHTML = "Search results for: " + keyword;
                
                // Extract and execute scripts
                const scripts = container.getElementsByTagName('script');
                for (let i = 0; i < scripts.length; i++) {
                    const script = document.createElement('script');
                    if (scripts[i].src) {
                        script.src = scripts[i].src;
                    } else {
                        script.text = scripts[i].innerText;
                    }
                    document.body.appendChild(script);
                }
            } else {
                document.getElementById('result').innerText = "No keyword provided. Try adding ?keyword=test to the URL.";
            }
        </script>
        
    </div>
</body>
</html>
