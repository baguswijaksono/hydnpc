### Steps to Install ESP8266 Board Support

1. **Open the Arduino IDE.**

2. **Add the ESP8266 URL to the Board Manager:**
   - Go to **File > Preferences**.
   - In the **Additional Boards Manager URLs** field, paste the following URL:
     ```
     http://arduino.esp8266.com/stable/package_esp8266com_index.json
     ```
   - If there’s already another URL, separate it with a comma.
   - Click **OK** to close the Preferences window.

3. **Install the ESP8266 Board Package:**
   - Go to **Tools > Board > Boards Manager**.
   - In the **Boards Manager** window, type "ESP8266" in the search bar.
   - Find the entry "ESP8266 by ESP8266 Community," select it, and click **Install**.

4. **Select the ESP8266 Board:**
   - Once installation is complete, go to **Tools > Board** and select the appropriate ESP8266 board (e.g., NodeMCU, Wemos D1 Mini).

---

### Steps to Install the ArduinoJson Library

1. **Open Library Manager:**
   - Go to **Sketch > Include Library > Manage Libraries** to open the Library Manager.

2. **Search for ArduinoJson:**
   - In the **Library Manager** search bar (top right corner), type "ArduinoJson."
   - Locate "ArduinoJson" in the list (ensure it's from the author Benoit Blanchon).

3. **Install the Library:**
   - Click on the entry for **ArduinoJson**.
   - Click the **Install** button to install the latest version of the library.
