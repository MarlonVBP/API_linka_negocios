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
    $connection->beginTransaction();

    $queryGetParent = "SELECT postagem_id FROM comentarios_postagens WHERE id = :id";
    $stmtGet = $connection->prepare($queryGetParent);
    $stmtGet->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtGet->execute();

    $row = $stmtGet->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $connection->rollBack();
        echo json_encode(['success' => 0, 'message' => 'Comentário não encontrado.']);
        exit;
    }

    $postagem_id = $row['postagem_id'];

    $delete_query = "DELETE FROM comentarios_postagens WHERE id = :id";
    $delete_stmt = $connection->prepare($delete_query);
    $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $delete_stmt->execute();

    $update_query = "UPDATE postagens SET comentarios = GREATEST(0, comentarios - 1) WHERE id = :postagem_id";
    $update_stmt = $connection->prepare($update_query);
    $update_stmt->bindValue(':postagem_id', $postagem_id, PDO::PARAM_INT);
    $update_stmt->execute();

    $connection->commit();

    http_response_code(200);
    echo json_encode(['success' => 1, 'message' => 'Comentário excluído e contador atualizado.']);
} catch (PDOException $e) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => 0, 'message' => 'Erro: ' . $e->getMessage()]);
}
