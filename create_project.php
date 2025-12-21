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

    // ====== TRATAR UPLOAD DE IMAGEM/VÍDEO ======
    $uploadedPath = '';

    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['media_file']['tmp_name'];
        $origName = basename($_FILES['media_file']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        // Tipos permitidos
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'mov');

        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/uploads/';
            $publicDir = 'uploads/';

            // Garante que a pasta exista
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName = uniqid('media_', true) . '.' . $ext;
            $dest = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, $dest)) {
                $uploadedPath = $publicDir . $newName; // caminho usado na aplicação
            }
        }
    }

    // Se fez upload com sucesso, usa o caminho do upload
    if ($uploadedPath !== '') {
        $image_url = $uploadedPath;
    }

    // ====== VALIDAÇÃO ======
    // Exigir pelo menos UM: URL OU upload (ou os dois)
    if ($title === '' || $location === '' || $description === '' || $tags === '' || $image_url === '') {
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