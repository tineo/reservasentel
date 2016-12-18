<?
  include("class/busqueda.php");
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
  $FechasFestivos=''; 
 //$FechaDesde = date("Y").','.(date("m")-1).','.(date("d")+1);
 $FechaDesde = date("Y").','.(date("m")-1).','.(date("d"));
 
 //
 if($_SESSION["tipo"]=='A'){ //Administrador, no se restringe ninguna regla
 	$FechaHasta = date("Y").','.(date("m")+84).','.(date("d")); //84 es igual a 7 a�os mas a la fecha actual
 }else{
 	$FechaHasta = FechaHasta;
	
	//Fechas Festivos no laborables
    //La consulta se esta mostrando desde la fecha actual, y posterior a 2 meses 
	$ResultFechas=mysql_query("select fecha from fechas_especiales where date(fecha) BETWEEN date(NOW()) and date(DATE_ADD(NOW(),INTERVAL ".(NumeroPosteriorMeses+1)." MONTH)) order by fecha asc ");
	 while($Campos=mysql_fetch_array($ResultFechas)){
		 $FechasFestivos .= ",'".$Utilitario->cambiaf_a_normal($Campos['fecha'])."'";
	 }
	 $FechasFestivos=substr($FechasFestivos,1); 
 }
?>
<link href="css/tooltip.css" rel="stylesheet" type="text/css" media="all" />
<link rel="stylesheet" type="text/css" href="js/JQuery/AutoComplete/simpleAutoComplete.css" />
<script type="text/javascript" src="js/JQuery/AutoComplete/simpleAutoComplete.js"></script> 
<script language="javascript" type="text/javascript" src="js/tooltip.js"></script>  
<script type="text/javascript" src="js/JQuery/ControlsFormat.js"></script>
<script type="text/javascript">
var r='class/busqueda.php';
var txtFecha,ddlUbicacion;
var idsalaCurrent,FechaCurrent,FechaBuscador,ubicacionCurrent,ddlSala,ddlPiso;
var PopUpReservar,PopUpAdvertencia;
var FechaCurrentSelected,HoraInicioCurrent,HoraFinalCurrent;
var PopUpUsuarios,txtBuscar,hdIdUsuarioAsignar;
						
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
			//0:Domingo y 6:S�bado		   
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
	
	PopUpUsuarios=new Dialogo("PopUpListadoUsuarios",{width:722,title:'B&uacute;squeda de usuarios',buttons:{'Cancelar':function(){PopUpUsuarios.close();}}});
	$('#txtBuscar').simpleAutoComplete('class/usuarios.php',{
		autoCompleteClassName: 'autocomplete',
		selectedClassName: 'sel',
		attrCallBack: 'rel',
		identifier: 'Usuarios'
	 });
	 hdIdUsuarioAsignar=$('#hdIdUsuarioAsignar')[0];





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
	if(ddlUbicacion.value==''){alert('Es requerido seleccionar la ubicaci&oacute;n'); ddlUbicacion.focus(); return;}
	if(ddlPiso.value==''){alert('Es requerido seleccionar el piso'); ddlPiso.focus(); return;}
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

    if($('#motivo-especial').length == 0) {

    }else{
      if($('#motivo-especial').val()==""||$('#motivo-especial').val()==null) {
        $('#motivo-especial').focus();
        return;
      }
    }

	PopUpReservar.close();
    var motivo = $('#motivo-especial').val();
	var params=$.param({command:"ReservarBusquedaPorSala",						
						idsalaCurrent: idsalaCurrent,						
						Fecha: FechaCurrentSelected,
						HoraInicio: HoraInicioCurrent,
						HoraFinal: HoraFinalCurrent,						
						idubicacion: ubicacionCurrent,						
						idevento: $('#Datos').attr('idevento'),
						asistentes: $('#Datos').attr('capacidad'),
						FechaBuscador: FechaBuscador,
						UsuarioAsignar: hdIdUsuarioAsignar.value,
                        motivo_especial: motivo
					  });
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);
		$('#ConteResultadoBuscar').html(j.Cuadro);
		SetControlHora();	
		alert('La reserva se realizo correctamente.');
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
						idubicacion:ddlUbicacion.value,
						piso: id							  
					  });
	callServer({data:params,url:r},function(d){	
		$('#tdSalas').html(d);

        //Tineo
        $("#ddlSala").change(function() {
            //console.log(this);
            var me = $(this).find(":selected");
            //console.log(me.text());
            //console.log(me.hasClass("option-especial"));

            if(me.hasClass("option-especial")){
                $("body").append("<div class='alert-modal'>Esta sala es especial &iquest;Desea aun realizar la reserva?</div>");

                $(".alert-modal").dialog({
                    modal: true,
                    title: 'Sala especial',
                    resizable: false,
                    buttons: {
                        Ok: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
            }


        });

		$.unblockUI();
	});
}

function ListadoUsuarios(Accion){
	if(Accion=='Abrir') $('#txtBuscar').val('');
	txtBuscar=$.trim($('#txtBuscar').val());	
	params=$.param({command:'PopUpUsuarios',TextoBuscar:txtBuscar,pagina:0});
	callServer({data:params,url:r},function(d){
		$('#PopUpGrilla').html(d);
		$.unblockUI();
		if(Accion=='Abrir') PopUpUsuarios.open();
	});
}

function AsignarUsuario(ch){
	hdIdUsuarioAsignar.value=$(ch).attr('key');
	$('#UserCodigo').text($(ch).attr('codigo'));
	$('#UserNombre').text($(ch).attr('nombres'));
	$('#UserEmail').text($(ch).attr('email'));
	$('#btnAbrirAsignar').hide();
	$('#btnNoAsignar').show();
	$('#TBDatosUsuarioAsignar').show();
	PopUpUsuarios.close();
}

function NoAsignar(){
	hdIdUsuarioAsignar.value='';
	$('#UserCodigo').text('');
	$('#UserNombre').text('');
	$('#UserEmail').text('');
	$('#btnAbrirAsignar').show();
	$('#btnNoAsignar').hide();
	$('#TBDatosUsuarioAsignar').hide();
}


</script>
</head> 
<body>
<?
 $MasterPage->MostrarCabecera();
?>
<input type="hidden" value="" id="hdIdUsuarioAsignar" />
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
<td width="170" height="30" align="center" valign="middle" bgcolor="#F2F2F2" class="textopequeTITULO"><a href="busqueda.php" class="textoLink"><strong>B&uacute;squeda Avanzada</strong></a></td>
<td width="11">&nbsp;</td>
<td width="187" align="center" valign="middle" bgcolor="#DDEBFF" class="textopequeTITULO"><a href="javascript:;" class="textoLink">B&uacute;squeda por Sala</a></td>
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
      <td valign="middle" align="left">&nbsp;&nbsp;<input name="b1" type="button" class="gris" value=" Buscar Sala " onclick="BusquedaPorSala();" />
          <input name="b2" type="button" class="gris" value=" Nueva B&uacute;squeda " onclick="NuevaBusqueda();" />
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
<div id="ContPopUpReservar" style="overflow: hidden">
<table width="500" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <!-- Tineo -->
    <td>
      <div class='modal-especial' >
        <p>Esta sala es especial &iquest;Desea aun realizar la reserva?</p>
        <div>
          <button onclick="$(this).closeOverlay();">OK</button>
        </div>
      </div>
      <div id="ConteDetalle"></div>
    </td>
    <!-- Tineo -->

  </tr>
 <!-- -->
  <tr>
    <td height="10px"></td>
  </tr>
  <? if($_SESSION["tipo"]=='A'){ ?>
  <tr>
    <td>
      <table>
        <tr>
          <td>
            
          	<input type="button" value=" Asignar Reserva " class="gris" onclick="ListadoUsuarios('Abrir');" id="btnAbrirAsignar" />
            <input type="button" value=" No Asignar Reserva " class="gris" onclick="NoAsignar();" id="btnNoAsignar" style="display:none;" />            
          </td>
        </tr>
        <tr>
          <td id="TBDatosUsuarioAsignar" style="display:none;">
          <table width="484" border="0" cellpadding="2" cellspacing="1" bgcolor="#E7E7E7">
          	<tr>  
            	<td height="25" colspan="4" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">Esta reserva ser&aacute; asignado al siguiente usuario:</td>
            </tr>
            <tr>
            	<td width="72" valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">C&oacute;digo</td>
                <td width="7" align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>  
                <td width="405" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong id="UserCodigo"></strong></td>
            </tr>
            <tr>
            	<td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Usuario</td>  
                <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>  
                <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong id="UserNombre"></strong></td>
            </tr>
            <tr>
            	<td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Email</td>  
                <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>  
                <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong id="UserEmail"></strong></td>
            </tr>
          </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <? } ?>
</table>
</div>
<div id="ContPopUpAdvertencia">
<table width="430" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td id="ConteMensaje" align="left"></td>
  </tr>
</table>
</div>
<div id="PopUpListadoUsuarios">
<table cellspacing="0" cellpadding="0" border="0" width="702px">
  <tr bgcolor="#FFFFFF"> 
	<td width="51"></td>
    <td width="122"><strong>Buscar usuario:</strong></td>
	<td width="392"><input type="text" maxlength="200" id="txtBuscar" style="width:350px;font-family: Arial, Helvetica, sans-serif;font-size: 12px;color: #3F3F3F;text-decoration:none" /></td>
    <td width="137"><input type="button" value=" Buscar " onClick="ListadoUsuarios('Buscar');" style="font-family: Arial, Helvetica, sans-serif;font-size: 12px;color: #3F3F3F;text-decoration:none" /></td>
  </tr>
  <tr>
    <td colspan="4">
      <div style="overflow-y:auto; min-height:300px; background:#ffffff" id="PopUpGrilla"></div>
    </td>
  </tr>         
  </table>      
</div>
<!-- BEGIN Tineo -->
<script type="application/javascript">


  // define the function within the global scope
  $.fn.closeOverlay = function() {
    $(".modal-especial").hide();$("#users-contain").fadeOut("slow");
  };



</script>
<!-- END Tineo -->
<?
  $MasterPage->MostrarFooter();
?>
</body>
</html>
