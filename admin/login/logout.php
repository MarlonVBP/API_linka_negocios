<?php
include '../../cors.php';

setcookie('auth_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

echo json_encode(['success' => true]);
