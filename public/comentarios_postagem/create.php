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

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'Dados inválidos. Verifique o corpo da solicitação.',
    ]);
    exit;
}

try {
    // Organizar e filtrar os dados recebidos
    $postagem_id = htmlspecialchars(trim($data->id));
    $user_name = htmlspecialchars(trim($data->nome));
    $profissao = htmlspecialchars(trim($data->profissao));
    $email = htmlspecialchars(trim($data->email));
    $conteudo = htmlspecialchars(trim($data->conteudo));
    $avaliacao = htmlspecialchars(trim($data->avaliacao));

    // Validar os dados
    if (empty($postagem_id) || empty($user_name) || empty($profissao) || empty($email) || empty($conteudo) || empty($avaliacao)) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Todos os campos são obrigatórios.',
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Formato de e-mail inválido.',
        ]);
        exit;
    }

    // Preparar a consulta SQL para inserção
    $query = "INSERT INTO `comentarios_postagens` (
            postagem_id,
            user_name,
            profissao,
            email,
            conteudo,
            avaliacao
            ) 
            VALUES (
            :postagem_id,
            :user_name,
            :profissao,
            :email,
            :conteudo,
            :avaliacao
            )";

    $stmt = $connection->prepare($query);

    // Associar os valores aos parâmetros da consulta
    $stmt->bindValue(':postagem_id', $postagem_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->bindValue(':profissao', $profissao, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':conteudo', $conteudo, PDO::PARAM_STR);
    $stmt->bindValue(':avaliacao', $avaliacao, PDO::PARAM_INT);

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
    http_response_code(500);
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
