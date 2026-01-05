<?php

header('Content-Type: text/html; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../conn.php';
require '../../vendor/autoload.php';

$acao = 'perguntar';
$mensagem_final = "";
$email = "";
$email_encoded = "";

if (isset($_GET['e']) && !empty($_GET['e'])) {
    $email_encoded = $_GET['e'];
    $email = base64_decode($email_encoded);


    if (isset($_GET['confirm']) && $_GET['confirm'] == 'sim') {
        $acao = 'cancelado';

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $query = "UPDATE newsletter SET ativo = 0 WHERE email = :email";
                $stmt = $connection->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                $mensagem_final = "Cancelamento efetuado.";


                $mail = new PHPMailer(true);
                try {
                    $corPrimaria = "#b22828";
                    $anoAtual = date('Y');

                    $htmlCancelamento = "
                    <!DOCTYPE html>
                    <html lang='pt-BR'>
                    <body style='margin:0; padding:0; background-color:#f4f4f4; font-family: Helvetica, Arial, sans-serif;'>
                        <table width='100%' style='background-color:#f4f4f4; padding:40px 0;'><tr><td align='center'>
                            <table width='600' style='background-color:#ffffff; border-radius:8px; overflow:hidden; max-width:100%;'>
                                <tr><td align='center' style='background-color:#333; padding:20px;'>
                                    <h1 style='color:#fff; margin:0; font-size:20px;'>Linka Negócios</h1>
                                </td></tr>
                                <tr><td style='padding:40px; color:#333;'>
                                    <h2 style='margin-top:0;'>Inscrição Cancelada</h2>
                                    <p>Confirmamos que o seu e-mail <strong>$email</strong> foi removido da nossa lista de envios da Newsletter.</p>
                                    <p>Respeitamos sua decisão e seus dados.</p>
                                    
                                    <hr style='border:0; border-top:1px solid #eee; margin:20px 0;'>
                                    
                                    <p style='font-size:14px; color:#666; margin-bottom:15px;'>
                                        Foi um engano ou mudou de ideia? Você pode se reinscrever a qualquer momento para voltar a receber nossos conteúdos.
                                    </p>
                                    
                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                        <tr>
                                            <td align='center'>
                                                <a href='https://www.linkanegocios.com.br' style='background-color: #eee; color: $corPrimaria; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; border: 1px solid #ccc; display: inline-block;'>Inscrever-se Novamente</a>
                                            </td>
                                        </tr>
                                    </table>
                                </td></tr>
                                <tr>
                                    <td align='center' style='background-color: #f8f9fa; padding: 15px; color: #999; font-size: 11px;'>
                                        © $anoAtual Linka Negócios.
                                    </td>
                                </tr>
                            </table>
                        </td></tr></table>
                    </body></html>";

                    $mail->isSMTP();
                    $mail->CharSet    = 'UTF-8';
                    $mail->Host       = 'smtp.titan.email';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'contato@linkanegocios.com.br';
                    $mail->Password   = '*******8';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    $mail->setFrom('contato@linkanegocios.com.br', 'Linka Negócios');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Confirmação de Cancelamento - Linka Negócios';
                    $mail->Body    = $htmlCancelamento;
                    $mail->send();
                } catch (Exception $e) {
                }
            } catch (PDOException $e) {
                $mensagem_final = "Erro ao processar. Tente novamente.";
            }
        }
    }
} else {
    die("Link inválido ou expirado.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Inscrição - Linka Negócios</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        .card {
            background: white;
            padding: 60px 40px;
            border-radius: 6px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 500px;
            width: 90%;
            border-top: 4px solid #b22828;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
        }

        p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 40px;
            font-size: 15px;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn-primary {
            display: block;
            padding: 14px 20px;
            background-color: #b22828;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 600;
            border: 1px solid #b22828;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #8f1f1f;
            border-color: #8f1f1f;
        }

        .btn-secondary {
            display: block;
            padding: 14px 20px;
            background-color: transparent;
            color: #666;
            text-decoration: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: normal;
            border: 1px solid #ccc;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background-color: #f4f4f4;
            color: #333;
            border-color: #999;
        }

        @media (min-width: 400px) {

            .btn-primary,
            .btn-secondary {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>

<body>

    <div class="card">

        <?php if ($acao == 'perguntar'): ?>
            <h1>Gerenciar sua Inscrição</h1>
            <p>
                Identificamos sua solicitação de descadastramento da nossa <strong>Newsletter</strong>.
                <br><br>
                Ao sair, você deixará de receber nossos resumos mensais com análises de mercado, tendências de gestão e artigos exclusivos.
                <br><br>
                Gostaria de reconsiderar e manter seu acesso a este conteúdo?
            </p>

            <div class="actions">
                <a href="https://www.linkanegocios.com.br" class="btn-primary">
                    Manter minha inscrição ativa
                </a>

                <a href="?e=<?php echo $email_encoded; ?>&confirm=sim" class="btn-secondary">
                    Confirmar cancelamento
                </a>
            </div>

        <?php else: ?>
            <h1>Inscrição Cancelada</h1>
            <p>
                Sua inscrição foi cancelada com sucesso e você não receberá mais a nossa newsletter.
                <br><br>
                <strong>Quando quiser voltar, estaremos aqui!</strong>
                <br><br>
                Para receber nossas atualizações novamente, basta acessar nosso site e cadastrar seu e-mail no formulário de inscrição.
                <br><br>
                Estamos ansiosos pelo seu retorno.
            </p>

            <div class="actions">
                <a href="https://www.linkanegocios.com.br" class="btn-primary">
                    Voltar para o site
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>