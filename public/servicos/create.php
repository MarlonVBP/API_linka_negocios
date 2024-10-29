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

try {
    if (!isset($data->titulo) || !isset($data->descricao) || !isset($data->imagem)) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Dados incompletos. É necessário fornecer título, descrição e imagem.'
        ]);
        exit;
    }

    $titulo = htmlspecialchars(trim($data->titulo));
    $descricao = htmlspecialchars(trim($data->descricao));
    $imagem = htmlspecialchars(trim($data->imagem));
    $conteudo1 = htmlspecialchars(trim($data->conteudo1));
    $conteudo2 = htmlspecialchars(trim($data->conteudo2));
    $conteudo3 = htmlspecialchars(trim($data->conteudo3));

    if (empty($titulo) || empty($descricao) || empty($imagem)) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Dados inválidos. Todos os campos devem ser preenchidos.'
        ]);
        exit;
    }

    $query = "INSERT INTO `servicos` (
            titulo,
            descricao,
            imagem,
            conteudo1,
            conteudo2,
            conteudo3
            ) 
            VALUES (
            :titulo,
            :descricao,
            :imagem,
            :conteudo1,
            :conteudo2,
            :conteudo3
            )";

    $stmt = $connection->prepare($query);

    $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
    $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindValue(':imagem', $imagem, PDO::PARAM_STR);
    $stmt->bindValue(':conteudo1', $conteudo1, PDO::PARAM_STR);
    $stmt->bindValue(':conteudo2', $conteudo2, PDO::PARAM_STR);
    $stmt->bindValue(':conteudo3', $conteudo3, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Serviço inserido com sucesso'
        ]);
        exit;
    }

    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção do serviço'
    ]);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
