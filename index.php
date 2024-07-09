<?php
  require('db.php');
  $title = "Mon compte";

  if(empty($_SESSION['user'])){
      header('Location: login.php');
  }
  
  $user = $_SESSION['user'];
  $content_title = "Bienvenue sur votre espace " . $user -> name;

  include('header.php'); ?>


<main role="main" class="flex-shrink-0">
  <div class="container" id="container">

    <h2 style="text-align: center; margin: 30px 0 50px;"><?=$content_title; ?></h2>
    <?php if (!empty($user->photo)):?>
      <img class="profile-photo" src="photos/<?=$user->id . '/' . $user->photo ?>" alt="Photo de profil de <?= $user->name ?>" />
    <?php else:?>
      <img class="profile-photo" src="photos/profile-picture.png" alt="Photo de profil" />
    <?php endif; ?>
    <p><a href="profile-modify.php" class="btn btn-light" title="Modifier le profil">Modifier mon profil</a></P>
    <p><a href="password.php" title="Modifier le mot de passe">Modifier mon mot de passe</a></p>
    <p><a class="btn btn-danger delete" title="Supprimer le compte" onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ?');" href="delete-account.php" style="float:right;">Supprimer mon compte</a></p>
  </div>
</main>


<?php include('footer.php'); ?>