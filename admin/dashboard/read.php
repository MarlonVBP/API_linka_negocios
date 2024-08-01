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
 $select = "SELECT mes, dados FROM dashboard ORDER BY id";
 $stmt = $connection->prepare($select);
 $stmt->execute();

 // Verificar se há registros
 if ($stmt->rowCount() > 0) {
  $vetor_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $mes = array_column($vetor_dados, 'mes');
  $dados = array_column($vetor_dados, 'dados');

  $response[0]= [
   'mes' => $mes,
   'dados' => $dados,
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
