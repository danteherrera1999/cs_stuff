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
    try{
        $trade_data =  json_decode($_GET['trade_data'],true);
        $AIDS = $trade_data["HAVE"];
        $WCIDS = $trade_data["WANT"];
        $SID = $_SESSION['userData']['steam_id'];
        $UTS = time();
    }catch(Exception $e){
        red(0);
    }
    //SANITIZE DATA
    if (COUNT($trade_data)!=2){
        red(0);
    }
    if (COUNT($AIDS) == 0 || COUNT($AIDS) > 10){
        red(1);
    }
    if (COUNT($WCIDS) == 0 || COUNT($WCIDS) > 10){
        red(8);
    }
    foreach ($AIDS as $AID){
        if (!preg_match("/^[0-9]{10,11}$/",$AID)){
            red(3);
        }
    }
    foreach ($WCIDS as $WCID){
        if (!preg_match("/^[0-9]{9,10}$/",$WCID)){
            red(3);
        }
    }
    $player_inventory = json_decode(file_get_contents("inv.json"),true);
    $inspect_url_prefix = "steam://rungame/730/76561202255233023/+csgo_econ_action_preview%20S";
    $market_url_prefix = "https://steamcommunity.com/market/listings/730/";
    $CONFIRMED_AIDS = [];
    $CONFIRMED_CIDS = [];
    $I_LINKS = [];
    $IC_LINKS = [];
    $CIDS = [];
    $NAMES = [];
    $TRADABLE = [];
    $WEARS = [];
    foreach ($player_inventory["assets"] as $asset){
        $CONFIRMED_AIDS[] = $asset["assetid"];
        $CONFIRMED_CIDS[] = $asset["classid"];
    }
    foreach ($AIDS as $AID){
        if (in_array($AID,$CONFIRMED_AIDS)){
            $TCID = $CONFIRMED_CIDS[array_search($AID,$CONFIRMED_AIDS)];
            $CIDS[] = $TCID;
            foreach ($player_inventory["descriptions"] as $D){
                if ($D['classid'] == $TCID){
                    if (!$D["tradable"]){
                        red(5);
                    }
                    $NAMES[] = "'".trim(preg_replace("/[^0-9a-zA-Z ()|™★-]/","",$D["market_name"]))."'";
                    try{$I_LINKS[]= str_replace([$inspect_url_prefix,"%owner_steamid%","%assetid%"],["",$SID,$AID],$D['actions'][0]['link']);}
                    catch(Exception){$I_LINKS[]="";}
                    try{$IC_LINKS[]=$D["icon_url"];}
                    catch(Exception){$IC_LINKS[]="";}
                    $tw = "F";
                    foreach ($D["tags"] as $tag){
                        if ($tag["category"] == "Exterior"){
                            $tw = (int)str_replace("WearCategory","",$tag["internal_name"]);
                        }
                    }
                    $WEARS[] = $tw;
                }
            }
        }else{
            red(4);
        }
    }
    //connect to database
    try{
        $conn = pg_connect("host=localhost dbname=CS1 user=postgres password=postgres");
        pg_query($conn,'SELECT * FROM item_listings');
        for ($i=0; $i<COUNT($NAMES);$i++){
            pg_query($conn,"INSERT INTO item_listings (steam_id,uts,asset_id,name,wear,inspect_link,icon_link,classid) VALUES (". implode(',',[$SID,$UTS,$AIDS[$i],$NAMES[$i],($WEARS[$i] != "F")? $WEARS[$i] : 'NULL', $I_LINKS[$i]? "'".$I_LINKS[$i]."'":'NULL', $IC_LINKS[$i]? "'".$IC_LINKS[$i]."'":'NULL',$CIDS[$i]]).")");
        }
        for ($i=0; $i<COUNT($WCIDS);$i++){
            pg_query($conn,"INSERT INTO want_listings (steam_id,uts,class_id) VALUES (". implode(',',[$SID,$UTS,$WCIDS[$i]]).")");
        }
        pg_close($conn);
    }catch(Exception $e){
        red(6);
    }
    header('Location: inventory_auth.php');
    exit();
?>