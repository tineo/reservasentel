<?
if(!empty($_POST["command"])){
	include('../config.php');//Si utiliza ajax es requerido importar la configuracion
	include("../includes/GridPaginador.php");
	include('../includes/util.php');
}else{
	include("includes/GridPaginador.php");
	include('includes/util.php');
}

 
$Utilitario = new Utilitario;
$Sala = new Salas;

if($_POST["command"]=='Guardar'){
	$Sala->Guardar();
}else if($_POST["command"]=='Editar'){
	$Sala->Editar();
}else if($_POST["command"]=='Eliminar'){
	$Sala->Eliminar();
}else if($_POST["command"]=='Listar'){
	$Sala->GetList($_POST['pagina'],$_POST['campo'],$_POST['orden'],$_POST['UbicacionFiltro']);
}

class Salas{	

	function Guardar(){
		global $Utilitario;
		$idsala = $_POST["idsala"];
		$idubicacion=$_POST['idubicacion'];

		//Tineo
		$sala_especial=$_POST['sala_especial'];

		if($idsala==0){
			$sql=sprintf(" INSERT INTO salas(idubicacion,nombre,piso,capacidad,caracteristicas,imagen) values(%s,'%s',%s,%s,'%s','%s') ",
					 $idubicacion,$Utilitario->mixed_to_latin1($_POST['nombre']),$_POST['piso'],$_POST['capacidad'],$Utilitario->mixed_to_latin1($_POST['caracteristicas']),$_POST['imagen']);
		   
		    mysql_query($sql);
			$idsala=mysql_insert_id();
		}else{
			//Tineo
 			$proc = sprintf(" CALL toggle_salas_especiales(%s, %s)",$idsala,($sala_especial=="true")?"1":"0");
			mysql_query($proc);
			error_log($proc, 0);

			$sql=sprintf(" UPDATE salas set idubicacion=%s,nombre='%s',piso=%s,capacidad=%s,caracteristicas='%s',imagen='%s' WHERE idsala=%s ",
				 	$idubicacion,$Utilitario->mixed_to_latin1($_POST['nombre']),$_POST['piso'],$_POST['capacidad'],$Utilitario->mixed_to_latin1($_POST['caracteristicas']),$_POST['imagen'],$idsala);
			
			mysql_query($sql);
			mysql_query(" DELETE FROM salas_eventos WHERE idsala = ".$idsala);
			mysql_query(" DELETE FROM salas_complementos WHERE idsala = ".$idsala);




		}
		$Ex = explode(',', $_POST['ArrIdsEventos']);
		for($i = 0; $i <= count($Ex) - 1; $i++){
			mysql_query(" INSERT INTO salas_eventos(idubicacion,idsala,idevento) values(".$idubicacion.",".$idsala.",".$Ex[$i].")");
		}
		$Exx = explode(',', $_POST['ArrIdsComplementos']);
		for($ix = 0; $ix <= count($Exx) - 1; $ix++){
			mysql_query(" INSERT INTO salas_complementos(idubicacion,idsala,idcomplemento) values(".$idubicacion.",".$idsala.",".$Exx[$ix].")");
		}		
		$this->GetList($_POST["pagina"],$_POST['campo'],$_POST['orden'],$_POST['UbicacionFiltro']);			
	}
	
	function Editar(){
		global $Utilitario;
		$sql = "SELECT idubicacion,nombre,piso,capacidad,caracteristicas,imagen FROM salas WHERE idsala=".$_POST["idsala"];
		$result=mysql_query($sql);
		$Field=mysql_fetch_array($result);

		//Tineo
		$sqlesp = "SELECT COUNT(*) as cant FROM salas_especiales WHERE idsala=".$_POST["idsala"];
		$resultesp=mysql_query($sqlesp);
		$Fieldesp=mysql_fetch_array($resultesp);


	   echo '{  "idubicacion": "' . $Field["idubicacion"] . '",
	            "nombre": "' . $Utilitario->convertir_utf8($Field["nombre"]) . '",
				"piso": "' . $Field["piso"] . '",
				"capacidad": "' . $Field["capacidad"] . '",
				"caracteristicas": "' . $Utilitario->convertir_utf8($Field["caracteristicas"]) . '",
				"imagen": "' . $Field["imagen"] . '",
				"eventos": "' . $this->EventosSeleccionados($_POST["idsala"]) . '",
				"complementos": "' . $this->ComplementosSeleccionados($_POST["idsala"]) . '",
				"sala_especial": "' . ($Fieldesp['cant']>0?"true":"false") . '"
			 }';
	}
	
	function EventosSeleccionados($idsala){
		$sql="select idevento from salas_eventos where idsala=".$idsala;
		$Result=mysql_query($sql);
		$Menu='';	  
		while($ArrFila=mysql_fetch_array($Result)){
			$Menu .= ",".$ArrFila["idevento"];
						
		}
		return substr($Menu,1); 
	}
	
	function ComplementosSeleccionados($idsala){
		$sql="select idcomplemento from salas_complementos where idsala=".$idsala;
		$Result=mysql_query($sql);
		$Marcas='';	  
		while($ArrFila=mysql_fetch_array($Result)){
			$Marcas .= ",".$ArrFila["idcomplemento"];
						
		}
		return substr($Marcas,1);
	}
	
	function GetList($pagina,$campo,$orden,$UbicacionFiltro){
		global $Utilitario;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("Paginar");
		$ControlGrid->SetRegistrosAMostrar(20);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(6);
		//$ControlGrid->BotonEliminar("btn_Eliminar","Eliminar");//Nombre boton,nombre funcion		
		$sql=" SELECT s.idsala,s.idubicacion,s.nombre,s.piso,s.capacidad,s.caracteristicas,s.imagen, u.nombre nomubicacion,
					   (select count(*) from reservas r where r.idsala=s.idsala) TotalReservas 
				FROM salas s, ubicaciones u 
				where u.idubicacion=s.idubicacion ";
		if(!empty($UbicacionFiltro)){
			$sql.=" and u.idubicacion=".$UbicacionFiltro;
		}
		//Ordenamiento
		$OrdenNombre='asc';$OrdenPiso='asc';$OrdenCapacidad='asc';
		$ImagenNombre='';$ImagenPiso='';$ImagenCapacidad='';
		$IconoOrdenar=$orden=='asc'?'<img src="images/ico-arriba.gif" width="10" height="3" border="0">':'<img border="0" src="images/ico-abajo.gif" width="10" height="3">';
		$Desplazamiento=$orden=='asc'?'desc':'asc';
		if(!empty($campo)){
			$sql.=" order by nomubicacion asc, $campo $orden ";
			if($campo=='nombre'){				
				$OrdenNombre=$Desplazamiento;
				$ImagenNombre=$IconoOrdenar;
			}else if($campo=='piso'){				
				$OrdenPiso=$Desplazamiento;
				$ImagenPiso=$IconoOrdenar;
			}else if($campo=='capacidad'){				
				$OrdenCapacidad=$Desplazamiento;
				$ImagenCapacidad=$IconoOrdenar;
			}
		}
		//Fin Ordenamiento 910
		
						
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows =  '<tr>
					<th width="256px">Ubicaci&oacute;n</th>
					<th width="250px" height="27"><a href="javascript:;" onclick="OrdenarGrid(\'nombre\',\''.$OrdenNombre.'\')" >Nombre de la sala</a>'.$ImagenNombre.'</th>
					<th width="105px"><a href="javascript:;" onclick="OrdenarGrid(\'piso\',\''.$OrdenPiso.'\')" >Piso</a>'.$ImagenPiso.'</th>
					<th width="105px"><a href="javascript:;" onclick="OrdenarGrid(\'capacidad\',\''.$OrdenCapacidad.'\')" >Capacidad</a>'.$ImagenCapacidad.'</th>
					<th width="164px" colspan="2" style="text-align:center;">Acci&oacute;n</th>
				  </tr>';		
		while($ArrFila=mysql_fetch_array($Resultado)){
			//$Checked=$ArrFila["acceso"]==1?' checked="checked" ':'';
			$DisabledEliminar='';
			if($ArrFila["TotalReservas"]>0){
				$DisabledEliminar=' disabled="disabled" ';
			}
			$Rows .= '<tr class="Registros" onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["nomubicacion"]).'</td>';
			$Rows .= ' <td >'.htmlentities($ArrFila["nombre"]).'</td>';
			$Rows .= ' <td>'.$ArrFila["piso"].'</td>';
			$Rows .= ' <td>'.$ArrFila["capacidad"].'</td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value=" Editar " onclick="Editar('.$ArrFila["idsala"].');" /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value=" Eliminar " onclick="Eliminar('.$ArrFila["idsala"].');" '.$DisabledEliminar.' /></td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}	
	
	function Eliminar(){
		mysql_query(" DELETE FROM salas_eventos WHERE idsala = ".$_POST['id']);
		mysql_query(" DELETE FROM salas_complementos WHERE idsala = ".$_POST['id']);
		mysql_query(" DELETE FROM salas WHERE idsala = ".$_POST['id']);
		$this->GetList($_POST["pagina"],$_POST['campo'],$_POST['orden'],$_POST['UbicacionFiltro']);	
	}	
	
	
}




?>
