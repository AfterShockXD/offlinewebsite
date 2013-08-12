<?php
    include("connect.php");
    session_start();
    session_destroy();
    $username = $_SESSION['username'];
setcookie($username, time()-3600);  
    header("Location: ../index.php");
    die;    
?> //immediately after here, instead of going to index.php(the login page), it goes straight to the page that would appear after if the user had logged in(control_panel.php).