<?php
    require('db.php');
    $title = "Modifier mon profil";

    if(empty($_SESSION['user'])){
        header('Location: login.php');
    }
    
    $user = $_SESSION['user'];


    if(!empty($_POST)){
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        extract($post);

        $errors = [];

        if (empty($name) || strlen($name) < 3) {
            array_push($errors, 'Le nom est obligatoire et doit contenir au moins 3 caractères');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, 'L\'email n\'est pas valide');
        }


        if (empty($errors)) {
            $req = $db->prepare('SELECT * FROM users WHERE name = :name AND id != :id');
            $req->bindValue(':name', $name, PDO::PARAM_STR);
            $req->bindValue(':id', $user -> id, PDO::PARAM_INT);
            $req->execute();

            if ($req -> rowCount() > 0) {
                array_push($errors, "Ce nom d'utilisateur n'est pas disponible.");
            }

            $req = $db->prepare('SELECT * FROM users WHERE email = :email AND id != :id');
            $req->bindValue(':email', $email, PDO::PARAM_STR);
            $req->bindValue(':id', $user -> id, PDO::PARAM_INT);
            $req->execute();

            if ($req -> rowCount() > 0) {
                array_push($errors, "Cette adresse email n'est pas disponible.");
            }

            if (!empty($_FILES["photo"]["name"])){
              $photo = $_FILES["photo"];
              // On crée un chemin où on stockera notre fichier
              $filePath = "photos/".$user -> id;
              // On crée le dossier en question, on indique les droits utilisateur dessus (true permet que ce soit récursif -> crée l'intégralité du chemin d'un coup)
              // Le @ permet de créer le dossier s'il n'existe pas encore (s'il existe, on ne fait rien)
              @mkdir($filePath, 0777, true);
              
              $allowedExtensions = ['jpeg', 'jpg', 'png'];
              // pathinfo récupère les infos du fichier, avec PATHINFO_EXTENSION, on récupère l'extension (sans le point)
              $extension = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));

              // Vérifie si l'extension fait bien partie du tableau d'extensions autorisées
              if (!in_array($extension, $allowedExtensions)){
                array_push($errors, "Le format du fichier n'est pas autorisé. Merci de choisir un autre format (jpeg, jpg ou png)");
              }
              else{
                $infos = getimagesize($photo['tmp_name']);
                $width = $infos[0];
                $height = $infos[1];
                if ($width < 200 || $height < 200){
                  array_push($errors, "L'image est trop petite ! Merci de sélectionner une image de minimum 200x200px");
                }
                else{
                  // On donne un nom unique au fichier (on lui passe l'id de l'utilisateur et "true" permet de renforcer l'unicité avec php)
                  $filename = uniqid($user -> id, true).'.'.$extension;
                  // On enregistre notre fichier (arg 1) dans le chemin indiqué en arg 2 (nom et extension du fichier précisés dans le chemin)
                  move_uploaded_file($photo['tmp_name'], $filePath.'/'.$filename);
                }
              }
            }

            if (empty($errors)){
                $req = $db -> prepare('SELECT * FROM users WHERE id = :id');
                $req -> bindValue(':id', $user->id, PDO::PARAM_INT);
                $req -> execute();

                $user = $req -> fetch();

                if ($user->photo){
                    $oldFilePath = 'photos/'.$user->id.'/'.$user->photo;
                }

                $req = $db -> prepare('UPDATE users SET name = :name, email = :email, photo = :photo WHERE id = :id');
                $req -> bindValue(':name', $name, PDO::PARAM_STR);
                $req -> bindValue(':email', $email, PDO::PARAM_STR);
                $req -> bindValue(':photo', $filename ?? $user->photo, PDO::PARAM_STR);
                $req -> bindValue(':id', $user -> id, PDO::PARAM_INT);
                $req -> execute();

                /*$_SESSION['user'] -> name = $name;
                $_SESSION['user'] -> email = $email;*/
                $req = $db -> prepare('SELECT * FROM users WHERE id = :id');
                $req -> bindValue(':id', $user->id, PDO::PARAM_INT);
                $req -> execute();

                $user = $req -> fetch();

                unset($_SESSION['user']);
                $_SESSION['user'] = $user;

                if (!empty($oldFilePath) && !empty($filename)){
                    // Supprime la photo sur le lien indiqué
                    @unlink($oldFilePath);
                }

                $success = "Le profil a bien été mis à jour";
            }
        }
    }
?>

<?php include('header.php'); ?>

<main role="main" class="flex-shrink-0">
  <div class="container" id="container">
    <h2>Modifier mon profil</h2>
    <?php include("messages.php"); ?>

    <?php if (empty($success)): ?>
    <form action="profile-modify.php" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label for="name">Nom d'utilisateur</label>
        <!-- On garde les éventuelles valeurs déjà remplies dans l'attribut "value" ($name ?? '' permet de mettre une valeur vide s'il n'y a pas de valeur $name) -->
        <input type="text" name="name" class="form-control" placeholder="Nom d'utilisateur" value="<?= $name ?? $user -> name;?>">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? $user -> email;?>">
      </div>
      <div class="form-group">
        <label for="photo">Photo (jpeg, jpg ou png, minimum 200x200px)</label>
        <input type="file" name="photo" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary" onclick="displayProfileForm()">Envoyer</button>
    </form>
    <?php endif; ?>
    <!-- <button id="button-cancel-modify-profile" onclick="modifyProfile('cancel')" style="display: none">Annuler la modification</button> -->
    <p><a href="index.php" title="Profil utilisateur">Revenir sur mon profil</a></p>
  </div>
</main>


<?php include('footer.php'); ?>