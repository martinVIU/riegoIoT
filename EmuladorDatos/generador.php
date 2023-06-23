<?php
echo '<html><head><title>Riego IoT - Generador de lecturas de suelos</title></head><body>';
echo '<h1><center>SIMULADOR DE SUELOS</center></h1><br>';

$total_sensores = 8;
$total_valvulas = 4;

$h1_1 = 60;
$h1_2 = 60;
$h2_1 = 62;
$h2_2 = 62;
$h3_1 = 64;
$h3_2 = 64;
$h4_1 = 58;
$h4_2 = 58;
$h5_1 = 53;
$h5_2 = 53;
$h6_1 = 50;
$h6_2 = 50;
$h7_1 = 63;
$h7_2 = 63;
$h8_1 = 65;
$h8_2 = 65;
$v1_1 = 0;
$v1_2 = 0;
$v2_1 = 0;
$v2_2 = 0;
$v3_1 = 0;
$v3_2 = 0;
$v4_1 = 0;
$v4_2 = 0;

$array = [];

//la posici�pn -1 almacena el nombre del sensor o de la v�lvula
$array['h1_1'][-1] = 'H1_1';
$array['h1_2'][-1] = 'H1_2';
$array['h2_1'][-1] = 'H2_1';
$array['h2_2'][-1] = 'H2_2';
$array['h3_1'][-1] = 'H3_1';
$array['h3_2'][-1] = 'H3_2';
$array['h4_1'][-1] = 'H4_1';
$array['h4_2'][-1] = 'H4_2';
$array['h5_1'][-1] = 'H5_1';
$array['h5_2'][-1] = 'H5_2';
$array['h6_1'][-1] = 'H6_1';
$array['h6_2'][-1] = 'H6_2';
$array['h7_1'][-1] = 'H7_1';
$array['h7_2'][-1] = 'H7_2';
$array['h8_1'][-1] = 'H8_1';
$array['h8_2'][-1] = 'H8_2';

$array['v1_1'][-1] = 'V1_1';
$array['v1_2'][-1] = 'V1_2';
$array['v2_1'][-1] = 'V2_1';
$array['v2_2'][-1] = 'V2_2';
$array['v3_1'][-1] = 'V3_1';
$array['v3_2'][-1] = 'V3_2';
$array['v4_1'][-1] = 'V4_1';
$array['v4_2'][-1] = 'V4_2';

//intervalos en los que hay sol y calienta el suelo, cada valor es el número de intervalo de 10 minutos del día (por ejemplo: 42 = minutos 420 a 429 del día).
$horas_sol = [
    42,
    43,
    44,
    45,
    46,
    47,
    48,
    49,
    50,
    51,
    52,
    53,
    54,
    55,
    56,
    57,
    58,
    59,
    60,
    61,
    62,
    63,
    64,
    65,
    66,
    67,
    68,
    69,
    70,
    71,
    72,
    73,
    74,
    75,
    76,
    77,
    78,
    79,
    80,
    81,
    82,
    83,
    84,
    85,
    86,
    87,
    88,
    89,
    90,
    91,
    92,
    93,
    94,
    95,
    96,
    97,
    98,
    99,
    100,
    101,
    102,
    103,
    104,
    105,
    106,
    107,
    108,
    109,
    110,
    111,
    112,
    113,
    114,
    115,
    116,
    117,
    118,
    119,
    120,
    121,
    122,
    123,
    124,
    125,
    126,
    127,
    128,
    129,
    130,
    131,
    132,
    133,
    134,
]; //sale a las 7:00 y se pone a las 22:20

//intervalos donde hay riegos
$horas_riego = [0, 1, 2, 3, 4, 5, 6, 61, 62, 63, 64, 65, 132, 133, 134];

for ($a = 0; $a <= 144; $a++) {
    //144 = 1440 minutos = 1 d�a en intervalos de 10 minutos
    //echo $a." <br>";
    if (in_array($a, $horas_sol)) {
        //se seca la tierra
        if ($a < 60) {
            //antes de las 10 am
            for ($b = 1; $b <= $total_sensores; $b++) {
                //la parte de arriba se va secando poco, aun no hay temperatura
                $variacion = rand(1, 3) / 10;
                ${'h' . $b . '_1'} = ${'h' . $b . '_1'} + $variacion;
                //la parte de abajo recoge la variacion de humedad de arriba menos un % de perdidas
                // ${"h".$b."_2"} = ${"h".$b."_1"} + round(rand(0,1),0)*(-1) * $variacion * rand(1,5);
                ${'h' . $b . '_2'} =
                    rand(10, 20) - 10 > 0
                        ? ${'h' . $b . '_1'} + $variacion * rand(1, 5)
                        : ${'h' . $b . '_1'} - ($variacion * rand(1, 5)) / 10;
            }
        }
        if ($a >= 60 && $a <= 420) {
            //de 10 am a 7pm horas de calor
            for ($b = 1; $b <= $total_sensores; $b++) {
                $variacion = rand(2, 4) / 10;
                ${'h' . $b . '_1'} = ${'h' . $b . '_1'} - $variacion;
                //la parte de abajo recoge la variacion de humedad de arriba menos un % de perdidas
                ${'h' . $b . '_2'} =
                    rand(10, 20) - 10 > 0
                        ? ${'h' . $b . '_1'} + $variacion * rand(1, 5)
                        : ${'h' . $b . '_1'} - ($variacion * rand(1, 5)) / 10;
            }
        }

        if ($a > 420) {
            //a partir de las 7pm refresca
            for ($b = 1; $b <= $total_sensores; $b++) {
                $variacion = rand(1, 5) / 10;
                ${'h' . $b . '_1'} = ${'h' . $b . '_1'} + $variacion;
                //la parte de abajo recoge la variacion de humedad de arriba menos un % de perdidas
                ${'h' . $b . '_2'} =
                    rand(10, 20) - 10 > 0
                        ? ${'h' . $b . '_1'} + $variacion * rand(1, 5)
                        : ${'h' . $b . '_1'} - ($variacion * rand(1, 5)) / 10;
            }
        }
    }

    if (in_array($a, $horas_riego)) {
        //activamos todas las valvulas
        for ($b = 1; $b <= $total_sensores; $b++) {
            $array['v' . $b . '_1'][$a] = '1';
            $array['v' . $b . '_2'][$a] = rand(45, 50);
        }

        //buena absorcion de agua
        if ($a < 60) {
            //antes de las 10 am
            for ($b = 1; $b <= $total_sensores; $b++) {
                //la parte de arriba se va secando poco, aun no hay temperatura
                $variacion = rand(3, 7) / 10;
                ${'h' . $b . '_1'} = ${'h' . $b . '_1'} - $variacion;
                //la parte de abajo recoge la variacion de humedad de arriba menos un % de perdidas
                ${'h' . $b . '_2'} =
                    rand(10, 20) - 10 > 0
                        ? ${'h' . $b . '_1'} + $variacion * rand(1, 5)
                        : ${'h' . $b . '_1'} - ($variacion * rand(1, 5)) / 10;
            }
        }
        if ($a >= 60 && $a <= 420) {
            //de 10 am a 7pm horas de calor
            for ($b = 1; $b <= $total_sensores; $b++) {
                $variacion = rand(3, 7) / 10;
                ${'h' . $b . '_1'} = ${'h' . $b . '_1'} - $variacion;
                //la parte de abajo recoge la variacion de humedad de arriba menos un % de perdidas
                ${'h' . $b . '_2'} =
                    rand(10, 20) - 10 > 0
                        ? ${'h' . $b . '_1'} + $variacion * rand(1, 5)
                        : ${'h' . $b . '_1'} - ($variacion * rand(1, 5)) / 10;
            }
        }

        if ($a > 420) {
            //a partir de las 7pm refresca
            for ($b = 1; $b <= $total_sensores; $b++) {
                $variacion = rand(3, 7) / 10;
                ${'h' . $b . '_1'} = ${'h' . $b . '_1'} + $variacion;
                //la parte de abajo recoge la variacion de humedad de arriba menos un % de perdidas
                ${'h' . $b . '_2'} =
                    rand(10, 20) - 10 > 0
                        ? ${'h' . $b . '_1'} + $variacion * rand(1, 5)
                        : ${'h' . $b . '_1'} - ($variacion * rand(1, 5)) / 10;
            }
        }
    } else {
        for ($b = 1; $b <= $total_valvulas; $b++) {
            $array['v' . $b . '_1'][$a] = '0';
            $array['v' . $b . '_2'][$a] = '0';
        }
    }
    //guardar las humedades en el array
    for ($b = 1; $b <= $total_sensores; $b++) {
        for ($c = 1; $c <= 2; $c++) {
            $array['h' . $b . '_' . $c][$a] = round(${'h' . $b . '_' . $c}, 2);
        }
    }
}

echo '<br>';

echo "<table border='1'>";
echo '<tr>';
for ($a = -1; $a <= 144; $a++) {
    if ($a > -1) {
        echo '<td>' . $a . '</td>';
    } else {
        echo '<td></td>';
    }
}
echo '</tr><tr>';
for ($b = 1; $b <= $total_sensores; $b++) {
    for ($c = 1; $c <= 2; $c++) {
        for ($a = -1; $a <= 144; $a++) {
            echo '<td>' . $array['h' . $b . '_' . $c][$a] . '</td>';
        }
        echo '</tr><tr>';
    }
    echo '</tr><tr>';
}
for ($b = 1; $b <= $total_valvulas; $b++) {
    for ($c = 1; $c <= 2; $c++) {
        for ($a = -1; $a <= 144; $a++) {
            echo '<td>' . $array['v' . $b . '_' . $c][$a] . '</td>';
        }
        echo '</tr><tr>';
    }
    echo '</tr><tr>';
}

echo '</tr><tr>';
echo '</tr></table>';

echo '<br><br>';
//Para crear las instrucciones SQL de inserci�n en la tabla con los datos generados
/*
for ($a=0;$a<=144;$a++){
    
    $hours = floor($a*10 / 60);
    $minutes = ($a*10 % 60);
    
    if (strlen($hours)<2 ){$hours = "0" . $hours;}
    if (strlen($minutes)<2 ){$minutes = "0" . $minutes;}
    
    for ($b=1;$b<=$total_sensores;$b++){ echo "<br>INSERT INTO `sensorestfg`.`readings` (`idSensor`,`value1`,`value2`,`idPlot`,`date`) VALUES ('"."H".$b."','".$array["h".$b."_1"][$a]."','".$array["h".$b."_2"][$a]."','1','2023-07-01 ".$hours.":".$minutes.":00');";}
    for ($b=1;$b<=$total_valvulas;$b++){ echo "<br>INSERT INTO `sensorestfg`.`readings` (`idSensor`,`value1`,`value2`,`idPlot`,`date`) VALUES ('"."V".$b."','".$array["h".$b."_1"][$a]."','".$array["h".$b."_2"][$a]."','1','2023-07-01 ".$hours.":".$minutes.":00');";}
}
*/
echo '<br>';
/*
for ($a=0;$a<=144;$a++){
    for ($b=1;$b<=$total_valvulas;$b++){
        if ($a<1){
            echo "<br>UPDATE `sensorestfg`.`timer` SET `".$a."`='".$array["v".$b."_1"][$a]."' WHERE  `id`=".$b.";";
        }else{
            echo "<br>UPDATE `sensorestfg`.`timer` SET `".$a."0`='".$array["v".$b."_1"][$a]."' WHERE  `id`=".$b.";";    
        }
        
        }
}
*/
echo '</body></html>';
?>
