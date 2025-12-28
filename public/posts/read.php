<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	try {
		$id = $_GET['id'] ?? null;
		$postagensMaisVisto = [];
		$postagensRecente = [];

		if ($id) {
			$updateQuery = "UPDATE postagens SET views = views + 1 WHERE id = :id";
			$updateStmt = $connection->prepare($updateQuery);
			$updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
			$updateStmt->execute();

			$dados = 1;
			$mes = date('m');
			$insetQuery = "INSERT INTO dashboard (mes, dados) VALUES (:mes, :dados)";
			$insetStmt = $connection->prepare($insetQuery);
			$insetStmt->bindParam(':mes', $mes, PDO::PARAM_INT);
			$insetStmt->bindParam(':dados', $dados, PDO::PARAM_INT);
			$insetStmt->execute();

			$query = "SELECT p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.views, p.criado_em, 
                             u.nome_admin as usuario_nome, 
                             c.nome as categoria_nome,
                             GROUP_CONCAT(DISTINCT t.nome) as tags
                      FROM postagens p 
                      JOIN admin u ON p.usuario_id = u.id 
                      JOIN categorias c ON p.categoria_id = c.id 
                      LEFT JOIN postagem_tags pt ON p.id = pt.postagem_id
                      LEFT JOIN tags t ON pt.tag_id = t.id
                      WHERE p.id = :id
                      GROUP BY p.id";

			$stmt = $connection->prepare($query);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$postagensRecente = $stmt->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$query2 = "SELECT p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, p.views, p.comentarios,
                              u.nome_admin as usuario_nome, 
                              c.nome as categoria_nome,
                              COUNT(DISTINCT cmt.id) as numero_comentarios,
                              GROUP_CONCAT(DISTINCT t.nome) as tags
                       FROM postagens p 
                       JOIN admin u ON p.usuario_id = u.id 
                       JOIN categorias c ON p.categoria_id = c.id
                       LEFT JOIN comentarios_postagens cmt ON p.id = cmt.postagem_id
                       LEFT JOIN postagem_tags pt ON p.id = pt.postagem_id
                       LEFT JOIN tags t ON pt.tag_id = t.id
                       GROUP BY p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, p.views, u.nome_admin, c.nome
                       ORDER BY p.views DESC, p.criado_em ASC
                       LIMIT 1";

			$stmt2 = $connection->prepare($query2);
			$stmt2->execute();
			$postagensMaisVisto = $stmt2->fetch(PDO::FETCH_ASSOC);

			if ($postagensMaisVisto) {
				$idMaisVisto = $postagensMaisVisto['id'];

				$query3 = "SELECT p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, p.views, p.comentarios,
                                  u.nome_admin AS usuario_nome, 
                                  c.nome AS categoria_nome,
                                  COUNT(DISTINCT cmt.id) AS numero_comentarios,
                                  GROUP_CONCAT(DISTINCT t.nome) as tags
                           FROM postagens p 
                           JOIN admin u ON p.usuario_id = u.id 
                           JOIN categorias c ON p.categoria_id = c.id
                           LEFT JOIN comentarios_postagens cmt ON p.id = cmt.postagem_id
                           LEFT JOIN postagem_tags pt ON p.id = pt.postagem_id
                           LEFT JOIN tags t ON pt.tag_id = t.id
                           WHERE p.id != :id 
                           GROUP BY p.id, p.titulo, p.conteudo, p.descricao, p.url_imagem, p.criado_em, p.views, u.nome_admin, c.nome
                           ORDER BY p.criado_em DESC";

				$stmt3 = $connection->prepare($query3);
				$stmt3->bindParam(':id', $idMaisVisto, PDO::PARAM_INT);
				$stmt3->execute();
				$postagensRecente = $stmt3->fetchAll(PDO::FETCH_ASSOC);

				$postagensRecente = array_merge([$postagensMaisVisto], $postagensRecente);
			}
		}

		foreach ($postagensRecente as &$postagem) {
			$postagem['conteudo'] = html_entity_decode($postagem['conteudo'], ENT_QUOTES, 'UTF-8');

			if (!empty($postagem['tags'])) {
				$postagem['tags'] = explode(',', $postagem['tags']);
			} else {
				$postagem['tags'] = [];
			}
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
