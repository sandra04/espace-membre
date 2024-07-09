<?php
    require('db.php');
    $title = "Connexion";

    if(!empty($_SESSION['user'])){
        header('Location: index.php');
    }

    if(!empty($_POST)){
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        extract($post);

        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
            array_push($errors, 'Paire email / mot de passe incorrecte');
        }

        if (empty($errors)){
            $req = $db -> prepare('SELECT * FROM users WHERE email = :email');
            $req -> bindValue(':email', $email, PDO::PARAM_STR);
            $req -> execute();

            // Permet de récupérer l'utilisateur (on le stocke dans une variable $user)
            $user = $req -> fetch();

            if ($user && password_verify($password, $user -> password)){
                // On vérifie les id et on connecte l'utilisateur si c'est ok
                // On stocke notre utilisateur dans une variable "session"
                $_SESSION['user'] = $user;

                // On redirige vers la page dashboard.php
                header('Location: index.php');
            }

            array_push($errors, 'Paire email / mot de passe incorrecte');
        }
    }
?>

<?php include('header.php'); ?>


<main role="main" class="flex-shrink-0">
  <div class="container" id="container">

    <h2><?=$title; ?></h2>

    <?php include("messages.php"); ?>

    <form action="login.php" method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? '';?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Mot de passe">
      </div>
      <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
    <p><a href="forgot.php" title="Mot de passe oublié">Mot de passe oublié</a></p><br/>
    <p>Vous n'avez pas encore de compte ? <a href="inscription.php" title="Créer un compte">Créer un compte</a></p>
  </div>
</main>


<?php include('footer.php'); ?>