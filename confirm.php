<?php
/**
 * Created by PhpStorm.
 * User: tineo
 * Date: 23/12/16
 * Time: 03:47 AM
 */
include_once 'config.php';
//SELECT * FROM reservas_notificaciones WHERE hash = '4ef06b13725b4338b80d72e9eb380417'




if(isset($_REQUEST['cc'])){
    $sqlcc0 = sprintf("SELECT * FROM reservas_notificaciones WHERE hash = '%s'",$_REQUEST['cc']);
    $results = mysql_query($sqlcc0) or mysql_error();
    echo $sqlcc0."<br/>";
    $count = mysql_num_rows($results);
    echo $count."<br/>";

    if($count>0) {
        //echo "cc";

        $sqlcc = sprintf("UPDATE reservas_notificaciones
                          SET state = 1
                          WHERE hash = '%s'", $_REQUEST['cc']);
        mysql_query($sqlcc);

        echo "Se confirmo tu reserva. Se cerrara la ventana en 5 segundos.";
    }else{
        echo "Enlace no valido o expirado. Se cerrara la ventana en 5 segundos.";
    }

}else{
    $sqlcd0 = sprintf("SELECT * FROM reservas_notificaciones WHERE hash = '%s'",$_REQUEST['cd']);
    $results = mysql_query($sqlcd0);
    $count = mysql_num_rows($results);

    if($count>0) {
        //echo "cc";

        $sqlcd = sprintf("UPDATE reservas_notificaciones
                          SET state = 1
                          WHERE hash = '%s'", $_REQUEST['cd']);
        mysql_query($sqlcd);

        echo "Se anulo tu reserva. Se cerrara la ventana en 5 segundos.";
    }else{
        echo "Enlace no valido o expirado. Se cerrara la ventana en 5 segundos.";
    }



}


echo '<script>setTimeout("window.close()", 5000);</script>';
