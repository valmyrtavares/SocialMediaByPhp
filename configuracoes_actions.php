<?php
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/UserDaoMysql.php';


$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();



$userDao = new UserDaoMysql($pdo);

$name = filter_input(INPUT_POST, 'name');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$birthdate = filter_input(INPUT_POST, 'birthdate');
$city = filter_input(INPUT_POST, 'city');
$work = filter_input(INPUT_POST, 'work');
$password = filter_input(INPUT_POST, 'password');
$password_confirmation = filter_input(INPUT_POST, 'password_confirmation');

if($name && $email){
    $userInfo->name = $name;
    $userInfo->city = $city;
    $userInfo->work = $work;

    //EMAIL
    if($userInfo->email != $email){
        if($userDao->findByEmail($email)===false){
            $userInfo->email = $email;
        }else{
            $_SESSION['flasn']= 'E-mail já existe !';
            header("Location:".$base."/configuracoes.php");
        }
    }
    //BIRTHDATE
    $birthdate = explode('/', $birthdate);
    if (count($birthdate) != 3) {
        $_SESSION['flash'] = 'Data de nascimento incompleta';
        header("Location: " . $base . "/configuracoes.php");
        exit;
    }
    $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];
    if (strtotime($birthdate) === false){
    $_SESSION['flash'] = $birthdate. ' Data de nascimento inválida ' .$birthdate;
    header("Location: " .$base. "/configuracoes.php");
    exit;
    }

    $userInfo->birthdate = $birthdate;

    //PASSWORD
    if(!empty($password))
    if($password === $password_confirmation){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userInfo->password = $hash;
    }else{
        $_SESSION['flash'] = 'As senhas não são iguais. ';
        header("Location: " .$base. "/configuracoes.php");
        exit;
    }


    $userDao->update($userInfo);
}

header("Location:".$base."/configuracoes.php");