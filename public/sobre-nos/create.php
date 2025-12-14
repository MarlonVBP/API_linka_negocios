<?php
include '../../cors.php';
include '../../conn.php';
//include '../../variaveis_globais/secretKey.php';

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

    $nome = htmlspecialchars(trim($data->nome));
    $email = htmlspecialchars(trim($data->email));
    $telefone = htmlspecialchars(trim($data->telefone));
    $empresa = isset($data->empresa) ? htmlspecialchars(trim($data->empresa)) : '';
    $area_atuacao = isset($data->area_atuacao) ? htmlspecialchars(trim($data->area_atuacao)) : '';
    $mensagem = htmlspecialchars(trim($data->mensagem));

    if (empty($nome) || empty($email) || empty($telefone) || empty($mensagem)) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Todos os campos obrigatórios devem ser preenchidos.',
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

    $query = "INSERT INTO `contato` (
            nome,
            email,
            telefone,
            empresa,
            area_atuacao,
            mensagem,
            data_envio
            ) 
            VALUES (
            :nome,
            :email,
            :telefone,
            :empresa,
            :area_atuacao,
            :mensagem,
            NOW()
            )";

    $stmt = $connection->prepare($query);

    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
    $stmt->bindValue(':empresa', $empresa, PDO::PARAM_STR);
    $stmt->bindValue(':area_atuacao', $area_atuacao, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.'
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Falha ao enviar a mensagem. Por favor, tente novamente.'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor. Por favor, tente novamente mais tarde.'
    ]);
    exit;
}
?>