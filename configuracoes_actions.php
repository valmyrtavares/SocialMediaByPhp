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
 

    //AVATAR

    if(isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])){
        $newAvatar = $_FILES['avatar'];
        
        
        if(in_array( $newAvatar['type'], ['image/jpeg', 'image/jpg', 'image/png'])){

            $avatarWidth = 200;
            $avatarHeight = 200;

            list($widthOrigin, $heightOrigin) = getimagesize($newAvatar['tmp_name']);
            $ratio = $widthOrigin / $heightOrigin;

            $newWidth = $avatarWidth;
            $newHeight = $newWidth / $ratio;

            if($newHeight < $avatarHeight){
                $newHeight = $avatarHeight;
                $newWidth = $newHeight * $ratio;
            }

            echo $newWidth. ' x ' .$newHeight;

            $x = $avatarWidth - $newWidth;
            $y = $avatarHeight - $newHeight;

            $x = $x<0 ? $x/2: $x;
            $y = $y<0 ? $y/2 : $Y;

            $finalImage = imagecreatetruecolor($avatarWidth, $avatarHeight);
            switch($newAvatar['type']){
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($newAvatar['tmp_name']);
                break;
                case 'image/png':
                    $image = imagecreatefrompng($newAvatar['tmp_name']);
                break;
            }

            imagecopyresampled(
                $finalImage, $image,
                $x, $y, 0, 0,
                $newWidth, $newHeight, $widthOrigin, $heightOrigin
            );

            $avatarName = md5(time().rand(0, 9999)). 'jpg';           
            
            imagejpeg($finalImage, './media/avatars/'.$avatarName, 100);

            $userInfo->avatar = $avatarName;
        }
    }

     //COVER

     if(isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])){
        $newCover = $_FILES['cover'];
        
       
        
        
        if(in_array( $newCover['type'], ['image/jpeg', 'image/jpg', 'image/png'])){

            $coverWidth = 850;
            $coverHeight = 313;

            list($widthOrigin, $heightOrigin) = getimagesize($newCover['tmp_name']);
            $ratio = $widthOrigin / $heightOrigin;

            $newWidth = $coverWidth;
            $newHeight = $newWidth / $ratio;

            if($newHeight < $coverHeight){
                $newHeight = $coverHeight;
                $newWidth = $newHeight * $ratio;
            }

           
            $x = $coverWidth - $newWidth;
            $y = $coverHeight - $newHeight;

            $x = $x<0 ? $x/2: $x;
            $y = $y<0 ? $y/2 : $Y;

            $finalImage = imagecreatetruecolor($coverWidth, $coverHeight);
            switch($newCover['type']){
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($newCover['tmp_name']);
                break;
                case 'image/png':
                    $image = imagecreatefrompng($newCover['tmp_name']);
                break;
            }

           
            imagecopyresampled(
                $finalImage, $image,
                $x, $y, 0, 0,
                $newWidth, $newHeight, $widthOrigin, $heightOrigin
            );

            $coverName = md5(time().rand(0, 9999)). 'jpg';           
            
            imagejpeg($finalImage, './media/covers/'.$coverName, 100);

            $userInfo->cover = $coverName;

         
        }
    }
 

    $userDao->update($userInfo);
}

header("Location:".$base."/configuracoes.php");