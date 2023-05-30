<?php
session_start();
$params = [
    'openid.assoc_handle' => $_GET['openid_assoc_handle'],
    'openid.signed'       => $_GET['openid_signed'],
    'openid.sig'          => $_GET['openid_sig'],
    'openid.ns'           => 'http://specs.openid.net/auth/2.0',
    'openid.mode'         => 'check_authentication',
];
$signed = explode(',', $_GET['openid_signed']);
foreach ($signed as $item) {
    $val = $_GET['openid_'.str_replace('.', '_', $item)];
    $params['openid.'.$item] = stripslashes($val);
}
$data = http_build_query($params);
//data prep
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Accept-language: en\r\n".
        "Content-type: application/x-www-form-urlencoded\r\n".
        'Content-Length: '.strlen($data)."\r\n",
        'content' => $data,
    ],
]);
$result = file_get_contents('https://steamcommunity.com/openid/login', false, $context);
if(preg_match("#is_valid\s*:\s*true#i", $result)){
    preg_match('#^https://steamcommunity.com/openid/id/([0-9]{17,25})#', $_GET['openid_claimed_id'], $matches);
    $steamID64 = is_numeric($matches[1]) ? $matches[1] : 0;
}else{
    header("Location: inventory.php");
    exit();
}
$steam_api_key = 'C34CF06727710515E9E1E3EB3BE83776';
$response = file_get_contents('https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='.$steam_api_key.'&steamids='.$steamID64);
$response = json_decode($response,true);
$userData = $response['response']['players'][0];
$_SESSION['Error'] = '';
$_SESSION['userData'] = [
    'steam_id'=>$userData['steamid'],
    'name'=>$userData['personaname'],
    'avatar'=>$userData['avatarmedium'],
    'profile_url'=>$userData['profileurl'],
    'profile_state'=>$userData['communityvisibilitystate']
];
try{
    $conn = pg_connect("host=localhost dbname=CS1 user=postgres password=postgres");
    pg_query($conn,"DELETE FROM users WHERE steam_id = ".$userData['steamid']);
    pg_query($conn,"INSERT INTO users VALUES (".$userData['steamid'].",'".$userData['personaname']."','".$userData['profileurl']."','".$userData['avatarmedium']."')");
}catch(Exception $e){echo $e;}
pg_close($conn);
$_SESSION['logged_in'] = true;
$redirect_url = "inventory_auth.php";
header("Location: $redirect_url"); 
exit();