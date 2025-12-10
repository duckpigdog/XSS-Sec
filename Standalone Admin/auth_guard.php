<?php
require_once __DIR__ . '/bootstrap.php';
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header('Location: login.php');
    exit;
}
