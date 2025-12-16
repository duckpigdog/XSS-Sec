<?php 
// Mode Check: If keyword is present, we serve the SVG image.
if (isset($_GET['keyword'])) {
    // 1. Set Content-Type to SVG
    header('Content-Type: image/svg+xml');
    // Disable XSS Protection header to ensure payload executes in modern browsers (though SVG XSS often bypasses this anyway)
    header("X-XSS-Protection: 0");
    
    $str = $_GET['keyword'];
    
    // Vulnerability: 
    // We are generating an SVG file.
    // SVG is XML.
    // The input is reflected inside a <text> tag.
    
    // Security Filters:
    // 1. Block 'script' tags
    $str = str_ireplace("script", "", $str);
    // 2. Block 'on' events (onclick, onload, etc.)
    $str = str_ireplace("on", "", $str);
    // 3. Block 'javascript' protocol
    $str = str_ireplace("javascript", "", $str);
    
    // Output SVG
    echo '<?xml version="1.0" standalone="no"?>';
    echo '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
    echo '<svg width="500" height="500" xmlns="http://www.w3.org/2000/svg">';
    echo '<rect width="100%" height="100%" fill="#1a1a1a" />';
    echo '<text x="50" y="50" font-family="Arial" font-size="24" fill="#00f3ff">';
    echo 'Welcome, ' . $str;
    echo '</text>';
    echo '</svg>';
    exit;
}

include '../headers.php'; 
setcookie("flag", "flag{svg_xml_entity_level19}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 19 - SVG XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 19: SVG XSS">Level 19: SVG XSS</h1>
        <p>Your task: We have a cool "Cyber Badge" generator.</p>
        <p>It outputs an <b>SVG Image</b>. Can you make it execute JavaScript?</p>
        <p>Note: SVG is XML-based. This might help bypass some filters.</p>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter your name" value="Hacker">
            <button type="submit">Generate Badge</button>
        </form>
        
        <div class="message">
            Clicking "Generate" will take you to the SVG file.
            <br>
            Use the browser's "Back" button to return.
        </div>
    </div>
</body>
</html>
