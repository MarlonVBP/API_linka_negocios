<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas DELETE é aceito.',
    ]);
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
    $delete_query = "DELETE FROM casos_sucesso WHERE id = :id";
    $delete_stmt = $connection->prepare($delete_query);
    $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($delete_stmt->execute()) {
        http_response_code(200); 
        echo json_encode([
            'success' => 1,
            'message' => 'Caso de sucesso excluído com sucesso.'
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Falha na exclusão do caso de sucesso.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
?>
