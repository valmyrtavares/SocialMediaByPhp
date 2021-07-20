<?php
require 'config.php';
require 'models/Auth.php';

$email = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');
$name = filter_input(INPUT_POST, 'name');
$birthdate = filter_input(INPUT_POST, 'birthdate'); // 00/00/0000
echo $email. " " .$password. " " .$name. " " .$birthdate;

if ($email && $password && $name && $birthdate) {
    $auth = new Auth($pdo, $base);

    //Forma de verificar se a data foi toda preenchida
    $birthdate = explode('/', $birthdate);
    if (count($birthdate) != 3) {
        $_SESSION['flash'] = 'Data de nascimento incompleta';
        header("Location: " . $base . "/signup.php");
        exit;
    }
    $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];
    if (strtotime($birthdate) === false){
    $_SESSION['flash'] = $birthdate. ' Data de nascimento inválida ' .$birthdate;
    header("Location: " . $base . "/signup.php");
    exit;
    }
    if ($auth->emailExists($email) === false) {

        $auth->registerUser($name, $email, $password, $birthdate);
        header('Location: ' .$base);
        exit;
    } else {
        $_SESSION['flash'] = 'Email já cadastrado';
        header("Location: " . $base . "/signup.php");
        exit;
    }
}


$_SESSION['flash'] = 'Preencha todos os campos';
header("Location: " . $base . "/signup.php");
exit;
