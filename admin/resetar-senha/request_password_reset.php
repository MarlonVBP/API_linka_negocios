<?php
// request_password_reset.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
include '../../cors.php';
include '../../conn.php';

$data = json_decode(file_get_contents('php://input'));
$email = $data->email;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validação do e-mail
  if (!isset($email) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "E-mail inválido ou campo de e-mail vazio."]);
    exit();
  }

  // Verificar se o email existe no banco de dados
  $query = "SELECT id FROM admin WHERE email = :email";
  $stmt = $connection->prepare($query);
  $stmt->bindValue(':email', $email);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    // Gerar um token de redefinição
    $token = bin2hex(random_bytes(50));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Armazenar o token e o prazo de validade no banco de dados
    $updateQuery = "UPDATE admin SET reset_token = ?, reset_expires = ? WHERE email = ?";
    $updateStmt = $connection->prepare($updateQuery);
    $updateStmt->bindValue(1, $token);
    $updateStmt->bindValue(2, $expiry);
    $updateStmt->bindValue(3, $email);
    $updateStmt->execute();

    // Enviar o link de redefinição por e-mail usando PHPMailer
    $reset_link = "https://linkanegocios.digital/resetar-senha/" . $token;

    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->CharSet = 'UTF-8';
      $mail->Host = 'smtp-relay.brevo.com';
      $mail->SMTPAuth = true;
      $mail->Username = '7efcfa001@smtp-brevo.com';
      $mail->Password = 'pzqFRB725kavVNSm';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;
        

      // Configurações do e-mail
      $mail->setFrom('marlonvicctor13@gmail.com', 'LinkaNegocios');
      $mail->addAddress($email);

      // Conteúdo do e-mail
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
      <p>&copy; 2024 Linka Negócios. Todos os direitos reservados.</p>
    </div>
  </div>
</div>";
      $mail->AltBody = "Clique no link para redefinir sua senha: $reset_link\n\nAviso: este link é válido por apenas 1 hora após o envio deste e-mail.";
      $mail->send();
      echo json_encode([
        "message" => "Email de redefinição enviado!",
        "token" => $token
      ]);
    } catch (Exception $e) {
      echo json_encode(["error" => "Erro ao enviar o e-mail: {$mail->ErrorInfo}"]);
    }
  } else {
    echo json_encode(["error" => "Usuário não encontrado!"]);
  }
}
