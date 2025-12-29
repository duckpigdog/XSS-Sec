<?php
include '../headers.php';
setcookie("flag", "flag{e90f848a-22ef-4597-a009-606f9e210cd6}", time() + 3600, "/", "", false, false);

function escape_for_template_literal($s) {
    return strtr($s, [
        '<' => '\\u003C',
        '>' => '\\u003E',
        '"' => '\\u0022',
        "'" => '\\u0027',
        '\\' => '\\u005C',
        '`' => '\\u0060',
    ]);
}

$q = isset($_GET['q']) ? $_GET['q'] : '';
$escaped = escape_for_template_literal($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 28 - Template Literal XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 28: Template Literal XSS">Level 28: Template Literal XSS</h1>
        <form method="GET" action="">
            <input type="text" name="q" placeholder="Enter payload here" value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
        </form>
        <div class="message">
            <div id="result"></div>
        </div>
    </div>
    <script>
        const preview = `Searching for: <?php echo $escaped; ?>`;
        document.getElementById('result').innerText = preview;
    </script>
</body>
</html>
