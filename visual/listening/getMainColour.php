<?php

function getMainColour($url) {
    $api_url = 'https://api.lagden.dev/v1/image-tools/dominant_colors?url=' . urlencode($url) . "&n_colors=1";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    ]);

    $json_data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return "Error: " . curl_error($ch);
    }
    curl_close($ch);

    if ($http_status !== 422) {
        $data = json_decode($json_data, true);
        if ($data && isset($data["ok"]) && $data["ok"] === true) {
            return $data["data"]["hex_colors"][0];
        } else {
            return "Error: " . ($data["message"] ?? "Invalid response");
        }
    }
    
    return "Error: Validation error";
}

?>