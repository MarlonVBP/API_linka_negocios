<?php
include '../../cors.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $query = "SELECT * FROM ProdutoDivulgacao WHERE id = :id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            $query = "SELECT * FROM ProdutoDivulgacao";
            $stmt = $connection->prepare($query);
        }

        $stmt->execute();

        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($produtos) {
            $response = [
                'success' => true,
                'data' => $produtos
            ];
        } else {
            http_response_code(404);
            $response = [
                'success' => false,
                'data' => [],  
                'message' => 'Nenhum produto encontrado.'
            ];
        }        
    } catch (PDOException $e) {
        http_response_code(500);
        $response = [
            'success' => false,
            'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
        ];
    }
} else {
    http_response_code(405);
    $response = [
        'success' => false,
        'message' => 'Método de requisição inválido. Apenas GET e permitido.'
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
