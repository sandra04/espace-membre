<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$dotenv->required([
    'DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME'
]);


session_start();

try{
	// Instance de PDO (PHP Data Objects) -> extension PHP
	$db = new PDO('mysql:hos=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], [
		// Lever une exception et récupérer infos sur l'erreur
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		// Garde la connexion persistante en cache, pour plus de rapidité
		PDO::ATTR_PERSISTENT => true,
		// Sous quelle forme on récupère les résultats (array, objet...)
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
	]);
}
catch(Exception $e){ // variable $e de type exception
	die('Erreur : '.$e->getMessage());
}

?>