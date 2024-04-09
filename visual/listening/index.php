<?php
function logConsole($msg) {
    echo "<script>console.log('$msg');</script>";
}

$api_key = isset($_GET['api_key']) ? $_GET['api_key'] : '';

$api_url = 'https://api.voidem.com/v1/listening?api_key=' . $api_key;

$json_data = file_get_contents($api_url);
$http_status = null;
if (isset($http_response_header) && is_array($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/', $header, $matches)) {
            $http_status = intval($matches[1]);
            break;
        }
    }
}

if ($http_status !== 429) {
    $data = json_decode($json_data, true);
    if ($data !== null) {
        if (isset($data["listeningData"])) {
            //logConsole(json_encode($data["listeningData"]));
            $playingNow = $data["listeningData"]["nowPlaying"];
            $artist = $data["listeningData"]["artist"];
            $song = $data["listeningData"]["song"];
            $album = $data["listeningData"]["album"];
            $image = $data["listeningData"]["image"];
            $url = $data["listeningData"]["url"];
            if ($playingNow === true) {
                $playing = "Now Playing";
            } else {
                $playing = "Last Played";
            }
        } else {
            //logConsole(json_encode($data["error"]));
            $dataSet = $data["error"];
        }
    } else {
        logConsole("Failed to decode JSON data.");
    }
} else {
    $dataSet = "Error: Too many requests. Please try again later.";
}

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voidem API</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../media/voidemAPI.png">
    <style>
        .bg-img {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .text-center {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .bg-blur {
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(30px);
        }

        .text-overlay {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); 
        }
    </style>
</head>

<body class="bg-gray-100 flex flex-col items-center justify-center h-screen">
    <div class="bg-img blur-sm" style="background-image: url('<?php echo $image?>'); background-repeat: no-repeat; background-size: cover; background-position: center center;">
        <div class="bg-blur"></div>
    </div>
    <div class="text-center">
        <h1 class="text-6xl font-bold text-overlay mb-4">Voidem API</h1>
        <p class="text-lg text-gray-700 mb-8 text-overlay">Visualising the listening API</p>
        <div class="bg-white shadow-md rounded px-8 pt-5 pb-8 mb-4 flex flex-col">
            <img src="<?php 
            if (isset($playing)) {
                echo $image;
            } else {
                echo "../media/error-icon.png";
            } 
            ?>" class="w-32 h-32 mx-auto mb-4 rounded-lg">
            <h2 class="text-xl font-semibold text-gray-900 mb-2"><?php
             if (isset($playing)) {
                echo $playing;
             } else {
                echo $dataSet;
             }
             ?></h2>
            <p class="text-lg text-gray-700 mb-2"><?php
                if (isset($playing)) {
                    echo "Artist: " . $artist;
                }
             ?></p>
            <p class="text-lg text-gray-700 mb-2"><?php
                if (isset($playing)) {
                    echo "Song: " . $song;
                }
            ?></p>
            <p class="text-lg text-gray-700 mb-2"><?php
                if (isset($playing)) {
                    echo "Album: " . $album;
                }
            ?></p>
            <?php
            if (isset($playing)) {
                echo '<p class="test-lg text-gray-700 mb-2"><a href="' . $url . '" class="text-blue-500 hover:underline" target="#">' . $url . '</a></p>';
            }
            ?>
        </div>
    </div>
</body>

</html>
