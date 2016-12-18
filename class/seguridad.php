<?

include('../config.php');

if($_POST['command']=='Login'){
	login();
}else if($_POST['command']=='CerrarSesion'){
	CerrarSesion();
}else if($_POST['command']=='SesionActivo'){
	SesionActivo();
}

function login(){
	$query = sprintf(" SELECT nombres_apellidos nombres, idusuario, tipo as tipoUser
					   FROM seg_usuarios WHERE codigo='%s' and acceso= 1 ", 
			 mysql_real_escape_string($_POST["i"]));
	$result=mysql_query($query);
	if(mysql_num_rows($result)){		
		$row=mysql_fetch_array($result);
		session_start();
		$_SESSION["tipo"]=$row["tipoUser"];		
		$_SESSION["idusuario"]=$row["idusuario"];
		$_SESSION["nomcompleto"]=$row["nombres"];
		
		mysql_free_result($result); 
		echo "1";
	}else{
		echo "0";
	}
}

function SesionActivo(){
	session_start();
	$SoyYo=$_SESSION["idusuario"];
}

function CerrarSesion(){
	session_start();
	session_unset();
	session_destroy();
} 

?>
