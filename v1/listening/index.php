<?php
session_start();

function postToConsole($postData) {
    $json_data = json_encode($postData);
    echo "<script>console.log($json_data)</script>";
}

$env = parse_ini_file('../../.env');
if (!$env) {
    die(json_encode(["error" => "Unable to parse .env file"]));
}

function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$DB_Host = $env["DB_HOST"];
$DB_User = $env["DB_USER"];
$DB_Name = $env["DB_NAME"];
$DB_Pass = $env["DB_PASS"];
$url = $env["URL_LISTENING"];
$restrictedIPS = json_decode($env["RESTRICTED_IPS"], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(["error" => "Error decoding RESTRICTED_IPS from .env file"]));
}

global $conn;
if (!isset($conn)) {
    $conn = new mysqli($DB_Host, $DB_User, $DB_Pass, $DB_Name);
    if ($conn->connect_error) {
        die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
    }
}

$apiKey = isset($_GET['api_key']) ? $_GET['api_key'] : '';
if (empty($apiKey) || $apiKey === '0') {
    die(json_encode(["error" => "Invalid API Key"]));
}

$stmt = $conn->prepare("SELECT max_uses, uses, restricted FROM users WHERE API_KEY = ?");
$stmt->bind_param("s", $apiKey);

if (!$stmt->execute()) {
    die(json_encode(["error" => "Error: " . $stmt->error]));
}

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die(json_encode(["error" => "Invalid API Key"]));
}

$userData = $result->fetch_assoc();
$maxUses = $userData['max_uses'];
$currentUses = $userData['uses'];
$restricted = $userData['restricted'];

if ($currentUses >= $maxUses) {
    die(json_encode(["error" => "User has reached maximum uses limit, please refer to the docs: https://api.voidem.com/docs"]));
}

$stmt->close();

$client_ip = getClientIP();
if ($restricted === 1 && !in_array($client_ip, $restrictedIPS)) {
    die(json_encode(["error" => "API Key is restricted"]));
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FAILONERROR => true,
));

$response = curl_exec($curl);

if ($response === false) {
    $error = curl_error($curl);
    die(json_encode(["error" => "cURL Error: $error"]));
}

$data = json_decode($response, true);

if (isset($data['recenttracks']['track'][0])) {
    $track = $data['recenttracks']['track'][0];
    $nowPlaying = isset($track['@attr']['nowplaying']) && $track['@attr']['nowplaying'] === "true";

    $newData = [
        "nowPlaying" => $nowPlaying,
        "artist" => $track['artist']['#text'],
        "song" => $track['name'],
        "album" => $track['album']['#text'],
        "image" => $track['image'][3]['#text'],
        "url" => $track['url']
    ];

    $listeningData = ["listeningData" => $newData];

    $updateStmt = $conn->prepare("UPDATE users SET uses = uses + 1 WHERE API_KEY = ?");
    $updateStmt->bind_param("s", $apiKey);
    $updateStmt->execute();
    $updateStmt->close();

    header('Content-Type: application/json');
    echo json_encode($listeningData);
} else {
    die(json_encode(["error" => "No track data found"]));
}

curl_close($curl);
$conn->close();
?>