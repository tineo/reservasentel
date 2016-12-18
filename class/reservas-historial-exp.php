<?

include('../config.php');
include('../includes/util.php');
session_start();
$Utilitario = new Utilitario;
$fechaExportar = date("d-m-Y");
$Desde = $_REQUEST['Desde'];
$Hasta = $_REQUEST['Hasta'];
$Ubicacion = $_REQUEST['Ubicacion'];
$Piso = $_REQUEST['Piso'];
$Sala = $_REQUEST['Sala'];



$sql=" select us.codigo codigoUsuario, us.nombres_apellidos, r.codigo, CONCAT_WS(' ',u.nombre,'-','Piso',s.piso,'-',s.nombre) as UbicacionSalas, 
			  DATE_FORMAT(r.fecha_reserva,'%d/%m/%Y') FecReserva, r.horario_inicio, r.horario_final, DATE_FORMAT(r.fecha_registro,'%d/%m/%Y') FecRegistro
	    from salas s, reservasant r, ubicaciones u, seg_usuarios us
	   where u.idubicacion=s.idubicacion
		 AND r.idsala= s.idsala
		 AND us.idusuario = r.idusuario
		 AND r.estado='R' ";
				 
if($_SESSION["tipo"] != 'A'){
	$sql.=" and r.idusuario=".$_SESSION["idusuario"]."";
}
				
if(!empty($Desde)){
	$sql.=" AND DATE(r.fecha_reserva) BETWEEN '".$Utilitario->cambiaf_a_mysql($Desde)."' AND '".$Utilitario->cambiaf_a_mysql($Hasta)."'";
}
if(!empty($Ubicacion)){
	$sql.=" AND s.idubicacion=".$Ubicacion." ";
}
if(!empty($Piso)){
	$sql.=" AND s.piso=".$Piso." ";
}
if(!empty($Sala)){
	$sql.=" AND s.idsala=".$Sala." ";
}		

$sql .=" order by r.fecha_reserva desc";	

$Result=mysql_query($sql);

header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=Listado_$fechaExportar.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border=1> ";
echo "<tr> ";
echo 	"<th>C&oacute;digo Usuario</th> ";
echo 	"<th>Usuario</th> ";
echo 	"<th>C&oacute;digo Reserva</th> ";
echo 	"<th>Ubicaci&oacute;n</th> ";
echo 	"<th>Fecha Reserva</th> ";
echo 	"<th>Hora Inicio</th> ";
echo 	"<th>Hora Final</th> ";
echo 	"<th>Fecha Registro</th> ";
echo "</tr> ";
while($ArrFila=mysql_fetch_array($Result)){
	echo "<tr> ";
	echo 	"<td>&nbsp;".$ArrFila['codigoUsuario']."</td> ";
	echo 	"<td>".$Utilitario->mixed_to_latin1($ArrFila['nombres_apellidos'])."</td> ";
	echo 	"<td>&nbsp;".$ArrFila['codigo']."</td> ";
	echo 	"<td>".$Utilitario->mixed_to_latin1($ArrFila['UbicacionSalas'])."</td> ";
	echo 	"<td>&nbsp;".$ArrFila['FecReserva']."</td> ";
	echo 	"<td>&nbsp;".$ArrFila['horario_inicio']."</td> ";
	echo 	"<td>&nbsp;".$ArrFila['horario_final']."</td> ";
	echo 	"<td>&nbsp;".$ArrFila['FecRegistro']."</td> ";
	echo "</tr> ";
}
echo "</table> ";
?>