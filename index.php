<?php include 'headers.php'; ?>
<?php
// Define all levels
$levels = [
    1 => ['name' => 'Level 1: Reflected XSS', 'desc' => 'The basics.'],
    2 => ['name' => 'Level 2: DOM-based XSS', 'desc' => 'Client-side manipulation.'],
    3 => ['name' => 'Level 3: Stored XSS', 'desc' => 'Persistent payloads.'],
    4 => ['name' => 'Level 4: Attribute Breakout', 'desc' => 'Escape the attribute.'],
    5 => ['name' => 'Level 5: Filter Bypass', 'desc' => 'No &lt;script&gt; allowed.'],
    6 => ['name' => 'Level 6: Quote Filtering', 'desc' => 'Break out of single quotes.'],
    7 => ['name' => 'Level 7: Keyword Removal', 'desc' => 'Double write bypass.'],
    8 => ['name' => 'Level 8: Encoding Bypass', 'desc' => 'HTML entities are your friend.'],
    9 => ['name' => 'Level 9: URL Validation', 'desc' => 'Must contain http://'],
    10 => ['name' => 'Level 10: Protocol Bypass', 'desc' => 'Case sensitivity matters.'],
    11 => ['name' => 'Level 11: JS Context', 'desc' => 'Break out of JS string.'],
    12 => ['name' => 'Level 12: DOM XSS via Hash', 'desc' => 'The server sees nothing.'],
    13 => ['name' => 'Level 13: Frontend Filter', 'desc' => 'Bypass the regex.'],
    14 => ['name' => 'Level 14: Double Encoding', 'desc' => 'Double the trouble.'],
    15 => ['name' => 'Level 15: Framework Injection', 'desc' => 'AngularJS Template Injection.'],
    16 => ['name' => 'Level 16: PostMessage XSS', 'desc' => 'Talk to the parent.'],
    17 => ['name' => 'Level 17: CSP Bypass', 'desc' => 'Strict CSP? Find a gadget.'],
    18 => ['name' => 'Level 18: Anchor Href XSS', 'desc' => 'Stored XSS in href.'],
    19 => ['name' => 'Level 19: DOM XSS in Select', 'desc' => 'Break out of select.'],
    20 => ['name' => 'Level 20: jQuery Anchor XSS', 'desc' => 'DOM XSS in jQuery attr().'],
    21 => ['name' => 'Level 21: JS String Reflection', 'desc' => 'Reflected XSS in JS string.'],
    22 => ['name' => 'Level 22: Reflected DOM XSS', 'desc' => 'Server reflection + Client sink.'],
    23 => ['name' => 'Level 23: Stored DOM XSS', 'desc' => 'Replace only once.'],
    24 => ['name' => 'Level 24: WAF Bypass (Tags/Attrs)', 'desc' => 'Reflected XSS with strict WAF.'],
    25 => ['name' => 'Level 25: SVG Animate XSS', 'desc' => 'SVG-specific vector bypass.'],
    26 => ['name' => 'Level 26: Canonical Link XSS', 'desc' => 'Escaping single quotes issue.'],
    27 => ['name' => 'Level 27: Stored XSS in onclick', 'desc' => 'Entities vs escaping pitfall.'],
    28 => ['name' => 'Level 28: Template Literal XSS', 'desc' => 'Reflected into JS template string.'],
    29 => ['name' => 'Level 29: Cookie Exfiltration', 'desc' => 'Stored XSS steals session cookie.'],
    30 => ['name' => 'Level 30: Angular Sandbox Escape', 'desc' => 'No strings, escape Angular sandbox.'],
    31 => ['name' => 'Level 31: AngularJS CSP Escape', 'desc' => 'Bypass CSP and escape Angular sandbox.'],
];

// Pagination Logic
$items_per_page = 5;
$total_levels = count($levels);
$total_pages = ceil($total_levels / $items_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages) $current_page = $total_pages;

$start_index = ($current_page - 1) * $items_per_page + 1;
$end_index = min($start_index + $items_per_page - 1, $total_levels);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Labs</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 10px;
        }
        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            background-color: rgba(0, 243, 255, 0.1);
            color: var(--neon-cyan);
            border: 1px solid var(--neon-cyan);
            transition: all 0.3s ease;
        }
        .pagination a.active {
            background-color: var(--neon-cyan);
            color: #0d0d0d;
            box-shadow: 0 0 10px var(--neon-cyan);
        }
        .pagination a:hover:not(.active) {
            background-color: rgba(0, 243, 255, 0.3);
            box-shadow: 0 0 5px var(--neon-cyan);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 data-text="XSS Vulnerability Labs">XSS Vulnerability Labs</h1>
        <p>Welcome to the XSS practice arena. Choose your level.</p>
        
        <ul class="level-list">
            <?php 
            for ($i = $start_index; $i <= $end_index; $i++) {
                if (isset($levels[$i])) {
                    $level = $levels[$i];
                    echo "<li><a href=\"level{$i}/index.php\">{$level['name']}</a> - {$level['desc']}</li>";
                }
            }
            ?>
        </ul>
        
        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <a href="?page=<?php echo $p; ?>" class="<?php echo $p === $current_page ? 'active' : ''; ?>">
                    <?php echo $p; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 50px; font-size: 0.8em; opacity: 0.7;">
            <p>Created for educational purposes only.</p>
        </div>
    </div>
</body>
</html>
