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

if (!$data || !isset($data->nome_admin) || !isset($data->email) || !isset($data->senha)) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'Dados incompletos ou inválidos.',
    ]);
    exit;
}

// Sanitizar e validar os dados recebidos
$nome_admin = htmlspecialchars(trim($data->nome_admin));
$email = filter_var(trim($data->email), FILTER_VALIDATE_EMAIL);
$senha = trim($data->senha);

if (!$email) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'Email inválido.',
    ]);
    exit;
}

// Verificar se o e-mail já está cadastrado
$query = "SELECT COUNT(*) FROM admin WHERE email = :email";
$stmt = $connection->prepare($query);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    http_response_code(409); // Conflito - e-mail já cadastrado
    echo json_encode([
        'success' => 0,
        'message' => 'O e-mail já está cadastrado.',
    ]);
    exit;
}

try {
    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

    // Preparar a consulta SQL para inserção
    $query = "INSERT INTO admin (nome_admin, email, senha) VALUES (:nome_admin, :email, :senha)";
    $stmt = $connection->prepare($query);

    // Associar os valores aos parâmetros da consulta
    $stmt->bindValue(':nome_admin', $nome_admin, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':senha', $senha_hash, PDO::PARAM_STR);

    // Executar a consulta e verificar se a inserção foi bem-sucedida
    if ($stmt->execute()) {
        http_response_code(201); // Criado
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
    } else {
        http_response_code(500); // Erro interno do servidor
        echo json_encode([
            'success' => 0,
            'message' => 'Falha na inserção dos dados'
        ]);
    }
} catch (PDOException $e) {
    // Definir código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
