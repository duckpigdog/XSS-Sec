<?php include '../headers.php'; ?>
<?php
$flag = 'flag{7b0967db-5513-4b9e-890c-ce6052e2daf5}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
$content = isset($_GET['content']) ? $_GET['content'] : '';
$safe = $content;
$safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
$safe = preg_replace('/window\s*\.\s*alert/i', 'window.blocked', $safe);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 41 - Fragment String Activation</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 41: Fragment Eval/Window Bypass">Level 41: Fragment Eval/Window Bypass</h1>
        <form method="GET" action="">
            <input type="text" name="content" placeholder='Enter payload here'>
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        <div class="message" style="margin-top:16px;">
            <?php
            if ($content !== '') {
                echo $safe;
            }
            ?>
        </div>
    </div>
</body>
</html>
