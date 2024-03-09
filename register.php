<?php

function consoleLog($data) {
    echo '<script>console.log($data)</script>';
}

$env = parse_ini_file('.env');
if (!$env) {
    die("Error: Unable to parse .env file");
}

$DB_Host = $env["DB_HOST"];
$DB_User = $env["DB_USER"];
$DB_Name = $env["DB_NAME"];
$DB_Pass = $env["DB_PASS"];

$conn = new mysqli($DB_Host, $DB_User, $DB_Pass, $DB_Name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registrationMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["username"];
    $user_password = $_POST["password"];
    $hashedPassword = password_hash($user_password, PASSWORD_DEFAULT);
    $api_key = "0";

    $stmt = $conn->prepare("INSERT INTO users (User, User_Password, API_KEY) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user_name, $hashedPassword, $api_key);

    if ($stmt->execute()) {
        $registrationMessage = "Registered successfully";
        sleep(1);
        $stmt->close();
        $conn->close();
        header("location: login.php");
        exit;
    } else {
        $registrationMessage = "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voidem API</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex flex-col items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-900 mb-4">Voidem API</h1>
        <p class="text-lg text-gray-700 mb-8">Register an account to get an API Key</p>
        
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 flex flex-col">
            <form method="post" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                        Username
                    </label>
                    <input name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" type="text">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" type="password">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
