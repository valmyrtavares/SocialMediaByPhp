<?php

class User{
    public $id;
    public $email;
    public $password;
    public $name;
    public $birthday;
    public $city;
    public $work;
    public $avatar;
    public $cover;
    public $token;

}

interface UserDAO {
    public function findByToken($token);
}