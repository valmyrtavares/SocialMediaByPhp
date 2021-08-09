<?php
require_once 'models/Post.php';
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/UserDaoMysql.php';
require_once 'dao/PostLikeDaoMysql.php';
require_once 'dao/PostCommentDaoMysql.php';

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

    public function delete($id, $id_user){
        $postLikeDao = new PostLikeDaoMysql($this->pdo);
        $postCommentDao = new PostCommentDaoMysql($this->pdo);


        $sql = $this->pdo->prepare("SELECT * FROM posts
         WHERE id = :id AND id_user = :id_user");
        $sql->bindValue(':id', $id);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if($sql->rowCount() > 0){
            $post = $sql->fetch(PDO::FETCH_ASSOC);

            $postLikeDao->deleteFromPost($id);
            $postCommentDao->deleteFromPost($id);

            if($post['type'] === 'photo'){
                $img = 'media/uploads/' .$post['body'];
                if(file_exists($img)){
                    unlink($img);
                }
            }

            $sql = $this->pdo->prepare("DELETE FROM posts
            WHERE id = :id AND id_user = :id_user");
            $sql->bindValue(':id', $id);
            $sql->bindValue(':id_user', $id_user);
            $sql->execute();
        }
    }

    public function getUserFeed($id_user){   
         
        $array =['feed'=>[]];
        $perPage = 2;

        $page = intval(filter_input(INPUT_GET, 'p'));
        if($page < 1){
            $page = 1;
        }
        $offset = ($page - 1) * $perPage;

        // $urlDao = new UserRelationDaoMysql($this->pdo);
        // $userList = $urlDao->getFollowing($id_user);        


        $sql = $this->pdo->prepare("SELECT * FROM posts 
        WHERE id_user = :id_user
        ORDER BY created_at DESC  LIMIT  $offset, $perPage");       
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if($sql->rowCount() > 0){
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);                   
            $array['feed'] = $this->_postListToObject($data, $id_user);
        }  

        $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM posts 
        WHERE id_user = :id_user");       
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        $totalData = $sql->fetch();
        $total = $totalData['c'];
        
        
        $array['pages'] = ceil($total/$perPage);
        $array['currentPage'] = $page;

        return $array;
    }

    public function getHomeFeed($id_user){
        
        $array =[];
        $perPage = 4;

        $page = intval(filter_input(INPUT_GET, 'p'));
        if($page < 1){
            $page = 1;
        }
        $offset = ($page - 1) * $perPage; //offset é quantos itens eu preciso pular para mostrar nessa pagina

        // LIMIT 0, 5 Isso quer dizer numa query que eu quero começar do primeiro item e pegar 5


        $urlDao = new UserRelationDaoMysql($this->pdo);
        $userList = $urlDao->getFollowing($id_user);        
        $userList[] = $id_user;
       
       $sql = $this->pdo->query("SELECT * FROM posts 
        WHERE id_user IN (".implode(',', $userList).")
        ORDER BY created_at DESC, id DESC LIMIT  $offset, $perPage");       ;
        if($sql->rowCount() > 0){
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);
                   
            $array['feed'] = $this->_postListToObject($data, $id_user);
        }

        //Pegar o total de posts
        $sql = $this->pdo->query("SELECT COUNT(*) AS c FROM posts 
        WHERE id_user IN (".implode(',', $userList).")");   
        $totalData = $sql->fetch();
        $total = $totalData['c'];

        $array['pages'] = ceil($total/$perPage);
        $array['currentPage'] = $page;


        return $array;
    }

    public function getPhotosFrom($id_user){
        $array = [];

        $sql = $this->pdo->prepare("SELECT * FROM  posts
        WHERE id_user = :id_user AND type = 'photo'
        ORDER BY created_at DESC");

        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if($sql->rowCount() > 0){
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);                   
            $array = $this->_postListToObject($data, $id_user);
        }


        return $array;
    }


    private function _postListToObject($post_list, $id_user){
        $posts = [];
        $userDao = new UserDaoMysql(($this->pdo));
        $postLikeDao = new PostLikeDaoMysql($this->pdo);
        $postCommentDao = new PostCommentDaoMysql($this->pdo);

     

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

          
            $newPost->likeCount = $postLikeDao->getLikeCount($newPost->id);
            $newPost-> liked = $postLikeDao->getLikeCount($newPost->id, $id_user);

            //FUTURAMENTE informações sobre coments
             $newPost->comments = $postCommentDao->getComments($newPost->id);
           

            $posts[] = $newPost;
        }    
       
        return $posts;
    }

}