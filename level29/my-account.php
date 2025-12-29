<?php
include '../headers.php';
$isAdmin = isset($_COOKIE['session']) && $_COOKIE['session'] === 'admin_session_value';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <style>
        :root { --bg:#f8fafc; --card:#ffffff; --text:#1f2937; --muted:#6b7280; --border:#e5e7eb; --accent:#10b981; --danger:#ef4444; }
        body { margin:0; background: var(--bg); color: var(--text); font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
        .wrap { max-width: 760px; margin: 0 auto; padding: 24px; }
        .card { background: var(--card); border:1px solid var(--border); border-radius:10px; padding:20px; box-shadow: 0 6px 16px rgba(17,24,39,.06); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <?php if ($isAdmin): ?>
                <h2>Admin Account</h2>
                <p>Welcome, admin. Session verified.</p>
                <ul>
                    <li>Role: Administrator</li>
                    <li>Permissions: All</li>
                    <li>flag{64a307ae-44e4-4c23-9246-65c3c0174098}</li>
                </ul>
            <?php else: ?>
                <h2>Access Denied</h2>
                <p style="color:#ef4444;">You are not logged in as admin.</p>
                <p class="muted">Replace your session cookie with the captured admin cookie and reload.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
