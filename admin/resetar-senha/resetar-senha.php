<?php

include '../../cors.php';
include '../../conn.php';

$data = json_decode(file_get_contents('php://input'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 $token = $data->token;
 $new_password = password_hash($data->new_password, PASSWORD_DEFAULT);

 // Verificar se o token é válido e não expirou
 $query = "SELECT id FROM admin WHERE reset_token = ? AND reset_expires > NOW()";
 $stmt = $connection->prepare($query);
 $stmt->bindValue("s", $token);
 $stmt->execute();

 if ($stmt->rowCount() > 0) {
  // Redefinir a senha
  $query = "UPDATE admin SET senha = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?";
  $stmt = $connection->prepare($query);
  $stmt->bindValue("ss", $new_password, $token);
  $stmt->execute();

  echo json_encode(["message" => "Senha redefinida com sucesso!"]);
 } else {
  echo json_encode(["error" => "Token inválido ou expirado!"]);
 }
}
