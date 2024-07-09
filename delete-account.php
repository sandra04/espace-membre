<?php
    require('db.php');
    $title = "Supprimer mon compte";

    if(empty($_SESSION['user'])){
        header('Location: login.php');
    }
  
    $user = $_SESSION['user'];
    $filePath = 'photos/'.$user->id.'/'.$user->photo;

    /*// Permet de créer une boucle sur le contenu du dossier
    $dir = new DirectoryIterator(dirname('photos/'.$user->id));

    foreach($dir as $fileinfo){
        // Si le nom du fichier commence par un point
        if ($fileinfo->isDot()){
        // if('.' === $fileinfo || '..' === $fileinfo) {
            // getPathname() permet de récupérer le chemin du fichier
            // echo $fileinfo->getPathname();
            unlink($fileinfo->getPathname());
        }
    }
    exit;*/
    
    // file_exists() fonctionne aussi pour un dossier, on vérifie donc qu'il s'agit bien d'un fichier grâce à is_file()
    if ($user->photo && file_exists($filePath) && is_file($filePath)){
        // Supprime le fichier
        unlink($filePath);
        // Supprime le dossier
        rmdir('photos/'.$user->id);
    }

    $req = $db -> prepare('DELETE FROM users WHERE id = :id');
    $req -> bindValue(':id', $user->id, PDO::PARAM_INT);
    $req -> execute();

    unset($_SESSION['user']);
    session_destroy();
    header('Location: login.php');
?>


<?php include('header.php'); ?>