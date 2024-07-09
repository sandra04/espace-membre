<?php
  require('db.php');
  $title = "Inscription";

  if(!empty($_SESSION['user'])){
    header('Location: index.php');
  }


  if(!empty($_POST)){
    // on filtre le tableau "post" et on nettoie ses string (notamment en retirant les  balises)
    $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    // extrait le tableau post (on pourra écrire directement $name au lieu de $post['name'], idem pour les autres champs du formulaire)
    extract($post);

    // stocke des erreurs s'il y en a
    $errors = [];

    if (empty($name) || strlen($name) < 3) {
      array_push($errors, 'Le nom est obligatoire et doit contenir au moins 3 caractères');
    }

    // filter_var($email, FILTER_VALIDATE_EMAIL) -> vérifie si notre adresse email est valide ou pas
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      array_push($errors, 'L\'email n\'est pas valide');
    }

    $regex = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.* )(?=.*[^a-zA-Z0-9]).{8,}$/m';
    if (empty($password) || strlen($password) < 8 || !preg_match_all($regex, $password)) {
      array_push($errors, 'Le mot de passe est obligatoire et doit contenir au moins 8 caractères et au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial');
    }

    if (empty($errors)) {
      // :name est un marqueur, on définit sa valeur sur la ligne d'en-dessous
      $req = $db->prepare('SELECT * FROM users WHERE name = :name');
      // On rattache la valeur de la variable $name au marqueur :name et on indique le type de la valeur (ici, une string)
      $req->bindValue(':name', $name, PDO::PARAM_STR);
      $req->execute();

      // Si la requête retourne au moins 1 résultat
      if ($req -> rowCount() > 0) {
        array_push($errors, "Ce nom d'utilisateur n'est pas disponible. Veuillez en choisir un autre");
      }


      $req = $db->prepare('SELECT * FROM users WHERE email = :email');
      $req->bindValue(':email', $email, PDO::PARAM_STR);
      $req->execute();

      if ($req -> rowCount() > 0) {
        array_push($errors, "Vous semblez avoir déjà un compte");
      }

      if (empty($errors)){
        $req = $db -> prepare('INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())');
        $req -> bindValue(':name', $name, PDO::PARAM_STR);
        $req -> bindValue(':email', $email, PDO::PARAM_STR);
        $req -> bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
        $req -> execute();
        
        // Vide les champs du formulaire
        unset($name, $email, $password);
        $success = 'Merci pour votre inscription ! Elle a bien été prise en compte. Vous pouvez maintenant <a href="login.php">vous connecter</a>';
      }
    }
  }
?>

<?php include('header.php'); ?>


<main role="main" class="flex-shrink-0">
  <div class="container" id="container">

    <h2><?=$title; ?></h2>

    <?php include("messages.php"); ?>
    <?php if (empty($success)): ?>
      <form action="inscription.php" method="post">
        <div class="form-group">
          <label for="name">Nom d'utilisateur</label>
          <!-- On garde les éventuelles valeurs déjà remplies dans l'attribut "value" (?? '' permet de mettre une valeur vide s'il n'y a pas de valeur $name) -->
          <input type="text" name="name" class="form-control" placeholder="Nom d'utilisateur" value="<?= $name ?? '';?>">
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? '';?>">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Mot de passe">
        </div>
        <button type="submit" class="btn btn-primary">Créer un compte</button>
      </form>
      <p>Vous avez déjà un compte ? <a href="login.php" title="Se connecter">Me connecter</a></p>
    <?php endif; ?>
  </div>
</main>


<?php include('footer.php'); ?>