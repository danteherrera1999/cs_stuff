<?php
    session_start();
    $SID = 'undefined';
    if (isset($_SESSION['logged_in'])){
        if($_SESSION['logged_in']){
            header("Location: inventory_auth.php");
            exit();
        }
    }
    ?>
<!DOCTYPE html>
<html>
    <head>
        <link href="main.css" rel="stylesheet">
        <title>Inventory</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@100&display=swap" rel="stylesheet">
    </head>
    <body style="display:block">
        <h1 style="color:white;display:block;margin:50px auto;margin-top:350px;text-align:center">Please Login To View Inventory And Post Trades</h1>
        <a id="login" href="init-openId.php"></a>
    </body>
</html>