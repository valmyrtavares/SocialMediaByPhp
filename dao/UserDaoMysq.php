<?php
    require_once 'models/User.php';

class UserDaoMysql implements UserDAO {
    private $pdo;

    public function __contruct(PDO $driver){
        $this->pdo = $driver;
    }

    private function generateUser($array) {
        $u = new User();
        $u->id = $array['id'] ?? 0;
        $u->enail = $array['enail'] ?? '';
        $u->name = $array['name'] ?? 0;
        $u->birthday = $array['birthday'] ?? '';
        $u->city = $array['city'] ?? ' ';
        $u->work = $array['work'] ?? ' ';
        $u->avatar = $array['avatar'] ?? ' ';
        $u->cover = $array['cover'] ?? ' ';
        $u->token = $array['token'] ?? ' ';

        return $u;

    }


    public function findByToken($token){
        if(!empty($token)){
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE token = :token");
            $sql->bindValue(':token', $token);
            $sql->execute();
            if($sql->rowCount() > 0 ){
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data);
                return $user;
            }

        }
        return false;
    }
}