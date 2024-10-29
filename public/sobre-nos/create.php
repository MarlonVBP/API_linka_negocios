<?php
include '../../cors.php';
include '../../conn.php';
include '../../variaveis_globais/secretKey.php';

$method = $_SERVER['REQUEST_METHOD'];

// Verificar se o método é POST
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

// Obter os dados JSON do corpo da solicitação
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
    // Validar o token do reCAPTCHA
    $token = $data->recaptcha;
    if (empty($token)) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Token reCAPTCHA não fornecido.'
        ]);
        exit;
    }

    // Verificar o token do reCAPTCHA com o Google
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$token}");
    $result = json_decode($response);

    if (!$result->success || $result->score < 0.5) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Erro de captcha, por favor tentar novamente...'
        ]);
        exit;
    }

    // Sanitizar os dados recebidos
    $nome = htmlspecialchars(trim($data->nome));
    $email = htmlspecialchars(trim($data->email));
    $telefone = htmlspecialchars(trim($data->telefone));
    $empresa = htmlspecialchars(trim($data->empresa));
    $area_atuacao = htmlspecialchars(trim($data->area_atuacao));
    $mensagem = htmlspecialchars(trim($data->mensagem));

    // Validar os campos obrigatórios
    if (empty($nome) || empty($email) || empty($telefone) || empty($mensagem)) {
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
    $query = "INSERT INTO `contato` (
            nome,
            email,
            telefone,
            empresa,
            area_atuacao,
            mensagem
            ) 
            VALUES (
            :nome,
            :email,
            :telefone,
            :empresa,
            :area_atuacao,
            :mensagem
            )";

    $stmt = $connection->prepare($query);

    // Associar os valores aos parâmetros da consulta
    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
    $stmt->bindValue(':empresa', $empresa, PDO::PARAM_STR);
    $stmt->bindValue(':area_atuacao', $area_atuacao, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);

    // Executar a consulta e verificar se a inserção foi bem-sucedida
    if ($stmt->execute()) {
        http_response_code(201);
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
