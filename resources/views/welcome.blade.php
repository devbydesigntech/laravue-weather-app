<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Weather App</title>

    <link rel="stylesheet" href="/css/main.css">
    <!-- Leaflet Map Stylesheet -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/leaflet/1/leaflet.css" />
    <!-- Algolia Places -->
    <script src="https://cdn.jsdelivr.net/npm/places.js@1.19.0" defer></script>
    <!-- Leaflet Map JavaScript -->
    <script src="https://cdn.jsdelivr.net/leaflet/1/leaflet.js" defer></script>
    <script src="/js/app.js" defer></script>

</head>
<body class="bg-blue-200">
    <div id="app" class="flex justify-center pt-16">
        <weather-app></weather-app>
    </div>
</body>
</html>