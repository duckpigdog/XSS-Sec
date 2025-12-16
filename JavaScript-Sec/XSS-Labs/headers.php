<?php
// Global Security Headers for XSS Lab
// We deliberately weaken security to facilitate XSS testing

// Allow any origin to access this lab (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Disable XSS Protection (Browser built-in)
header("X-XSS-Protection: 0");

// Allow framing (for admin bot)
header("X-Frame-Options: ALLOWALL");

// Referrer Policy: Send full URL (useful for XSS platforms to know the victim URL)
header("Referrer-Policy: unsafe-url");

// Upgrade Insecure Requests: Forces browser to use HTTPS for subresources
// This solves the CORS redirect issue when loading external HTTPS scripts from HTTP localhost
// e.g. //xs.pe/6HW -> https://xs.pe/6HW
header("Content-Security-Policy: upgrade-insecure-requests");
?>