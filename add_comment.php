<?php
require 'db.php';
require 'auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
    $comment    = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($project_id <= 0 || $comment === '') {
        header("Location: project_view.php?id=" . $project_id . "&comment_error=1");
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO comments (project_id, user_id, comment)
        VALUES (?, ?, ?)
    ");
    $stmt->execute(array(
        $project_id,
        currentUserId(),
        $comment
    ));

    header("Location: project_view.php?id=" . $project_id . "&comment_success=1");
    exit;
}

header("Location: index.php");