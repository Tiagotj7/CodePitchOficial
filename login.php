<?php
require 'db.php';
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        header("Location: index.php?login_error=1");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 1");
    $stmt->execute(array($email));
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header("Location: index.php?login_error=1");
        exit;
    }

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_admin']   = isset($user['is_admin']) ? (int)$user['is_admin'] : 0;

    header("Location: index.php");
    exit;
}

header("Location: index.php");