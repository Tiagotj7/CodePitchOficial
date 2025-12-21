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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $location    = isset($_POST['location']) ? trim($_POST['location']) : '';
    $image_url   = isset($_POST['image_url']) ? trim($_POST['image_url']) : $project['image_url'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $tags        = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    // ===== TRATAR UPLOAD DE IMAGEM/VÍDEO =====
    $uploadedPath = '';

    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $tmpName  = $_FILES['media_file']['tmp_name'];
        $origName = basename($_FILES['media_file']['name']);
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'mov');

        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/uploads/';
            $publicDir = 'uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName = uniqid('media_', true) . '.' . $ext;
            $dest    = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, $dest)) {
                $uploadedPath = $publicDir . $newName;
            }
        }
    }

    if ($uploadedPath !== '') {
        $image_url = $uploadedPath;
    }

    // ===== VALIDAÇÃO =====
    if ($title === '' || $location === '' || $description === '' || $tags === '' || $image_url === '') {
        $error = "Preencha todos os campos. É necessário informar uma URL ou enviar um arquivo de mídia.";
    } else {
        $upd = $pdo->prepare("
            UPDATE projects
            SET title = ?, location = ?, image_url = ?, description = ?, tags = ?
            WHERE id = ? AND user_id = ?
        ");
        $upd->execute(array(
            $title,
            $location,
            $image_url,
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
        <label>URL de Imagem/Vídeo (opcional se usar upload)</label>
      </div>
      <div class="upload-row">
        <input type="file" id="mediaFile" name="media_file" accept="image/*,video/*" hidden>
        <button type="button" class="upload-btn" onclick="document.getElementById('mediaFile').click()">
          📁 Enviar nova imagem/vídeo
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
      <button type="submit" class="btn">Salvar Alterações</button>
    </form>
  </section>
</main>

<script src="assets/js/script.js"></script>
</body>
</html>