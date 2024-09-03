<?php
include '../../cors.php';
include '../../conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// Permitir apenas requisições DELETE
if ($method === 'OPTIONS') {
    exit;
}

if ($method !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Método não permitido. Apenas DELETE é aceito.',
    ]);
    exit;
}

// Obter o ID do registro a partir da query string
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id === null) {
    echo json_encode([
        'success' => 0,
        'message' => 'Por favor, forneça o ID do comentário.'
    ]);
    exit;
}

try {
    // Iniciar uma transação
    $connection->beginTransaction();

    // Verificar se o registro existe e obter o ID da postagem
    $fetch_post = "SELECT produto_id FROM `comentarios_produtos` WHERE id = :id";
    $fetch_stmt = $connection->prepare($fetch_post);
    $fetch_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $fetch_stmt->execute();

    $comment = $fetch_stmt->fetch(PDO::FETCH_ASSOC);

    if ($comment) {
        $produto_id = $comment['produto_id'];

        // Excluir o registro
        $delete_post = "DELETE FROM `comentarios_produtos` WHERE id = :id";
        $delete_stmt = $connection->prepare($delete_post);
        $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            // Atualizar o número de comentários
            $queryUpdate = "UPDATE `postagens` 
                            SET comentarios = comentarios - 1 
                            WHERE id = :produto_id";
            $stmtUpdate = $connection->prepare($queryUpdate);
            $stmtUpdate->bindValue(':produto_id', $produto_id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // Commit da transação
            $connection->commit();

            echo json_encode([
                'success' => 1,
                'message' => 'Registro excluído com sucesso.'
            ]);
            exit;
        } else {
            // Reverter a transação em caso de falha na exclusão
            $connection->rollBack();
            echo json_encode([
                'success' => 0,
                'message' => 'Falha ao excluir o registro. Algo deu errado.'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'ID inválido. Nenhum registro encontrado com o ID fornecido.'
        ]);
        exit;
    }
} catch (PDOException $e) {
    // Reverter a transação em caso de erro
    $connection->rollBack();
    // Definir código de resposta HTTP para erro interno do servidor
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
