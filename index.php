<?php
// Database Configuration
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "weatherapp";

// Establishing Database Connection
$dbConnection = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($dbConnection->connect_error) {
    die("Database Connection Failed: " . $dbConnection->connect_error);
}

// Function to get weather icon URL
function getWeatherIcon($iconCode) {
    $iconUrl = "https://openweathermap.org/img/wn/$iconCode.png";
    return $iconUrl;
}

// Function to fetch weather data from OpenWeatherMap API
function getWeatherData($searchCity, $dbConn) {
    $weatherApiKey = "e4f0b722ff7b11e4e6e52010667643a9"; 
    $apiEndpoint = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($searchCity) . "&appid=" . $weatherApiKey . "&units=metric";
    
    $weatherJson = @file_get_contents($apiEndpoint);

    if ($weatherJson === false) {
        echo "City name not available";
        return;
    }

    if ($weatherJson) {
        $weatherArray = json_decode($weatherJson, true);

        $currentDate = date("Y-m-d");
        $temp = $weatherArray['main']['temp'];
        $humid = $weatherArray['main']['humidity'];
        $press = $weatherArray['main']['pressure'];
        $windSpd = $weatherArray['wind']['speed'];
        $weatherCond = $weatherArray['weather'][0]['description'];
        $cityName = $weatherArray['name'];
        $weatherIcon = $weatherArray['weather'][0]['icon'];

        $insertQuery = "INSERT INTO weatherdata (Date, city, temperature, conditions, humidity, windSpeed, pressure, weather_icon) 
                        VALUES ('$currentDate', '$cityName', '$temp', '$weatherCond', '$humid', '$windSpd', '$press', '$weatherIcon')";
        
        $result = $dbConn->query($insertQuery);

        if (!$result) {
            echo "Error: failed to fetch";
        }
    }
}

// Handle POST Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requestedCity = isset($_POST["textBox"]) ? $_POST["textBox"] : '';
    getWeatherData($requestedCity, $dbConnection);
}

// Delete Data Older Than 7 Days
$deleteOldQuery = "DELETE FROM weatherdata WHERE Date < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$deleteResult = $dbConnection->query($deleteOldQuery);

// In case there is an issue when deleting data
if (!$deleteResult) {
    echo "Error deleting old data: " . $dbConnection->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-right-button">
        <form action="indexx.html" method="post">
            <button type="submit">Back</button>
        </form>
    </div>

    <div id="city-name-title">
        <h2>Past 7-days Forecasted Data</h2>
    </div>

    <form method="post" class="search-form">
        <div class="search-row">
            <input type="text" placeholder="Search the location" name="textBox" class="cityname">
            <button id="search-icon" class="search-button">
                <img src="https://cdn3.iconfinder.com/data/icons/feather-5/24/search-1024.png" alt="">
            </button>
        </div>
    </form>

    <div class="weather-container">
        <?php
        if (!empty($requestedCity)) {
            $weatherQuery = "SELECT * FROM (SELECT * FROM weatherdata WHERE city = '$requestedCity' ORDER BY Date DESC, id DESC) AS sub GROUP BY Date ORDER BY Date DESC LIMIT 7";
            $queryResult = $dbConnection->query($weatherQuery);

            if ($queryResult) {
                while ($weatherRow = $queryResult->fetch_assoc()) {
                    echo '<div class="weather-details">';
                    
                    // Check if the weather icon URL is available

                    
                    echo '<p>City: ' . htmlspecialchars($weatherRow['city']) . '</p>';
                    echo '<p>Date: ' . (isset($weatherRow['Date']) ? htmlspecialchars($weatherRow['Date']) : 'N/A') . '</p>';
                    echo '<p>Temperature: ' . htmlspecialchars($weatherRow['temperature']) . '°C</p>';
                    if (isset($weatherRow['weather_icon'])) {
                        $iconUrl = getWeatherIcon($weatherRow['weather_icon']);
                        echo '<img src="' . $iconUrl . '" alt="Weather Icon">';
                    }
                    echo '<p>Condition: ' . htmlspecialchars($weatherRow['conditions']) . '</p>';
                    echo '<p>Humidity: ' . htmlspecialchars($weatherRow['humidity']) . '%</p>';
                    echo '<p>Pressure: ' . htmlspecialchars($weatherRow['pressure']) . 'hPa</p>';
                    echo '<p>Wind Speed: ' . htmlspecialchars($weatherRow['windSpeed']) . 'm/s</p>';
                    echo '</div>';
                }
            } else {
                echo "Error: " . $dbConnection->error;
            }
        }
        ?>
    </div>

    <footer>
        <p>Copy Right © Reserved By: Rohan Sitoula, 2408887</p>
    </footer>
</body>
</html>
