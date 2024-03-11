<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("location: login.php");
    exit;
}

function generateApiKey() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $apiKey = '';
    $length = strlen($characters);
    for ($i = 0; $i < 32; $i++) {
        $apiKey .= $characters[rand(0, $length - 1)];
    }
    return $apiKey;
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

$username = $_SESSION["username"];
$apiKey = '';

// Check if API key exists for the user
$stmt = $conn->prepare("SELECT API_KEY FROM users WHERE User = ?");
$stmt->bind_param("s", $username);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $apiKey = $row['API_KEY'];
    }
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();

if (isset($_POST['generate'])) {
    $apiKey = generateApiKey();
    $stmt = $conn->prepare("UPDATE users SET API_KEY = ? WHERE User = ?");
    $stmt->bind_param("ss", $apiKey, $username);

    if ($stmt->execute()) {
        // API key generated successfully
    } else {
        echo "Error updating record: " . $conn->error;
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
    <title>Voidem API - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="media/voidemAPI.png">
</head>

<body class="bg-gray-100 flex flex-col items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-900 mb-4">Voidem API</h1>
        <p class="text-lg text-gray-700 mb-8">Welcome, <?php echo $username; ?></p>

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 flex flex-col">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="api-key">
                    Your API Key
                </label>
                <input name="api-key" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="api-key" type="text" value="<?php echo $apiKey; ?>" readonly>
            </div>
            <div class="flex items-center justify-between">
                <form method="post">
                    <button name="generate" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Generate New Key
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
