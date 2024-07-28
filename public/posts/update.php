<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Obter o ID do usuário baseado no e-mail
    $sql = "SELECT id FROM admin WHERE email=:email";
    $stmt = $connection->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario_id = $user['id'];
        
        if (isset($_POST['id']) && isset($_POST['title']) && isset($_POST['content']) && isset($_POST['category_id'])) {
            $post_id = intval($_POST['id']);
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $category_id = intval($_POST['category_id']);
            $image_updated = false;
            
            // Verifica se uma nova imagem foi fornecida
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $image = $_FILES['image']['name'];
                $target_dir = "imagens/";
                $target_file = $target_dir . basename($image);
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_updated = true;
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Erro ao fazer o upload da imagem'
                    ];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
            }
            
            $query = "UPDATE postagens SET categoria_id = :categoria_id, titulo = :titulo, conteudo = :conteudo";
            if ($image_updated) {
                $query .= ", url_imagem = :url_imagem";
            }
            $query .= " WHERE id = :post_id AND usuario_id = :usuario_id";
            
            $stmt = $connection->prepare($query);
            
            $stmt->bindValue(':categoria_id', $category_id, PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $title, PDO::PARAM_STR);
            $stmt->bindValue(':conteudo', $content, PDO::PARAM_STR);
            $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            
            if ($image_updated) {
                $stmt->bindValue(':url_imagem', $target_file, PDO::PARAM_STR);
            }
            
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Postagem atualizada com sucesso',
                    'data' => [
                        'id' => $post_id,
                        'title' => $title,
                        'content' => $content,
                        'category_id' => $category_id,
                        'image' => $image_updated ? $target_file : null
                    ]
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Falha na atualização da postagem'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Campos obrigatórios não fornecidos'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Usuário não encontrado'
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
