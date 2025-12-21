<?php
require 'db.php';
require 'auth.php';
requireLogin();

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

if ($id <= 0 || $project_id <= 0) {
    header("Location: project_view.php?id=" . $project_id);
    exit;
}

try {
    if (isAdmin()) {
        // Admin pode excluir qualquer comentário
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute(array($id));
    } else {
        // Usuário comum só pode apagar o próprio comentário
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute(array($id, currentUserId()));
    }
} catch (PDOException $e) {
    error_log('ERRO DELETE COMMENT: ' . $e->getMessage());
}

header("Location: project_view.php?id=" . $project_id);
exit;