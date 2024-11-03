<?php
require 'db.php';
require 'kita.php';

function main(): void
{
    get('/', 'dashboard');
    get('/api', 'api');
    get('/listen', 'listen');
}

function listen(): void
{
global $conn;
    $temperature = isset($_GET['temperature']) ? floatval($_GET['temperature']) : null;
    $humidity = isset($_GET['humidity']) ? floatval($_GET['humidity']) : null;
    $ph_level = isset($_GET['ph_level']) ? floatval($_GET['ph_level']) : null;
    if ($temperature !== null && $humidity !== null && $ph_level !== null) {
        $stmt = $conn->prepare("INSERT INTO data (temperature, humidity, ph_level) VALUES (?, ?, ?)");
        $stmt->bind_param("ddd", $temperature, $humidity, $ph_level);
        $stmt->execute();
        $stmt->close();
        $alert_message = null;
        if ($temperature > 30.0) {
            $alert_message = "High temperature detected: {$temperature}�C";
            $stmt = $conn->prepare("INSERT INTO alerts (type, value, message) VALUES ('Temperature', ?, ?)");
            $stmt->bind_param("ds", $temperature, $alert_message);
            $stmt->execute();
            $stmt->close();
        }
        if ($ph_level < 5.5 || $ph_level > 6.5) {
            $alert_message = "Dangerous pH level detected: {$ph_level}";
            $stmt = $conn->prepare("INSERT INTO alerts (type, value, message) VALUES ('pH Level', ?, ?)");
            $stmt->bind_param("ds", $ph_level, $alert_message);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(["status" => "success", "message" => "Data processed"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Missing or invalid parameters"]);
    }
}

function api(): void {
global $conn;

    header('Content-Type: application/json');

    if (isset($_GET['action']) && $_GET['action'] == 'get_data') {
        $data = [];
        $interval = isset($_GET['filter']) ? $_GET['filter'] : '24 HOUR'; // Default to the last 24 hours

        $allowed_intervals = ['1 HOUR', '3 HOUR', '6 HOUR', '12 HOUR', '24 HOUR'];
        if (!in_array($interval, $allowed_intervals)) {
            $interval = '24 HOUR'; // Set a default if an invalid interval is provided
        }

        $query = "SELECT temperature, humidity, ph_level, timestamp 
                  FROM data 
                  WHERE timestamp >= NOW() - INTERVAL $interval 
                  ORDER BY timestamp DESC 
                  LIMIT 50";
        $result = $conn->query($query);

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode($data);
    }

    if (isset($_GET['action']) && $_GET['action'] == 'get_alerts') {
        $alerts = [];
        $query = "SELECT type, value, message, timestamp FROM alerts ORDER BY timestamp DESC LIMIT 10";
        $result = $conn->query($query);

        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }

        echo json_encode($alerts);
    }
}

function dashboard(): void 
{
    ?>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Data Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
 body { font-family: Arial, sans-serif; background-color: #f4f4f9; color: #333; transition: background-color 0.3s, color 0.3s; }
        .container { width: 90%; margin: auto; padding: 20px; }
        .chart-container { flex: 1 1 45%; min-width: 300px; margin-bottom: 20px; }
        .alerts { background-color: #f8d7da; padding: 15px; margin-bottom: 20px; color: #721c24; border: 1px solid #f5c6cb; }
        .row { display: flex; flex-wrap: wrap; gap: 20px; }
        .dark-mode { background-color: #1e1e2f; color: #cfcfd4; }
        .dark-mode .alerts { background-color: #5a3d3f; color: #f8d7da; border-color: #4a2d2f; }
        .toggle-button { margin-bottom: 20px; padding: 10px 20px; cursor: pointer; border: none; background-color: #007bff; color: #fff; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sensor Data Dashboard</h1>

        <button id="toggleDarkMode" class="toggle-button">Toggle Dark Mode</button>

        <!-- Time Interval Selection -->
        <select id="timeInterval">
            <option value="1 HOUR">Last 1 Hour</option>
            <option value="3 HOUR">Last 3 Hours</option>
            <option value="6 HOUR">Last 6 Hours</option>
            <option value="12 HOUR">Last 12 Hours</option>
            <option value="24 HOUR" selected>Last 24 Hours</option>
        </select>

        <div id="alerts" class="alerts"></div>

        <div class="row">
            <div class="chart-container"><canvas id="temperatureChart"></canvas></div>
            <div class="chart-container"><canvas id="humidityChart"></canvas></div>
        </div>
        <div class="row">
            <div class="chart-container"><canvas id="phChart"></canvas></div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const timeInterval = document.getElementById('timeInterval');
            timeInterval.addEventListener('change', fetchData);
            fetchData();

            // Fetch and display alerts
            fetch('api?action=get_alerts')
                .then(response => response.json())
                .then(data => {
                    const alertsDiv = document.getElementById('alerts');
                    alertsDiv.innerHTML = data.length > 0 ? data.map(alert => `
                        <p><strong>${alert.type} Alert:</strong> ${alert.message} (Value: ${alert.value}, Time: ${alert.timestamp})</p>
                    `).join('') : "<p>No alerts</p>";
                });

            // Fetch data and update charts based on the selected interval
            function fetchData() {
                const selectedInterval = timeInterval.value;
                fetch(`api?action=get_data&filter=${selectedInterval}`)
                    .then(response => response.json())
                    .then(data => {
                        const labels = data.map(entry => entry.timestamp);
                        const temperatureData = data.map(entry => entry.temperature);
                        const humidityData = data.map(entry => entry.humidity);
                        const phData = data.map(entry => entry.ph_level);

                        renderChart('temperatureChart', labels, temperatureData, 'Temperature (°C)', 'rgba(255, 99, 132, 0.6)');
                        renderChart('humidityChart', labels, humidityData, 'Humidity (%)', 'rgba(54, 162, 235, 0.6)');
                        renderChart('phChart', labels, phData, 'pH Level', 'rgba(75, 192, 192, 0.6)');
                    });
            }

            // Chart rendering function
            function renderChart(canvasId, labels, data, label, color) {
                new Chart(document.getElementById(canvasId), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: data,
                            backgroundColor: color,
                            borderColor: color,
                            borderWidth: 1,
                            fill: false
                        }]
                    },
                    options: {
                        scales: {
                            x: { display: true, title: { display: true, text: 'Timestamp' } },
                            y: { display: true, title: { display: true, text: label } }
                        }
                    }
                });
            }
        });

        // Dark mode toggle functionality
        const toggleButton = document.getElementById('toggleDarkMode');
        toggleButton.addEventListener('click', function () {
            document.body.classList.toggle('dark-mode');
            toggleButton.textContent = document.body.classList.contains('dark-mode') ? 'Switch to Light Mode' : 'Switch to Dark Mode';
        });
    </script>
</body>
</html>
    <?php
}
