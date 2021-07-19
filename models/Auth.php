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

    public function validateLogin($email, $password){
        $userDao = new UserDaoMysql($this->pdo);

        $user = $userDao->findByEmail($email);
        if($user){

            if(password_verify($password, $user->password)){

                $token = md5(time().rand(0, 9999));

                $_SESSION['token'] = $token;
                $user->token = $token;
                $userDao->update($user);

                return true;
            }

        }
    }

    public function emailExists($email){
        $userDao = new UserDaoMysql($this->pdo);
        return $userDao->findByEmail($email) ? true : false;
    }


}