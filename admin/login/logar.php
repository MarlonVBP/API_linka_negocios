<?php
include '../../cors.php';
include '../../conn.php';
include 'criarJwt.php';

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

if (!isset($data->email) || !isset($data->senha)) {
    echo json_encode([
        'success' => 0,
        'message' => 'Dados insuficientes para login'
    ]);
    exit;
}

try {

    $sql = "SELECT * FROM `admin` WHERE email=:email";
    $stmt = $connection->prepare($sql);
    $stmt->bindValue(':email', $data->email, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stmt->rowCount() === 0 || !password_verify($data->senha, $user['senha'])) {

        echo json_encode([
            'success' => 0,
            'message' => 'E-mail ou senha inválido'
        ]);
        exit;
    }

    $token = criar_jwt($user['id'], $data->email, $data->senha);

    $cookieParams = [
        'expires' => time() + (60 * 60 * 24),
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ];

    setcookie('auth_token', $token, $cookieParams);

    $responseData = [

        'nome' => $user['nome_admin'],
        'email' => $data->email
    ];

    echo json_encode([
        'success' => 1,
        'response' => $responseData
    ]);
    exit;
} catch (Exception $e) {

    http_response_code(500);
    error_log($e->getMessage());
    
    echo json_encode([
        'success' => 0,
        'message' => 'Erro interno do servidor'
    ]);
    exit;
}
