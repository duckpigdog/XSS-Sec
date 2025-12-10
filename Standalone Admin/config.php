<?php
declare(strict_types=1);
$APP_NAME = 'Standalone Admin';
$FIXED_CREDENTIALS = [
    'admin' => 'Admin@123',
    'analyst' => 'Analyst@123'
];
$FIXED_USERS = [
    ['id' => 1, 'username' => 'alice', 'email' => 'alice@example.com', 'role' => 'admin'],
    ['id' => 2, 'username' => 'bob', 'email' => 'bob@example.com', 'role' => 'editor'],
    ['id' => 3, 'username' => 'carol', 'email' => 'carol@example.com', 'role' => 'viewer']
];
