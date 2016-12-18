<?
  include("MasterPage.php");
  $MasterPage = new MasterPage();  
 ?>
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Reservas de Salas :: entel</title>
<?
 $MasterPage->MostrarScriptCss();  
?>
<?
	$HorasAntesReservado=FormatHora(EmailHorasAntesReservado);
	function FormatHora($Hora){
		$TextoMinutos='';
		list($HoraF, $MinutosF) = split(':', $Hora);
		if($HoraF != '0'){
			if($MinutosF != '00'){
				$TextoMinutos=' y '.$MinutosF.' minutos';
			}
			$TextoHora=(int)$HoraF>1?' horas':' hora';
			return (int)$HoraF.$TextoHora.$TextoMinutos;
		}else{
			return '30 minutos';
		}
	}
?>
<script type="text/javascript">
	var VerficandoFlag=false;
	$(function(){
		Reloj();
	});
	
	function Reloj() {
		// Obtiene la fecha actual
		var FechaLocal = new Date() ;	
		// Obtiene la hora
		var horas = FechaLocal.getHours() ;	
		// Obtiene los minutos
		var minutos = FechaLocal.getMinutes() ;	
		// Obtiene los segundos
		var segundos = FechaLocal.getSeconds() ;	
		// Si es menor o igual a 9 le concatena un 0
		if (horas <= 9) horas = "0" + horas;	
		// Si es menor o igual a 9 le concatena un 0
		if (minutos <= 9) minutos = "0" + minutos;	
		// Si es menor o igual a 9 le concatena un 0
		if (segundos <= 9) segundos = "0" + segundos;	
		// Asigna la hora actual a la caja de texto reloj
		$('#HoraActual').text(horas+":"+minutos+":"+segundos);
		var HoraPorEjecutar=0;
		if(parseInt(minutos,10)<=15 && parseInt(minutos,10)>=1){
			HoraPorEjecutar=(15-parseInt(minutos,10));
		}else if(parseInt(minutos,10)>45 || parseInt(minutos,10)==0){
			HoraPorEjecutar=(60-parseInt(minutos,10));
			if(parseInt(minutos,10)==0) HoraPorEjecutar=0;		
		}	
		$('#Minutos').text(HoraPorEjecutar);
		/*if($('#HoraActual').text()=='15:51:00' ||$('#HoraActual').text()=='05:10:00' || $('#HoraActual').text()=='08:10:00' || $('#HoraActual').text()=='11:10:00' || $('#HoraActual').text()=='14:10:00' || $('#HoraActual').text()=='17:10:00' || $('#HoraActual').text()=='20:10:00' || $('#HoraActual').text()=='23:10:00' || $('#HoraActual').text()=='02:10:00'){
			window.location.href='email-reservas.php';
			return;
		}*/
		if(HoraPorEjecutar==0 && VerficandoFlag==true){
			VerficandoFlag=false;
			$('#PorVerificando').hide();
			$('#Verificando').show();
			var params=$.param({command:'VerificarReserva',HM: horas+":"+minutos,TiempoAntes:'<? echo $HorasAntesReservado;?>'});
			callServer({data:params,url:'class/email-reservas.php'},function(d){window.location.href='email-reservas.php';},false);
		}
		if(HoraPorEjecutar>0){
			$('#PorVerificando').show();
			$('#Verificando').hide();
			VerficandoFlag=true;
		}else{
			$('#PorVerificando').hide();
			$('#Verificando').show();
		}
		// Cada segundo invoca a si mismo
		setTimeout("Reloj()",1000);
	}
</script>
</head>
<body>
<?
 $MasterPage->MostrarCabecera();
?>

<table width="910" border="0" align="center" cellpadding="0" cellspacing="0" id="tbInicio">
 <tr>
    <td width="25">&nbsp;</td>
</tr>
<tr>
<td height="50" align="left" valign="middle" bgcolor="#F2F2F2" colspan="4"><span class="tituloSERV">Alerta de Reservas</span></td>
</tr>
<tr>
<td height="25" align="left" valign="middle" bgcolor="#FFFFFF" colspan="4" id="tdReloj">
</td>
</tr>
<tr>
<td height="25" align="left" valign="middle" bgcolor="#FFFFFF" colspan="4">
<table border="0" cellpadding="0" cellspacing="5" width="540px" align="center">	
    <tr>
    	<td class="textopequeTITULO" valign="top" style="text-align:justify;">
        <img src="images/correo.jpg" alt="Alerta de reservas" width="123px" style="margin-left: 10px; float: right;">
        El sistema verificara cada 30 minutos si hay reservas que se realizaran en <strong><? echo $HorasAntesReservado;?></strong> antes que se realice la reserva,  se emitirá un correo electrónico informándole al usuario que tiene una reserva por realizarse. <br /><br />
        <strong class="textopequeTITULO">Fecha y Hora Actual: </strong><span class="textopequeTITULO" style="color:#E2520B; font-weight:bold"><? echo date("d/m/Y");?>&nbsp;&nbsp;&nbsp;</span><span class="textopequeTITULO" style="color:#E2520B; font-weight:bold" id="HoraActual"></span><br />
        <a id="PorVerificando">En <span style="color:#E2520B; font-weight:bold" id="Minutos">20</span> minutos el sistema estará verificando si hay reservas por realizarse.<br /><br /></a>
        <a style="color:#E2520B; font-weight:bold" id="Verificando">El sistema está verificando si hay reservas por realizarse.<br /><br /></a>
        
        <strong>Nota Importante:</strong> En la computadora donde se está ejecutando esta pantalla debe estar la <strong>hora peruana</strong>, para no tener ningún inconveniente en la hora que se realizo la reserva. 
        <p></p>
        </td>
    </tr>
    <tr>
    	<td height="50px"></td>
    </tr>
</table>
</td>
</tr>
</table>
<?
  $MasterPage->MostrarFooter();
?>
 </body>
</html> 
