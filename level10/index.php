<?php 
include '../headers.php'; 
setcookie("flag", "flag{protocol_obfuscation_level10}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 10 - Protocol Bypass</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 10: Protocol Bypass">Level 10: Protocol Bypass</h1>
        <p>Your task: The system tries to block the javascript: protocol.</p>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Add Link</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                
                // Vulnerability: Blacklist filter for "javascript"
                // The filter is case-insensitive (str_ireplace) but only removes the exact word "javascript".
                
                // Attack Vector: Protocol Obfuscation
                // Browsers ignore tabs, newlines, and spaces inside the protocol scheme.
                // e.g. java	script:alert(1) works in some older browsers or specific contexts.
                // BUT modern browsers are stricter.
                // A better approach for modern XSS labs: HTML Entity Encoding (already covered in Level 8).
                
                // Let's try: "javascript" keyword removal.
                // Bypass: "javascripjavascriptt" (Double Write - covered in Level 7)
                // Bypass: "java script" (Space/Tab injection - covered here)
                
                // To make this level unique from Level 8 (Encoding) and Level 7 (Double Write):
                // We will filter "javascript" specifically.
                // AND we will NOT filter entities.
                // BUT we want to focus on "Protocol Obfuscation".
                
                // Let's block "javascript" but allow characters like TAB or Newline if the browser parses them?
                // Actually, modern Chrome DOES NOT allow `java script:`.
                
                // Alternative Idea: Filter "javascript" but forget "vbscript" (IE only) or "data" (if context allows).
                // Or maybe the filter only checks the START of the string?
                
                // Let's go with: It filters "javascript" string ANYWHERE.
                // But we can use HTML entities because we are in an attribute.
                // Wait, Level 8 was exactly that.
                
                // New Idea based on prompt: "Restrictions on http/https".
                // Maybe it enforces that it DOES NOT start with javascript?
                // Bypass: ` javascript:alert(1)` (Space at start) - Modern browsers might strip this.
                // Bypass: `JavaScript:alert(1)` (Case sensitivity) -> If filter is case sensitive.
                
                // Let's implement a CASE SENSITIVE filter for this level.
                // The user said: "Browser protocol parsing is loose".
                
                // Implementation:
                // 1. Filter "javascript" (lowercase only).
                // 2. Filter "script" (lowercase only).
                // 3. Output to href.
                
                $str = str_replace("javascript", "", $str);
                $str = str_replace("script", "", $str);
                
                echo '<a href="' . $str . '">Your Link</a>';
            }
            ?>
        </div>
    </div>
</body>
</html>
