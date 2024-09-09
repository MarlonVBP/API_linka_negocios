<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $postagensRecente = [];

        // Consulta para obter todas as postagens com tags
        $query = "SELECT p.id, p.categoria_id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, u.nome_admin AS usuario_nome, c.nome AS categoria_nome, 
                         GROUP_CONCAT(pt.tag_id) AS tags_ids
                  FROM postagens p 
                  JOIN admin u ON p.usuario_id = u.id 
                  JOIN categorias c ON p.categoria_id = c.id
                  LEFT JOIN postagem_tags pt ON p.id = pt.postagem_id
                  GROUP BY p.id
                  ORDER BY p.criado_em DESC";

        $stmt = $connection->prepare($query);
        $stmt->execute();
        $postagensRecente = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decodificar as entidades HTML no campo 'conteudo' e processar tags
        foreach ($postagensRecente as &$postagem) {
            $postagem['conteudo'] = html_entity_decode($postagem['conteudo'], ENT_QUOTES, 'UTF-8');

            // Processar tags
            $postagem['tags_id'] = $postagem['tags_ids'] ? explode(',', $postagem['tags_ids']) : [];
            unset($postagem['tags_ids']); // Remover campo tags_ids, se não for necessário
        }

        $response = [
            'success' => true,
            'data' => $postagensRecente
        ];
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Erro ao buscar postagens: ' . $e->getMessage()
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Método de requisição inválido'
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
