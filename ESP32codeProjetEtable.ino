#include <OneWire.h>
#include <DallasTemperature.h>
#include <WiFi.h>
#include <PubSubClient.h>

// Configuration du réseau WiFi local créé par l'ESP32
const char* ssid = "ESP32_Server";
const char* password = "12345678";
const char* mqtt_server = "192.168.4.2 ";
const char* mqtt_topic = "temperature";

// DS18B20 Setup
const int oneWireBus = 4;     
OneWire oneWire(oneWireBus);
DallasTemperature sensors(&oneWire);

// WiFi et MQTT client setup
WiFiClient espClient;
PubSubClient client(espClient);

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
  client.setServer(mqtt_server, 1883);
  sensors.begin();
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  sensors.requestTemperatures(); 
  float temperatureC = sensors.getTempCByIndex(0);

  char tempString[8];
  dtostrf(temperatureC, 1, 2, tempString);
  Serial.print("Température envoyée: ");
  Serial.println(tempString);

  client.publish(mqtt_topic, tempString);

  delay(5000);
}
