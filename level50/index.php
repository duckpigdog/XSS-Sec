<?php
$flag = 'flag{a3c2f6d5-4b1e-43fa-9f6a-2c8c9e1f50ab}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
$input = isset($_GET['content']) ? $_GET['content'] : '';
$render = $input;
for ($i = 0; $i < 2; $i++) {
    $dec = rawurldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
$allowed = '';
$rest = $render;
if ($rest !== '') {
    if (preg_match('/<\s*xss[^>]*\bclass\s*=\s*(?:"progress-bar-animated"|\'progress-bar-animated\'|progress-bar-animated)[^>]*\bonanimationstart\s*=\s*alert\s*\(\s*(?:1|document\s*\.\s*cookie)\s*\)[^>]*>(?:\s*<\/\s*xss\s*>)?/i', $rest, $m)) {
        $allowed = $m[0];
        $rest = str_replace($m[0], '', $rest);
    }
}
$safe = $rest;
if ($safe !== '') {
    $safe = preg_replace('/<\s*script\b[\s\S]*?<\s*\/\s*script\s*>/i', '', $safe);
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus|onanimationend|onanimationstart)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed|a)\b/i', '<blocked', $safe);
    $safe = htmlspecialchars($safe, ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Level 50 - 实战站点（Bootstrap）</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 56px; }
        xss { display: inline-block; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">RealSite</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Docs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Support</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php">返回首页</a></li>
                </ul>
                <span class="navbar-text">Level 50</span>
            </div>
        </div>
    </nav>
    <main class="container">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mt-3">用户提交</h1>
                <form method="GET" action="" class="row g-3">
                    <div class="col-12">
                        <input type="text" name="content" class="form-control" placeholder="请输入内容" value="<?php echo htmlspecialchars($input, ENT_QUOTES); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">提交</button>
                        <a href="index.php" class="btn btn-danger ms-2">清空</a>
                    </div>
                </form>
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header">渲染结果</div>
                        <div class="card-body">
                            <?php echo $allowed; ?>
                            <?php echo $safe; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <h5 class="mt-3">系统状态</h5>
                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="64" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 64%">64%</div>
                </div>
                <p class="text-muted mt-2">站点运行中...</p>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
