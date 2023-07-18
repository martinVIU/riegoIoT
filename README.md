# Diseño y Desarrollo de un Sistema de Riego Telemático Escalable IoT

## TFG de Martín González bajo la dirección de la profesora Yudith Cardinale para la VIU (Valencia International University)

### Grado en Ingeniería Informática, Julio 2023

El código aqui presente corresponde a un Sistema de Riego IoT diseñado en Arduino, que se comunica con una aplicación/plataforma PHP con soporte multiusuario y multiparcelar.

El contenido de este repositorio está dividido en dos secciones, la sección en la nube (la plataforma PHP) y los tipos de sensores disponibles.

El sistema de riego consta de un Control Central que es el que se comunica con el resto de sensores y diversos tipos de sensores, permitiendo poder utilizar cuanta cantidad sea necesaria de sensores independientemente del tipo de sensor.

El funcionamiento del sistema es el siguiente:

El Control Central recibe las órdenes de la aplicación PHP, y actúa conforme a la información recibida: el estado de las válvulas de la parcela (cuales están abiertas/cerradas) y si ha de mandar información actualizada del estado de los sensores a la aplicación PHP.

A continuación, el Control Central se comunica con cada sensor/cámara y envía la información recibida de cada sensor a la aplicación PHP.

Por su parte la aplicación PHP permite llevar un control de las mediciones recibidas por los sensores, abrir y cerrar las válvulas, ver las fotografías tomadas por las cámaras, así como el poder encender/apagar las electroválvulas de forma remota, de forma manual o programada.

En caso de que la parcela se encuentre en España, se muestra la predicción metereológica de la AEMET, y si se encuentra dentro del territorio de Galicia, se muestra a mayores la predicción de MeteoGalicia.

Para poder utilizar la aplicacón hay que rellenar el fichero de configuración de la base de datos a utilizar, situado en "cfg/pdoconfig.php" dentro del directorio de la aplicación PHP, y la API_KEY de AEMET, situada en "Controller/aemet/apikey.php", obtenible en la web https://opendata.aemet.es/centrodedescargas/obtencionAPIKey

Video del prototipo en funcionamiento: https://youtu.be/8qh5mUIiYXw
