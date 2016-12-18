<?
if(!empty($_POST["command"])){
	include('../config.php');//Si utiliza ajax es requerido importar la configuracion
	include("../includes/GridPaginadorChico.php");
	include('../includes/util.php');
	session_start();	
}else{
	include("includes/GridPaginadorChico.php");
	include('includes/util.php');
}

$idreservaCurrent;

 
$Utilitario = new Utilitario;
$Ubicacion = new MisReservas;

if($_POST["command"]=='DetalleSalaSeleccion'){
	$Ubicacion->DetalleSalaSeleccion();
}else if($_POST["command"]=='Eliminar'){
	$Ubicacion->Eliminar();
}else if($_POST["command"]=='Paginar'){
	$Ubicacion->DatosCargar($_POST['pagina'],'AJAX');
}

class MisReservas{

	function DatosCargar($pagina,$Modo){
		global $Utilitario, $idreservaCurrent;
		$Grilla=$this->Listado($pagina);
		$Detalle='';
		if(!empty($idreservaCurrent)){
			$Detalle=$this->DetalleSala($idreservaCurrent);
		}
		$Html='<table valign="top" border="0">
				 <tr>
				  <td valign="top">
					<table width="503" border="0" cellpadding="0" cellspacing="0">					  
					   <tr>           
							<td height="10px"></td>
					   </tr>
					  <tr>
						   <td id="td_Listado" valign="top">'.$Grilla.'</td>               
					   </tr>          
					</table>
					</td>
					<td>&nbsp;</td>
					<td align="left" valign="top" id="ConteDetalle">'.$Detalle.'</td> 
				 </tr>
				</table>';
		if($Modo=='Load'){			
			 echo $Html;
		}else{
			 echo $Html;
		}   
	}
	
	
	function DetalleSala($idreserva){
		global $Utilitario;
		$result=mysql_query("select s.idsala, s.idubicacion, s.nombre, s.piso, s.capacidad, s.caracteristicas, s.imagen, 
									CONCAT_WS(' ',u.nombre,'-','Piso',s.piso,'-',s.nombre) as UbicacionSalas,r.codigo,
									r.fecha_reserva, r.horario_inicio, r.horario_final,DATEDIFF(r.fecha_reserva,NOW()) diasFaltan,r.idreserva
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
		
		$TextoEstado='ATENDIDO';
		if($Campos["diasFaltan"]>=0){
			$TextoEstado='RESERVADA<br /><br /><a href="javascript:;" onclick="Eliminar('.$Campos["idreserva"].')" class="datos"><span class="datos">Eliminar Reserva</span>&nbsp;<img width="11" height="12" border="0" src="images/del.gif"></a>';			
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
		return $Html;
	}
	
	//and (DATEDIFF(r.fecha_reserva,NOW())>0)
	function Listado($pagina){ 
		global $Utilitario, $idreservaCurrent;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("Paginar");
		$ControlGrid->SetRegistrosAMostrar(15);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(5);
		$ControlGrid->SetTabla('<table class="grid-Tabla" cellpadding="0" cellspacing="1" style="503">');
		$ControlGrid->SetTablaNothing('<table class="grid-Tabla-Nothing" style="503">');
		$sql=" select r.idreserva,s.idsala, r.codigo, CONCAT_WS(' ',u.nombre,'-','Piso',s.piso,'-',s.nombre) as UbicacionSalas,r.fecha_reserva,
					  DATEDIFF(r.fecha_reserva,NOW()) diasFaltan		  
			  from salas s, reservas r, ubicaciones u
			  where u.idubicacion=s.idubicacion
				 and r.idsala= s.idsala
				 and r.idusuario=".$_SESSION["idusuario"]."
				 and r.estado='R'
				 and date_format(r.fecha_reserva,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d')
			 order by r.fecha_reserva desc ";			
				  
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows='<tr>';
		$Rows.=' <th width="50px" height="27">C&oacute;digo</th>';
		$Rows.=' <th width="260px">Ubicaci&oacute;n</th>';
		$Rows.=' <th width="90px">Fecha</th>';	
		$Rows.=' <th width="70px">Estado</th>';	
		$Rows.=' <th width="33px">Ver</th>';
	    $Rows.='</tr>';
		
		$Corre=0;
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Corre++;
			if($Corre==1) $idreservaCurrent=$ArrFila["idreserva"];
			$TextoEstado='ATENDIDO';
			if($ArrFila["diasFaltan"]>=0) $TextoEstado='RESERVADA';
			$Rows .= '<tr class="Registros" onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td height="27">'.$ArrFila["codigo"].'</td>';
			$Rows .= ' <td>'.$ArrFila["UbicacionSalas"].'</td>';
			$Rows .= ' <td>'.$Utilitario->cambiaf_a_normal($ArrFila["fecha_reserva"]).'</td>';
			$Rows .= ' <td>'.$TextoEstado.'</td>';
			$Rows .= ' <td align="center"><a href="javascript:;" onclick="DetalleSalaSeleccion('.$ArrFila["idreserva"].')"><img src="images/edit.gif" width="15" height="15" border="0" /></a></td>';
			$Rows .= '</tr>';
		}
		return $ControlGrid->GetRegistros($Rows);	
	}	
	
	function DetalleSalaSeleccion(){			
	    global $Utilitario;
		echo '{ "Detalle": "' . $Utilitario->EliminarTagsHtmlJSON($this->DetalleSala($_POST["idreservaCurrent"])) . '"}';
	}	
	
	function Eliminar(){
		//Se cambio de eliminar las reservas a mantener en un estado eliminado, en cualquier momento el administrador lo puede reservar, mientras no has resersas en el rango actual y la fecha sea mayor al actual
		//mysql_query(" DELETE FROM reservas WHERE idreserva = ".$_POST['id']);
		mysql_query(" UPDATE reservas set estado='E' WHERE idreserva = ".$_POST['id']);	
		$this->DatosCargar($_POST['pagina'],'AJAX');
	}	
	
	
}




?>
