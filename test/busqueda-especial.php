<?  
  include("MasterPage.php");
  $MasterPage = new MasterPage();   
?>
<?
 //Validar si el usuario tiene acceso a la pagina actual 
	if(empty($_SESSION['CantReservasEspecial'])){
?>
		<script type="text/javascript"> 
            window.location.href='denegado.php';
        </script>		
<? 
	die();
	}					
?>
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<title>Reservas de Salas :: entel</title>
<?
 $MasterPage->MostrarScriptCss();
 $FechasFestivos=''; 
 //$FechaDesde = date("Y").','.(date("m")-1).','.(date("d")+1);
 $FechaDesde = date("Y").','.(date("m")-1).','.(date("d"));
 $FechaHasta = date("Y").','.(date("m")+84).','.(date("d")); //84 es igual a 7 años mas a la fecha actual
 
?>
<script type="text/javascript" src="js/JQuery/ControlsFormat.js"></script>
<script type="text/javascript">
var r='class/busqueda-especial.php';
var ddlUbicacion,ddlCapacidad,ddlProyector,ddlTelefono,txtFecha,txtHoraInicio,txtHoraFinal;
var idsalaCurrent,FechaCurrent,HoraInicioCurrent,HoraFinalCurrent;
var PopUpAdvertencia;
$(function(){
	var FechasFestivos = [<? echo $FechasFestivos;?>];
	$('#txtFecha').datepicker({
        showOn: 'button',
		buttonImage: 'images/calendario.gif',
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		'minDate': new Date(<? echo $FechaDesde; ?>),
		'maxDate': new Date(<? echo $FechaHasta; ?>),
		beforeShowDay: function(date){
			var day = date.getDay();
			//0:Domingo y 6:Sábado		   
			if (day == 0 || day == 6) {
				return [false, "somecssclass"]
			} else {
			var Dia=String(date.getDate());
			Dia=Dia.length==1?'0'+Dia:Dia;
			var Mes=String((date.getMonth() + 1));
			Mes=Mes.length==1?'0'+Mes:Mes;
			var Anio=date.getFullYear();			
			var FechaCurrent  = Dia + '/' + Mes + '/' + Anio;			
				return jQuery.inArray(FechaCurrent, FechasFestivos) == -1
			? [true, '']
			: [false, 'someothercssclass'];
			}
         }	
    });
	
	SetRangoHoras('8:30','18:00','9:00','18:00');
	
	ddlUbicacion=$("#ddlUbicacion")[0];
	ddlCapacidad=$("#ddlCapacidad")[0];
	ddlProyector=$("#ddlProyector")[0];
	ddlTelefono=$("#ddlTelefono")[0];
	txtFecha=$("#txtFecha")[0];
	txtHoraInicio=$("#txtHoraInicio")[0];
	txtHoraFinal=$("#txtHoraFinal")[0];
	
	PopUpAdvertencia=new Dialogo("ContPopUpAdvertencia",{width:450,title:'ADVERTENCIA',buttons:{'Cerrar':function(){PopUpAdvertencia.close();}}});
});


function NuevaBusqueda(){
	Loading();
	ddlUbicacion.value='';
	ddlCapacidad.value='';
	ddlProyector.value='';
	ddlTelefono.value='';
	txtFecha.value='';
	txtHoraInicio.value='';
	txtHoraFinal.value='';
	$('#ConteResultado').hide();
    $.unblockUI();
}

function BusquedaAvanzanda(pagina){
	var capacidadUno='';
	var capacidadDos='';
	txtHoraInicio=$("#txtHoraInicio")[0];
	txtHoraFinal=$("#txtHoraFinal")[0];
	if(ddlUbicacion.value==''){alert('Es requerido seleccionar la ubicación'); ddlUbicacion.focus(); return;}
	if($.trim(txtFecha.value)==''){alert('Es requerido seleccionar la fecha de reserva'); txtFecha.focus(); return;}
	if($.trim(txtHoraInicio.value)==''){alert('Es requerido seleccionar la hora de inicio'); txtHoraInicio.focus(); return;}
	if($.trim(txtHoraFinal.value)==''){alert('Es requerido seleccionar la hora final'); txtHoraFinal.focus(); return;}
	if(ddlCapacidad.value!=''){
		capacidadUno=ddlCapacidad.value.split('-')[0];
		capacidadDos=ddlCapacidad.value.split('-')[1];
	}
	//Validar si hay error en las horas
	var ErrorHoraInicio=$("#txtHoraInicio").attr('class');
	if($("#txtHoraInicio").attr('class').indexOf('error')!=-1){alert('La hora de inicio no debe ser mayor a la hora final, verifique por favor.'); txtHoraInicio.focus(); return;}
	if($("#txtHoraFinal").attr('class').indexOf('error')!=-1){alert('La hora final no debe ser menor a la hora incial, verifique por favor.'); txtHoraFinal.focus(); return;}
	if($.trim(txtHoraInicio.value)==$.trim(txtHoraFinal.value)){alert('La hora final debe ser mayor a la hora incial, verifique por favor.'); txtHoraFinal.focus(); return;}
	FechaCurrent=txtFecha.value;
	HoraInicioCurrent=txtHoraInicio.value;
	HoraFinalCurrent=txtHoraFinal.value;
	var params=$.param({command:"BusquedaAvanzanda",
						Ubicacion:ddlUbicacion.value,						
						capacidadUno :capacidadUno,
						capacidadDos: capacidadDos,					  
						Proyector: ddlProyector.value,
						Telefono: ddlTelefono.value,
						Fecha: txtFecha.value,
						HoraInicio: txtHoraInicio.value,
						HoraFinal: txtHoraFinal.value,						  						  
						pagina:pagina						  
					  });
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);
		if($.trim(j.Validar)==='DENEGADO'){
			$('#ConteMensaje').html(j.Mensaje);
			$('#ConteResultado').hide();
			PopUpAdvertencia.open();
		}else{
			$('#td_Listado').html(j.Grilla);
			idsalaCurrent=j.idsalaCurrent;
			$('#ConteDetalle').html(j.Detalle);
			$('#ConteResultado').show();
		}
		$.unblockUI();	
	});	
}

function DetalleSalaSeleccion(id){
	idsalaCurrent=id;
	var params=$.param({command:"DetalleSalaSeleccion",						
						idsalaCurrent: id,
						Fecha: FechaCurrent,
						HoraInicio: HoraInicioCurrent,
						HoraFinal: HoraFinalCurrent		  
					  });
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);
		$('#ConteDetalle').html(j.Detalle);			
		$.unblockUI();		
	});
}


function Reservar(){
	var params=$.param({command:"ReservarBusquedaAvanzada",						
						idsalaCurrent: idsalaCurrent,
						Fecha: FechaCurrent,
						HoraInicio: HoraInicioCurrent,
						HoraFinal: HoraFinalCurrent,
						idubicacion: $('#Datos').attr('idubicacion'),
						idevento: $('#Datos').attr('idevento'),
						asistentes: $('#Datos').attr('capacidad')		  
					  });
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);
		if($.trim(j.Validar)==='SIN CREDITO'){
			$.unblockUI();
			alert('La reserva se realizo correctamente\n\nEstimado usuario te informamos que se termino la cantidad de reservas especiales asignadas.');
			window.location.href='inicio.php';		
		}else{
			$('#ContadorReservas').html(j.Credito);
			alert('La reserva se realizo correctamente.');			
			NuevaBusqueda();
		}
	});
}

function Hora(i){
	$('#tdtxtHoraInicio').html('<input type="text" class="FRM" id="txtHoraInicio" maxlength="8"  size="10" readonly="readonly" />');
	$('#tdtxtHoraFinal').html('<input type="text" class="FRM" id="txtHoraFinal" maxlength="8"  size="10" readonly="readonly" />');	
	
	var params=$.param({command:"RangoHoras",						
						idubicacion: i		  
					  });
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);
		SetRangoHoras(j.RangoUnoInicio,j.RangoUnoFinal,j.RangoDosInicio,j.RangoDosFinal);
		$.unblockUI();		
	});
}

function SetRangoHoras(RangoUnoInicio,RangoUnoFinal,RangoDosInicio,RangoDosFinal){
    //Para el rango de horas   
    $("#txtHoraInicio").timePicker({
        startTime: RangoUnoInicio,
        endTime: RangoUnoFinal,
        show24Hours: true,
        separator: ':',
        step: 30       
    });
   
    $("#txtHoraFinal").timePicker({
        startTime: RangoDosInicio,
        endTime: RangoDosFinal,
        show24Hours: true,
        separator: ':',
        step: 30       
    });

    var oldTime = $.timePicker("#txtHoraInicio").getTime();
    $("#txtHoraInicio").change(function() {
        if($.timePicker("#txtHoraFinal").getTime() < $.timePicker(this).getTime() && $("#txtHoraFinal").val()!='') {
            $(this).addClass("error");
        }
        else {
            $(this).removeClass("error");
            $("#txtHoraFinal").removeClass("error");
        }
    });
    $("#txtHoraFinal").change(function() {
        if($.timePicker("#txtHoraInicio").getTime() > $.timePicker(this).getTime()) {
            $(this).addClass("error");
        }
        else {
            $(this).removeClass("error");
            $("#txtHoraInicio").removeClass("error");
        }
    });
}
</script>
</head>
<body>
<?
 $MasterPage->MostrarCabecera();
?>
<!--Contenido Inicio-->
<table width="910" border="0" align="center" cellpadding="0" cellspacing="0" id="tbInicio">
 <tr>
    <td width="25">&nbsp;</td>
</tr>
<tr>
<td height="50" align="left" valign="middle" bgcolor="#F2F2F2" colspan="4"><span class="tituloSERV"><? echo $TituloPaginaActual;?></span></td>
</tr>
<tr>
<td height="25" align="left" valign="middle" bgcolor="#FFFFFF" colspan="4">
</td>
</tr>
<tr>    
<td width="150" height="30" align="center" valign="middle" bgcolor="#DDEBFF" class="textopequeTITULO"><a href="javascript:;" class="textoLink"><strong>Búsqueda Avanzada</strong></a></td>
<td width="11">&nbsp;</td>
<td width="167" align="center" valign="middle" bgcolor="#F2F2F2" class="textopequeTITULO"><a href="busqueda-x-salas-especial.php" class="textoLink">Búsqueda por Sala</a></td>
<td width="580">&nbsp;</td>
</tr>
<tr>        
<td colspan="4" valign="top"  align="left">
  <table width="910" border="0" cellpadding="0" align="left" cellspacing="0" bgcolor="#F2F2F2">
    <!--DWLayoutTable-->
    <tr>              
      <td width="218" height="7"></td>
      <td width="88"></td>
      <td width="83"></td>
      <td width="81"></td>
      <td width="122"></td>
      <td width="80"></td>
      <td width="78"></td>
      <td width="129"></td>
      <td width="6"></td>
    </tr>
    <tr>              
      <td height="15" valign="middle" class="textopequeTITULO">UBICACION</td>
      <td valign="middle" class="textopequeTITULO">CAPACIDAD</td>
      <td valign="middle" class="textopequeTITULO">PROYECTOR</td>
      <td valign="middle" class="textopequeTITULO">TELEFONO</td>
      <td valign="middle" class="textopequeTITULO">FECHA</td>
      <td valign="middle" class="textopequeTITULO">HORA INICIO</td>
      <td valign="middle" class="textopequeTITULO">HORA FINAL</td>
      <td valign="middle" class="textopequeTITULO">&nbsp;</td>
    </tr>
    <tr>              
      <td valign="middle">
       <select class="gris" id="ddlUbicacion" style="width:210px" onChange="Hora(this.value);">
        <option value="" selected="selected"></option>
        <? 
            $ResultUbicacion=mysql_query("select u.idubicacion, u.nombre 
                                          from ubicaciones u ");
            while($Campos=mysql_fetch_array($ResultUbicacion)){
                echo '<option value="'.$Campos['idubicacion'].'">'.htmlentities($Campos['nombre']).'</option>';
            }
        ?>
       </select>
                    
                    </td>
      <td valign="middle"><select class="gris" id="ddlCapacidad">
          <option selected="selected" value="">---</option>
          <option value="2-4">2 - 4</option>
          <option value="4-6">4 - 6</option>
          <option value="6-8"> 6- 8</option>
          <option value="8-10">8 - 10</option>
          <option value="10-12">10 - 12</option>
          <option value="12-20">12 - 20</option>
          <option value="20-60">20 - 60</option>
          <option value="60-100">60 - 100</option>         
      </select></td>
      <td valign="middle"><select class="gris" id="ddlProyector">
          <option selected="selected" value="">---</option>
          <option value="1">SI</option>
          <option value="0">NO</option>
      </select></td>
      <td valign="middle"><select class="gris" id="ddlTelefono">
          <option selected="selected" value="">---</option>
          <option value="1">SI</option>
          <option value="0">NO</option>
      </select></td>
      <td valign="middle"><input name="dater" type="text" class="gris" id="txtFecha" readonly="readonly" size="15" /></td>
      <td valign="middle" id="tdtxtHoraInicio"><input type="text" class="FRM" id="txtHoraInicio" maxlength="8"  size="10" readonly="readonly" /></td>
      <td valign="middle" id="tdtxtHoraFinal">
      <input type="text" class="FRM" id="txtHoraFinal" size="10" maxlength="8" readonly="readonly" />
      </td>
      <td valign="middle"><input name="b1" type="button" class="gris" value="Buscar Sala" onClick="BusquedaAvanzanda(0);" />
          <input name="b2" type="button" class="gris" value="Nuevo" onClick="NuevaBusqueda();" /></td>
    </tr>
    <tr>
      <td height="9"></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      
    </tr>
  </table>
 </td>          
</tr>
<tbody id="ConteResultado" style="display:none;">
<tr>        
<td colspan="4" valign="top">
<table>
 <tr>
  <td valign="top">
    <table width="393" border="0" cellpadding="0" cellspacing="0">
      <tr>           
            <td valign="middle" align="left"><span class="SUBTituloC">RESULTADOS DE BUSQUEDA </span></td>
       </tr>
       <tr>           
            <td valign="middle" align="left"><span class="SUBTituloC">COMPLEMENTOS </span><span class="datos">P: Proyector | T: Teléfono | Z: Pizarra | E: Ecran</span></td>
       </tr>
       <tr>           
            <td height="10px"></td>
       </tr>
      <tr>
           <td id="td_Listado"></td>               
       </tr>          
    </table>
    </td>
    <td>&nbsp;</td>
    <td width="2" align="left" valign="top" id="ConteDetalle"></td> 
 </tr>
</table>
 </td>       
</tr>      
  </tbody> 
  <tr>
   <td colspan="4" height="40px"></td>
  </tr>   
</table> 
<div id="ContPopUpAdvertencia">
<table width="430" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td id="ConteMensaje" align="left"></td>
  </tr>
</table>
</div>

<?
  $MasterPage->MostrarFooter();
?>
</body>
</html>
