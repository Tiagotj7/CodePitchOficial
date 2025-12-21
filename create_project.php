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

    $maxFiles   = 5;
    $allowedExt = array('jpg', 'jpeg', 'png', 'gif');
    $uploadLimit = ini_get('upload_max_filesize'); // ex: "10M"

    // ====== LISTA DE MÍDIAS (URL + UPLOADS) ======
    $media          = array();
    $uploadErrors   = array();
    $uploadFeedback = array(); // para mostrar ✅ / ❌ por arquivo

    // 1) Se o usuário informou uma URL, já entra como primeira mídia
    if ($urlMedia !== '') {
        $media[] = $urlMedia;
        $uploadFeedback[] = array(
            'name'   => $urlMedia,
            'status' => 'url',
            'msg'    => 'URL adicionada como mídia principal.'
        );
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
                if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                    $msg = "O arquivo \"{$name}\" é maior que o limite permitido ({$uploadLimit}).";
                    $uploadErrors[] = $msg;
                    $uploadFeedback[] = array(
                        'name'   => $name,
                        'status' => 'too_big',
                        'msg'    => $msg,
                    );
                } else {
                    $msg = "Falha ao enviar o arquivo \"{$name}\" (código de erro {$error}).";
                    $uploadErrors[] = $msg;
                    $uploadFeedback[] = array(
                        'name'   => $name,
                        'status' => 'error',
                        'msg'    => $msg,
                    );
                }
                continue;
            }

            // Se já temos 5 mídias, para
            if (count($media) >= $maxFiles) {
                $msg = "Você tentou enviar mais de {$maxFiles} arquivos. Arquivo \"{$name}\" foi ignorado.";
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
                $msg = "O arquivo \"{$origName}\" possui extensão não permitida.";
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
                    'msg'    => 'Arquivo enviado com sucesso.',
                );
            } else {
                $msg = "Não foi possível salvar o arquivo \"{$origName}\" no servidor.";
                $uploadErrors[] = $msg;
                $uploadFeedback[] = array(
                    'name'   => $origName,
                    'status' => 'error',
                    'msg'    => $msg,
                );
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
        // Guarda mensagens em sessão para exibir na index
        $_SESSION['post_error_msg']  = $errorMsg;
        $_SESSION['upload_feedback'] = $uploadFeedback;
        $_SESSION['max_upload_size'] = $uploadLimit;

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

    // Limpa qualquer feedback antigo de sessão para não reaparecer
    unset($_SESSION['post_error_msg'], $_SESSION['upload_feedback'], $_SESSION['max_upload_size']);

    header("Location: index.php?post_success=1");
    exit;
}

header("Location: index.php");