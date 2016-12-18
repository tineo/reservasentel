<?
  include("class/ubicacion.php");
  include("MasterPage.php");
  $MasterPage = new MasterPage();  
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Reservas de Salas :: entel</title>
<?
 $MasterPage->MostrarScriptCss();  
?>
<script type="text/javascript">
var r='class/ubicacion.php';
var idubicacion,txtNombre,txtHorarioInicio,txtHorarioFinal,txtCaract;
var campo='nombre';
var orden='asc';
$(function(){
	txtNombre=$("#txtNombre")[0];
	txtHorarioInicio=$("#txtHorarioInicio")[0];
	txtHorarioFinal=$("#txtHorarioFinal")[0];
	txtCaract=$("#txtCaract")[0];	
	$('#txtCaract').keypress(function(e){if(e.which == 13){return false;}});
	
	//Para el rango de horas
	$("#txtHorarioInicio, #txtHorarioFinal").timePicker({
		startTime: "06:00",
		endTime: new Date(0, 0, 0, 23, 00, 0),
		show24Hours: true,
		separator: ':',
		step: 30		
	});

    var oldTime = $.timePicker("#txtHorarioInicio").getTime();
    $("#txtHorarioInicio").change(function() {
		if ($("#txtHorarioFinal").val()) { // Only update when second input has a value.
			// Calculate duration.
			var duration = ($.timePicker("#txtHorarioFinal").getTime() - oldTime);
			var time = $.timePicker("#txtHorarioInicio").getTime();
			// Calculate and update the time in the second input.
			$.timePicker("#txtHorarioFinal").setTime(new Date(new Date(time.getTime() + duration)));
			oldTime = time;
		}
    });
    $("#txtHorarioFinal").change(function() {
		if($.timePicker("#txtHorarioInicio").getTime() > $.timePicker(this).getTime()) {
			$(this).addClass("error");
		}
		else {
			$(this).removeClass("error");
		}
    });
});

function Nuevo(){
	Loading();
	idubicacion=0;
	txtNombre.value='';
	txtHorarioInicio.value='8:30';
	txtHorarioFinal.value='18:00';
	txtCaract.value='';
	CantidadLetras($('#txtCaract'),300,'_contador');
	$('#tbInicio').hide();
    $('#tbDetalle').show();
    $.unblockUI();
}

function Regresar(){
	Loading();	
	$('#tbInicio').show();
    $('#tbDetalle').hide();
    $.unblockUI();
}

function Paginar(pagina){
	PaginaActual=pagina;
	params = $.param({command:"Buscar",pagina:pagina});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}

function OrdenarGrid(c,o){
	PaginaActual=0;
	campo=c;
	orden=o;
	params = $.param({command:"Buscar",pagina:0,campo:c,orden:o});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}

function Editar(id){
	idubicacion=id;
	params = $.param({command:"Editar",idubicacion:id});
	callServer({data:params,url:r},function(d){		
		j=toEval(d);
		txtNombre.value=j.nombre;
		txtHorarioInicio.value=j.horario_inicio;
		txtHorarioFinal.value=j.horario_final;
		txtCaract.value=j.caracteristicas;
		CantidadLetras($('#txtCaract'),300,'_contador');		
		$('#tbInicio').hide();
		$('#tbDetalle').show();
		$.unblockUI();
	});
}

function Grabar(){
	if($.trim(txtNombre.value)==""){alert("Por favor ingrese el nombre de la ubicación.");txtNombre.focus();return;}
	if($.trim(txtHorarioInicio.value)==""){alert("Por favor ingrese el horario de inicio de la antención.");txtHorarioInicio.focus();return;}
	if($.timePicker("#txtHorarioInicio").getTime() >= $.timePicker("#txtHorarioFinal").getTime()){alert("El horario de inicio no debe mayor o igual al horario final.");txtHorarioInicio.focus();return;}	
	if($.trim(txtHorarioFinal.value)==""){alert("Por favor ingrese el horario final de la antención.");txtHorarioFinal.focus();return;}
	//if($.timePicker("#txtHorarioInicio").getTime() < $.timePicker("#txtHorarioFinal").getTime()){alert("El horario de final no debe mayor al horario inicio.");txtHorarioFinal.focus();return;}
	var params = $.param({command:"Guardar",
						  idubicacion:idubicacion,
						  nombre:$.trim(txtNombre.value),
						  horario_inicio:$.trim(txtHorarioInicio.value),
						  horario_final:$.trim(txtHorarioFinal.value),
						  caracteristicas:$.trim(txtCaract.value),
						  pagina:PaginaActual,
						  campo:campo,
						  orden:orden
						});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		alert('Los datos ingresados fue guardado correctamente.');
		$('#tbInicio').show();
		$('#tbDetalle').hide();
		$.unblockUI();		
	});
}

function Eliminar(id){
	if(!confirm("Confirmar si desea eliminar el la ubicación seleccionado.")) return;
	params = $.param({command:"Eliminar",id:id,pagina:PaginaActual,campo:campo,orden:orden});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
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
    <td>&nbsp;</td>
</tr>
<tr>
<td width="910" height="50" align="left" valign="middle" bgcolor="#F2F2F2"><span class="tituloSERV"><? echo $TituloPaginaActual;?></span></td>
</tr>
 <tr>
    <td height="35" ><div align="right"><input name="button" type="button" class="FRM" value=" Nueva Ubicación " onclick="Nuevo();" /></div></td>
     
  </tr>
  <tr>
    <td id="td_Listado" align="center">
	 <? 
		$Datos = new Ubicacion();
		$Datos->GetList(0,'nombre','asc');
      ?>
    </td>
  </tr>
 </table> 
 

<table width="910" border="0" cellpadding="0" cellspacing="0" id="tbDetalle" style="display:none;" >
 <tr>
    <td>&nbsp;</td>
</tr>
<tr>
<td width="910" height="50" valign="middle" align="left" bgcolor="#F2F2F2"><span class="tituloSERV"><? echo $TituloPaginaActual;?></span></td>
</tr>
<tr>
    <td colspan="4"><table width="900" border="0" cellpadding="3" cellspacing="1" style="margin-top:6px; margin-bottom:6px;">
        <tr>
          <td height="25" bgcolor="#FFFFFF"><div align="right"><input name="button" type="button" class="FRM" value=" Regresar " onclick="Regresar();" /></div></td>
      </tr>                      
    </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="left">
    <table width="910" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="160" height="35" bgcolor="#FFFFFF"><a class="textopeque">Nombre:</a></td>
    <td width="790" bgcolor="#FFFFFF"><input type="text" class="FRM" id="txtNombre"  size="82" maxlength="100" /><span  class="textopeque">&nbsp;*</span></td>
  </tr>
  <tr>
    <td width="160" height="35" bgcolor="#FFFFFF"><a class="textopeque">Horario Atención:</a></td>
    <td width="790" bgcolor="#FFFFFF">
    </div><span  class="textopeque">Desde: </span><input type="text" class="FRM" id="txtHorarioInicio" maxlength="5"  size="15" onkeyup="ValidEntero(this,':')" onblur="ValidEntero(this,':')" /><span  class="textopeque">&nbsp;*&nbsp;&nbsp;&nbsp;&nbsp;</span>
    <span  class="textopeque">Hasta: </span><input type="text" class="FRM" id="txtHorarioFinal" size="15" maxlength="5" onkeyup="ValidEntero(this,':')" onblur="ValidEntero(this,':')" /><span  class="textopeque">&nbsp;*</span></div>
    </td>
  </tr> 
    <td height="40" bgcolor="#FFFFFF" valign="top"><a class="textopeque">Caracter&iacute;sticas:</a></td>
    <td bgcolor="#FFFFFF"><textarea class="FRM" id="txtCaract" style="width:420px; height:75px;" onKeyUp="CantidadLetras(this,300,'_contador');" onblur="CantidadLetras(this,300,'_contador');"></textarea></td>
  </tr>
  <tr> 
    <td></td>
    <td bgcolor="#FFFFFF" valign="top" colspan="2"><a class="textopeque">Contador de caracteres </a><span id="_contador"></span>  de 300</td>    
  </tr>
   <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="30" bgcolor="#FAFAFA">&nbsp;</td>
    <td bgcolor="#FAFAFA"><input  onClick="Grabar()" type="button" class="FRM" value=" Grabar " /></td>
  </tr>
      </table>
    </td>
  </tr>
    </table>
<!--Detalle Fin-->
<?
  $MasterPage->MostrarFooter();
?>
</body>
</html>
