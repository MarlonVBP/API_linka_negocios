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

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'Dados inválidos. Verifique o corpo da solicitação.',
    ]);
    exit;
}

try {
    $produto_id = htmlspecialchars(trim($data->id));
    $user_name = htmlspecialchars(trim($data->user_name));
    $profissao = htmlspecialchars(trim($data->profissao));
    $empresa = htmlspecialchars(trim($data->empresa));
    $email = htmlspecialchars(trim($data->email));
    $conteudo = htmlspecialchars(trim($data->conteudo));
    $avaliacao = htmlspecialchars(trim($data->avaliacao));

    if (empty($produto_id) || empty($user_name) || empty($profissao) || empty($email) || empty($conteudo) || empty($avaliacao)) {
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

    $query = "INSERT INTO `comentarios_produtos` (
            produto_id,
            user_name,
            profissao,
            empresa,
            email,
            conteudo,
            avaliacao
            ) 
            VALUES (
            :produto_id,
            :user_name,
            :profissao,
            :empresa,
            :email,
            :conteudo,
            :avaliacao
            )";

    $stmt = $connection->prepare($query);

    $stmt->bindValue(':produto_id', $produto_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->bindValue(':profissao', $profissao, PDO::PARAM_STR);
    $stmt->bindValue(':empresa', $empresa, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':conteudo', $conteudo, PDO::PARAM_STR);
    $stmt->bindValue(':avaliacao', $avaliacao, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $queryUpdate = "UPDATE `postagens` 
        SET comentarios = comentarios + 1 
        WHERE id = :produto_id";
        $stmtUpdate = $connection->prepare($queryUpdate);

        $stmtUpdate->bindValue(':produto_id', $produto_id, PDO::PARAM_INT);
        $stmtUpdate->execute();

        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção dos dados'
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
?>