<?php
    require_once 'models/User.php';
    require_once 'dao/UserRelationDaoMysql.php';
    require_once 'dao/PostDaoMysql.php';

class UserDaoMysql implements UserDAO {
    private $pdo;

    public function __construct(PDO $driver){
        $this->pdo = $driver;
    }

    private function generateUser($array, $full=false) {
        $u = new User();
        $u->id = $array['id'] ?? 0;
        $u->email = $array['email'] ?? '';
        $u->name = $array['name'] ?? 0;
        $u->password = $array['password'] ?? '';
        $u->birthdate = $array['birthdate'] ?? '';
        $u->city = $array['city'] ?? ' ';
        $u->work = $array['work'] ?? ' ';
        $u->avatar = $array['avatar'] ?? ' ';
        $u->cover = $array['cover'] ?? ' ';
        $u->token = $array['token'] ?? ' ';

   

        if($full){
            $urDaoMysql = new UserRelationDaoMysql($this->pdo);
            $postDaoMysql = new PostDaoMysql($this->pdo);

            $u->followers = $urDaoMysql->getFollowers($u->id);
            foreach( $u->followers as $key=> $follower_id){
                $newUser = $this->findById($follower_id);
                $u->followers[$key] = $newUser;
            }

            $u->following = $urDaoMysql->getFollowing($u->id);
            foreach( $u->following as $key=> $follower_id){
                $newUser = $this->findById($follower_id);
                $u->following[$key] = $newUser;
            }
            $u->photos = $postDaoMysql->getPhotosFrom($u->id);
        }
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

    public function findByEmail($email){
      
        if(!empty($email)){
          
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $sql->bindValue(':email', $email);
            $sql->execute();
            if($sql->rowCount() > 0 ){
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data);
                return $user;
            }

        }
        return false;
    }

    public function findById($id, $full = false){
        if(!empty($id)){          
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $sql->bindValue(':id', $id);
            $sql->execute();
            if($sql->rowCount() > 0 ){
                $data = $sql->fetch(PDO::FETCH_ASSOC);               
                $user = $this->generateUser($data, $full);
                return $user;
            }
        }
        return false;
    }

    public function update(User $u) {
        $sql = $this->pdo->prepare("UPDATE users SET 
        email = :email,
        password = :password,
        name = :name,
        birthdate = :birthdate,
        city = :city,
        work = :work,
        avatar = :avatar,
        token = :token
        WHERE id = :id ");
    
        $sql->bindValue(':email', $u->email);
        $sql->bindValue(':password', $u->password);
        $sql->bindValue(':name', $u->name);
        $sql->bindValue(':birthdate', $u->birthdate);
        $sql->bindValue(':city', $u->city);
        $sql->bindValue(':work', $u->work);
        $sql->bindValue(':avatar', $u->avatar);
        $sql->bindValue(':token', $u->token);
        $sql->bindValue(':id', $u->id);
        $sql->execute();

    return true;

    }

    public function insert(User $u) {
        $sql = $this->pdo->prepare("INSERT INTO users (
            email, password, name, birthdate, token
        ) VALUES (
            :email, :password, :name, :birthdate, :token
        ) ");
        $sql->bindValue(':email',$u->email);
        $sql->bindValue(':password',$u->password);
        $sql->bindValue(':name', $u->name);
        $sql->bindValue(':birthdate', $u->birthdate);
        $sql->bindValue(':token', $u->token);    
        echo "Conteudo do ";
        print_r($u) ;
       
        $sql->execute();

        return true;
    }
}