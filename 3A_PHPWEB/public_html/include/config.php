<?php
require_once('/home/vendor/autoload.php');
require_once('/home/GoogleAuthenticator.php');

session_start();

$db = new PDO('mysql:host=localhost;dbname=nicomace2501;charset=utf8mb4', 'nicomace2501', 'PlHdXwmzywIP');

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, array(
        'cache' => false
));

date_default_timezone_set('Europe/Paris');
