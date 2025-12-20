<?php
require 'db.php';
require 'auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $location    = isset($_POST['location']) ? trim($_POST['location']) : '';
    $image_url   = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $tags        = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    if ($title === '' || $location === '' || $image_url === '' || $description === '' || $tags === '') {
        header("Location: index.php?post_error=1");
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO projects (user_id, title, location, image_url, description, tags)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute(array(
        currentUserId(),
        $title,
        $location,
        $image_url,
        $description,
        $tags
    ));

    header("Location: index.php?post_success=1");
    exit;
}

header("Location: index.php");