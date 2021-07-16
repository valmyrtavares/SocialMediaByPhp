<?php
require_once 'dao/UserDaoMysq.php';
class Auth {

    public function __contruct(PDO $pdo, $base){
        $this->pdo = $pdo;
        $this->base = $base;
    }
    public function checkToken() {
        if(!empty($_SESSION['token'])){
            $token = $_SESSION['token'];

            $userDao = new UserDaoMysql($this->pdo);
            $user = $userDao->findByToken($token);
            if($user){
                return $user;
            }


        }
        header("Location: ".$this->base."/login.php");
    }


}