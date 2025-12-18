<?php
include '../../cors.php';
include '../../conn.php';
include 'jwtEhValido.php';

try {
    if (!isset($_COOKIE['auth_token']) || empty($_COOKIE['auth_token'])) {
        echo json_encode([
            'success' => 0,
            'message' => 'Token não encontrado (Cookie ausente).'
        ]);
        exit;
    }
    $tokenString = $_COOKIE['auth_token'];

    $tokenValido = jwt_eh_valido($tokenString);

    if ($tokenValido) {
        echo json_encode([
            'success' => 1,
            'message' => 'Acesso permitido.'
        ]);
        exit;
    }

    setcookie('auth_token', '', ['expires' => time() - 3600, 'path' => '/']);

    echo json_encode([
        'success' => 0,
        'message' => 'Token inválido ou expirado.'
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro interno ao validar token.'
    ]);
    exit;
}
