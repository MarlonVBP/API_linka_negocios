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

    // Obter os dados do administrador
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar se o e-mail está cadastrado
    if ($stmt->rowCount() === 0 || !password_verify($data->senha, $user['senha'])) {
        // E-mail não encontrado
        echo json_encode([
            'success' => 0,
            'message' => 'E-mail ou senha inválido'
        ]);
        exit;
    }

    // Se as credenciais forem válidas, criar o token JWT
    $token = criar_jwt($user['id'], $data->email, $data->senha);

    $cookieParams = [
        'expires' => time() + (60 * 60 * 24), // 1 dia
        'path' => '/', // Valido para todo o site
        'domain' => '', // Automático (ou defina se necessário para subdomínios)
        'secure' => true, // OBRIGATÓRIO: Só envia se for HTTPS
        'httponly' => true, // OBRIGATÓRIO: JS não acessa
        'samesite' => 'Strict' // Proteção contra CSRF (Use 'Lax' se 'Strict' bloquear navegação externa)
    ];

    // Define o cookie
    setcookie('auth_token', $token, $cookieParams);

    // Retorna APENAS dados públicos do usuário, SEM O TOKEN
    $responseData = [
        // 'token' => $token, // REMOVIDO: Não enviamos mais o token no corpo
        'nome' => $user['nome_admin'],
        'email' => $data->email
    ];

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
