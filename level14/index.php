<?php 
include '../headers.php'; 
setcookie("flag", "flag{js_variable_breakout_level14}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 14 - JS Variable Escape</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 14: JS Variable Escape">Level 14: JS Variable Escape</h1>
        <p>Your task: The input is put inside a JavaScript variable. Can you break out?</p>
        
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Enter payload here" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div id="result" class="message"></div>

        <script>
            // Vulnerability: PHP outputs input directly into a JS string variable.
            // It does NOT use proper JS encoding (json_encode).
            // It might escape double quotes, but maybe not single quotes, or maybe nothing.
            
            <?php
            if (isset($_GET['keyword'])) {
                $str = $_GET['keyword'];
                // Simulation: The dev thought addslashes() or escaping double quotes is enough.
                // Or maybe they just forgot.
                // Let's assume they did NOT escape anything for this level to demonstrate the concept clearly.
                // But to make it slightly realistic, let's say they used htmlspecialchars() which is for HTML context, NOT JS context.
                // htmlspecialchars() converts " to &quot; and ' to &#039; (if ENT_QUOTES set).
                // BUT if they didn't set ENT_QUOTES, single quotes might slip through.
                // OR simpler: They just echo it.
                
                // Let's go with: Direct echo (No sanitization), but inside a variable.
                // var search = "USER_INPUT";
                
                // If user inputs: "; alert(1); //
                // Result: var search = ""; alert(1); //";
                
                echo "var search = \"$str\";";
            } else {
                echo "var search = \"\";";
            }
            ?>
            
            if (search) {
                document.getElementById('result').innerText = "Searching for: " + search;
            }
        </script>
    </div>
</body>
</html>
