//Cámara inalámbrica
//incluir las librerias necesias
#include "WiFiClientSecure.h"
#include "WiFi.h"
#include "esp_camera.h"
#include "esp_timer.h"
#include "img_converters.h"
#include "Arduino.h"
#include "soc/soc.h"           // Para evitar caidas de tensión
#include "soc/rtc_cntl_reg.h"  //Para evitar caidas de tensión
#include "driver/rtc_io.h"
#include "ESPAsyncWebServer.h"
#include <StringArray.h>
#include <LittleFS.h>
#include <FS.h>
#include <HTTPClient.h>
#include "freertos/FreeRTOS.h"  //freeRTOS items to be used
#include "freertos/task.h"

//id del sensor
String sensorID = "W010101";

// configuración WiFi
const char *ssid = "YOUR_SSID";
const char *password = "YOUR_SSID_PASSWORD";

//Configuración de la IP de la cámara inalámbrica
IPAddress staticIP(192, 168, 1, 211);
IPAddress gateway(192, 168, 1, 1);
IPAddress subnet(255, 255, 255, 0);
IPAddress dns(192, 168, 1, 1);

//IP, puerto y página de recepción del HTTP POST con los datos en el Control Central
String serverName = "192.168.1.201";  
String serverPath = "/camara";        
const int serverPort = 80;

//mecanismos de control de subida en el Control Central
String url_upload_block = "http://192.168.1.201/iniciar_upload";  //HTTP para indicar el inicio de envío de fotografía
String url_upload_end = "http://192.168.1.201/fin_upload";        //HTTP para indicar el inicio de envío de fotografía

WiFiClient client;
File file2;
HTTPClient http;

//crear un servidor web asíncrono en el puerto 80
AsyncWebServer server(80);

//variable de control de la disponibilidad de la cámara
boolean takeNewPhoto = false;

//archivo que almacenará la fotografía en LittleFS
#define FILE_PHOTO "/photo.jpg"

//variables de control de la cámara
// pins del módulo de la cámara OV2640
#define PWDN_GPIO_NUM 32
#define RESET_GPIO_NUM -1
#define XCLK_GPIO_NUM 0
#define SIOD_GPIO_NUM 26
#define SIOC_GPIO_NUM 27
#define Y9_GPIO_NUM 35
#define Y8_GPIO_NUM 34
#define Y7_GPIO_NUM 39
#define Y6_GPIO_NUM 36
#define Y5_GPIO_NUM 21
#define Y4_GPIO_NUM 19
#define Y3_GPIO_NUM 18
#define Y2_GPIO_NUM 5
#define VSYNC_GPIO_NUM 25
#define HREF_GPIO_NUM 23
#define PCLK_GPIO_NUM 22

camera_config_t config;

//página web que se mostrará cuando se acceda a la cámara mediante HTTP
const char index_html[] PROGMEM = R"rawliteral(
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
	  <h1>Dispositivo: C&aacute;mara Autom&aacute;tica ID C110022</h1>
	</center>
</body>
</html>)rawliteral";

//función para el envío de la fotografía tomada mediante el envío de HTTP POST, utilizando el planificador de procesos del microcontrolador
void sendPhoto2_task(void *pvParameters) {
 
  //variables de control
  String getAll;
  String getBody;
  size_t fbLen;
  int remainingData;

  //leer el archivo con la fotografía
  File file2 = LittleFS.open(FILE_PHOTO, "r");
  if (!file2) {
    Serial.println("Error opening the file");
  }

  Serial.println("Connecting to server: " + serverName + " : " + (String)serverPort);

  //variables necesarias para el uso del planificador de procesos
  TickType_t xLastWakeTime = xTaskGetTickCount();
  const TickType_t xFrequency = 100;  //delay for mS

  //conectar al Control Central y enviar la fotografía
  if (client.connect(serverName.c_str(), serverPort)) {
    Serial.println("Connection successful!");

    //definir las cabeceras y delimitadores del mensaje HTTP POST
    String head = "--TFGMartinGonzalez\r\nContent-Disposition: form-data; name=\"imageFile\"; filename=\"esp32-cam.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n";
    String tail = "\r\n--TFGMartinGonzalez--\r\n";

    uint32_t imageLen = file2.size();
    uint32_t extraLen = head.length() + tail.length();
    uint32_t totalLen = imageLen + extraLen;

    client.println("POST " + serverPath + " HTTP/1.1");
    client.println("Host: " + serverName);
    client.println("Content-Length: " + String(totalLen));
    client.println("Content-Type: multipart/form-data; boundary=TFGMartinGonzalez");
    client.println();
    client.print(head);

    Serial.println("--------------------");
    Serial.println(file2.size());
    Serial.println("--------------------");

    //se envía el archivo en tramos de 1024 bytes
    char fbBuf[1024];

    int readData = file2.readBytes(fbBuf, 1024);
    size_t fbLen = file2.size();
    int remainingData = file2.size();
    for (size_t n = 0; n < fbLen; n = n + 1024) {
      if (n + 1024 < fbLen) {
        client.write(fbBuf, 1024);
        readData = file2.readBytes(fbBuf, 1024);
        //mostrar por el puerto serie el proceso de envío
        Serial.print("Read: ");
        Serial.print(readData);
        Serial.print(" Total: ");
        Serial.print(fbLen);
        Serial.print(" n: ");
        Serial.print(n);
        Serial.print(" Remaining: ");
        remainingData = remainingData - 1024;
        Serial.println(remainingData);
      } else if (fbLen % 1024 > 0) {
        //envío de la última parte
        size_t remainder = fbLen % 1024;
        client.write(fbBuf, remainder);
      }
      //intervalo de ejecución de la tarea establecida
      vTaskDelay(100 / portTICK_PERIOD_MS);
    }
    //envio de la cola
    Serial.println("\nSending Tail...");
    client.print(tail);

    int timoutTimer = 10000;
    long startTimer = millis();
    boolean state = false;

    //recoger la respuesta, si la hubo
    while ((startTimer + timoutTimer) > millis()) {
      Serial.print(".");
      delay(100);
      while (client.available()) {
        char c = client.read();
        if (c == '\n') {
          Serial.print(c);
          if (getAll.length() == 0) { state = true; }
          getAll = "";
        } else if (c != '\r') {
          getAll += String(c);
        }
        if (state == true) { getBody += String(c); }
        startTimer = millis();
      }
      if (getBody.length() > 0) { break; }
    }
    //eliminar la tarea del planificador de tareas del microcontrolador
    vTaskDelete(NULL);

    client.stop();
    //mostrar en el puerto serie el mensaje devuelto, en caso de haberlo
    Serial.println();
    Serial.println(getBody);
  } else {
    getBody = "Connection to " + serverName + " failed.";
    Serial.println(getBody);
  }

  //eliminar la tarea del planificador de tareas del microcontrolador
  vTaskDelete(NULL);  //borramos la tarea

  //eliminar el bloque de subidas en el Control Central
  http.begin(client, url_upload_end);
  http.end();

  ESP.restart();
}

// función para comprobar si se ha tomado una fotografía correctamente
bool checkPhoto(fs::FS &fs) {
  File f_pic = fs.open(FILE_PHOTO);
  unsigned int pic_sz = f_pic.size();
  return (pic_sz > 100);
}

//función para tomar una fotografía y guardarla en la memoria interna LittleFS
void capturePhotoSaveLittleFS(void) {
  camera_fb_t *fb = NULL;  // puntero
  bool ok = 0;             // booleano indicador de si la fotografía se ha tomado de forma correcta o no

  do {
    //sacar una fotografía
    Serial.println("Taking a photo...");

    fb = esp_camera_fb_get();
    if (!fb) {
      Serial.println("Camera capture failed");
      return;
    }

    //guardar la fotografía en LittleFS
    Serial.printf("Picture file name: %s\n", FILE_PHOTO);
    File file = LittleFS.open(FILE_PHOTO, FILE_WRITE);

    //almacenar la fotografía
    if (!file) {
      Serial.println("Failed to open file in writing mode");
    } else {
      file.write(fb->buf, fb->len);
    }
    file.close();
    esp_camera_fb_return(fb);

    //comprobar si se ha guardado la fotografía de forma correcta en LittleFS
    ok = checkPhoto(LittleFS);
  } while (!ok);
}

//función de inicialización del sensor
void setup() {
  // abrir el puerto serie para mostrar mensajes de control
  Serial.begin(115200);

  //establecer la configuración WiFi
  if (WiFi.config(staticIP, gateway, subnet, dns, dns) == false) {
    Serial.println("Configuration failed.");
  }
  //conectar a la WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  //preparar la memoria interna para su uso
  if (!LittleFS.begin(true)) {
    Serial.println("An Error has occurred while mounting LittleFS");
    ESP.restart();
  } else {
    delay(500);
    Serial.println("LittleFS mounted successfully");
  }

  // OV2640 camera module
  // configuración de la cámara
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  //adecuación de la configuración según el funcionamiento de la PSRAM
  if (psramFound()) {
    config.frame_size = FRAMESIZE_UXGA;
    config.jpeg_quality = 10;
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_SVGA;
    config.jpeg_quality = 12;
    config.fb_count = 1;
  }

  // inicializar la cámara
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    ESP.restart();
  }

  //página web a servir para el acceso a raíz
  server.on("/", HTTP_GET, [](AsyncWebServerRequest *request) {
    request->send_P(200, "text/html", index_html);
  });

  //página web a servir y funcionamiento interno en caso de acceso a /capture
  server.on("/capture", HTTP_GET, [](AsyncWebServerRequest *request) {
    //permitir tomar una foto nueva
    takeNewPhoto = true;

    //devolver mensaje informativo
    request->send_P(200, "text/plain", "Taking Photo");
  });

  //página web a servir y funcionamiento interno en caso de acceso a /enviar_foto
  server.on("/enviar_foto", HTTP_GET, [](AsyncWebServerRequest *request) {
    //denegar el tomar una foto nueva (ya que sobreescribiría la foto tomada anteriormente)
    takeNewPhoto = false;

    //crear un bloqueo de subidas en el Control Central
    http.begin(client, url_upload_block);
    http.end();

    //crear la tarea en el microcontrolador para el envío de la fotografía
    xTaskCreatePinnedToCore(sendPhoto2_task, "sendPhoto2_task", 20000, NULL, 3, NULL, 1);

    //devolver mensaje informativo
    request->send_P(200, "text/plain", "Photo Sent");
  });

  //iniciar el servidor web
  server.begin();
}

//función que gestiona el tiempo de ejecución
void loop() {
  //sacar una fotografía nueva si es necesario
  if (takeNewPhoto) {
    capturePhotoSaveLittleFS();
    takeNewPhoto = false;
  }
  delay(1);
}
