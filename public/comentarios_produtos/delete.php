<?php
include '../../cors.php';
include '../../conn.php';
require_once '../../admin/login/jwtEhValido.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => 0, 'message' => 'Método não permitido.']);
    exit;
}


$token = $_COOKIE['auth_token'] ?? null;
if (!$token || !jwt_eh_valido($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}


$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id === null) {
    echo json_encode(['success' => 0, 'message' => 'ID não fornecido.']);
    exit;
}

try {

    $delete_query = "DELETE FROM comentarios_produtos WHERE id = :id";
    $delete_stmt = $connection->prepare($delete_query);
    $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($delete_stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => 1,
            'message' => 'Comentário excluído com sucesso.'
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Falha na exclusão do comentário.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
