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
        'message' => 'Método não permitido. Apenas POST é permitido.',
    ]);
    exit;
}

// Obter e processar os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

try {
    include 'currentTime_function.php';

    $criado_em = getCurrentTime();

    // Organizar e filtrar os dados recebidos
    $pagina_id = htmlspecialchars(trim($data->pagina_id));
    $user_name = htmlspecialchars(trim($data->user_name));
    $profissao = htmlspecialchars(trim($data->profissao));
    $email = htmlspecialchars(trim($data->email));
    $conteudo = htmlspecialchars(trim($data->conteudo));
    $avaliacao = htmlspecialchars(trim($data->avaliacao));

    // Preparar a consulta SQL para inserção
    $query = "INSERT INTO `comentarios_paginas` (
            pagina_id,
            user_name,
            profissao,
            email,
            conteudo,
            avaliacao,
            criado_em
            ) 
            VALUES (
            :pagina_id,
            :user_name,
            :profissao,
            :email,
            :conteudo,
            :avaliacao,
            :criado_em
            )";

    $stmt = $connection->prepare($query);

    // Associar os valores aos parâmetros da consulta
    $stmt->bindValue(':pagina', $pagina, PDO::PARAM_STR);
    $stmt->bindValue(':postagem_id', $postagem_id, PDO::PARAM_STR);
    $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->bindValue(':profissao', $profissao, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':conteudo', $conteudo, PDO::PARAM_STR);
    $stmt->bindValue(':avaliacao', $avaliacao, PDO::PARAM_STR);
    $stmt->bindValue(':criado_em', $criado_em, PDO::PARAM_STR);

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
?>
