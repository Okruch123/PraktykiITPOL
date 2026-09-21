<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SETPOINT — Rezerwacja kortów</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@500;600;700;800&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/main.css">
        <link rel="icon" href="data:,">
    </head>
    <body>
        <?php
            require("PHP/db_getters/config.php");
        ?>
        <div id="app">
            <script src="JS/main.js" type="module"></script>
        </div>
    </body>
</html>