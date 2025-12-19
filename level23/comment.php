<?php
header('Content-Type: application/json');

$file = 'comments.json';

// Ensure the file exists
if (!file_exists($file)) {
    file_put_contents($file, '[]');
}

// Handle Clear Action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    file_put_contents($file, '[]');
    echo json_encode(['status' => 'cleared']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if it's a clear request (alternative way)
    if (isset($_GET['action']) && $_GET['action'] === 'clear') {
        file_put_contents($file, '[]');
        echo json_encode(['status' => 'cleared']);
        exit;
    }
    
    if (isset($input['author']) && isset($input['text'])) {
        $current_data = json_decode(file_get_contents($file), true);
        if (!is_array($current_data)) {
            $current_data = [];
        }
        
        // Add new comment
        $new_comment = [
            'author' => $input['author'],
            'text' => $input['text'],
            'date' => date('Y-m-d H:i')
        ];
        
        $current_data[] = $new_comment;
        
        file_put_contents($file, json_encode($current_data));
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    }
} else {
    // GET request - return comments
    echo file_get_contents($file);
}
?>
