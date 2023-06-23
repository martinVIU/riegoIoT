<?php

//upload & process
require_once '../Model/funcionesBBDD.php';
require_once '../Controller/process_functions.php';

$idParcela = '';
$idSensor = '';
$delimiter = '&';

if (isset($_POST['parcela'])) {
    if ($_POST['parcela'] != '') {
        $idParcela = $_POST['parcela'];
    }
}
if (isset($_POST['sensor'])) {
    if ($_POST['sensor'] != '') {
        $idSensor = $_POST['sensor'];
    }
}

$idParcela = str_replace("\r", '', $idParcela);
$idParcela = str_replace("\n", '', $idParcela);
$idSensor = str_replace("\r", '', $idSensor);
$idSensor = str_replace("\n", '', $idSensor);

if ($idParcela != '') {
    $conn = createConnectionDB();
    //en un sensor de humedad
    if ($idSensor == '') {
        if (isset($_FILES['textFile']['tmp_name'])) {
            //Son lecturas de Humedad y Electrov�lvulas
            $filenameCheck = '../input/readings/' . $idParcela . '/';

            if (!file_exists($filenameCheck)) {
                mkdir($filenameCheck, 0755);
                exit();
            }

            $files = [];
            //listar archivos en directorio destino
            if ($handle = opendir($filenameCheck)) {
                while (false !== ($entry = readdir($handle))) {
                    if (
                        $entry != '.' &&
                        $entry != '..' &&
                        substr($entry, -4, 4) == '.txt' &&
                        substr($entry, 0, strlen($idParcela)) == $idParcela
                    ) {
                        $files[] = $entry;
                    }
                }

                closedir($handle);
            }
            $file_name = $idParcela . '_' . count($files) . '.txt';
            if (!file_exists($file_name)) {
                if (
                    move_uploaded_file(
                        $_FILES['textFile']['tmp_name'],
                        $filenameCheck . $file_name
                    )
                ) {
                    echo 'archivo subido ' . $filenameCheck . $file_name;
                    echo "\nOK - ARCHIVO \n";
                } else {
                    echo "\nFAIL - ARCHIVO KO\n";
                }
            } //end readings
            //procesar los archivos de texto recibidos
            processReceivedReadings($conn, $idParcela, $filenameCheck);
        } //end sensor file
    }
    if ($idSensor != '') {
        //es un sensor camara

        $dir_Sensor = '../View/images/cameras/' . $idSensor . '/';

        //el directorio de todas las fotos del sensor
        $target_dir = $dir_Sensor;

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755);
            mkdir($target_dir . 'thumbs', 0755);
        }
        $files = [];
        //listar archivos en directorio destino
        if ($handle = opendir($target_dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (
                    $entry != '.' &&
                    $entry != '..' &&
                    substr($entry, -4, 4) == '.jpg'
                ) {
                    $files[] = $entry;
                }
            }
            closedir($handle);
        }
        $file_name = count($files) . '.jpg';

        $target_file = $target_dir . '' . $file_name;
        $uploadOk = 1;
        if (isset($_FILES['imageFile']['tmp_name'])) {
            if (
                move_uploaded_file(
                    $_FILES['imageFile']['tmp_name'],
                    $target_file
                )
            ) {
                echo 'archivo subido ' . $target_file;

                //hacer miniaturas y copiar al directorio correspondiente
                $date = date('Y-m-d H:i:s');

                createThumbnailFile(
                    $conn,
                    $idSensor,
                    $dir_Sensor,
                    $date,
                    $idParcela,
                    $target_file,
                    $target_dir . '/'
                );
                echo "\nOK - ARCHIVO \n";
            } else {
                echo "\nFAIL - ARCHIVO KO\n";
            }
        } //end camera
    }
} //
?>
