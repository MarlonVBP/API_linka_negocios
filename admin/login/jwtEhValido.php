<?php

require_once __DIR__ . '/base64url_encode.php';
require_once __DIR__ . '/base64url_decode.php';

function jwt_eh_valido($token)
{
    $parts = explode('.', $token);

    if (count($parts) !== 3) {
        return false;
    }

    $secret = '#Frngoclimao20';
    $header = $parts[0];
    $payload = $parts[1];
    $signature_provided = $parts[2];

    $signature_generated = base64url_encode(hash_hmac('sha256', $header . '.' . $payload, $secret, true));

    if ($signature_provided !== $signature_generated) {
        return false;
    }

    $infos_token = json_decode(base64url_decode($payload));

    if (isset($infos_token->exp) && time() < (int)$infos_token->exp) {
        return true;
    }

    return false;
}
