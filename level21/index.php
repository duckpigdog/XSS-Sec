<?php 
include '../headers.php'; 
setcookie("flag", "flag{064b7412-4974-4053-ac90-8d5c3d1ba2a6}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Analytics - DataView</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; margin: 0; padding: 0; color: #111827; }
        .header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; }
        .logo { font-weight: 800; font-size: 1.25rem; color: #4f46e5; letter-spacing: -0.025em; }
        .search-bar { flex: 1; max-width: 500px; margin: 0 40px; }
        .search-input { width: 100%; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; outline: none; transition: border-color 0.15s; }
        .search-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        
        .main-content { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .results-header { margin-bottom: 20px; }
        .results-count { color: #6b7280; font-size: 0.9rem; }
        
        .result-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 15px; transition: box-shadow 0.2s; }
        .result-card:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .result-title { color: #1d4ed8; font-weight: 600; font-size: 1.1rem; text-decoration: none; display: block; margin-bottom: 5px; }
        .result-url { color: #059669; font-size: 0.85rem; margin-bottom: 8px; }
        .result-desc { color: #4b5563; font-size: 0.95rem; line-height: 1.5; }
        
        .no-results { text-align: center; padding: 60px 0; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">DataView Search</div>
        <div class="search-bar">
            <form action="" method="GET">
                <input type="text" name="q" class="search-input" placeholder="Search documentation..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            </form>
        </div>
        <div style="font-size: 0.9rem; font-weight: 500;">Admin Portal</div>
    </div>

    <div class="main-content">
        <?php
        $query = isset($_GET['q']) ? $_GET['q'] : '';
        
        if ($query) {
            echo '<div class="results-header">';
            echo '<h2>Results for "' . htmlspecialchars($query) . '"</h2>';
            echo '<div class="results-count">Found 0 results (0.14 seconds)</div>';
            echo '</div>';
            
            echo '<div class="no-results">';
            echo '<p>No documents found matching your query.</p>';
            echo '<p>Try different keywords or check your spelling.</p>';
            echo '</div>';
        } else {
            echo '<div class="no-results" style="padding-top: 100px;">';
            echo '<h3>Welcome to DataView Search</h3>';
            echo '<p>Enter a keyword to search the internal knowledge base.</p>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- Analytics Tracking Script -->
    <script>
        // We track search queries to improve our ranking algorithm.
        // The query is injected into the tracking object.
        
        var analyticsData = {
            sessionId: "sess_<?php echo uniqid(); ?>",
            timestamp: <?php echo time(); ?>,
            
            // Vulnerability: 
            // The query is reflected inside a JavaScript string.
            // HTML entities (<, >) ARE encoded by the backend (htmlspecialchars).
            // BUT single quotes (') are NOT encoded (default htmlspecialchars behavior before PHP 8.1 without ENT_QUOTES, 
            // or if explicitly set to ENT_NOQUOTES/ENT_COMPAT).
            
            // Scenario: The dev used htmlspecialchars($q) which protects HTML context,
            // but forgot that inside JS string, single quote ' is the dangerous character.
            
            searchTerm: '<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_COMPAT) : ''; ?>',
            
            category: 'general'
        };

        // Simulated tracking pixel
        var img = new Image();
        img.src = '/log?q=' + encodeURIComponent(analyticsData.searchTerm);
        
        console.log("Analytics sent for:", analyticsData.searchTerm);
    </script>
</body>
</html>
