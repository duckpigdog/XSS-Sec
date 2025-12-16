<?php
include '../headers.php'; 
setcookie("flag", "flag{upload_mime_sniff_level20}", time() + 3600, "/", "", false, false);

// Upload Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file = $_FILES['file'];
    $filename = basename($file['name']);
    $target_file = $target_dir . $filename;
    
    // Security Check: Blacklist Extension
    // We block .php, .php5, .phtml to prevent RCE
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $blacklist = ['php', 'php5', 'phtml', 'exe', 'sh'];
    
    if (in_array($ext, $blacklist)) {
        $error = "Extension blocked!";
    } else {
        // MIME Type Check (Weak)
        // We allow images and text, but maybe block 'text/html'?
        // Let's implement a filter that blocks 'text/html' MIME type but allows others.
        
        if ($file['type'] === 'text/html') {
            $error = "HTML files are not allowed!";
        } else {
            // Move file
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $success = "File uploaded successfully! <a href='$target_file' target='_blank'>View File</a>";
            } else {
                $error = "Upload failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 20 - File Upload XSS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="../index.php">Home</a>
        </div>
        <h1 data-text="Level 20: File Upload XSS">Level 20: File Upload XSS</h1>
        <p>Your task: Upload a file that executes JavaScript when viewed.</p>
        <p>Rules: No <code>.php</code> (we don't want RCE). No <code>text/html</code> MIME type.</p>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <label>Select File:</label>
            <input type="file" name="file" required>
            <button type="submit">Upload</button>
        </form>
        
        <div class="message">
            <?php
            if (isset($error)) echo "<span style='color:red'>$error</span>";
            if (isset($success)) echo "<span style='color:green'>$success</span>";
            ?>
        </div>
        
        <div style="margin-top:20px; font-size:0.8em; color:#666;">
            Hint: Browsers sometimes guess the content type (MIME Sniffing).
        </div>
    </div>
</body>
</html>
