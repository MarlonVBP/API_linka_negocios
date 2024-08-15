<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $postagensRecente = [];

        // Consulta para obter todas as postagens
        $query = "SELECT p.id, p.categoria_id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, u.nome_admin AS usuario_nome, c.nome AS categoria_nome 
                  FROM postagens p 
                  JOIN admin u ON p.usuario_id = u.id 
                  JOIN categorias c ON p.categoria_id = c.id
                  ORDER BY p.criado_em DESC";

        $stmt = $connection->prepare($query);
        $stmt->execute();
        $postagensRecente = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decodificar as entidades HTML no campo 'conteudo'
        foreach ($postagensRecente as &$postagem) {
            $postagem['conteudo'] = html_entity_decode($postagem['conteudo'], ENT_QUOTES, 'UTF-8');
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
