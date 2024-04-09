<?php

function visualizer($apiType, $apiKey) {
    switch ($apiType) {
        case 'listening':
            $fullURL = "https://api.voidem.com/visual/listening?api_key=" . $apiKey;
            echo $fullURL;
            exit;
    }
}

if(isset($_POST['apiType'], $_POST['apiKey'])) {
    $apiType = $_POST['apiType'];
    $apiKey = $_POST['apiKey'];
    visualizer($apiType, $apiKey);
}
?>