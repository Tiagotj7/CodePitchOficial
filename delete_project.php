<?php
require 'db.php';
require 'auth.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    UPDATE projects
    SET status = 0
    WHERE id = ? AND user_id = ?
");
$stmt->execute(array($id, currentUserId()));

header("Location: project.php?delete_success=1");
exit;