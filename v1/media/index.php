<?php
$env = parse_ini_file('.env');
$DB_Host = $env["DB_HOST"];
$DB_User = $env["DB_USERNAME"];
$DB_Name = $env["DB_DATABASE"];
$DB_Pass = $env["DB_PASSWORD"];

$req = isset($_GET['req']) ? $_GET['req'] : '';

if (empty($req)) {
    die(json_encode(["error" => "Invalid request"]));
} else {
    header('Content-Type: application/json');
    if ($req == "titles") {
        $conn = new mysqli($DB_Host, $DB_User, $DB_Pass, $DB_Name);
        if ($conn->connect_error) {
            die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
        }
        $sql = "SELECT Title FROM movies";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $titles = [];
            while ($row = $result->fetch_assoc()) {
                array_push($titles, $row["Title"]);
            }
            echo json_encode($titles);
        } else {
            echo json_encode(["error" => "No titles found"]);
        }

    } else if ($req == "links") {
        $conn = new mysqli($DB_Host, $DB_User, $DB_Pass, $DB_Name);
        if ($conn->connect_error) {
            die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
        }
        $sql = "SELECT MovieURL FROM movies";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $links = [];
            while ($row = $result->fetch_assoc()) {
                array_push($links, $row["MovieURL"]);
            }
            echo json_encode($links);
        } else {
            echo json_encode(["error" => "No links found"]);
        }
    } else {
        echo json_encode(["error" => "Invalid request"]);
    }
}

?>