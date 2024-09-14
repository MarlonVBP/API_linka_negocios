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
    // Obter dados JSON do corpo da requisição
    $data = file_get_contents('php://input');
    $json = json_decode($data, true);

    // Verificar se o JSON foi decodificado corretamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar JSON');
    }

    // Obter IDs dos comentários que foram visualizados
    $ids = isset($json['ids']) ? $json['ids'] : [];

    // Verificar se o array de IDs não está vazio e é um array
    if (empty($ids) || !is_array($ids)) {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum ID fornecido ou formato inválido.',
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
    $update = "UPDATE comentarios_paginas SET visualizado = false WHERE id IN ($placeholders)";
    $stmt = $connection->prepare($update);
    $stmt->execute($ids);

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
} catch (Exception $e) {
    // Definir o código de resposta HTTP para erro na requisição
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
