<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
include '../../cors.php';
include '../../conn.php';

$data = json_decode(file_get_contents('php://input'));
$email = isset($data->email) ? filter_var($data->email, FILTER_SANITIZE_EMAIL) : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (empty($email)) {
    echo json_encode(["error" => "O campo de e-mail é obrigatório."]);
    exit();
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "E-mail inválido."]);
    exit();
  }

  $query = "SELECT id FROM admin WHERE email = :email";
  $stmt = $connection->prepare($query);
  $stmt->bindValue(':email', $email);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {

    $token = bin2hex(random_bytes(50));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $updateQuery = "UPDATE admin SET reset_token = ?, reset_expires = ? WHERE email = ?";
    $updateStmt = $connection->prepare($updateQuery);
    $updateStmt->execute([$token, $expiry, $email]);

    $url_frontend = "https://linkanegocios.com.br/resetar-senha/";
    $reset_link = $url_frontend . $token;

    $mail = new PHPMailer(true);

    try {
      $anoAtual = date('Y');

      $mail->isSMTP();
      $mail->CharSet = 'UTF-8';
      $mail->Host = 'smtp.titan.email';
      $mail->SMTPAuth = true;
      $mail->Username = 'contato@linkanegocios.com.br';
      $mail->Password = '*******8';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port = 465;
      $mail->setFrom('contato@linkanegocios.com.br', 'Linka Negócios');
      $mail->addAddress($email);
      $mail->isHTML(true);
      $mail->Subject = 'Redefinir sua senha';
      $mail->Body = "
                    <div style='font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 20px;'>
                      <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);'>
                        <div style='background-color: #221e1f; color: #ffffff; padding: 15px; text-align: center;'>
                          <h2 style='margin: 0;'>Redefinição de Senha</h2>
                        </div>
                        <div style='padding: 20px; color: #7b7b7b;'>
                          <p>Olá,</p>
                          <p>Recebemos uma solicitação para redefinir sua senha. Clique no botão abaixo para criar uma nova senha:</p>
                          <a href='$reset_link' style='display: inline-block; margin: 20px 0; padding: 10px 20px; background-color: #b22828; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 4px;'>Redefinir Senha</a>
                          <p style='font-size: 14px; color: #7b7b7b;'>Se você não solicitou esta redefinição, ignore este e-mail.</p>
                          <p style='font-size: 12px; color: #dc3545; margin-top: 20px;'>Aviso: este link é válido por apenas 1 hora após o envio deste e-mail.</p>
                        </div>
                        <div style='background-color: #f2f1ed; color: #7b7b7b; padding: 10px; text-align: center; font-size: 12px;'>
                          <p>&copy; $anoAtual Linka Negócios. Todos os direitos reservados.</p>
                        </div>
                      </div>
                    </div>";
      $mail->AltBody = "Clique no link para redefinir sua senha: $reset_link\n\nAviso: este link é válido por apenas 1 hora após o envio deste e-mail.";

      $mail->send();

      echo json_encode([
        "message" => "Email de redefinição enviado com sucesso!",
      ]);
    } catch (Exception $e) {
      echo json_encode(["error" => "Erro ao enviar o e-mail: {$mail->ErrorInfo}"]);
    }
  } else {
    echo json_encode(["error" => "E-mail não encontrado em nossa base de dados."]);
  }
} else {
  echo json_encode(["error" => "Método de requisição inválido."]);
}
