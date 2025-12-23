<?php
require 'auth.php';
require 'db.php';

$clientId     = app_env('GOOGLE_CLIENT_ID');
$clientSecret = app_env('GOOGLE_CLIENT_SECRET');
$redirectUri  = app_env('GOOGLE_REDIRECT_URI');

if (!$clientId || !$clientSecret || !$redirectUri) {
    die('Configuração de Google OAuth ausente. Verifique o .env');
}

// Verifica state (CSRF)
if (empty($_GET['state']) || empty($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
    unset($_SESSION['oauth2state']);
    die('Requisição inválida (state). Tente novamente.');
}

if (!isset($_GET['code'])) {
    die('Código de autorização ausente.');
}

$code = $_GET['code'];

// 1) Troca code por access_token
$tokenResponse = null;
$tokenUrl = 'https://oauth2.googleapis.com/token';

$postData = [
    'code'          => $code,
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri'  => $redirectUri,
    'grant_type'    => 'authorization_code',
];

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response   = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError  = curl_error($ch);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    error_log("GOOGLE TOKEN ERROR: HTTP $httpCode - $curlError - $response");
    die('Falha ao autenticar com o Google. Tente novamente.');
}

$tokenResponse = json_decode($response, true);
$accessToken   = $tokenResponse['access_token'] ?? null;

if (!$accessToken) {
    die('Access token não encontrado na resposta do Google.');
}

// 2) Busca dados do usuário (userinfo)
$ch = curl_init('https://openidconnect.googleapis.com/v1/userinfo');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$userInfoResponse = curl_exec($ch);
$httpCode         = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError        = curl_error($ch);
curl_close($ch);

if ($httpCode !== 200 || !$userInfoResponse) {
    error_log("GOOGLE USERINFO ERROR: HTTP $httpCode - $curlError - $userInfoResponse");
    die('Falha ao obter dados do Google. Tente novamente.');
}

$userInfo = json_decode($userInfoResponse, true);

// Campos principais
$googleId  = $userInfo['sub']    ?? null;
$email     = $userInfo['email']  ?? null;
$name      = $userInfo['name']   ?? null;
$verified  = $userInfo['email_verified'] ?? false;

if (!$googleId || !$email) {
    die('Não foi possível identificar sua conta Google.');
}

// 3) Verifica se já existe usuário com este google_id
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
    $stmt->execute([$googleId]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('ERRO SELECT USER BY GOOGLE_ID: ' . $e->getMessage());
    $user = false;
}

if (!$user) {
    // Se não achou por google_id, tenta por email
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        error_log('ERRO SELECT USER BY EMAIL: ' . $e->getMessage());
        $user = false;
    }

    if ($user) {
        // Atualiza esse usuário com o google_id
        try {
            $upd = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $upd->execute([$googleId, $user['id']]);
        } catch (PDOException $e) {
            error_log('ERRO UPDATE GOOGLE_ID: ' . $e->getMessage());
        }
    } else {
        // Cria novo usuário
        $randomPassword = bin2hex(random_bytes(8)); // só para preencher password_hash
        $passwordHash   = password_hash($randomPassword, PASSWORD_DEFAULT);
        $status         = 1;

        try {
            $ins = $pdo->prepare("
                INSERT INTO users (name, email, password_hash, status, is_admin, bio, github, linkedin, twitter, website, google_id)
                VALUES (?, ?, ?, ?, 0, NULL, NULL, NULL, NULL, NULL, ?)
            ");
            $ins->execute([
                $name ?: explode('@', $email)[0],
                $email,
                $passwordHash,
                $status,
                $googleId
            ]);

            $newId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$newId]);
            $user = $stmt->fetch();

        } catch (PDOException $e) {
            error_log('ERRO INSERT USER FROM GOOGLE: ' . $e->getMessage());
            die('Erro ao criar usuário com a conta Google.');
        }
    }
}

// 4) Faz login na sessão
if ($user) {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_admin']   = isset($user['is_admin']) ? (int)$user['is_admin'] : 0;

    header("Location: index.php");
    exit;
}

die('Não foi possível completar o login com o Google.');