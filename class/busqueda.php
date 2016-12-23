<?
if(!empty($_POST["command"]) || !empty($_REQUEST['identifier'])){
	include_once('../config.php');//Si utiliza ajax es requerido importar la configuracion
	include_once("../includes/GridPaginador.php");
	include_once('../includes/util.php');
	session_start();
}else{
	include_once("includes/GridPaginador.php");
	include_once('includes/util.php');
}

 
$Utilitario = new Utilitario;
$idsalaCurrent=0;
$Busqueda = new Busquedas;

if($_POST["command"]=='BusquedaAvanzanda'){
	$Busqueda->BusquedaAvanzanda();
}else if($_POST["command"]=='DetalleSalaSeleccion'){
	$Busqueda->DetalleSalaSeleccion();
}else if($_POST["command"]=='ComboPisos'){
	$Busqueda->ComboPisos();
}else if($_POST["command"]=='ComboSalas'){
	$Busqueda->ComboSalas();
}else if($_POST["command"]=='BusquedaPorSala'){
	$Busqueda->BusquedaPorSala($_POST["Fecha"],$_POST["Sala"],true);
}else if($_POST["command"]=='ReservarBusquedaAvanzada'){
	$Busqueda->ReservarBusquedaAvanzada();
}else if($_POST["command"]=='ReservarBusquedaPorSala'){
	$Busqueda->ReservarBusquedaPorSala();
}else if($_POST["command"]=='RangoHoras'){
	$Busqueda->RangoHoras();
}else if($_REQUEST['command']=='PopUpUsuarios'){
	$Busqueda->PopUpUsuarios($_POST['pagina'],$_POST['TextoBuscar']);
}


class Busquedas{	
	
	function BusquedaAvanzanda(){
		global $Utilitario, $idsalaCurrent;
		//Varificar si el paramentro de maximo de horas por dia esta habilitado
		if($_SESSION["tipo"]!='A'){//Solo el administrador del sistema no ingresa a estas validaciones
			$ResultParametroHoras=mysql_query("select hora from config_horas_dias where activo=1");
			$CampoMaximoHoras=mysql_fetch_array($ResultParametroHoras);			
			if(!empty($CampoMaximoHoras['hora'])){
				$ValidarHorasMaximoPorDias=$this->ValidarHorasMaximoPorDia($_POST["HoraInicio"],$_POST["HoraFinal"],$CampoMaximoHoras['hora'].':00');
				if($ValidarHorasMaximoPorDias!='Continuar'){
					echo '{"Validar": "' . 'DENEGADO' . '",
						   "Mensaje": "' . $Utilitario->EliminarTagsHtmlJSON($ValidarHorasMaximoPorDias) . '"
						  }';
					die();
				}
			}		
		}
		
		//Validar Reservas Acumuladas
		if(EstadoReservasAcumuladas == 1 && $_SESSION["tipo"]!='A'){
			$ValidarReservasAcumuladas=$this->ReservasAcumuladas_Y_HorasPorReserva($_POST["Fecha"],$_POST["HoraInicio"],$_POST["HoraFinal"]);
			if($ValidarReservasAcumuladas!='Continuar'){
				echo '{"Validar": "' . 'DENEGADO' . '",
					   "Mensaje": "' . $Utilitario->EliminarTagsHtmlJSON($ValidarReservasAcumuladas) . '"
					  }';
				die();
			}
		}
		
		
		$Grilla=$this->Listado($_POST["pagina"],$_POST["Ubicacion"],$_POST["capacidadUno"],$_POST["capacidadDos"],$_POST["Proyector"],$_POST["Telefono"],$_POST["Fecha"],$_POST["HoraInicio"],$_POST["HoraFinal"]);
		$Detalle='';
		if(!empty($idsalaCurrent)){
			$Detalle=$Utilitario->EliminarTagsHtmlJSON($this->DetalleSala($idsalaCurrent,$_POST["Fecha"],$_POST["HoraInicio"],$_POST["HoraFinal"]));
		}
	    echo '{ "Validar": "' . 'OK' . '",
				"Grilla": "' . $Utilitario->EliminarTagsHtmlJSON($Grilla) . '",
				"Detalle": "' . $Detalle . '",
				"idsalaCurrent": "' . $idsalaCurrent . '"				
			  }';
	}
	
	function ReservasAcumuladas_Y_HorasPorReserva($Fecha,$HoraInicioEnvio,$HoraFinalEnvio){
		global $Utilitario;
		//***************************************************************************************************************************************************
		//Antes de validar con las reservas acumuladas, se necesita validar el rango de horario que envia el usuario, con el parametro horas por reserva    *
		//***************************************************************************************************************************************************
		//Restar para saber la diferencia de horas a reservar
		$HoraEnvio=$Utilitario->RestarHoras($HoraInicioEnvio,$HoraFinalEnvio);
		
		//esta funcion retorna en formta: 01-30-00, convertir a: 01:30
		list($Hora, $Minutos, $Segundos) = split('-', $HoraEnvio);
		$Hora=(int)$Hora;
		$HoraEnvio=$Hora.':'.$Minutos;		
		//Verificar si esta en el rango con el parametro
		//y el máximo de horas por reserva		
		if($HoraEnvio>HorasPorReserva){
			return 'El m&aacute;ximo de horas por reserva no debe superar a <strong>'.$this->FormatHora(HorasPorReserva).'</strong> , por favor modifique el rango de horas.';
		}
		
		//Validar las horas reservados, entre el rango
		$FechaPorReservar=$Fecha;
		$FechaPorReservar=$Utilitario->cambiaf_a_mysql($FechaPorReservar);
		
		$CantidadUltimoDia=1;
		if(Semana==1){
			$CantidadUltimoDia=6;
		}elseif(Semana==2){
			$CantidadUltimoDia=13;
		}elseif(Semana==3){
			$CantidadUltimoDia=20;
		}elseif(Semana==4){
			$CantidadUltimoDia=27;
		}		
		
		$ResultRangoParaReservasAcumuladas=mysql_query("SELECT DATE_SUB('".$FechaPorReservar."',INTERVAL WEEKDAY('".$FechaPorReservar."') DAY) as primerDiaSemana,
											   DATE_ADD(DATE_SUB('".$FechaPorReservar."',INTERVAL WEEKDAY('".$FechaPorReservar."') DAY),INTERVAL ".$CantidadUltimoDia." DAY) as ultimoDiaSemana;");		
											   
		$CampoRango=mysql_fetch_array($ResultRangoParaReservasAcumuladas);
		//echo 'El resultado '.$FechaPorReservar.' es el Rango de fechas: '.$Utilitario->cambiaf_a_normal($CampoRango['primerDiaSemana']) .' hasta el '. $Utilitario->cambiaf_a_normal($CampoRango['ultimoDiaSemana']);
		$ResultReservados=mysql_query("select codigo,fecha_reserva,horario_inicio,horario_final from reservas 
							where estado='R'
							and reserva_especial is null
							and date(fecha_reserva) BETWEEN date('".$CampoRango['primerDiaSemana']."') and date('".$CampoRango['ultimoDiaSemana']."')						
							and idusuario=".$_SESSION["idusuario"]);
		
		$TotalResevado=0;
		
		$HoraCurent=$Utilitario->RestarHoras($HoraInicioEnvio,$HoraFinalEnvio);			
		//esta funcion retorna en formta: 01-30-00, convertir a: 01:30
		list($Hora, $Minutos, $Segundos) = split('-', $HoraCurent);		
			
		$SumaReservados=$Hora.':'.$Minutos;
		$HoraEnvio=$SumaReservados;
		$HoraTotalReservados='00:00';
		while($CamposHorasReservados=mysql_fetch_array($ResultReservados)){			
			//Restar para saber la diferencia de horas reservados 
			$HoraCurent=$Utilitario->RestarHoras($CamposHorasReservados['horario_inicio'],$CamposHorasReservados['horario_final']);
			
			//esta funcion retorna en formta: 01-30-00, convertir a: 01:30
			list($Hora, $Minutos, $Segundos) = split('-', $HoraCurent);
			$HoraCurent=$Hora.':'.$Minutos;
			$HoraTotalReservados=$Utilitario->SumarHoras($HoraTotalReservados,$HoraCurent);
			//$SumaReservados=$SumaReservados+$HoraCurent;
			$SumaReservados=$Utilitario->SumarHoras($SumaReservados,$HoraCurent);
			$HtmlReservadosDet.='<tr>';
			$HtmlReservadosDet.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['codigo'].'</td>';
			$HtmlReservadosDet.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$Utilitario->cambiaf_a_normal($CamposHorasReservados['fecha_reserva']).'</td>';
			$HtmlReservadosDet.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['horario_inicio'].'</td>';
			$HtmlReservadosDet.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['horario_final'].'</td>';
			$HtmlReservadosDet.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$this->FormatHora($HoraCurent).'</td>';
			$HtmlReservadosDet.='</tr>';
		}
		$HtmlReservados.='<table width="420px" border="0" align="left" cellpadding="0" cellspacing="0">';
		$HtmlReservados.='<tr>';
		//$HtmlReservados.='<td class="textopequeTITULO">Estimado usuario, no será posible realizar su reserva, porque desde el <strong>'.$Utilitario->cambiaf_a_normal($CampoRango['primerDiaSemana']).'</strong> hasta el ';
        // $HtmlReservados.='<strong>'.$Utilitario->cambiaf_a_normal($CampoRango['ultimoDiaSemana']).'</strong> tiene para distribuir '.$this->FormatHora(HorasSemana.':00').' de reservas.<br />A continuación se detalla las reservas realizadas:</td>';
		$HtmlReservados.='<td class="textopequeTITULO">Estimado usuario, no será posible realizar su reserva, porque actualmente tiene <strong>'.$this->FormatHora($HoraTotalReservados).'</strong> reservados, ahora necesitas reservar <strong>'.$this->FormatHora($HoraEnvio).'</strong>, el resultado es <strong>'.$this->FormatHora($SumaReservados).'</strong>, en la cual supera el máximo de <strong>'.$this->FormatHora(HorasSemana.':00').'</strong>  para distribuirlas desde el <strong>'.$Utilitario->cambiaf_a_normal($CampoRango['primerDiaSemana']).'</strong> hasta el <strong>'.$Utilitario->cambiaf_a_normal($CampoRango['ultimoDiaSemana']).'</strong>.<br /><br />A continuación se detalla las reservas realizadas:</td>';
		$HtmlReservados.='</tr>';
		$HtmlReservados.='<tr><td height="8px"></td></tr>';
		$HtmlReservados.='<tr>';
		$HtmlReservados.='<td>';
		$HtmlReservados.='<table width="420px" border="0" align="left" cellpadding="3" cellspacing="1" bgcolor="#CCCCCC">';		
		$HtmlReservados.='<tr>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="40px"><strong>Código</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Fecha</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Hora Inicio</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Hora Final</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="170px"><strong>Horas Reservado</strong></td>';
		$HtmlReservados.='</tr>';
		$HtmlReservados.=$HtmlReservadosDet;	
		$HtmlReservados.='<tr>';			
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF" align="right" colspan="4"><strong>Total de horas reservados:</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$this->FormatHora($HoraTotalReservados).'</td>';
		$HtmlReservados.='</tr>';
		$HtmlReservados.='</table>';
		$HtmlReservados.='</td>';
		$HtmlReservados.='</tr>';
		$HtmlReservados.='</table>';
		$TotalSuma=(float) str_replace(':','.',$SumaReservados);
		//return $TotalSuma;
		if($TotalSuma>HorasSemana){
			return $HtmlReservados;
			
			
		}
		/*if($SumaReservados>HorasSemana.':00'){
			return $HtmlReservados;
		}*/		
		return 'Continuar';
		 
	}
	
	function DetalleSala($idsala,$fecha_reserva,$horario_inicio,$horario_final){
		global $Utilitario;
		$result=mysql_query("select s.idsala, s.idubicacion, s.nombre, s.piso, s.capacidad, s.caracteristicas, s.imagen, 
									CONCAT_WS(' ',u.nombre,'-','Píso',s.piso,'-',s.nombre) as UbicacionSalas,
									(select se.idevento from salas_eventos se where se.idsala=s.idsala) as idevento
							  from salas s, ubicaciones u 
							 where u.idubicacion=s.idubicacion 
							   and s.idsala=".$idsala);
		$Campos=mysql_fetch_array($result);
		$Imagen='';
		if(!empty($Campos['imagen'])){
			$Imagen='<img src="uploads/salas/'.$Campos['imagen'].'" height="253" alt="imagen salas" />';
		}
		//Complementos
		$resultComple=mysql_query("select c.nombre,leyenda from complementos c, salas_complementos sc
				where sc.idcomplemento=c.idcomplemento and sc.idsala=".$idsala);
		$Titulo='';
		$TextoRetorno='Ninguno';
		while($CamposComple=mysql_fetch_array($resultComple)){
			$Titulo.=', '.htmlentities($CamposComple['nombre']);
		}
		if(!empty($Titulo)){
			$TextoRetorno=substr($Titulo,2);
		}


		
		$Html='<table width="505" border="0" cellpadding="2" cellspacing="1" bgcolor="#E7E7E7">';
		$Html.='<tr>';
		$Html.='  <td height="37" colspan="4" valign="middle" bgcolor="#FFFFFF" id="Datos" idubicacion="'.$Campos['idubicacion'].'" capacidad="'.$Campos['capacidad'].'" idevento="'.$Campos['idevento'].'">';
		$Html.='  <span class="textopequeTITULO"><strong>'.$Campos['UbicacionSalas'].'</strong></span><br />';
		$Html.='  <span class="textopequeTITULO">'.htmlentities($Campos['caracteristicas']).'</span>';
		$Html.='  </td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td colspan="4" valign="top" bgcolor="#FFFFFF">'.$Imagen.'</td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td height="25" colspan="4" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">Capacidad: '.$Campos['capacidad'].'</td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td height="25" colspan="4" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">Complementos: '.$TextoRetorno.'</td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td height="25" colspan="4" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">Fecha y horario que desea reservar:</td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td width="72" valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Fecha</td>';
		$Html.='  <td width="7" align="center" valign="middle" class="textopeque" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td width="195" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$fecha_reserva.'</strong></td>';
		$Html.='  <td width="210" rowspan="3" align="center" valign="middle" bgcolor="#EEEEEE"><input type="button" class="gris" onclick="Reservar();" id="b3" value="RESERVAR AHORA" style="cursor:pointer" /></td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Hora Inicio</td>';
		$Html.='  <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$horario_inicio.'</strong></td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Hora Final</td>';
		$Html.='  <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$horario_final.'</strong></td>';
		$Html.='</tr>';
		$Html.='</table>';

		if($this->isEspecial($idsala)){

			$Html.= "<div class='motivo-especial'><span>Motivo: </span><br/><textarea id='motivo-especial' required></textarea></div>";
			$Html.= "<div id='users-contain' class='overlay-especial' ><div></div></div>";

			$Html.= '<script>$(".modal-especial").show();$(".users-contain").show();</script>';

		}else{
			$Html.= '<script>$(".modal-especial").hide();$(".users-contain").hide();</script>';
		}



		return $Html;
	}
	
	
	function Listado($pagina,$idubicacion,$capacidadUno,$capacidadDos,$proyector,$telefono,$fecha_reserva,$horario_inicio,$horario_final){
		global $Utilitario, $idsalaCurrent;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("BusquedaAvanzanda");
		$ControlGrid->SetRegistrosAMostrar(20);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(3);
		$ControlGrid->SetTabla('<table class="grid-Tabla" cellpadding="0" cellspacing="1" style="393">');
		$ControlGrid->SetTablaNothing('<table class="grid-Tabla-Nothing" style="393">');
		$sql=" select * from (
				select s.idsala, CONCAT_WS(' ',u.nombre,'-','Píso',s.piso,'-',s.nombre) as UbicacionSalas,s.capacidad,
						 (select count(sc.idsala) from complementos c, salas_complementos sc
							where sc.idcomplemento=c.idcomplemento and c.idcomplemento=1 and sc.idsala=s.idsala) as proyector,
						 (select count(sc.idsala) from complementos c, salas_complementos sc
							where sc.idcomplemento=c.idcomplemento and c.idcomplemento=2 and sc.idsala=s.idsala) as telefono,
						 r.idreserva 
				  from salas s left outer join reservas r 
						 on r.idsala= s.idsala 
							and date_format(r.fecha_reserva,'%d/%m/%Y') ='".$fecha_reserva."'							
						    and ( r.horario_final > '".$horario_inicio."' AND r.horario_inicio < '".$horario_final."')
							and estado='R',
				  ubicaciones u
				  where u.idubicacion=s.idubicacion 
					and u.idubicacion=".$idubicacion."
			) tb
			where idreserva is null ";
			if(!empty($capacidadUno)) $sql .= " and capacidad BETWEEN ".$capacidadUno." and ".$capacidadDos." ";
			if($proyector!='') $sql .= " and proyector=".$proyector." ";
			if($telefono!='') $sql .= " and telefono=".$telefono." ";
				  
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows='<tr>';
		$Rows.=' <th width="270px" height="27">Ubicaci&oacute;n</th>';
		$Rows.=' <th width="90px">Complementos</th>';	
		$Rows.=' <th width="33px">Ver</th>';
	    $Rows.='</tr>';
		
		$Corre=0;
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Corre++;
			if($Corre==1) $idsalaCurrent=$ArrFila["idsala"];

			//Tineo
			if($this->isEspecial($ArrFila["idsala"]))
				$Rows .= '<tr class="Registros class-especial">';
			else
				$Rows .= '<tr class="Registros " onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';

			$Rows .= ' <td height="27">'.$ArrFila["UbicacionSalas"].'</td>';
			$Rows .= ' <td align="center">'.$this->Complementos($ArrFila["idsala"]).'</td>';
			$Rows .= ' <td align="center"><a href="javascript:;" onclick="DetalleSalaSeleccion('.$ArrFila["idsala"].')"><img src="images/edit.gif" width="15" height="15" border="0" /></a></td>';
			$Rows .= '</tr>';
		}
		return $ControlGrid->GetRegistros($Rows);	
	}

	//Tineo
	function isEspecial($idsala){
		$sql = sprintf("SELECT idsala FROM salas_especiales WHERE idsala = %s", $idsala);
		$results = mysql_query($sql);
		$count = mysql_num_rows($results);
		return ($count>0)?true:false;
	}

	
	function Complementos($id){
		global $Utilitario;
		$sql = "select c.nombre,leyenda from complementos c, salas_complementos sc
				where sc.idcomplemento=c.idcomplemento and sc.idsala=".$id;
		$result=mysql_query($sql);
		$Leyenda='';
		$Titulo='';
		$TextoRetorno='Ninguno';
		while($Campos=mysql_fetch_array($result)){
			$Leyenda.=', '.$Campos['leyenda'];
			$Titulo.=', '.htmlentities($Campos['nombre']);
		}
		if(!empty($Titulo)){
			$TextoRetorno='<span style="cursor:help" title="'.substr($Titulo,2).'">'.substr($Leyenda,2).'<span>';
		}
		return $TextoRetorno;
	  
	}
	
	function DetalleSalaSeleccion(){
		global $Utilitario;
		//Varificar si el paramentro de maximo de horas por dia esta habilitado
		if($_SESSION["tipo"]!='A'){//Solo el administrador del sistema no ingresa a estas validaciones
			$ResultParametroHoras=mysql_query("select hora from config_horas_dias where activo=1");
			$CampoMaximoHoras=mysql_fetch_array($ResultParametroHoras);			
			if(!empty($CampoMaximoHoras['hora'])){
				$ValidarHorasMaximoPorDias=$this->ValidarHorasMaximoPorDia($_POST["HoraInicio"],$_POST["HoraFinal"],$CampoMaximoHoras['hora'].':00');
				if($ValidarHorasMaximoPorDias!='Continuar'){
					echo '{"Validar": "' . 'DENEGADO' . '",
						   "Mensaje": "' . $Utilitario->EliminarTagsHtmlJSON($ValidarHorasMaximoPorDias) . '"
						  }';
					die();
				}
			}		
		}
		
		//Validar Reservas Acumuladas
		if(EstadoReservasAcumuladas == 1 && $_SESSION["tipo"]!='A'){
			$ValidarReservasAcumuladas=$this->ReservasAcumuladas_Y_HorasPorReserva($_POST["Fecha"],$_POST["HoraInicio"],$_POST["HoraFinal"]);
			if($ValidarReservasAcumuladas!='Continuar'){
				echo '{"Validar": "' . 'DENEGADO' . '",
					   "Mensaje": "' . $Utilitario->EliminarTagsHtmlJSON($ValidarReservasAcumuladas) . '"
					  }';
				die();
			}
		}
		
		
		//Varificar si el paramentro de recurrentes esta habilitado
		if($_SESSION["tipo"]!='A'){//Solo el administrador del sistema no ingresa a estas validaciones
			$ResultParametroRecurrentes=mysql_query("SELECT mes,dia FROM config_recurrentes where activo=1");
			$CampoRecurrentes=mysql_fetch_array($ResultParametroRecurrentes);			
			if(!empty($CampoRecurrentes['mes'])){
				$ValidarRecurrentes=$this->ValidarReservasRecurrentes($CampoRecurrentes['mes'],$CampoRecurrentes['dia'],$_POST["idsalaCurrent"]);
				if($ValidarRecurrentes!='Continuar'){
					echo '{"Validar": "' . 'DENEGADO' . '",
						   "Mensaje": "' . $Utilitario->EliminarTagsHtmlJSON($ValidarRecurrentes) . '"
						  }';
					die();
				}
			}
		}
		
		
		
					
	    echo '{ "Validar": "' . 'OK' . '",
				"Detalle": "' . $Utilitario->EliminarTagsHtmlJSON($this->DetalleSala($_POST["idsalaCurrent"],$_POST["Fecha"],$_POST["HoraInicio"],$_POST["HoraFinal"])) . '"
			  }';
	}
	
	function ReservarBusquedaAvanzada(){
		global $Utilitario;
		//Varificar si el paramentro de recurrentes esta habilitado
		if($_SESSION["tipo"]!='A'){//Solo el administrador del sistema no ingresa a estas validaciones
			$ResultParametroRecurrentes=mysql_query("SELECT mes,dia FROM config_recurrentes where activo=1");
			$CampoRecurrentes=mysql_fetch_array($ResultParametroRecurrentes);			
			if(!empty($CampoRecurrentes['mes'])){
				$ValidarRecurrentes=$this->ValidarReservasRecurrentes($CampoRecurrentes['mes'],$CampoRecurrentes['dia'],$_POST['idsalaCurrent']);
				if($ValidarRecurrentes!='Continuar'){
					echo '{"Validar": "' . 'DENEGADO' . '",
						   "Mensaje": "' . $Utilitario->EliminarTagsHtmlJSON($ValidarRecurrentes) . '"
						  }';
					die();
				}
			}
		}
		
		$idubicacion = $_POST["idubicacion"];
		$Codigo=$this->NumeroOrden();
		
				
		//El nuevo requerimiento para ue el administrador asigne una reserva a cualquier usuario		
		if($_SESSION["tipo"]=='A' && !empty($_POST['UsuarioAsignar'])){//Si es el administrador del sistema podra asignar reservas espeaciales			
			$sql=sprintf(" INSERT INTO reservas(idubicacion,idsala,idevento,idusuario,codigo,fecha_reserva,horario_inicio,horario_final,asistentes,reserva_especial,idadmin,estado,fecha_registro) 
									values(%s,%s,%s,%s,'%s','%s','%s','%s','%s',%s,%s,'R',NOW()) ",
				 $_POST['idubicacion'],$_POST['idsalaCurrent'],$_POST['idevento'],$_POST['UsuarioAsignar'],$Codigo,$Utilitario->cambiaf_a_mysql($_POST['Fecha']),
				 $_POST['HoraInicio'],$_POST['HoraFinal'],$_POST['asistentes'],1,$_SESSION["idusuario"]);
		}else{
			$sql=sprintf(" INSERT INTO reservas(idubicacion,idsala,idevento,idusuario,codigo,fecha_reserva,horario_inicio,horario_final,asistentes,estado,fecha_registro) 
									values(%s,%s,%s,%s,'%s','%s','%s','%s','%s','R',NOW()) ",
				 $_POST['idubicacion'],$_POST['idsalaCurrent'],$_POST['idevento'],$_SESSION["idusuario"],$Codigo,$Utilitario->cambiaf_a_mysql($_POST['Fecha']),
				 $_POST['HoraInicio'],$_POST['HoraFinal'],$_POST['asistentes']);
		}

		mysql_query($sql);
		$rid = mysql_insert_id();

		if(!empty($_POST["motivo_especial"])){
			$last =  mysql_insert_id();
			$procedure = sprintf("CALL proc_reservas_especiales ('%s', %s, %s)",
				$_POST["motivo_especial"], $_POST['idsalaCurrent'], $last);
			mysql_query($procedure);



			//include_once  '../includes/EnvioCorreo.php';
			//$er = new EnvioEmail();
			//$er->Enviar("it","Prueba","Email de prueba de envio");


		}

		$procedure = sprintf("CALL proc_reservas_especiales (%s, %s, %s)",
			"intento esp", 3, 4);
		mysql_query($procedure);

		//FIX TRIGGER CLEARDB
		$trigger = "INSERT INTO reservas_notificaciones (idreserva, hash, state, notify)
    					VALUES ($rid, MD5(CONCAT($rid,'1')), 0, 0)";
		mysql_query($trigger);

		echo '{"Validar": "' . 'OK' . '"}';
	}
	
	function ReservarBusquedaPorSala(){
		global $Utilitario;	
		$idubicacion = $_POST["idubicacion"];
		$Codigo=$this->NumeroOrden();
		
		//El nuevo requerimiento para ue el administrador asigne una reserva a cualquier usuario		
		if($_SESSION["tipo"]=='A' && !empty($_POST['UsuarioAsignar'])){//Si es el administrador del sistema podra asignar reservas espeaciales			
			$sql=sprintf(" INSERT INTO reservas(idubicacion,idsala,idevento,idusuario,codigo,fecha_reserva,horario_inicio,horario_final,asistentes,reserva_especial,idadmin,estado,fecha_registro) 
									values(%s,%s,%s,%s,'%s','%s','%s','%s','%s',%s,%s,'R',NOW()) ",
				 $_POST['idubicacion'],$_POST['idsalaCurrent'],$_POST['idevento'],$_POST['UsuarioAsignar'],$Codigo,$Utilitario->cambiaf_a_mysql($_POST['Fecha']),
				 $_POST['HoraInicio'],$_POST['HoraFinal'],$_POST['asistentes'],1,$_SESSION["idusuario"]);
		}else{
			$sql=sprintf(" INSERT INTO reservas(idubicacion,idsala,idevento,idusuario,codigo,fecha_reserva,horario_inicio,horario_final,asistentes,estado,fecha_registro) 
									values(%s,%s,%s,%s,'%s','%s','%s','%s','%s','R',NOW()) ",
				 $_POST['idubicacion'],$_POST['idsalaCurrent'],$_POST['idevento'],$_SESSION["idusuario"],$Codigo,$Utilitario->cambiaf_a_mysql($_POST['Fecha']),
				 $_POST['HoraInicio'],$_POST['HoraFinal'],$_POST['asistentes']);
		}
		mysql_query($sql);
		$rid = mysql_insert_id();

		if(!empty($_POST["motivo_especial"])){
			$last =  mysql_insert_id();
			$procedure = sprintf("CALL proc_reservas_especiales ('%s', %s, %s)",
				$_POST["motivo_especial"], $_POST['idsalaCurrent'], $last);
			mysql_query($procedure);


			//include_once  '../includes/EnvioCorreo.php';
			//$er = new EnvioEmail();
			//$er->Enviar("it","Prueba","Email de prueba de envio");

		}

		//Si la reserva es desde sala enviar las fechas
		if(!empty($_POST["FechaBuscador"])){
			$this->BusquedaPorSala($_POST["FechaBuscador"],$_POST["idsalaCurrent"],false);
		}

		//FIX TRIGGER CLEARDB
		$trigger = "INSERT INTO reservas_notificaciones (idreserva, hash, state, notify)
    					VALUES ($rid, MD5(CONCAT($rid,'1')), 0, 0)";
		mysql_query($trigger);

		
	}	
	
	function NumeroOrden(){
		global $Utilitario;
		$newNumero=strtoupper($Utilitario->CodigoUnicoAleatorio(7));
		$resultCod = mysql_query(" SELECT count(*) FROM reservas where codigo='".$newNumero."' ");
		if(mysql_num_rows($resultCod)){
			mysql_free_result($resultCod);
			return $newNumero;
		}else{
			$this->NumeroOrden();
		}		
	} 
	
	
	//Busqueda por salas 
	
	function ComboPisos(){
		global $Utilitario;
		$Result=mysql_query('select distinct piso idpiso, piso from salas where idubicacion='.$_POST['id'].' order by piso asc');
		$Html='<select class="gris" id="ddlPiso" onchange="ComboSalas(this.value);"><option value="">-----------</option>';
		while($Campo=mysql_fetch_array($Result)){
			$Html.='<option value="'.$Campo['idpiso'].'">'.$Utilitario->convertir_utf8($Campo['piso']).'</option>';	
		}
		$Html.='</select>';
		echo $Html;
	}
	
	function ComboSalas(){
		global $Utilitario;
		$Result=mysql_query('select idsala, nombre from salas where idubicacion='.$_POST['idubicacion'].' and piso='.$_POST['piso'].' order by nombre asc');
		$Html='<select class="gris" id="ddlSala"><option value="">-----------</option>';
		while($Campo=mysql_fetch_array($Result)){
			if($this->isEspecial($Campo['idsala'])){
				$Html .= '<option class="option-especial" value="' . $Campo['idsala'] . '">' . $Utilitario->convertir_utf8($Campo['nombre'])." (Solo Videoconferencia)" . '</option>';
			}else{
				$Html .= '<option  value="' . $Campo['idsala'] . '">' . $Utilitario->convertir_utf8($Campo['nombre']) . '</option>';
			}
		}
		$Html.='</select>';
		echo $Html;
	}
	//Cuando se realiza una reserva se vuelve a llamar a esta funcion, en este caso no se realizara la validacion, pero si es realizado una busqueda es requerido realizar la validacion
	function BusquedaPorSala($Fecha,$Sala,$RealizarValidacion){
		global $Utilitario;
		
		//Varificar si el paramentro de recurrentes esta habilitado
		if($_SESSION["tipo"]!='A' && $RealizarValidacion==true){//Solo el administrador del sistema no ingresa a estas validaciones
			$ResultParametroRecurrentes=mysql_query("SELECT mes,dia FROM config_recurrentes where activo=1");
			$CampoRecurrentes=mysql_fetch_array($ResultParametroRecurrentes);			
			if(!empty($CampoRecurrentes['mes'])){
				$ValidarRecurrentes=$this->ValidarReservasRecurrentes($CampoRecurrentes['mes'],$CampoRecurrentes['dia'],$Sala);
				if($ValidarRecurrentes!='Continuar'){
					echo '{"Validar": "' . 'DENEGADO' . '",
						   "Mensaje": "' . $Utilitario->EliminarTagsHtmlJSON($ValidarRecurrentes) . '"
						  }';
					die();
				}
			}
		}
		
		$ArrFilasHoras=Array();
		$ArrColumnasFechas=Array();
		$ArrFechaFeriado=Array();
		$ArrFechaAnteriores=Array();
		$ArrBancoFechasHoras=Array();
		
		
		//$ArrFilasHoras=array("08:00", "08:30", "09:00","09:30","10:00","10:30","11:00","11:30","12:00","12:30","13:00","13:30","14:00","14:30","15:00","15:30","16:00","16:30","17:00","17:30","18:00");
		
		
		
		$NumGlobalFechas=0;
		$FechaRangoInicio='';
		$FechaRangoFinal='';
		
		//Consulta de fechas
		$FechaEnvio=$Fecha;
		$FechaHoy=date("Y-m-d");
		$FechaHastaFormat=str_replace(",","-",FechaHasta);//Y,m,d	
		$FechaHasta=date("Y-m-d", strtotime("$FechaHastaFormat +1 month"));		
		
		//Llenar los dias no laborables
		$ResultFechasFeriados=mysql_query("select fecha from fechas_especiales where date(fecha) BETWEEN date(NOW()) and date(DATE_ADD(NOW(),INTERVAL ".(NumeroPosteriorMeses+1)." MONTH)) order by fecha asc ");
		
		$i=0;
		while($Campos=mysql_fetch_array($ResultFechasFeriados)){
			$ArrFechaFeriado[$i++]=$Utilitario->cambiaf_a_normal($Campos['fecha']);
		}
	
		$Fecha=date($Utilitario->cambiaf_a_mysql($FechaEnvio));
		$Menos=1;
		//Llennar la cabecera de resultado
		$resultDet=mysql_query("select s.idsala, s.idubicacion, s.nombre, s.piso, s.capacidad, s.caracteristicas, s.imagen, 
									CONCAT_WS(' ',u.nombre,'-','Píso',s.piso,'-',s.nombre) as UbicacionSalas,
									(select se.idevento from salas_eventos se where se.idsala=s.idsala) as idevento,
									horario_inicio, horario_final
							  from salas s, ubicaciones u 
							 where u.idubicacion=s.idubicacion 
							   and s.idsala=".$Sala);
		$CamposDet=mysql_fetch_array($resultDet);
		
		//llenar el rango de hora segun la ubicacion
		$HoraRangoInicio=$CamposDet['horario_inicio'];
		$HoraRangoFinal=$CamposDet['horario_final'];	
		$ArrFilasHoras[$Count++] = $HoraRangoInicio;
		while($HoraRangoInicio!=$HoraRangoFinal){
			list($Hora, $Minutos) = split(':', $HoraRangoInicio);
			$HoraRangoInicio=date("H:i", mktime($Hora, $Minutos+30, 0));				
			$ArrFilasHoras[$Count++] = $HoraRangoInicio;
		}
				
		//Complementos
		$resultComple=mysql_query("select c.nombre,leyenda from complementos c, salas_complementos sc
				where sc.idcomplemento=c.idcomplemento and sc.idsala=".$Sala);
		$Titulo='';
		$DetComplemento='Ninguno';
		while($CamposComple=mysql_fetch_array($resultComple)){
			$Titulo.=', '.htmlentities($CamposComple['nombre']);
		}
		if(!empty($Titulo)){
			$DetComplemento=substr($Titulo,2);
		}		
		$Html='<table width="900px" border="0" align="center" cellpadding="3" cellspacing="1" id="tbDetalleFechas" bgcolor="#CCCCCC">';		
		$Html.='<tr>';
		$Html.='<td height="38" colspan="9" valign="top" bgcolor="#FFFFFF">';
		$Html.='<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$Html.='<tr>';
		$Html.='<td width="458" height="38" valign="middle" class="SUBTituloC">BUSQUEDA EN '.strtoupper($CamposDet['UbicacionSalas']).'</td>';
		$Html.='<td width="431" valign="middle" class="textopequeTITULO" align="right" style="padding-right:6px;">Capacidad: '.$CamposDet['capacidad'].'&nbsp;&nbsp;&nbsp;&nbsp;Complementos: '.$DetComplemento.'</td>';
		$Html.='</tr>';
		$Html.='</table>';
		$Html.='</td>';
		$Html.='</tr>';
		
		
		$Html.='<tr>';
		$ContenidoFechasMenos='';
		$ContenidoFechasMas='';
		//*********************************Recorre las fechas anterior**************************************
		$NumMostrar=1;
		for($Restar=1; $Restar<=10; $Restar++){			
			$CurrentDate=date("d/m/Y", strtotime("$Fecha -$Restar day"));
			$DiaSemana=$Utilitario->WeekDay($CurrentDate);
			//Validar que la fecha esta entre lunes y vienes, y no laborables, que no sea menor a la fecha de hoy y menor igual a tres
			if($DiaSemana>=0 && $DiaSemana<=4 && !in_array($CurrentDate,$ArrFechaFeriado) && strtotime($Utilitario->cambiaf_a_mysql($CurrentDate))>=strtotime($FechaHoy) && $NumMostrar<=3){
				$ArrFechaAnteriores[$Restar]=$CurrentDate;
				$NumMostrar++;
				$FechaRangoInicio=$CurrentDate;
			}
		}
		//Ordenar las fechas de menor a mayor
		if(sizeof($ArrFechaAnteriores)){//Varificar si hay fechas en el array
			sort($ArrFechaAnteriores);
			foreach($ArrFechaAnteriores as $key => $FechaMostrar){
				$ContenidoFechasMenos.='<td width="60px" align="center" bgcolor="#EFEFEF" class="SubTitulo over cabeceras" id="'.str_replace("/","",$FechaMostrar).'" >'.$FechaMostrar.'</td>';
				$ArrColumnasFechas[$NumGlobalFechas++]=$FechaMostrar;				
			}
		}else{//Si no hay fechas anteriores ingresar la fecha de envio en el rango inicial para la consultas de reservas
			$FechaRangoInicio=$FechaEnvio;
		}
		
		//Agregar la fecha enviado
		$ArrColumnasFechas[$NumGlobalFechas++]=$FechaEnvio;	
		
		//********************************Recorre las fechas superior*****************************************
		$NumMostrar=1;
		for($Sumar=1; $Sumar<=10; $Sumar++){
			$CurrentDate=date("d/m/Y", strtotime("$Fecha +$Sumar day"));
			$DiaSemana=$Utilitario->WeekDay($CurrentDate);
			//Validar que la fecha esta entre lunes y vienes, y no laborables, que sea menor a la fecha maxima y menor igual a tres
			if($DiaSemana>=0 && $DiaSemana<=4 && !in_array($CurrentDate,$ArrFechaFeriado) && strtotime($Utilitario->cambiaf_a_mysql($CurrentDate))<=strtotime($FechaHasta) && $NumMostrar<=3){						
				$ContenidoFechasMas.='<td width="60px" align="center" bgcolor="#EFEFEF" class="SubTitulo over cabeceras" id="'.str_replace("/","",$CurrentDate).'">'.$CurrentDate.'</td>';
				$NumMostrar++;
				$ArrColumnasFechas[$NumGlobalFechas++]=$CurrentDate;			
				$FechaRangoFinal=$CurrentDate;
			}		
		}
		//Si no hay fechas superiores ingresar la fecha de envio en el rango final para la consultas de reservas
		if(empty($FechaRangoFinal)) $FechaRangoFinal=$FechaEnvio;
		
		
		$Html.='<td width="60px" align="center" class="textopequeTITULO">Hora / Fecha</td>'.$ContenidoFechasMenos.'<td class="SubTitulo over cabeceras" width="60px" align="center" bgcolor="#EFEFEF" id="'.str_replace("/","",$FechaEnvio).'"><strong>'.$FechaEnvio.'</strong></td>'.$ContenidoFechasMas;		
		$Html.='</tr>';
		
		//Llenar las fechas y horas reservadas
		$ResultReservados=mysql_query("select r.fecha_reserva,r.horario_inicio, r.horario_final, su.nombres_apellidos from reservas r, seg_usuarios su
										where r.estado='R'
										 and su.idusuario=r.idusuario
										 and r.fecha_reserva BETWEEN '".$Utilitario->cambiaf_a_mysql($FechaRangoInicio)."' and '".$Utilitario->cambiaf_a_mysql($FechaRangoFinal)."'  and r.idsala=".$Sala);
		$Count=0;
		$ArrTitle = array();
		while($CamposReservados=mysql_fetch_array($ResultReservados)){
			$FechaDB=$Utilitario->cambiaf_a_normal($CamposReservados['fecha_reserva']);//'12/07/2011';
			$HoraInicioDB=$CamposReservados['horario_inicio'];//'10:00';
			$HoraFinalDB=$CamposReservados['horario_final'];//'12:00';			
			if(!in_array($FechaDB.'-'.$HoraInicioDB, $ArrBancoFechasHoras)) {
				$ArrBancoFechasHoras[$Count++] = $FechaDB.'-'.$HoraInicioDB;
				$ArrTitle['RESERVADO POR : '.htmlentities($CamposReservados['nombres_apellidos']).'  '.$CamposReservados['horario_inicio'].' - '.$CamposReservados['horario_final'].'ƒ'.$Count]=$FechaDB.'-'.$HoraInicioDB;
			}
			while($HoraInicioDB!=$HoraFinalDB){
				list($Hora, $Minutos) = split(':', $HoraInicioDB);
				$HoraInicioDB=date("H:i", mktime($Hora, $Minutos+30, 0));	
				if(!in_array($FechaDB.'-'.$HoraInicioDB, $ArrBancoFechasHoras)) {
					$ArrBancoFechasHoras[$Count++] = $FechaDB.'-'.$HoraInicioDB;
					$ArrTitle['RESERVADO POR : '.htmlentities($CamposReservados['nombres_apellidos']).'  '.$CamposReservados['horario_inicio'].' - '.$CamposReservados['horario_final'].'ƒ'.$Count]=$FechaDB.'-'.$HoraInicioDB;
				}				
			}			
		}
		//Construir los campos de seleccion		
		foreach($ArrFilasHoras as $FilasHoras){
			$Hora=str_replace(":","",$FilasHoras);
			$Html.='<tr>';
			$Html.='<td width="60px" height="20px" align="center" bgcolor="#efefef" class="negro over" id="'.$Hora.'">'.$FilasHoras.'</td>';
			foreach($ArrColumnasFechas as $ColumnaFechas){				
				$Fechas=str_replace("/","",$ColumnaFechas);				
				if(in_array($ColumnaFechas.'-'.$FilasHoras, $ArrBancoFechasHoras)){//Hora no Disponible
					$Title=array_search($ColumnaFechas.'-'.$FilasHoras, $ArrTitle);
					$Title=explode('ƒ',$Title);//onmouseover="ddrivetip(\''.$Title[0].'\')" onmouseout="hideddrivetip();"
					$Html.='<td width="60px" align="center" bgcolor="#fff0f0" onmouseover="ddrivetip(\''.$Title[0].'\');AyudaHover(this,\''.$Hora.'\',\''.$Fechas.'\')" onmouseout="hideddrivetip();AyudaOut(this)" class="'.$Fechas.'" disp="0" Hora="'.$Hora.'" Fechas="'.$Fechas.'" HoraFormat="'.$FilasHoras.'" FechasFormat="'.$ColumnaFechas.'"></td>';
				}else{//Hora Disponible
					$ColorCelda='#e8f1ff';
					if($FechaEnvio==$ColumnaFechas) $ColorCelda='#efefef';
					$Html.='<td width="60px" align="center" bgcolor="'.$ColorCelda.'" onmouseover="AyudaHover(this,\''.$Hora.'\',\''.$Fechas.'\')" onmouseout="AyudaOut(this)" class="'.$Fechas.'" disp="1" Hora="'.$Hora.'" Fechas="'.$Fechas.'" HoraFormat="'.$FilasHoras.'" FechasFormat="'.$ColumnaFechas.'"></td>';
				}
			}
			$Html.='</tr>';			
		}
		echo '{"Validar": "' . 'OK' . '",
			   "Cuadro": "' . $Utilitario->EliminarTagsHtmlJSON($Html) . '"
			  }';
	}
	
	
	function ValidarHorasMaximoPorDia($HoraInicioEnvio,$HoraFinalEnvio,$HorasMaximoPorDia){
		global $Utilitario;	
		
		//***************************************************************************************************************************************************
		//Antes de validar con las reservas realizados, se necesita validar el rango de horario que envia el usuario, con el parametro horas Maximo por día *
		//***************************************************************************************************************************************************
		//Restar para saber la diferencia de horas a reservar
		$HoraEnvio=$Utilitario->RestarHoras($HoraInicioEnvio,$HoraFinalEnvio);
		
		//esta funcion retorna en formta: 01-30-00, convertir a: 01:30
		list($HoraX, $MinutosX, $SegundosX) = split('-', $HoraEnvio);
		$HoraX=(int)$HoraX;
		$HoraEnvio=$HoraX.':'.$MinutosX;		
		//Verificar si esta en el rango con el parametro		
		 if($_SESSION["tipo"]!='A'){		 	
			if($HoraEnvio>$HorasMaximoPorDia){
				return 'Estimado usuario, el horario de la reserva no debe superar a <strong>'.$this->FormatHora($HorasMaximoPorDia).' por día</strong>, por favor modifique el rango de horas.';
			 }
		 }
		 
		 
		 
		 /*
		 $MostrarPrueba=RestarHoras('13:00','14:30');
		 $MostrarPrueba=$MostrarPrueba.'<br />'.SumarHora("5:50","1:30");
		*/	 
		 
		 $HtmlReservados.='<table width="420px" border="0" align="left" cellpadding="0" cellspacing="0">';
		 $HtmlReservados.='<tr>';
		 $HtmlReservados.='<td class="textopequeTITULO">No será posible realizar su reserva, por que superó el máximo de '.$this->FormatHora($HorasMaximoPorDia).' por día, a continuación se detalla las reservas que se realizó el día de hoy:</td>';
		 $HtmlReservados.='</tr>';
		 $HtmlReservados.='<tr><td height="8px"></td></tr>';
		 $HtmlReservados.='<tr>';
		 $HtmlReservados.='<td>';
		 $HtmlReservados.='<table width="420px" border="0" align="left" cellpadding="3" cellspacing="1" bgcolor="#CCCCCC">';		
		 $HtmlReservados.='<tr>';
		 $HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="40px"><strong>Código</strong></td>';
		 $HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Fecha</strong></td>';
		 $HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Hora Inicio</strong></td>';
		 $HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Hora Final</strong></td>';
		 $HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="170px"><strong>Horas Reservado</strong></td>';
		 $HtmlReservados.='</tr>';
		 //$HoraFinal='0:00';
		 $HoraFinal='0:00';// para realizar el calculo con la hora de envio
		 $HoraCurent='0:00';
		 //La version anterior se realizaba por la fecha actual, ahora se realizara por la fecha que el usuario envia
		 $FechaActual=$Utilitario->cambiaf_a_mysql($_POST["Fecha"]);		 
		 //$ResultReservados=mysql_query("select codigo,fecha_reserva,horario_inicio,horario_final from reservas where estado='R' and date_format(fecha_registro,'%Y/%m/%d')=date_format(NOW(),'%Y/%m/%d') and idusuario=".$_SESSION["idusuario"]);
		 $ResultReservados=mysql_query("select codigo,fecha_reserva,horario_inicio,horario_final 
		 								from reservas 
										where estado='R'
										and reserva_especial is null 
										and date_format(fecha_reserva,'%Y/%m/%d')=date('".$FechaActual."') 
										and idusuario=".$_SESSION["idusuario"]);
		 //Recorrer los horarios reservados segun la fecha			  
		 $CantidadReservados=0;
		 while($CamposHorasReservados=mysql_fetch_array($ResultReservados)){
			//Restar para saber la diferencia de horas reservados 
			$HoraCurent=$Utilitario->RestarHoras($CamposHorasReservados['horario_inicio'],$CamposHorasReservados['horario_final']);
			
			//esta funcion retorna en formta: 01-30-00, convertir a: 01:30
			list($Hora, $Minutos, $Segundos) = split('-', $HoraCurent);
			$HoraCurent=$Hora.':'.$Minutos;	
			
			//Sumar las horas del recorrido
			$HoraFinal=$Utilitario->SumarHoras($HoraFinal,$HoraCurent);
			$HtmlReservados.='<tr>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['codigo'].'</td>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$Utilitario->cambiaf_a_normal($CamposHorasReservados['fecha_reserva']).'</td>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['horario_inicio'].'</td>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['horario_final'].'</td>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$this->FormatHora($HoraCurent).'</td>';
			$HtmlReservados.='</tr>';
		 }
		  $HoraFinalReservados=$HoraFinal;
		 //Sumar la hora de envio
		 $HoraFinal=$Utilitario->SumarHoras($HoraFinal,$HoraEnvio);
		 
		 $HtmlReservados.='</table>';
		 $HtmlReservados.='</td>';
		 $HtmlReservados.='</tr>';
		 $HtmlReservados.='<tr><td height="8px"></td></tr>';
		 $HtmlReservados.='<tr>';
		 $HtmlReservados.='<td class="textopequeTITULO">Actualmente tienes <strong>'.$this->FormatHora($HoraFinalReservados).'</strong> reservados, ahora necesitas reservar <strong>'.$this->FormatHora($HoraEnvio).'</strong>, el resultado es <strong>'.$this->FormatHora($HoraFinal).'</strong>, en la cual supera el máximo de <strong>'.$this->FormatHora($HorasMaximoPorDia).'</strong> por día.</td>';
		 $HtmlReservados.='</tr>';
		 $HtmlReservados.='</table>';
		 //Verificar que el resultado de las horas reservados no se mayor al parametro	
		 //return $HoraFinal .' '.$HorasMaximoPorDia .' '.$FechaActual; 
		 if($HoraFinal>$HorasMaximoPorDia){
			return $HtmlReservados;
		 }		 
		 return 'Continuar';
	}
	
	function ValidarReservasRecurrentes($Mes,$Dia,$IDsala){
		global $Utilitario;
		$MesParaElRango=$Mes+1;
		$Result=mysql_query("select codigo,fecha_reserva,horario_inicio,horario_final from reservas 
							where estado='R'
							and reserva_especial is null
							and date(fecha_reserva) BETWEEN date(NOW()) and date(DATE_ADD(NOW(),INTERVAL ".$MesParaElRango." MONTH))
							and idsala=".$IDsala."
							and idusuario=".$_SESSION["idusuario"]);		
		$TotalResevado=0;
		$HtmlReservados.='<table width="420px" border="0" align="left" cellpadding="0" cellspacing="0">';
		$HtmlReservados.='<tr>';
		$HtmlReservados.='<td class="textopequeTITULO">Estimado usuario, no será posible realizar su reserva, porque las reservas recurrente de salas solo se aceptara para '.$Dia.' veces dentro del plazo de un '.$Mes.' mes(es), a continuación se detalla las reservas que se realizaron:</td>';
		$HtmlReservados.='</tr>';
		$HtmlReservados.='<tr><td height="8px"></td></tr>';
		$HtmlReservados.='<tr>';
		$HtmlReservados.='<td>';
		$HtmlReservados.='<table width="420px" border="0" align="left" cellpadding="3" cellspacing="1" bgcolor="#CCCCCC">';		
		$HtmlReservados.='<tr>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="40px"><strong>Código</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Fecha</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Hora Inicio</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="70px"><strong>Hora Final</strong></td>';
		$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#F7F8F9" width="170px"><strong>Horas Reservado</strong></td>';
		$HtmlReservados.='</tr>';
		
		while($CamposHorasReservados=mysql_fetch_array($Result)){
			$TotalResevado++;
			//Restar para saber la diferencia de horas reservados 
			$HoraCurent=$Utilitario->RestarHoras($CamposHorasReservados['horario_inicio'],$CamposHorasReservados['horario_final']);
			
			//esta funcion retorna en formta: 01-30-00, convertir a: 01:30
			list($Hora, $Minutos, $Segundos) = split('-', $HoraCurent);
			$HoraCurent=$Hora.':'.$Minutos;	
			$HtmlReservados.='<tr>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['codigo'].'</td>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$Utilitario->cambiaf_a_normal($CamposHorasReservados['fecha_reserva']).'</td>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['horario_inicio'].'</td>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$CamposHorasReservados['horario_final'].'</td>';
			$HtmlReservados.=' <td class="textopequeTITULO" bgcolor="#FFFFFF">'.$this->FormatHora($HoraCurent).'</td>';
			$HtmlReservados.='</tr>';
		}
		$HtmlReservados.='</table>';
		$HtmlReservados.='</td>';
		$HtmlReservados.='</tr>';
		$HtmlReservados.='</table>';
		if($TotalResevado>=$Dia){
			return $HtmlReservados;
		}
		return 'Continuar';
	}
	
	
	
	function FormatHora($Hora){
		$TextoMinutos='';
		list($HoraF, $MinutosF) = split(':', $Hora);
		if($MinutosF!='00'){
			$TextoMinutos=' y '.$MinutosF.' minutos';
		}
		$TextoHora=(int)$HoraF>1?' horas':' hora';
		return (int)$HoraF.$TextoHora.$TextoMinutos;
	}
	
	function RangoHoras(){
        $Result=mysql_query("select horario_inicio, horario_final from ubicaciones where idubicacion=".$_POST['idubicacion']);
        $Campo=mysql_fetch_array($Result);
        $RangoUnoInicio=$Campo['horario_inicio'];
        list($Hora, $Minutos) = split(':', $RangoUnoInicio);
        $RangoDosInicio=date("H:i", mktime($Hora, $Minutos+30, 0));
        list($Hora, $Minutos) = split(':', $Campo['horario_final']);
        $RangoDosFinal=$Campo['horario_final'];
        $RangoUnoFinal=date("H:i", mktime($Hora, $Minutos-30, 0));   
       
        echo '{ "RangoUnoInicio": "' . $RangoUnoInicio . '",
                "RangoUnoFinal": "' . $RangoUnoFinal . '",
                "RangoDosInicio": "' . $RangoDosInicio . '",
                "RangoDosFinal": "' . $RangoDosFinal . '"
             }';
    }
	
	function PopUpUsuarios($pagina,$BuscarUsuario){
		global $Utilitario;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("PaginarBuscar");
		$ControlGrid->SetRegistrosAMostrar(10);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(4);
		//$ControlGrid->BotonEliminar("btn_Eliminar","Eliminar");//Nombre boton,nombre funcion		
		$sql=" SELECT u.idusuario,u.codigo,u.nombres_apellidos nombres,email,acceso, CONCAT_WS(' - ',codigo,nombres_apellidos) CodigoNombres 
					FROM seg_usuarios u where u.tipo<>'A'"; //Menos los administradores
		if(!empty($BuscarUsuario)){
			$sql .=" and locate('".$Utilitario->mixed_to_latin1($BuscarUsuario)."', CONCAT_WS('   ',codigo,nombres_apellidos,email)) > 0 " ;
		}
		
					
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows =  '<tr>
					<th width="20px" height="27"></th>
					<th width="80px">C&oacute;digo</th>
					<th width="347px">Usuario</th>
					<th width="245px">Email</th>
				  </tr>';		
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Rows .= '<tr class="Registros" onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td><input type="checkbox" key="'.$ArrFila["idusuario"].'" codigo="'.htmlentities($ArrFila["codigo"]).'" nombres="'.htmlentities($ArrFila["nombres"]).'" email="'.htmlentities($ArrFila["email"]).'" nombres="'.htmlentities($ArrFila["nombres"]).'" onclick="AsignarUsuario(this)" /></td>';
			$Rows .= ' <td>'.$ArrFila["codigo"].'</td>';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["nombres"],ENT_QUOTES,"iso-8859-1").'</td>';
			$Rows .= ' <td>'.$ArrFila["email"].'</td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}	
	
}




?>
