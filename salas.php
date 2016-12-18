<?
  include("class/salas.php");
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

<script type="text/javascript" src="js/JQuery/ControlsFormat.js"></script>
<script type="text/javascript">
var r='class/salas.php';
var idsala,ddlUbicacion,txtNombre,txtPiso,txtCapacidad,txtCaract;
var campo='nombre';
var orden='asc';
$(function(){
	ddlUbicacion=$("#ddlUbicacion")[0];
	txtNombre=$("#txtNombre")[0];
	txtPiso=$("#txtPiso")[0];
	txtCapacidad=$("#txtCapacidad")[0];
	txtCaract=$("#txtCaract")[0];	
	$('#txtCaract').keypress(function(e){if(e.which == 13){return false;}});
	$(".integer").format({precision: 0,autofix:true});	
});

function Nuevo(){
	Loading();
	idsala=0;
	ddlUbicacion.value='';
	txtNombre.value='';
	txtPiso.value='';
	txtCapacidad.value='';
	txtCaract.value='';
	CantidadLetras($('#txtCaract'),300,'_contador');
	UploadImage('tdImagen','ImgImagen','salas','');
	$('#tbDetalle .chEventos').each(function(i,e){e.checked=false}); 
    $('#tbDetalle .chComplementos').each(function(i,e){e.checked=false});
    $('#ChE1, #ChP1').attr('checked',true);
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

function Filtar(){
	PaginaActual=0;
	campo='nombre';
	orden='asc';
	params = $.param({command:"Listar",pagina:0,campo:campo,orden:orden,UbicacionFiltro:$('#ddlUbicacionFiltro').val()});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}

function Paginar(pagina){
	PaginaActual=pagina;
	params = $.param({command:"Listar",pagina:pagina,campo:campo,orden:orden,UbicacionFiltro:$('#ddlUbicacionFiltro').val()});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}

function OrdenarGrid(c,o){
	PaginaActual=0;
	campo=c;
	orden=o;
	params = $.param({command:"Listar",pagina:0,campo:c,orden:o,UbicacionFiltro:$('#ddlUbicacionFiltro').val()});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}


function Grabar(){
	var ArrIdsEventos=[];
	var ArrIdsComplementos=[];
	if($.trim(ddlUbicacion.value)==""){alert("Por favor seleccione la ubicación.");ddlUbicacion.focus();return;}
	if($.trim(txtNombre.value)==""){alert("Por favor ingrese el nombre de la sala.");txtNombre.focus();return;}
	if($.trim(txtPiso.value)==""){alert("Por favor ingrese el piso.");txtPiso.focus();return;}
	if($.trim(txtCapacidad.value)==""){alert("Por favor ingrese la capacidad.");txtCapacidad.focus();return;}	
	var FileImagen=$("#ImgImagen").length>0?$('#ImgImagen').attr("img"):'';
	
	$('#tbDetalle .chEventos').each(function(i,e){if(e.checked) ArrIdsEventos.push($(e).attr('key'))});	
	if(ArrIdsEventos.length==0){alert("Seleccione el tipo de evento."); return;}
	
	$('#tbDetalle .chComplementos').each(function(i,e){if(e.checked) ArrIdsComplementos.push($(e).attr('key'))});	
	
	var params = $.param({command:"Guardar",
						  idsala:idsala,
						  idubicacion:$.trim(ddlUbicacion.value),
						  nombre:$.trim(txtNombre.value),
						  piso:$.trim(txtPiso.value),
						  capacidad:$.trim(txtCapacidad.value),
						  caracteristicas:$.trim(txtCaract.value),
						  ArrIdsEventos:ArrIdsEventos.join(','),
						  ArrIdsComplementos:ArrIdsComplementos.join(','),
						  imagen:FileImagen,
						  pagina:PaginaActual,
						  campo:campo,
						  orden:orden,
						  UbicacionFiltro:$('#ddlUbicacionFiltro').val(),

                            //Tineo
                          sala_especial: $('#sala_especial').is(":checked")
						});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		alert('Los datos ingresados fue guardado correctamente.');
		$('#tbInicio').show();
		$('#tbDetalle').hide();
		$.unblockUI();		
	});
}

function Editar(id){
	$('#tbDetalle .chEventos').each(function(i,e){e.checked=false}); 
    $('#tbDetalle .chComplementos').each(function(i,e){e.checked=false});
	idsala=id;
	params = $.param({command:"Editar",idsala:id});
	callServer({data:params,url:r},function(d){		
		j=toEval(d);
		ddlUbicacion.value=j.idubicacion;
		txtNombre.value=j.nombre;
		txtPiso.value=j.piso;
		txtCapacidad.value=j.capacidad;
		txtCaract.value=j.caracteristicas;
		CantidadLetras($('#txtCaract'),300,'_contador');
		UploadImage('tdImagen','ImgImagen','salas',j.imagen);
		$.each(j.eventos.split(','),function(i,s){$('#ChE'+s).attr('checked',true);});
		$.each(j.complementos.split(','),function(i,s){$('#ChP'+s).attr('checked',true);});		

        //Tineo
        $("#sala_especial").attr('checked',(j.sala_especial!="true"?false:true));
        //console.log('sala',j.sala_especial);
        //console.log(d);
        $('#tbInicio').hide();
		$('#tbDetalle').show();
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
    <td height="35" ><div align="center">
    Filtrar salas por ubicación&nbsp;&nbsp;
    <select class="FRM" id="ddlUbicacionFiltro" onchange="Filtar();">
     <option value="">  Ver Todos  </option>
     <?
     	$Result=mysql_query("select idubicacion,nombre from ubicaciones");
		while($Campo=mysql_fetch_array($Result)){
			echo '<option value="'.$Campo['idubicacion'].'">'.$Campo['nombre'].'</option>';
		}
	 ?>
    </select>
    </div></td>     
 </tr>
 <tr>
    <td height="35" ><div align="right"><input name="button" type="button" class="FRM" value=" Nueva Sala " onclick="Nuevo();" /></div></td>     
 </tr>
 <tr>
    <td id="td_Listado" align="center">
	 <? 
		$Datos = new Salas();
		$Datos->GetList(0,'nombre','asc','');
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
    <td width="160" height="35" bgcolor="#FFFFFF"><a class="textopeque">Ubicación:</a></td>
    <td width="790" bgcolor="#FFFFFF">
    <select class="FRM" id="ddlUbicacion">
     <option value=""></option>
     <?
     	$Result=mysql_query("select idubicacion,nombre from ubicaciones");
		while($Campo=mysql_fetch_array($Result)){
			echo '<option value="'.$Campo['idubicacion'].'">'.$Campo['nombre'].'</option>';
		}
	 ?>
    </select>
    &nbsp;*</span>
    
    </td>
  </tr>
  <tr>
    <td width="160" height="23" bgcolor="#FFFFFF"><a class="textopeque">Nombre:</a></td>
    <td width="790" bgcolor="#FFFFFF"><input type="text" class="FRM" id="txtNombre"  size="82" maxlength="100" /><span  class="textopeque">&nbsp;*</span></td>
  </tr>
  <tr>
    <td width="160" height="25" bgcolor="#FFFFFF"><a class="textopeque">Piso:</a></td>
    <td width="790" bgcolor="#FFFFFF">
    <input type="text" class="FRM integer" id="txtPiso" maxlength="2"  size="15" /><span  class="textopeque">&nbsp;*&nbsp;&nbsp;&nbsp;&nbsp;</span>
    </td>
  </tr>   
  <tr>
    <td width="160" height="30" bgcolor="#FFFFFF"><a class="textopeque">Capacidad:</a></td>
    <td width="790" bgcolor="#FFFFFF">
    <input type="text" class="FRM integer" id="txtCapacidad" size="15" maxlength="5" onkeyup="ValidEntero(this)" onblur="ValidEntero(this)" /><span  class="textopeque">&nbsp;*</span>
    </td>
  </tr>   
  <tr>
    <td width="160" height="30" bgcolor="#FFFFFF"><a class="textopeque">Imagen:</a></td>
    <td width="790" bgcolor="#FFFFFF" id="tdImagen"></td>
  </tr>
  <tr>
    <td width="160" height="30" bgcolor="#FFFFFF"><a class="textopeque">Tipo de Evento:</a></td>
    <td width="790" bgcolor="#FFFFFF">
   <? 
	$ResultEventos=mysql_query("select idevento,nombre from eventos");
	while($Campos=mysql_fetch_array($ResultEventos)){
		echo ' <input type="radio" name="radio" id="ChE'.$Campos['idevento'].'" key="'.$Campos['idevento'].'" class="chEventos" />
		       <label for="ChE'.$Campos['idevento'].'">'.htmlentities($Campos['nombre']).'</label>&nbsp;&nbsp;&nbsp;		
		     ';
	}
  ?>
    </td>
  </tr>
  <tr>
    <td width="160" height="40" bgcolor="#FFFFFF"><a class="textopeque">Complementos:</a></td>
    <td width="790" bgcolor="#FFFFFF">
   <? 
	$ResultEventos=mysql_query("select idcomplemento,nombre from complementos order by orden asc");
	while($Campos=mysql_fetch_array($ResultEventos)){
		echo ' <input type="checkbox" id="ChP'.$Campos['idcomplemento'].'" key="'.$Campos['idcomplemento'].'" class="chComplementos" />
		       <label for="ChP'.$Campos['idcomplemento'].'">'.htmlentities($Campos['nombre']).'</label>&nbsp;&nbsp;&nbsp;		
		     ';
	}
  ?>
    </td>
  </tr>
  <tr>
    <td height="30" bgcolor="#FFFFFF" valign="top"><a class="textopeque">Caracter&iacute;sticas:</a></td>
    <td bgcolor="#FFFFFF"><textarea class="FRM" id="txtCaract" style="width:420px; height:75px;" onKeyUp="CantidadLetras(this,300,'_contador');" onblur="CantidadLetras(this,300,'_contador');"></textarea></td>
  </tr>
  <tr> 
    <td></td>
    <td bgcolor="#FFFFFF" valign="top" colspan="2" class="textopeque"><a >Contador de caracteres </a><span id="_contador"></span>  de 300</td>    
  </tr>
      <!-- BEGIN  Tineo -->
        <tr >
            <td height="30">Sala especial: </td>
            <td><input type="checkbox" id="sala_especial"/></td>
        </tr>
        <!-- END Tineo -->


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
