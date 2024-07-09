<?php
    require('db.php');
    $title = "Réinitialiser le mot de passe";

    if(!empty($_SESSION['user'])) {
        header('Location: index.php');
    }

    // Vérifie s'il y a bien le paramètre "token" dans l'URL
    if (empty($_GET['token'])) {
        header('Location: inscription.php');
    }
    

    $token = $_GET['token'];


    $req = $db -> prepare('SELECT * FROM password_resets WHERE token = :token');
    $req -> bindValue(':token', $token, PDO::PARAM_STR);
    $req -> execute();

    if (!$req -> rowCount()) {
        header('Location: inscription.php');
    }
    else{
        $password_reset = $req -> fetch();
    }
    

    if(!empty($_POST)){
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        extract($post);

        $errors = [];
        
        if ($email !== $password_reset->email) {
            array_push($errors, 'L\'adresse email est invalide');
        }

        $regex = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.* )(?=.*[^a-zA-Z0-9]).{8,}$/m';
        if (empty($password) || strlen($password) < 8 || !preg_match_all($regex, $password)) {
            array_push($errors, 'Le mot de passe doit contenir au moins 8 caractères et contenir au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial');
        }
        if ($password !== $passwordConfirmation){
            array_push($errors, 'L\'un des mots de passe ne correspond pas');
        }


        if(empty($errors)){
            $req = $db -> prepare('UPDATE users SET password = :password WHERE email = :email');
            $req -> bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $req -> bindValue(':email', $email, PDO::PARAM_STR);
            $req -> execute();

            $success = "Le mot de passe a bien été mis à jour ! <a href='login.php' title='Se connecter'>Me connecter</a>";

            $req = $db -> prepare('DELETE FROM password_resets WHERE email = :email');
            $req -> bindValue(':email', $email, PDO::PARAM_STR);
            $req -> execute();
        }
    }
?>


<?php include('header.php'); ?>


<main role="main" class="flex-shrink-0">
  <div class="container" id="container">

    <h2><?=$title; ?></h2>

    <?php include("messages.php"); ?>

    <?php if (empty($success)): ?>
    <form action="reset.php?token=<?=$token?>" method="post">
      <div class="form-group">
        <label for="email">Votre email</label>
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? '';?>">
      </div>
      <div class="form-group">
        <label for="password">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-control">
      </div>
      <div class="form-group">
        <label for="passwordConfirmation">Confirmer le nouveau mot de passe</label>
        <input type="password" name="passwordConfirmation" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
    </form>
    <?php endif; ?>

  </div>
</main>


<?php include('footer.php'); ?>