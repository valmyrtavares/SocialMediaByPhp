<?php
require 'config.php';
require'models/Auth.php';

$email = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');

echo "EMAIL: " .$email. "</br>";
echo "PASSWORD: " .$password;

if($email && $password){
    $auth = new Auth($pdo, $base);

    if($auth->validateLogin($emal, $password)){
        header("Location: ".$base);
        exit;

    }
}

header("Location: ".$base. "/login.php");
exit;
