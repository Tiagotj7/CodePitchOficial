<?php
require 'db.php';
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($name === '' || $email === '' || $password === '') {
        header("Location: index.php?register_error=1");
        exit;
    }

    // Verifica se já existe usuário com este email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(array($email));
    if ($stmt->fetch()) {
        header("Location: index.php?register_error=2");
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute(array($name, $email, $hash));

    header("Location: index.php?register_success=1");
    exit;
}

header("Location: login.php");