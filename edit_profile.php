<?php
require 'db.php';
require 'auth.php';
requireLogin();

// id do perfil a editar
if (isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
} else {
    $editId = currentUserId();
}

if (!$editId) {
    header("Location: index.php");
    exit;
}

// Só o próprio usuário ou admin pode editar
if (!isAdmin() && currentUserId() !== $editId) {
    die("Você não tem permissão para editar este perfil.");
}

// Busca usuário
$stmt = $pdo->prepare("
    SELECT id, name, email, bio, github, linkedin, instagram, website
    FROM users
    WHERE id = ? AND status = 1
");
$stmt->execute(array($editId));
$userProfile = $stmt->fetch();

if (!$userProfile) {
    die("Usuário não encontrado ou desativado.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = isset($_POST['name']) ? trim($_POST['name']) : '';
    $bio      = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    $github   = isset($_POST['github']) ? trim($_POST['github']) : '';
    $linkedin = isset($_POST['linkedin']) ? trim($_POST['linkedin']) : '';
    $instagram  = isset($_POST['instagram']) ? trim($_POST['instagram']) : '';
    $website  = isset($_POST['website']) ? trim($_POST['website']) : '';

    if ($name === '') {
        $error = "O nome não pode ficar em branco.";
    } else {
        $upd = $pdo->prepare("
            UPDATE users
            SET name = ?, bio = ?, github = ?, linkedin = ?, instagram = ?, website = ?
            WHERE id = ?
        ");
        $upd->execute(array(
            $name,
            $bio,
            $github,
            $linkedin,
            $instagram,
            $website,
            $editId
        ));

        // Se o usuário estiver editando o próprio perfil, atualiza o nome na sessão
        if (currentUserId() === $editId) {
            $_SESSION['user_name'] = $name;
        }

        $success = "Perfil atualizado com sucesso!";
        // Atualiza dados em memória
        $userProfile['name']     = $name;
        $userProfile['bio']      = $bio;
        $userProfile['github']   = $github;
        $userProfile['linkedin'] = $linkedin;
        $userProfile['instagram']  = $instagram;
        $userProfile['website']  = $website;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Editar Perfil - CodePitch</title>

  <link rel="apple-touch-icon" sizes="180x180" href="src/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="src/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="src/favicon-16x16.png">
  <link rel="manifest" href="src/site.webmanifest">
  <link rel="mask-icon" href="src/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="src/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-config" content="/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">

  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'header_partial.php'; ?>

<main class="page-main">
  <section class="profile-section">
    <h2>Editar Perfil</h2>

    <?php if ($error): ?>
      <p style="color:#f85149;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p style="color:#2ea043;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" class="profile-form">
      <div class="input-group">
        <input type="text" name="name" value="<?= htmlspecialchars($userProfile['name']) ?>" placeholder=" " required>
        <label>Nome</label>
      </div>

      <div class="input-group">
        <textarea name="bio" placeholder=" "><?= htmlspecialchars($userProfile['bio']) ?></textarea>
        <label>Bio</label>
      </div>

      <div class="input-group">
        <input type="url" name="github" value="<?= htmlspecialchars($userProfile['github']) ?>" placeholder=" ">
        <label>GitHub (URL)</label>
      </div>
      <div class="input-group">
        <input type="url" name="linkedin" value="<?= htmlspecialchars($userProfile['linkedin']) ?>" placeholder=" ">
        <label>LinkedIn (URL)</label>
      </div>
      <div class="input-group">
        <input type="url" name="instagram" value="<?= htmlspecialchars($userProfile['instagram']) ?>" placeholder=" ">
        <label>Instagram / X (URL)</label>
      </div>
      <div class="input-group">
        <input type="url" name="website" value="<?= htmlspecialchars($userProfile['website']) ?>" placeholder=" ">
        <label>Site Pessoal / Portfólio (URL)</label>
      </div>

      <button type="submit" class="btn">Salvar Perfil</button>
      <a href="profile.php?id=<?= $userProfile['id'] ?>" class="btn secondary" style="margin-left:0.5rem;text-decoration:none;">
        Voltar ao Perfil
      </a>
    </form>
  </section>
</main>

<script src="assets/js/script.js"></script>
</body>
</html>