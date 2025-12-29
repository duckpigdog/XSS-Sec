<?php
include '../headers.php';
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://ajax.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; connect-src 'self' https://ajax.googleapis.com; object-src 'none'; base-uri 'self'");
setcookie("flag", "flag{632efed5-6060-4900-9e61-15d52ff086a4}", time() + 3600, "/", "", false, false);
$search = isset($_GET['search']) ? $_GET['search'] : '';
$render = urldecode($search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 31 - AngularJS CSP Escape</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.4/angular.min.js"></script>
    <script src="focus.js" defer></script>
    </head>
<body ng-app>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 31: AngularJS CSP Escape">Level 31: AngularJS CSP Escape</h1>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Enter payload here" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>">
            <button type="submit">Search</button>
            <button type="button" id="clear-btn" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        <div class="message">
            <div id="content"><?php echo $render; ?></div>
        </div>
    </div>
</body>
</html>
