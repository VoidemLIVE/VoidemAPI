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

$stmt = $conn->prepare("SELECT API_KEY, restricted, uses, max_uses FROM users WHERE User = ?");
$stmt->bind_param("s", $username);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $apiKey = $row['API_KEY'];
        $restricted = $row['restricted'];
        $uses = $row['uses'];
        $max_uses = $row['max_uses'];
    } else {
        // Redirect to index.php if the username doesn't exist in the database
        header("location: index.php");
        exit;
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
        // success
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

if (isset($_POST['toggleRestricted'])) {
    if ($restricted === 1) {
        $restricted1 = 0;
        $restricted = 0;
    } else {
        $restricted1 = 1;
        $restricted = 1;
    }
    $stmt = $conn->prepare("UPDATE users SET restricted = ? WHERE User = ?");
    $stmt->bind_param("ss", $restricted1, $username);

    if ($stmt->execute()) {
        // success
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="media/voidemAPI.png">
    <style>
    .fa-info-circle {
        font-size: 1rem;
    }
    .fa-info-circle:hover {
        color: #4a90e2;
    }
</style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-900 mb-4">Voidem API</h1>
        <p class="text-lg text-gray-700 mb-8">Welcome, <?php echo $username; ?></p>

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 flex flex-col">
            <div class="mb-4">
                <label class="block text-gray-700 text-base font-bold mb-2" for="api-key">
                    Uses
                </label>
                <input name="uses-box" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="uses-box" type="text" value="<?php 
                echo ($uses . "/" . $max_uses);
                if ($uses >= $max_uses) {
                    echo " [Limit Reached]";
                }
                ?>" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-base font-bold mb-2" for="api-key">
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
                <button id="copyButton" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Copy
                </button>
            </div>
            <br>
            <div class="mb-4">
                <label class="block text-gray-700 text-base font-bold mb-2" for="restricted-box">
                    Restricted
                </label>
                <input name="restricted-box" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="restricted-box" type="text" value="<?php 
                    if ($restricted === 1) {
                        echo "TRUE";
                    } else {
                        echo "FALSE";
                    }
                ?>" readonly>
            </div>
            <div class="flex items-center justify-between">
                <form method="post">
                    <button name="toggleRestricted" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Toggle Restricted
                    </button>
                </form>
                <a href="https://apidocs.voidem.com/endpoints/v1-listening" target="#"><button name="helpRestricted" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Help <i class="fas fa-info-circle"></i>
                </button></a>
            </div>
            <br>
            <a href="/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Logout</a>
        </div>
    </div>
</body>

</html>

<script>
    document.getElementById("copyButton").addEventListener("click", function() {
        var copyText = document.getElementById("api-key").value;
        var textarea = document.createElement("textarea");
        textarea.value = copyText;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");
        document.body.removeChild(textarea);
        document.getElementById("copyButton").innerHTML = "Copied!";
        setTimeout(function(){
            document.getElementById("copyButton").innerHTML = "Copy";
        }, 2000);
    });
</script>