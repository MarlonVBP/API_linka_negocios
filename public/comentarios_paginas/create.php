<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// Permitir apenas requisições POST
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

// Obter e processar os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

try {
    // Organizar e filtrar os dados recebidos
    $pagina_id = htmlspecialchars(trim($data->id));
    $user_name = htmlspecialchars(trim($data->user_name));
    $profissao = htmlspecialchars(trim($data->profissao));
    $empresa = htmlspecialchars(trim($data->empresa));
    $email = htmlspecialchars(trim($data->email));
    $conteudo = htmlspecialchars(trim($data->conteudo));
    $avaliacao = htmlspecialchars(trim($data->avaliacao));

    // Preparar a consulta SQL para inserção
    $query = "INSERT INTO `comentarios_paginas` (
            pagina_id,
            user_name,
            profissao,
            empresa,
            email,
            conteudo,
            avaliacao
            ) 
            VALUES (
            :pagina_id,
            :user_name,
            :profissao,
            :empresa,
            :email,
            :conteudo,
            :avaliacao
            )";

    $stmt = $connection->prepare($query);

    // Associar os valores aos parâmetros da consulta
    $stmt->bindValue(':pagina_id', $pagina_id, PDO::PARAM_STR);
    $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->bindValue(':profissao', $profissao, PDO::PARAM_STR);
    $stmt->bindValue(':empresa', $empresa, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':conteudo', $conteudo, PDO::PARAM_STR);
    $stmt->bindValue(':avaliacao', $avaliacao, PDO::PARAM_STR);

    // Executar a consulta e verificar se a inserção foi bem-sucedida
    if ($stmt->execute()) {
        http_response_code(201); // Criado
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
        exit;
    }

    // Se a inserção falhar, retornar uma mensagem de erro
    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção dos dados'
    ]);
    exit;
} catch (PDOException $e) {
    // Definir código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
