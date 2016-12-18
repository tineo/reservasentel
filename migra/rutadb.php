<?PHP 

$conexion=mysql_connect("localhost", "root", "root") or die("Problemas en la conexion");

mysql_select_db("salas",$conexion) or die("Problemas en la selecci&oacute;n de la base de datos");

?>