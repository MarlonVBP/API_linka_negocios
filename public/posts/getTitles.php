
<?php
include '../../cors.php';
include '../../conn.php';

// Verificar se o método de requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
 http_response_code(405);
 echo json_encode([
  'success' => 0,
  'message' => 'Método não permitido. Apenas GET é aceito.',
 ]);
 exit;
}

try {
 $query = "SELECT titulo, criado_em FROM postagens LIMIT 3";
 $stmt = $connection->prepare($query);
 $stmt->execute();

 $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

 foreach ($titles as &$title){

  $date = new DateTime($title['criado_em']);
  $title['criado_em'] = $date->format('d M, Y');
 }

 if ($titles) {
  http_response_code(200);
  echo json_encode([
   'success' => 1,
   'response' => $titles
  ]);
 } else {
  http_response_code(404);
  echo json_encode([
   'success' => 0,
   'message' => 'Nenhuma categoria encontrada'
  ]);
 }
 exit;
} catch (PDOException $e) {
 http_response_code(500);
 echo json_encode([
  'success' => 0,
  'message' => 'Erro no servidor: ' . $e->getMessage()
 ]);
 exit;
}
?>
