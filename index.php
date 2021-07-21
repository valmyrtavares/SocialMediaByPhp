<?php
require 'config.php';
require 'models/Auth.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();
$activeMenu = 'lulu';

require 'partials/header.php';
require 'partials/menu.php';
?>
<section class="feed mt-10">

<?= print_r($userInfo);?>
</section>



 
 <?php
 require 'partials/footer.php';
 ?>