<?php
require_once 'models/Post.php';
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/UserDaoMysq.php';

class PostDaoMysql implements PostDAO{
    private $pdo;

    public function __construct(PDO $driver){
        $this->pdo = $driver;
    }

    public function insert(Post $p){
        $sql = $this->pdo->prepare("INSERT INTO posts 
        (id_user, type, created_at, body )
        VALUES
        (:id_user, :type, :created_at, :body) 
        ");
        $sql->bindValue(':id_user', $p->id_user);
        $sql->bindValue(':type', $p->type);
        $sql->bindValue(':created_at', $p->created_at);
        $sql->bindValue(':body', $p->body);
        $sql->execute();
    }

    public function getHomeFeed($id_user){
        
        $array =[];
        $urlDao = new UserRelationDaoMysql($this->pdo);
        $userList = $urlDao->getRealtionsFrom($id_user);
        
        $sql = $this->pdo->query("SELECT * FROM posts 
        WHERE id_user IN (".implode(',', $userList).")
        ORDER BY created_at DESC");       ;
        if($sql->rowCount() > 0){
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);
                   
            $array = $this->_postListToObject($data, $id_user);
        }
        return $array;
    }

    private function _postListToObject($post_list, $id_user){
        $posts = [];
        $userDao = new UserDaoMysql(($this->pdo));

     

        foreach($post_list as $item){          
            
            $newPost = new Post();        
            $newPost->id = $item['id'];           
            $newPost->type = $item['type'];
            $newPost->created_at = $item['created_at'];
            $newPost->body = $item['body'];
            $newPost->mine = false;


            if($item['id_user'] == $id_user){
                $newPost->mine = true;
            }
            $newPost->user = $userDao->findById($item['id_user']);

            //FUTURAMENTE informações sobre LIKE
            $newPost->likeCount = 0;
            $newPost-> liked = false; 

            //FUTURAMENTE informações sobre coments
            $newPost->comments = [];

            $posts[] = $newPost;
        }      
        return $posts;
    }

}