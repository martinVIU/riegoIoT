<?php

function form_show_datepicker(
    $date_readings,
    $idParcela = '',
    $destination = 'index.php',
    $page
) {
    echo '
 <form action="' .
        $destination .
        '" method="post" >
<div class="col-md-6">
<label for="data_query">Fecha de consulta :</label>
<input type="hidden" name="parcela" value="' .
        $idParcela .
        '" >
<input type="hidden" name="page" value="' .
        $page .
        '" >
<input type="date" name="date" id="date" class="form-control" value="' .
        $date_readings .
        '" onchange="this.form.submit()">

</div>
</form>';
}

function form_add_user($destination = 'index.php', $page = '1')
{
    echo '<form action="' .
        $destination .
        '" method="post">
  <label for="fname">Usuario</label>
  <input type="text" id="uname" name="uname"><br><br>
  <label for="lname">Contrase&ntilde;a</label>
  <input type="text" id="pwd" name="pwd"><br><br>
  <label for="mmail">Correo</label>
  <input type="text" id="mail" name="mail"><br><br>
  <input type="submit" value="A&ntilde;adir Usuario">
  <input type="hidden" name="page" value="' .
        $page .
        '" >
</form>';
}

function form_add_plot($destination = 'index.php')
{
    echo '
 <form action="' .
        $destination .
        '" method="post">
  <label for="fname">Id parcela</label>
  <input type="text" id="plotname" name="plotname"><br><br>
  <input type="submit" value="Submit">
</form>';
}

function process_POST_add_user($conn, $array_POST)
{
    //funcion que procesa el POST para a�adir usuario
    if (isset($array_POST['uname'])) {
        //comprobar el usuario
        if ($array_POST['uname'] != '') {
            if (check_user($conn, $array_POST['uname']) == false) {
                if (check_pwd($conn, $array_POST['pwd']) == true) {
                    if (check_mail($conn, $array_POST['pwd']) == true) {
                        add_user(
                            $conn,
                            $array_POST['uname'],
                            $array_POST['pwd'],
                            $array_POST['mail']
                        );
                        echo '<br>USUARIO A�ADIDO<br>';
                    } else {
                        echo 'correo no v�lido';
                    }
                } else {
                    echo 'contrase�a no v�lida';
                }
            } else {
                echo 'usuario no valido';
            }
        } else {
            echo 'No se permiten campos vacios';
        }
    }
}

?>
