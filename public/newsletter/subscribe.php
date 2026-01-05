<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../cors.php';
include '../../conn.php';
require '../../vendor/autoload.php';


function enviarEmailBoasVindas($emailDestino, $tipo = 'novo')
{
    $corPrimaria = "#b22828";
    $corFundo = "#f4f4f4";
    $anoAtual = date('Y');



    $linkUnsub = "https://linkanegocios.com.br/api/public/newsletter/unsubscribe.php?e=" . base64_encode($emailDestino);

    if ($tipo == 'novo') {
        $titulo = "Bem-vindo(a) à Newsletter da Linka Negócios!";
        $texto  = "Obrigado por se inscrever. Agora você faz parte da nossa lista exclusiva e receberá <strong>gratuitamente</strong> nossos melhores conteúdos mensais sobre gestão e mercado.";
    } else {
        $titulo = "Bem-vindo(a) de volta!";
        $texto  = "Ficamos felizes em ter você de volta! Sua <strong>inscrição na newsletter</strong> foi reativada e você voltará a receber nossos conteúdos.";
    }

    $html = "
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <body style='margin: 0; padding: 0; background-color: $corFundo; font-family: Helvetica, Arial, sans-serif;'>
        <table width='100%' style='background-color: $corFundo; padding: 40px 0;'>
            <tr><td align='center'>
                <table width='600' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; max-width: 100%;'>
                    <tr>
                        <td align='center' style='background-color: $corPrimaria; padding: 30px;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>Linka Negócios</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding: 40px; color: #333333; line-height: 1.6;'>
                            <h2 style='color: #212121; margin-top: 0;'>$titulo</h2>
                            <p>$texto</p>
                            <p>Fique de olho na sua caixa de entrada!</p>
                            <br>
                            <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td align='center'>
                                        <a href='https://www.linkanegocios.com.br' style='background-color: $corPrimaria; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;'>Acessar o Site</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align='center' style='background-color: #f8f9fa; padding: 20px; color: #666; font-size: 12px; border-top: 1px solid #eee;'>
                            <p style='margin: 0 0 10px 0;'>
                                Você recebeu este e-mail porque se inscreveu na newsletter da Linka Negócios.
                            </p>
                            <p style='margin: 0;'>
                                Não deseja mais receber? 
                                <a href='$linkUnsub' style='color: $corPrimaria; text-decoration: underline;'>Cancelar inscrição</a>.
                            </p>
                            <p style='margin-top: 10px; color: #999;'>© $anoAtual Linka Negócios.</p>
                        </td>
                    </tr>
                </table>
            </td></tr>
        </table>
    </body>
    </html>
    ";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->CharSet    = 'UTF-8';
        $mail->Host       = 'smtp.titan.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'contato@linkanegocios.com.br';
        $mail->Password   = '*******8';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('contato@linkanegocios.com.br', 'Linka Negócios');
        $mail->addAddress($emailDestino);
        $mail->isHTML(true);
        $mail->Subject = $titulo;
        $mail->Body    = $html;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => 0, 'message' => 'Método não permitido.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || empty($data->email)) {
    http_response_code(400);
    echo json_encode(['success' => 0, 'message' => 'E-mail é obrigatório.']);
    exit;
}

$email = filter_var(trim($data->email), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => 0, 'message' => 'Formato de e-mail inválido.']);
    exit;
}

try {
    $checkQuery = "SELECT id, ativo FROM newsletter WHERE email = :email";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->bindValue(':email', $email, PDO::PARAM_STR);
    $checkStmt->execute();

    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        if ($existingUser['ativo'] == 1) {
            echo json_encode([
                'success' => 1,
                'message' => 'Este e-mail já está inscrito e ativo em nossa lista!'
            ]);
        } else {

            $reactivateQuery = "UPDATE newsletter SET ativo = 1 WHERE id = :id";
            $reactivateStmt = $connection->prepare($reactivateQuery);
            $reactivateStmt->bindValue(':id', $existingUser['id'], PDO::PARAM_INT);

            if ($reactivateStmt->execute()) {
                enviarEmailBoasVindas($email, 'volta');
                http_response_code(200);
                echo json_encode([
                    'success' => 1,
                    'message' => 'Bem-vindo de volta! Sua inscrição foi reativada com sucesso.'
                ]);
            } else {
                throw new Exception("Erro ao reativar inscrição.");
            }
        }
        exit;
    }


    $query = "INSERT INTO newsletter (email, ativo) VALUES (:email, 1)";
    $stmt = $connection->prepare($query);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);

    if ($stmt->execute()) {
        enviarEmailBoasVindas($email, 'novo');
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Inscrição realizada com sucesso! Obrigado.'
        ]);
    } else {
        throw new Exception("Erro ao salvar no banco.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => 0, 'message' => 'Erro interno ao processar inscrição.']);
}
