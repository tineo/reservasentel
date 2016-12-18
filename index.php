<?
session_start();
include('config.php');
$Mensaje='';
if(!empty($_REQUEST['p'])){
	$codigo=intval($_REQUEST['p']);
	if($codigo>0){
		$query = sprintf(" SELECT nombres_apellidos nombres, idusuario, tipo
						   FROM seg_usuarios WHERE codigo='%s' and acceso= 1 ",
				 mysql_real_escape_string($codigo)); 	  
		$result=mysql_query($query);
		if(mysql_num_rows($result)){		
			$row=mysql_fetch_assoc($result);		
			$_SESSION["idusuario"]=$row["idusuario"];
			$_SESSION["nomcompleto"]=$row["nombres"];
			$_SESSION["tipo"]=$row["tipo"];
			mysql_free_result($result);
			
			//*************************** Nuevo Requerimiento, Migrar a una tabla historial (reservasant) las reservas pasadas un dia antes de la fecha actual.******************************
			//*** Fernando Torres -> 24-10-2012
			
			// Verificar 
			$QUERY_RESERVADOS = "
					select idreserva, idubicacion, idsala, idevento, idusuario, codigo, fecha_reserva, horario_inicio, horario_final, asistentes, estado, fecha_registro, reserva_especial, idespecial, idadmin    
					 from reservas r
					where r.estado='R'
					  and date_format(r.fecha_reserva,'%Y-%m-%d') <= date_format(DATE_SUB(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')
					 order by r.fecha_reserva desc 
			";
			$ResultReservado = mysql_query($QUERY_RESERVADOS);
			while($Campo = mysql_fetch_array($ResultReservado)){
				//Inserta a la tabla de historial
				$Query_Insert = "
				insert into reservasant(idreserva, idubicacion, idsala, idevento, idusuario, codigo, fecha_reserva, horario_inicio, horario_final, asistentes, estado, fecha_registro, reserva_especial, idespecial, idadmin) 
				values(".$Campo['idreserva'].",".$Campo['idubicacion'].",".$Campo['idsala'].",".$Campo['idevento'].",".$Campo['idusuario'].",'".$Campo['codigo']."','".$Campo['fecha_reserva']."','".$Campo['horario_inicio']."',
				       '".$Campo['horario_final']."','".$Campo['asistentes']."','".$Campo['estado']."','".$Campo['fecha_registro']."','".$Campo['reserva_especial']."','".$Campo['idespecial']."','".$Campo['idadmin']."')
				";
				mysql_query($Query_Insert);
				
				// Eliminar los reservados
				$Query_Delete = " delete from reservas where idreserva = ".$Campo['idreserva'];
				mysql_query($Query_Delete);
				
			}
			
			//*************************** FIN  ******************************
			
			
			header("location: inicio.php");
		}else{
			$Mensaje='Acceso denegado, contáctese con el administrador.';
		}
	}else{
		$Mensaje='Los parámetros de envío no son validos, por favor verificar.';
	}
}else{
	$Mensaje='Los parámetros de envío no son validos, por favor verificar.';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Reservas de Salas :: entel</title>
<link href="css/estilos.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8">
<!--<script type="text/javascript" src="js/JQuery/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/JQuery/UI_1.7/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/JQuery/jquery.blockUI.js"></script>
<script type="text/javascript" src="js/global.js"></script>-->
<script type="text/javascript">
/*function IniciarSesion(i){
	params = $.param({command:"Login",i:i});
	callServer({data:params,url:'class/seguridad.php'},function(d){		
		if($.trim(d)=='1'){
			window.location.href='inicio.php';
		}else{
			alert('Acceso denegado, contáctese con el administrador.');
		}
		$.unblockUI();
	});
}*/
</script>
</head>
<body>
<table width="550" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>    
    <td width="42" height="25">&nbsp;</td>
  </tr>
  <tr>
    <td height="258" colspan="2" valign="top">
     <table width="100%" border="1" cellpadding="0" cellspacing="0" background="images/_back.png">
      <tr>
        <td height="135" colspan="3" valign="top">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="48" height="13" style="width: 36px"></td>
                <td width="33" style="width: 18px"></td>
                <td width="425" style="width: 320px"></td>
                <td width="40" style="width: 28px"></td>
            </tr>
          <tr>
            <td height="122" style="width: 36px">&nbsp;</td>
                <td valign="top" style="width: 18px">
                </td>
                <td valign="top" style="width: 320px" class="textopeque"><table width="100%" border="0" cellpadding="3" cellspacing="0">
                  <tr>
                    <td height="2" style="width: 2px" colspan="2">
                     <img src="images/logo.png" width="366" height="122" border="0" />                    </td>         
                  </tr>
                  <tr>
                    <td height="19" style="width: 1px">&nbsp;</td>
                    <td align="center" valign="middle" style="width: 216px"><strong> Sistema de Gestión de Reservas de Salas</strong></td>
                  </tr>
                   <tr>
                    <td height="19" style="width: 1px">&nbsp;</td>
                    <td align="left" valign="middle" style="width: 216px; height:35px;"><strong><? echo $Mensaje;?></strong> </td>
                  </tr>
                 
                </table>
                </td>
                <td style="width: 28px">&nbsp;</td>
              </tr>
              <tr>
            <td height="50px" style="width: 36px"></td>
                <td style="width: 18px"></td>
                <td style="width: 320px"></td>
                <td style="width: 28px"></td>
            </tr>
          </table>
          </td>
        </tr>
      </table>
     </td> 
 </tr>     
</table>

 </body>
</html> 
