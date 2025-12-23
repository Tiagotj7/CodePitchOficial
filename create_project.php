<?php
require 'db.php';
require 'auth.php';
requireLogin();

// Helper: verifica se URL termina com extensão de imagem permitida
function isAllowedImageUrl($url, $allowedExt) {
    $path = parse_url($url, PHP_URL_PATH);
    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return $ext !== '' && in_array($ext, $allowedExt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $location    = isset($_POST['location']) ? trim($_POST['location']) : '';
    $urlMedia    = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $tags        = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    $maxFiles    = 5;
    $allowedExt  = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $uploadLimit = ini_get('upload_max_filesize'); // ex: "10M"

    $media          = array();   // lista de caminhos de mídias (pode ficar vazia)
    $uploadErrors   = array();
    $uploadFeedback = array();

    // 1) URL (se for imagem válida)
    if ($urlMedia !== '') {
        if (isAllowedImageUrl($urlMedia, $allowedExt)) {
            $media[] = $urlMedia;
            $uploadFeedback[] = array(
                'name'   => $urlMedia,
                'status' => 'url',
                'msg'    => 'URL de imagem adicionada.'
            );
        } else {
            $msg = "A URL informada não parece ser uma imagem válida (use .jpg, .jpeg, .png, .gif ou .webp).";
            $uploadErrors[] = $msg;
            $uploadFeedback[] = array(
                'name'   => $urlMedia,
                'status' => 'error',
                'msg'    => $msg,
            );
        }
    }

    // 2) Upload múltiplo de IMAGENS (opcional)
    if (!empty($_FILES['media_files']) && is_array($_FILES['media_files']['name'])) {
        $names  = $_FILES['media_files']['name'];
        $tmp    = $_FILES['media_files']['tmp_name'];
        $errors = $_FILES['media_files']['error'];

        for ($i = 0; $i < count($names); $i++) {
            $name  = $names[$i];
            $error = $errors[$i];

            if ($name === '' && $error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                    $msg = "A imagem \"{$name}\" é maior que o limite permitido ({$uploadLimit}).";
                    $uploadErrors[] = $msg;
                    $uploadFeedback[] = array(
                        'name'   => $name,
                        'status' => 'too_big',
                        'msg'    => $msg,
                    );
                } else {
                    $msg = "Falha ao enviar a imagem \"{$name}\" (código de erro {$error}).";
                    $uploadErrors[] = $msg;
                    $uploadFeedback[] = array(
                        'name'   => $name,
                        'status' => 'error',
                        'msg'    => $msg,
                    );
                }
                continue;
            }

            if (count($media) >= $maxFiles) {
                $msg = "Você tentou enviar mais de {$maxFiles} imagens. A imagem \"{$name}\" foi ignorada.";
                $uploadErrors[] = $msg;
                $uploadFeedback[] = array(
                    'name'   => $name,
                    'status' => 'ignored',
                    'msg'    => $msg,
                );
                break;
            }

            $origName = basename($name);
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                $msg = "A imagem \"{$origName}\" possui extensão não permitida. Use apenas: " . implode(', ', $allowedExt) . ".";
                $uploadErrors[] = $msg;
                $uploadFeedback[] = array(
                    'name'   => $origName,
                    'status' => 'error',
                    'msg'    => $msg,
                );
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
                $path = $publicDir . $newName;
                $media[] = $path;

                $uploadFeedback[] = array(
                    'name'   => $origName,
                    'status' => 'ok',
                    'msg'    => 'Imagem enviada com sucesso.',
                );
            } else {
                $msg = "Não foi possível salvar a imagem \"{$origName}\" no servidor.";
                $uploadErrors[] = $msg;
                $uploadFeedback[] = array(
                    'name'   => $origName,
                    'status' => 'error',
                    'msg'    => $msg,
                );
            }
        }
    }

    // ===== VALIDAÇÃO GERAL (sem exigir imagem) =====
    $errorMsg = '';

    if ($title === '' || $location === '' || $description === '' || $tags === '') {
        $errorMsg = "Preencha todos os campos obrigatórios.";
    }

    if (count($media) > $maxFiles) {
        $errorMsg = "Você tentou enviar mais de {$maxFiles} imagens. Envie no máximo {$maxFiles}.";
    }

    if ($errorMsg !== '') {
        $_SESSION['post_error_msg']  = $errorMsg;
        $_SESSION['upload_feedback'] = $uploadFeedback;
        $_SESSION['max_upload_size'] = $uploadLimit;

        header("Location: index.php?post_error=1");
        exit;
    }

    // Pode não haver mídia nenhuma: media pode ser []
    $mainMedia = $media[0] ?? '';               // imagem principal ou vazio
    $mediaJson = json_encode($media);          // [] se vazio

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

    unset($_SESSION['post_error_msg'], $_SESSION['upload_feedback'], $_SESSION['max_upload_size']);

    header("Location: index.php?post_success=1");
    exit;
}

header("Location: index.php");