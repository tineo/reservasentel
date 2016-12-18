<?
  include("class/configuracion.php");
  include("MasterPage.php");
  $MaxMes=5;
  $MaxDias=30;
  $MaxHoras=24;
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
<link rel="stylesheet" href="js/JQuery/css/jquery.tabs.css" type="text/css">
<link rel="stylesheet" href="js/JQuery/css/jquery.tabs-ie.css" type="text/css" media="projection, screen">
<style type="text/css">
/*Crear en un archivo css, es para el calendario*/
.altn  {
	font-family : "Century Gothic";
	font-size : 9pt;
	color: #ffffff;
	background-color: #666666;
}

.tit  {
	font-family : "Century Gothic";
	font-size : 10pt;
	color: #ffffff;
	background-color: #333333;
	font-weight: bold;
}
.feriado{
	font-family : "Century Gothic";
	background-color:	#ff0000;
	color:	#FFFFFF;
	font-weight: bold;
	text-align:	center;
	}
	
.laboral{
	background-color: #FFFFFF;
}
	
.laboralLink{
	font-family : "Century Gothic";
	color:	#666666;
	font-weight: normal;
	text-align:	center;
}
.laboralLink:hover{
	font-family : "Century Gothic";
	color:	#FF0000;
	font-weight: bold;
	text-align:	center;
}
	
.fertivo{
	background-color: #ff0000;
}

.fertivoLink{
	font-family : "Century Gothic";
	color:	#FFFFFF;
	font-weight: bold;
	text-align:	center;
}
.fertivoLink:hover{
	font-family : "Century Gothic";
	color:	#333333;
	font-weight: bold;
	text-align:	center;
}
.hoy{
	background-color: #D7E9FF;
}

.hoyLink{
	font-family : verdana,arial,helvetica;
	color:	#559DFF;
	font-weight: bold;
	text-align:	center;
}
.hoyLink:hover{
	font-family : verdana,arial,helvetica;
	color:	#559DFF;
	font-weight: bold;
	text-align:	center;
}


</style>
<script src="js/JQuery/jquery.tabs.pack.js" type="text/javascript"></script>
<script src="js/JQuery/ControlsFormat.js" type="text/javascript"></script>
<script type="text/javascript">
var r='class/configuracion.php';
//Configurar parametro
var MaxMes=<? echo $MaxMes;?>;
var MaxDias=<? echo $MaxDias;?>;
var MaxHoras=<? echo $MaxHoras;?>;
var idrecurrente,txtDefinicion,txtMes,txtDia;
var idhoradia,txtHora,txtDefinicionHoras;
var PopUpRecurente,PopUpHoras;
var ddlSemanas,txtHorasSemanas,ddlHorasResevas,ddlEstado;
$(function(){
	$('#TabsConfiguracion').tabs().show('fast');	
	$('.integer').format({precision:0,autofix:true});
	//Recurrentes
	PopUpRecurente=new Dialogo("PopUpRecurentes",{width:470,title:'Reservas recurrentes de salas',buttons:{'Cancelar':function(){PopUpRecurente.close();},"Guardar":GuardarRecurente}});
	txtDefinicion=$('#txtDefinicion')[0];
	txtMes=$('#txtMes')[0];
	txtDia=$('#txtDia')[0];
	$('#txtDefinicion').keypress(function(e){if(e.which == 13){return false;}});	
	$('#txtMes').keyup(function(e){var value=parseInt($.trim($(this).val()));if(value<1||value>MaxMes)$(this).val('');});
	$('#txtDia').keyup(function(e){var value=parseInt($.trim($(this).val()));if(value<1||value>MaxDias)$(this).val('');});
	//Horas Por Día
	PopUpHoras=new Dialogo("PopUpHorasPorDia",{width:470,title:'Máximo de horas por día',buttons:{'Cancelar':function(){PopUpHoras.close();},"Guardar":GuardarHoras}});
	txtDefinicionHoras=$('#txtDefinicionHoras')[0];
	txtHora=$('#txtHora')[0];
	$('#txtDefinicionHoras').keypress(function(e){if(e.which == 13){return false;}});
	$('#txtHora').keyup(function(e){var value=parseInt($.trim($(this).val()));if(value<1||value>MaxHoras)$(this).val('');});
	
	//Horas por reserva
	ddlSemanas=$('#ddlSemanas')[0];
	txtHorasSemanas=$('#txtHorasSemanas')[0];
	ddlHorasResevas=$('#ddlHorasResevas')[0];
	ddlEstado=$('#ddlEstado')[0];
});

function NuevoRecurente(){
	Loading();
	idrecurrente=0;
	txtDefinicion.value='';
	txtMes.value='';
	txtDia.value='';
	CantidadLetras($('#txtDefinicion'),200,'_contador');
	PopUpRecurente.open();
	$.unblockUI();
}

function GuardarRecurente(){	
	if($.trim(txtMes.value)==''){alert('Por favor ingrese el máximo en meses.');txtMes.focus();return;}
	if($.trim(txtDia.value)==''){alert("Por favor ingrese el máximo en días.");txtDia.focus();return;}
	if($.trim(txtDefinicion.value)==''){alert('Por favor ingrese la definición.');txtDefinicion.focus();return;}
	CambioMesDia='NO';
	if($('#txtMes').attr('DataMesActual')!=$.trim(txtMes.value)||$('#txtDia').attr('DataDiaActual')!=$.trim(txtDia.value)) CambioMesDia='SI';
	PopUpRecurente.close();
	params=$.param({command:'GuardarRecurrente',
					idrecurrente:idrecurrente,
					mes:$.trim(txtMes.value),
					dia:$.trim(txtDia.value),
					definicion:$.trim(txtDefinicion.value),
					CambioMesDia:CambioMesDia			
				  });
	callServer({data:params,url:r},function(d){	
		if($.trim(d)=='Existe'){			
			alert('Los datos ingresados ya existe como parametro, por favor verificar.');
			PopUpRecurente.open();			
		}else{
			$('#td_ListadoRecurente').html(d);
			alert('Los datos ingresados fueron guardado correctamente.');			
		}
		$.unblockUI();		
	});
}

function EditarRecurrente(id){
	idrecurrente=id;
	params=$.param({command:"EditarRecurrente",idrecurrente:id});
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);		
		txtMes.value=j.mes;
		$('#txtMes').attr('DataMesActual',j.mes)
		txtDia.value=j.dia;
		$('#txtDia').attr('DataDiaActual',j.dia)
		txtDefinicion.value=j.definicion;
		CantidadLetras($('#txtDefinicion'),200,'_contador');		
		PopUpRecurente.open();
		$.unblockUI();
	});
}

function ActivarRecurrente(id){
	params=$.param({command:"ActivarRecurrente",id:id});
	callServer({data:params,url:r},function(d){		
		$('#td_ListadoRecurente').html(d);
		$.unblockUI();
	});
}

function EliminarRecurrente(id){
	if(!confirm("Confirma si desea eliminar el registro seleccionado")) return;
	params=$.param({command:"EliminarRecurrente",id:id});
	callServer({data:params,url:r},function(d){		
		$('#td_ListadoRecurente').html(d);
		$.unblockUI();
	});
}

//Tab Horas Por Dia
function NuevaHoras(){
	Loading();
	idhoradia=0;
	txtDefinicionHoras.value='';
	txtHora.value='';
	CantidadLetras($('#txtDefinicionHoras'),200,'_contadorHoras');
	PopUpHoras.open();
	$.unblockUI();
}

function GuardarHoras(){
	if($.trim(txtHora.value)==''){alert("Por favor ingrese la hora por día.");txtHora.focus();return;}
	if($.trim(txtDefinicionHoras.value)==''){alert('Por favor ingrese la definición.');txtDefinicionHoras.focus();return;}
	CambioHora='NO';
	if($('#txtHora').attr('DataHoraActual')!=$.trim(txtHora.value)) CambioHora='SI';
	PopUpHoras.close();
	params=$.param({command:'GuardarHoras',
					idhoradia:idhoradia,
					hora:$.trim(txtHora.value),
					definicion:$.trim(txtDefinicionHoras.value),
					CambioHora:CambioHora			
				  });
	callServer({data:params,url:r},function(d){	
		if($.trim(d)=='Existe'){
			alert('Los datos ingresados ya existe como parametro, por favor verificar.');
			PopUpHoras.open();			
		}else{
			$('#td_ListadoHoras').html(d);
			alert('Los datos ingresados fueron guardado correctamente.');			
		}
		$.unblockUI();		
	});
}

function EditarHoras(id){
	idhoradia=id;
	params=$.param({command:"EditarHoras",idhoradia:id});
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);
		txtHora.value=j.hora;
		$('#txtHora').attr('DataHoraActual',j.hora)
		txtDefinicionHoras.value=j.definicion;
		CantidadLetras($('#txtDefinicionHoras'),200,'_contadorHoras');		
		PopUpHoras.open();
		$.unblockUI();
	});
}

function ActivarHoras(id){
	params=$.param({command:"ActivarHoras",id:id});
	callServer({data:params,url:r},function(d){		
		$('#td_ListadoHoras').html(d);
		$.unblockUI();
	});
}

function EliminarHoras(id){
	if(!confirm("Confirma si desea eliminar el registro seleccionado")) return;
	params=$.param({command:"EliminarHoras",id:id});
	callServer({data:params,url:r},function(d){		
		$('#td_ListadoHoras').html(d);
		$.unblockUI();
	});
}

//Calendario Festivo
function CargarCalendario(dia,nuevo_mes,nuevo_anio){
	params=$.param({command:"CargarCalendario",dia:dia,nuevo_mes:nuevo_mes,nuevo_anio:nuevo_anio});
	callServer({data:params,url:r},function(d){		
		$('#td_Calendario').html(d);
		$.unblockUI();
	});
}

function DiaFestivo(Fecha,Tipo,FechaPasado){
	if(FechaPasado==1) return;//Si la fecha es anterior que la actual
	if(Tipo=='L'){
		if(!confirm('Confirmar si la fecha: '+Fecha+' es un día laborable')) return;
	}else if(Tipo=='F'){
		if(!confirm('Confirmar si la fecha: '+Fecha+'  es un día no laborable')) return;
	}
	params=$.param({command:"FechasFestivos",Fecha:Fecha,Tipo:Tipo});
	callServer({data:params,url:r},function(d){		
		$('#td_Calendario').html(d);
		$.unblockUI();
	});
}

//Cantidad de resevas por semana
function GuardarReservasSemana(){
	//ddlSemanas,txtHorasSemanas,ddlHorasResevas,ddlEstado
	if(ddlEstado.value==1){
		if($.trim(txtHorasSemanas.value)==''){alert('Es requerido ingresar las hoas por semana'); txtHorasSemanas.focus(); return;}
		if($.trim(ddlHorasResevas.value)==''){alert('Es requerido ingresar las hoas por reserva'); ddlHorasResevas.focus(); return;}
	}
	params=$.param({command:"ReservasPorSemana",
					Semanas:ddlSemanas.value,
					HorasSemanas:$.trim(txtHorasSemanas.value),
					HorasResevas:$.trim(ddlHorasResevas.value),
					Estado:ddlEstado.value
				  });
	callServer({data:params,url:r},function(d){
		alert('Los datos fueron guardado correctamente.');
		$.unblockUI();
	});
}

//Cantidad de Meses en el calendario
function CantidadMesesAdelante(Mes){	
	params=$.param({command:"CantidadMesesAdelante",Mes:Mes});
	callServer({data:params,url:r},function(d){
		alert('El parámetro fue guardado correctamente.');
		$.unblockUI();
	});
}

//Este parametro es para enviar email, en cuantas horas antes que se realice la reserva
function EmailHorasAntes(Hora){	
	params=$.param({command:"EmailHorasAntes",Hora:Hora});
	callServer({data:params,url:r},function(d){
		alert('El parámetro fue guardado correctamente.');
		$.unblockUI();
	});
}
</script>
</head>
<body>
<?
 $MasterPage->MostrarCabecera();
 $Configuracion=new ModuloAdmin;
?>
<!--Contenido Inicio-->
<table width="910" border="0" align="center" cellpadding="0" cellspacing="0" id="tbInicio">
<tr>
    <td>&nbsp;</td>
</tr>
<tr>
<td width="910" height="50" align="left" valign="middle" bgcolor="#F2F2F2"><span class="tituloSERV"><? echo $TituloPaginaActual;?></span></td>
</tr>
<tr>
  <td height="20px"></td>
</tr>
<tr>
 <td align="left">
    <div id="TabsConfiguracion" style="display:none;">
     <ul>
      <li><a href="#ConteRecurrentes"><span>Reservas recurrentes de salas</span></a></li>
      <li><a href="#ConteHoras"><span>Máximo de horas por día</span></a></li>
      <li><a href="#ConteFechas"><span>Calendario días festivos</span></a></li>
      <li><a href="#ReservasAcumuladas"><span>Reservas Acumuladas</span></a></li>
      <li><a href="#ConteOtros"><span>Otros par&aacute;metros</span></a></li>
     </ul>
      <div id="ConteRecurrentes">
       <table width="870" border="0" align="center" cellpadding="0" cellspacing="0">
         <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td height="35" ><div align="right"><input name="button" type="button" class="FRM" value="Nuevo Recurente" onclick="NuevoRecurente();" /></div></td>             
        </tr>
        <tr>
          <td id="td_ListadoRecurente">
          <?
          	$Configuracion->ListarRecurrente(0);
		  ?>
          </td>             
        </tr>
       </table>      
      </div>
      <div id="ConteHoras">
       <table width="870" border="0" align="center" cellpadding="0" cellspacing="0">
         <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td height="35" ><div align="right"><input name="button" type="button" class="FRM" value="Nueva Hora por día" onclick="NuevaHoras();" /></div></td>             
        </tr>
        <tr>
          <td id="td_ListadoHoras">
          <?
          	$Configuracion->ListarHoras(0);
		  ?>
          </td>             
        </tr>
       </table>
      </div>
      <div id="ConteFechas">
       <table width="300" border="0" align="center" cellpadding="0" cellspacing="0">
         <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td height="35" class="textopeque" bgcolor="#F2F2F2" align="center"><strong>FERIADO NO LABORABLE</strong></td>             
        </tr>
        <tr>
          <td id="td_Calendario" align="center">
          <?
          	$Configuracion->CargarCalendario('','','');
		  ?>
          </td>             
        </tr>
       </table>
      </div>
      <div id="ReservasAcumuladas">
        <?
        	$ResultReservas=mysql_query('select semana,horas_semana,horas_reserva,estado from config_reserva_semana');
			$Campo=mysql_fetch_array($ResultReservas);
			$semana=$Campo['semana'];
			$estado=$Campo['estado'];
			$horas_reserva=$Campo['horas_reserva']
		?>
       <table width="100%" height="100%" border="0" align="center" cellpadding="0" cellspacing="5">
         <tr>
          <td colspan="2">&nbsp;</td>
        </tr>        
        <tr>
          <td align="left" colspan="2">
          En esta opción se podrá definir la cantidad de reservas en horas por semanas, y el máximo de horas por reserva. Asimismo se podrá habilitar o deshabilitar esta regla.
          </td>             
        </tr>
        <tr>
          <td colspan="2" height="5px"></td>
        </tr>
        <tr>
          <td width="20%"> Semana:</td>
          <td width="80%">
            <select id="ddlSemanas" class="textopequeTITULO">
              <option value="1" <? echo $semana==1?'selected="selected"':''?>>1 Semana</option>
              <option value="2" <? echo $semana==2?'selected="selected"':''?>>2 Semanas</option>
              <option value="3" <? echo $semana==3?'selected="selected"':''?>>3 Semanas</option>
              <option value="4" <? echo $semana==4?'selected="selected"':''?>>4 Semanas</option>
            </select>
          </td>
        </tr>
        <tr>
          <td> Horas semana:</td>
          <td>
           <input type="text" id="txtHorasSemanas" maxlength="3" style="width:40px" class="textopequeTITULO integer" value="<? echo $Campo['horas_semana'];?>" />
          </td>
        </tr>
        <tr>
          <td> Horas por reserva:</td>
          <td>          
           <select id="ddlHorasResevas" class="textopequeTITULO">
              <option value="1:00" <? echo $horas_reserva=='1:00'?'selected="selected"':''?>>1 hora</option>
              <option value="1:30" <? echo $horas_reserva=='1:30'?'selected="selected"':''?>>1 hora y 30 minutos</option>
              <option value="2:00" <? echo $horas_reserva=='2:00'?'selected="selected"':''?>>2 horas</option>
              <option value="2:30" <? echo $horas_reserva=='2:30'?'selected="selected"':''?>>2 horas y 30 minutos</option>
              <option value="3:00" <? echo $horas_reserva=='3:00'?'selected="selected"':''?>>3 horas</option>
              <option value="3:30" <? echo $horas_reserva=='3:30'?'selected="selected"':''?>>3 horas y 30 minutos</option>
              <option value="4:00" <? echo $horas_reserva=='4:00'?'selected="selected"':''?>>4 horas</option>
              <option value="4:30" <? echo $horas_reserva=='4:30'?'selected="selected"':''?>>4 horas y 30 minutos</option>
              <option value="5:00" <? echo $horas_reserva=='5:00'?'selected="selected"':''?>>5 horas</option>
              <option value="5:30" <? echo $horas_reserva=='5:30'?'selected="selected"':''?>>5 horas y 30 minutos</option>
              <option value="6:00" <? echo $horas_reserva=='6:00'?'selected="selected"':''?>>6 horas</option>
              <option value="6:30" <? echo $horas_reserva=='6:30'?'selected="selected"':''?>>6 horas y 30 minutos</option>
              <option value="7:00" <? echo $horas_reserva=='7:00'?'selected="selected"':''?>>7 horas</option>
              <option value="7:30" <? echo $horas_reserva=='7:30'?'selected="selected"':''?>>7 horas y 30 minutos</option>
              <option value="8:00" <? echo $horas_reserva=='8:00'?'selected="selected"':''?>>8 horas</option>              
              <option value="8:30" <? echo $horas_reserva=='8:30'?'selected="selected"':''?>>8 horas y 30 minutos</option>
              <option value="9:00" <? echo $horas_reserva=='9:00'?'selected="selected"':''?>>9 horas</option>
              <option value="9:30" <? echo $horas_reserva=='9:30'?'selected="selected"':''?>>9 horas y 30 minutos</option>
              <option value="10:00" <? echo $horas_reserva=='10:00'?'selected="selected"':''?>>10 horas</option>
             
            </select>
           
          </td>
        </tr>
        <tr>
          <td> Estado:</td>
          <td>
            <select id="ddlEstado" class="textopequeTITULO">
              <option value="1" <? echo $estado==1?'selected="selected"':''?>>Habilitado</option>
              <option value="0" <? echo $estado==0?'selected="selected"':''?>>Deshabilidatado</option>
            </select>
          </td>
        </tr>
        <tr>
          <td colspan="2" height="5px"></td>
        </tr>
         <tr>
          <td align="right">
            <input type="button" class="gris" value="  Guardar  " onclick="GuardarReservasSemana();" />
          </td>
          <td></td> 
        </tr>
        <tr>
          <td colspan="2" height="20px"></td>
        </tr>
       </table>
      </div>
      <div id="ConteOtros">
       <table width="100%" height="100%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td class="textopeque" bgcolor="#F2F2F2" align="left"><strong>Los supervisores pueden ver en el calendario de reservas hasta 
          <select class="textopequeTITULO" id="ddlCantidadMes" onchange="CantidadMesesAdelante(this.value)">
          	<option value="1" <? echo NumeroPosteriorMeses==1?'selected="selected"':''?>>1 Mes</option>
            <option value="2" <? echo NumeroPosteriorMeses==2?'selected="selected"':''?>>2 Meses</option>
            <option value="3" <? echo NumeroPosteriorMeses==3?'selected="selected"':''?>>3 Meses</option>
            <option value="4" <? echo NumeroPosteriorMeses==4?'selected="selected"':''?>>4 Meses</option>
            <option value="5" <? echo NumeroPosteriorMeses==5?'selected="selected"':''?>>5 Meses</option>
            <option value="6" <? echo NumeroPosteriorMeses==6?'selected="selected"':''?>>6 Meses</option>
            <option value="7" <? echo NumeroPosteriorMeses==7?'selected="selected"':''?>>7 Meses</option>
            <option value="8" <? echo NumeroPosteriorMeses==8?'selected="selected"':''?>>8 Meses</option>
            <option value="9" <? echo NumeroPosteriorMeses==9?'selected="selected"':''?>>9 Meses</option>
            <option value="10" <? echo NumeroPosteriorMeses==10?'selected="selected"':''?>>10 Meses</option>
            <option value="11" <? echo NumeroPosteriorMeses==11?'selected="selected"':''?>>11 Meses</option>
            <option value="12" <? echo NumeroPosteriorMeses==12?'selected="selected"':''?>>12 Meses</option>
          </select>
           en adelante.</strong></td>             
        </tr>
        <tr>
          <td height="17px"></td>
        </tr> 
        <tr>  
          <td class="textopeque" bgcolor="#F2F2F2" align="left"><strong>El sistema enviara un correo electrónico al usuario 
          <select class="textopequeTITULO" id="ddlEmailHorasAntes" onchange="EmailHorasAntes(this.value)">
          	  <option value="0:15" <? echo EmailHorasAntesReservado=='0:15'?'selected="selected"':''?>>15 minutos</option>
          	  <option value="0:30" <? echo EmailHorasAntesReservado=='0:30'?'selected="selected"':''?>>30 minutos</option>
              <option value="1:00" <? echo EmailHorasAntesReservado=='1:00'?'selected="selected"':''?>>1 hora</option>
              <option value="1:30" <? echo EmailHorasAntesReservado=='1:30'?'selected="selected"':''?>>1 hora y 30 minutos</option>
              <option value="2:00" <? echo EmailHorasAntesReservado=='2:00'?'selected="selected"':''?>>2 horas</option>
              <option value="2:30" <? echo EmailHorasAntesReservado=='2:30'?'selected="selected"':''?>>2 horas y 30 minutos</option>
              <option value="3:00" <? echo EmailHorasAntesReservado=='3:00'?'selected="selected"':''?>>3 horas</option>
              <option value="3:30" <? echo EmailHorasAntesReservado=='3:30'?'selected="selected"':''?>>3 horas y 30 minutos</option>
              <option value="4:00" <? echo EmailHorasAntesReservado=='4:00'?'selected="selected"':''?>>4 horas</option>
              <option value="4:30" <? echo EmailHorasAntesReservado=='4:30'?'selected="selected"':''?>>4 horas y 30 minutos</option>
              <option value="5:00" <? echo EmailHorasAntesReservado=='5:00'?'selected="selected"':''?>>5 horas</option>           
          </select>
           antes que se realice la reserva.</strong></td>             
        </tr> 
        <tr>
          <td height="200px">&nbsp;</td>
        </tr>                     
       </table>
      </div>
    </div>
 </td>
</tr>
</table>

<!--PopUps-->
<div id="PopUpRecurentes">
<table width="450" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="30" bgcolor="#FFFFFF" class="textopeque" >Palzo en Meses:</td>
    <td bgcolor="#FFFFFF"><input type="text" id="txtMes" DataMesActual="" size="5" maxlength="2" class="integer" style="font-family:sans-serif;font-size:12px;color:#454545;text-decoration:none;" /><span class="textopeque">&nbsp;* m&aacute;ximo <? echo $MaxMes;?></span></td>
  </tr>
  <tr>
    <td height="30" bgcolor="#FFFFFF" class="textopeque">Veces:</td>
    <td bgcolor="#FFFFFF"><input type="text" id="txtDia" DataDiaActual="" size="5" maxlength="2" class="integer" style="font-family:sans-serif;font-size:12px;color:#454545;text-decoration:none;" /><span class="textopeque">&nbsp;* m&aacute;ximo <? echo $MaxDias;?></span></td>
  </tr>
  <tr>
    <td width="100" height="30" bgcolor="#FFFFFF" class="textopeque" valign="top">Defici&oacute;n:</td>
    <td width="340" bgcolor="#FFFFFF" class="textopeque">
    <textarea id="txtDefinicion" style="width:320px; height:45px;font-family:sans-serif;font-size:12px;color:#454545;text-decoration:none;" onKeyUp="CantidadLetras(this,200,'_contador');" onblur="CantidadLetras(this,200,'_contador');"></textarea><span class="textopeque">&nbsp;*</span>
    <br />Contador de caracteres <span id="_contador"></span>  de 200
  </tr>
</table>
</div>

<div id="PopUpHorasPorDia">
<table width="450" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="30" bgcolor="#FFFFFF" class="textopeque">Horas:</td>
    <td bgcolor="#FFFFFF"><input type="text" id="txtHora" DataHoraActual="" size="5" maxlength="2" class="integer" style="font-family:sans-serif;font-size:12px;color:#454545;text-decoration:none;" /><span class="textopeque">&nbsp;* m&aacute;ximo <? echo $MaxHoras;?></span></td>
  </tr>
  <tr>
    <td width="100" height="30" bgcolor="#FFFFFF" class="textopeque" valign="top">Defici&oacute;n:</td>
    <td width="340" bgcolor="#FFFFFF" class="textopeque">
    <textarea id="txtDefinicionHoras" style="width:320px; height:45px;font-family:sans-serif;font-size:12px;color:#454545;text-decoration:none;" onKeyUp="CantidadLetras(this,200,'_contadorHoras');" onblur="CantidadLetras(this,200,'_contadorHoras');"></textarea><span class="textopeque">&nbsp;*</span>
    <br />Contador de caracteres <span id="_contadorHoras"></span>  de 200
  </tr>
</table>
</div>
<?
  $MasterPage->MostrarFooter();
?>
</body>
</html>
