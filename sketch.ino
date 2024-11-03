#include <ESP8266WiFi.h>          // or #include <WiFi.h> for ESP32
#include <ESP8266HTTPClient.h>    // or #include <HTTPClient.h> for ESP32
#include <WiFiClientSecure.h>     // Secure client for HTTPS connections

// Replace with your network credentials and server URL
const char* ssid = "";
const char* password = "";
const char* serverUrl = "";
const char* token = "your_token_here";  // Replace with your actual token

// Sensor readings (replace with actual sensor code)
float temperature = 29.5;
float humidity = 65.0;
float phLevel = 6.3;

void setup() {
    Serial.begin(115200);
    WiFi.begin(ssid, password);

    // Connect to Wi-Fi
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nConnected to Wi-Fi");
}

void loop() {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClientSecure client;   // Secure client for HTTPS
        client.setInsecure();      // Bypass SSL verification (for testing only)

        HTTPClient http;

        // Construct URL with parameters
        String url = String(serverUrl) + "?temperature=" + String(temperature) + 
                     "&humidity=" + String(humidity) + 
                     "&ph_level=" + String(phLevel) + 
                     "&token=" + String(token);

        http.begin(client, url);   // Pass the secure client and URL

        // Send HTTP GET request
        int httpResponseCode = http.GET();

        // Check response
        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println("Server Response: " + url);
        } else {
            Serial.println("Error on sending GET: " + String(httpResponseCode));
        }

        // End HTTP connection
        http.end();
    } else {
        Serial.println("Disconnected from Wi-Fi, trying to reconnect...");
        WiFi.reconnect();
    }

    delay(60000); // Delay to control the frequency of sending data
}
