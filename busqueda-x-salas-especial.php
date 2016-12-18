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
 $FechaDesde = date("Y").','.(date("m")-1).','.(date("d"));
 $FechaHasta = date("Y").','.(date("m")+84).','.(date("d")); //84 es igual a 7 años mas a la fecha actual
?>
<link href="css/tooltip.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="js/tooltip.js"></script>  
<script type="text/javascript" src="js/JQuery/ControlsFormat.js"></script>
<script type="text/javascript">
var r='class/busqueda-especial.php';
var txtFecha,ddlUbicacion;
var idsalaCurrent,FechaCurrent,FechaBuscador,ubicacionCurrent,ddlSala,ddlPiso;
var PopUpReservar,PopUpAdvertencia;
var FechaCurrentSelected,HoraInicioCurrent,HoraFinalCurrent;
						
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
    
	txtFecha=$("#txtFecha")[0];
	ddlUbicacion=$("#ddlUbicacion")[0];
	
	PopUpReservar=new Dialogo("ContPopUpReservar",{width:520,title:'RESERVAR SALA',buttons:{'Cancelar':function(){$('#ConteResultadoBuscar input').each(function(i,e){e.checked=false; $(e).hide();});PopUpReservar.close();}},  close: function(event, ui) {$('#ConteResultadoBuscar input').each(function(i,e){e.checked=false; $(e).hide();});}  });
	PopUpAdvertencia=new Dialogo("ContPopUpAdvertencia",{width:450,title:'ADVERTENCIA',buttons:{'Cerrar':function(){PopUpAdvertencia.close();}}});
});

function NuevaBusqueda(){
	Loading();
	txtFecha.value='';
	ddlUbicacion.value='';
	$('#tdSalas').html('<select class="gris" id="ddlSala"><option value="">-----------</option></select>');
	$('#ConteResultado').hide();
    $.unblockUI();
}

function BusquedaPorSala(){
	ddlPiso=$("#ddlPiso")[0];
	ddlSala=$("#ddlSala")[0];
	if($.trim(txtFecha.value)==''){alert('Es requerido seleccionar la fecha de reserva'); txtFecha.focus(); return;}
	if(ddlUbicacion.value==''){alert('Es requerido seleccionar la ubicación'); ddlUbicacion.focus(); return;}
	if(ddlPiso.value==''){alert('Es requerido seleccionar el píso'); ddlPiso.focus(); return;}
	if(ddlSala.value==''){alert('Es requerido seleccionar la sala'); ddlSala.focus(); return;}
	FechaBuscador=$.trim(txtFecha.value);
	ubicacionCurrent=ddlUbicacion.value;
	idsalaCurrent=ddlSala.value;
	var params=$.param({command:"BusquedaPorSala",
						Fecha: txtFecha.value,					
						Sala:ddlSala.value				  
					  });	
	callServer({data:params,url:r},function(d){					
		var j=toEval(d);
		if($.trim(j.Validar)==='DENEGADO'){
			$('#ConteResultadoBuscar').empty();
			$('#ConteMensaje').html(j.Mensaje);			
			PopUpAdvertencia.open();
			$('#ConteResultadoBuscar input').each(function(i,e){e.checked=false; $(e).hide();});
		}else{
			$('#ConteResultadoBuscar').html(j.Cuadro);
			SetControlHora();
			$('#ConteResultado').show();
		}
		$.unblockUI();		
	});	
}

function SetControlHora(){	
	var CountGrupo=0;
	$('#ConteResultadoBuscar .cabeceras').each(function(i,e){
		CountGrupo +=1;
		ArrHoras=$('#ConteResultadoBuscar .'+e.id);
		$.each(ArrHoras,function(x,a){
			var disponible=$(a).attr('disp');		
			if(disponible=='1'){
				var HoraAnt = $(ArrHoras[x-1]);
				var HoraSig = $(ArrHoras[x+1]);
				if(HoraAnt.attr('disp')=='0'){				
					CountGrupo +=1;
					$(HoraAnt).html('<input type="checkbox" style="display:none" grupo="'+($(HoraAnt).attr('Fechas')+CountGrupo)+'" onclick="SeletedCheckBox(this)" Hora="'+$(HoraAnt).attr('HoraFormat')+'" Fechas="'+$(HoraAnt).attr('FechasFormat')+'" />');
				}				
				if(HoraSig.attr('disp')=='0'){
					$(HoraSig).html('<input type="checkbox" style="display:none" grupo="'+($(HoraSig).attr('Fechas')+CountGrupo)+'" onclick="SeletedCheckBox(this)" Hora="'+$(HoraSig).attr('HoraFormat')+'" Fechas="'+$(HoraSig).attr('FechasFormat')+'" />');
				}
				$(a).html('<input type="checkbox" style="display:none" grupo="'+($(a).attr('Fechas')+CountGrupo)+'" onclick="SeletedCheckBox(this)" Hora="'+$(a).attr('HoraFormat')+'" Fechas="'+$(a).attr('FechasFormat')+'" />');	
			}
		});		
	});	
}

function AyudaHover(td,h,f){
	$('#ConteResultadoBuscar .over').css({'background-color' : '#EFEFEF'});
	$('#'+h).css({'background-color' : '#E6FFCC'});
	$('#'+f).css({'background-color' : '#E6FFCC'});
	if($(td).children('input')){//Mostrar Checkbox
		$(td).children('input').show(); 
	}
}

function AyudaOut(td){
	$('#ConteResultadoBuscar .over').css({'background-color' : '#EFEFEF'});
	if(!$(td).find('input').attr('checked')){//Mostrar Checkbox
		$(td).children('input').hide(); 
	}
}



function SeletedCheckBox(ch){
	var CheckBoxClick;
	var CheckBoxGrupo;
	var CountChecked=0;
	if(ch.checked){
		CheckBoxClick=ch;
		CheckBoxGrupo=$(ch).attr('grupo');
		var ArrRangoReservar=[];
		$('#ConteResultadoBuscar input').each(function(i,e){
			if($(e).attr('grupo')!=CheckBoxGrupo && $(e).attr('checked')==true){
				$(e).attr('checked',false);
				$(e).hide();
				ArrRangoReservar=[];
			}else if($(e).attr('grupo')==CheckBoxGrupo && $(e).attr('checked')==true){
				CountChecked++;	
				ArrRangoReservar.push($(e).attr('Hora'));
				if(CountChecked==2){	
					FechaCurrentSelected=$(e).attr('Fechas');
					HoraInicioCurrent=ArrRangoReservar[0];
					HoraFinalCurrent=ArrRangoReservar[1];
					//Mostrar El PopUp
					var params=$.param({command:"DetalleSalaSeleccion",						
										idsalaCurrent: idsalaCurrent,
										Fecha: FechaCurrentSelected,
										HoraInicio: HoraInicioCurrent,
										HoraFinal: HoraFinalCurrent
									  });
					callServer({data:params,url:r},function(d){		
						var j=toEval(d);
						if($.trim(j.Validar)==='DENEGADO'){
							$('#ConteMensaje').html(j.Mensaje);							
							PopUpAdvertencia.open();
							$('#ConteResultadoBuscar input').each(function(i,e){e.checked=false; $(e).hide();});
						}else{
							$('#ConteDetalle').html(j.Detalle);	
							PopUpReservar.open();						
						}
						$.unblockUI();			
					});
				}
			}
		});
	}	
}

function Reservar(){
	PopUpReservar.close();
	var params=$.param({command:"ReservarBusquedaPorSala",						
						idsalaCurrent: idsalaCurrent,						
						Fecha: FechaCurrentSelected,
						HoraInicio: HoraInicioCurrent,
						HoraFinal: HoraFinalCurrent,						
						idubicacion: ubicacionCurrent,						
						idevento: $('#Datos').attr('idevento'),
						asistentes: $('#Datos').attr('capacidad'),
						FechaBuscador: FechaBuscador		  
					  });
	callServer({data:params,url:r},function(d){		
		//var j=toEval(d);
		/*$('#ConteResultadoBuscar').html(j.Cuadro);
		SetControlHora();	
		alert('La reserva se realizo correctamente.');
		$.unblockUI();*/
		
		
		if($.trim(d)==='SIN CREDITO'){
			$.unblockUI();
			alert('La reserva se realizo correctamente\n\nEstimado usuario te informamos que se termino la cantidad de reservas especiales asignadas.');
			window.location.href='inicio.php';		
		}else{
			$('#ContadorReservas').html(d);
			alert('La reserva se realizo correctamente.');			
			NuevaBusqueda()
		}
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
						idubicacion:ddlUbicacion.value,
						piso: id							  
					  });
	callServer({data:params,url:r},function(d){	
		$('#tdSalas').html(d);
		$.unblockUI();
	});
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
<td height="50" align="left" valign="middle" bgcolor="#F2F2F2" colspan="4"><span class="tituloSERV"><? echo $TituloPaginaActual;?></span></td>
</tr>
<tr>
<td height="25" align="left" valign="middle" bgcolor="#FFFFFF" colspan="4"></td>
</tr>
<tr>    
<td width="170" height="30" align="center" valign="middle" bgcolor="#F2F2F2" class="textopequeTITULO"><a href="busqueda-especial.php" class="textoLink"><strong>Búsqueda Avanzada</strong></a></td>
<td width="11">&nbsp;</td>
<td width="187" align="center" valign="middle" bgcolor="#DDEBFF" class="textopequeTITULO"><a href="javascript:;" class="textoLink">Búsqueda por Sala</a></td>
<td width="540">&nbsp;</td>
</tr>
<tr>        
<td colspan="4" valign="top"  align="left">
  <table width="910" border="0" cellpadding="0" cellspacing="0" bgcolor="#F2F2F2">
    <tr>              
      <td width="122" height="7"></td>
      <td width="102"></td>             
      <td width="93"></td>
      <td width="93"></td>
      <td width="500"></td>
    </tr>
    <tr>          
      <td valign="middle" class="textopequeTITULO">FECHA</td>
      <td valign="middle" class="textopequeTITULO">UBICACION</td>
      <td valign="middle" class="textopequeTITULO">PISO</td> 
      <td valign="middle" class="textopequeTITULO">SALA</td>              
      <td valign="middle" class="textopequeTITULO">&nbsp;</td>
    </tr>
    <tr>             
      <td valign="middle"><input name="dater" type="text" class="gris" id="txtFecha" readonly="readonly" size="15" /></td>
      <td valign="middle">
       <select class="gris" id="ddlUbicacion" onChange="ComboPisos(this.value);">
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
      <td valign="middle" align="left">&nbsp;&nbsp;<input name="b1" type="button" class="gris" value=" Buscar Sala " onClick="BusquedaPorSala();" />
          <input name="b2" type="button" class="gris" value=" Nueva Búsqueda " onClick="NuevaBusqueda();" />
       </td>
    </tr>
    <tr>
      <td height="9"></td>
      <td></td>
      <td></td>
      <td></td>                       
    </tr>
  </table>
 </td>          
</tr>
<tbody id="ConteResultado" style="display:none;">
<tr>        
<td colspan="4" valign="top" align="left" height="17px"></td>       
</tr>
<tr>        
<td colspan="4" valign="top" align="left" id="ConteResultadoBuscar"></td>       
</tr>      
</tbody> 
<tr>
  <td colspan="4" height="40px"></td>
</tr>   
</table>
<div id="ContPopUpReservar">
<table width="500" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td id="ConteDetalle"></td>
  </tr>
</table>
</div>
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
