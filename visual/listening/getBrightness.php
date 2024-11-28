<?php

function getBrightness(string $hex): string|bool {
    $api_url = 'https://api.lagden.dev/v1/color-tools/check_brightness?color=' . urlencode($hex) . '&color_format=hex';

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
        $error = curl_error($ch);
        curl_close($ch);
        return "Error: " . $error;
    }
    curl_close($ch);

    if ($http_status === 200) {
        $data = json_decode($json_data, true);
        if ($data && isset($data["ok"]) && $data["ok"] === true) {
            return $data["data"]["is_dark"];
        }
    }
    
    $data = json_decode($json_data, true);
    return "Error: " . ($data["message"] ?? "API request failed with status $http_status");
}

?>