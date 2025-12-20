<?php
require 'db.php';
require 'auth.php';
requireLogin();

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

$stmt = $pdo->prepare("
    UPDATE comments
    SET status = 0
    WHERE id = ? AND user_id = ?
");
$stmt->execute(array($id, currentUserId()));

header("Location: project_view.php?id=" . $project_id);
exit;