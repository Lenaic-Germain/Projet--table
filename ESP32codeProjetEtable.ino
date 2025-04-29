#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include <SPI.h>
#include <MFRC522.h>

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
IPAddress server_addr(192, 168, 4, 2);  // IP du serveur MariaDB
char user[] = "etable_user";  // Nom d'utilisateur MariaDB
char password_db[] = "yourpassword";  // Mot de passe MariaDB

WiFiClient espClient;
PubSubClient client(espClient);
MySQL_Connection conn((Client *)&espClient);

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
  client.setServer(mqtt_server, 1883);

  // Connexion à la base de données
  Serial.print("Connexion à MariaDB...");
  if (conn.connect(server_addr, 3306, user, password_db)) {
    Serial.println(" Connecté !");
  } else {
    Serial.println(" Échec de connexion !");
  }

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

  // Vérifier la présence d'un tag RFID
  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    Serial.print("RFID Tag Detecté: ");

    // Convertir l'UID RFID en String
    String tagID = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
      tagID += String(rfid.uid.uidByte[i], HEX);
    }
    Serial.println(tagID);

    // Convertir String en char array
    char tagCharArray[30];
    tagID.toCharArray(tagCharArray, sizeof(tagCharArray));

    // Publier les données RFID via MQTT
    if (client.publish(mqtt_topic_rfid, tagCharArray)) {
      Serial.println("RFID publié avec succès !");
    } else {
      Serial.println("Échec de la publication MQTT");
    }

    delay(100);

    // Mettre à jour cowID dans la base de données
    if (conn.connected()) {
      char query[200];
      sprintf(query, "UPDATE cows SET rfid_tag='%s' WHERE cowID='%d'", tagCharArray, cowID);

      // Création d'un curseur MySQL pour exécuter la requête
      MySQL_Cursor *cur = new MySQL_Cursor(&conn);
      cur->execute(query);
      delete cur;  // Libération de la mémoire
      Serial.println("RFID assigné à la vache avec succès");
    } else {
      Serial.println("Connexion à MariaDB perdue !");
    }

    Serial.print("Cow ID Assigné: ");
    Serial.println(cowID);

    cowID++;

    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
  }

  // Lire la température
  sensors.requestTemperatures();
  float temperatureC = sensors.getTempCByIndex(0);

  char tempString[8];
  dtostrf(temperatureC, 1, 2, tempString);
  Serial.print("Température envoyée: ");
  Serial.println(tempString);
  client.publish(mqtt_topic_temp, tempString);

  delay(5000);
}
