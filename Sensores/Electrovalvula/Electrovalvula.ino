// Electroválvula
// librerias necesarias
#include "WiFi.h"
#include "ESPAsyncWebSrv.h"
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// definir las características de la pantalla LCD
#define SCREEN_WIDTH 128    // OLED anchura de la pantalla, en pixels
#define SCREEN_HEIGHT 64    // OLED altura de la pantalla, en pixels
#define OLED_RESET -1       // Pin de reset
#define SCREEN_ADDRESS 0x3C /// dirección de la pantalla, aparece en la ficha técnica

// definir el objeto pantalla
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// configuración de la WiFi
const char *ssid = "YOUR_SSID";
const char *password = "YOUR_SSID_PASSWORD";

// configuración de los pines para las luces LED
const int ledPin_Yellow = 14;
const int ledPin_Red = 27;
const int ledPin_Green = 26;
const int ledPin_Blue = 25;

// configuración de los pines del conmutador y del caudalímetro
const int RELAY_PIN = 32;
const int FLOW_PIN = 5;

bool relayOn = false;

// definir la IP de la Electroválvula
IPAddress staticIP(192, 168, 1, 212);
IPAddress gateway(192, 168, 1, 1);
IPAddress subnet(255, 255, 255, 0);
IPAddress dns(192, 168, 1, 1);

WiFiClient client;
HTTPClient http;

// crear un servidor web asíncrono en el puerto 80
AsyncWebServer server(80);

// variables para el control de caudal
long currentMillis = 0;
long previousMillis = 0;
int interval = 1000;

// configuración referente al caudalímetro
float calibrationFactorDefined = 4.5;
volatile byte pulseCount;
byte pulse1Sec = 0;

// configuración referente al control de flujo
float flowRate;
unsigned int flowMilliLitres;
unsigned long totalMilliLitres;
float flowLitres;
float totalLitres;

// id del sensor
String sensorID = "V010101";

// configuración de la conexión y de la conexión al Control Central
String dataToSend = "";
String separator = "&";
String serverName = "192.168.1.201";
String serverPath = "/recibir_post_valvula";
const int serverPort = 80;
String serverURL = "http://" + serverName + ":" + serverPort + serverPath;

// función que cuenta de pulsos
void IRAM_ATTR pulseCounter()
{
  pulseCount++;
}

// función de envío de datos por método HTTP POST
void sendHTTPPOST()
{

  HTTPClient http;

  http.begin(serverURL); // destino del HTTP request
  http.addHeader("Connection", "close");
  http.addHeader("Content-Type", "raw"); // cabecera indicando el tipo de contenido
  String longitud = String(dataToSend.length());
  http.addHeader("Content-Length", longitud);
  int httpResponseCode = http.POST(dataToSend); // envio de la petición HTTP
  Serial.println();
  Serial.print("datapost POST: ");
  Serial.println(dataToSend);

  if (httpResponseCode > 0)
  {
    String response = http.getString(); // Obtener respuesta
    Serial.println(httpResponseCode);   // Mostrar codigo devuelto
    Serial.println(response);           // Mostrar respuesta recibida
  }
  else
  {
    Serial.print("Error on sending POST: ");
    Serial.println(httpResponseCode);
  }
  http.end();
}

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
	  <h1>Dispositivo: ElectroV&aacute;lvula Caudal&iacute;metro ID %id_sensor%</h1>
	</center>
</body>
</html>)rawliteral";

void setup()
{
  // inicializar la comunicació por puerto serie
  Serial.begin(115200);

  // establecer los pines que van a ser utilizados
  pinMode(ledPin_Yellow, OUTPUT);
  pinMode(ledPin_Blue, OUTPUT);
  pinMode(ledPin_Green, OUTPUT);
  pinMode(ledPin_Red, OUTPUT);
  pinMode(RELAY_PIN, OUTPUT);

  // Estado inicial del conmutador: Apagado
  digitalWrite(RELAY_PIN, LOW);

  // Estado inicial de las luces LED: Apagadas
  digitalWrite(ledPin_Yellow, LOW);
  digitalWrite(ledPin_Blue, LOW);
  digitalWrite(ledPin_Green, LOW);
  digitalWrite(ledPin_Red, LOW);

  // inicializar el caudalímetro
  pinMode(FLOW_PIN, INPUT_PULLUP);

  // activar el LED de corriente
  digitalWrite(ledPin_Green, HIGH);

  /*
    //inicializar la pantalla LCD
    if (!display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) {
      Serial.println(F("SSD1306 display allocation failed"));
    }
  */

  // conectar a la red WiFi
  if (WiFi.config(staticIP, gateway, subnet, dns, dns) == false)
  {
    Serial.println("Configuration failed.");
  }

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED)
  {
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }
  if (WiFi.status() == WL_CONNECTED)
  {
    digitalWrite(ledPin_Blue, HIGH);
  }

  Serial.println(WiFi.localIP());

  // inicializar las variables del caudalímetro
  pulseCount = 0;
  flowRate = 0.0;
  flowMilliLitres = 0;
  totalMilliLitres = 0;
  previousMillis = 0;

  attachInterrupt(digitalPinToInterrupt(FLOW_PIN), pulseCounter, RISING);

  // acción a realizar cuando se visite la página cambiar mediante HTTP GET
  server.on("/cambiar", HTTP_GET, [](AsyncWebServerRequest *request)
            {
    String message = "";
    if (relayOn == false) {
      //si se activa el conmutador, encender el LED Rojo
      relayOn = true;
      digitalWrite(RELAY_PIN, HIGH);
      digitalWrite(ledPin_Red, HIGH);
      message = "Relay ON";
    } else {
      //si se desactiva el conmutador, apagar el LED Rojo
      relayOn = false;
      digitalWrite(RELAY_PIN, LOW);
      digitalWrite(ledPin_Red, LOW);
      message = "Relay OFF";
    }
    //enviar codigo OK junto con mensaje
    request->send(200, "text/html", message); });

  server.on("/", HTTP_GET, [](AsyncWebServerRequest *request)
            {
    index_html.replace("%id_sensor%", sensorID);
    request->send(200, "text/html", index_html); });

  server.on("/enviar_post", HTTP_GET, [](AsyncWebServerRequest *request)
            {
    //enviar código OK 
    request->send(200, "text/html", "Sending Data");
    
    //preparar los datos a enviar y encender el LED amarillo mientras se envían
    digitalWrite(ledPin_Yellow, HIGH);
    dataToSend = sensorID + separator;
    if (relayOn == true) {
      dataToSend = dataToSend + "1";
    } else {
      dataToSend = dataToSend + "0";
    }
    dataToSend = dataToSend + separator + String(float(flowRate));
    sendHTTPPOST();
    
    //una vez se han enviado los datos, apagar el LED amarillo
    digitalWrite(ledPin_Yellow, LOW); });

  // iniciar el servidor Web
  server.begin();

  // inicializar la pantalla LCD
  display.begin(SSD1306_SWITCHCAPVCC, 0x3C); // initialize with the I2C addr 0x3C (128x64)
  display.clearDisplay();
}

void loop()
{

  // obtener el tiempo actual
  currentMillis = millis();
  if (currentMillis - previousMillis > interval)
  {

    pulse1Sec = pulseCount;
    pulseCount = 0;

    // calcular en número de pulsos que han ocurrido en el periodo de tiempo, para poder extrapolarlo a un segundo completo
    // se utiliza el factor de corrección para lograr una medición más precisa
    flowRate = ((1000.0 / (millis() - previousMillis)) * pulse1Sec) / calibrationFactorDefined;
    previousMillis = millis();

    // convertir los litros por minuto a mililitros por segundo
    flowMilliLitres = (flowRate / 60) * 1000;

    // añadir los litros detectados al total de litros
    flowLitres = (flowRate / 60);
    totalLitres += flowLitres;

    // enviar los datos por el puerto serie
    Serial.print("Flow: ");
    Serial.print(int(flowRate));
    Serial.println("L/min");

    // limpiar pantalla y mostrar los valores actuales detectados
    display.clearDisplay();
    display.setCursor(10, 0);
    display.setTextSize(1);
    display.setTextColor(WHITE);
    display.print("Medidor de caudal");
    display.setCursor(0, 20);
    display.setTextSize(2);
    display.setTextColor(WHITE);
    display.print("C:");
    display.print(float(flowRate));
    display.setCursor(100, 28);
    display.setTextSize(1);
    display.print("L/M");
    display.setCursor(0, 45);
    display.setTextSize(2);
    display.setTextColor(WHITE);
    display.print("T:");
    display.print(totalLitres);
    display.setCursor(100, 53);
    display.setTextSize(1);
    display.print("L");
    display.display();
  }
}
