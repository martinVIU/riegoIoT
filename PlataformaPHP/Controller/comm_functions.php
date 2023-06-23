<?php

function actualiza_estado_valvula($conn, $id_parcela, $idSensor)
{
    //funcion que lee la hora actual, lee la tabla temporizador y actualiza el valor de la valvula correspondiente
    //hacer que si se modifica el temporizador, se a�ade a la modifcacion de la valvula un valor de tiempo actual + 59 minutos abierta

    $date = date('Y-m-d', time());
    $date1 = new DateTime('now');
    $date2 = new DateTime($date);
    $interval = $date1->diff($date2);
    $diff_mins = floor(
        abs($date1->getTimestamp() - $date2->getTimestamp()) / 60
    );
    $interval_db = $diff_mins - ($diff_mins % 10);
    $result = leerBBDD(
        $conn,
        'temporizadorInteval',
        $id_parcela,
        $interval_db,
        $idSensor,
        ''
    );

    $status_valve_timetable = $result[0][0];
    //si se ha pulsado boton, se almace desde t hasta t+1h
    //si aun en ese tiempo, ignorar temporizador, sino sobreescribir valor.

    $result = leerBBDD(
        $conn,
        'estadoValvulasValorHora',
        $id_parcela,
        '',
        $idSensor,
        ''
    );

    if ($result[0]['tActiv'] == null) {
        //actualizar con el valor presente en la tabla programador
        if ($result[0]['status'] != $status_valve_timetable) {
            //actualizar valor de estado de electrov�lvula
            actualizarBBDD(
                $conn,
                'estadoValvulas',
                $id_parcela,
                $idSensor,
                $status_valve_timetable,
                '',
                ''
            );
        } else {
            //la electrov�lvula ya tiene el valor correcto
        }
    } else {
        $date1 = new DateTime('now');
        $date2 = new DateTime($result[0]['tActiv']);
        $interval = $date1->diff($date2);
        $diff_mins = floor(
            abs($date1->getTimestamp() - $date2->getTimestamp()) / 60
        );

        //echo "<br> Intervalo (".$interval_db.") diferencia (minutos): ".$diff_mins;

        if ($diff_mins > 0 && $diff_mins <= 60) {
            //echo "<br><h2>EN HORA</h2>";
        } else {
            //echo "<br><h2>HORA PASADA/AUN NO EN HORA</h2>";
            //actualizar con el valor presente en la tabla programador
            if ($result[0]['status'] != $status_valve_timetable) {
                //actualizar valor de estado de electrov�lvula
                actualizarBBDD(
                    $conn,
                    'estadoValvulas',
                    $id_parcela,
                    $idSensor,
                    $status_valve_timetable,
                    '',
                    ''
                );
            } else {
                //la electrov�lvula ya tiene el valor correcto
            }
        }
    }
}

function setOrder($conn, $idParcela, $interval_readings)
{
    //el intervalo entre lecturas es en minutos

    $result = leerBBDD($conn, 'parcelasUltimaLectura', $idParcela, '', '', '');
    $id_parcela = $result[0]['lastreading'];
    if (count($result) == 0) {
        exit();
    } //no existe la parcela en la BBDD

    $date1 = new DateTime('now');
    $date2 = new DateTime($result[0]['lastreading']);
    $interval = $date1->diff($date2);
    $diff_mins = floor(
        abs($date1->getTimestamp() - $date2->getTimestamp()) / 60
    );

    if ($diff_mins >= 0 && $diff_mins < $interval_readings) {
        //echo "___" . $diff_mins . "_interval_".$interval_readings."___";
        //lecturas aun en tiempo
        return 0;
    } else {
        //hay que actualizar lecturas, por lo que se marca el momento actual como referencia
        actualizarBBDD(
            $conn,
            'parcelasUltimaLectura',
            $idParcela,
            $date1->format('Y-m-d H:i:s'),
            '',
            '',
            ''
        );

        return 1;
    }
}

function orders_to_device($conn, $idParcela, $delimiter, $num_order = 0)
{
    $result = leerBBDD($conn, 'parcelasID', $idParcela, '', '', '');
    $id_parcela = $result[0]['id'];
    if (count($result) == 0) {
        exit();
    } //no existe la parcela en la BBDD

    $result = leerBBDD($conn, 'estadoValvulas', $id_parcela, '', '', '');
    if (count($result) > 0) {
        $array_valves = [];

        //Se actualiza el estado de las v�lvulas
        for ($i = 0; $i < count($result); $i++) {
            actualiza_estado_valvula(
                $conn,
                $id_parcela,
                $result[$i]['idSensor']
            );
        }
        unset($result); //eliminar la variable de resultados por prevenci�n

        //recargar los datos actualizados
        $result = leerBBDD($conn, 'estadoValvulas', $id_parcela, '', '', '');
        for ($i = 0; $i < count($result); $i++) {
            $temp = [];
            array_push($temp, $result[$i]['idSensor']);
            array_push($temp, $result[$i]['status']);
            $array_valves[] = implode(';', $temp);
            unset($temp);
        }
        $array_valves_string = implode($delimiter, $array_valves);

        //emitir la orden para el Control Central
        echo 'ORDER:' . $num_order . ':ORDER';
        echo 'VALVES:' . $array_valves_string . $delimiter . ':VALVES'; //a�adir delimitador al final para que el while use el ultimo valor tambien
    }
}

?>
