<?php
    $env = parse_ini_file(__DIR__ . "/../../restricted/passes.env");

    foreach($env as $key => $value){
        putenv("$key=$value");
    }
?>