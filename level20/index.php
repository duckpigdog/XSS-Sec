<?php 
include '../headers.php'; 
setcookie("flag", "flag{f86af6e9-4e9f-4088-8755-f227388b84d1}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - TechCorp</title>
    <!-- Use jQuery 3.x but we simulate the vulnerability via manual code or older logic if needed -->
    <!-- The vulnerability is in how we use jQuery, not necessarily jQuery itself -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; color: #1c1e21; margin: 0; padding: 0; }
        .header { background: #1877f2; color: #fff; padding: 15px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; }
        .logo { font-weight: bold; font-size: 1.4em; margin-right: 30px; }
        .nav-items a { color: #fff; text-decoration: none; margin-right: 20px; font-weight: 500; opacity: 0.9; }
        .nav-items a:hover { opacity: 1; }
        
        .main-container { max-width: 700px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .page-title { margin-top: 0; color: #1c1e21; border-bottom: 1px solid #e5e5e5; padding-bottom: 20px; margin-bottom: 30px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #606770; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 16px; transition: border-color 0.2s; }
        .form-control:focus { border-color: #1877f2; outline: none; }
        
        .btn-submit { background-color: #1877f2; color: #fff; border: none; padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; width: 100%; }
        .btn-submit:hover { background-color: #166fe5; }
        
        .back-link { display: inline-block; margin-top: 20px; color: #1877f2; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
    <script>
        // Auto-redirect to include returnPath if missing
        // This simulates a real scenario where you arrive here from another page
        if (!window.location.search.includes('returnPath=')) {
            var newUrl = new URL(window.location.href);
            newUrl.searchParams.set('returnPath', '/');
            window.history.replaceState({}, '', newUrl);
            // Alternatively, trigger a reload:
            // window.location.href = newUrl.toString();
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="logo">TechCorp</div>
        <div class="nav-items">
            <a href="#">Products</a>
            <a href="#">Solutions</a>
            <a href="#">Support</a>
        </div>
    </div>

    <div class="main-container">
        <h2 class="page-title">Submit Feedback</h2>
        <p style="color: #606770; margin-bottom: 30px;">We value your feedback. Please let us know how we can improve our products.</p>
        
        <form id="feedbackForm">
            <div class="form-group">
                <label>Subject</label>
                <input type="text" class="form-control" placeholder="Feature Request / Bug Report">
            </div>
            
            <div class="form-group">
                <label>Message</label>
                <textarea class="form-control" rows="5" placeholder="Describe your experience..."></textarea>
            </div>
            
            <button type="submit" class="btn-submit">Submit Feedback</button>
        </form>
        
        <!-- Vulnerability Source: location.search (URL parameters) -->
        <!-- Vulnerability Sink: jQuery anchor href attribute -->
        
        <a id="backLink" class="back-link" href="../index.php">Back to Home</a>
        
        <script>
            $(document).ready(function() {
                // Get the 'returnPath' parameter from the URL
                var params = new URLSearchParams(window.location.search);
                var returnPath = params.get('returnPath');
                
                // If the auto-redirect script just ran, window.location.search might be updated in history but not DOM
                // But new URLSearchParams(window.location.search) reads current location.
                
                if (returnPath) {
                    // Vulnerable Code:
                    // Using jQuery's attr() to set href from untrusted source
                    // If returnPath is "javascript:alert(1)", it executes XSS.
                    $('#backLink').attr('href', returnPath);
                }
            });
            
            // Simple form handling (simulation)
            $('#feedbackForm').on('submit', function(e) {
                e.preventDefault();
                alert('Thank you for your feedback!');
            });
        </script>
    </div>
</body>
</html>
