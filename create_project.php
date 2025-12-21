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

    $maxFiles = 5;
    $allowedExt = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'mov');
    $uploadLimit = ini_get('upload_max_filesize'); // ex: "10M"

    // ====== LISTA DE MÍDIAS (URL + UPLOADS) ======
    $media = array();
    $uploadErrors = array();

    // 1) Se o usuário informou uma URL, já entra como primeira mídia
    if ($urlMedia !== '') {
        $media[] = $urlMedia;
    }

    // 2) Processar uploads múltiplos (até 5 no total contando a URL)
    if (!empty($_FILES['media_files']) && is_array($_FILES['media_files']['name'])) {
        $names  = $_FILES['media_files']['name'];
        $tmp    = $_FILES['media_files']['tmp_name'];
        $errors = $_FILES['media_files']['error'];

        for ($i = 0; $i < count($names); $i++) {
            $name  = $names[$i];
            $error = $errors[$i];

            // Nenhum arquivo nesse índice
            if ($name === '' && $error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            // Algum erro de upload
            if ($error !== UPLOAD_ERR_OK) {
                // Erro de tamanho excedido
                if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                    $uploadErrors[] = "O arquivo \"{$name}\" é maior que o limite permitido ({$uploadLimit}).";
                } else {
                    $uploadErrors[] = "Falha ao enviar o arquivo \"{$name}\" (código de erro {$error}).";
                }
                continue;
            }

            // Se já temos 5 mídias, para
            if (count($media) >= $maxFiles) {
                break;
            }

            $origName = basename($name);
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                $uploadErrors[] = "O arquivo \"{$origName}\" possui extensão não permitida.";
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
            } else {
                $uploadErrors[] = "Não foi possível salvar o arquivo \"{$origName}\" no servidor.";
            }
        }
    }

    // ====== VALIDAÇÃO ======
    $errorMsg = '';

    if ($title === '' || $location === '' || $description === '' || $tags === '') {
        $errorMsg = "Preencha todos os campos obrigatórios.";
    }

    // Nenhuma mídia válida (nem URL, nem upload OK)
    if (count($media) === 0) {
        if (!empty($uploadErrors)) {
            // Erros de upload (tamanho, tipo, etc)
            $errorMsg = implode(' ', $uploadErrors);
        } else {
            $errorMsg = $errorMsg ?: "Informe pelo menos uma imagem ou vídeo (URL ou upload).";
        }
    }

    // Limite de 5 mídias
    if (count($media) > $maxFiles) {
        $errorMsg = "Você tentou enviar mais de {$maxFiles} mídias. Envie no máximo {$maxFiles}.";
    }

    if ($errorMsg !== '') {
        // Guarda a mensagem em sessão para exibir na index (ou você pode tratar por GET)
        $_SESSION['post_error_msg'] = $errorMsg;
        header("Location: index.php?post_error=1");
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