<?Php
//CODIGO ADICIONAL CREADO POR YOEL
	require ("config.php");
	$Eli = mysql_query("Delete From reservas WHERE idreserva='$_GET[idres]'",$cn) or die("Problemas en la Limpieza:".mysql_error());

header ("location: reservados.php");
?>