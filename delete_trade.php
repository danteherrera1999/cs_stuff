<?php
    function red($error) {
        $error_list = ["An Unknown Error Occurred",
        "User Must Submit Between 1 And 10 Items",
        "Request Timeout",
        "Inconsistent Item Data",
        "Could Not Locate Item In Player Inventory",
        "Some Items Are Not Currently Tradable",
        "Database Error Occurred. Please Try Again Later.",
        "User Could Not Be Authenticated",
        "User Must Submit Between 1 And 10 Wanted Items"];
        $_SESSION['Error'] = "ERROR: ".$error_list[$error];
        header("Location: inventory.php");
        exit();
    }
    session_start();
    if (isset($_SESSION['logged_in'])){
        if(!$_SESSION['logged_in']){
            red(7);
        }
    }else{
        red(7);
    }
    $trade_uts = $_GET['trade_uts'];
    try{
        $conn = pg_connect("host=localhost dbname=CS1 user=postgres password=postgres");
        pg_query($conn,'DELETE FROM item_listings WHERE uts='.$trade_uts.' AND steam_id='.$_SESSION['userData']['steam_id']);
        pg_query($conn,'DELETE FROM want_listings WHERE uts='.$trade_uts.' AND steam_id='.$_SESSION['userData']['steam_id']);
        pg_close($conn);
    }catch(Exception $e){
        red(6);
    }
    
    header('Location: user_trades.php');
    exit();
?>