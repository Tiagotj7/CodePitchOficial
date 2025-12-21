<?php
require 'db.php';
require 'auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $location    = isset($_POST['location']) ? trim($_POST['location']) : '';
    $urlMedia    = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $tags        = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    // ====== LISTA DE MÍDIAS (URL + UPLOADS) ======
    $media = array();

    // 1) Se o usuário informou uma URL, já entra como primeira mídia
    if ($urlMedia !== '') {
        $media[] = $urlMedia;
    }

    // 2) Processar uploads múltiplos (até 5 no total contando a URL)
    if (!empty($_FILES['media_files']) && is_array($_FILES['media_files']['name'])) {
        $names  = $_FILES['media_files']['name'];
        $tmp    = $_FILES['media_files']['tmp_name'];
        $errors = $_FILES['media_files']['error'];

        $allowedExt = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'mov');

        for ($i = 0; $i < count($names); $i++) {
            if ($errors[$i] !== UPLOAD_ERR_OK || $names[$i] === '') {
                continue;
            }

            // Se já tiver 5 mídias, para
            if (count($media) >= 5) {
                break;
            }

            $origName = basename($names[$i]);
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                continue;
            }

            $uploadDir = __DIR__ . '/uploads/';
            $publicDir = 'uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName = uniqid('media_', true) . '.' . $ext;
            $dest    = $uploadDir . $newName;

            if (move_uploaded_file($tmp[$i], $dest)) {
                $media[] = $publicDir . $newName;
            }
        }
    }

    // ====== VALIDAÇÃO ======
    // Pelo menos uma mídia (URL ou upload)
    if ($title === '' || $location === '' || $description === '' || $tags === '' || count($media) === 0) {
        header("Location: index.php?post_error=1");
        exit;
    }

    // Limite de 5 mídias
    if (count($media) > 5) {
        header("Location: index.php?post_error=max_files");
        exit;
    }

    // Define a mídia principal (primeiro item) e JSON com todas
    $mainMedia  = $media[0];
    $mediaJson  = json_encode($media);

    $stmt = $pdo->prepare("
        INSERT INTO projects (user_id, title, location, image_url, media_json, description, tags)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute(array(
        currentUserId(),
        $title,
        $location,
        $mainMedia,
        $mediaJson,
        $description,
        $tags
    ));

    header("Location: index.php?post_success=1");
    exit;
}

header("Location: index.php");