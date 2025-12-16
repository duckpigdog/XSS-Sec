<?php 
include '../headers.php'; 
setcookie("flag", "flag{ng_bind_html_bypass_level15}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 15 - Angular/Framework XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <!-- Load AngularJS (simulated or real CDN) -->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
</head>
<body ng-app="">
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 15: Framework Injection">Level 15: Framework Injection</h1>
        <p>Your task: We are using a modern framework (AngularJS) to sanitize HTML. Or are we?</p>
        <p>The input is displayed using <code>ng-bind-html</code> (unsafe mode simulated) or template injection.</p>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Vulnerability: Outputting directly into an AngularJS template context
                // If user inputs {{ 7 * 7 }}, Angular evaluates it.
                // This is Client-Side Template Injection (CSTI).
                
                // Also, "HTML -> JS -> HTML" logic:
                // The server outputs HTML.
                // Angular (JS) reads it as a template.
                // Angular renders it back to HTML.
                
                // We do htmlspecialchars to prevent "traditional" XSS (<script>)
                // BUT we do NOT escape Angular template syntax {{ }}.
                
                $safe_html = htmlspecialchars($str);
                
                echo "<div id='result'>Hello, $safe_html</div>";
            }
            ?>
        </div>
        
        <div style="margin-top:20px; font-size:0.8em; color:#666;">
            Angular Version: 1.8.2 <br>
            Try to trigger an alert using Template Injection. <br>
            Example: <code>{{constructor.constructor('alert(1)')()}}</code>
        </div>
    </div>
</body>
</html>
