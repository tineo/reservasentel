<?
  include("class/usuarios.php");
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
<link rel="stylesheet" type="text/css" href="js/JQuery/AutoComplete/simpleAutoComplete.css" />
<script type="text/javascript" src="js/JQuery/AutoComplete/simpleAutoComplete.js"></script>    
<script type="text/javascript" src="js/JQuery/ControlsFormat.js"></script>
<script type="text/javascript">
var r='class/usuarios.php';
var idusuario,txtCodigo,txtNombre,txtEmail,txtTelefono;
var BuscarUsuario;
var campo='nombres';
var orden='asc';
$(function(){
	txtCodigo=$("#txtCodigo")[0];
	txtNombre=$("#txtNombre")[0];
	txtEmail=$("#txtEmail")[0];
	txtTelefono=$("#txtTelefono")[0];
	$('#txtCodigo').format({precision: 2,autofix:true});
	
	$('#NombresUserAutocomplete').simpleAutoComplete('class/usuarios.php',{
		autoCompleteClassName: 'autocomplete',
		selectedClassName: 'sel',
		attrCallBack: 'rel',
		identifier: 'Usuarios'
	 });
	BuscarUsuario='';	
});

function Nuevo(){
	Loading();
	idusuario=0;
	txtCodigo.value='';
	txtNombre.value='';
	txtEmail.value='';
	txtTelefono.value='';
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
	idusuario=id;
	params = $.param({command:"Editar",idusuario:id});
	callServer({data:params,url:r},function(d){		
		j=toEval(d);
		txtCodigo.value=j.codigo;
		txtNombre.value=j.nombres;
		txtEmail.value=j.email;
		$('#hdCodigo_Ant').val(j.codigo);
		txtTelefono.value=j.telefono;		
		$('#tbInicio').hide();
		$('#tbDetalle').show();
		$.unblockUI();
	});
}

function Grabar(){
	if($.trim(txtCodigo.value)==""){alert("Por favor ingrese el código del usuario.");txtCodigo.focus();return;}
	if($.trim(txtNombre.value)==""){alert("Por favor ingrese nombres y apellidos.");txtNombre.focus();return;}	
	if($.trim(txtEmail.value)!=''){
		if(ValidarEmail(txtEmail,"El email ingresado no es valido, verifique por favor...")== false){ return;}
	}	
	var CambioCodigo = $('#hdCodigo_Ant').val()==$.trim(txtCodigo.value)?'NO':'SI';
	var params = $.param({command:"Guardar",
						  idusuario:idusuario,
						  codigo:$.trim(txtCodigo.value),
						  nombres:$.trim(txtNombre.value),
						  email:$.trim(txtEmail.value),
						  telefono:$.trim(txtTelefono.value),						  
						  CambioCodigo:CambioCodigo,
						  pagina:PaginaActual,
						  campo:campo,
						  orden:orden,
						  BuscarUsuario:BuscarUsuario
						});
	callServer({data:params,url:r},function(d){		
		if($.trim(d)=='Existe'){
			txtCodigo.focus()
			alert('El código: '+$.trim(txtCodigo.value)+' ya esta registrado, por favor ingresar otro código.');			
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

function Acceso(e, id){
if(e.checked){
	if(!confirm("Confirma si desea activar el acceso al sistema al usuario seleccionado.")){ e.checked = false; return;}
}else{ 
	if(!confirm("Confirma si desea deshabilitar el acceso al sistema al usuario seleccionado.")){e.checked = true; return;}
}
 params = $.param({command:"Acceso",idusuario:id,acceso:e.checked==true?1:0});
	callServer({data:params,url:r},function(d){		
		//$('#td_Listado').html(d);
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
    <td align="center" >
    <span> Buscar por usuario:&nbsp;</span>
    <input type="text" id="NombresUserAutocomplete" name="NombresUser" class="FRM" autocomplete="off" style="width: 350px; height: 23px;" />&nbsp;&nbsp;
    <input name="button" type="button" class="FRM" value="Buscar" onclick="Buscar();" />    
    </td>     
  </tr>
 <tr>
    <td height="35" ><div align="right"><input name="button" type="button" class="FRM" value="Nuevo Usuario" onclick="Nuevo();" /></div></td>     
  </tr>
  <tr>
    <td id="td_Listado" align="center">
	 <? 
		$Datos = new Usuarios();
		$Datos->GetList(0,'nombres','asc','');
      ?>
    </td>
  </tr>
 </table> 
 
 
<input type="hidden" value="" id="hdCodigo_Ant" />
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
    <td width="160" height="30" bgcolor="#FFFFFF"><a class="textopeque">C&oacute;digo:</a></td>
    <td width="790" bgcolor="#FFFFFF"><input type="text" class="FRM" id="txtCodigo"  size="60" maxlength="10" /><span  class="textopeque">&nbsp;*</span></td>
  </tr>
  <tr>
    <td width="160" height="30" bgcolor="#FFFFFF"><a class="textopeque">Nombres y Apellidos:</a></td>
    <td width="790" bgcolor="#FFFFFF"><input type="text" class="FRM" id="txtNombre" maxlength="100"  size="60" /><span  class="textopeque">&nbsp;*</span></td>
  </tr>
  <tr>
    <td height="30"><a class="textopeque">Email:</a></td>
    <td><input type="text" class="FRM" id="txtEmail" size="60" maxlength="100" /></td>
    </tr>
  <tr>
    <td height="30" bgcolor="#FFFFFF"><a class="textopeque">Tel&eacute;fono:</a></td>
    <td bgcolor="#FFFFFF"><input type="text" class="FRM" id="txtTelefono" size="60" maxlength="20" /></td>
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
</body>
</html>
