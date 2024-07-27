<?php
include '../../cors.php';
include '../../conn.php';
include 'criarJwt.php';

// Obter os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

try {
    // Preparar a consulta SQL para obter os dados do administrador com o e-mail fornecido
    $sql = "SELECT * FROM `admin` WHERE email=:email";
    $stmt = $connection->prepare($sql);
    $stmt->bindValue(':email', $data->email, PDO::PARAM_STR);
    $stmt->execute();

    // Verificar se a consulta retornou algum resultado
    if ($stmt->rowCount() > 0) {
        // Obter os dados do administrador
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar a senha fornecida com a senha armazenada no banco de dados
        if (password_verify($data->senha, $user['senha'])) {
            // Se as credenciais forem válidas, criar o token JWT
            $token = criar_jwt($user['id'], $data->email, $data->senha);

            $responseData = [
                'token' => $token,
                'nome' => $user['nome_admin']
            ];

            // Enviar a resposta com o token JWT
            echo json_encode([
                'success' => 1,
                'response' => $responseData
            ]);
            exit;
        }
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
