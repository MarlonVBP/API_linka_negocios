<?php
include '../../cors.php';
include '../../conn.php';

// Verificar se o método de requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas GET é aceito.',
    ]);
    exit;
}

try {
    // Obter o ID da página dos parâmetros da URL
    $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : null;

    // Verificar se o ID foi fornecido e é um número
    if ($id === null || !filter_var($id, FILTER_VALIDATE_INT)) {
        echo json_encode([
            'success' => 0,
            'message' => 'ID da página inválido.',
        ]);
        exit;
    }

    // Preparar e executar a consulta SQL
    $select = "SELECT * FROM comentarios_paginas WHERE pagina_id = :id ORDER BY avaliacao DESC, criado_em DESC";
    $stmt = $connection->prepare($select);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Verificar se há registros
    if ($stmt->rowCount() > 0) {
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatar a data e a avaliação de cada comentário
        foreach ($comentarios as &$comentario) {
            // Decodificar as entidades HTML
            $comentario['conteudo'] = html_entity_decode($comentario['conteudo'], ENT_QUOTES, 'UTF-8');

            // Formatar a data
            $date = new DateTime($comentario['criado_em']);
            $comentario['criado_em'] = $date->format('M d, Y');

            // Converter a avaliação em estrelas
            $avaliacao = intval($comentario['avaliacao']); // Certifique-se de que 'avaliacao' é um número inteiro
            $rating_stars = '';
            for ($i = 1; $i <= 5; $i++) {
                $rating_stars .= $avaliacao >= $i ? '&#9733;' : '&#9734;'; // Use '★' para estrela cheia e '☆' para estrela vazia
            }
            $comentario['avaliacao'] = $rating_stars;
        }

        echo json_encode([
            'success' => 1,
            'response' => $comentarios,
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Nenhum registro encontrado.',
            'response' => [],
        ]);
    }
} catch (PDOException $e) {
    // Definir o código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
    ]);
    exit;
}
