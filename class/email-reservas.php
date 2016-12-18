<?
if(!empty($_POST["command"])){
	include_once('../config.php');//Si utiliza ajax es requerido importar la configuracion
	include_once("../includes/EnvioCorreo.php");
	include_once('../includes/util.php');
}


$Utilitario = new Utilitario;
$Alerta = new EmailReservas;

if($_POST["command"]=='VerificarReserva'){
	$Alerta->VerificarReserva();
}

class EmailReservas{	

	function VerificarReserva(){
		global $Utilitario;
		$Email = new EnvioEmail();
		$HoraMinutos=$Utilitario->SumarHoras($_POST['HM'],EmailHorasAntesReservado);
		$Result=mysql_query("select u.nombres_apellidos as Usuario, u.email,u.codigo,
									ub.nombre as Ubicacion,s.nombre as Sala, s.piso,
									r.codigo,r.fecha_reserva,r.horario_inicio,r.horario_final
							   from reservas r, seg_usuarios u,ubicaciones ub, salas s
							  where r.idusuario=u.idusuario
							    and r.idubicacion=ub.idubicacion
							    and r.idsala=s.idsala
							    and date(r.fecha_reserva)=date(now())
							    and r.horario_inicio = '".$HoraMinutos."'
							 order by r.fecha_reserva asc, r.horario_inicio asc");
		
		/*$Result=mysql_query("select u.nombres_apellidos as Usuario, u.email,u.codigo,
									ub.nombre as Ubicacion,s.nombre as Sala, s.piso,
									r.codigo,r.fecha_reserva,r.horario_inicio,r.horario_final
							   from reservas r, seg_usuarios u,ubicaciones ub, salas s
							  where r.idusuario=u.idusuario
							    and r.idubicacion=ub.idubicacion
							    and r.idsala=s.idsala
							    and date(r.fecha_reserva)=date('2011-11-28')
							    and r.horario_inicio = '".$HoraMinutos."'
							 order by r.fecha_reserva asc, r.horario_inicio asc");*/
		//echo $HoraMinutos;
		//$rr=false;
		
		//ESTO VA ABAJO MUESTRA EL TIEMPO ANTES DE LA RESERVA
		//Estimado/a: '.$Campo["Usuario"].'<br />
		//Les recordamos que en '.$_POST['TiempoAntes'].' iniciara su reunión en la sala<br /><br />

		while($Campo=mysql_fetch_array($Result)){
			$Contenido='Le recordamos que en 15 minutos iniciar&aacute; la reserva que realiz&oacute; en el sistema de administraci&oacute;n de salas.<br /><br />
						<table width="600px" border="0" bgcolor="#F0F0F0" cellpadding="3" cellspacing="1">
						  <tr>
							<td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>UBICACI&Oacute;N:</strong> </td>
							<td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>PISO/SALA</strong></td>
                            <td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>FECHA</strong></td>
                            <td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>H. INI.</strong></td>
                            <td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>H. FIN</strong></td>
						  </tr>
						  <tr>							
							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['Ubicacion'] .'</td>
                            <td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['piso'].' / '.$Campo['Sala'].'</td>
                            <td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Utilitario->cambiaf_a_normal($Campo['fecha_reserva']) .'</td>
                            <td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['horario_inicio'] .'</td>
                            <td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['horario_final'] .'</td>
						  </tr>						  				 
						</table> ';
			if(!empty($Campo["email"])){
				$Email->Enviar($Campo["email"],'Reserva por realizarse',$Contenido);
			}			
			
			//$d='<table width="600px" border="0" bgcolor="#F0F0F0" cellpadding="3" cellspacing="1">
//						  <tr>
//							<td width="140px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;"><strong>Ubicaci&oacute;n:</strong> </td>
//							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['Ubicacion'] .'</td>
//						  </tr>
//						  <tr>
//							<td width="140px" valign="top" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;"><strong>Piso:</strong> </td>
//							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['piso'] .'</td>
//						  </tr>
//						  <tr>
//							<td width="140px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;"><strong>Sala:</strong> </td>
//							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['Sala'] .'</td>
//						  </tr>
//						  <tr>
//							<td width="140px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;"><strong>C&oacute;digo:</strong> </td>
//							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['codigo'] .'</td>
//						  </tr>
//						  <tr>
//							<td width="140px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;"><strong>Fecha Reserva:</strong> </td>
//							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Utilitario->cambiaf_a_normal($Campo['fecha_reserva']) .'</td>
//						  </tr>					  
//						  <tr>
//							<td width="140px" valign="top" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;"><strong>Inicio:</strong> </td>
//							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['horario_inicio'] .'</td>
//						  </tr>  
//						  <tr>
//							<td width="140px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;"><strong>Final:</strong> </td>
//							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['horario_final'] .'</td>
//						  </tr>					 
//						</table>';
		}		
	}


	function notifyReserva(){
		global $Utilitario;
		$Email = new EnvioEmail();
		$HoraMinutos=$Utilitario->SumarHoras($_POST['HM'],EmailHorasAntesReservado);
		$Result=mysql_query("select u.nombres_apellidos as Usuario, u.email,u.codigo,
									ub.nombre as Ubicacion,s.nombre as Sala, s.piso,
									r.codigo,r.fecha_reserva,r.horario_inicio,r.horario_final
							   from reservas r, seg_usuarios u,ubicaciones ub, salas s
							  where r.idusuario=u.idusuario
							    and r.idubicacion=ub.idubicacion
							    and r.idsala=s.idsala
							    and date(r.fecha_reserva)=date(now())
							    and r.horario_inicio = '".$HoraMinutos."'
							 order by r.fecha_reserva asc, r.horario_inicio asc");

		while($Campo=mysql_fetch_array($Result)){
			$Contenido='Le recordamos que en 15 minutos iniciar&aacute; la reserva que realiz&oacute; en el sistema de administraci&oacute;n de salas.<br /><br />
						<table width="600px" border="0" bgcolor="#F0F0F0" cellpadding="3" cellspacing="1">
						  <tr>
							<td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>UBICACI&Oacute;N:</strong> </td>
							<td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>PISO/SALA</strong></td>
                            <td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>FECHA</strong></td>
                            <td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>H. INI.</strong></td>
                            <td width="120px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;text-align:center;"><strong>H. FIN</strong></td>
						  </tr>
						  <tr>							
							<td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['Ubicacion'] .'</td>
                            <td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['piso'].' / '.$Campo['Sala'].'</td>
                            <td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Utilitario->cambiaf_a_normal($Campo['fecha_reserva']) .'</td>
                            <td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['horario_inicio'] .'</td>
                            <td width="460px" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#333333;text-decoration:none;">'. $Campo['horario_final'] .'</td>
						  </tr>						  				 
						</table> ';
			if(!empty($Campo["email"])){
				$Email->Enviar("itsudatte01@gmail.com",'Reserva por realizarse',$Contenido);
			}

		}
	}
		
	
	
}




?>
