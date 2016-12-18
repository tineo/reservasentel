<?
//Coneccion Base Datos 


//OPENSHIFT
if( getenv('OPENSHIFT_MYSQL_DB_HOST') != ""){
    //echo getenv('OPENSHIFT_MYSQL_DB_HOST');
    $cn = (mysql_connect ( getenv('OPENSHIFT_MYSQL_DB_HOST').":".getenv('OPENSHIFT_MYSQL_DB_PORT'),  getenv('OPENSHIFT_MYSQL_DB_USERNAME'), getenv('OPENSHIFT_MYSQL_DB_PASSWORD') )) or die ( mysql_error() );
    mysql_select_db ( "reserva", $cn ) or die ( mysql_error() );
}else {

//Desarrollo 
    /*
    $cn = (mysql_connect ( "localhost", "root", "root" )) or die ( mysql_error() );
          mysql_select_db ( "_salas", $cn ) or die ( mysql_error() );
    */

//Produccion
    $cn = (mysql_connect("Localhost", "revasen_users", "==D6?+BxgIZ2")) or die (mysql_error());
    mysql_select_db("revasen_salas", $cn) or die (mysql_error());



//Fernandin
/*
$cn = (mysql_connect ( "localhost", "fernandi_users", "DB5CUte_Bo!m" )) or die ( mysql_error () );
      mysql_select_db ( "fernandi_nextel", $cn ) or die ( mysql_error () );
*/

}

//Datos de la empresa
//*******************
define("Domino","http://reservasentel.info/");//domino del sistema
define("NombreAplicativo","Reservas de Salas :: entel");//Nombre del aplicativo
define("Telefono","222-0397");//Telefono de la empresa
	   
//Correos de la Empresa
//*********************
define('RutaLogoCorreo','http://www.reservasentel.info/images/logo.png');//Correo de envio
define('EmailEnvio','reservas@reservasentel.info');//Correo de envio
define('EmailContacto','reservas@reservasentel.info');//Correo de envio

//Configuracion del sistema
//*************************
define("ColorFilaOver","#F9FCFF");//Color cuando pasa el mouse sobre las filas de las grilla.
define("ColorFilaSpecial","#3f6094");//Color cuando pasa el mouse sobre las filas de las grilla. Tineo! Sala Especial

//Se ha definido que el parametro que los supervisores pueden ver en el calendario de reservas hasta n meses en adelante
$ResultOtrosParametros=mysql_query('select cant_meses,email_horas_antes,semana, horas_semana, horas_reserva, estado from config_varios, config_reserva_semana');
$OtrosParametros=mysql_fetch_array($ResultOtrosParametros);

//Configuracion para el calendario;
define("NumeroPosteriorMeses",$OtrosParametros['cant_meses']);//cambiar aquí el parametro de cantidad de meses posterior a mostrar
define("EmailHorasAntesReservado",$OtrosParametros['email_horas_antes']);//Este parametro es para enviar email, en cuantas horas antes que se realice la reserva
//Reservas Acumuladas
define("Semana",$OtrosParametros['semana']);
define("HorasSemana",$OtrosParametros['horas_semana']);
define("HorasPorReserva",$OtrosParametros['horas_reserva']);
define("EstadoReservasAcumuladas",$OtrosParametros['estado']);




$FlagDiaMostrar=0;//cambiar aquí el parametro los diías del mes posterior, 0: el mismo dia del mes, 1: Último dia del mes
//Si se necesita saber el ultima dia de un mes 
$UltimoDiaMes=date("d",(mktime(0,0,0,(date("m")+(NumeroPosteriorMeses+1)),1,date("Y"))-1));
$DiaMostrar=$FlagDiaMostrar==0?(date("d")):$UltimoDiaMes;
$NumeroCalendario=NumeroPosteriorMeses-1;// Se resta una para el calendario de JQUERY, se necesita sumar un mes si desea la fecha por php 
define("FechaHasta",(date("Y")).','.(date("m")+$NumeroCalendario).','.$DiaMostrar);

//PARCHE PARA ELIMINACION DE RESERVAS DE CAFETERIA - YOEL!
  //CAFETERIA SAN ISIADRO
  $Eli = mysql_query("Delete FROM reservas WHERE idsala = '31' AND horario_final < '16:30' AND horario_final > '11:30'",$cn) or die("Problemas en la Limpieza:".mysql_error());
  $Eli = mysql_query("Delete FROM reservas WHERE idsala = '31' AND horario_inicio > '11:30' AND horario_inicio < '16:30'",$cn) or die("Problemas en la Limpieza:".mysql_error());
  //CAFETERIA SAN BORJA
  $Eli = mysql_query("Delete FROM reservas WHERE idsala = '16' AND horario_final < '16:30' AND horario_final > '11:30'",$cn) or die("Problemas en la Limpieza:".mysql_error());
  $Eli = mysql_query("Delete FROM reservas WHERE idsala = '16' AND horario_inicio > '11:30' AND horario_inicio < '16:30'",$cn) or die("Problemas en la Limpieza:".mysql_error());

?>