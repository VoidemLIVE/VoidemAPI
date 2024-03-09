<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoidemAPI Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="media/voidemAPI.png">
    <style>
        .menu-item:hover,
        .menu-item.active {
            background-color: #111827;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex h-screen">
        <div class="bg-gray-700 text-white w-64">
            <div class="p-4">
                <a href="/docs"><img src="media/voidemAPI.png" class="logo-img"></img></a>
            </div>
            <nav class="mt-2">
                <a href="#" class="menu-item flex items-center py-2 px-4" onclick="loadContent('includes/home.php', this)">
                    <i class="fas fa-home h-6 w-6 mr-2"></i>
                    <span>Introduction</span>
                </a>
                <a href="#" class="menu-item flex items-center py-2 px-4" onclick="loadContent('includes/getting_key.php', this)">
                    <i class="fas fa-key h-6 w-6 mr-2"></i>
                    <span>Generate an API key</span>
                </a>
                <a href="#" class="menu-item flex items-center py-2 px-4" onclick="loadContent('includes/endpoints.php', this)">
                    <i class="fas fa-link h-6 w-6 mr-2"></i>
                    <span>Endpoints</span>
                </a>
            </nav>
        </div>
        <div id="content" class="w-full p-4">
            <!-- DO NOT CHANGE OR ELSE-->
        </div>
    </div>

    <script>
        function loadContent(url, element) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('content').innerHTML = html;
                    document.querySelectorAll('.menu-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    element.classList.add('active');
                })
                .catch(error => console.error('Error loading content:', error));
        }
        window.onload = function() {
            loadContent('includes/home.php', document.querySelector('.menu-item'));
        };
    </script>
</body>
</html>
