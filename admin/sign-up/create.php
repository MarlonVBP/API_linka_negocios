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

if (!$data || !isset($data->nome_admin) || !isset($data->email) || !isset($data->senha)) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'Dados incompletos ou inválidos.',
    ]);
    exit;
}

$nome_admin = htmlspecialchars(trim($data->nome_admin));
$email = filter_var(trim($data->email), FILTER_VALIDATE_EMAIL);
$senha = trim($data->senha);
$foto_perfil = isset($data->foto_perfil) ? htmlspecialchars(trim($data->foto_perfil)) : null;
$cargo = isset($data->cargo) ? htmlspecialchars(trim($data->cargo)) : null;

if (!$email) {
    http_response_code(400);
    echo json_encode([
        'success' => 0,
        'message' => 'Email inválido.',
    ]);
    exit;
}

$query = "SELECT COUNT(*) FROM admin WHERE email = :email";
$stmt = $connection->prepare($query);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    http_response_code(409);
    echo json_encode([
        'success' => 0,
        'message' => 'O e-mail já está cadastrado.',
    ]);
    exit;
}

try {

    $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

    $query = "INSERT INTO admin (nome_admin, email, senha, foto_perfil, cargo) VALUES (:nome_admin, :email, :senha, :foto_perfil, :cargo)";

    $stmt = $connection->prepare($query);
    $stmt->bindValue(':nome_admin', $nome_admin, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':senha', $senha_hash, PDO::PARAM_STR);
    $stmt->bindValue(':foto_perfil', $foto_perfil, PDO::PARAM_STR);
    $stmt->bindValue(':cargo', $cargo, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Dados inseridos com sucesso'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => 'Falha na inserção dos dados'
        ]);
    }
} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
