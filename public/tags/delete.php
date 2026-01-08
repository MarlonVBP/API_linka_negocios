<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas POST e permitido.',
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$id = isset($data->id) ? intval($data->id) : null;

if ($id === null) {
    echo json_encode([
        'success' => 0,
        'message' => 'ID é obrigatório.'
    ]);
    exit;
}

try {
    $query = "DELETE FROM tags WHERE id = :id";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => 1,
            'message' => 'Tag excluída com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Falha ao excluir tag'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
