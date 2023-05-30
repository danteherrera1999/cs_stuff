<?php
    session_start();
    $SID = 'undefined';
    if (isset($_SESSION['logged_in']) && isset($_SESSION['Error'])){
        if($_SESSION['logged_in']){
            $username = $_SESSION['userData']['name'];
            $avatar = $_SESSION['userData']['avatar'];
            $SID = $_SESSION['userData']['steam_id'];
        }
        else{
            header("location: inventory.php");
            exit();
        }
    }else{
        header("location: inventory.php");
        exit();
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
    <body id=<?=$SID?>>
        <div id="user-box">
            <img id="avatar" src="<?=$avatar?>"></image>
            <a href="logout.php">Logout</a>
        </div>
        <div id="inv-box"></div>
        <div id="trade-box"></div>
        <div id="for-section">
            <input id="search-input-box">
            <div id="search-box"></div>
            <div id="for-box"></div>
        </div>
        <div id="wildcard-box">
            <div id="0000000000" class="wildcard"><p>Any Knife</p></div>
            <div id="0000000001" class="wildcard"><p>Any Gloves</p></div>
        </div>
        <a id="trade-button">Post</div>
        <script src="main.js" type="text/javascript" ></script>
        <?php
        if ($_SESSION['Error']){
            echo '<script type="text/javascript">setTimeout(()=>{alert(\''.$_SESSION["Error"].'\')},300)</script>';
            $_SESSION['Error'] = '';
        }
        ?>
        <a id="home_link" href='./home.php'><h3>Home</h3></a>
    </body>
</html>