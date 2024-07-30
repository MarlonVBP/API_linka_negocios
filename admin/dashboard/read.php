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
 // Preparar e executar a consulta SQL
 $select = "SELECT mes, dados_line, dados_bar FROM dashboard ORDER BY id";
 $stmt = $connection->prepare($select);
 $stmt->execute();

 // Verificar se há registros
 if ($stmt->rowCount() > 0) {
  $vetor_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $months = array_column($vetor_dados, 'mes');
  $dataset1 = array_column($vetor_dados, 'dados_line');
  $dataset2 = array_column($vetor_dados, 'dados_bar');

  $response[0]= [
   'mes' => $months,
   'dados_line' => $dataset1,
   'dados_bar' => $dataset2,
  ];

  echo json_encode([
   'success' => 1,
   'response' => $response
  ]);
 } else {
  echo json_encode([
   'success' => 0,
   'message' => 'Nenhum registro encontrado.',
   'response' => [],
  ]);
 }
} catch (PDOException $e) {
 // Definir o código de resposta HTTP para erro interno do servidor
 http_response_code(500);
 echo json_encode([
  'success' => 0,
  'message' => 'Erro no servidor: ' . $e->getMessage(),
 ]);
 exit;
}
