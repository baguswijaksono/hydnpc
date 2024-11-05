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


void getConf() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(endpoint);

    int httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println("Received JSON:");
      Serial.println(payload);

      StaticJsonDocument<512> doc;
      DeserializationError error = deserializeJson(doc, payload);

      if (!error) {
        // Access JSON fields, assuming they are "setting1" and "setting2"
        const char* setting1 = doc["setting1"];
        int setting2 = doc["setting2"];

        Serial.println("Parsed Settings:");
        Serial.print("Setting 1: ");
        Serial.println(setting1);
        Serial.print("Setting 2: ");
        Serial.println(setting2);
      } else {
        Serial.print("JSON deserialization failed: ");
        Serial.println(error.c_str());
      }
    } else {
      Serial.print("Error on HTTP request: ");
      Serial.println(httpCode);
    }

    http.end();
  } else {
    Serial.println("WiFi Disconnected");
  }
}
