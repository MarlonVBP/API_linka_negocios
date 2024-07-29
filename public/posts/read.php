<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $query = "SELECT p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, u.nome_admin as usuario_nome, c.nome as categoria_nome 
                      FROM postagens p 
                      JOIN admin u ON p.usuario_id = u.id 
                      JOIN categorias c ON p.categoria_id = c.id 
                      WHERE p.id = :id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            $query = "SELECT p.id, p.titulo, p.conteudo, p.descricao,p.url_imagem, p.criado_em, u.nome_admin as usuario_nome, c.nome as categoria_nome 
                      FROM postagens p 
                      JOIN admin u ON p.usuario_id = u.id 
                      JOIN categorias c ON p.categoria_id = c.id";
            $stmt = $connection->prepare($query);
        }

        $stmt->execute();

        $postagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($postagens as &$postagem) {
            // Decodificar as entidades HTML no campo 'conteudo'
            $postagem['conteudo'] = html_entity_decode($postagem['conteudo'], ENT_QUOTES, 'UTF-8');
        }

        $response = [
            'success' => true,
            'data' => $postagens
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
