<?
	require ("rutadb.php");
	$consult = mysql_query("Select * From seg_usuarios_matriz Where est=0 Limit 0,1",$conexion) or die("Problemas en seleccionar el reg:".mysql_error());

	$cont = mysql_num_rows($consult);
	if ($cont=='1') {

	while ($consulta=mysql_fetch_array($consult))
	{ 
		$codigo = $consulta['codigo'];
		$nombres_apellidos = $consulta['nombre']." ".$consulta['ape_pat']." ".$consulta['ape_mat'];
		$email = $consulta['mail'];
		$telefonos = $consulta['cell'];
		$tipo = "S";
		$acceso = "0";
		mysql_query("UPDATE seg_usuarios_matriz SET est = '1' WHERE codigo = $codigo");
		//ELIMINA REGISTRO INSERTADO PARA VACIAR LA TABLA
		$Eli = mysql_query("Delete From seg_usuarios_matriz Where codigo = $codigo",$conexion) or die("Problemas en la Limpieza:".mysql_error());
		
		//PROCESO DE INSERTANDO NUEVOS REGISTROS
		mysql_query("INSERT INTO `seg_usuarios` ( `codigo` , `nombres_apellidos` , `email` , `telefonos` , `tipo`, `acceso` )  VALUES ( '$codigo', '$nombres_apellidos', '$email', '$telefonos', '$tipo', '$acceso' )",$conexion) or die("Problemas en el select".mysql_error());
		//INSERTANDO PERMISOS
		$consultx = mysql_query("Select * From seg_usuarios Where acceso=0 Limit 0,1",$conexion) or die("Problemas en el select:".mysql_error());
		while ($consultax=mysql_fetch_array($consultx))
		{
			$idusuario = $consultax['idusuario'];
			mysql_query("INSERT INTO `seg_menu_usuarios` ( `idusuario` , `idmenu` )  VALUES ( '$idusuario', '6' )",$conexion) or die("Problemas en el select".mysql_error());
			mysql_query("INSERT INTO `seg_menu_usuarios` ( `idusuario` , `idmenu` )  VALUES ( '$idusuario', '7' )",$conexion) or die("Problemas en el select".mysql_error());
			mysql_query("UPDATE seg_usuarios SET acceso = '1' WHERE idusuario = $idusuario");
		}
	}
	} else {
		//Esto es para limpiar la tabla por completo en caso que se quede algun dato en la tabla de importacion seg_usuarios_matriz
		mysql_query("TRUNCATE TABLE seg_usuarios_matriz",$conexion);
		echo "PROCESO TERMINADO";
	}
?>