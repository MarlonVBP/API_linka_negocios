<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $id = $_GET['id'] ?? null;
        $postagensMaisVisto = [];
        $postagensRecente = [];

        if ($id) {
            $query = "SELECT p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, u.nome_admin as usuario_nome, c.nome as categoria_nome 
                      FROM postagens p 
                      JOIN admin u ON p.usuario_id = u.id 
                      JOIN categorias c ON p.categoria_id = c.id 
                      WHERE p.id = :id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $postagensRecente = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else {
            $query2 = "SELECT p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, p.view, u.nome_admin as usuario_nome, c.nome as categoria_nome 
                      FROM postagens p 
                      JOIN admin u ON p.usuario_id = u.id 
                      JOIN categorias c ON p.categoria_id = c.id
                      ORDER BY p.view DESC, p.criado_em ASC
                      LIMIT 1";
            $stmt2 = $connection->prepare($query2);
            $stmt2->execute();
            $postagensMaisVisto = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($postagensMaisVisto) {
                $idMaisVisto = $postagensMaisVisto['id'];

                $query3 = "SELECT p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, p.view,
                              u.nome_admin AS usuario_nome, c.nome AS categoria_nome 
                           FROM postagens p 
                           JOIN admin u ON p.usuario_id = u.id 
                           JOIN categorias c ON p.categoria_id = c.id
                           WHERE p.id != :id 
                           ORDER BY p.criado_em DESC
                           LIMIT 3 OFFSET 0";

                $stmt3 = $connection->prepare($query3);
                $stmt3->bindParam(':id', $idMaisVisto, PDO::PARAM_INT);
                $stmt3->execute();
                $postagensRecente = $stmt3->fetchAll(PDO::FETCH_ASSOC);

                $postagensRecente = array_merge([$postagensMaisVisto], $postagensRecente);
            }
        }

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
