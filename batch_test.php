<?php
/**
 * Created by PhpStorm.
 * User: tineo
 * Date: 23/12/16
 * Time: 01:17 AM
 */
$num_notify = 1;
$sended = 0;
$time_start = microtime(true);
$rustart = getrusage();

include_once 'config.php';

include_once 'testmail.php';

$sqle = "SELECT * FROM config_notify_emails";
$resemails = mysql_query($sqle);
$bcc = array();

while($f=mysql_fetch_array($resemails)){
    $bcc[] = $f["email"];
}
$sql00 = "SELECT * FROM config_notify";
$results00 = mysql_query($sql00);
$max_notify = mysql_num_rows($results00);


$sql0 = "SELECT * FROM config_notify 
WHERE before_after = 1 ORDER BY lapse_in_min DESC";
$results0 = mysql_query($sql0);

$lapses = array();

while($fields=mysql_fetch_array($results0)){
    //echo $fields['lapse_in_min']."<br/>";
    //echo $fields['before_after']."<br/>";
    //echo $fields['status']."<br/>";
    //echo "<br/>";

    //$sql = sprintf("SELECT idsala FROM salas_especiales WHERE idsala = %s", $idsala);
    $sql1 = sprintf("SELECT * FROM notificaciones WHERE (diff_min BETWEEN %s AND %s) AND state = 0 AND notify <= %s AND notify = %d",
        $fields['lapse_in_min'] - 2,
        $fields['lapse_in_min'] + 2,
        $max_notify,
        $num_notify -1 );
    //echo "<br/>";
    echo $sql1;
    echo "<br/>";

    $results1 = mysql_query($sql1);

    while($fields1=mysql_fetch_array($results1)){
        //echo "notify: ".$fields2['notify']."<br/><br/>";
        $notify = intval($fields1['notify'])+1;
        $hash = md5($fields1['idreserva'].$notify);
        $hasql = sprintf("UPDATE reservas_notificaciones SET notify = %d, hash = '%s' WHERE idreserva = %d",
            $notify,
            $hash,
            $fields1['idreserva']
        );
        mysql_query($hasql) or die(mysql_error());

        //echo $hasql."<br/>";

        //sendme($fields2['sala'],$fields2['piso'],$fields2['sede'],array($fields2['email']), $bcc);
        sendme($fields1['sala'],
            $fields1['piso'],
            $fields1['sede'],
            $hash,
            array("soporte@keinescorp.com"),
            $bcc);
        //echo $fields2['diff_min']."<br/>";
        //echo $fields2['hash']."<br/>";
        $sended++;
    }

    $num_notify++;
}

//echo "<br/>";
//echo "<br/>";
//echo "<br/>";

$sql = "SELECT * FROM config_notify 
WHERE before_after = 2 ORDER BY lapse_in_min ASC";
$results = mysql_query($sql);

$lapses = array();

while($fields=mysql_fetch_array($results)){
    //echo $fields['lapse_in_min']."<br/>";
    //echo $fields['before_after']."<br/>";
    //echo $fields['status']."<br/>";
    //echo "<br/>";

    //$sql = sprintf("SELECT idsala FROM salas_especiales WHERE idsala = %s", $idsala);
    $sql = sprintf("SELECT * FROM notificaciones WHERE (diff_min BETWEEN %s AND %s) AND state = 0  AND notify <= %s AND notify = %d ",
        ($fields['lapse_in_min']*-1) - 2,
        ($fields['lapse_in_min']*-1) + 2,
        $max_notify,
        $num_notify - 1 );
    echo $sql;
    echo "<br/>";

    //$sql = "SELECT * FROM notificaciones WHERE state = 0  AND notify <= %s ";

    //$emails = array('cesar@tineo.mobi', 'itsudatte01@gmail.com' => 'A name');
    $results = mysql_query($sql);
    //sleep(10);
    while($fields2=mysql_fetch_array($results)){
        //echo "notify: ".$fields2['notify']."<br/><br/>";
        $notify = intval($fields2['notify'])+1;
        $hash = md5($fields2['idreserva'].$notify);
        $hasql = sprintf("UPDATE reservas_notificaciones SET notify = %d, hash = '%s' WHERE idreserva = %d",
            $notify,
            $hash,
            $fields2['idreserva']
            );
        mysql_query($hasql) or die(mysql_error());

        //echo $hasql."<br/>";

        //sendme($fields2['sala'],$fields2['piso'],$fields2['sede'],array($fields2['email']), $bcc);
        sendme($fields2['sala'],
            $fields2['piso'],
            $fields2['sede'],
            $hash,
            array("soporte@keinescorp.com"),
            $bcc);
        //echo $fields2['diff_min']."<br/>";
        //echo $fields2['hash']."<br/>";
        $sended++;
    }

    $num_notify++;
}

echo "<br/>";
echo "<br/>";
echo "<br/>";



$count = mysql_num_rows($results);
//return ($count>0)?true:false;

$fichero = 'notified.txt';


function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
    -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}

$ru = getrusage();
$time_end = microtime(true);

$message = "[". date('d/m/Y H:i:s')."] ";
$message .=  " P: " . rutime($ru, $rustart, "utime") .
    " ms - ";
$message .= "S: " . rutime($ru, $rustart, "stime") .
    " ms - ";
$message .= "T: " . intval((($time_end - $time_start)*1000)) .
    " ms \n";

if($sended >0) file_put_contents($fichero, $message, FILE_APPEND | LOCK_EX);

echo $message;