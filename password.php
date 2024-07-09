<?php
  require('db.php');
  $title = "Modifier mot de passe";

  if(empty($_SESSION['user'])){
      header('Location: login.php');
  }
  
  $user = $_SESSION['user'];

  if(!empty($_POST)) {
    $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

    extract($post);

    $errors = [];
    
    if (!password_verify($actualPassword, $user -> password)) {
      array_push($errors, 'L\'un des mots de passe ne correspond pas');
    }
    $regex = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.* )(?=.*[^a-zA-Z0-9]).{8,}$/m';
    if (empty($newPassword) || strlen($newPassword) < 8 || !preg_match_all($regex, $newPassword)) {
      array_push($errors, 'Le mot de passe doit contenir au moins 8 caractères et contenir au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial');
    }
    if ($newPassword !== $newPasswordConfirmation) {
      if (!in_array("L\'un des mots de passe ne correspond pas", $errors)) {
        array_push($errors, 'L\'un des mots de passe ne correspond pas');
      }
    }
    if(empty($errors)){
      $req = $db -> prepare('UPDATE users SET password = :password WHERE id = :id');
      $req -> bindValue(':password', password_hash($newPassword, PASSWORD_DEFAULT), PDO::PARAM_STR);
      $req -> bindValue(':id', $user->id, PDO::PARAM_INT);
      $req -> execute();

      $req = $db -> prepare('SELECT * FROM users WHERE id = :id');
      $req -> bindValue(':id', $user->id, PDO::PARAM_INT);
      $req -> execute();

      $user = $req -> fetch();

      unset($_SESSION['user']);
      $_SESSION['user'] = $user;

      $success = "Le mot de passe a bien été mis à jour";
    }
  }
?>


<?php include('header.php'); ?>


<main role="main" class="flex-shrink-0">
  <div class="container" id="container">

    <h2><?=$title; ?></h2>

    <?php include("messages.php"); ?>

    <?php if (empty($success)): ?>
    <form action="password.php" method="post">
      <div class="form-group">
      <div class="form-group">
        <label for="actualPassword">Mot de passe actuel</label>
        <input type="password" name="actualPassword" class="form-control">
      </div>
      <div class="form-group">
        <label for="newPassword">Nouveau mot de passe</label>
        <input type="password" name="newPassword" class="form-control">
      </div>
      <div class="form-group">
        <label for="newPasswordConfirmation">Confirmer le nouveau mot de passe</label>
        <input type="password" name="newPasswordConfirmation" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary">Modifier le mot de passe</button>
    </form>
    <?php endif; ?>
    <p><a href="index.php" title="Profil utilisateur">Revenir sur mon profil</a></p>

  </div>
</main>


<?php include('footer.php'); ?>