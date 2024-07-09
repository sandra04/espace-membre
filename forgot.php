<?php
    require('db.php');
    $title = "Mot de passe oublié";

    if(!empty($_SESSION['user'])) {
        header('Location: index.php');
    }

    require('vendor/autoload.php');

    if(!empty($_POST)) {
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        extract($post);

        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, 'L\'adresse email n\'est pas valide');
        }
        else{
            $req = $db->prepare('SELECT * FROM users WHERE email = :email');
            $req->bindValue(':email', $email, PDO::PARAM_STR);
            $req->execute();

            if ($req -> rowCount() > 0 && empty($errors)) {
                $user = $req-> fetch();
                
                $token = uniqid();

                $req = $db->prepare('INSERT INTO password_resets (email, token, created_at) VALUES (:email, :token, NOW())');
                $req->bindValue(':email', $email, PDO::PARAM_STR);
                $req->bindValue(':token', $token, PDO::PARAM_STR);
                $req->execute();

                $emailMessage = "<p>Bonjour,</p><p>Suite à votre demande, vous pouvez réinitialiser votre mot de passe via <a href='reset.php?token=".$token."'>ce lien</a>.</p>";

                // Create the Transport
                $transport = (new Swift_SmtpTransport($_ENV['ML_HOST'], $_ENV['ML_PORT']))
                ->setUsername($_ENV['ML_USER'])
                ->setPassword($_ENV['ML_PASSWORD'])
                ;

                // Create the Mailer using your created Transport
                $mailer = new Swift_Mailer($transport);

                // Create a message
                $message = (new Swift_Message('Mot de passe oublié'))
                ->setFrom(['test@mon-site.com' => 'John Doe'])
                ->setTo([$email => $user->name])
                ->addPart($emailMessage, "text/html")
                ;

                // Send the message
                $result = $mailer->send($message);

                if ($result) {
                    $success = "Votre demande a bien été prise en compte. Vous allez recevoir sous peu un email de réinitialisation de votre mot de passe";
                    unset($email);
                }
            }
        }
    }
?>

<?php include('header.php'); ?>


<main role="main" class="flex-shrink-0">
  <div class="container" id="container">

    <h2><?=$title; ?></h2>

    <?php include("messages.php"); ?>

    <form action="forgot.php" method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? '';?>">
      </div>
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
    <p>Retourner sur la page <a href="login.php" title="Se connecter">connexion</a></p>
  </div>
</main>


<?php include('footer.php'); ?>