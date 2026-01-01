<?php
include '../headers.php';
$flag = 'flag{e68a3fb5-d813-42e3-9dc2-5a3f3ddd5ee2}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
$input = isset($_GET['content']) ? $_GET['content'] : '';
$render = $input;
for ($i = 0; $i < 2; $i++) {
    $d = urldecode($render);
    if ($d === $render) break;
    $render = $d;
}
$output = '';
if ($render !== '') {
    $pos = stripos($render, '</textarea>');
    if ($pos !== false) {
        $part1 = substr($render, 0, $pos + 11);
        $part2 = substr($render, $pos + 11);
        $output .= $part1;
        if (preg_match('/<img[^>]*\bonerror\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)[^>]*>/i', $part2, $m)) {
            $output .= $m[0];
            $rest2 = str_replace($m[0], '', $part2);
            $output .= htmlspecialchars($rest2, ENT_QUOTES);
        } else {
            $output .= htmlspecialchars($part2, ENT_QUOTES);
        }
    } else {
        $output .= htmlspecialchars($render, ENT_QUOTES);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 45 - RCDATA Textarea Breakout XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 45: RCDATA Textarea Breakout XSS">Level 45: RCDATA Textarea Breakout XSS</h1>
        <form method="GET" action="">
            <input type="text" name="content" placeholder="Enter payload here" value="<?php echo htmlspecialchars($input, ENT_QUOTES); ?>">
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear</button>
        </form>
        <div class="message">
            <div id="content"><?php echo $output; ?></div>
        </div>
    </div>
</body>
</html>
