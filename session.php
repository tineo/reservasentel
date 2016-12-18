<?
session_start();
/* Fix  PHP 5.5+ */
/*if(!session_is_registered("idusuario")){
	header("location: index.php");
}*/

if(!isset($_SESSION['idusuario'])){
	header("location: index.php");
}

//Pagina Actual 
$PaginaActual = basename($_SERVER["REQUEST_URI"]); 
//$pagina = 'portal/admin/'.basename($PHP_SELF);
//Validar que no permita mostrar por url las sgtes paginas:
if($paginaActual=='MasterPage.php' || $paginaActual=='session.php'){
	header('location: inicio.php');
}
?>
