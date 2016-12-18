<?
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
</head>
<body>
<?
 $MasterPage->MostrarCabecera();
?>
<div align="center" style="height:30px"></div>
<div align="center" class="TituloInicio" style="height:45px" runat="server" id="Titulo">
 BIENVENIDO - SISTEMA DE RESERVAS DE SALAS
</div>
<!--<div align="center">
 <img src="images/encuesta.jpg" />
</div>-->
<?
  $MasterPage->MostrarFooter();
?>
 </body>
</html> 
