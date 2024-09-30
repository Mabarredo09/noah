<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather and Flood Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        .weather-container {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            max-width: 400px;
        }
        .input-container {
            margin-bottom: 15px;
        }
        #map {
            height: 500px;
            margin-top: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="weather-container">
        <h3>Weather and Flood Map</h3>
        <div class="input-container">
            <input type="text" id="cityInput" placeholder="Enter city name" />
            <button onclick="getWeather()">Get Weather</button>
        </div>
        <p id="weather">Enter a city and click "Get Weather" to see the forecast and flood map.</p>
    </div>
    <div id="map"></div>
    
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Replace with your valid Weatherstack API key
        const apiKey = 'ff187ad2c39781b382a240bd841c3a30'; 

        // Initialize the map
        const map = L.map('map').setView([14.5995, 120.9842], 8); // Default to Manila, PH

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Add fictional flood risk layer (replace with real flood data)
        const floodLayerUrl = 'https://geojson.io/#map=9.19/15.6469/120.9945'; // Replace with real GeoJSON source
        fetch(floodLayerUrl)
            .then(response => response.json())
            .then(floodData => {
                // Add flood risk layer to the map
                L.geoJSON(floodData, {
                    style: function (feature) {
                        return { color: 'red' };
                    },
                    onEachFeature: function (feature, layer) {
                        layer.bindPopup(`<b>Flood Risk Area</b><br>${feature.properties.name}`);
                    }
                }).addTo(map);
            })
            .catch(error => {
                console.error('Error loading flood data:', error);
            });

        async function getWeather() {
            // Get the city input from the user
            const city = document.getElementById('cityInput').value.trim();
            
            if (city === "") {
                document.getElementById('weather').innerHTML = 'Please enter a city name.';
                return;
            }

            try {
                const response = await fetch(`https://api.weatherstack.com/current?access_key=${apiKey}&query=${city}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const data = await response.json();

                if (data.current) {
                    document.getElementById('weather').innerHTML = `
                        <p>City: ${data.location.name}</p>
                        <p>Temperature: ${data.current.temperature}°C</p>
                        <p>Weather: ${data.current.weather_descriptions[0]}</p>
                        <p>Humidity: ${data.current.humidity}%</p>
                        <p>Wind Speed: ${data.current.wind_speed} m/s</p>
                    `;

                    // Set map view to the selected city location
                    const lat = data.location.lat;
                    const lon = data.location.lon;
                    map.setView([lat, lon], 10);

                    // Add a marker to show the weather location
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup(`<b>${data.location.name}</b><br>${data.current.weather_descriptions[0]}`)
                        .openPopup();

                } else {
                    document.getElementById('weather').innerHTML = 'Unable to retrieve valid weather data.';
                }
            } catch (error) {
                document.getElementById('weather').innerHTML = 'Unable to retrieve weather data.';
                console.error('Error fetching weather data:', error);
            }
        }
    </script>
</body>
</html>
