<?php 
/*$ruta = $_REQUEST['ruta'];
header("Pragma: no-cache"); 
header("Expires: 0"); 
header("Content-Transfer-Encoding: binary"); 
header("Content-Disposition: attachment; filename=$ruta"); 
header("Content-type: application/force-download");
echo $ruta;*/

$ruta = $_GET["ruta"];
$nomFile = $_GET["nomFile"];
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$nomFile\"\n");
$fp=fopen("$ruta", "r");
fpassthru($fp);


?>
