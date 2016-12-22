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
 
 

 
 
 //
 /*$ResultMaximoHora=mysql_query("select hora MaximoDeHoraPorDia from config_horas_dias where activo=1");
 $CampoDia=mysql_fetch_array($ResultMaximoHora);
 $MaximoDeHoraPorDia=$CampoDia['MaximoDeHoraPorDia'];*/
 //echo 'Fernando '.RestarHoras('10:00:00','11:00:00');


//$_SESSION["tipo"]
?>
<link rel="stylesheet" type="text/css" href="js/JQuery/AutoComplete/simpleAutoComplete.css" />
<script type="text/javascript" src="js/JQuery/AutoComplete/simpleAutoComplete.js"></script> 
<script type="text/javascript" src="js/JQuery/ControlsFormat.js"></script>
<script type="text/javascript">
var r='class/busqueda.php';
var ddlUbicacion,ddlCapacidad,ddlProyector,ddlTelefono,txtFecha,txtHoraInicio,txtHoraFinal;
var idsalaCurrent,FechaCurrent,HoraInicioCurrent,HoraFinalCurrent;
var PopUpAdvertencia;
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
	
	SetRangoHoras('8:30','18:00','9:00','18:00');
	
	ddlUbicacion=$("#ddlUbicacion")[0];
	ddlCapacidad=$("#ddlCapacidad")[0];
	ddlProyector=$("#ddlProyector")[0];
	ddlTelefono=$("#ddlTelefono")[0];
	txtFecha=$("#txtFecha")[0];
	txtHoraInicio=$("#txtHoraInicio")[0];
	txtHoraFinal=$("#txtHoraFinal")[0];
	
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
    if($('#motivo-especial').length == 0) {

    }else{
        if($('#motivo-especial').val()==""||$('#motivo-especial').val()==null){
            $('#motivo-especial').focus();
            return;
        }
    }

    var motivo = $('#motivo-especial').val();
	var params=$.param({command:"ReservarBusquedaAvanzada",						
						idsalaCurrent: idsalaCurrent,
						Fecha: FechaCurrent,
						HoraInicio: HoraInicioCurrent,
						HoraFinal: HoraFinalCurrent,
						idubicacion: $('#Datos').attr('idubicacion'),
						idevento: $('#Datos').attr('idevento'),
						asistentes: $('#Datos').attr('capacidad'),
						UsuarioAsignar: hdIdUsuarioAsignar.value,
						motivo_especial: motivo
					  });
	callServer({data:params,url:r},function(d){		
		var j=toEval(d);
		if($.trim(j.Validar)==='DENEGADO'){
			$.unblockUI();
			$('#ConteMensaje').html(j.Mensaje);			
			PopUpAdvertencia.open();			
		}else{
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
        /*if ($("#txtHoraFinal").val()) { // Only update when second input has a value.
            // Calculate duration.
            var duration = ($.timePicker("#txtHoraFinal").getTime() - oldTime);
            var time = $.timePicker("#txtHoraInicio").getTime();
            // Calculate and update the time in the second input.
            $.timePicker("#txtHoraFinal").setTime(new Date(new Date(time.getTime() + duration)));
            oldTime = time;
        }*/
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
<!--Contenido Inicio-->
<input type="hidden" value="" id="hdIdUsuarioAsignar" />
<table width="910" border="0" align="center" cellpadding="0" cellspacing="0" id="tbInicio">
 <tr>
    <td width="25">&nbsp;</td>
</tr>
<tr>
<td height="50" align="left" valign="middle" bgcolor="#F2F2F2" colspan="4"><span class="tituloSERV"><? echo $TituloPaginaActual;?></span></td>
</tr>
<? //ESTE TR ES PARA EL MENSAJE DE RESTRICCION DE RESERVA DE CAFETERIA ?>
<tr>
<td height="38" align="left" valign="middle" colspan="4"><span class="mmsbusqueda">Recuerda que según el Reglamento de Reserva de Salas de Reuniones las Cafeter&iacute;as del piso 18 de PR y del piso 6 de CO SB no se pueden reservar entre 11:30am y 4:30pm, de realizar una reserva el sistema no lo reconocer&aacute;.</span></td>
</tr>
<tr>
<td height="10" align="left" valign="middle" bgcolor="#FFFFFF" colspan="4">
	<?
    	//$Results=mysql_query(" select date_format(now(),'%d/%m/%Y %H:%i:%s') fecha;");
//		$CampoHora=mysql_fetch_array($Results);
//		echo 'Fecha Hora: PHP: '.date("d/m/Y H:i:s");
//		echo '<br />Fecha Hora: MYSQL: '.$CampoHora['fecha'];
	?>
</td>
</tr>
<tr>    
<td width="150" height="30" align="center" valign="middle" bgcolor="#DDEBFF" class="textopequeTITULO"><a href="javascript:;" class="textoLink"><strong>B&uacute;squeda Avanzada</strong></a></td>
<td width="11">&nbsp;</td>
<td width="167" align="center" valign="middle" bgcolor="#F2F2F2" class="textopequeTITULO"><a href="busqueda-x-salas.php" class="textoLink">B&uacute;squeda por Sala</a></td>
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
       <select class="gris" id="ddlUbicacion" style="width:210px" onchange="Hora(this.value);">
        <option value="" selected="selected"></option>
        <? 
            /*$ResultUbicacion=mysql_query("select s.idsala, CONCAT_WS(' ',u.nombre,'-','P�so',s.piso,'-',s.nombre) as nombres 
                                          from salas s,ubicaciones u
                                          where u.idubicacion=s.idubicacion");*/
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
      <td valign="middle"><input name="b1" type="button" class="gris" value="Buscar Sala" onclick="BusquedaAvanzanda(0);" />
          <input name="b2" type="button" class="gris" value="Nuevo" onclick="NuevaBusqueda();" /></td>
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
            <td valign="middle" align="left"><span class="SUBTituloC">COMPLEMENTOS </span><span class="datos">P: Proyector | T: Tel&eacute;fono | Z: Pizarra | E: Ecran</span></td>
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
    <!--<td width="2" align="left" valign="top" id="ConteDetalle"></td> -->
    <td width="2" align="left" valign="top">
    	<table>
        	<tr>
                <!-- Tineo -->
            	<td>
                    <div class='modal-especial' >
                        <p>Por favor indica el motivo de tu reunión con videoconferencia.</p>
                        <div>
                            <button onclick="$(this).closeOverlay();">Aceptar</button>
                        </div>
                    </div>
                    <div id="ConteDetalle"></div>
                </td>
                <!-- Tineo -->
            </tr>
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
    </td> 
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
