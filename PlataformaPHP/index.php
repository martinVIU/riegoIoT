<?php

require_once('./Model/funcionesBBDD.php');
require_once('./Controller/functions.php');
require_once('./View/functions.php');
include_once('./Controller/meteogalicia/leyenda.php');
require_once('./Controller/meteogalicia/municipios.php');
require_once('./Controller/meteogalicia/functions.php');
require_once('./Controller/aemet/functions.php');
include_once('./Controller/aemet/apikey.php');
include_once('./View/html/header.php');
include_once('./View/html/footer.php');

$logged = -1;
$conn = createConnectionDB();

function checkLogin($conn,$mail,$pass){
    $result = leerBBDD($conn,'checklogin',$mail,$pass,'','');
    if (count($result)>0){
        return $result[0]['level'];
    }else{
        return -1;
    }
}

$cookie_name = "RiegoIoT";
$landing_page="index.php";

if(isset($_POST['loginuser'])){
    $logged = checkLogin($conn,$_POST['loginuser'],$_POST['loginpass']);
    
    $temp = leerBBDD($conn,'usuariosID','',$_POST['loginuser'],'','') ;
    if (count($temp)>0){
    $userID = $temp[0]['id'];
    if ( $logged > -1){
        //hay login, establecer cookie
        $cookie_value = leerBBDD($conn,'usuariosID','',$_POST['loginuser'],'','') ;
        setcookie($cookie_name, $cookie_value[0]['id'], time() + (86400 * 30), "/"); // 86400 = 1 day
        $temp = leerBBDD($conn,'usuariosLVL',$cookie_value[0]['id'],'','','');
        $user_level = $temp[0]['level'];
        write_header($user_level,$logged);
    }else{
        //no hay cookie
        write_header($user_level,$logged);
        screenLogin($landing_page);
        exit();
    }
    }
}

if(!isset($_COOKIE[$cookie_name]) && $logged <0)  {
  //echo "Cookie named '" . $cookie_name . "' is not set!";
  write_header(-1,-1);
  screenLogin($landing_page);
  exit();
} else{
    if ($logged<0){
        $userID = $_COOKIE[$cookie_name];
        $temp = leerBBDD($conn,'usuariosLVL',$_COOKIE[$cookie_name],'','','');
        $user_level = $temp[0]['level'];
        $logged = $user_level;    
    }
    
}
if (count($temp)>0){$user_level = $temp[0]['level'];}else{$user_level = -1;}
unset($temp);

if(isset($_POST)){ $array_POST=$_POST;}else{$array_POST=[];}

if (isset($_GET['page'])){
    //echo "pagina es _".$_GET['page']."__";
    if ($_GET['page'] == 0){
        //pantalla asignar parcela
        $page=0;
    }
    if ($_GET['page'] == 1){
        //pantalla añadir usuario
        $page=1;
    }
    if ($_GET['page'] == 2){
        //pantalla asignar CP a parcela
        $page=2;
    }
    if ($_GET['page'] == 3){
        //mostrar tabla lecturas
        $page=3;
    }
    if ($_GET['page'] == 4){
        //mostrar tabla sensores
        $page=4;
    }
    if ($_GET['page'] == 5){
        //mostrar tabla camaras
        $page=5;
    }
    if ($_GET['page'] == 6){
        //mostrar programador
        $page=6;
    }
    if ($_GET['page'] == 7){
        //mostrar y modificar estado valvulas
        $page=7;
    }
    if ($_GET['page'] == 8){
        //logout
        $page=8;
    }
}
if(isset($_POST['page'])){ $page=(int)$_POST['page'];}
if (!isset($page)){$page=3;}

if ($logged > -1){
    if ($page != 8){write_header($user_level,0);}else{write_header($user_level,-1);}
$array_usuarios=leerBBDD($conn,'usuarios','','','','');
    
if ($user_level == 0){    
    if ($page == 0){
        //pantalla asignar parcela
        echo "<center>";
        screen_show_assign_plots($conn,$array_usuarios,$array_POST,$page,$landing_page);
        echo "</center>";
    }
    if ($page == 1){
        echo "<center>";
        //pantalla añadir usuario
        screen_show_screen_add_user($conn,$array_POST);
        echo "</center>";
    }
    if ($page == 2){
        echo "<center>";
        //pantalla asignar CP a parcela
        screen_show_add_postal_code_to_plot($conn,$array_POST,$page,$landing_page);
        echo "</center>";
    }
}
if ($user_level > -1){
    if ($page == 3){
        echo "<center>";
        //mostrar tabla lecturas
        screen_show_table_readings($conn, $array_POST,$userID,$page,$landing_page);
        echo "</center>";
    }
    if ($page == 4){
        echo "<center>";
        //mostrar tabla sensores
        screen_show_table_devices($conn, $array_POST,$userID,$page,$landing_page);
        echo "</center>";
    }
    if ($page == 5){
        echo "<center>";
        //mostrar tabla camaras
        screen_show_table_cameras($conn, $array_POST,$userID,"",$page,$landing_page);
        echo "</center>";
    }
    if ($page == 6){
        echo "<center>";
        //mostrar programador
        screen_show_timetable_irrigation($conn, $array_POST,$userID,$page,$meteogalicia_prediccion_leyenda,$meteogalicia_prediccion_viento,$idConcello,$api_key,$landing_page);
        echo "</center>";
    }
    if ($page == 7){
        echo "<center>";
        //mostrar y modificar estado valvulas
        screen_show_valves($conn, $array_POST,$userID,$page,$landing_page);
        echo "</center>";
    }
    if ($page == 8){
        if (isset($_COOKIE[$cookie_name])) {
            unset($_COOKIE[$cookie_name]); 
            setcookie($cookie_name, null, -1, '/');
            write_header($user_level,-1);
            screenLogin($landing_page);
            $logged = -1;
            exit();
        }
    }
}


unset($_POST);

echo "</div>";

if ($logged>-1){
    menu_circle($user_level,$landing_page);    
}


//TEMP
//echo "cargar miniaturas del directorio. Generar miniaturas al procesar foto recibida y colocar donde corresponda con el nombre fecha o nombre al azar.";
//echo "imagen es link a foto grande"; 

//ATRIBUIR AUTORES";
/*
grifo 
https://www.flaticon.com/free-icon/faucet_1166424
timer
https://www.flaticon.com/free-icon/timer_684270
camera
https://www.flaticon.com/free-icon/camera_3392000
drop
https://www.freepik.com/free-icon/raindrop_14129430.htm
ruler
https://www.iconbolt.com/iconsets/font-awesome-solid/ruler
location
https://uxwing.com/land-location-icon/
plot
https://freesvg.org/vector-clip-art-of-land-parcel-icon
sample user sin modificar
https://commons.wikimedia.org/wiki/File:Sample_User_Icon.png
*/

//print_r($array_POST);
echo "</body></html>";
}
?>