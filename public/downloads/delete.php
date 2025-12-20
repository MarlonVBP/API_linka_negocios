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
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas DELETE e aceito.',
    ]);
    exit;
}

$token = $_COOKIE['auth_token'] ?? null;

if (!$token || !jwt_eh_valido($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Sessão expirada ou inválida.']);
    exit;
}

$tokenParts = explode('.', $token);
$payload = json_decode(base64url_decode($tokenParts[1]));
if (!isset($payload->ID_USER)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token inválido.']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id === null) {
    echo json_encode([
        'success' => 0,
        'message' => 'ID do registro não fornecido.'
    ]);
    exit;
}

try {
    $delete_query = "DELETE FROM ProdutoDivulgacao WHERE id = :id";
    $delete_stmt = $connection->prepare($delete_query);
    $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($delete_stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => 1,
            'message' => 'Serviço excluído com sucesso.'
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Falha na exclusão do serviço.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
