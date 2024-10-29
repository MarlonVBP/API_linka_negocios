<?php
include '../../cors.php';
include '../../conn.php';

// Verificar se o método de requisição é POST
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
    $data = file_get_contents('php://input');
    $json = json_decode($data, true);

    // Verificar se o JSON foi decodificado corretamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar JSON');
    }

    $ids = isset($json['ids']) ? $json['ids'] : [];

    if (empty($ids) || !is_array($ids)) {
        echo json_encode([
            'success' => 0,
            'message' => 'IDs inválidos. Verifique o formato dos IDs.',
        ]);
        exit;
    }

    // Sanitizar e validar IDs
    $ids = array_map('intval', $ids);

    if (empty($ids)) {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum ID válido fornecido.',
        ]);
        exit;
    }

    // Preparar a consulta SQL para atualizar o atributo 'visualizado'
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $update = "UPDATE comentarios_produtos SET visualizado = false WHERE id IN ($placeholders)";
    $stmt = $connection->prepare($update);
    $stmt->execute($ids);

    echo json_encode([
        'success' => 1,
        'message' => 'Comentários atualizados com sucesso.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro na requisição: ' . $e->getMessage(),
    ]);
} finally {
    // Fechar a conexão
    $connection = null;
}
?>
