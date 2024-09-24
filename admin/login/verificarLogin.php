<?php
include '../../cors.php';
include '../../conn.php';
include 'jwtEhValido.php';

// Obter os dados JSON do corpo da solicitação
$data = json_decode(file_get_contents("php://input"));

try {
    // Verificar se o token está presente e é uma string
    if (!isset($data->token) || !is_string($data->token) || empty($data->token)) {
        echo json_encode([
            'success' => 0,
            'message' => 'Token não encontrado ou inválido. Acesso negado.'
        ]);
        exit;
    }

    // Verificar se o token JWT é válido
    $token = jwt_eh_valido($data->token);

    if ($token) {
        // Se o token for válido, enviar resposta de sucesso
        echo json_encode([
            'success' => 1,
            'message' => 'Acesso permitido.'
        ]);
        exit;
    }

    // Se o token não for válido, enviar resposta de acesso negado
    echo json_encode([
        'success' => 0,
        'message' => 'Token inválido. Acesso negado.'
    ]);
    exit;
} catch (Exception $e) {
    // Definir o código de resposta HTTP para erro interno do servidor
    http_response_code(500);

    // Enviar resposta com mensagem de erro
    echo json_encode([
        'success' => 0,
        'message' => 'Ocorreu um erro. Tente novamente mais tarde.'
    ]);
    exit;
}
