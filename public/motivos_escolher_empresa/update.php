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
        'message' => 'Metodo nao permitido. Apenas PUT e aceito.',
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
    $select_query = "SELECT * FROM motivos_escolher_empresa WHERE id = :id";
    $select_stmt = $connection->prepare($select_query);
    $select_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $select_stmt->execute();

    if ($select_stmt->rowCount() > 0) {
        $titulo = isset($data->titulo) ? htmlspecialchars(trim($data->titulo)) : null;
        $descricao = isset($data->descricao) ? htmlspecialchars(trim($data->descricao)) : null;
        $imagem = isset($data->imagem) ? htmlspecialchars(trim($data->imagem)) : null;

        $update_query = "UPDATE motivos_escolher_empresa SET 
                            titulo = :titulo, 
                            descricao = :descricao, 
                            imagem = :imagem 
                        WHERE id = :id";

        $update_stmt = $connection->prepare($update_query);

        $update_stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $update_stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
        $update_stmt->bindValue(':imagem', $imagem, PDO::PARAM_STR);
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
