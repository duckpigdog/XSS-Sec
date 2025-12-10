<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = $FIXED_USERS;
}
