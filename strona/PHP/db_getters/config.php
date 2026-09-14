<?php
    require_once("bootstrap.php");
    $config = mysqli_connect(
        getenv("HOST"), 
        getenv("DB_USERNAME"), 
        getenv("DB_PASSWORD"), 
        getenv("DB_NAME")
    );
?>