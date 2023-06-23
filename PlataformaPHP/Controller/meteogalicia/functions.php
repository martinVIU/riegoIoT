<?php
// Funciones MeteoGalicia

function meteogalicia_obtener_prediccion_concello(
    $council_selected,
    $caso,
    $meteogalicia_prediccion_leyenda,
    $meteogalicia_prediccion_viento,
    $temp_file = 'Controller/meteogalicia/temp/temp_json',
    $temp_file_councils = 'Controller/meteogalicia/temp/json_concellos'
) {
    //descargar_archivo_json($council_selected,$caso);
    $datos_json = leer_json($caso, $temp_file);

    switch ($caso) {
        case 1: //meteogalicia - valor pr�ximos d�as - Generica
            $url =
                'https://servizos.meteogalicia.gal/mgrss/predicion/jsonPredConcellos.action?idConc=' .
                $council_selected;
            break;

        case 2: //meteogalicia - valor pr�ximos d�as (Hoy + 3 d�as / Hora por Hora))
            $url =
                'https://servizos.meteogalicia.gal/mgrss/predicion/jsonPredHorariaConcellos.action?idConc=' .
                $council_selected;
            break;
        default:
            $url = '';
    }
    //Hay que descargar el archivo para procesar el json
    $file_name = basename($url);
    if (file_put_contents($temp_file_councils, file_get_contents($url))) {
        //echo "File downloaded successfully";
    } else {
        echo 'File downloading failed.';
    }
    $file = file_get_contents($temp_file_councils);
    $data = json_decode($file); //decode data

    switch ($caso) {
        case 1: //prediccion general a 4 dias, incluye lluvia
            $temp = $data->predConcello;
            $array_predicciones = $temp->listaPredDiaConcello;
            echo '<table border=1><tr>';
            for ($a = 0; $a < count($array_predicciones); $a++) {
                $title = $array_predicciones[$a]->dataPredicion;
                $title =
                    substr($title, 0, strpos($title, 'T')) .
                    '<br>' .
                    substr($title, strpos($title, 'T') + 1);
                echo '<td>' . $title . '</td>';
            }
            echo '</tr>';
            echo '<tr>';
            for ($a = 0; $a < count($array_predicciones); $a++) {
                $tmax = $array_predicciones[$a]->tMax;
                $tmin = $array_predicciones[$a]->tMin;
                $rain_morning = $array_predicciones[$a]->pchoiva->manha;
                $rain_afternoon = $array_predicciones[$a]->pchoiva->tarde;
                $rain_night = $array_predicciones[$a]->pchoiva->noite;
                $wind_morning = $array_predicciones[$a]->vento->manha;
                $wind_afternoon = $array_predicciones[$a]->vento->tarde;
                $wind_night = $array_predicciones[$a]->vento->noite;

                echo '<td>';
                echo 'T maxima: ' . $tmax . '<br>';
                echo 'T minima: ' . $tmin . '<br>';
                echo 'Lluvia (manana): ' . $rain_morning . '<br>';
                echo 'Lluvia (tarde): ' . $rain_afternoon . '<br>';
                echo 'Lluvia (noche): ' . $rain_night . '<br>';
                echo 'Viento (manana): ' . $wind_morning . '<br>';
                echo 'Viento (tarde): ' . $wind_afternoon . '<br>';
                echo 'Viento (noche): ' . $wind_night;
                echo '</td>';
            }
            echo '</tr>';
            echo '</table>';
            break;

        case 2: //prediccion por dias y horas
            $temp = $data->predHoraria;
            $array_predicciones = $temp->listaPredDiaHoraria;
            //echo "<br>total dias: ".count($array_predicciones)."<br>";
            //dato en dia 1 hora 3
            echo '<table border=1><tr>';
            for ($a = 0; $a < count($array_predicciones); $a++) {
                for (
                    $b = 0;
                    $b < count($array_predicciones[$a]->listaPredHora);
                    $b++
                ) {
                    $title =
                        $array_predicciones[$a]->listaPredHora[$b]
                            ->dataPredicion;
                    //echo strpos($title,"T");
                    $title =
                        substr($title, 0, strpos($title, 'T')) .
                        '<br>' .
                        substr($title, strpos($title, 'T') + 1);
                    echo '<td>' . $title . '</td>';
                }
            }
            echo '</tr>';
            echo '<tr>';
            for ($a = 0; $a < count($array_predicciones); $a++) {
                for (
                    $b = 0;
                    $b < count($array_predicciones[$a]->listaPredHora);
                    $b++
                ) {
                    $sky = $array_predicciones[$a]->listaPredHora[$b]->icoCeo;
                    $wind =
                        $array_predicciones[$a]->listaPredHora[$b]->icoVento;
                    echo '<td>' .
                        $meteogalicia_prediccion_leyenda[$sky] .
                        '<br>';
                    echo $meteogalicia_prediccion_viento[$wind] . '<br>';
                    echo $array_predicciones[$a]->listaPredHora[$b]->tMedia .
                        '</td>';
                }
            }
            echo '</tr>';
            echo '</table>';
            break;
        default:
            break;
    }
}

function descargar_archivo_json($council_selected, $file_temp)
{
    $url =
        'https://servizos.meteogalicia.gal/mgrss/predicion/jsonPredHorariaConcellos.action?idConc=' .
        $council_selected;
    $file_name = basename($url);
    echo file_get_contents($url);

    if (file_put_contents($file_temp, file_get_contents($url))) {
        //echo "File downloaded successfully";
    } else {
        echo 'File downloading failed.';
    }
}

function leer_json($file_temp)
{
    $file = $file_temp;
    if (file_exists($file)) {
        //$filename = 'file.json';
        $data = file_get_contents($file); //data read from json file
        $data = json_decode($data); //decode  data

        return $data;
    }
}
?>
