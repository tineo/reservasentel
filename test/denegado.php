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
<div align="center">
 <img src="images/denegado.jpg" />
</div>
<?
  $MasterPage->MostrarFooter();
?>
 </body>
</html> 
