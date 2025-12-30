<?php
include '../headers.php';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
header("Content-Security-Policy: default-src 'self'; script-src 'self'; report-uri /csp-report?token=" . $token);
setcookie("flag", "flag{795f47f1-0007-41ac-bba5-cd4645bc1417}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 34 - CSP Bypass (report-uri token)</title>
    <link rel="stylesheet" href="../assets/style.css">
    </head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 34: CSP Bypass (Chrome)">Level 34: CSP Bypass (Chrome)</h1>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Enter payload here" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>">
            <input type="text" name="token" placeholder="Optional token for CSP injection" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>" style="margin-left: 6px;">
            <button type="submit">Search</button>
            <a href="index.php" style="display:inline-block;background:#ff3333;color:#fff;padding:6px 10px;margin-left:5px;text-decoration:none;">Clear</a>
        </form>
        <div class="message">
            <div id="content"><?php echo $search; ?></div>
        </div>
    </div>
</body>
</html>
