<?php
//Funciones AEMET
$api_key =
    'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJtYXJ0aW5mb3Jlc3RhbGVzQGdtYWlsLmNvbSIsImp0aSI6ImJkYjBhNGNhLTY0ZjYtNGQ4MS04NjMyLTNiOTg1NGQ0NDg5MyIsImlzcyI6IkFFTUVUIiwiaWF0IjoxNjY2NjIwMzUxLCJ1c2VySWQiOiJiZGIwYTRjYS02NGY2LTRkODEtODYzMi0zYjk4NTRkNDQ4OTMiLCJyb2xlIjoiIn0.GZ5c5eYFnhJdMz0WexbZHaElxDVtaK_lMO6yp1NxwX8';

function get_all_weather_station_to_disk(
    $api_key,
    $dir_temp = 'Controller/aemet/temp/'
) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL =>
            'https://opendata.aemet.es/opendata/api/valores/climatologicos/inventarioestaciones/todasestaciones/?api_key=' .
            $api_key,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => ['cache-control: no-cache'],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    //obtener la respuesta
    if ($err) {
        echo 'cURL Error #:' . $err;
    } else {
        echo gettype($response);
        $response = json_decode($response, true);
    }

    //guardar la respuesta

    //listado de sitios
    $fh = fopen($dir_temp . 'allweather.txt', 'w');
    $fh2 = fopen($dir_temp . 'allweather_m.txt', 'w');
    $ch = curl_init();
    //guardar los datos
    curl_setopt($ch, CURLOPT_URL, $response['datos']);
    curl_setopt($ch, CURLOPT_FILE, $fh);
    curl_exec($ch);
    //guardar los metadatos
    curl_setopt($ch, CURLOPT_URL, $response['metadatos']);
    curl_setopt($ch, CURLOPT_FILE, $fh2);
    curl_exec($ch);
    curl_close($ch);
}

function get_data_town(
    $api_key,
    $town = '36024',
    $dir_temp = 'Controller/aemet/temp/'
) {
    $url =
        'https://opendata.aemet.es/opendata/api/prediccion/especifica/municipio/horaria/' .
        $town;
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => $api_key . ':',
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'cache-control: no-cache',
            'Accept: application/javascript',
            'api_key:' . $api_key,
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo 'cURL Error #:' . $err;
    } else {
        $response = json_decode($response, true);
    }

    //datos del municipio
    $fh = fopen($dir_temp . 'town_time.txt', 'w'); //datos
    $fh2 = fopen($dir_temp . 'town_time_m.txt', 'w'); //metadatos
    $ch = curl_init();
    //guardar en archivos
    curl_setopt($ch, CURLOPT_URL, $response['datos']);
    curl_setopt($ch, CURLOPT_FILE, $fh);
    curl_exec($ch);

    curl_setopt($ch, CURLOPT_URL, $response['metadatos']);
    curl_setopt($ch, CURLOPT_FILE, $fh2);
    curl_exec($ch);
    curl_close($ch);
}

function get_all_towns($apiKey, $dir_temp = 'Controller/aemet/temp/')
{
    //Obtener todos los municipios disponibles
    $url = 'https://opendata.aemet.es/opendata/api/maestro/municipios';

    echo $url;
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => $apiKey . ':',
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'cache-control: no-cache',
            'Accept: application/javascript',
            'api_key:' . $apiKey,
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo 'cURL Error #:' . $err;
    }
    if (!function_exists('json_last_error_msg')) {
        function json_last_error_msg()
        {
            static $ERRORS = [
                JSON_ERROR_NONE => 'No error',
                JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
                JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
                JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX => 'Syntax error',
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            ];

            $error = json_last_error();
            return isset($ERRORS[$error]) ? $ERRORS[$error] : 'Unknown error';
        }
    }
    $ch = curl_init();
    $a = utf8_encode($response);
    $array = json_decode($a);
    $fh2 = $dir_temp . 'all_towns.txt';

    curl_setopt($ch, CURLOPT_URL, $a);
    //curl_setopt($ch, CURLOPT_FILE, $fh);
    curl_exec($ch);

    curl_close($ch);
}

function aemet_check_weather(
    $townID,
    $api_key,
    $dir_temp = 'Controller/aemet/temp/'
) {
    get_data_town($api_key, $townID);
    $filename = $dir_temp . 'town_time.txt';

    $json = file_get_contents($filename);

    //para limpiar el json recibido
    $json = mb_convert_encoding($json, 'UTF8', 'Windows-1252');

    $data = json_decode($json);
    $aemet = [];

    //AEMET no indica los posibles valores devueltos, por eso se descartga el campo value
    // 11 despejado
    //12 poco nuboso
    //value 17 y 17n "Nubes altas"
    //discrepancias en los periodos, periodos repetidos
    for ($a = 0; $a < count($data[0]->prediccion->dia); $a++) {
        $array_probPrecipitacion = [];
        $array_probPrecipitacion_val = [];
        $array_probTormenta = [];
        $array_probTormenta_val = [];
        $array_probNieve = [];
        $array_probNieve_val = [];
        //rellenar cada apartado segun las horas q salen, recorrer 1a seccion,
        for (
            $i = 0;
            $i < count($data[0]->prediccion->dia[$a]->estadoCielo);
            $i++
        ) {
            $aemet[$a][
                (int) $data[0]->prediccion->dia[$a]->estadoCielo[$i]->periodo
            ]['descripcion'] =
                $data[0]->prediccion->dia[$a]->estadoCielo[$i]->descripcion;
        }
        for (
            $i = 0;
            $i < count($data[0]->prediccion->dia[$a]->precipitacion);
            $i++
        ) {
            $aemet[$a][
                (int) $data[0]->prediccion->dia[$a]->precipitacion[$i]->periodo
            ]['precipitacion'] =
                $data[0]->prediccion->dia[$a]->precipitacion[$i]->value;
        }
        for ($i = 0; $i < count($data[0]->prediccion->dia[$a]->nieve); $i++) {
            $aemet[$a][(int) $data[0]->prediccion->dia[$a]->nieve[$i]->periodo][
                'nieve'
            ] = $data[0]->prediccion->dia[$a]->nieve[$i]->value;
        }
        for (
            $i = 0;
            $i < count($data[0]->prediccion->dia[$a]->temperatura);
            $i++
        ) {
            $aemet[$a][
                (int) $data[0]->prediccion->dia[$a]->temperatura[$i]->periodo
            ]['temperatura'] =
                $data[0]->prediccion->dia[$a]->temperatura[$i]->value;
        }

        for (
            $i = 0;
            $i < count($data[0]->prediccion->dia[$a]->probPrecipitacion);
            $i++
        ) {
            $temp1 =
                $data[0]->prediccion->dia[$a]->probPrecipitacion[$i]->periodo;
            $lim1 = (int) substr($temp1, 0, 2);
            $lim2 = (int) substr($temp1, 2, 2);
            if ($lim1 > $lim2) {
                $lim2 = 23;
            } //incluye hasta fin del dia
            $pos = count($array_probPrecipitacion);
            for ($b = $lim1; $b < $lim2 + 1; $b++) {
                $array_probPrecipitacion[$pos] = $b;
                $array_probPrecipitacion_val[$pos] =
                    $data[0]->prediccion->dia[$a]->probPrecipitacion[$i]->value;
                $pos++;
            }
        }

        for (
            $i = 0;
            $i < count($data[0]->prediccion->dia[$a]->probTormenta);
            $i++
        ) {
            $temp1 = $data[0]->prediccion->dia[$a]->probTormenta[$i]->periodo;
            $lim1 = (int) substr($temp1, 0, 2);
            $lim2 = (int) substr($temp1, 2, 2);
            if ($lim1 > $lim2) {
                $lim2 = 23;
            } //incluye hasta fin del dia
            for ($b = $lim1; $b < $lim2 + 1; $b++) {
                $array_probTormenta[] = $b;
                $array_probTormenta_val[] =
                    $data[0]->prediccion->dia[$a]->probTormenta[$i]->value;
            }
        }

        for (
            $i = 0;
            $i < count($data[0]->prediccion->dia[$a]->probNieve);
            $i++
        ) {
            $temp1 = $data[0]->prediccion->dia[$a]->probNieve[$i]->periodo;
            $lim1 = (int) substr($temp1, 0, 2);
            $lim2 = (int) substr($temp1, 2, 2);
            if ($lim1 > $lim2) {
                $lim2 = 23;
            } //incluye hasta fin del dia
            for ($b = $lim1; $b < $lim2 + 1; $b++) {
                $array_probNieve[] = $b;
                $array_probNieve_val[] =
                    $data[0]->prediccion->dia[$a]->probNieve[$i]->value;
            }
        }
        foreach ($aemet[$a] as $clave => $valor) {
            if (in_array((int) $clave, $array_probPrecipitacion) == 1) {
                $aemet[$a][$clave]['probPrecipitacion'] =
                    $array_probPrecipitacion_val[
                        array_search(
                            $clave,
                            array_keys($array_probPrecipitacion)
                        )
                    ];
            }
            if (in_array($clave, $array_probTormenta) == 1) {
                $aemet[$a][$clave]['probTormenta'] =
                    $array_probTormenta[
                        array_search($clave, array_keys($array_probTormenta))
                    ];
            }
            if (in_array($clave, $array_probNieve) == 1) {
                $aemet[$a][$clave]['probNieve'] =
                    $array_probNieve[
                        array_search($clave, array_keys($array_probNieve))
                    ];
            }
        }
        unset($array_probPrecipitacion);
        unset($array_probPrecipitacion_val);
        unset($array_probTormenta);
        unset($array_probTormenta_val);
        unset($array_probNieve);
        unset($array_probNieve_val);
    }

    $Today = date('y:m:d');

    echo '<table border=1>';
    echo '<tr>';
    for ($a = 0; $a < count($aemet); $a++) {
        $NewDate = Date('d-m-Y', strtotime('+' . $a . ' days'));
        echo '<td colspan="' .
            count($aemet[$a]) .
            '" > Dia ' .
            $NewDate .
            '</td>';
    }
    echo '</tr><tr>';

    for ($a = 0; $a < count($aemet); $a++) {
        foreach ($aemet[$a] as $clave => $valor) {
            echo '<td>' . $clave . ':00</td>';
        }
    }
    echo '</tr><tr>';
    for ($a = 0; $a < count($aemet); $a++) {
        foreach ($aemet[$a] as $clave => $valor) {
            echo '<td>';
            if (isset($valor['descripcion'])) {
                echo $valor['descripcion'] . '<br>';
            }
            if (isset($valor['precipitacion'])) {
                echo 'Precip: ' . $valor['precipitacion'] . '<br>';
            }
            if (isset($valor['nieve'])) {
                echo 'nieve: ' . $valor['nieve'] . '<br>';
            }
            if (isset($valor['temperatura'])) {
                echo 'temperatura: ' . $valor['temperatura'] . '<br>';
            }
            if (isset($valor['probPrecipitacion'])) {
                echo 'probPrecipitacion: ' .
                    $valor['probPrecipitacion'] .
                    '<br>';
            }
            if (isset($valor['probTormenta'])) {
                echo 'probTormenta: ' . $valor['probTormenta'] . '<br>';
            }
            if (isset($valor['probNieve'])) {
                echo 'probNieve: ' . $valor['probNieve'];
            }
            echo '</td>';
        }
    }

    echo '</tr></table>';
}
?>
