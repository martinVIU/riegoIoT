<?php
require_once './../../Model/funcionesBBDD.php';
require_once './../../Controller/comm_functions.php';

$idParcela = '';
$delimiter = '&';

if (isset($_GET['parcela'])) {
    if ($_GET['parcela'] != '') {
        $idParcela = $_GET['parcela'];
    }
}

$conn = createConnectionDB();

//0 = sensor no haga nada
//1 = env�e datos
$num_order = setOrder($conn, $idParcela, 2);

if ($idParcela != '') {
    orders_to_device($conn, $idParcela, $delimiter, $num_order);
}

?>
