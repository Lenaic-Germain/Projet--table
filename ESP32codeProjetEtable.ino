#include <OneWire.h>
#include <DallasTemperature.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <MySQL_Connector_Arduino.h>

// WiFi & MQTT Setup
const char* ssid = "ESP32_Server";
const char* password = "12345678";
const char* mqtt_server = "192.168.4.2";
const char* mqtt_topic_temp = "temperature";
const char* mqtt_topic_rfid = "rfid";

// DS18B20 Temperature Sensor
const int oneWireBus = 4;
OneWire oneWire(oneWireBus);
DallasTemperature sensors(&oneWire);

// RFID Setup
#define SS_PIN  5
#define RST_PIN 22
MFRC522 rfid(SS_PIN, RST_PIN);

// MySQL Connection
IPAddress server_addr(192, 168, 4, 2);  // MySQL Server IP
char user[] = "etable_user";  // MySQL Username
char password_db[] = "yourpassword";  // MySQL Password
WiFiClient client;
MySQL_Connector conn(&client);

// Variables
int cowID = 0;

void setup_wifi() {
  Serial.println("Création du réseau WiFi local...");
  WiFi.softAP(ssid, password);
  IPAddress IP = WiFi.softAPIP();
  Serial.print("Adresse IP de l'ESP32 : ");
  Serial.println(IP);
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Connexion au serveur MQTT...");
    if (client.connect("ESP32Client")) {
      Serial.println("connecté");
    } else {
      Serial.print("Échec, rc=");
      Serial.print(client.state());
      Serial.println(" nouvelle tentative dans 5 secondes");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  setup_wifi();
  conn.connect(server_addr, 3306, user, password_db);
  sensors.begin();
  
  SPI.begin();
  rfid.PCD_Init();
  Serial.println("RFID Scanner Ready");
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  // Check for RFID Tag
  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    Serial.print("RFID Tag Detected: ");
    
    // Convert RFID UID to String
    String tagID = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
      tagID += String(rfid.uid.uidByte[i], HEX);
    }
    Serial.println(tagID);

    // Publish RFID Data
    char tagCharArray[20];
    tagID.toCharArray(tagCharArray, 20);
    client.publish(mqtt_topic_rfid, tagCharArray);

    // Update cowID in MySQL
    char query[200];
    sprintf(query, "UPDATE cows SET rfid_tag='%s' WHERE cowID='%d'", tagCharArray, cowID);
    
    if (conn.connected()) {
      MySQL_Query sql_query = MySQL_Query(&conn);
      if (sql_query.execute(query)) {
        Serial.println("RFID assigned to cow successfully");
      } else {
        Serial.println("SQL Error");
      }
    }

    Serial.print("Cow ID Assigned: ");
    Serial.println(cowID);

    cowID++;
    
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
  }

  // Read Temperature
  sensors.requestTemperatures();
  float temperatureC = sensors.getTempCByIndex(0);

  char tempString[8];
  dtostrf(temperatureC, 1, 2, tempString);
  Serial.print("Température envoyée: ");
  Serial.println(tempString);
  client.publish(mqtt_topic_temp, tempString);

  delay(5000);
}
