<?php

function createConnectionDB()
{
    //la ruta relativa cambia en funci�n de la profundidad de nivel desde donde sea llamado
    $config_file_pdo = '/cfg/pdoconfig.php';
    if (!file_exists($config_file_pdo)) {
        if (!file_exists('..' . $config_file_pdo)) {
            if (!file_exists('./..' . $config_file_pdo)) {
                if (!file_exists('./../..' . $config_file_pdo)) {
                    if (!file_exists('.' . $config_file_pdo)) {
                        require_once '.' . $config_file_pdo;
                    } else {
                        require_once '.' . $config_file_pdo;
                    }
                    //echo "DIR NO ENCONTRADO!!!!".getcwd();
                } else {
                    require_once './../..' . $config_file_pdo;
                }
            } else {
                require_once './..' . $config_file_pdo;
            }
        } else {
            require_once '..' . $config_file_pdo;
        }
    } else {
        require_once $config_file_pdo;
    }

    try {
        $connect = new PDO(
            "mysql:host=$host;dbname=$dbname",
            $username,
            $password
        );
        //echo "Connected to $dbname at $host successfully.<br>";
    } catch (PDOException $pe) {
        die("Could not connect to the database $dbname :" . $pe->getMessage());
    }

    return $connect;
}

function insertarBBDD(
    $connect,
    $tabla,
    $dato1,
    $dato2,
    $dato3,
    $dato4,
    $dato5 = '',
    $dato6 = ''
) {
    switch ($tabla) {
        case 'camaras':
            $sql =
                'INSERT INTO cameras(idSensor,date,file,idPlot) values(:idSensor,:date,:file,:idPlot)';
            break;
        case 'lecturas':
            $sql =
                'INSERT INTO readings(idSensor,value1,value2,idPlot,date) values(:idSensor,:value1,:value2,:idPlot,:date)';
            break;
        case 'parcelas':
            $sql =
                'INSERT INTO plots(id,idPlot,geolocation,idUser,idINE,lastreading) VALUES(NULL,:nombreParcela,:geolocation, NULL, NULL, NULL)';
            break;
        case 'temporizador':
            $sql =
                'INSERT INTO timer(idSensor,idPlot) VALUES (:idSensor,:idPlot)';
            break;
        case 'sensores':
            $sql =
                'INSERT INTO sensores(id,idPlot,idSensor,value,date) VALUES(NULL,:idPlot,:idSensor,:value,:date)';
            break;
        case 'usuarios':
            $sql =
                'INSERT INTO users(id,name,level,mail,pass) VALUES(NULL,:idUser,:level,:mail,:pass)';
            break;
        case 'estadoValvulas':
            $sql =
                'INSERT INTO statevalves(id,idPlot,idSensor,status,tActiv) VALUES(NULL,:idPlot,:idSensor,:status,NULL)';
            break;
        default:
            $sql = '';
    }

    //echo $sql;
    $sql = $connect->prepare($sql);

    switch ($tabla) {
        case 'camaras':
            $sql->bindParam(':idSensor', $dato1, PDO::PARAM_STR, 20);
            $sql->bindParam(':date', $dato2, PDO::PARAM_STR, 10);
            $sql->bindParam(':file', $dato3, PDO::PARAM_STR, 40);
            $sql->bindParam(':idPlot', $dato4, PDO::PARAM_STR, 10);
            break;
        case 'lecturas':
            $sql->bindParam(':idSensor', $dato1, PDO::PARAM_STR, 20);
            $sql->bindParam(':value1', $dato2, PDO::PARAM_STR, 10);
            $sql->bindParam(':value2', $dato3, PDO::PARAM_INT, 11);
            $sql->bindParam(':idPlot', $dato4, PDO::PARAM_STR, 10);
            $sql->bindParam(':date', $dato5, PDO::PARAM_STR, 40);
            break;
        case 'parcelas':
            $sql->bindParam(':nombreParcela', $dato1, PDO::PARAM_STR, 20);
            $sql->bindParam(':geolocation', $dato2, PDO::PARAM_STR, 60);
            //$sql->bindParam(':cp',$dato3,PDO::PARAM_STR, 60);
            break;
        case 'temporizador':
            $sql->bindParam(':idSensor', $dato1, PDO::PARAM_STR, 25);
            $sql->bindParam(':idPlot', $dato2, PDO::PARAM_STR, 25);
            break;

        case 'sensores':
            $sql->bindParam(':idPlot', $datos[0], PDO::PARAM_STR, 10);
            $sql->bindParam(':idSensor', $datos[1], PDO::PARAM_STR, 10);
            $sql->bindParam(':value', $datos[2], PDO::PARAM_STR, 10);
            $sql->bindParam(':date', $datos[2], PDO::PARAM_STR, 50);
            break;

        case 'sensoresDif':
            $sql->bindParam(':idPlot', $datos[0], PDO::PARAM_STR, 10);
            $sql->bindParam(':idSensor', $datos[1], PDO::PARAM_STR, 10);
            $sql->bindParam(':value', $datos[2], PDO::PARAM_STR, 10);
            $sql->bindParam(':date', $datos[2], PDO::PARAM_STR, 50);
            break;

        case 'usuarios':
            $sql->bindParam(':idUser', $dato1, PDO::PARAM_STR, 25);
            $sql->bindParam(':level', $dato2, PDO::PARAM_STR, 25);
            $sql->bindParam(':mail', $dato3, PDO::PARAM_STR, 25);
            $sql->bindParam(':pass', $dato4, PDO::PARAM_STR, 25);
            break;

        case 'estadoValvulas':
            $sql->bindParam(':idPlot', $dato1, PDO::PARAM_STR, 25);
            $sql->bindParam(':idSensor', $dato2, PDO::PARAM_STR, 25);
            $sql->bindParam(':status', $dato3, PDO::PARAM_STR, 25);
            break;
        default:
            $sql = '';
    }

    if ($tabla === 'lecturas') {
    }
    $sql->execute();
}

function consultarBBDD($connect, $tabla, $dato1, $dato2, $dato3, $dato4, $modo)
{
    if ($tabla === 'lecturas') {
        $sql =
            'insert into readings(idSensor,value1,value2) values(:idSensor,:value1,:value2)';
    }

    $sql = $connect->prepare($sql);

    if ($tabla === 'lecturas') {
        $sql->bindParam(':idSensor', $datos[0], PDO::PARAM_STR, 25);
        $sql->bindParam(':value1', $datos[1], PDO::PARAM_STR, 25);
        $sql->bindParam(':value2', $datos[2], PDO::PARAM_STR, 25);
    }
    $sql->execute();
}

function actualizarBBDD($connect, $tabla, $dato1, $dato2, $dato3, $dato4, $modo)
{
    if ($tabla === 'parcelasUltimaLectura') {
        $sql =
            'UPDATE plots SET `lastreading`=:lastreading WHERE `idPlot`=:idPlot';
    }
    if ($tabla === 'parcelas') {
        $sql = 'UPDATE plots SET `idUser`=:dato1 WHERE `idPlot`=:idPlot';
    }
    if ($tabla === 'parcelasINE') {
        $sql = 'UPDATE plots SET `idINE`=:dato1 WHERE `idPlot`=:idPlot';
    }

    if ($tabla === 'temporizador') {
        $sql =
            'UPDATE `timer` SET `' .
            $dato3 .
            '`=:value WHERE  `idSensor`=:idSensor AND `idPlot`=:idPlot';
    }

    if ($tabla === 'estadoValvulas') {
        $sql =
            'UPDATE `statevalves` SET `status`=:value WHERE  `idSensor`=:idSensor AND `idPlot`=:idPlot';
    }
    if ($tabla === 'estadoValvulasConHora') {
        $sql =
            'UPDATE `statevalves` SET `status`=:value, `tActiv`=:ultimaModificacion WHERE  `idSensor`=:idSensor AND `idPlot`=:idPlot';
    }

    //echo "<br><br>".$sql."____dato1_".$dato2."   idPlot: ".$dato1."<br><br>";
    $sql = $connect->prepare($sql);

    if ($tabla === 'parcelasUltimaLectura') {
        $sql->bindParam(':lastreading', $dato2, PDO::PARAM_STR, 50);
        $sql->bindParam(':idPlot', $dato1, PDO::PARAM_STR, 25);
    }
    if ($tabla === 'parcelas') {
        $sql->bindParam(':idPlot', $dato1, PDO::PARAM_STR, 25);
        $sql->bindParam(':dato1', $dato2, PDO::PARAM_STR, 25);
    }

    if ($tabla === 'parcelasINE') {
        $sql->bindParam(':idPlot', $dato1, PDO::PARAM_STR, 25);
        $sql->bindParam(':dato1', $dato2, PDO::PARAM_STR, 25);
    }

    if ($tabla === 'temporizador') {
        $sql->bindParam(':value', $dato4, PDO::PARAM_STR, 25);
        $sql->bindParam(':idSensor', $dato2, PDO::PARAM_STR, 25);
        $sql->bindParam(':idPlot', $dato1, PDO::PARAM_STR, 25);
    }

    if ($tabla === 'estadoValvulas') {
        $sql->bindParam(':value', $dato3, PDO::PARAM_STR, 25);
        $sql->bindParam(':idSensor', $dato2, PDO::PARAM_STR, 25);
        $sql->bindParam(':idPlot', $dato1, PDO::PARAM_STR, 25);
    }

    if ($tabla === 'estadoValvulasConHora') {
        $sql->bindParam(':value', $dato3, PDO::PARAM_STR, 25);
        $sql->bindParam(':ultimaModificacion', $dato4, PDO::PARAM_STR, 50);
        $sql->bindParam(':idSensor', $dato2, PDO::PARAM_STR, 25);
        $sql->bindParam(':idPlot', $dato1, PDO::PARAM_STR, 25);
    }

    $sql->execute();
}

function borrarBBDD($connect, $tabla, $dato1, $dato2, $dato3, $dato4, $modo)
{
    //NO USADO
    if ($tabla === 'lecturas') {
        $sql =
            'insert into readings(idSensor,value1,value2) values(:idSensor,:value1,:value2)';
    }

    $sql = $connect->prepare($sql);

    if ($tabla === 'lecturas') {
        $sql->bindParam(':idSensor', $datos[0], PDO::PARAM_STR, 25);
        $sql->bindParam(':value1', $datos[1], PDO::PARAM_STR, 25);
        $sql->bindParam(':value2', $datos[2], PDO::PARAM_STR, 25);
    }
    $sql->execute();
}

function leerBBDD($connect, $tabla, $idPlot, $dato2, $dato3, $dato4)
{
    if ($tabla === 'checklogin') {
        $sql = 'SELECT level FROM users WHERE `mail`=:mail AND `pass`=:pass';
    }
    if ($tabla === 'lecturas') {
        $sql =
            'Select idSensor, value1, value2, date from readings Where `idPlot`=:idPlot ORDER BY date ASC';
    }
    if ($tabla === 'lecturasF') {
        $sql =
            'Select idSensor, value1, value2, date from readings Where `idPlot`=:idPlot AND date between :date1 AND :date2 ORDER BY date ASC';
    }
    if ($tabla === 'lecturasDevices') {
        $sql = 'SELECT DISTINCT idSensor from readings Where `idPlot`=:idPlot';
    }
    if ($tabla === 'sensoresDif') {
        $sql =
            'SELECT DISTINCT idSensor from readings Where `idPlot`=:idPlot AND date between :date1 AND :date2 ORDER BY date ASC';
    }
    if ($tabla === 'usuarios') {
        $sql = 'Select id, name from users';
    }
    if ($tabla === 'usuariosC') {
        $sql = 'Select mail from users Where `mail`=:mail';
    }
    if ($tabla === 'usuariosID') {
        $sql = 'Select id from users Where `mail`=:mail';
    }
    if ($tabla === 'usuariosLVL') {
        $sql = 'Select level from users Where `id`=:userID';
    }
    if ($tabla === 'parcelas') {
        $sql = 'Select idPlot, geolocation, idUser, idINE from plots';
    }
    if ($tabla === 'parcelasU') {
        $sql =
            'Select idPlot, geolocation, idUser, idINE from plots WHERE `idUser`=:idUser';
    }
    if ($tabla === 'parcelasID') {
        $sql = 'Select id from plots WHERE `idPlot`=:idPlot';
    }
    if ($tabla === 'parcelasUltimaLectura') {
        $sql = 'Select lastreading from plots WHERE `idPlot`=:idPlot';
    }
    if ($tabla === 'parcelasINE') {
        $sql = 'Select idINE from plots WHERE `idPlot`=:idPlot';
    }
    if ($tabla === 'tipoSensores') {
        $sql = 'Select letter, type, dsc from sensortypes';
    }
    if ($tabla === 'camaras') {
        //$sql="SELECT idSensor, date, file, thumb from camaras Where `idPlot`=:idPlot";
        $sql =
            'Select idSensor, date, file from cameras Where `idPlot`=:idPlot AND date between :date1 AND :date2 ORDER BY date ASC';
    }
    if ($tabla === 'temporizador') {
        $sql =
            'SELECT `idSensor`, `0`, `10`, `20`, `30`, `40`, `50`, `60`, `70`, `80`, `90`, `100`, `110`, `120`, `130`, `140`, `150`, `160`, `170`, `180`, `190`, `200`, `210`, `220`, `230`, `240`, `250`, `260`, `270`, `280`, `290`, `300`, `310`, `320`, `330`, `340`, `350`, `360`, `370`, `380`, `390`, `400`, `410`, `420`, `430`, `440`, `450`, `460`, `470`, `480`, `490`, `500`, `510`, `520`, `530`, `540`, `550`, `560`, `570`, `580`, `590`, `600`, `610`, `620`, `630`, `640`, `650`, `660`, `670`, `680`, `690`, `700`, `710`, `720`, `730`, `740`, `750`, `760`, `770`, `780`, `790`, `800`, `810`, `820`, `830`, `840`, `850`, `860`, `870`, `880`, `890`, `900`, `910`, `920`, `930`, `940`, `950`, `960`, `970`, `980`, `990`, `1000`, `1010`, `1020`, `1030`, `1040`, `1050`, `1060`, `1070`, `1080`, `1090`, `1100`, `1110`, `1120`, `1130`, `1140`, `1150`, `1160`, `1170`, `1180`, `1190`, `1200`, `1210`, `1220`, `1230`, `1240`, `1250`, `1260`, `1270`, `1280`, `1290`, `1300`, `1310`, `1320`, `1330`, `1340`, `1350`, `1360`, `1370`, `1380`, `1390`, `1400`, `1410`, `1420`, `1430`, `1440` FROM timer WHERE `idPlot` = :idPlot;';
    }
    if ($tabla === 'temporizadorInteval') {
        $sql =
            'SELECT `' .
            $dato2 .
            '` FROM timer WHERE `idPlot` = :idPlot AND `idSensor` = :idSensor;';
    }
    if ($tabla === 'temporizadorValor') {
        $sql = 'SELECT `' . $dato2 . '` FROM timer WHERE `idSensor`=:idSensor';
    }

    if ($tabla === 'estadoValvulas') {
        $sql =
            'Select idSensor, status from statevalves WHERE `idPlot`=:idPlot';
    }
    if ($tabla === 'estadoValvulasValor') {
        $sql =
            'Select status from statevalves WHERE `idPlot`=:idPlot AND `idSensor`=:idSensor';
    }
    if ($tabla === 'estadoValvulasValorHora') {
        $sql =
            'Select status, tActiv from statevalves WHERE `idPlot`=:idPlot AND `idSensor`=:idSensor';
    }
    //echo "<br><br>".$sql."<br>";
    $sql = $connect->prepare($sql);

    if ($tabla === 'checklogin') {
        $sql->bindParam(':mail', $idPlot, PDO::PARAM_STR, 25);
        $sql->bindParam(':pass', $dato2, PDO::PARAM_STR, 25);
    }
    if ($tabla === 'lecturas') {
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 25);
    }
    if ($tabla === 'lecturasF') {
        $date = date('Y-m-d H:i:s', strtotime($dato2));
        $stop_date = date('Y-m-d H:i:s', strtotime($dato2 . ' +1 day'));

        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 25);
        $sql->bindParam(':date1', $date, PDO::PARAM_STR, 25);
        $sql->bindParam(':date2', $stop_date, PDO::PARAM_STR, 25);
    }

    if ($tabla === 'sensoresDif') {
        $date = date('Y-m-d H:i:s', strtotime($dato2));
        $stop_date = date('Y-m-d H:i:s', strtotime($dato2 . ' +1 day'));

        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 25);
        $sql->bindParam(':date1', $date, PDO::PARAM_STR, 25);
        $sql->bindParam(':date2', $stop_date, PDO::PARAM_STR, 25);
    }
    if ($tabla === 'usuarios') {
    }
    if ($tabla === 'usuariosC') {
        $sql->bindParam(':mail', $dato2, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'usuariosID') {
        $sql->bindParam(':mail', $dato2, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'usuariosLVL') {
        $sql->bindParam(':userID', $idPlot, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'parcelas') {
    }
    if ($tabla === 'parcelasU') {
        $sql->bindParam(':idUser', $dato2, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'parcelasID') {
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'parcelasUltimaLectura') {
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'parcelasINE') {
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'lecturasDevices') {
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'tipoSensores') {
    }
    if ($tabla === 'camaras') {
        $date = date('Y-m-d H:i:s', strtotime($dato2));
        $stop_date = date('Y-m-d H:i:s', strtotime($dato2 . ' +1 day'));

        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
        $sql->bindParam(':date1', $date, PDO::PARAM_STR, 25);
        $sql->bindParam(':date2', $stop_date, PDO::PARAM_STR, 25);
    }
    if ($tabla === 'temporizador') {
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'temporizadorInteval') {
        //$sql->bindParam(':inteval',$dato2,PDO::PARAM_STR, 60);
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
        $sql->bindParam(':idSensor', $dato3, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'temporizadorValor') {
        $sql->bindParam(':idSensor', $dato3, PDO::PARAM_STR, 60);
    }
    if ($tabla === 'estadoValvulas') {
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
    }
    if (
        $tabla === 'estadoValvulasValor' ||
        $tabla === 'estadoValvulasValorHora'
    ) {
        $sql->bindParam(':idPlot', $idPlot, PDO::PARAM_STR, 60);
        $sql->bindParam(':idSensor', $dato3, PDO::PARAM_STR, 60);
    }
    $sql->execute();
    $resultados = $sql->fetchAll();
    return $resultados;
}

function check_user($conn, $username)
{
    //funcion q comprueba si existe el usuario
    $result = leerBBDD($conn, 'usuariosC', '', $username, '', '');
    if (count($result) > 0) {
        return true;
    } else {
        //echo "no existe";
        return false;
    }
    //no se deber�a llegar nunca
    return true;
}

function check_pwd($password)
{
    //funcion para la fortaleza de la contrase�a
    //rellenar con las politicas de seguridad
    return true;
}

function check_mail($mail)
{
    //funcion para la fortaleza de la contrase�a
    //rellenar con las politicas de seguridad
    return true;
}

function add_user($conn, $user, $password, $mail)
{
    //nivel 0 = admin, nivel 1 = usuario
    $level = 1;
    insertarBBDD($conn, 'usuarios', $user, $level, $mail, $password, '');
}

function add_plot($conn, $plot, $location)
{
    //nivel 0 = admin, nivel 1 = usuario
    $level = 1;
    insertarBBDD($conn, 'parcelas', $plot, $location, '', '', '');
}

function check_plot_id($conn, $username)
{
    $result = leerBBDD($conn, 'parcelas', '', $username, '', '');
    if (count($result) > 0) {
        //echo "existe";
        return true;
    } else {
        //echo "no existe";
        return false;
    }
    //no se deber�a llegar nunca
    return true;
}

function list_sensors($conn, $plot)
{
}
?>
