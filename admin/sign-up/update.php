<?php
include '../../cors.php';
include '../../conn.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metodo nao permitido. Apenas PUT e aceito.',
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
file_put_contents('log.txt', print_r($data, true), FILE_APPEND);

$email = isset($data->email) ? htmlspecialchars(trim($data->email)) : null;

if (!$email) {
    echo json_encode([
        'success' => 0,
        'message' => 'E-mail não fornecido.'
    ]);
    exit;
}

try {
    $select_query = "SELECT * FROM admin WHERE email = :email";
    $select_stmt = $connection->prepare($select_query);
    $select_stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $select_stmt->execute();

    if ($select_stmt->rowCount() > 0) {
        $nome_admin = isset($data->nome_admin) ? htmlspecialchars(trim($data->nome_admin)) : null;
        $foto_perfil = isset($data->foto_perfil) ? htmlspecialchars(trim($data->foto_perfil)) : null;
        $cargo = isset($data->cargo) ? htmlspecialchars(trim($data->cargo)) : null;
        $ultimo_login = isset($data->ultimo_login) ? htmlspecialchars(trim($data->ultimo_login)) : null;

        $update_query = "UPDATE admin SET
                            nome_admin = :nome_admin,
                            foto_perfil = :foto_perfil,
                            cargo = :cargo,
                            ultimo_login = :ultimo_login
                         WHERE email = :email";

        $update_stmt = $connection->prepare($update_query);

        $update_stmt->bindValue(':nome_admin', $nome_admin, PDO::PARAM_STR);
        $update_stmt->bindValue(':foto_perfil', $foto_perfil, PDO::PARAM_STR);
        $update_stmt->bindValue(':cargo', $cargo, PDO::PARAM_STR);
        $update_stmt->bindValue(':ultimo_login', $ultimo_login, PDO::PARAM_STR);
        $update_stmt->bindValue(':email', $email, PDO::PARAM_STR);

        if ($update_stmt->execute()) {
            http_response_code(200); 
            echo json_encode([
                'success' => 1,
                'message' => 'Dados atualizados com sucesso.'
            ]);
        } else {
            echo json_encode([
                'success' => 0,
                'message' => 'Falha na atualização dos dados.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Registro não encontrado para o e-mail fornecido.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    file_put_contents('log.txt', 'PDOException: ' . $e->getMessage() . "\n", FILE_APPEND); // Log do erro PDO
}
?>
