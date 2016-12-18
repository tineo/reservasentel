<?
  include("class/reservas-historial.php");
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
<script type="text/javascript">
var r='class/reservas-historial.php';
var PaginaActual=0;
var txtDesde,txtHasta,ddlUbicacion;
var FiltroDesde,FiltroHasta,FiltroUbicacion,FiltroPiso,FiltroSala;
FiltroDesde='';
FiltroHasta='';
FiltroUbicacion='';
FiltroPiso='';
FiltroSala='';

$(function(){
	var FechasFestivos = [];
	var dates = $('#txtFechaDesde, #txtFechaHasta').datepicker({
		maxDate:'+0m +0w',
		changeMonth: true,
		changeYear: true,
		showOn: 'button',
		buttonImage: 'images/calendario.gif',
		buttonImageOnly: true,
		onSelect: function(selectedDate) {
			var option = this.id == "txtFechaDesde" ? "minDate" : "maxDate";
			var instance = $(this).data("datepicker");
			var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
			dates.not(this).datepicker("option", option, date);
		},
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
	txtDesde=$("#txtFechaDesde")[0];
	txtHasta=$("#txtFechaHasta")[0];
	ddlUbicacion=$("#ddlUbicacion")[0];
});

function ValidarRangoFechas(){
	FiltroDesde=$.trim(txtDesde.value);
	FiltroHasta=$.trim(txtHasta.value);
	if(FiltroDesde!='' || FiltroHasta!=''){
		if(FiltroDesde == '' || (FiltroDesde == '' && FiltroHasta != '')){alert('Es requerido ingresar el rango de fecha Desde: '); return false;}
		if(FiltroHasta == '' || (FiltroDesde != '' && FiltroHasta == '')){alert('Es requerido ingresar el rango de fecha Hasta: '); return false;}
	};	
	return true;
}

function Buscar(){
	if(!ValidarRangoFechas()) return;
	FiltroUbicacion=ddlUbicacion.value;
	FiltroPiso=$('#ddlPiso').val();
	FiltroSala=$('#ddlSala').val();	
	params = $.param({command:'Buscar',pagina:0,Desde:FiltroDesde,Hasta:FiltroHasta,Ubicacion:FiltroUbicacion,Piso:FiltroPiso,Sala:FiltroSala});
	callServer({data:params,url:r},function(d){		
		$('#td_Contenido').html(d);
		$.unblockUI();
	});
}

function Todos(){
	txtDesde.value='';	
	txtHasta.value='';
	ddlUbicacion.value='';
	$('#tdPisos').html('<select class="gris" id="ddlPiso"><option value="">-----------</option></select>');
	$('#tdSalas').html('<select class="gris" id="ddlSala"><option value="">-----------</option></select>');
	FiltroDesde='';
	FiltroHasta='';
	FiltroUbicacion='';
	FiltroPiso='';
	FiltroSala='';
	params = $.param({command:'Buscar',pagina:0,Desde:FiltroDesde,Hasta:FiltroHasta,Ubicacion:FiltroUbicacion,Piso:FiltroPiso,Sala:FiltroSala});
	callServer({data:params,url:r},function(d){		
		$('#td_Contenido').html(d);
		$.unblockUI();
	});
}

function Paginar(pagina){
	PaginaActual=pagina;
	params=$.param({command:'Buscar',pagina:pagina,Desde:FiltroDesde,Hasta:FiltroHasta,Ubicacion:FiltroUbicacion,Piso:FiltroPiso,Sala:FiltroSala});
	callServer({data:params,url:r},function(d){		
		$('#td_Contenido').html(d);
		$.unblockUI();
	});
}

//$('#Datos').attr('idsala')
function DetalleSalaSeleccion(id){	
	var params=$.param({command:"DetalleSalaSeleccion",						
						idreservaCurrent: id
					  });
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);
		$('#ConteDetalle').html(j.Detalle);			
		$.unblockUI();		
	});
}

function Eliminar(id){	
	if(!confirm("Confirmar si desea eliminar la reserva.")) return;
	params = $.param({command:"Eliminar",id:id,pagina:PaginaActual,Desde:FiltroDesde,Hasta:FiltroHasta,Ubicacion:FiltroUbicacion,Piso:FiltroPiso,Sala:FiltroSala});
	callServer({data:params,url:r},function(d){		
		$('#td_Contenido').html(d);
		$.unblockUI();
	});
}

function ComboPisos(id){
	if(id==''){
		$('#tdPisos').html('<select class="gris" id="ddlPiso"><option value="">-----------</option></select>');
		$('#tdSalas').html('<select class="gris" id="ddlSala"><option value="">-----------</option></select>');
		return;
	}
	var params=$.param({command:"ComboPisos",						
						id: id							  
					  });
	callServer({data:params,url:r},function(d){	
		$('#tdPisos').html(d);
		$('#tdSalas').html('<select class="gris" id="ddlSala"><option value="">-----------</option></select>');
		$.unblockUI();
	});
}

function ComboSalas(id){
	if(id==''){
		$('#tdSalas').html('<select class="gris" id="ddlSala"><option value="">-----------</option></select>');
		return;
	}
	var params=$.param({command:"ComboSalas",						
						idubicacion:$('#ddlUbicacion').val(),
						piso: id							  
					  });
	callServer({data:params,url:r},function(d){	
		$('#tdSalas').html(d);
		$.unblockUI();
	});
}

function ExportarListado(){
	if($('#td_Contenido tr.Registros').length==0){
		alert('No hay registros para exportar.');
		return;
	}	
	params = $.param({Desde:FiltroDesde,
					  Hasta:FiltroHasta,
					  Ubicacion:FiltroUbicacion,
					  Piso:FiltroPiso,
					  Sala:FiltroSala
					 });
	window.location.href='class/reservas-historial-exp.php?'+params;
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
    <td>&nbsp;</td>
</tr>
<tr>
<td width="910" height="50" align="left" valign="middle" bgcolor="#F2F2F2"><span class="tituloSERV"><? echo $TituloPaginaActual;?></span></td>
</tr>
<tr>
<td height="15" align="left" valign="middle" bgcolor="#FFFFFF"></td>
</tr>
<tr>        
<td valign="top"  align="left">
  <table width="910" border="0" cellpadding="0" cellspacing="0" bgcolor="#F2F2F2">
    <tr>    
    <td colspan="6" height="30" align="center" valign="middle" bgcolor="#F2F2F2" class="textopequeTITULO"><span class="textopequeTITULO"><strong>Búsqueda</strong></span></td>
    </tr>
    <tr>              
      <td  height="7" colspan="6"></td>
    </tr>
    <tr>          
      <td width="118" valign="middle" class="textopequeTITULO">FECHA DESDE</td>
      <td width="126" valign="middle" class="textopequeTITULO">FECHA HASTA</td>
      <td width="121" valign="middle" class="textopequeTITULO">UBICACION</td>
      <td width="107" valign="middle" class="textopequeTITULO">PISO</td> 
      <td width="103" valign="middle" class="textopequeTITULO">SALA</td>
      <td width="335" valign="middle" class="textopequeTITULO">&nbsp;</td>
    </tr>
    <tr>             
      <td valign="middle"><input name="dater" type="text" class="gris" id="txtFechaDesde" readonly="readonly" size="15" /></td>
      <td valign="middle"><input name="dater" type="text" class="gris" id="txtFechaHasta" readonly="readonly" size="15" /></td>
      <td valign="middle">
       <select class="gris" id="ddlUbicacion" onchange="ComboPisos(this.value);">
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
       <td valign="middle" id="tdPisos"><select class="gris" id="ddlPiso"><option value="">-----------</option></select></td> 
      <td valign="middle" id="tdSalas"><select class="gris" id="ddlSala"><option value="">-----------</option></select></td>              
      <td valign="middle" align="left">&nbsp;&nbsp;<input type="button" class="gris" value=" Buscar " onclick="Buscar();" />
          <input type="button" class="gris" value=" Ver Todos " onclick="Todos();" />&nbsp;&nbsp;
          <input type="button" class="gris" value=" Exportar a Excel " onclick="ExportarListado();" />
       </td>
    </tr>
    <tr>
      <td height="10" colspan="6"></td>                            
    </tr>
   </table>
  </td>
  </tr>
<tr>              
  <td  height="15"></td>
</tr>

<tr>
<td id="td_Contenido" align="center" valign="top">
 <? 
    $Datos = new MisReservasHistorial();
    $Datos->DatosCargar(0,'','','','','','Load');
  ?>
</td>
</tr>
 </table> 
<?
  $MasterPage->MostrarFooter();
?>
</body>
</html>
