<?php
include 'headers.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Dummy authentication
    if ($username === 'admin' && $password === 'password') {
        // Redirect to admin or show success
        header("Location: index.php");
        exit;
    } else {
        $error = "ACCESS DENIED: Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }
        .login-header {
            margin-bottom: 30px;
            border-bottom: 2px solid var(--neon-pink);
            padding-bottom: 10px;
        }
        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            display: block;
            color: var(--neon-cyan);
            font-size: 0.8rem;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .input-icon {
            position: absolute;
            right: 15px;
            top: 42px;
            color: var(--neon-pink);
            opacity: 0.7;
        }
        .auth-status {
            min-height: 20px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: var(--neon-pink);
            text-shadow: 0 0 5px var(--neon-pink);
        }
        .cyber-decoration {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--neon-yellow);
            box-shadow: 0 0 10px var(--neon-yellow);
        }
        .d-top-left { top: -5px; left: -5px; }
        .d-top-right { top: -5px; right: -5px; }
        .d-bottom-left { bottom: -5px; left: -5px; }
        .d-bottom-right { bottom: -5px; right: -5px; }
    </style>
</head>
<body>
    <div class="container login-container">
        <!-- Decorative Corners -->
        <div class="cyber-decoration d-top-left"></div>
        <div class="cyber-decoration d-top-right"></div>
        <div class="cyber-decoration d-bottom-left"></div>
        <div class="cyber-decoration d-bottom-right"></div>

        <div class="login-header">
            <h1 data-text="SECURE ACCESS">SECURE ACCESS</h1>
            <p style="color: var(--neon-pink); font-size: 0.8rem; letter-spacing: 2px;">/// RESTRICTED AREA ///</p>
        </div>

        <form method="POST" action="">
            <div class="auth-status">
                <?php if ($error): ?>
                    [!] <?php echo htmlspecialchars($error); ?>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label for="username">Identity String</label>
                <input type="text" id="username" name="username" placeholder="Enter Username" required autocomplete="off">
                <span class="input-icon">>_</span>
            </div>

            <div class="input-group">
                <label for="password">Access Code</label>
                <input type="password" id="password" name="password" placeholder="Enter Password" required>
                <span class="input-icon">**</span>
            </div>

            <button type="submit">AUTHENTICATE</button>
        </form>
        
        <div style="margin-top: 30px; font-size: 0.7rem; opacity: 0.5;">
            SYSTEM VERSION 2.0.77 <br>
            UNAUTHORIZED ACCESS IS A CRIME
        </div>
    </div>

    <script>
        // Simple input focus effect
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.querySelector('label').style.color = 'var(--neon-yellow)';
            });
            input.addEventListener('blur', () => {
                input.parentElement.querySelector('label').style.color = 'var(--neon-cyan)';
            });
        });
    </script>
</body>
</html>
