<?php
require 'auth.php'; // inicia sessão
require 'db.php';   // carrega .env e conecta no banco, se precisar depois

$clientId    = app_env('GOOGLE_CLIENT_ID');
$redirectUri = app_env('GOOGLE_REDIRECT_URI');

if (!$clientId || !$redirectUri) {
    die('Configuração de Google OAuth ausente. Verifique o .env');
}

// Scopes que queremos: id, email, nome
$scope = urlencode('openid email profile');

// Proteção CSRF com state
$state = bin2hex(random_bytes(16));
$_SESSION['oauth2state'] = $state;

$params = [
    'client_id'     => $clientId,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'online',
    'include_granted_scopes' => 'true',
    'state'         => $state,
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

header('Location: ' . $authUrl);
exit;