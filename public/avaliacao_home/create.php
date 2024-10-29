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

try {
    if (!isset($data->nome) || !isset($data->avaliacao) || !isset($data->mensagem)) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Dados incompletos. É necessário fornecer nome, avaliação e mensagem.'
        ]);
        exit;
    }

    $nome = htmlspecialchars(trim($data->nome));
    $avaliacao = (int) $data->avaliacao;
    $mensagem = htmlspecialchars(trim($data->mensagem));
    $foto_perfil = isset($data->foto_perfil) ? htmlspecialchars(trim($data->foto_perfil)) : null;

    if (empty($nome) || empty($mensagem) || $avaliacao < 1 || $avaliacao > 5) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => 'Dados inválidos. Nome, avaliação e mensagem são obrigatórios, e a avaliação deve estar entre 1 e 5.'
        ]);
        exit;
    }

    $query = "INSERT INTO `avaliacao_empresa` (
            nome,
            avaliacao,
            mensagem,
            foto_perfil
            ) 
            VALUES (
            :nome,
            :avaliacao,
            :mensagem,
            :foto_perfil
            )";

    $stmt = $connection->prepare($query);

    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':avaliacao', $avaliacao, PDO::PARAM_INT);
    $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);
    $stmt->bindValue(':foto_perfil', $foto_perfil, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201); 
        echo json_encode([
            'success' => 1,
            'message' => 'Avaliação inserida com sucesso'
        ]);
        exit;
    }

    echo json_encode([
        'success' => 0,
        'message' => 'Falha na inserção da avaliação'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
?>
