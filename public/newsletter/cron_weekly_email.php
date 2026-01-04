<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Acesso Negado: Este script so pode ser executado via Cron Job (Terminal).");
}

set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../cors.php';
include '../../conn.php';
require '../../vendor/autoload.php';

try {
    $queryPosts = "SELECT id, titulo, descricao, url_imagem, views, criado_em 
                   FROM postagens 
                   WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                   ORDER BY views DESC, criado_em DESC
                   LIMIT 5";

    $stmtPosts = $connection->prepare($queryPosts);
    $stmtPosts->execute();
    $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

    if (count($posts) === 0) {
        echo "Nenhum post encontrado nos últimos 30 dias.";
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao buscar posts: " . $e->getMessage());
}

$mesAtual = date('m/Y');
$anoAtual = date('Y');

$fontFamily = "'Helvetica Neue', Helvetica, Arial, sans-serif";
$corPrimaria = "#b22828";
$corFundo = "#f4f4f4";
$corTexto = "#333333";
$corCinza = "#666666";

$emailContent = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Newsletter Linka Negócios</title>
</head>
<body style='margin: 0; padding: 0; background-color: $corFundo; font-family: $fontFamily;'>
    
    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: $corFundo; padding: 40px 0;'>
        <tr>
            <td align='center'>
                
                <table border='0' cellpadding='0' cellspacing='0' width='600' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 600px; width: 100%;'>
                    
                    <tr>
                        <td align='center' style='padding: 40px 0 30px 0; background-color: #ffffff; border-bottom: 3px solid $corPrimaria;'>
                            <h1 style='color: $corPrimaria; margin: 0; font-size: 28px; text-transform: uppercase; letter-spacing: 1px;'>Linka Negócios</h1>
                            <p style='color: $corCinza; font-size: 14px; margin-top: 5px; text-transform: uppercase;'>Destaques do Mês • $mesAtual</p>
                        </td>
                    </tr>

                    <tr>
                        <td style='padding: 30px 40px; color: $corTexto; font-size: 16px; line-height: 1.6;'>
                            <p style='margin: 0;'>Olá! 👋</p>
                            <p style='margin-top: 10px;'>Selecionamos os <strong>5 artigos mais lidos</strong> pela nossa comunidade este mês. Conteúdo direto ao ponto para alavancar seu conhecimento.</p>
                        </td>
                    </tr>
";

$rank = 1;
foreach ($posts as $post) {
    $linkPost = "https://www.linkanegocios.com.br/read-more/" . $post['id'];

    $imgSrc = $post['url_imagem'];
    if (strpos($imgSrc, 'http') === false) {
        $imgSrc = "https://linkanegocios.com.br/api/public/posts/" . $imgSrc;
    }

    $emailContent .= "
                    <tr>
                        <td style='padding: 0 40px 40px 40px;'>
                            <div style='border: 1px solid #eeeeee; border-radius: 8px; overflow: hidden;'>
                                
                                <a href='$linkPost' style='text-decoration: none; display: block;'>
                                    <img src='$imgSrc' alt='{$post['titulo']}' style='width: 100%; height: auto; display: block; border-bottom: 1px solid #eeeeee;'>
                                </a>

                                <div style='padding: 25px;'>
                                    <span style='background-color: $corPrimaria; color: #ffffff; font-size: 11px; font-weight: bold; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;'>
                                        TOP #$rank
                                    </span>

                                    <h3 style='margin: 15px 0 10px 0; font-size: 20px; line-height: 1.4; color: $corTexto;'>
                                        <a href='$linkPost' style='text-decoration: none; color: $corTexto;'>{$post['titulo']}</a>
                                    </h3>

                                    <p style='color: $corCinza; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;'>
                                        " . substr(strip_tags($post['descricao']), 0, 110) . "...
                                    </p>

                                    <table border='0' cellpadding='0' cellspacing='0' style='margin-top: 10px;'>
                                        <tr>
                                            <td align='center' bgcolor='$corPrimaria' style='border-radius: 6px;'>
                                                <a href='$linkPost' style='background-color: #b22828; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-size: 14px;'>
                                                    Ler Artigo Completo
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    </div>
                            </div>
                        </td>
                    </tr>
    ";
    $rank++;
}

$emailContent .= "
                    <tr>
                        <td align='center' style='padding: 30px; background-color: #f9f9f9; border-top: 1px solid #eeeeee;'>
                            <p style='margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: $corTexto;'>Linka Negócios</p>
                            <p style='margin: 0; font-size: 12px; color: #999999; line-height: 1.5;'>
                                © $anoAtual Todos os direitos reservados.
                            </p>
                            <p style='margin-top: 15px; font-size: 12px;'>
                                <a href='https://www.linkanegocios.com.br' style='color: $corPrimaria; text-decoration: none;'>Visitar Site</a>
                            </p>
                        </td>
                    </tr>

                </table>
                <div style='height: 40px;'></div>

            </td>
        </tr>
    </table>

</body>
</html>
";

try {
    $querySubs = "SELECT email FROM newsletter WHERE ativo = 1";
    $stmtSubs = $connection->prepare($querySubs);
    $stmtSubs->execute();
    $subscribers = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar assinantes: " . $e->getMessage());
}

$mail = new PHPMailer(true);
$mail->SMTPDebug = 0;

try {
    $mail->isSMTP();
    $mail->CharSet = 'UTF-8';
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '7efcfa001@smtp-brevo.com';
    $mail->Password   = 'pzqFRB725kavVNSm';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->setFrom('marlonvicctor13@gmail.com', 'Linka Negócios');
    $mail->isHTML(true);
    $mail->Subject = 'Os Artigos Mais Lidos do Mês - Linka Negócios';
    $mail->Body    = $emailContent;
    $mail->AltBody = 'Veja os top 5 artigos do mês em linkanegocios.com.br';

    $enviados = 0;
    if (count($subscribers) > 0) {
        foreach ($subscribers as $sub) {
            try {
                $mail->addAddress($sub['email']);
                $mail->send();
                $enviados++;
                $mail->clearAddresses();
            } catch (Exception $e) {
                $mail->clearAddresses();
                continue;
            }
        }
        echo "Newsletter MENSAL enviada com sucesso para $enviados assinantes.";
    } else {
        echo "Nenhum assinante ativo.";
    }
} catch (Exception $e) {
    echo "Erro geral: {$mail->ErrorInfo}";
}
