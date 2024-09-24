<?php
include '../../cors.php';
include '../../conn.php';
include 'criarJwt.php';

// Obter os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

// Verificar se os campos necessários estão presentes
if (!isset($data->email) || !isset($data->senha)) {
    echo json_encode([
        'success' => 0,
        'message' => 'Dados insuficientes para login'
    ]);
    exit;
}

try {
    // Preparar a consulta SQL para verificar se o e-mail existe
    $sql = "SELECT * FROM `admin` WHERE email=:email";
    $stmt = $connection->prepare($sql);
    $stmt->bindValue(':email', $data->email, PDO::PARAM_STR);
    $stmt->execute();

    // Verificar se o e-mail está cadastrado
    if ($stmt->rowCount() === 0) {
        // E-mail não encontrado
        echo json_encode([
            'success' => 0,
            'message' => 'E-mail inválido'
        ]);
        exit;
    }

    // Obter os dados do administrador
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar a senha fornecida com a senha armazenada no banco de dados
    if (!password_verify($data->senha, $user['senha'])) {
        // Senha incorreta
        echo json_encode([
            'success' => 0,
            'message' => 'Senha inválida'
        ]);
        exit;
    }

    // Se as credenciais forem válidas, criar o token JWT
    $token = criar_jwt($user['id'], $data->email, $data->senha);

    $responseData = [
        'token' => $token,
        'nome' => $user['nome_admin'],
        'email' => $data->email // Adiciona o e-mail à resposta
    ];

    // Enviar a resposta com o token JWT
    echo json_encode([
        'success' => 1,
        'response' => $responseData
    ]);
    exit;

} catch (Exception $e) {
    // Definir o código de resposta HTTP para erro interno do servidor
    http_response_code(500);

    // Registrar erro (opcional)
    error_log($e->getMessage());

    // Enviar resposta com mensagem de erro
    echo json_encode([
        'success' => 0,
        'message' => 'Erro interno do servidor'
    ]);
    exit;
}
?>
