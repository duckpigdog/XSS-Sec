<?php 
include '../headers.php'; 
setcookie("flag", "flag{064b7412-4974-4053-ac90-8d5c3d1ba2a6}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 21 - JS String Reflection</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 21: JS String Reflection">Level 21: JS String Reflection</h1>
        <form method="GET" action="">
            <input type="text" name="q" placeholder="Enter payload here" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        
        <div class="message">
            <?php
            $query = isset($_GET['q']) ? $_GET['q'] : '';
            if ($query) {
                echo '<h2>Results for: ' . htmlspecialchars($query) . '</h2>';
                echo '<p style="color:#999;">Found 0 results (0.14 seconds)</p>';
            } else {
                echo '<p>Enter a keyword to search the internal knowledge base.</p>';
            }
            ?>
        </div>

    <script>
        var analyticsData = {
            sessionId: "sess_<?php echo uniqid(); ?>",
            timestamp: <?php echo time(); ?>,
            searchTerm: '<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_COMPAT) : ''; ?>',
            category: 'general'
        };

        var img = new Image();
        img.src = '/log?q=' + encodeURIComponent(analyticsData.searchTerm);
        console.log("Analytics sent for:", analyticsData.searchTerm);
    </script>
</body>
</html>
