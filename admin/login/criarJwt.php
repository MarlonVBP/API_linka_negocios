<?php
require 'base64url_encode.php';

function criar_jwt($ID_USER, $TIPO_USER, $LOGIN_USER)
{
    $secret = '#Frngoclimao20';

    $header = json_encode(['alg' => 'HS256', 'type' => 'JWT']);
    $header_encoded = base64url_encode($header);

    $exp = strtotime('+15 days');
    $payload = json_encode([
        'ID_USER' => $ID_USER,
        'TIPO_USER' => $TIPO_USER,
        'LOGIN_USER' => $LOGIN_USER,
        'exp' => $exp
    ]);
    $payload_encoded = base64url_encode($payload);

    $signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $secret, true);
    $signature_encoded = base64url_encode($signature);

    return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
}
