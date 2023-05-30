<?php
    $wears  = ["Factory New","Minimal Wear","Field Tested","Well Worn","Battle Scarred","Any"];
    $trades;
    function query_db_for_user($SID){
        global $trades;
        try{
            $conn = pg_connect("host=localhost dbname=CS1 user=postgres password=postgres");
            $trade_matches = pg_fetch_all(pg_query($conn,"SELECT DISTINCT uts, steam_id FROM item_listings WHERE steam_id = $SID"));
            foreach ($trade_matches as $trade_match){
                $t = [];
                $t['have'] = pg_fetch_all(pg_query($conn,"SELECT DISTINCT uts, steam_id,asset_id,name,wear,inspect_link,icon_link,classid FROM item_listings WHERE uts = ".$trade_match['uts']." AND steam_id = ".$trade_match['steam_id']));
                $t['for'] = pg_fetch_all(pg_query($conn,"SELECT DISTINCT class_id FROM want_listings WHERE uts = ".$trade_match['uts']." AND steam_id = ".$trade_match['steam_id']));
                $t['user'] = pg_fetch_all(pg_query($conn,"SELECT * FROM users WHERE steam_id= ".$trade_match['steam_id']))[0];
                for ($i = 0; $i< count($t['for']); $i++){
                    $result = pg_fetch_all(pg_query($conn,"SELECT name,icon_url FROM items WHERE classid = ".$t['for'][$i]['class_id']));
                    if ($result != false){
                        $t['for'][$i] = array_merge($t['for'][$i],$result[0]);
                    }
                    else{
                        $t['for'][$i] = array_merge($t['for'][$i],['name'=> "wildcard", 'icon_url' => "wildcard"]);
                    }
                }
                $trades[]= $t;
            }
            pg_close($conn);
        }catch(Exception){}
    }
    $cdn_url = 'https://steamcommunity-a.akamaihd.net/economy/image/';
    $inspect_url_prefix = "steam://rungame/730/76561202255233023/+csgo_econ_action_preview%20S";
    $market_url_prefix = "https://steamcommunity.com/market/listings/730/";
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
    if (isset($_POST['search'])){
        $search_query = $_POST['search'];
    }else{$search_query='';}
    if (isset($_POST['wear'])){
        $wear_query = $_POST['wear'];
    }else{$wear_query="Any";}
    query_db_for_user($SID);
    ?>
    <!DOCTYPE html>
<html>
    <head>
        <link href="home.css" rel="stylesheet">
        <title>Active Trades</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@100&display=swap" rel="stylesheet">
    </head>
    <body>
        <a id="inv_button" href="./inventory_auth.php">Inventory</a>
        <a id="inv_button" href="./home.php">Home</a>
        <div id="main_body">
            <form method="post">
            <div id="search">
                <input type= "submit" id="search_button" value="Search">
                <input value="<?=$search_query?>" name="search" pattern="/[a-zA-Z0-9 ]{0-20}/" type="text" id="search_primary"></input>
                <h1>Wear</h1>
                <select style="width:100%;" value=<?=$wear_query?>  name="wear" type="text" id="search_wear">
                    <?php
                    foreach ($wears as $wear){
                        $s = ($wear == $wear_query)? "selected":"";
                        echo "<option ". $s ." >$wear</option>\n";
                    }
                    ?>
                </select>
                </input>
            </div>
            </form>
            <div id="trades">
                <?php 
                        foreach ($trades as $trade){
                            echo '<div class="trade-container-container">';
                            echo '<div class="trade-container">';
                            echo '<div class="have-box">';
                            foreach ($trade['have'] as $have_item){
                                echo '<div class="item_card" style="color:white;background-image:url('.$cdn_url.$have_item['icon_link'].')">';
                                echo '<h4 class="card-title">'.$have_item['name']."</h4>";
                                try{echo '<a class="inspect_anchor" href="'.$inspect_url_prefix.$have_item['inspect_link'].'">Inspect</a>';}catch(Exception){};
                                echo '<a class="market_anchor" target="_blank" href="'.$market_url_prefix.$have_item['name'].'">Market</a>';
                                echo '</div>';
                            }
                            echo '</div>';
                            echo '<image style="height: 100px;width:200px;" src="./arrow.png">';
                            echo '<div class="for-box">';
                            foreach ($trade['for'] as $for_item){
                                echo '<div class="item_card" style="color:white;background-image:url('.$cdn_url.$for_item['icon_url'].')">';
                                $name = ($for_item['classid'] = 0)? "Any Knife": "Any Gloves";
                                if ($for_item['name'] != 'wildcard'){
                                    echo '<a class="market_anchor" target="_blank" href="'.$market_url_prefix.$for_item['name'].'">Market</a>';
                                    $name = $for_item['name'];

                                }
                                echo '<h4 class="card-title">'.$name."</h4>";
                                echo '</div>';
                            }
                            echo '</div>';
                            echo '</div>';
                            echo '<a class="delete_card" href="delete_trade.php?trade_uts='.$trade['have'][0]['uts'].'"><h4>Delete</h4></a>';
                            echo "</div>";
                        }
                ?>
            </div>
        </div>
        </body>
</html>