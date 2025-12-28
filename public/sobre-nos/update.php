<?php
// Configurar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../../conn.php'; // Conectar ao banco de dados

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas POST e aceito.',
    ]);
    exit;
}

try {
    // Obter IDs dos comentários que foram visualizados
    $data = json_decode(file_get_contents('php://input'), true);
    $ids = isset($data) ? $data : [];

    if (empty($ids) || !is_array($ids)) {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum ID fornecido ou formato inválido.',
        ]);
        exit;
    }

    // Criar placeholders para a consulta
    $ids_placeholder = implode(',', array_fill(0, count($ids), '?'));
    $update = "UPDATE contato SET visualizado = false WHERE id IN ($ids_placeholder)";

    // Preparar e executar a consulta
    $stmt = $connection->prepare($update);
    $stmt->execute($ids);

    echo json_encode([
        'success' => 1,
        'message' => 'Contatos atualizados com sucesso.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
    ]);
} finally {
    $connection = null; // Fechar a conexão
}
?>