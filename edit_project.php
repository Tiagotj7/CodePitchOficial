<?php
require 'db.php';
require 'auth.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Busca projeto
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute(array($id));
$project = $stmt->fetch();

if (!$project || $project['user_id'] != currentUserId()) {
    die("Projeto não encontrado ou sem permissão.");
}

// Recupera lista de mídias atual
$existingMedia = array();
if (!empty($project['media_json'])) {
    $decoded = json_decode($project['media_json'], true);
    if (is_array($decoded)) {
        $existingMedia = $decoded;
    }
}

// Fallback se media_json estiver vazio
if (empty($existingMedia) && !empty($project['image_url'])) {
    $existingMedia = array($project['image_url']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $location    = isset($_POST['location']) ? trim($_POST['location']) : '';
    $urlMedia    = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $tags        = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    // Recomeça a partir das mídias existentes
    $media = $existingMedia;

    // Atualiza a primeira mídia se a URL foi alterada
    if ($urlMedia !== '') {
        if (empty($media)) {
            $media[] = $urlMedia;
        } else {
            // Se a URL mudou em relação à mídia principal antiga
            if ($urlMedia !== $media[0]) {
                $media[0] = $urlMedia;
            }
        }
    } else {
        // Se o campo de URL foi esvaziado, mas existia mídia principal,
        // não removemos as mídias antigas; apenas mantemos o array como está.
        // Se quiser remover tudo quando limpar a URL e não enviar upload,
        // seria necessário outra lógica/UI.
    }

    // ===== TRATAR UPLOADS NOVOS =====
    if (!empty($_FILES['media_files']) && is_array($_FILES['media_files']['name'])) {
        $names  = $_FILES['media_files']['name'];
        $tmp    = $_FILES['media_files']['tmp_name'];
        $errors = $_FILES['media_files']['error'];

        $allowedExt = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'mov');

        for ($i = 0; $i < count($names); $i++) {
            if ($errors[$i] !== UPLOAD_ERR_OK || $names[$i] === '') {
                continue;
            }

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

    // Remove mídias vazias e duplicadas simples
    $media = array_values(array_filter($media, function ($item) {
        return $item !== null && $item !== '';
    }));
    $media = array_values(array_unique($media));

    // Garantir no máximo 5
    if (count($media) > 5) {
        $error = "Máximo de 5 mídias por projeto.";
    }

    if ($title === '' || $location === '' || $description === '' || $tags === '' || count($media) === 0) {
        $error = $error ?: "Preencha todos os campos e tenha pelo menos uma mídia.";
    }

    if ($error === '') {
        $mainMedia = $media[0];
        $mediaJson = json_encode($media);

        $upd = $pdo->prepare("
            UPDATE projects
            SET title = ?, location = ?, image_url = ?, media_json = ?, description = ?, tags = ?
            WHERE id = ? AND user_id = ?
        ");
        $upd->execute(array(
            $title,
            $location,
            $mainMedia,
            $mediaJson,
            $description,
            $tags,
            $id,
            currentUserId()
        ));

        header("Location: project_view.php?id=" . $id . "&update_success=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Editar Projeto - CodePitch</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'header_partial.php'; ?>

<main class="container">
  <section class="projects-section">
    <h2>Editar Projeto</h2>

    <?php if ($error): ?>
      <p style="color:#f85149;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="input-group">
        <input type="text" name="title" value="<?= htmlspecialchars($project['title']) ?>" placeholder=" " required>
        <label>Título do Projeto</label>
      </div>
      <div class="input-group">
        <input type="text" name="location" value="<?= htmlspecialchars($project['location']) ?>" placeholder=" " required>
        <label>Localização</label>
      </div>
      <div class="input-group">
        <input type="url" name="image_url" value="<?= htmlspecialchars($project['image_url']) ?>" placeholder=" ">
        <label>URL de Imagem/Vídeo principal (opcional se usar upload)</label>
      </div>
      <div class="upload-row">
        <input type="file" id="mediaFile" name="media_files[]" accept="image/*,video/*" hidden multiple>
        <button type="button" class="upload-btn" onclick="document.getElementById('mediaFile').click()">
          📁 Enviar novas imagens/vídeos (até 5 no total)
        </button>
        <span id="uploadFileName" class="upload-file-name"></span>
      </div>
      <div class="input-group">
        <textarea name="description" placeholder=" " required><?= htmlspecialchars($project['description']) ?></textarea>
        <label>Descrição do Projeto</label>
      </div>
      <div class="input-group">
        <input type="text" name="tags" value="<?= htmlspecialchars($project['tags']) ?>" placeholder=" " required>
        <label>Tags (separadas por vírgula)</label>
      </div>

      <?php if (!empty($existingMedia)): ?>
        <p style="margin-bottom:0.5rem;">Mídias atuais (serão mantidas se você não remover/alterar manualmente via URL):</p>
        <div class="project-media-grid">
          <?php
          $maxPreview = min(5, count($existingMedia));
          for ($i = 0; $i < $maxPreview; $i++):
              $m = $existingMedia[$i];
              $ext = strtolower(pathinfo($m, PATHINFO_EXTENSION));
              $isVideo = in_array($ext, array('mp4', 'webm', 'ogg', 'mov'));
          ?>
            <div class="project-media-item">
              <?php if ($isVideo): ?>
                <video controls>
                  <source src="<?= htmlspecialchars($m) ?>" type="video/<?= $ext === 'ogv' ? 'ogg' : $ext ?>">
                </video>
              <?php else: ?>
                <img src="<?= htmlspecialchars($m) ?>" alt="Mídia atual"
                     onerror="this.src='https://via.placeholder.com/150x90?text=Mídia'">
              <?php endif; ?>
            </div>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

      <button type="submit" class="btn" style="margin-top:1rem;">Salvar Alterações</button>
    </form>
  </section>
</main>

<script src="assets/js/script.js"></script>
</body>
</html>