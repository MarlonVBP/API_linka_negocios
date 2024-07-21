<?php
include '../cors.php';
include '../conn.php';
include 'criarJwt.php';

// Obter os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

try {
    // Preparar a consulta SQL para verificar as credenciais do administrador
    $sql = "SELECT `id_admin` FROM `admin` WHERE email_admin=:email AND senha_admin=:senha";
    $stmt = $connection->prepare($sql);
    $stmt->bindValue(':email', $data->email, PDO::PARAM_STR);
    $stmt->bindValue(':senha', $data->password, PDO::PARAM_STR);
    $stmt->execute();

    // Verificar se a consulta retornou algum resultado
    if ($stmt->rowCount() > 0) {
        // Se as credenciais forem válidas, obter o ID do administrador
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Criar o token JWT
        $token = criar_jwt($user['id_admin'], $data->email, $data->password);

        // Enviar a resposta com o token JWT
        echo json_encode([
            'success' => 1,
            'token' => $token
        ]);
        exit;
    }

    // Se as credenciais forem inválidas, enviar uma resposta de falha ao realizar o login
    echo json_encode([
        'success' => 0,
        'message' => 'Falha ao realizar o login'
    ]);
    exit;
} catch (Exception $e) { // Usar Exception em vez de PDOException para capturar qualquer tipo de exceção
    // Definir o código de resposta HTTP para erro interno do servidor
    http_response_code(500);

    // Enviar resposta com mensagem de erro
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
    exit;
}
