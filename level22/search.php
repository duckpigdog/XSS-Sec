<?php
// Simulate a JSON API response
header('Content-Type: application/json');

$q = isset($_GET['q']) ? $_GET['q'] : '';

// VULNERABILITY:
// The server escapes double quotes to prevent breaking out of the JSON string value,
// BUT it fails to escape backslashes.
$safe_q = str_replace('"', '\"', $q);

// Mock results
$results_json = '[]';
if ($q && strlen($q) > 2) {
    // Only return mock results if query is meaningful and not just an attack payload (simple heuristic)
    // In a real app, this would query a database.
    $mock_results = [
        [
            "title" => "Tech Giant Unveils New AI Chip",
            "summary" => "The latest processor promises 50% faster neural network training speeds.",
            "date" => "2 hours ago",
            "category" => "Hardware"
        ],
        [
            "title" => "Cybersecurity Trends for 2025",
            "summary" => "Experts predict a rise in supply chain attacks and AI-driven phishing campaigns.",
            "date" => "5 hours ago",
            "category" => "Security"
        ],
        [
            "title" => "Global Markets React to Tech Sector Volatility",
            "summary" => "Investors are cautious as major tech stocks see significant fluctuations this week.",
            "date" => "1 day ago",
            "category" => "Business"
        ]
    ];
    
    // We can use json_encode for the results part as that's not where the vulnerability is
    // The vulnerability is in the manual concatenation of searchTerm
    $results_json = json_encode($mock_results);
}

// Construct JSON manually to ensure the vulnerability exists as intended in the searchTerm field
// (json_encode would safely escape backslashes too, fixing the vulnerability)
echo '{"results":' . $results_json . ',"searchTerm":"' . $safe_q . '"}';
?>
