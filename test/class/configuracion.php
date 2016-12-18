<?
if(!empty($_POST["command"])){
	include('../config.php');//Si utiliza ajax es requerido importar la configuracion
	include("../includes/GridPaginador.php");
	include('../includes/util.php');
	include('../includes/calendario.php');
}else{
	include("includes/GridPaginador.php");
	include('includes/util.php');
	include('includes/calendario.php');
}

 
$Utilitario = new Utilitario;
$Accion = new ModuloAdmin; 

if($_POST["command"]=='GuardarRecurrente'){
	$Accion->GuardarRecurrente();
}else if($_POST["command"]=='EditarRecurrente'){
	$Accion->EditarRecurrente();
}else if($_POST["command"]=='EliminarRecurrente'){
	$Accion->EliminarRecurrente();
}else if($_POST["command"]=='ListarRecurrente'){
	$Accion->ListarRecurrente(0);
}else if($_POST["command"]=='ActivarRecurrente'){
	$Accion->ActivarRecurrente();
}else if($_POST["command"]=='GuardarHoras'){
	$Accion->GuardarHoras();
}else if($_POST["command"]=='EditarHoras'){
	$Accion->EditarHoras();
}else if($_POST["command"]=='EliminarHoras'){
	$Accion->EliminarHoras();
}else if($_POST["command"]=='ListarHoras'){
	$Accion->ListarHoras(0);
}else if($_POST["command"]=='ActivarHoras'){
	$Accion->ActivarHoras();
}else if($_POST["command"]=='CargarCalendario'){
	$Accion->CargarCalendario($_POST['dia'],$_POST['nuevo_mes'],$_POST['nuevo_anio']);
}else if($_POST["command"]=='FechasFestivos'){
	$Accion->FechasFestivos();
}else if($_POST["command"]=='ReservasPorSemana'){
	$Accion->ReservasPorSemana();
}else if($_POST["command"]=='CantidadMesesAdelante'){
	$Accion->CantidadMesesAdelante();
}else if($_POST["command"]=='EmailHorasAntes'){
	$Accion->EmailHorasAntes();
}


//
class ModuloAdmin{		
	/*Tab de Reservas Recurrentes*/
	function GuardarRecurrente(){
		global $Utilitario;
		$idrecurrente = $_POST["idrecurrente"];		
		if($idrecurrente==0){
			$query = " SELECT count(*) existe FROM config_recurrentes WHERE mes=".$_POST['mes']." and dia=".$_POST['dia']." "; 	  
			$result=mysql_query($query);
			$Field=mysql_fetch_array($result);
			mysql_free_result($result);		
			$exite=$Field["existe"];
			if($exite==0){				
				$sql=sprintf(" INSERT INTO config_recurrentes(definicion,mes,dia,activo) values('%s',%s,%s,0) ",
					 $Utilitario->mixed_to_latin1($_POST['definicion']),$_POST['mes'],$_POST['dia']);			
			}else{
				echo"Existe";
				die();
			}							    
		}else{
			if($_POST['CambioMesDia']=='SI'){
				$query = " SELECT count(*) existe FROM config_recurrentes WHERE mes=".$_POST['mes']." and dia=".$_POST['dia']." ";
				$result=mysql_query($query);
				$Field=mysql_fetch_array($result);
				mysql_free_result($result);		
				$exite=$Field["existe"];
				if($exite>0){
					echo"Existe";
					die();
				}			
			}
			$sql=sprintf(" UPDATE config_recurrentes set definicion='%s',mes=%s,dia=%s WHERE idrecurrente=%s ",
				 	$Utilitario->mixed_to_latin1($_POST['definicion']),$_POST['mes'],$_POST['dia'],$idrecurrente);
		}
		mysql_query($sql);		
		$this->ListarRecurrente(0);
	}
	
	function EditarRecurrente(){
		global $Utilitario;
		$sql = "SELECT definicion,mes,dia FROM config_recurrentes WHERE idrecurrente=".$_POST["idrecurrente"];
		$result=mysql_query($sql);
		$Field=mysql_fetch_array($result);		
	   echo '{ "definicion": "' . $Utilitario->convertir_utf8($Field["definicion"]) . '",
			   "mes": "' . $Field["mes"] . '",
			   "dia": "' . $Field["dia"] . '"
			 }';
	}	
	
	function ListarRecurrente($pagina){
		global $Utilitario;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("Paginar");
		$ControlGrid->SetRegistrosAMostrar(100);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(5);
		$sql=" SELECT idrecurrente,definicion,mes,dia,activo FROM config_recurrentes order by mes asc,dia asc ";
		$Resultado = $ControlGrid->GetResultados($sql);	
		$Rows =  '<tr>
					<th width="520px" height="27">Definici&oacute;n</th>
					<th width="84px" align="center">Plazo en Meses</th>
					<th width="84px" align="center">Veces</th>
					<th width="56px" style="text-align:center;">Vigente</th>
					<th width="146px" colspan="2" style="text-align:center;">Acci&oacute;n</th>
				  </tr>';		
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Checked='';
			$Disabled='';
			$DisabledEditar='';
			$TextoMes=$ArrFila["mes"];
			$TextoDia=$ArrFila["dia"];
			if($ArrFila["activo"]==1){
				$Checked=' checked="checked" ';
				$Disabled=' disabled="disabled" ';
			}
			
			if($ArrFila["idrecurrente"]==1){				
				$Disabled=' disabled="disabled" ';
				$DisabledEditar=' disabled="disabled" ';
				$TextoHorasPorDia='Deshabilitado';
				$TextoMes='Deshabilitado';
				$TextoDia='Deshabilitado';
			}
			$Rows .= '<tr class="Registros" onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["definicion"],ENT_QUOTES,"iso-8859-1").'</td>';
			$Rows .= ' <td style="text-align:center">'.$TextoMes.'</td>';			
			$Rows .= ' <td style="text-align:center">'.$TextoDia.'</td>';
			$Rows .= ' <td align="center"><input type="radio" name="Recurrente" onclick="ActivarRecurrente('.$ArrFila["idrecurrente"].');" '.$Checked.' /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value="Editar" onclick="EditarRecurrente('.$ArrFila["idrecurrente"].');" '.$DisabledEditar.' /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value="Eliminar" onclick="EliminarRecurrente('.$ArrFila["idrecurrente"].');" '.$Disabled.' /></td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}
	
	function ActivarRecurrente(){
		mysql_query("update config_recurrentes set activo=0 ");
		mysql_query("update config_recurrentes set activo=1 where idrecurrente=".$_POST["id"]);
		$this->ListarRecurrente(0);
	}
	
	function EliminarRecurrente(){	
		mysql_query(" DELETE FROM config_recurrentes WHERE idrecurrente = ".$_POST['id']);			
		$this->ListarRecurrente(0);
	}	
	
	/*Tab de Horas por Día*/
	function GuardarHoras(){
		global $Utilitario;
		$idhoradia = $_POST["idhoradia"];		
		if($idhoradia==0){
			$query = " SELECT count(*) existe FROM config_horas_dias WHERE hora=".$_POST['hora'];
			$result=mysql_query($query);
			$Field=mysql_fetch_array($result);
			mysql_free_result($result);		
			$exite=$Field["existe"];
			if($exite==0){				
				$sql=sprintf(" INSERT INTO config_horas_dias(definicion,hora,activo) values('%s',%s,0) ",
					 $Utilitario->mixed_to_latin1($_POST['definicion']),$_POST['hora']);			
			}else{
				echo"Existe";
				die();
			}							    
		}else{
			if($_POST['CambioHora']=='SI'){
				$query = " SELECT count(*) existe FROM config_horas_dias WHERE hora=".$_POST['hora'];
				$result=mysql_query($query);
				$Field=mysql_fetch_array($result);
				mysql_free_result($result);		
				$exite=$Field["existe"];
				if($exite>0){
					echo"Existe";
					die();
				}			
			}
			$sql=sprintf(" UPDATE config_horas_dias set definicion='%s',hora=%s WHERE idhoradia=%s ",
				 	$Utilitario->mixed_to_latin1($_POST['definicion']),$_POST['hora'],$idhoradia);
		}
		mysql_query($sql);		
		$this->ListarHoras(0);
	}
	
	function EditarHoras(){
		global $Utilitario;
		$sql = "SELECT definicion,hora FROM config_horas_dias WHERE idhoradia=".$_POST["idhoradia"];
		$result=mysql_query($sql);
		$Field=mysql_fetch_array($result);		
	   echo '{ "definicion": "' . $Utilitario->convertir_utf8($Field["definicion"]) . '",
			   "hora": "' . $Field["hora"] . '"
			 }';
	}	
	
	function ListarHoras($pagina){
		global $Utilitario;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("Paginar");
		$ControlGrid->SetRegistrosAMostrar(100);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(4);
		$sql=" SELECT idhoradia,definicion,hora,activo FROM config_horas_dias order by hora asc ";
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows =  '<tr>
					<th width="552px" height="27">Definici&oacute;n</th>
					<th width="100px" align="center">Horas por D&iacute;a</th>	
					<th width="74px" style="text-align:center;">Vigente</th>
					<th width="144px" colspan="2" style="text-align:center;">Acci&oacute;n</th>
				  </tr>';		
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Checked='';
			$Disabled='';
			$DisabledEditar='';
			$TextoHorasPorDia=$ArrFila["hora"];
			if($ArrFila["activo"]==1){
				$Checked=' checked="checked" ';
				$Disabled=' disabled="disabled" ';
				$DisabledEditar='';				
			}
			
			if($ArrFila["idhoradia"]==1){				
				$Disabled=' disabled="disabled" ';
				$DisabledEditar=' disabled="disabled" ';
				$TextoHorasPorDia='Deshabilitado';
			}
			$Rows .= '<tr class="Registros" onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["definicion"],ENT_QUOTES,"iso-8859-1").'</td>';
			$Rows .= ' <td style="text-align:center">'.$TextoHorasPorDia.'</td>';
			$Rows .= ' <td align="center"><input type="radio" name="Horas" onclick="ActivarHoras('.$ArrFila["idhoradia"].');" '.$Checked.' /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value="Editar" onclick="EditarHoras('.$ArrFila["idhoradia"].');" '.$DisabledEditar.' /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value="Eliminar" onclick="EliminarHoras('.$ArrFila["idhoradia"].');" '.$Disabled.' /></td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}
	
	function ActivarHoras(){
		mysql_query("update config_horas_dias set activo=0 ");
		mysql_query("update config_horas_dias set activo=1 where idhoradia=".$_POST["id"]);
		$this->ListarHoras(0);
	}
	 
	function EliminarHoras(){	
		mysql_query(" DELETE FROM config_horas_dias WHERE idhoradia = ".$_POST['id']);			
		$this->ListarHoras(0);
	}
	
	//**************Calendario Festivo***************************
	function CargarCalendario($dia,$nuevo_mes,$nuevo_ano){
		global $Utilitario;
		$Calendario= new Calendario;
		if (empty($dia)){
			$tiempo_actual = time();
			$mes = date("n", $tiempo_actual);
			$ano = date("Y", $tiempo_actual);
			$dia=date("d");
			$fecha=$ano . "-" . $mes . "-" . $dia;
			if(strlen($mes)==1) $mes='0'.$mes;
			$FechaDB=$ano . "-" . $mes;
		}else{
			$mes = $nuevo_mes;
			$ano = $nuevo_ano;
			$dia = $dia;
			$fecha=$ano . "-" . $mes . "-" . $dia;
			if(strlen($mes)==1) $mes='0'.$mes;
			$FechaDB=$ano . "-" . $mes;
		}
		$Feriados = array();
		$Result=mysql_query(" select fecha,DATE_FORMAT(fecha,'%d') as dia from fechas_especiales where SUBSTR(fecha,1,7)='".$FechaDB."'  order by fecha asc ");
		while($Campo=mysql_fetch_array($Result)){
			$Feriados[$Utilitario->cambiaf_a_normal($Campo['fecha'])]=intval($Campo['dia']);
		}
		$Calendario->Mostrar($dia,$mes,$ano,$Feriados);
	}
	
	function FechasFestivos(){
		global $Utilitario;		
		$Tipo=$_POST['Tipo'];
		$Fechas=$Utilitario->cambiaf_a_mysql($_POST['Fecha']);
		$ArrFecha=explode('-',$Fechas);
		if($Tipo=='F'){//Ingresar fecha como feriado
			mysql_query("INSERT INTO fechas_especiales(fecha) values('".$Fechas."')");
		}else{//Eliminar fecha feriado
			mysql_query("DELETE FROM fechas_especiales where SUBSTR(fecha,1,10)='".$Fechas."' ");
		}
		$this->CargarCalendario($ArrFecha[2],$ArrFecha[1],$ArrFecha[0]);
	}
	
	// Cantidad de resevas por semana
	function ReservasPorSemana(){					
		mysql_query("update config_reserva_semana set semana=".$_POST['Semanas'].", horas_semana=".$_POST['HorasSemanas'].", horas_reserva='".$_POST['HorasResevas']."', estado=".$_POST['Estado']." ");
	}
	
	// Cantidad de Meses en el calendario
	function CantidadMesesAdelante(){
		mysql_query("update config_varios set cant_meses=".$_POST['Mes']);
	}
	
	// Este parametro es para enviar email, en cuantas horas antes que se realice la reserva
	function EmailHorasAntes(){
		mysql_query("update config_varios set email_horas_antes='".$_POST['Hora']."'");
	}
}




?>
