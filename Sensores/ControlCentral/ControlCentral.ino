//cargar las bibliotecas necesarias
#include "WiFi.h"
#include "ESPAsyncWebSrv.h"
#include <LittleFS.h>
#include <HTTPClient.h>
#define USE_SERIAL Serial

//si no se inicia correctamente la memoria interna, formatearla
#define FORMAT_LittleFS_IF_FAILED true

//configuración de la red WiFi
const char *ssid = "YOUR_SSID";
const char *password = "YOUR_SSID_PASSWORD";

//configuración de pins de luces LED
const int ledPin_Yellow = 25;
const int ledPin_Blue = 27;
const int ledPin_Green = 32;
const int ledPin_Red = 33;

//configuración de red
IPAddress staticIP(192, 168, 1, 201);
IPAddress gateway(192, 168, 1, 1);
IPAddress subnet(255, 255, 255, 0);
IPAddress dns(192, 168, 1, 1);

//configuración del servidor en la nube
String cloudServerIP = "192.168.1.38";
int cloudServerPort = 80;

//configuración de los datos del sensor
String plotID = "Parcela1";
String sensorID = "B100100";

//rutas de interacción con el servidor en la nube
String cloudServerPathOrders = "/TFG/Controller/orders/getorders.php?parcela=" + plotID;
String cloudServerPathPost = "/TFG/upload/upload.php?parcela=" + plotID;
String cloudServerPathPostFoto = "/TFG/upload/upload.php";

WiFiClient client;
HTTPClient http;

//variables de control de envío y recepción de archivos, para evitar solapamientos
File file;
String FILE_PHOTO_TEMP = "/foto.jpg";
bool uploadComplete = false;
bool receivedData = false;
int informationType = 0;
bool gathering = false;
bool sendingData = false;
String mensaje = "";

//dirección base de la red
String rootAddress = "http://192.168.";

//arrays que contienen la ultima parte de las direcciones IP de cada sensor
//se define el tamaño de cada array y se le incorpora la última mitad de la dirección IP
#define ARRAYSIZE_H 1
#define ARRAYSIZE_C 1
#define ARRAYSIZE_V 1
String arrayAddressHumidity[ARRAYSIZE_H] = { "1.210" };
String arrayAddressCameras[ARRAYSIZE_C] = { "1.211" };
String arrayAddressValves[ARRAYSIZE_V] = { "1.212" };
String arrayCamerasID[ARRAYSIZE_C] = { "camarita1" };   //Array con los Sensor ID de cada camara
String arrayValvulasID[ARRAYSIZE_V] = { "V010101" };  //Array con los Sensor ID de cada camara

//variable de control de envío, por si hay más de un sensor cámara
int currentCamera = 0;

AsyncWebServer server(80);

//variables para el control de ejecución en un intervalo lo suficientemente extenso para que de tiempo a obtener todos los datos de los sensores y no haya posiblidad de solapamiento.
long interval_between_server_connections = 600000;  //tiempo entre conexiones al servidor en la nube en milisegundos. (En este caso se ejecuta cada 600 segundos = 10 minutos)
long previousMillis = 0;
unsigned long currentMillis = 0;

//función para leer archivos en la memoria interna
String readFile(fs::FS &fs, const char *path) {
  String returnString = "";
  File file = fs.open(path, "r");
  if (!file) {
    Serial.println("Failed to open file for reading");
    return "FAIL";
  }
  while (file.available()) {
    returnString = returnString + (char)file.read();
  }
  file.close();
  return returnString;
}

//función para crear y sobreescribir archivos
void writeFile(fs::FS &fs, const char *path, String message) {
  File file = fs.open(path, FILE_WRITE);
  if (!file) {
    Serial.println("Failed to open file for writing");
    return;
  }
  if (!file.print(message)) {
    Serial.println("Write failed");
  }
  file.close();
}

//página web a mostrar si se conecta con el Control Central directamente
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
	  <h1>Dispositivo: Control Central ID %id_sensor%</h1>
	</center>
</body>
</html>)rawliteral";

//función que gestiona la recepción de fotografías por parte del Control Central
//la recepción se recibe en múltiples recepciones
void handleUpload(AsyncWebServerRequest *request, String filename, size_t index, uint8_t *data, size_t len, bool final) {
  digitalWrite(ledPin_Yellow, HIGH);
  String logmessage = "Client:" + (String)request->client()->remoteIP().toString() + " " + (String)request->url();
  if (!index) {  //recepción de la primera parte del archivo
    logmessage = "Upload Start: " + String(filename);
    //recibir y guardar el archivo
    request->_tempFile = LittleFS.open(FILE_PHOTO_TEMP, FILE_WRITE);
    request->_tempFile.close();
  }

  if (len) {  //recepción de las partes intermedias del archivo
    request->_tempFile = LittleFS.open(FILE_PHOTO_TEMP, FILE_APPEND);
    //añadir la parte recibida al archivo
    request->_tempFile.write(data, len);
    logmessage = "Writing file: " + String(filename) + " index=" + String(index) + " len=" + String(len);
    request->_tempFile.close();
  }

  if (final) {  //recepción de la última parte del archivo
    logmessage = "Upload Complete: " + String(filename) + ",size: " + String(index + len);
    // close the file handle as the upload is now done
    request->_tempFile.close();
    Serial.println(logmessage);
    //establecer que se ha recibido el archivo
    uploadComplete = true;
    digitalWrite(ledPin_Yellow, LOW);
  }
  Serial.println("Upload complete!");
  digitalWrite(ledPin_Yellow, LOW);
}

//función que envia los datos o la fotografía al servidor en la nube
void sendDataToCloud(void *pvParameters) {
  //establecer que se están enviando datos
  sendingData = true;
  //variables de control
  String getAll;
  String getBody;
  size_t fbLen;
  int remainingData;
  String archivo =" ";
  String tail = "";
  String head = "";
  String head1 = "";
  String head2 = "";

  //datos a enviar según el tipo de información escogido
  if (informationType == 1) {
    archivo = "/humedad.txt";
  }
  if (informationType == 2) {
    archivo = "/valvula.txt";
  }
  if (informationType == 3) {
      //leer el archivo con la fotografía
    archivo = FILE_PHOTO_TEMP;
  }

File file2 = LittleFS.open(archivo, "r");
    if (!file2) {
      Serial.println("Error opening file");
    }

  Serial.println("Connecting to server: " + cloudServerIP + " : " + (String)cloudServerPort);

  //variables necesarias para el uso del planificador de procesos
  TickType_t xLastWakeTime = xTaskGetTickCount();
  const TickType_t xFrequency = 100;  //delay for mS

  //conectar al Control Central y enviar la fotografía
  if (client.connect(cloudServerIP.c_str(), cloudServerPort)) {
    Serial.println("Connection successful!");
    
    if (informationType == 1 || informationType == 2) {
    
    //definir las cabeceras y delimitadores del mensaje HTTP POST
     head1 = "--TFGMartinGonzalez\r\nContent-Disposition: form-data; name=\"parcela\";\r\n\r\n" + plotID + "\r\n";
     head = "--TFGMartinGonzalez\r\nContent-Disposition: form-data; name=\"textFile\"; filename=\"readings.txt\"\r\nContent-Type: text/plain\r\n\r\n";
     tail = "\r\n--TFGMartinGonzalez--\r\n";

    uint32_t imageLen = file2.size();
    uint32_t extraLen = head1.length() + head.length() + tail.length();
    uint32_t totalLen = imageLen + extraLen;
    
    client.println("POST " + cloudServerPathPostFoto + " HTTP/1.1");
    client.println("Host: " + cloudServerIP);
    client.println("Content-Length: " + String(totalLen));
    client.print(F("Connection: keep-alive\r\n"));
    client.println("Content-Type: multipart/form-data; boundary=TFGMartinGonzalez");
    client.println();
    client.print(head1);
    client.print(head);
  }

 if (informationType == 3) {
    
    String head1 = "--TFGMartinGonzalez\r\nContent-Disposition: form-data; name=\"parcela\";\r\n\r\n" + plotID + "\r\n";
    String head2 = "--TFGMartinGonzalez\r\nContent-Disposition: form-data; name=\"sensor\";\r\n\r\n" + arrayCamerasID[currentCamera] + "\r\n";
    String head = "--TFGMartinGonzalez\r\nContent-Disposition: form-data; name=\"imageFile\"; filename=\"photo.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n";
    String tail = "\r\n--TFGMartinGonzalez--\r\n";

    uint32_t imageLen = file2.size();
    uint32_t extraLen = head2.length() +head1.length() + head.length() + tail.length();
    uint32_t totalLen = imageLen + extraLen;

    client.println("POST " + cloudServerPathPostFoto + " HTTP/1.1");
    client.println("Host: " + cloudServerIP);
    client.println("Content-Length: " + String(totalLen));
    client.print(F("Connection: keep-alive\r\n"));
    client.println("Content-Type: multipart/form-data; boundary=TFGMartinGonzalez");
    client.println();
    client.print(head1);
    client.print(head2);
    client.print(head);
  }

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
    uploadComplete = true;
    int timoutTimer = 10000;
    long startTimer = millis();
    boolean state = false;

    //recoger la respuesta, si la hubo
    while ((startTimer + timoutTimer) > millis()) {
      delay(100);
      while (client.available()) {
        char c = client.read();
        if (c == '\n') {
          //Serial.print(c);
          if (getAll.length() == 0) { state = true; }
          getAll = "";
        } else if (c != '\r') {
          getAll += String(c);
        }
        if (state == true) { getBody += String(c); }
        startTimer = millis();
      }
    }
    //se ha terminado de enviar los datos
    sendingData = false;
    client.stop();

    //eliminar la tarea del planificador de tareas del microcontrolador
    vTaskDelete(NULL);

    //mostrar en el puerto serie el mensaje devuelto, en caso de haberlo
    Serial.println();
    Serial.println(getBody);
  } else {
    getBody = "Connection to " + cloudServerIP + " failed.";
    Serial.println(getBody);
  }
  //se ha terminado de enviar los datos
  sendingData = false;
  
  //eliminar la tarea del planificador de tareas del microcontrolador
  vTaskDelete(NULL);  //borrar la tarea del planificador de tareas
}

//función para enviar una fotografía mediante HTTP POST en multiples partes
void receivePhotoTask(void *pvParameters) {
  digitalWrite(ledPin_Yellow, HIGH);

  //variables para el planificador de tareas de FreeRTOS
  TickType_t xLastWakeTime = xTaskGetTickCount();
  const TickType_t xFrequency = 100;  //retraso en milisegundos
  vTaskDelay(100 / portTICK_PERIOD_MS);
  int timoutTimer = 10000;
  long startTimer = millis();
  boolean state = false;

  //la recepción de fotografías desde el sensor, se realiza primero tomando la fotografía y a continuación enviándola
  String url = "http://192.168." + arrayAddressCameras[currentCamera] + "/capture";       //HTTP que captura la foto
  String url2 = "http://192.168." + arrayAddressCameras[currentCamera] + "/enviar_foto";  //HTTP sube la foto al servidor
  //tomar la foto
  Serial.println(url);
  http.begin(client, url);
  int httpCode = http.GET();  // Realizar petición
  http.end();
  //es necesario dar un tiempo de margen entre que se toma la fotografía y se recibe
  delay(5000);
  //enviar la foto
  http.begin(client, url2);
  httpCode = http.GET();  // Realizar petición
  http.end();
  digitalWrite(ledPin_Yellow, LOW);
  //una vez enviada, eliminar la tarea del planificador de tareas
  vTaskDelete(NULL);
}

//función que recorre todos los sensores definidos en los arrays, recoge los datos de cada uno y los envía según va pasando de sensor en sensor
void automaticGatheringTask(void *pvParameters) {
  bool sent = false;
  digitalWrite(ledPin_Red, LOW);
  //variables para el planificador de tareas de FreeRTOS
  TickType_t xLastWakeTime = xTaskGetTickCount();
  const TickType_t xFrequency = 100;  //retraso en milisegundos
  vTaskDelay(100 / portTICK_PERIOD_MS);
  digitalWrite(ledPin_Yellow, HIGH);
  //SENSORES DE HUMEDAD
  informationType = 1;  //establecer que la información a enviar son datos de los sensores de humedad
  String url = "";      //HTTP del sensor de humedad
  
  //recorrer todos los sensores de humedad y enviar los datos de cada uno de ellos de cada vez
  Serial.println ("\nHumidity Sensors:\n");
    //HUMEDAD
    informationType = 1;  //establecer que la información a enviar son datos de las Electroválvulas
    for (int i = 0; i < ARRAYSIZE_H; i++) {
      digitalWrite(ledPin_Yellow, HIGH);
      receivedData = false;
      url = rootAddress + arrayAddressHumidity[i] + "/enviar_post";
      Serial.print("Conecting to URL: " + url);
      if (http.begin(client, url)) {  //Iniciar conexión
        Serial.print("[HTTP] Petition Sent\n");
        int httpCode = http.GET();  // Realizar petición
        http.end();
      }
      while (receivedData == false) {
        Serial.print(".H.");
        delay(200);
      }
      if (receivedData == true) {
        //una vez se han recibido los datos, proceder a enviarlos
        sendingData = true;
        digitalWrite(ledPin_Yellow, LOW);
        digitalWrite(ledPin_Red, HIGH);
        //una vez se recibieron los datos, se envían al servidor en la nube
        Serial.println("Humidity Data received and sent to the Cloud Server\r\n");
        xTaskCreatePinnedToCore(sendDataToCloud, "sendDataToCloud_H", 20000, NULL, 3, NULL, 1);
        //permitir la recepción de nuevos datos
        receivedData = false;
        while (sendingData == true) {
          Serial.print(".");
          delay(200);
        }
        digitalWrite(ledPin_Red, LOW);
        Serial.println(sendingData);
      }
      digitalWrite(ledPin_Red, LOW);
    }
Serial.println ("\nElectrovalves:\n");
  //VALVULAS
  informationType = 2;  //establecer que la información a enviar son datos de las Electroválvulas
  for (int i = 0; i < ARRAYSIZE_V; i++) {
    digitalWrite(ledPin_Yellow, HIGH);
    receivedData = false;
    url = rootAddress + arrayAddressValves[i] + "/enviar_post";
    Serial.print("Conecting to URL:");
    Serial.println(url);
    if (http.begin(client, url)) {  //Iniciar conexión
      Serial.print("[HTTP] Petition Sent\n");
      int httpCode = http.GET();  // Realizar petición
      http.end();
    }

    while (receivedData == false) {
      Serial.print(".Z1.");
      delay(200);
    }
    while (uploadComplete == false) {
      Serial.print(".");
      delay(200);
    }
    if (uploadComplete== true) {
      digitalWrite(ledPin_Yellow, LOW);
      digitalWrite(ledPin_Red, HIGH);
      sendingData = true;
      //una vez se recibieron los datos, se envían al servidor en la nube
      Serial.println("Valve Data received and sent to the Cloud Server\r\n");
      xTaskCreatePinnedToCore(sendDataToCloud, "sendDataToCloud_V", 20000, NULL, 3, NULL, 1);
      //permitir la recepción de nuevos datos
      receivedData = false;
          while (sendingData == true) {
          Serial.print(".");
          delay(200);
        }
      digitalWrite(ledPin_Red, LOW);
    }
    digitalWrite(ledPin_Red, LOW);
  }
Serial.println ("\nCameras:\n");
  //CAMARAS
  informationType = 3;
  for (int i = 0; i < ARRAYSIZE_C; i++) {
    digitalWrite(ledPin_Yellow, HIGH);
    currentCamera = i;
    uploadComplete = false;
    sendingData = true;
    //Crear la automatizacion para recepción de la fotografía de la camara que corresponda
    xTaskCreatePinnedToCore(receivePhotoTask, "receivePhotoTask", 20000, NULL, 3, NULL, 1);
    digitalWrite(ledPin_Yellow, LOW);
    digitalWrite(ledPin_Red, HIGH);
    while (uploadComplete == false) {
      Serial.print(".*.");
    }
    Serial.println("\nPhoto received, uploading it to the cloud sever.");
    //una vez se ha recibido la foto, se envía al servidor en la nube
    xTaskCreatePinnedToCore(sendDataToCloud, "sendDataToCloud_C", 20000, NULL, 3, NULL, 1);
        while (sendingData == true) {
          Serial.print(".");
          delay(200);
        }
    digitalWrite(ledPin_Red, LOW);
  }
  //eliminar cualquier tarea del planificador de tareas que quedara pendiente
  vTaskDelete(NULL);
  digitalWrite(ledPin_Red, LOW);
}

//función que procesa las órdenes recibidas desde el servidor en la nube
void receiveOrders(void *pvParameters) {
  digitalWrite(ledPin_Yellow, HIGH);
  String url = "";
  //variables para el planificador de tareas de FreeRTOS
  TickType_t xLastWakeTime = xTaskGetTickCount();
  const TickType_t xFrequency = 100;  //retraso en milisegundos
  vTaskDelay(100 / portTICK_PERIOD_MS);
  int timoutTimer = 10000;
  long startTimer = millis();
  boolean state = false;
  
  //Primero se ajusta el estado de las válvulas y después se ejecuta el comando recibido
  //Procesar la parte referente al estado de las valvulas
  int values_ini = mensaje.indexOf("VALVES:");
  int values_end = mensaje.indexOf(":VALVES");
  String valves = mensaje.substring((values_ini + 7), (values_end));
  String valve_temp = "";
  digitalWrite(ledPin_Yellow, HIGH);
  while (valves.indexOf("&") > 0) {
    values_end = valves.indexOf("&");
    valve_temp = valves.substring(0, (values_end));

    //COMPROBAR EL ESTADO DE LAS VALVULAS
    //Se recorre el array de válvulas y se comprueba el estado de cada una de ellas para ver si coincide con el de las órdenes
    for (int i = 0; i < ARRAYSIZE_V; i++) {
      if (arrayValvulasID[i] == valve_temp.substring(0, valve_temp.indexOf(";"))) {
        //CONECTAR A LA VALVULA Y RECIBIR EL ARCHIVO CON EL ESTADO
        receivedData = false;
        url = rootAddress + arrayAddressValves[i] + "/enviar_post";
        if (http.begin(client, url)) {  //Iniciar conexión
          int httpCode = http.GET();  // Realizar petición
          http.end();
        }
        //esperar a recibir datos
        while (receivedData == false) {
          //Serial.print(".");
          delay(200);
        }
        if (receivedData == true) {
          //permitir la recepción de nuevos datos
          receivedData = false;

          //LEER EL ARCHIVO RECIBIDO Y PROCESARLO
          String temp = readFile(LittleFS, "/valvula.txt");
          if (valve_temp.substring(0, valve_temp.indexOf(";")) == temp.substring(0, temp.indexOf("&"))) {  //if ID LEIDA SERVIDOR == ID LEIDA ARCHIVO VALVULA
            //Comprobar si el estado leido coincide con el estado requerido por el servidor
            if (valve_temp.substring(valve_temp.indexOf(";") + 1) == (temp.substring(temp.indexOf("&") + 1, temp.indexOf("&") + 2))) {
              //Serial.println("Same state, no change of state needed");
            } else {
              //Serial.println("VALORES DIFERENTES, SE NECESITA CAMBIO");
              url = rootAddress + arrayAddressValves[i] + "/cambiar";
              if (http.begin(client, url))  //Iniciar conexión
              {
                int httpCode = http.GET();  // Realizar petición
                if (httpCode > 0) {
                 // Serial.println("Different state detected, state changed");
                } else {
                 // Serial.printf("[HTTP] GET... failed, error: %s\n", http.errorToString(httpCode).c_str());
                }
                http.end();
              }
            }
          }
        }
      }
    }
    valves = valves.substring(values_end + 1);  //resto de valvulas
  }                                             //end while
  //Procesar la parte referente al comando
  values_ini = mensaje.indexOf("ORDER:");
  values_end = mensaje.indexOf(":ORDER");
  String comando = mensaje.substring((values_ini + 6), (values_end));
  Serial.print("Command: ");
  Serial.println(comando);
  if (comando == "0") {
    Serial.println("Zero Command - Stand by");
  }

  if (comando == "1") {
    digitalWrite(ledPin_Yellow, HIGH);
    Serial.println("Command One - Gather sensor information");

    //Crear la tarea para recepción de la información de los sensores
    xTaskCreatePinnedToCore(automaticGatheringTask, "automaticGatheringTask", 20000, NULL, 3, NULL, 1);
  }
  //eliminar la tarea del planificador de tareas
  vTaskDelete(NULL);
  digitalWrite(ledPin_Yellow, LOW);
}

//funcion que conecta con el servidor para recibir las órdenes
void getActionsFromCloud() {
//para evitar que pueda ejecutarse multiples veces
  if (gathering == false) {
    gathering = true;                                                   
    String url = "http://" + cloudServerIP + cloudServerPathOrders;  //HTTP de donde obtener las ordenes
    digitalWrite(ledPin_Red, HIGH);
    if (http.begin(client, url))  //Iniciar conexión
    {
      int httpCode = http.GET();  // Realizar petición
      if (httpCode > 0) {
        if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
          String payload = http.getString();  // Obtener respuesta
          mensaje = payload;
          //crear la función que procese las órdenes
          xTaskCreatePinnedToCore(receiveOrders, "receiveOrders", 20000, NULL, 3, NULL, 1);
          Serial.println("Orders received.");
        }
      } else {
        Serial.printf("[HTTP] GET... failed, error: %s\n", http.errorToString(httpCode).c_str());
      }
      http.end();
    }
    digitalWrite(ledPin_Red, LOW);
    gathering = false;
  }
}  
 
//función de inicialización del sensor
void setup() {
  //inicializar el puerto serie
  Serial.begin(115200);

  //inicializar los LEDs y definir el estado inicial a apagado
  pinMode(ledPin_Yellow, OUTPUT);
  pinMode(ledPin_Blue, OUTPUT);
  pinMode(ledPin_Green, OUTPUT);
  pinMode(ledPin_Red, OUTPUT);

  digitalWrite(ledPin_Yellow, LOW);
  digitalWrite(ledPin_Blue, LOW);
  digitalWrite(ledPin_Green, LOW);
  digitalWrite(ledPin_Red, LOW);

  //encender el LED verde para indicar que se recibe corriente eléctrica
  digitalWrite(ledPin_Green, HIGH);

  //inicializar la memoria interna
  if (!LittleFS.begin(FORMAT_LittleFS_IF_FAILED)) {
    Serial.println('An Error has occurred while mounting LittleFS');
    return;
  }

  //inicializar la conexión WiFi y encender el LED azul en caso de conectar
  if (WiFi.config(staticIP, gateway, subnet, dns, dns) == false) {
    Serial.println("Configuration failed.");
  }

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }

  if (WiFi.status() == WL_CONNECTED) {
    //activar el led correspondiente a la conexión wifi
    digitalWrite(ledPin_Blue, HIGH);
  }

  //página a mostrar en caso de conexión directa
  server.on("/", HTTP_GET, [](AsyncWebServerRequest *request) {
    index_html.replace("%id_sensor%", sensorID);
    request->send(200, "text/html", index_html);
  });
  
  //recibir y almacenar los datos enviados por la Electroválvula
  server.on(
    "/recibir_post_valvula",
    HTTP_POST,
    [](AsyncWebServerRequest *request) {},
    NULL,
    [](AsyncWebServerRequest *request, uint8_t *data, size_t len, size_t index, size_t total) {
      //recopilar los datos recibidos
      String dataReceived = "";
      for (size_t i = 0; i < len; i++) {
        dataReceived = dataReceived + (char)data[i];
      }
      request->send(200, "text/html", "Received");

      dataReceived = dataReceived + "\r\n";
      //Como los sensores van a enviarse de uno en uno, se emplea write a la hora de guardar el archivo
      writeFile(LittleFS, "/valvula.txt", dataReceived);
      receivedData = true;
    });

  //recibir y almacenar los datos enviados por el MultiSensor de Humedad
  server.on(
    "/recibir_post_humedad",
    HTTP_POST,
    [](AsyncWebServerRequest *request) {},
    NULL,
    [](AsyncWebServerRequest *request, uint8_t *data, size_t len, size_t index, size_t total) {
      //recopilar los datos recibidos
      String dataReceived = "";
      for (size_t i = 0; i < len; i++) {
        dataReceived = dataReceived + (char)data[i];
      }
      request->send(200, "text/html", "RECIBIDO");

      dataReceived = dataReceived + "\r\n";
      //Como los sensores van a enviarse de uno en uno, se emplea write a la hora de guardar el archivo
      writeFile(LittleFS, "/humedad.txt", dataReceived);
      receivedData = true;
    });

  server.on("/iniciar_upload", HTTP_GET, [](AsyncWebServerRequest *request) {
    //Comprobar si existe archivo camara, si existe, returnString 0
    //Si no existe, crearlo y returnString codigo ok
    uploadComplete = false;
    writeFile(LittleFS, "/cam_subiendo.lck", "1");
    LittleFS.remove("/cam_fin.lck");
  });

  server.on("/fin_upload", HTTP_GET, [](AsyncWebServerRequest *request) {
    //Comprobar si existe archivo de fin de subida generico
    //Si no existe, crearlo y returnString codigo ok
    //Una vez se suba al servidor, borrar ambos archivos
    uploadComplete = true;
    LittleFS.remove("/cam_subiendo.lck");
    writeFile(LittleFS, "/cam_fin.lck", "1");
  });
  
  //página a la que se conecta el sensor cámara para enviar la fotografía
  server.on(
    "/camara", HTTP_POST, [](AsyncWebServerRequest *request) {
      request->send(200);
    },
    handleUpload);

  /*
  //página que fuerza el envío de todos los sensores a la nube
  server.on("/enviar_datos_a_nube", HTTP_GET, [](AsyncWebServerRequest *request) {
    request->send(200, "text/html", "Iniciando recopilaci&oacute;n de datos");
    xTaskCreatePinnedToCore(automaticGatheringTask, "automaticGatheringTask", 20000, NULL, 3, NULL, 1);
  });
  */
  //iniciar el servidor web
  server.begin();
}

void loop() {

  unsigned long currentMillis = millis();

  if (currentMillis - previousMillis > interval_between_server_connections) {
    // si se ha llegado al intervalo, conectar con el servidor para recibir nuevas órdenes
    previousMillis = currentMillis;
    getActionsFromCloud();
  }
}
