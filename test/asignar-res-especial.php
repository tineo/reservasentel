<?
  include("class/asignar-res-especial.php");
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
  //ESTA LINEA DE AQUI ABAJO SE CONTROLA EL TIEMPO DEL CALENDARIO DESDE HASTA, SOLO HEMOS QUITADO EL +1 EN EL DIA PARA QUE COJA EL DIA ACTUAL. 
  //$FechaDesdeReferencia = date("Y").','.(date("m")-1).','.(date("d")+1);
  $FechaDesdeReferencia = date("Y").','.(date("m")-1).','.date("d");
  $FechaHastaReferencia = (date("Y")+1).','.(date("m")-1).','.date("d");
 ?>
<link rel="stylesheet" type="text/css" href="js/JQuery/AutoComplete/simpleAutoComplete.css" />
<script type="text/javascript" src="js/JQuery/AutoComplete/simpleAutoComplete.js"></script>    
<script type="text/javascript" src="js/JQuery/ControlsFormat.js"></script>
<script type="text/javascript">txtNombre,txtFecInicio,txtFecFinal,ddlCantidad
var r='class/asignar-res-especial.php';
var idespecial,txtFecInicio,txtNombre,txtFecFinal,ddlCantidad,hdIdUsuario,PopUpUsuarios;
var BuscarUsuario,txtBuscar;
var campo='nombres';
var orden='asc';
$(function(){
	hdIdUsuario=$("#hdIdUsuario")[0];
	txtNombre=$("#txtNombre")[0];
	txtFecInicio=$("#txtFecInicio")[0];	
	txtFecFinal=$("#txtFecFinal")[0];
	ddlCantidad=$("#ddlCantidad")[0];
	var dates = $('#txtFecInicio, #txtFecFinal').datepicker({
		minDate: new Date(<? echo $FechaDesdeReferencia; ?>),
		maxDate: new Date(<? echo $FechaHastaReferencia; ?>),
		changeMonth: true,
		changeYear: true,
		showOn: 'button',
		buttonImage: 'images/calendario.gif',
		buttonImageOnly: true,
		onSelect: function(selectedDate) {
			var option = this.id == "txtFecInicio" ? "minDate" : "maxDate";
			var instance = $(this).data("datepicker");
			var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
			dates.not(this).datepicker("option", option, date);
		}
	});
	
	$('#NombresUserAutocomplete').simpleAutoComplete('class/usuarios.php',{
		autoCompleteClassName: 'autocomplete',
		selectedClassName: 'sel',
		attrCallBack: 'rel',
		identifier: 'Usuarios'
	 });
	 $('#txtBuscar').simpleAutoComplete('class/usuarios.php',{
		autoCompleteClassName: 'autocomplete',
		selectedClassName: 'sel',
		attrCallBack: 'rel',
		identifier: 'Usuarios'
	 });
	BuscarUsuario='';
	PopUpUsuarios=new Dialogo("PopUpListadoUsuarios",{width:722,title:'Búsqueda de usuarios',buttons:{'Cancelar':function(){PopUpUsuarios.close();}}});	
});

function Nuevo(){
	Loading();
	idespecial=0;
	txtNombre.value='';
	txtFecInicio.value='';	
	txtFecFinal.value='';
	ddlCantidad.value='';
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

function Buscar(){
	PaginaActual=0;
	BuscarUsuario=$.trim($('#NombresUserAutocomplete').val());
	params = $.param({command:"Buscar",pagina:0,BuscarUsuario:BuscarUsuario});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}

function Paginar(pagina){
	PaginaActual=pagina;
	params = $.param({command:"Buscar",pagina:pagina,campo:campo,orden:orden,BuscarUsuario:BuscarUsuario});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}

function OrdenarGrid(c,o){
	PaginaActual=0;
	campo=c;
	orden=o;
	params = $.param({command:"Buscar",pagina:0,campo:c,orden:o,BuscarUsuario:BuscarUsuario});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}

function Editar(id){
	idespecial=id;
	params = $.param({command:"Editar",idespecial:id});
	callServer({data:params,url:r},function(d){		
		j=toEval(d);
		hdIdUsuario.value=j.idusuario;
		txtNombre.value=j.nombres;
		txtFecInicio.value=j.fecInicio;		
		txtFecFinal.value=j.fecFinal;
		ddlCantidad.value=j.cantidad;		
		$('#tbInicio').hide();
		$('#tbDetalle').show();
		$.unblockUI();
	});
}

function Grabar(){
	if(hdIdUsuario.value==""){alert("Por favor seleccione el usuario.");txtNombre.focus();return;}
	if(txtFecInicio.value==""){alert("Por favor seleccione la fecha de inicio.");txtFecInicio.focus();return;}
	if(txtFecFinal.value==""){alert("Por favor seleccione la fecha de final.");txtFecFinal.focus();return;}
	if(ddlCantidad.value==""){alert("Por favor seleccione la cantidad.");ddlCantidad.focus();return;}
	var params = $.param({command:"Guardar",
						  idespecial:idespecial,
						  IdUsuario:hdIdUsuario.value,
						  FecInicio:txtFecInicio.value,
						  FecFinal:txtFecFinal.value,
						  Cantidad:ddlCantidad.value,
						  pagina:PaginaActual,
						  BuscarUsuario:BuscarUsuario
						});
	callServer({data:params,url:r},function(d){		
		if($.trim(d)=='Existe'){
			txtFecInicio.focus()
			alert('El código: '+$.trim(txtFecInicio.value)+' ya esta registrado, por favor ingresar otro código.');			
		}else{
			$('#td_Listado').html(d);
			alert('Los datos ingresados fue guardado correctamente.');
			$('#tbInicio').show();
			$('#tbDetalle').hide();
		}
		$.unblockUI();		
	});
}

function Eliminar(id){
	if(!confirm("Confirmar si desea eliminar el usuario y todos su reservas.")) return;
	params = $.param({command:"Eliminar",id:id,pagina:PaginaActual,campo:campo,orden:orden,BuscarUsuario:BuscarUsuario});
	callServer({data:params,url:r},function(d){		
		$('#td_Listado').html(d);
		$.unblockUI();
	});
}

function AbrirUsuarios(Accion){
	if(Accion=='Abrir') $('#txtBuscar').val('');
	txtBuscar=$.trim($('#txtBuscar').val());	
	params=$.param({command:'PopUpUsuarios',TextoBuscar:txtBuscar,pagina:0});
	callServer({data:params,url:r},function(d){
		$('#PopUpGrilla').html(d);
		$.unblockUI();
		if(Accion=='Abrir') PopUpUsuarios.open();
	});
}

function PaginarBuscar(i){
	params=$.param({command:'PopUpUsuarios',TextoBuscar:txtBuscar,pagina:i});
	callServer({data:params,url:r},function(d){
		$('#PopUpGrilla').html(d);
		$.unblockUI();
	});
}

function AsignarUsuario(ch){
	hdIdUsuario.value=$(ch).attr('key');
	txtNombre.value=$(ch).attr('data');
	PopUpUsuarios.close();
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
    <td align="center" >
    <span> Buscar por usuario:&nbsp;</span>
    <input type="text" id="NombresUserAutocomplete" name="NombresUser" class="FRM" autocomplete="off" style="width: 350px; height: 23px;" />&nbsp;&nbsp;
    <input name="button" type="button" class="FRM" value="Buscar" onclick="Buscar();" />    
    </td>     
  </tr>
 <tr>
    <td height="35" ><div align="right"><input name="button" type="button" class="FRM" value=" Nueva Asignación " onclick="Nuevo();" /></div></td>     
  </tr>
  <tr>
    <td id="td_Listado" align="center">
	 <? 
		$Datos = new Usuarios();
		$Datos->GetList(0,'');
      ?>
    </td>
  </tr>
 </table> 
 
 
<input type="hidden" value="" id="hdIdUsuario" />
<table width="910" border="0" align="center" cellpadding="0" cellspacing="0" id="tbDetalle" style="display:none;" >
 <tr>
    <td>&nbsp;</td>
</tr>
<tr>
<td width="910" height="50" align="left" valign="middle" bgcolor="#F2F2F2"><span class="tituloSERV"><? echo $TituloPaginaActual;?></span></td>
</tr>
<tr>
    <td><table width="900" border="0" align="center" cellpadding="3" cellspacing="1" style="margin-top:6px; margin-bottom:6px;">
        <tr>
          <td height="25" bgcolor="#FFFFFF"><div align="right"><input name="button" type="button" class="FRM" value="Regresar" onclick="Regresar();" /></div></td>
      </tr>                      
    </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td  align="left">
    <table width="910" border="0" cellspacing="0" cellpadding="0">
 <tr>
    <td width="160" height="30" bgcolor="#FFFFFF"><a class="textopeque">Usuario:</a></td>
    <td width="790" bgcolor="#FFFFFF"><input type="text" class="FRM" id="txtNombre" maxlength="100"  size="60" readonly="readonly" /><span  class="textopeque">&nbsp;*</span>
    	<input type="button" class="FRM" value=" Buscar Usuario " onclick="AbrirUsuarios('Abrir');" />
    </td>
  </tr>
  <tr>
    <td width="160" height="30" bgcolor="#FFFFFF"><a class="textopeque">Fecha Inicio:</a></td>
    <td width="790" bgcolor="#FFFFFF"><input type="text" class="FRM" id="txtFecInicio"  size="15" maxlength="10" /><span  class="textopeque">&nbsp;*</span></td>
  </tr>  
  <tr>
    <td height="30"><a class="textopeque">Fecha Final:</a></td>
    <td><input type="text" class="FRM" id="txtFecFinal" size="15" maxlength="10" /><span  class="textopeque">&nbsp;*</span></td>
    </tr>
  <tr>
    <td height="30" bgcolor="#FFFFFF"><a class="textopeque">Cantidad:</a></td>
    <td bgcolor="#FFFFFF">
     <select id="ddlCantidad" class="FRM">
      <option value="">Seleccione aquí</option>
      <option value="1">1</option>
      <option value="2">2</option>
      <option value="3">3</option>
      <option value="4">4</option>
      <option value="5">5</option>
      <option value="6">6</option>
      <option value="7">7</option>
      <option value="8">8</option>
      <option value="9">9</option>
      <option value="10">10</option>
      <option value="20">20</option>
      <option value="30">30</option>
      <option value="40">40</option>
      <option value="50">50</option>
      <option value="100">100</option>
      <option value="150">150</option>
     </select><span  class="textopeque">&nbsp;*</span>
    
    </td>
  </tr>
  
  <tr>
    <td height="30" bgcolor="#FAFAFA">&nbsp;</td>
    <td bgcolor="#FAFAFA"><input  onClick="Grabar()" type="button" class="FRM" value="Grabar" /></td>
  </tr>
      </table>
    </td>
  </tr>
    </table>
<!--Detalle Fin-->
<?
  $MasterPage->MostrarFooter();
?>
<div id="PopUpListadoUsuarios">
<table cellspacing="0" cellpadding="0" border="0" width="702px">
  <tr bgcolor="#FFFFFF"> 
	<td width="51"></td>
    <td width="122"><strong>Buscar usuario:</strong></td>
	<td width="392"><input type="text" maxlength="200" id="txtBuscar"  style="width:350px;font-family: Arial, Helvetica, sans-serif;font-size: 12px;color: #3F3F3F;text-decoration:none" /></td>
    <td width="137"><input type="button" value=" Buscar " onClick="AbrirUsuarios('Buscar');" style="font-family: Arial, Helvetica, sans-serif;font-size: 12px;color: #3F3F3F;text-decoration:none" /></td>
  </tr>
  <tr>
    <td colspan="4">
      <div style="overflow-y:auto; min-height:300px; background:#ffffff" id="PopUpGrilla"></div>
    </td>
  </tr>         
  </table>      
</div>
</body>
</html>
