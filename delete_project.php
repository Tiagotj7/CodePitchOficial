<?php
require 'db.php';
require 'auth.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: project.php");
    exit;
}

try {
    if (isAdmin()) {
        // Admin pode deletar qualquer projeto
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute(array($id));
    } else {
        // Usuário comum só deleta o que é dele
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute(array($id, currentUserId()));
    }
} catch (PDOException $e) {
    error_log('ERRO DELETE PROJECT: ' . $e->getMessage());
}

header("Location: project.php?delete_success=1");
exit;