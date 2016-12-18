<?
if(!empty($_POST["command"])){
	include('../config.php');//Si utiliza ajax es requerido importar la configuracion
	include("../includes/GridPaginador.php");
	include('../includes/util.php');	
}else{
	include("includes/GridPaginador.php");
	include('includes/util.php');
}

$idreservaCurrent;

 
$Utilitario = new Utilitario;
$Ubicacion = new Reservados;

if($_POST["command"]=='DetalleSalaSeleccion'){
	$Ubicacion->DetalleSalaSeleccion();
}else if($_POST["command"]=='DetalleSala'){
	$Ubicacion->DetalleSala();
}else if($_POST["command"]=='DetalleSalaRestaurar'){
	$Ubicacion->DetalleSalaRestaurar();
}else if($_POST["command"]=='Buscar'){
	$Ubicacion->Listado($_POST['pagina'],$_POST['Desde'],$_POST['Hasta'],$_POST['Ubicacion'],$_POST['Piso'],$_POST['Sala'],$_POST['Usuario']);
}else if($_POST["command"]=='Restaurar'){
	$Ubicacion->Restaurar();
}else if($_POST["command"]=='ComboPisos'){
	$Ubicacion->ComboPisos();
}else if($_POST["command"]=='ComboSalas'){
	$Ubicacion->ComboSalas();
}else if($_REQUEST['identifier']=='Usuarios'){
	$User->AutoComplete();
}

class Reservados{

	function Listado($pagina,$Desde,$Hasta,$Ubicacion,$Piso,$Sala,$Usuario){
		global $Utilitario, $idreservaCurrent;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("Paginar");
		$ControlGrid->SetRegistrosAMostrar(15);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(6);
		$ControlGrid->SetTabla('<table class="grid-Tabla" cellpadding="0" cellspacing="1" style="503">');
		$ControlGrid->SetTablaNothing('<table class="grid-Tabla-Nothing" style="503">');
		$sql=" select r.idreserva,s.idsala, r.codigo, CONCAT_WS(' ',u.nombre,'-','Piso',s.piso,'-',s.nombre) as UbicacionSalas,r.fecha_reserva,
					  DATEDIFF(r.fecha_reserva,NOW()) diasFaltan,su.nombres_apellidos, su.codigo, r.horario_inicio,r.horario_final, r.estado		  
			  from salas s, reservas r, ubicaciones u, seg_usuarios su
			  where u.idubicacion=s.idubicacion
				 and r.idsala= s.idsala
				 and su.idusuario=r.idusuario ";	
		if(!empty($Desde)){
			$sql.=" AND DATE(r.fecha_reserva) BETWEEN '".$Utilitario->cambiaf_a_mysql($Desde)."' AND '".$Utilitario->cambiaf_a_mysql($Hasta)."'";
		}  
		if(!empty($Ubicacion)){
			$sql.=" AND s.idubicacion=".$Ubicacion." ";
		}
		if(!empty($Piso)){
			$sql.=" AND s.piso=".$Piso." ";
		}
		if(!empty($Sala)){
			$sql.=" AND s.idsala=".$Sala." ";
		}
		if(!empty($Usuario)){
			$sql .=" AND locate('".$Utilitario->mixed_to_latin1($Usuario)."',CONCAT_WS('   ',su.codigo,su.nombres_apellidos,su.email)) > 0 " ;
		}
			
		$sql .="order by r.fecha_reserva desc";		  
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows='<tr>';
		$Rows.=' <th width="70px">C&oacute;digo</th>';
		$Rows.=' <th width="257px" height="27">Usuario</th>';		
		$Rows.=' <th width="230px">Ubicaci&oacute;n</th>';
		$Rows.=' <th width="170px">Fecha y Hora de Reserva</th>';	
		$Rows.=' <th width="90px">Estado</th>';	
		$Rows.=' <th width="63px" style="text-align:center;">Acci&oacute;n</th>';
	    $Rows.='</tr>';
		
		$Corre=0;
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Corre++;
			if($Corre==1) $idreservaCurrent=$ArrFila["idreserva"];
			/*$TextoEstado='ATENDIDO';
			if($ArrFila["diasFaltan"]>=0) $TextoEstado='RESERVADO';*/
			
			//Validar Los estado de las reservas
			$Style='';
			$Restaurar='';
			if($ArrFila["diasFaltan"]>=0){//Por atender
				$TextoEstado='RESERVADA';
				if($ArrFila["estado"]=='E'){
					$TextoEstado='ELIMINADO';
					$Style='style="background-color: #FFF4F4;"';
					$Restaurar='<a href="javascript:;" onclick="DetalleSalaRestaurar('.$ArrFila["idreserva"].')"><img src="images/restaurar.png" width="16" height="16" border="0" alt="Restaurar Reserva" title="Restaurar sala reservada" /></a>&nbsp;';
				}
			}else{//Atendido
				$TextoEstado='ATENDIDO';
				if($ArrFila["estado"]=='E'){
					$TextoEstado='ELIMINADO';
					$Style='style="background-color: #FFF4F4;"';
				}
			}
			
			
			$Rows .= '<tr class="Registros" '.$Style.' onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td>'.$ArrFila["codigo"].'</td>';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["nombres_apellidos"]).'</td>';			
			$Rows .= ' <td>'.$ArrFila["UbicacionSalas"].'</td>';
			$Rows .= ' <td>'.$Utilitario->cambiaf_a_normal($ArrFila["fecha_reserva"]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ArrFila["horario_inicio"].' - '.$ArrFila["horario_final"].'</td>';
			$Rows .= ' <td>'.$TextoEstado.'</td>';
			$Rows .= ' <td align="center">
			            '.$Restaurar.'
						<a href="javascript:;" onclick="DetalleSalaSeleccion('.$ArrFila["idreserva"].')"><img src="images/edit.gif" width="15" height="15" border="0" alt="Ver detalle de la reserva" title="Ver detalle de la reserva" /></a>
						<a href="elimSALA.php?idres='.$ArrFila["idreserva"].'"><img src="images/ic-eliminar.gif" width="15" height="15" border="0" alt="Eliminar reserva" title="Eliminar reserva" /></a>
					  </td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}
	
	function DetalleSala(){
		global $Utilitario;
		$idreserva=$_POST['idreserva'];
		$result=mysql_query("select s.idsala, s.idubicacion, s.nombre, s.piso, s.capacidad, s.caracteristicas, s.imagen, 
									CONCAT_WS(' ',u.nombre,'-','Piso',s.piso,'-',s.nombre) as UbicacionSalas,r.codigo,
									r.fecha_reserva, r.horario_inicio, r.horario_final,DATEDIFF(r.fecha_reserva,NOW()) diasFaltan,r.idreserva,r.estado
							  from salas s, ubicaciones u, reservas r
							 where u.idubicacion=s.idubicacion 
							   and r.idsala= s.idsala
								and r.idreserva=".$idreserva);
		$Campos=mysql_fetch_array($result);
		$Imagen='';
		if(!empty($Campos['imagen'])){
			$Imagen='<img src="uploads/salas/'.$Campos['imagen'].'" height="243" alt="imagen salas" />';
		}
		//Complementos
		$resultComple=mysql_query("select c.nombre,leyenda from complementos c, salas_complementos sc, reservas r
									where sc.idcomplemento=c.idcomplemento 
									and r.idsala=sc.idsala
									and r.idreserva=".$idreserva);
		$Titulo='';
		$TextoRetorno='Ninguno';
		while($CamposComple=mysql_fetch_array($resultComple)){
			$Titulo.=', '.htmlentities($CamposComple['nombre']);
		}
		if(!empty($Titulo)){
			$TextoRetorno=substr($Titulo,2);
		}
		
		/*$TextoEstado='ATENDIDO';
		if($Campos["diasFaltan"]>=0){
			$TextoEstado='RESERVADO';			
		} */
		
		
		if($Campos["diasFaltan"]>=0){//Por atender
			$TextoEstado='RESERVADA';
			if($Campos["estado"]=='E'){
				$TextoEstado='ELIMINADO';
			}
		}else{//Atendido
			$TextoEstado='ATENDIDO';
			if($Campos["estado"]=='E'){
				$TextoEstado='ELIMINADO';
			}
		}
			
		
		$Html='<table width="395" border="0" cellpadding="2" cellspacing="1" bgcolor="#E7E7E7">';
		$Html.='<tr>';
		$Html.='  <td height="37" colspan="4" valign="middle" bgcolor="#FFFFFF" >';
		$Html.='  <span class="textopequeTITULO"><strong>'.htmlentities($Campos['UbicacionSalas']).'</strong></span><br />';
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
		$Html.='  <td width="102" valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">N&uacute;mero Orden</td>';
		$Html.='  <td width="7" align="center" valign="middle" class="textopeque" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td width="125" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$Campos['codigo'].'</strong></td>';
		$Html.='  <td width="170" rowspan="4" align="center" valign="middle" bgcolor="#EEEEEE" class="titulo">'.$TextoEstado.'</td>';
		$Html.='</tr>';	
		
		$Html.='<tr>';
		$Html.='  <td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Fecha</td>';
		$Html.='  <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$Utilitario->cambiaf_a_normal($Campos['fecha_reserva']).'</strong></td>';
		$Html.='</tr>';
		
		$Html.='<tr>';
		$Html.='  <td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Hora Inicio</td>';
		$Html.='  <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$Campos['horario_inicio'].'</strong></td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Hora Final</td>';
		$Html.='  <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$Campos['horario_final'].'</strong></td>';
		$Html.='</tr>';
		$Html.='</table>';
		echo $Html;
	}
		
	function DetalleSalaRestaurar(){
		global $Utilitario;
		$idreserva=$_POST['idreserva'];
		$result=mysql_query("select s.idsala, s.idubicacion, s.nombre, s.piso, s.capacidad, s.caracteristicas, s.imagen, 
									CONCAT_WS(' ',u.nombre,'-','Piso',s.piso,'-',s.nombre) as UbicacionSalas,r.codigo,
									r.fecha_reserva, r.horario_inicio, r.horario_final,DATEDIFF(r.fecha_reserva,NOW()) diasFaltan,r.idreserva,r.estado
							  from salas s, ubicaciones u, reservas r
							 where u.idubicacion=s.idubicacion 
							   and r.idsala= s.idsala
								and r.idreserva=".$idreserva);
		$Campos=mysql_fetch_array($result);
		$Imagen='';
		if(!empty($Campos['imagen'])){
			$Imagen='<img src="uploads/salas/'.$Campos['imagen'].'" height="243" alt="imagen salas" />';
		}
		//Complementos
		$resultComple=mysql_query("select c.nombre,leyenda from complementos c, salas_complementos sc, reservas r
									where sc.idcomplemento=c.idcomplemento 
									and r.idsala=sc.idsala
									and r.idreserva=".$idreserva);
		$Titulo='';
		$TextoRetorno='Ninguno';
		while($CamposComple=mysql_fetch_array($resultComple)){
			$Titulo.=', '.htmlentities($CamposComple['nombre']);
		}
		if(!empty($Titulo)){
			$TextoRetorno=substr($Titulo,2);
		}
		$TextoEstado='ELIMINADO';			
		
		$Html='<table width="395" border="0" cellpadding="2" cellspacing="1" bgcolor="#E7E7E7">';
		$Html.='<tr>';
		$Html.='  <td height="37" colspan="4" valign="middle" bgcolor="#FFFFFF" >';
		$Html.='  <span class="textopequeTITULO"><strong>'.htmlentities($Campos['UbicacionSalas']).'</strong></span><br />';
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
		$Html.='  <td width="102" valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">N&uacute;mero Orden</td>';
		$Html.='  <td width="7" align="center" valign="middle" class="textopeque" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td width="125" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$Campos['codigo'].'</strong></td>';
		$Html.='  <td width="170" rowspan="4" align="center" valign="middle" bgcolor="#EEEEEE" class="titulo">'.$TextoEstado.'</td>';
		$Html.='</tr>';	
		
		$Html.='<tr>';
		$Html.='  <td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Fecha</td>';
		$Html.='  <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$Utilitario->cambiaf_a_normal($Campos['fecha_reserva']).'</strong></td>';
		$Html.='</tr>';
		
		$Html.='<tr>';
		$Html.='  <td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Hora Inicio</td>';
		$Html.='  <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$Campos['horario_inicio'].'</strong></td>';
		$Html.='</tr>';
		$Html.='<tr>';
		$Html.='  <td valign="middle" class="textopequeTITULO" style="padding-left:8px;" bgcolor="#FFFFFF">Hora Final</td>';
		$Html.='  <td align="center" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF">:</td>';
		$Html.='  <td valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><strong>'.$Campos['horario_final'].'</strong></td>';
		$Html.='</tr>';
		$Html.='</table>';
		
		
		$Html.='<table width="395" border="0" cellpadding="2" cellspacing="1" bgcolor="#E7E7E7">';
		$Html.='<tr>';
		$Html.='  <td height="25" colspan="4" valign="middle" class="textopequeTITULO" bgcolor="#FFFFFF"><span style="color:#BD5F11">Por favor confirmar si desea restaurar la fecha y hora de la sala eliminada, para continuar hacer clic en el botón <strong>Restaurar reserva.</strong></span></td>';		
		$Html.='</table>';
		echo $Html;
	}
	
	function Restaurar(){
		mysql_query(" UPDATE reservas set estado='R' WHERE idreserva = ".$_POST['idreserva']);	
		$this->Listado($_POST['pagina'],$_POST['Desde'],$_POST['Hasta'],$_POST['Ubicacion'],$_POST['Piso'],$_POST['Sala'],$_POST['Usuario']);
	}
	
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
			$Html.='<option value="'.$Campo['idsala'].'">'.$Utilitario->convertir_utf8($Campo['nombre']).'</option>';	
		}
		$Html.='</select>';
		echo $Html;
	}
	
	function AutoComplete(){
		if( isset( $_REQUEST['query'] ) && $_REQUEST['query'] != "" ){
			$q = strtoupper(mysql_real_escape_string( $_REQUEST['query'] ));
			$Result=mysql_query("SELECT nombres_apellidos nombres FROM seg_usuarios where locate('".$q."',nombres_apellidos) > 0 order by locate('".$q."',nombres_apellidos) limit 30");
			if ($Result){
				echo '<ul>'."\n";
				while($Campos = mysql_fetch_array( $Result ) )
				{
				$nombres = $Campos['nombres'];
				$nombres = preg_replace('/(' . $q . ')/i', '<span style="font-weight:bold;">'.$q.'</span>', $nombres);
				echo "\t".'<li id="autocomplete_'.$Campos['id'].'" rel="'.$Campos['nombres'].'">'. utf8_encode( $nombres ) .'</li>'."\n";
				}
				echo '</ul>';
			}
		}
		
	}
}




?>
