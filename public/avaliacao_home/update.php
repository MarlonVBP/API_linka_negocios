<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas PUT é aceito.',
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$id = isset($data->id) ? intval($data->id) : null;

if ($id === null) {
    echo json_encode([
        'success' => 0,
        'message' => 'ID do registro não fornecido.'
    ]);
    exit;
}

try {
    $select_query = "SELECT * FROM avaliacao_empresa WHERE id = :id";
    $select_stmt = $connection->prepare($select_query);
    $select_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $select_stmt->execute();

    if ($select_stmt->rowCount() > 0) {
        $nome = isset($data->nome) ? htmlspecialchars(trim($data->nome)) : null;
        $avaliacao = isset($data->avaliacao) ? (int) $data->avaliacao : null;
        $mensagem = isset($data->mensagem) ? htmlspecialchars(trim($data->mensagem)) : null;
        $foto_perfil = isset($data->foto_perfil) ? htmlspecialchars(trim($data->foto_perfil)) : null;

        $update_query = "UPDATE avaliacao_empresa SET 
                            nome = :nome, 
                            avaliacao = :avaliacao, 
                            mensagem = :mensagem, 
                            foto_perfil = :foto_perfil 
                        WHERE id = :id";

        $update_stmt = $connection->prepare($update_query);

        $update_stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $update_stmt->bindValue(':avaliacao', $avaliacao, PDO::PARAM_INT);
        $update_stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);
        $update_stmt->bindValue(':foto_perfil', $foto_perfil, PDO::PARAM_STR);
        $update_stmt->bindValue(':id', $id, PDO::PARAM_INT);

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
            'message' => 'Registro não encontrado para o ID fornecido.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
?>
