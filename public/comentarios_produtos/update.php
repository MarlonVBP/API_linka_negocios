<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => 0, 'message' => 'Metodo nao permitido.']);
    exit;
}

try {
    $data = file_get_contents('php://input');
    $json = json_decode($data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar JSON');
    }

    $ids = isset($json['ids']) ? $json['ids'] : [];

    $novo_status = isset($json['novo_status']) ? (bool)$json['novo_status'] : false;

    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => 0, 'message' => 'IDs inválidos.']);
        exit;
    }

    $ids = array_map('intval', $ids);
    if (empty($ids)) exit;

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $update = "UPDATE comentarios_produtos SET visualizado = ? WHERE id IN ($placeholders)";

    $params = array_merge([$novo_status], $ids);

    $stmt = $connection->prepare($update);
    $stmt->execute($params);

    echo json_encode(['success' => 1, 'message' => 'Status atualizado com sucesso.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => 0, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => 0, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>