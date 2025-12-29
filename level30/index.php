<?php
include '../headers.php';
setcookie("flag", "flag{da54ec3b-12a6-4037-9625-6cdc64f1d056}", time() + 3600, "/", "", false, false);
$search = isset($_GET['search']) ? $_GET['search'] : '';
$qs = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
if ($qs !== '') {
    $parts = explode('&', $qs);
    if (count($parts) > 1) {
        $expr = urldecode(implode('&', array_slice($parts, 1)));
    } else {
        $expr = $search !== '' ? $search : '1';
    }
} else {
    $expr = '1';
}
$expr = preg_replace('/fromCharCode\\s*\\(([^)]*)\\)\\s*=\\s*1/', 'fromCharCode($1)', $expr);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 30 - Angular Sandbox Escape</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.4/angular.min.js"></script>
    </head>
<body ng-app>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 30: Angular Sandbox Escape">Level 30: Angular Sandbox Escape</h1>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Enter payload here" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        <div class="message">
            <div>{{ <?php echo $expr; ?> }}</div>
            <div ng-init="<?php echo $expr; ?>"></div>
        </div>
    </div>
</body>
</html>
