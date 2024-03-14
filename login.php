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

$loginMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["username"];
    $user_password = $_POST["password"];

    $stmt = $conn->prepare("SELECT User, User_Password FROM users WHERE User = ?");
    $stmt->bind_param("s", $user_name);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $hashedPassword = $row['User_Password'];
            if (password_verify($user_password, $hashedPassword)) {
                $loginMessage = "Login successful";
                session_start();
                $_SESSION["username"] = $user_name;
                $stmt->close();
                $conn->close();
                header("location: dashboard.php");
                exit;
            } else {
                $loginMessage = "Incorrect username or password";
            }
        } else {
            $loginMessage = "Incorrect username or password";
        }
    } else {
        $loginMessage = "Error: " . $stmt->error;
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
    <title>Voidem API - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="media/voidemAPI.png">
</head>

<body class="bg-gray-100 flex flex-col items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-900 mb-4">Voidem API</h1>
        <p class="text-lg text-gray-700 mb-8">Login to access your account</p>
        
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
                        Login
                    </button>
                </div>
            </form>
        </div>
        <?php if (!empty($loginMessage)): ?>
            <p class="text-red-500"><?php echo $loginMessage; ?></p>
        <?php endif; ?>
        <p class="text-gray-700">Don't have an account? <a href="register.php" class="text-blue-500 hover:text-blue-700">Register</a></p>
    </div>
</body>

</html>
