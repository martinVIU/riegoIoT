<?php

function printTable($table, $results)
{
    echo '<table border=1>';
    echo '<tr>';

    if ($table == 'lecturas') {
        echo '<th>idSensor</th><th>valor1</th><th>valor2</th><th>fechas</th>';
    }
    echo '</tr>';
    if ($table == 'lecturas') {
        for ($i = 0; $i < count($results); $i++) {
            echo '<tr><td>' .
                $results[$i]['idSensor'] .
                '</td><td>' .
                $results[$i]['valor1'] .
                '</td><td>' .
                $results[$i]['valor2'] .
                '</td><td>' .
                $results[$i]['date'] .
                '</td></tr>';
        }
    }
    echo '</table>';
}

function minutes($time)
{
    $time = explode(':', $time);
    return $time[0] * 60 + $time[1];
}

function printTable2(
    $table,
    $results,
    $fecha = '',
    $array_desc = [],
    $thumbs_dir = 'thumbs',
    $idParcela = '',
    $page = 4
) {
    $intervals_hour = 6;
    $intervals_total_hours = 24; //el numero de intervalos del dia, en este caso con 6 intervalos por hora (intervalos de 10 minutos)
    $intervals = $intervals_hour * $intervals_total_hours;
    $a1 = $results;
    echo '<table border=1>';
    echo '<tr>';
    //CABECERAS DE TABLA
    if ($table == 'lecturas') {
    }
    if ($table == 'devices') {
        echo '<td>Sensor</td>';
        echo '<td>Tipo</td>';
        echo '<td>Descripcion</td>';
    }
    if ($table == 'estadoValvulas') {
        echo '<td>Sensor</td>';
        echo '<td>Estado</td>';
    }
    if (
        $table == 'sensoresDif2' ||
        $table == 'camaras' ||
        $table == 'temporizador'
    ) {
        echo '<td>ID Sensor</td>';
        //tabla de horas y minutos
        $horas = 0;
        $minutos = 0;
        $new_hour = false;
        for ($i = 0; $i < $intervals; $i++) {
            //echo '<td>'.$horas.":".$minutos.'</td>';
            echo '<td>';
            $minutos = $minutos + 10;
            if ($minutos > 59) {
                $minutos = 0;
                $horas = $horas + 1;
                $new_hour = true;
            }
            if ($new_hour == true) {
                if (strlen($horas - 1) < 2) {
                    echo '0' . $horas - 1;
                } else {
                    echo $horas - 1;
                }
            } else {
                if (strlen($horas) < 2) {
                    echo '0' . $horas;
                } else {
                    echo $horas;
                }
            }
            echo ':';
            if ($new_hour == true) {
                echo '59';
                $new_hour = false;
            } else {
                if (strlen($minutos) < 2) {
                    echo '0' . $minutos;
                } else {
                    echo $minutos;
                }
            }
            echo '</td>';
        }
    } //Fin cabecera

    //CONTENIDO TABLA
    if ($table == 'devices') {
        foreach ($results as $clave => $valor) {
            echo '<tr>';
            echo '<td>' . $valor['idSensor'] . '</td>';
            for ($b = 0; $b < count($array_desc); $b++) {
                if (
                    substr($valor['idSensor'], 0, 1) ==
                    $array_desc[$b]['letter']
                ) {
                    echo '<td>' .
                        $array_desc[$b]['type'] .
                        '</td><td>' .
                        $array_desc[$b]['dsc'] .
                        '</td>';
                }
            }
            echo '</tr>';
        }
    }

    if ($table == 'estadoValvulas') {
        foreach ($results as $clave => $valor) {
            echo '<tr>';
            echo '<td>' . $valor['idSensor'] . '</td>';
            if ($valor['status'] == 0) {
                echo '<td><button class="button_img" name="' .
                    $idParcela .
                    '" value="' .
                    $valor['idSensor'] .
                    '____0" alt="V�lvula apagada" ><img src="View/images/boton_off.png"></td>';
            }
            if ($valor['status'] == 1) {
                echo '<td><button class="button_img" name="' .
                    $idParcela .
                    '" value="' .
                    $valor['idSensor'] .
                    '____1" alt="V�lvula Encendida"><img src="View/images/boton_on.png"></td>';
            }
            echo '</tr>';
        }
    }

    if (
        $table == 'sensoresDif2' ||
        $table == 'camaras' ||
        $table == 'temporizador'
    ) {
        //array sensores unicos
        if ($table == 'sensoresDif2' || $table == 'camaras') {
            $results2 = array_column($results, 'idSensor');
            $arrayunique = array_values(array_unique($results2));
        }
        if ($table == 'temporizador') {
            $arrayunique = $results;
        }
        if (count($arrayunique) > 0) {
            //HACER POR CADA SENSOR
            for ($a = 0; $a < count($arrayunique); $a++) {
                //PREPARAR EL ARRAY
                $timevalues = [];
                $timestamp = [];
                for ($i = 0; $i < 24 * 6; $i++) {
                    $timevalues[$i * 10] = '';
                    $timestamp[$i * 10] = '';
                }
                //print_r ($timevalues);

                //temporizador array 1 sola dimension
                if ($table == 'temporizador') {
                    $limite = 144 - 1;
                    for ($b = 0; $b <= $limite; $b++) {
                        $timeposition = $b * 10;
                        $timevalues[$timeposition] =
                            $timevalues[$timeposition] .
                            $results[$a][$b * 10] .
                            '<br>';
                    }
                }

                //INTRODUICR DATOS EN EL ARRAY
                for ($i = 0; $i < count($results); $i++) {
                    if ($table == 'sensoresDif2' || $table == 'camaras') {
                        if ($results[$i]['idSensor'] == $arrayunique[$a]) {
                            $timeposition = minutes(
                                substr($results[$i]['date'], 11)
                            );
                            $timeposition = floor($timeposition / 10) * 10;
                            if ($table == 'camaras') {
                                $timevalues[floor($timeposition / 10)] =
                                    $timevalues[$timeposition] .
                                    "<a target=\"_blank\" rel=\"noopener\" href=\"View/images/cameras/" .
                                    $results[$i]['idSensor'] .
                                    '/' .
                                    $results[$i]['file'] .
                                    "\"><img src=\"View/images/cameras/" .
                                    $results[$i]['idSensor'] .
                                    '/' .
                                    $thumbs_dir .
                                    '/t_' .
                                    $results[$i]['file'] .
                                    "\"></a><br>";
                            }
                            if ($table == 'sensoresDif2') {
                                $timevalues[floor($timeposition / 10)] =
                                    $timevalues[$timeposition] .
                                    $results[$i]['value1'] .
                                    ' ' .
                                    $results[$i]['value2'] .
                                    '<br>';
                            }
                            if ($table == 'temporizador') {
                                $timevalues[floor($timeposition / 10)] =
                                    $timevalues[$timeposition] .
                                    $results[0][$i] .
                                    '<br>';
                            }
                            if (
                                $table == 'sensoresDif2' ||
                                $table == 'camaras'
                            ) {
                                $timestamp[floor($timeposition / 10)] =
                                    $timestamp[$timeposition] .
                                    $results[$i]['date'];
                            }
                        }
                    }
                }
                echo '</tr>';
                echo '<tr>';
                if ($table == 'sensoresDif2' || $table == 'camaras') {
                    echo '<td>' . $arrayunique[$a] . '</td>';

                    for ($i = 0; $i < $intervals; $i++) {
                        if (isset($timevalues[$i])) {
                            if (
                                $table == 'sensoresDif2' ||
                                $table == 'camaras'
                            ) {
                                echo "<td><label title=\"" .
                                    $timestamp[$i] .
                                    "\">" .
                                    $timevalues[$i] .
                                    '</label></td>';
                            }
                        } else {
                            echo '<td></td>';
                        }
                    }
                }
                if ($table == 'temporizador') {
                    echo '<td>' . $results[$a]['idSensor'] . '</td>';
                    //print_r($timevalues);
                    for ($i = 0; $i <= 144 - 1; $i++) {
                        $position_string =
                            $results[$a]['idSensor'] .
                            '____' .
                            $i * 10 .
                            '____';
                        if ((int) $timevalues[$i * 10] == 0) {
                            echo '<td><button name="' .
                                $idParcela .
                                '" class="button_timetable off" value="' .
                                $position_string .
                                '0"></button></td>';
                        }
                        if ((int) $timevalues[$i * 10] == 1) {
                            echo '<td><button name="' .
                                $idParcela .
                                '" class="button_timetable on" value="' .
                                $position_string .
                                '1"></button></td>';
                        }
                    }
                }
                echo '</tr>';
                unset($timevalues);
                unset($timeposition);
            } //fin for $a
        } else {
            echo '<tr><td></td>';
            for ($i = 0; $i < $intervals; $i++) {
                if ($i % $intervals_hour == 0) {
                    //no hay registros
                    echo '<td colspan="' .
                        $intervals_hour .
                        '"><center>No hay registros</center></td>';
                }
            }
        } //fin datos

        echo '</tr>';
    }
    echo '<input type="hidden" name="parcela" value="' . $idParcela . '" >';
    echo '<input type="hidden" name="page" value="' . $page . '" >';
    echo '</table>';
}

function menu_circle($user_level = 0, $landing_page)
{
    echo '
		<div class="container">
			
			<div class="component">
				<!-- Start Nav Structure -->
				<button class="cn-button" id="cn-button">+</button>
				<div class="cn-wrapper" id="cn-wrapper">
				    <ul>';

    if ($user_level == 0) {
        //links ADMIN
        echo '<li><a href="' .
            $landing_page .
            '?page=1"><span><img src="View/images/icons/user_add.png" height="45" width="45" style=" position: relative; left: -20px;" alt=""></span></a></li>'; //pantalla a�adir usuario
        echo '<li><a href="' .
            $landing_page .
            '?page=0"><span><img src="View/images/icons/plot.png" height="45" width="45" style=" position: relative; left: -20px;" alt=""></span></a></li>'; //pantalla asignar parcela
        echo '<li><a href="' .
            $landing_page .
            '?page=2"><span><img src="View/images/icons/land-location-icon.png" height="45" width="45" style=" position: relative; left: -20px;" alt=""></span></a></li>'; //pantalla asignar CP a parcela
        echo '<li><a href="' .
            $landing_page .
            '?page=3"><span><img src="View/images/icons/ruler.png" height="45" width="45" style=" position: relative; left: -20px;" alt=""></span></a></li>'; //mostrar tabla lecturas
        echo '<li><a href="' .
            $landing_page .
            '?page=4"><span><img src="View/images/icons/raindrop.png" height="45" width="45" style=" position: relative; left: -20px;" alt=""></span></a></li>'; //mostrar tabla sensores
        echo '<li><a href="' .
            $landing_page .
            '?page=5"><span><img src="View/images/icons/camera.png" height="45" width="45" style=" position: relative; left: -20px;" alt=""></span></a></li>'; //mostrar tabla camaras
        echo '<li><a href="' .
            $landing_page .
            '?page=6"><span><img src="View/images/icons/timer.png" height="45" width="45" style=" position: relative; left: -20px;" alt=""></span></a></li>'; //mostrar programador
        echo '<li><a href="' .
            $landing_page .
            '?page=7"><span><img src="View/images/icons/faucet.png" height="45" width="45" style=" position: relative; left: -20px;" alt=""></span></a></li>'; //mostrar y modificar estado valvulas
        echo '<li><a href="' .
            $landing_page .
            '?page=8"><span><img src="View/images/icons/logout.png" height="45" width="45" style=" position: relative; left: -20px;" alt="Logout"></span></a></li>'; //logout
    }
    if ($user_level > 0) {
        //links USER
        echo '<li><a href="' .
            $landing_page .
            '?page=3"><span><img src="View/images/icons/ruler.png" height="45" width="45" style=" position: relative; left: -10px;" alt=""></span></a></li>'; //mostrar tabla lecturas
        echo '<li><a href="' .
            $landing_page .
            '?page=4"><span><img src="View/images/icons/raindrop.png" height="45" width="45" style=" position: relative; left: -7px;" alt=""></span></a></li>'; //mostrar tabla sensores
        echo '<li><a href="' .
            $landing_page .
            '?page=5"><span><img src="View/images/icons/camera.png" height="45" width="45" style=" position: relative; left: -7px;" alt=""></span></a></li>'; //mostrar tabla camaras
        echo '<li><a href="' .
            $landing_page .
            '?page=6"><span><img src="View/images/icons/timer.png" height="45" width="45" style=" position: relative; left: -10px;" alt=""></span></a></li>'; //mostrar programador
        echo '<li><a href="' .
            $landing_page .
            '?page=7"><span><img src="View/images/icons/faucet.png" height="45" width="45" style=" position: relative; left: -0px;" alt=""></span></a></li>'; //mostrar y modificar estado valvulas
        echo '<li><a href="' .
            $landing_page .
            '?page=8"><span><img src="View/images/icons/logout.png" height="45" width="45" style=" position: relative; left: -0px;" alt="Logout"></span></a></li>'; //logout
    }
    /*
                    iconos extraidos de
                    https://www.flaticon.com/free-icon/faucet_1166424
                    https://www.flaticon.com/free-icon/timer_684270
                    https://www.flaticon.com/free-icon/camera_3392000
                    https://www.freepik.com/free-icon/raindrop_14129430.htm
                    https://www.iconbolt.com/iconsets/font-awesome-solid/ruler
                    https://uxwing.com/land-location-icon/
                    https://freesvg.org/vector-clip-art-of-land-parcel-icon
                    https://commons.wikimedia.org/wiki/File:Sample_User_Icon.png
                    */

    echo '</ul>
				</div>
				<div id="cn-overlay" class="cn-overlay"></div>
				<!-- End Nav Structure -->
			</div>
		</div><!-- /container -->
		<script src="View/js/polyfills.js"></script>
		<script src="View/js/demo1.js"></script>
	</body>
</html>';
}

function load_postal_codes()
{
    //extraido del archivo de codigos postales procesados de https://github.com/inigoflores/ds-codigos-postales-ine-es basado en el INE (Instituto Nacional de Estad�stica (Espa�a))
    $fileName = 'Model/csv/codigos_postales_municipios.csv';
    $csv = array_map('str_getcsv', file($fileName));
    //var_dump($csv);
    //devuelve el array entero, y asi evitamos un procesamiento extra
    return $csv;
}

function list_councils(
    $array_councils,
    $nombreId = 'municipios',
    $selected = ''
) {
    //devuelve el municipioID que lo usa el INE y MeteoGalicia
    echo '<select name="' .
        $nombreId .
        '" id="municipios" onchange="this.form.submit()">';
    foreach ($array_councils as $town) {
        echo '<option';
        if ($selected == $town[1]) {
            echo ' selected';
        }
        if ($town[0] != 'codigo_postal') {
            echo ' value="' .
                $town[1] .
                '">' .
                $town[0] .
                ' - ' .
                $town[2] .
                '</option>';
        }
    }
    echo '</select>';
}

function screen_show_table_readings(
    $conn,
    $array_POST,
    $idUsuario,
    $page,
    $landing_page
) {
    show_page_header('MEDICIONES');
    //echo "<br><center><h1></h1></center><br>";
    $idParcela = list_plots_by_user(
        $conn,
        $array_POST,
        $idUsuario,
        $landing_page,
        $page
    );
    echo '<br>';
    $date_readings = get_dateTime_Picker(
        $conn,
        $array_POST,
        $idParcela,
        $page,
        $landing_page
    );
    $plotID = leerBBDD($conn, 'parcelasID', $idParcela, $date_readings, '', '');
    if (count($plotID) > 0) {
        $result = leerBBDD(
            $conn,
            'lecturasF',
            $plotID[0]['id'],
            $date_readings,
            '',
            ''
        );
    } else {
        $result = [];
    }
    echo '<br>';

    printTable2('sensoresDif2', $result, $page);
}

function screen_show_table_devices(
    $conn,
    $array_POST,
    $idUsuario,
    $page,
    $landing_page
) {
    show_page_header('SENSORES DISPONIBLES');
    $idParcela = list_plots_by_user(
        $conn,
        $array_POST,
        $idUsuario,
        $landing_page,
        $page
    );
    $plotID = leerBBDD($conn, 'parcelasID', $idParcela, '', '', '');
    $descriptions = leerBBDD($conn, 'tipoSensores', $idParcela, '', '', '');
    if (count($plotID) > 0) {
        $result = leerBBDD(
            $conn,
            'lecturasDevices',
            $plotID[0]['id'],
            '',
            '',
            ''
        );
        printTable2('devices', $result, '', $descriptions, $page);
    }
}

function screen_show_table_cameras(
    $conn,
    $array_POST,
    $idUsuario = 1,
    $date_readings = '',
    $page,
    $landing_page
) {
    show_page_header('CAMARAS');
    $idParcela = list_plots_by_user(
        $conn,
        $array_POST,
        $idUsuario,
        $landing_page,
        $page
    );
    echo '<br>';
    $date_readings = get_dateTime_Picker(
        $conn,
        $array_POST,
        $idParcela,
        $page,
        $landing_page
    );
    $plotID = leerBBDD($conn, 'parcelasID', $idParcela, $date_readings, '', '');
    if (count($plotID) > 0) {
        $result = leerBBDD(
            $conn,
            'camaras',
            $plotID[0]['id'],
            $date_readings,
            '',
            ''
        );
    } else {
        $result = [];
    }
    echo '<br>';
    printTable2('camaras', $result, $page);
}

function screen_show_timetable_irrigation(
    $conn,
    $array_POST,
    $idUsuario,
    $page,
    $meteogalicia_prediccion_leyenda,
    $meteogalicia_prediccion_viento,
    $idConcello,
    $api_key,
    $landing_page
) {
    show_page_header('PROGRAMADOR DE RIEGO');
    $idParcela = list_plots_by_user(
        $conn,
        $array_POST,
        $idUsuario,
        $landing_page,
        $page
    );
    $modify = [];
    $plotID = leerBBDD($conn, 'parcelasID', $idParcela, '', '', '');
    if (isset($array_POST[$idParcela])) {
        $modify = explode('____', $array_POST[$idParcela]);
        $valor = leerBBDD(
            $conn,
            'temporizadorValor',
            $plotID[0]['id'],
            $modify[1],
            $modify[0],
            ''
        );
        if (count($valor) > 0) {
            if ($valor[0][$modify[1]] == 0) {
                $nuevo_valor = 1;
            }
            if ($valor[0][$modify[1]] == 1) {
                $nuevo_valor = 0;
            }
            //echo "<br>plotID: ".$plotID[0]['id']." A_".$modify[0]." B_".$modify[1]." nuevo_valor: ".$nuevo_valor."<br>";
            actualizarBBDD(
                $conn,
                'temporizador',
                $plotID[0]['id'],
                $modify[0],
                $modify[1],
                $nuevo_valor,
                ''
            );
        }
    }
    if (count($plotID) > 0) {
        //echo "plot es ".$plotID[0]['id']."<br>";
        $result = leerBBDD($conn, 'temporizador', $plotID[0]['id'], '', '', '');
        //print("<pre>".print_r($result,true)."</pre>");
    } else {
        $result = [];
    }
    $plotINE = leerBBDD($conn, 'parcelasINE', $idParcela, '', '', '');
    if (count($plotINE) > 0) {
        if (array_key_exists($plotINE[0]['idINE'], $idConcello) == 1) {
            //mostrar la previsi�n de Meteogalicia
            echo '<br><center><h2>PREDICCION METEOGALICIA</h2></center>';
            echo '<br>Prediccion por dias<br><br>';
            meteogalicia_obtener_prediccion_concello(
                $plotINE[0]['idINE'],
                1,
                $meteogalicia_prediccion_leyenda,
                $meteogalicia_prediccion_viento
            );
            echo '<br>Prediccion por horas<br><br>';
            meteogalicia_obtener_prediccion_concello(
                $plotINE[0]['idINE'],
                2,
                $meteogalicia_prediccion_leyenda,
                $meteogalicia_prediccion_viento
            );
            echo '<br><br>';
        }
        echo '<br><center><h2>PREDICCION AEMET</h2></center>';
        //mostrar la previsi�n de AEMET
        aemet_check_weather($plotINE[0]['idINE'], $api_key);
        echo '<br><br>';
        echo '<br><center><h2>HORAS DE RIEGO</h2></center>';
        echo '<form action="' . $landing_page . '" method="post">';
        printTable2('temporizador', $result, '', '', '', $idParcela, $page);
        echo '<br><br>';
        echo '<br><br>';
    }
    echo '</form>';
}

function screen_show_valves(
    $conn,
    $array_POST,
    $idUsuario,
    $page,
    $landing_page,
    $minutes_to_add = 60
) {
    show_page_header('ACTIVAR/DESACTIVAR VALVULAS');
    $idParcela = list_plots_by_user(
        $conn,
        $array_POST,
        $idUsuario,
        $landing_page,
        $page
    );
    echo '<br>';
    $modify = [];
    $plotID = leerBBDD($conn, 'parcelasID', $idParcela, '', '', '');
    if (isset($array_POST[$idParcela])) {
        $modify = explode('____', $array_POST[$idParcela]);
        $valor = leerBBDD(
            $conn,
            'estadoValvulasValor',
            $plotID[0]['id'],
            $modify[1],
            $modify[0],
            ''
        );
        if (count($valor) > 0) {
            if ($valor[0]['status'] == 0) {
                $nuevo_valor = 1;
            }
            if ($valor[0]['status'] == 1) {
                $nuevo_valor = 0;
            }
            //actualizarBBDD($conn,'estadoValvulas',$plotID[0]['id'],$modify[0],$nuevo_valor,'','');
            $time = new DateTime('now');
            $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));

            $new_time = $time->format('Y-m-d H:i');
            //a�adir tiempo de tener la v�lvula abierta
            actualizarBBDD(
                $conn,
                'estadoValvulasConHora',
                $plotID[0]['id'],
                $modify[0],
                $nuevo_valor,
                $new_time,
                ''
            );
        }
    }

    if (count($plotID) > 0) {
        $result = leerBBDD(
            $conn,
            'estadoValvulas',
            $plotID[0]['id'],
            '',
            '',
            ''
        );
    } else {
        $result = [];
    }
    echo '<form action="' . $landing_page . '" method="post">';
    printTable2('estadoValvulas', $result, '', '', '', $idParcela, $page);

    echo '</form>';
}

function dropdown_parcelas(
    $array_parcelas,
    $nombreId = 'parcelas',
    $selected = ''
) {
    echo '<select name="' .
        $nombreId .
        '" id="parcelas" onchange="this.form.submit()">';
    foreach ($array_parcelas as $plot) {
        echo '<option';
        if ($selected == $plot['idINE']) {
            echo ' selected';
        }
        echo ' value="' .
            $plot['idINE'] .
            '">' .
            $plot['idPlot'] .
            ' - ' .
            $plot['geolocation'] .
            '</option>';
    }
    echo '</select>';
}

function dropdown_usuarios(
    $array_usuarios,
    $nombreId = 'usuarios',
    $selected = ''
) {
    echo '<select name="' .
        $nombreId .
        '" id="usuarios" onchange="this.form.submit()">';
    echo '<option';
    if ($selected == '') {
        echo ' selected';
    }
    echo ' value="0">Seleccionar Usuario</option>';
    foreach ($array_usuarios as $user) {
        echo '<option';
        if ($selected == $user['id']) {
            echo ' selected';
        }
        echo ' value="' . $user['id'] . '">' . $user['name'] . '</option>';
    }
    echo '</select>';
}

function screen_show_assign_plots(
    $conn,
    $array_usuarios,
    $array_POST,
    $page,
    $landing_page
) {
    //pantalla que muestra las parcelas y permite escoger a que usuario pertenece cada parcela(multiparcelar)
    show_page_header('ASIGNAR PARCELA(S)/USUARIO');
    if (count($array_POST) > 0) {
        foreach ($array_POST as $clave => $valor) {
            if (str_starts_with($clave, 'parcela')) {
                $idParcela_POST = substr($clave, 7, strlen($clave));
                actualizarBBDD(
                    $conn,
                    'parcelas',
                    $idParcela_POST,
                    $valor,
                    '',
                    '',
                    ''
                );
            }
        }
    }
    //actualizar bbdd
    $array_parcelas = leerBBDD($conn, 'parcelas', '', '', '', '');
    echo '<form action="' . $landing_page . '" method="post">';
    echo '<table border=1>';
    echo '<tr><td>Parcela</td><td>Usuario</td></tr>';
    echo '<tr>';
    for ($a = 0; $a < count($array_parcelas); $a++) {
        echo '<td>' . $array_parcelas[$a]['idPlot'] . '</td><td>';
        dropdown_usuarios(
            $array_usuarios,
            'parcela' . $array_parcelas[$a]['idPlot'],
            $array_parcelas[$a]['idUser']
        );
        echo '</td></tr>';
    }
    echo '</table>';
    echo '<input type="hidden" name="page" value="' . $page . '" >';
    echo '</form>';
}

function screen_show_screen_add_user($conn, $array_POST)
{
    show_page_header('A&Ntilde;ADIR USUARIO');
    process_POST_add_user($conn, $array_POST);
    form_add_user();
}

function screen_show_add_postal_code_to_plot(
    $conn,
    $array_POST,
    $page,
    $landing_page
) {
    show_page_header('ASIGNAR LOCALIZACION');
    if (count($array_POST) > 0) {
        foreach ($array_POST as $clave => $valor) {
            if (str_starts_with($clave, 'parcela')) {
                $idParcela_POST = substr($clave, 7, strlen($clave));
                actualizarBBDD(
                    $conn,
                    'parcelasINE',
                    $idParcela_POST,
                    $valor,
                    '',
                    '',
                    ''
                );
            }
        }
    }
    //actualizar bbdd
    $array_parcelas = leerBBDD($conn, 'parcelas', '', '', '', '');

    echo '<form action="' . $landing_page . '" method="post">';
    echo '<table border=1>';
    echo '<tr><td>Parcela</td><td>Localizacion</td></tr>';
    echo '<tr>';
    for ($a = 0; $a < count($array_parcelas); $a++) {
        echo '<td>' . $array_parcelas[$a]['idPlot'] . '</td><td>';
        $listado_municipios = load_postal_codes();
        list_councils(
            $listado_municipios,
            'parcela' . $array_parcelas[$a]['idPlot'],
            $array_parcelas[$a]['idINE']
        );
        echo '</td></tr>';
    }
    echo '</table>';
    echo '<input type="hidden" name="page" value="' . $page . '" >';
    echo '</form>';
}

function list_plots_by_user(
    $conn,
    $array_POST,
    $idUsuario,
    $landing_page,
    $page
) {
    $array_parcelas = leerBBDD($conn, 'parcelasU', '', $idUsuario, '', '');
    //print("<pre>".print_r($array_POST,true)."</pre>");
    if (isset($array_POST['parcela'])) {
        $plot_selected = $array_POST['parcela'];
    } else {
        $plot_selected = '-2';
    }
    echo '<form action="' . $landing_page . '" method="post">';
    echo '<label>Seleccionar Parcela:</label><br>';
    echo '<select id="plot" name="parcela" onchange="this.form.submit()">';
    echo '<option value="-1">Escoger Parcela para ver datos</option>';
    if (count($array_parcelas) > 0) {
        for ($a = 0; $a < count($array_parcelas); $a++) {
            echo '<option value="' . $array_parcelas[$a]['idPlot'] . '"';
            if ($plot_selected == $array_parcelas[$a]['idPlot']) {
                echo ' selected';
            }
            echo '>' .
                $array_parcelas[$a]['idPlot'] .
                '(' .
                $array_parcelas[$a]['geolocation'] .
                ')</option>';
        }
    } else {
        echo '<option>No hay parcelas asignadas</option>';
    }
    echo '</select>';
    echo '<input type="hidden" name="page" value="' . $page . '" >';
    echo '</form>';
    return $plot_selected;
}

function get_dateTime_Picker(
    $conn,
    $array_POST,
    $idParcela,
    $page,
    $landing_page
) {
    //establecer la fecha de hoy
    if (isset($array_POST['date'])) {
        $date_readings = $array_POST['date'];
    } else {
        $date_readings = date('Y-m-d');
    }
    $temp = leerBBDD($conn, 'parcelasID', $idParcela, '', '', '');
    form_show_datepicker($date_readings, $idParcela, $landing_page, $page);
    return $date_readings;
}

function screenLogin($landing_page)
{
    echo '
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form action="' .
        $landing_page .
        '" method="post">
        <h3>Inicie Sesi&oacute;n para continuar</h3>

        <label for="username">Correo electr&oacute;nico</label>
        <input type="text" placeholder="Introduzca Correo Electr&oacute;nico" id="username" name="loginuser">

        <label for="password">Contrase&ntilde;a</label>
        <input type="password" placeholder="Introduzca Contrase&ntilde;a" id="password" name="loginpass">

        <button>Iniciar Sesi&oacute;n</button>
    </form>
</body>
    ';
}

function show_page_header($title_header)
{
    echo '<div class="header1"><h1>' . $title_header . '</h1>';
    echo '</div><div class="content1">';
}
?>
