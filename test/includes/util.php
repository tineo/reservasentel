<? 

class Utilitario{

private $latin1_to_utf8;
private $utf8_to_latin1;

// UTF 8, para AJAX
public function __construct() { 
	for($i=32; $i<=255; $i++) {
		$this->latin1_to_utf8[chr($i)] = utf8_encode(chr($i));
		$this->utf8_to_latin1[utf8_encode(chr($i))] = chr($i);
	}
}

public function convertir_utf8($text) {
	return utf8_encode($this->mixed_to_latin1($text,false));
}

public function mixed_to_latin1($text,$set=true) {
	foreach( $this->utf8_to_latin1 as $key => $val ) {
		$text = str_replace($key, $val, $text);
		if($val=='"' && $set==false){
			$text=str_replace('"', "'", $text);
		}else if($val=="'" && $set==true){
			$text=str_replace("'", '"', $text);
		}
	}
	$consalto=$text;
	$sinsalto=eregi_replace("[\n|\r|\n\r]", '', $consalto);
	/*//Para eliminar al inicio del editor el tag <p></p>
	if('<p>'==substr($sinsalto,0,3)){
		$EliminateTagP=substr($sinsalto,3);
		$TapEliminado=substr($EliminateTagP,0,-4);
		$sinsalto=$TapEliminado;
	}*/	
	$text=$sinsalto;
	return $text;
}

//Para editores
public function CampoHTML($text,$set=false) {	
	foreach( $this->utf8_to_latin1 as $key => $val ) {
		$text = str_replace($key, $val, $text);
		if($val=='"' && $set==false){
			$text=str_replace('"', "'", $text);
		}else if($val=="'" && $set==true){
			$text=str_replace("'", '"', $text);
		}		
	}	
	$consalto=$text;
	$sinsalto=eregi_replace("[\n|\r|\n\r]", '', $consalto);
	$text=$sinsalto;
	return $text;
}


//Convierte fecha de mysql a normal
public function cambiaf_a_normal($fecha){ 
	ereg( "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha); 
	$lafecha=$mifecha[3]."/".$mifecha[2]."/".$mifecha[1]; 
	return $lafecha; 
} 

//Convierte fecha de normal a mysql
public function cambiaf_a_mysql($fecha){ 
	ereg( "([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $fecha, $mifecha); 
	$lafecha=$mifecha[3]."-".$mifecha[2]."-".$mifecha[1]; 
	return $lafecha; 
}

public function EliminarTagsHTML($texto){ 
	$HtmlTag= ereg_replace("<[^>]+>","",$texto);
	return ereg_replace("&nbsp;","",$HtmlTag);
}

public function EliminarTagsHtmlJSON($texto){ 
	$HtmlTag= ereg_replace("<","\u003c",$texto);
	$HtmlTag= ereg_replace(">","\u003e",$HtmlTag);
	$HtmlTag= ereg_replace("</","\u003c/",$HtmlTag);
	$HtmlTag= ereg_replace("/>","/\u003e",$HtmlTag);
	$HtmlTag= ereg_replace('"','\\"',$HtmlTag);	
	return $HtmlTag;
}

public function CodigoUnicoAleatorio($numStr){
	srand((double)microtime()*rand(1000000,9999999));
	$arrChar=array();
	$uId='';
	for($i=65;$i<90;$i++){
		array_push($arrChar,chr($i));
		array_push($arrChar,strtolower(chr($i)));
	}
	for($i=48;$i<57;$i++){
		array_push($arrChar,chr($i));
	}
	for($i=0;$i<$numStr;$i++){
		$uId.=$arrChar[rand(0,count($arrChar))];
	}
  return $uId;
}


//La funcion regresa el numero correspondiente a la lista siguiente:
//0 Lunes
//1 Martes
//2 Miercoles
//3 Jueves
//4 Viernes
//5 Sabado
//6 Domingo
// Esta funcion acepta como parametro la fecha en formato DD/MM/YYYY
public function WeekDay($fecha){ 	
	$fecha=str_replace("/","-",$fecha);
	list($dia,$mes,$anio)=explode("-",$fecha);
	return (((mktime ( 0, 0, 0, $mes, $dia, $anio) - mktime ( 0, 0, 0, 7, 17, 2006))/(60*60*24))+700000) % 7;
}

//Formato de envio es:'5:50','1:30'
//Retorna: 7:20
public function SumarHoras($hora1,$hora2){
	$hora1=split(":",$hora1);
	$hora2=split(":",$hora2);
	$horas=(int)$hora1[0]+(int)$hora2[0];
	$minutos=(int)$hora1[1]+(int)$hora2[1];
	$horas+=(int)($minutos/60);
	$minutos=$minutos%60;
	if($minutos==0)$minutos='00';
	return $horas.":".$minutos;
}

 //Formato de envio es: '10:00:00','11:00:00'
 //Retorna: 01-00-00
 public function RestarHoras($horaini,$horafin){
	$horai=substr($horaini,0,2);
	$mini=substr($horaini,3,2);
	$segi=substr($horaini,6,2);

	$horaf=substr($horafin,0,2);
	$minf=substr($horafin,3,2);
	$segf=substr($horafin,6,2);

	$ini=((($horai*60)*60)+($mini*60)+$segi);
	$fin=((($horaf*60)*60)+($minf*60)+$segf);

	$dif=$fin-$ini;

	$difh=floor($dif/3600);
	$difm=floor(($dif-($difh*3600))/60);
	$difs=$dif-($difm*60)-($difh*3600);
	return date("H-i-s",mktime($difh,$difm,$difs));
}




/*
	<  = \u003c
	>  = \u003e
	</ = \u003c/
	/> = /\u003e
	"  = \\"  
*/	

}
?>