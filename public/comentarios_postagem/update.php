<?php
include '../../cors.php';
include '../../conn.php';

// Verificar se o método de requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas POST é aceito.',
    ]);
    exit;
}

try {
    // Obter IDs dos comentários que foram visualizados
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];

    if (empty($ids) || !is_array($ids)) {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum ID fornecido ou formato inválido.',
        ]);
        exit;
    }

    // Preparar a consulta SQL para atualizar o atributo 'visualizado'
    $update = "UPDATE comentarios_postagens SET visualizado = false WHERE id IN (" . implode(',', array_map('intval', $ids)) . ")";
    $stmt = $connection->prepare($update);
    $stmt->execute();

    echo json_encode([
        'success' => 1,
        'message' => 'Comentários atualizados com sucesso.',
    ]);
} catch (PDOException $e) {
    // Definir o código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
    ]);
    exit;
}
?>
