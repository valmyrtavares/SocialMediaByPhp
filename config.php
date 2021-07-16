<?php
session_start();
$base = 'http://localhost/devsbook'; 

$db_name = 'devsbook';
$db_host = 'locahost';
$db_user = 'root';
$db_pass = 'Amanda@230406';

$pdo = new PDO("mysql:dbname =".$db_name.";host = ".$db_host,$db_user, $db_pass);