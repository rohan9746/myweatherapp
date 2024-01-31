// Authors Name = Rohan Sitoula
// Authors ID = 2408887
function getWeatherForCity(city) {
    const apiKey = `e4f0b722ff7b11e4e6e52010667643a9`;
    const weatherUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`;
    fetch(weatherUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('City not found');
            }
            return response.json();
        })
        .then(data => {
            displayWeather(data, city);
        })
        .catch(error => {
            console.error('Error:', error.message);
            displayError(error.message);
        });
}

let intervalId;

function displayWeather(data, city) {
    const weatherInfoDiv = document.getElementById('weather-info');

    weatherInfoDiv.innerHTML = '';

    const cityHeading = document.createElement('h3');
    cityHeading.textContent = `Weather in ${city}`;
    weatherInfoDiv.appendChild(cityHeading);

    const humidity = data.main.humidity;
    const windSpeed = data.wind.speed;
    const temperatureCelsius = Math.round(data.main.temp);
    const description = data.weather[0].description;
    const weatherIcon = data.weather[0].icon;
    const pressure = data.main.pressure;

    const humidityDiv = document.createElement('p');
    humidityDiv.textContent = `Humidity: ${humidity}%`;
    weatherInfoDiv.appendChild(humidityDiv);

    const pressureDiv = document.createElement('p');
    pressureDiv.textContent = `Pressure: ${pressure} hg`;
    weatherInfoDiv.appendChild(pressureDiv);

    const descriptionDiv = document.createElement('p');
    descriptionDiv.textContent = `Description: ${description}`;
    weatherInfoDiv.appendChild(descriptionDiv);

    const iconDiv = document.createElement('img');
    iconDiv.src = `https://openweathermap.org/img/w/${weatherIcon}.png`;
    weatherInfoDiv.appendChild(iconDiv);

    const windSpeedDiv = document.createElement('p');
    windSpeedDiv.textContent = `Wind Speed: ${windSpeed} m/s`;
    weatherInfoDiv.appendChild(windSpeedDiv);

    const temperatureDiv = document.createElement('p');
    temperatureDiv.textContent = `Temperature: ${temperatureCelsius} °C`;
    weatherInfoDiv.appendChild(temperatureDiv);

    function setok(data) {
        let date_time = document.getElementById('date_time');
        let timestampOffset = data.timezone;
        const timestamp = Math.floor(Date.now() / 1000) + timestampOffset;
        const da = new Date(timestamp * 1000);
        const localTime = da.toLocaleString("en-US", {
            hour: "numeric",
            minute: "numeric",
            second: "numeric",
            timeZone: "UTC",
        });
        date_time.textContent = `Time:  ${localTime}`;
    }

    setok(data);
    clearInterval(intervalId);
    intervalId = setInterval(() => setok(data), 1000);
}

// THE content will only execute when all the document inside the html content has been phrased fully.
document.addEventListener('DOMContentLoaded', function () {
    getWeatherForCity('Mangalore');

    const searchIcon = document.getElementById('search-icon');
    searchIcon.addEventListener('click', function () {
        const city = document.getElementById('city').value;
        getWeatherForCity(city);
    });
});

function displayError(errorMessage) {
    const weatherInfoDiv = document.getElementById('weather-info');
    weatherInfoDiv.innerHTML = '';
    const errorParagraph = document.createElement('p');
    errorParagraph.textContent = errorMessage;
    errorParagraph.classList.add('error-message');
    weatherInfoDiv.appendChild(errorParagraph);
}
