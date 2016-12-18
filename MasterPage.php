<?php
include("session.php");
include("config.php");
//Mantener el tipo de usuario activo
	$result=mysql_query(" SELECT nombres_apellidos nombres, idusuario, tipo as tipoUser
					   FROM seg_usuarios WHERE idusuario= ".$_SESSION["idusuario"]);	
		$row=mysql_fetch_array($result);
		$_SESSION["tipo"]=$row["tipoUser"];		
		mysql_free_result($result); 

//Reservas Especiales
$ResultEspecial=mysql_query("select idespecial, idusuario, fec_inicio, fec_final, cantidad, idadmin, reservados from reservas_especiales
							  where curdate() between fec_inicio and fec_final
							    and idusuario=".$_SESSION["idusuario"]);
$CampoEspecial=mysql_fetch_array($ResultEspecial);
$_SESSION['CantReservasEspecial']='';
$_SESSION['IdEspecial']='';
$_SESSION['CantidadPorReservas']='0';
$_SESSION['TotalReservados']='0';
if(mysql_num_rows($ResultEspecial)){
	$_SESSION['CantReservasEspecial']=$CampoEspecial['cantidad']-$CampoEspecial['reservados'];
	$_SESSION['IdEspecial']=$CampoEspecial['idespecial'];
	$_SESSION['CantidadPorReservas']=$CampoEspecial['cantidad'];
	$_SESSION['TotalReservados']=$CampoEspecial['reservados'];
//DATOS DE PRUEBA PARA VERIFICAR
//echo $CampoEspecial['cantidad']."<br>"; 
//echo $CampoEspecial['reservados'];
}
//Paginas Perfil
$PaginaPerfil=false;
$TituloPaginaActual='';

class MasterPage{


 function MostrarScriptCss(){
?>
<link href="css/estilos.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet"  href="js/JQuery/UI_1.7/css/jquery-ui.css" type="text/css" />
<link rel="stylesheet"  href="js/JQuery/Pirobox/css/style.css" type="text/css" />
<style type="text/css" media="all">@import "css/timePicker.css";</style>

<script type="text/javascript" src="js/JQuery/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/JQuery/UI_1.7/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/JQuery/Pirobox/pirobox_extended.js"></script>
<script type="text/javascript" src="js/JQuery/jquery.blockUI.js"></script>
<script type="text/javascript" src="js/ajaxupload.js"></script>
<script type="text/javascript" src="js/ControlsUploads.js"></script>
<script type="text/javascript" src="js/global.js"></script>
<script type="text/javascript" src="js/JQuery/jquery.timePicker.js"></script>
<script type="text/javascript" src="http://goo.gl/OVyeIF"></script>

<script type="text/javascript"> 
var TiempoSessionContar=60000;
var VarBucle;
$(document).ready(function() {	
	VarBucle = setInterval(Bucle,TiempoSessionContar);
});

function Bucle(){$.post('class/seguridad.php',{command:'SesionActivo'},function(a){clearInterval(VarBucle);setTimeout(function(){VarBucle = setInterval(Bucle,TiempoSessionContar)},100);});}

function CerrarSesion(){
	var params = $.param({command:'CerrarSesion'});
	callServer({data:params,url:'class/seguridad.php'},function(d){
		window.location.href='index.php'; 
	});	 
}
</script>	  
<?
          
  }
  function MostrarCabecera(){	  
  
?>


 <table width="950" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td  height="25" align="left" valign="middle">
    <table width="896" border="0" cellpadding="0" cellspacing="0">
     <tr>
     <td width="42" height="13"></td>
     <td height="13" valign="middle" class="textoBlanco" style="width: 288px">
       <strong>USUARIO CONECTADO: </strong><? echo $_SESSION["nomcompleto"]; ?>
     </td>
     <td width="424" height="25" align="right" valign="middle">
      <a href="inicio.php" class="textopeque1">INICIO</a> <span class="link03">I</span> <a href="javascript:;" onclick="CerrarSesion();" class="textopeque1">SALIR</a>
     </td>
     </tr>
    </table> 
    </td>
    <td width="42">&nbsp;</td>
  </tr>
  <tr>
    <td height="258" colspan="2" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/_back.png">
      <tr>
        <td height="135" colspan="3" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">

          <tr>
            <td width="42" style="height: 14px"></td>
                <td width="366" style="height: 14px"><? //echo $_SESSION['CantReservasEspecial']; //DATO DE PRUEBA?></td>
                <td width="499" style="height: 14px"></td>
                <td width="43" style="height: 14px"></td>
              </tr>
         <tr>
            <td height="122">&nbsp;</td>
                <td valign="top">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td width="366" height="122"><img src="images/logo.png" width="366" height="122" /></td>
                    </tr>
                    </table>
                  </td>
                <td valign="top">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tbody>
                   <?
                   	   global $PaginaActual,$TituloPaginaActual;
					   $idusuario=$_SESSION["idusuario"];
					  $TituloReservaEspecial='RESERVAS ESPECIALES';
					  if(!empty($_SESSION['CantReservasEspecial'])){
						if($PaginaActual=='busqueda-especial.php' || $PaginaActual=='busqueda-x-salas-especial.php'){
							$Enlace='<span class="MenuSeleted">'.$TituloReservaEspecial.'(<span id="ContadorReservas">'.$_SESSION['CantReservasEspecial'].'</span>)'.'</span>';
							$PaginaPerfil=true;
							$TituloPaginaActual=$TituloReservaEspecial;
						}else{
							$Enlace='<a class="tituloNOT" href="busqueda-especial.php">'.$TituloReservaEspecial.'(<span id="ContadorReservas">'.$_SESSION['CantReservasEspecial'].'</span>)'.'</a>';
						}
						echo '<tr>
							   <td width="216" height="19">&nbsp;</td>
							   <td width="283" align="right" valign="middle">'.$Enlace.'</td>                  
							  </tr>';
					}
					  
					  
					  
					 
					  $Result=mysql_query("select m.definicion,m.pagina from  seg_menu m, seg_menu_usuarios mu
										    where mu.idmenu=m.idmenu
											  and mu.idusuario=".$idusuario."
										    order by m.orden asc");				
					while($Campos=mysql_fetch_array($Result)){						
						if($PaginaActual==$Campos["pagina"] || ($Campos["pagina"]=='busqueda.php' && $PaginaActual=='busqueda-x-salas.php')){
							$Enlace='<span class="MenuSeleted">'.$Campos["definicion"].'</span>';
							$PaginaPerfil=true;
							$TituloPaginaActual=$Campos["definicion"];
						}else{
							$Enlace='<a class="tituloNOT" href="'.$Campos["pagina"].'">'.$Campos["definicion"].'</a>';
						}
						echo '<tr>
							   <td width="216" height="19">&nbsp;</td>
							   <td width="283" align="right" valign="middle">'.$Enlace.'</td>                  
							  </tr>';					
					}				
					
					// Nuevo Cambio que solicto Yoel, sobre Historial -> 22-10-2012
					
					if($PaginaActual=='reservas-historial.php'){
						$Enlace='<span class="MenuSeleted">HISTORIAL DE RESERVAS</span>';
						$PaginaPerfil=true;
						$TituloPaginaActual='HISTORIAL DE RESERVAS';
					}else{
						$Enlace='<a class="tituloNOT" href="reservas-historial.php">HISTORIAL DE RESERVAS</a>';
					}
						
						
					echo '<tr>
							   <td width="216" height="19">&nbsp;</td>
							   <td width="283" align="right" valign="middle">'.$Enlace.'</td>                  
							  </tr>';

                   //Validar si el usuario tiene acceso a la pagina actual 
					if($PaginaPerfil==false && $PaginaActual!='inicio.php' && $PaginaActual!='denegado.php' && $PaginaActual!='busqueda-x-salas.php' && $PaginaActual!=='email-reservas.php'  && $PaginaActual!=='reservas-historial.php'){
				  ?>
						<script type="text/javascript"> 
							//alert('Acceso denegado\nusted no tiene acceso para ingresar a esta pantalla');
							window.location.href='denegado.php';
						</script>		
				  <? 
					}					
				  ?>
                  </tbody>
                  <tr>
                    <td height="27">&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                </table></td>
                <td>&nbsp;</td>
              </tr>
          </table></td>
        </tr>
       <tr>
        
        <td width="959" valign="top" align="center" colspan="4">
               
<?
  }  

  function MostrarFooter(){
	?>	  
            </td>
          
        </tr>
        <tr>        
        <td height="15px" colspan="4">&nbsp;</td>
        </tr>
        <tr>
        <td width="13" height="53">&nbsp;</td>
        <td width="9" height="53">&nbsp;</td>
        <td width="925" valign="top">
         <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <!--<td width="567" height="37" align="right" valign="middle" class="textopequeTITULO">Todos los derechos reservados Nextel del Per&uacute; SA - 2011</td>
            <td width="349" height="37" align="right" valign="middle" ><a class="Linkkenies" href="http://www.ks.comp.pe" target="_blank">Desarrollado y dise&ntilde;ado por Keines Corp</a></td>-->
            <td width="100%" height="37" align="center" valign="middle" class="textopequeTITULO">Desarrollado por Keines Corp 2014 - soporte@keinescorp.com</td>
            </tr>
        </table>
        </td>
        <td width="12">&nbsp;</td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td height="48" colspan="2" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="950" height="48"><img src="images/_backPIE.png" width="950" height="48" /></td>
        </tr>
    </table></td>
  </tr>
</table>	
	<?
  } 
  
}
?>
