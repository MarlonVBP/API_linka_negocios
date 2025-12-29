<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => 0, 'message' => 'Metodo nao permitido.']);
    exit;
}

try {
    $id = isset($_GET['id']) && $_GET['id'] !== 'undefined' && $_GET['id'] !== '' ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : null;
    $status = isset($_GET['status']) ? intval($_GET['status']) : 1;

    if ($id !== null && filter_var($id, FILTER_VALIDATE_INT)) {
        $select = "SELECT * FROM comentarios_produtos WHERE produto_id = :id ORDER BY criado_em DESC";
        $stmt = $connection->prepare($select);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        $select = "SELECT * FROM comentarios_produtos WHERE visualizado = :status ORDER BY criado_em DESC";
        $stmt = $connection->prepare($select);
        $stmt->bindValue(':status', $status, PDO::PARAM_BOOL);
    }

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($comentarios as &$comentario) {
            $comentario['conteudo'] = html_entity_decode($comentario['conteudo'], ENT_QUOTES, 'UTF-8');
            $date = new DateTime($comentario['criado_em']);
            $comentario['criado_em'] = $date->format('M d, Y');

            $avaliacao = intval($comentario['avaliacao']);
            $rating_stars = '';
            for ($i = 1; $i <= 5; $i++) {
                $rating_stars .= $avaliacao >= $i ? '&#9733;' : '&#9734;';
            }
            $comentario['avaliacao'] = $rating_stars;
        }
        echo json_encode(['success' => 1, 'response' => $comentarios]);
    } else {
        echo json_encode(['success' => 1, 'response' => [], 'message' => 'Nenhum comentário encontrado.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => 0, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>