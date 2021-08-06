<?php

require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/PostCommentDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();



$id = filter_input(INPUT_POST, 'id');
$txt = filter_input(INPUT_POST, 'txt');



$array = ['error' => ''];

$postDao = new PostDaoMysql($pdo);

if(isset($_FILES['photo']) && !empty($_FILES['photo']['tmp_name'])){
    $photo = $_FILES['photo'];

    if(in_array($photo['type'],['image/jpeg','image/jpg', 'image/png'])){

        list($widthOrig, $heightOrig) = getimagesize($photo['tmp_name']);
        $ratio = $widthOrig / $heightOrig;

        $newWidth = $maxWidth;
        $newHeight = $maxHeight;
        $ratioMax = $maxWidth / $maxHeight;

        if($ratioMax > $ratio){
            $newWidth = $newHeight * $ratio;            
        }else{
            $newHeight = $newWidth / $ratio;
        }

        // $newWidth = $maxWidth;
        // $newHeight = $newWidth / $ratio;

        // if($newHeight < $maxHeight){
        //     $newHeight = $maxHeight;
        //     $newWidth = $newHeight * $ratio;
        // }

        $finalImage = imagecreatetruecolor( $newWidth,$newHeight );
        switch($photo['type']){
            case 'image/jpe':
            case 'image/jpeg':
                $image = imagecreatefromjpeg($photo['tmp_name']);
            break;
            case 'image/phb':
                $image = imagecreatefrompng($photo['tmp_name']);
            break;
        }

        imagecopyresampled(
            $finalImage, $image,
            0, 0, 0, 0,
            $newWidth, $newHeight, $widthOrig, $heightOrig
        );

        $photoName = md5(time().rand(0, 9999)). '.jpg';
        imagejpeg($finalImage, 'media/uploads/'. $photoName);

        $newPost = new Post();
        $newPost->id_user = $userInfo->id;
        $newPost->type = 'photo';
        $newPost->created_at = date('Y-m-d H:i:s');
        $newPost->body = $photoName;

        $postDao->insert($newPost);

    }else{
        $array['error'] = 'Arquivo não suportado (jpg ou png)';
    }

}else{
    $array['error'] = 'Nemnuma imagem enviada';
}


header("Content-Type: application/json");
echo json_encode($array);
exit;