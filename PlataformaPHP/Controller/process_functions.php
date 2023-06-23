<?php
function processReceivedReadings(
    $conn,
    $idParcela,
    $dir = './input/readings',
    $delimiter = '&'
) {
    //procesar archivos de texto con datos de la parcela seg�n el tipo de sensor
    if ($idParcela != '') {
        $result = leerBBDD($conn, 'parcelasID', $idParcela, '', '', '');
        $id_parcela = $result[0]['id'];

        if (count($result) == 0) {
            exit();
        } //no existe la parcela en la BBDD

        $files = [];

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (
                    $entry != '.' &&
                    $entry != '..' &&
                    $entry != 'thumbs' &&
                    substr($entry, -4, 4) == '.txt' &&
                    substr($entry, 0, strlen($idParcela)) == $idParcela
                ) {
                    $files[] = $entry;
                }
            }

            closedir($handle);
        }

        print_r($files);

        for ($i = 0; $i < count($files); $i++) {
            if (filesize($dir . '/' . $files[$i]) > 0) {
                //leer el archivo
                ($myfile = fopen($dir . '/' . $files[$i], 'r')) or
                    die('Unable to open file!');
                $data = fread($myfile, filesize($dir . '/' . $files[$i])); //Cada archivo solo tiene una linea de sensor
                $array_data = explode($delimiter, $data);
                fclose($myfile);

                $date = date('Y-m-d H:i:s');

                $type = substr($array_data[0], 0, 1); //letra indicadora del tipo de sensor
                if ($type == 'H') {
                    //es un sensor de humedad
                    insertarBBDD(
                        $conn,
                        'lecturas',
                        $array_data[0],
                        $array_data[1],
                        $array_data[2],
                        $id_parcela,
                        $date
                    );
                }

                if ($type == 'V') {
                    //es una electrov�lvula
                    //comprobar si existe la valvula en el programador, si no existe, a�adirla
                    $result = leerBBDD(
                        $conn,
                        'estadoValvulasValor',
                        $id_parcela,
                        '',
                        $array_data[0],
                        ''
                    );
                    if (count($result) < 1) {
                        //no existe la valvula, a�adir a temporizador y a estadoValvulas
                        insertarBBDD(
                            $conn,
                            'estadoValvulas',
                            $id_parcela,
                            $array_data[0],
                            $array_data[1],
                            ''
                        );
                        insertarBBDD(
                            $conn,
                            'temporizador',
                            $array_data[0],
                            $id_parcela,
                            '',
                            ''
                        );
                    }
                    //a�adir la lectura de la valvula
                    insertarBBDD(
                        $conn,
                        'lecturas',
                        $array_data[0],
                        $array_data[1],
                        $array_data[2],
                        $id_parcela,
                        $date
                    );
                }
                //borrar el archivo una vez procesado
                echo "\n\n\nBORRRAAARRR " . $dir . '/' . $files[$i] . "\n\n";
            }
            unlink($dir . '/' . $files[$i]);
        } //end for $files
    } //end idparcela != ''
} //end function

function makeThumbnail($file, $thumb_dir, $width = 160, $height = 107)
{
    $file_name = substr($file, strrpos($file, '/') + 1);
    if (substr($file, -3) == 'jpg') {
        $new_image = imagecreatefromjpeg($file);
    }
    if (substr($file, -3) == 'png') {
        $new_image = imagecreatefrompng($file);
    }
    $thumb = imagecreatetruecolor($width, $height); // size in pixels
    $original_width = imagesx($new_image);
    $original_height = imagesy($new_image);
    imagecopyresampled(
        $thumb,
        $new_image,
        0,
        0,
        0,
        0,
        $width,
        $height,
        $original_width,
        $original_height
    );

    $thumb_name = 't_' . $file_name;
    if (substr($file, -3) == 'jpg') {
        imagejpeg($thumb, $thumb_dir . $thumb_name, 90);
    }
    if (substr($file, -3) == 'png') {
        imagepng($thumb, $thumb_name);
    }
}

function createThumbnails(
    $conn,
    $sensorID,
    $destination_folder,
    $date,
    $idParcela,
    $dir = './input/photos/'
) {
    //procesar las im�genes subidas por la camara del directorio especificado, crea miniaturas
    // y coloca fotos y miniaturas en el directorio de imagenes y miniatura correspondiente
    $result = leerBBDD($conn, 'parcelasID', $idParcela, '', '', '');

    $id_parcela = $result[0]['id'];

    $thumb_dir = 'thumbs/';

    $files = [];

    if ($handle = opendir($dir)) {
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
    print_r($files);

    for ($i = 0; $i < count($files); $i++) {
        $file = $dir . $files[$i];
        //idSensor, fecha,archivo, idParcela
        //insert filename in db
        insertarBBDD(
            $conn,
            'camaras',
            $sensorID,
            $date,
            $files[$i],
            $id_parcela
        );
        //create the thumbnails
        makeThumbnail($file, $dir . $thumb_dir);

        $basedir = getcwd() . '/';

        //move the files
        $source = './' . $file;
        $destination = $destination_folder;

        if (!file_exists($destination)) {
            mkdir($destination, 0755);
            mkdir($destination . 'thumbs', 0755);
        }

        // move the file to the destination folder
        if (rename($source, $destination . basename($source))) {
            //echo 'File was successfully moved';
        } else {
            echo 'Error moving file';
        }
        $source = './' . $dir . $thumb_dir . 't_' . $files[$i];
        $destination = $destination_folder . $thumb_dir;
    }
}

function createThumbnailFile(
    $conn,
    $sensorID,
    $destination_folder,
    $date,
    $idParcela,
    $fileToThumbnail,
    $dir = './input/photos/'
) {
    //procesar las im�genes subidas por la camara del directorio especificado, crea miniaturas
    // y coloca fotos y miniaturas en el directorio de imagenes y miniatura correspondiente
    //solo 1 archivo en vez del dierctorio entero
    $result = leerBBDD($conn, 'parcelasID', $idParcela, '', '', '');

    $id_parcela = $result[0]['id'];

    $thumb_dir = 'thumbs/';

    $files = [];

    if ($handle = opendir($dir)) {
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

    $file = $fileToThumbnail;

    //idSensor, fecha,archivo, idParcela
    //insert filename in db
    insertarBBDD(
        $conn,
        'camaras',
        $sensorID,
        $date,
        substr($fileToThumbnail, strrpos($file, '/') + 1),
        $id_parcela
    );
    //create the thumbnails
    makeThumbnail($file, $dir . $thumb_dir);

    $basedir = getcwd() . '/';

    //move the files
    $source = './' . $file;
    $destination = $destination_folder;

    if (!file_exists($destination)) {
        mkdir($destination, 0755);
        mkdir($destination . 'thumbs', 0755);
    }

    // move the file to the destination folder
    if (rename($source, $destination . basename($source))) {
        //echo 'File was successfully moved';
    } else {
        echo 'Error moving file';
    }
    $source = './' . $dir . $thumb_dir . 't_' . $file;
    $destination = $destination_folder . $thumb_dir;
}
?>
