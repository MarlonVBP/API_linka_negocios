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
$nome = isset($data->nome) ? htmlspecialchars(trim($data->nome)) : null;

if ($id === null || empty($nome)) {
    echo json_encode([
        'success' => 0,
        'message' => 'ID e Nome são obrigatórios.'
    ]);
    exit;
}

try {
    $query = "UPDATE tags SET nome = :nome WHERE id = :id";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => 1,
            'message' => 'Tag atualizada com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Falha ao atualizar tag'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
