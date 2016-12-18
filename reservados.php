<?
  include("class/reservados.php");
  include("MasterPage.php");
  $MasterPage = new MasterPage();  
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Reservas de Salas Nextel</title>
<?
 $MasterPage->MostrarScriptCss();  
?>
<link rel="stylesheet" type="text/css" href="js/JQuery/AutoComplete/simpleAutoComplete.css" />
<script type="text/javascript" src="js/JQuery/AutoComplete/simpleAutoComplete.js"></script>
<script type="text/javascript">
var r='class/reservados.php';
var PaginaActual=0;
var PopUpReservar,PopUpRestaurar;
var idreservaRestaurar=0;
var txtDesde,txtHasta,ddlUbicacion;
var FiltroDesde,FiltroHasta,FiltroUbicacion,FiltroPiso,FiltroSala,FiltroUsuario;
FiltroDesde='';
FiltroHasta='';
FiltroUbicacion='';
FiltroPiso='';
FiltroSala='';
FiltroUsuario='';

$(function(){
	PopUpReservar=new Dialogo("ContPopUpReservar",{width:520,title:'SALA RESERVADO',buttons:{'Cerrar':function(){PopUpReservar.close();}}});
	PopUpRestaurar=new Dialogo("ContPopUpRestaurar",{width:520,title:'RESTAURAR SALA RESERVADO',buttons:{'Cancelar':function(){PopUpRestaurar.close();},'Restaurar reserva':Restaurar}});
	var FechasFestivos = [];
	var dates = $('#txtFechaDesde, #txtFechaHasta').datepicker({
		maxDate:'+60m +1w',	
		minDate:'+0m +0w',
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
	$('#NombresUserAutocomplete').simpleAutoComplete('class/usuarios.php',{
		autoCompleteClassName: 'autocomplete',
		selectedClassName: 'sel',
		attrCallBack: 'rel',
		identifier: 'Usuarios'
	 });
	 
	txtDesde=$("#txtFechaDesde")[0];
	txtHasta=$("#txtFechaHasta")[0];
	ddlUbicacion=$("#ddlUbicacion")[0];
	FiltroUsuario='';
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
	FiltroUsuario=$.trim($('#NombresUserAutocomplete').val());
	params = $.param({command:'Buscar',pagina:0,Desde:FiltroDesde,Hasta:FiltroHasta,Ubicacion:FiltroUbicacion,Piso:FiltroPiso,Sala:FiltroSala,Usuario:FiltroUsuario});
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
	$('#NombresUserAutocomplete').val('');
	FiltroDesde='';
	FiltroHasta='';
	FiltroUbicacion='';
	FiltroPiso='';
	FiltroSala='';
	FiltroUsuario='';	
	params = $.param({command:'Buscar',pagina:0,Desde:FiltroDesde,Hasta:FiltroHasta,Ubicacion:FiltroUbicacion,Piso:FiltroPiso,Sala:FiltroSala,Usuario:FiltroUsuario});
	callServer({data:params,url:r},function(d){		
		$('#td_Contenido').html(d);
		$.unblockUI();
	});
}

function Paginar(pagina){
	PaginaActual=pagina;
	params=$.param({command:'Buscar',pagina:pagina,Desde:FiltroDesde,Hasta:FiltroHasta,Ubicacion:FiltroUbicacion,Piso:FiltroPiso,Sala:FiltroSala,Usuario:FiltroUsuario});
	callServer({data:params,url:r},function(d){		
		$('#td_Contenido').html(d);
		$.unblockUI();
	});
}

function DetalleSalaSeleccion(id){	
	var params=$.param({command:"DetalleSala",						
						idreserva: id
					  });
	callServer({data:params,url:r},function(d){
		$('#ConteDetalle').html(d);	
		PopUpReservar.open();			
		$.unblockUI();		
	}); 
}

function DetalleSalaRestaurar(id){	
	idreservaRestaurar=id;
	var params=$.param({command:"DetalleSalaRestaurar",						
						idreserva: id
					  });
	callServer({data:params,url:r},function(d){
		$('#ConteDetalleRestaurar').html(d);	
		PopUpRestaurar.open();			
		$.unblockUI();		
	});
}

function Restaurar(){
	PopUpRestaurar.close();
	var params=$.param({command:"Restaurar",						
						idreserva: idreservaRestaurar,
						pagina:PaginaActual,
						Desde:FiltroDesde,
						Hasta:FiltroHasta,
						Ubicacion:FiltroUbicacion,
						Piso:FiltroPiso,
						Sala:FiltroSala,
						Usuario:FiltroUsuario
					  });
	callServer({data:params,url:r},function(d){
		$('#td_Contenido').html(d);
		alert('La reserva fue restaurado correctamente.');					
		$.unblockUI();		
	});
}

function Eliminar(id){	
	if(!confirm("Confirmar si desea eliminar la reserva.")) return;
	params = $.param({command:"Eliminar",id:id,pagina:PaginaActual,Desde:FiltroDesde,Hasta:FiltroHasta,Ubicacion:FiltroUbicacion,Piso:FiltroPiso,Sala:FiltroSala,Usuario:FiltroUsuario});
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
<!-- AGREGADO POR YOEL -->
function ExportarListado(){
	if($('#td_Contenido tr.Registros').length==0){
		alert('No hay registros para exportar.');
		return;
	}	
	params = $.param({Desde:FiltroDesde,
					  Hasta:FiltroHasta,
					  Ubicacion:FiltroUbicacion,
					  Piso:FiltroPiso,
					  Sala:FiltroSala,
					  Usuario:FiltroUsuario
					 });
	window.location.href='class/reservas-pendientes-exp.php?'+params;
}
<!-- FIN DE AGREGADO POR YOEL -->
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
    <td colspan="7" height="30" align="center" valign="middle" bgcolor="#F2F2F2" class="textopequeTITULO"><span class="textopequeTITULO"><strong>Búsqueda</strong></span></td>
    </tr>
    <tr>              
      <td  height="7" colspan="6"></td>
    </tr>
    <tr>          
      <td width="118" valign="middle" class="textopequeTITULO">FECHA DESDE</td>
      <td width="126" valign="middle" class="textopequeTITULO">FECHA HASTA</td>
      <td width="121" valign="middle" class="textopequeTITULO">UBICACION</td>
      <td width="107" valign="middle" class="textopequeTITULO">PISO</td> 
      <td width="95" valign="middle" class="textopequeTITULO">SALA</td>
      <td width="217" valign="middle" class="textopequeTITULO">USUARIO</td>
      <td width="126" valign="middle" class="textopequeTITULO">&nbsp;</td>
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
      <td valign="middle"><input type="text" id="NombresUserAutocomplete" name="NombresUser" class="FRM" autocomplete="off" style="width: 200px; height:15px" /></td>              
      <td valign="middle" align="left">&nbsp;&nbsp;<input type="button" class="gris" value=" Buscar " onclick="Buscar();" />
          <input type="button" class="gris" value=" Ver Todos " onclick="Todos();" />
          <input type="button" class="gris" value=" Exportar a Excel " onclick="ExportarListado();" />       </td>
    </tr>
    <tr>
      <td height="10" colspan="7"></td>                            
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
    $Datos = new Reservados();
    $Datos->Listado(0,'','','','','','');
  ?>
</td>
</tr>
 </table> 
 <div id="ContPopUpReservar">
<table width="500" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td id="ConteDetalle"></td>
  </tr>
</table>
</div>
<div id="ContPopUpRestaurar">
<table width="500" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td id="ConteDetalleRestaurar"></td>
  </tr>
</table>
</div>
<?
  $MasterPage->MostrarFooter();
?>
</body>
</html>
