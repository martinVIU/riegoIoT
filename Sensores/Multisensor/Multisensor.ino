// MultiSensor de Humedad
// incluir las librerias necesarias
#include "driver/adc.h"
#include "esp_adc_cal.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// formatear la memoria flash interna si no se inicializa correctamente
#define FORMAT_LittleFS_IF_FAILED true

// definir el formato de la pantalla LCD
LiquidCrystal_I2C lcd(0x27, 16, 2); // Dirección de la pantalla LCD es 0x3F, 16 = número de caracteres, 2 = número de líneas

// configuración de los puertos GPIO donde se encuentran los LEDS
const int ledPin = 5;
const int ledPin2 = 16;
const int ledPin3 = 17;

// configuración de los puertos GPIO donde se encuentran los sensores
const int sensorPin = 34;
const int sensorPin2 = 35;

// se definen las variables que gestionan la humedad
float reading1;
float reading2;

// se definen los valores máximo y mínimo de lecturas admitidas. Estos dependerán de la zona donde se situe el sensor.
const int readingMax = 3521;  // Valor humedad ambiente para el sensor 1
const int readingMin = 1670;  // Valor 100% humedad para el sensor 1
const int readingMax2 = 3532; // Valor humedad ambiente para el sensor 2
const int readingMin2 = 1585; // Valor 100% humedad para el sensor 2

// configuración Wifi
const char *ssid = "YOUR_SSID";
const char *password = "YOUR_SSID_PASSWORD";

// configuración de la IP, puerto y página que recibirá los datos de la humedad en el Control Central
String serverName = "192.168.1.201";
String serverPath = "/recibir_post_humedad";
const int serverPort = 80;

// dirección http resultante
String serverURL = "http://" + serverName + ":" + serverPort + serverPath;

// configuración del separador de datos por defecto y la variable que almacenará los datos a enviar
String separator = "&";
String dataToSend = "";

// configuración de la IP del sensor
IPAddress staticIP(192, 168, 1, 210);
IPAddress gateway(192, 168, 1, 1);
IPAddress subnet(255, 255, 255, 0);
IPAddress dns(192, 168, 1, 1);

// configuración de la id del sensor
String sensorID = "H1123321";

// configuración del cliente WiFi
WiFiClient client;

// configuración del servidor web, aceptando conexiones en el puerto 80
WebServer server(80);

// definición de la página web mostrada cuando se conecta al sensor directamente
String index_html = R"rawliteral(
<!DOCTYPE HTML><html>
<head>
<meta charset="utf-8">
<title>TFG - Mart&iacute;n Gonz&aacute;lez Dom&iacute;nguez<</title>
</head>

<body>
	<center>
	  <img src="https://carrerasuniversitarias.pe/logos/original/logo-universidad-internacional-de-valencia.png">		
<p>
<h1>Trabajo de Fin de Grado</h1>
<h2>Sistema de Riego IoT</h2>
<h3>Mart&iacute;n Gonz&aacute;lez Dom&iacute;nguez </h3></p></center>
	<center>
	  <h1>Dispositivo: MultiSensor Humedad ID %id_sensor%</h1>
	</center>
</body>
</html>)rawliteral";

// Redefinir la funcion map para que en vez de long use float
float map2(float x, float in_min, float in_max, float out_min, float out_max)
{
  return (x - in_min) * (out_max - out_min) / (in_max - in_min) + out_min;
}

// función que comprueba si el valor de humedad se encuentra dentro de los límites establecidos para el sensor
// en caso de superarse el valor mínimo o máximo, establece el valor al valor mínimo o máximo correspondiente
float readingToHumidity(int value, int sensor)
{
  int reading_min, reading_max;
  if (sensor == 0)
  {
    reading_min = readingMin;
    reading_max = readingMax;
  }
  if (sensor == 1)
  {
    reading_min = readingMin2;
    reading_max = readingMax2;
  }

  value = constrain(value, reading_min, reading_max);
  float humidity = map2(value, reading_min, reading_max, 0, 100);

  // Debido a que 100% es la humedad ambiente y 0% es el 100% de humedad hay que restar la humedad detectar al máximo de humedad posible
  humidity = 100.0 - humidity;
  return humidity;
}

// función que envía los datos de humedad detectados mediante el método HTTP POST al Control Central
void sendHTTPPOST()
{

  // configuración del cliente HTTP
  HTTPClient http;
  http.begin(serverURL); // Destino del HTTP request

  // cabeceras
  http.addHeader("Connection", "close");
  http.addHeader("Content-Type", "raw"); // Se define el tipo de contenido en la cabecera
  String dataLength = String(dataToSend.length());
  http.addHeader("Content-Length", dataLength); // Se añade la longitud de los a enviar a la cabecera

  // envío de datos
  int httpResponseCode = http.POST(dataToSend);

  // Si se recibe error, mostrarlo por consola
  if (httpResponseCode > 0)
  {
    String response = http.getString(); // Obtener respuesta
    Serial.println(httpResponseCode);   // Mostrar el código HTTP devuelto
    Serial.println(response);           // Mostrar la respuesta devuelta
  }
  else
  {
    // Mostrar el mensaje de error
    Serial.print("Error on sending POST: ");
    Serial.println(httpResponseCode);
  }
  http.end();
}

// función que lee los valores de los sensores y prepara la cadena que va a ser enviada al Control Central
void readDataFromSensors()
{

  int humidity = analogRead(sensorPin);
  reading1 = readingToHumidity(humidity, 0);
  humidity = analogRead(sensorPin2);
  reading2 = readingToHumidity(humidity, 1);

  dataToSend = sensorID + separator + String(reading1) + separator + String(reading2);
}

// función que muestra una página web con el valor del sensor cuando se conecta directamente al sensor
void displayIndexPage()
{
  index_html.replace("%id_sensor%", sensorID);
  server.send(200, "text/html", index_html);
}

// función que muestra la página relacionada con el envío de datos del sensor, procede a la lectura de los datos actuales y a continuación los envía
void sendDataPOST()
{
  server.send(200, "text/html", "Sending sensor data");
  readDataFromSensors();
  digitalWrite(ledPin3, HIGH);
  sendHTTPPOST();

  // apagar LED de transmision de datos
  digitalWrite(ledPin3, LOW);
}

// función que gestiona el arranque del sensor
void setup()
{
  // abrir el puerto serie para mostrar mensajes error
  Serial.begin(9600);
  // preparar la conexión de las luces LED
  pinMode(ledPin, OUTPUT);
  pinMode(ledPin2, OUTPUT);
  pinMode(ledPin3, OUTPUT);

  // se inicia la pantalla LCD
  lcd.init();
  lcd.backlight();

  // se preparar la conexión Wifi y se establece la conexión
  WiFi.mode(WIFI_STA);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  if (WiFi.config(staticIP, gateway, subnet, dns, dns) == false)
  {
    Serial.println("Configuration failed.");
  }

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED)
  {
    Serial.print(".");
    delay(500);
  }
  // se enciende el LED de Power
  digitalWrite(ledPin, HIGH);

  // se muestra la dirección IP
  Serial.println();
  Serial.print("ESP32 IP Address: ");
  Serial.println(WiFi.localIP());

  // se establecen las páginas disponibles en el sensor
  server.on("/", displayIndexPage);
  server.on("/enviar_post", sendDataPOST);

  // se inicializa el servidor HTTP
  server.begin();
  Serial.println("HTTP server started");
}

// función que gestiona el tiempo de ejecución
void loop()
{
  // se comprueba si se está conectado a la WiFi
  if (WiFi.status() == WL_CONNECTED)
  {
    // Encender LED de Wifi
    digitalWrite(ledPin2, HIGH);
  }
  else
  {
    // Apagar LED de Wifi
    digitalWrite(ledPin2, LOW);
  }

  // se comprueba si se ha recibido alguna conexión HTTP al sensor por el puerto 80
  server.handleClient();

  // se lee el valor de los sensores y se muesta por pantalla
  readDataFromSensors();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Sensor1: ");
  lcd.print(reading1);
  lcd.setCursor(0, 1);
  lcd.print("Sensor2: ");
  lcd.print(reading2);

  // se establece un periodo de espera entre refresco de datos
  delay(1000);
}