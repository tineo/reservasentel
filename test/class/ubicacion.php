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
$Ubicacion = new Ubicacion;

if($_POST["command"]=='Guardar'){
	$Ubicacion->Guardar();
}else if($_POST["command"]=='Editar'){
	$Ubicacion->Editar();
}else if($_POST["command"]=='Eliminar'){
	$Ubicacion->Eliminar();
}else if($_POST["command"]=='Buscar'){
	$Ubicacion->GetList($_POST['pagina'],$_POST['campo'],$_POST['orden']);
}

class Ubicacion{	

	function Guardar(){
		global $Utilitario;
		$idubicacion = $_POST["idubicacion"];		
		if($idubicacion==0){
			$sql=sprintf(" INSERT INTO ubicaciones(nombre,horario_inicio,horario_final,caracteristicas) values('%s','%s','%s','%s') ",
					 $Utilitario->mixed_to_latin1($_POST['nombre']),$_POST['horario_inicio'],$_POST['horario_final'],$Utilitario->mixed_to_latin1($_POST['caracteristicas']));
		}else{
			$sql=sprintf(" UPDATE ubicaciones set nombre='%s',horario_inicio='%s',horario_final='%s',caracteristicas='%s' WHERE idubicacion=%s ",
				 	$Utilitario->mixed_to_latin1($_POST['nombre']),$_POST['horario_inicio'],$_POST['horario_final'],$Utilitario->mixed_to_latin1($_POST['caracteristicas']),$idubicacion);
			
		}
		mysql_query($sql);
		$this->GetList($_POST["pagina"],$_POST['campo'],$_POST['orden']);			
	}
	
	function Editar(){
		global $Utilitario;
		$sql = "SELECT nombre,horario_inicio,horario_final,caracteristicas FROM ubicaciones WHERE idubicacion=".$_POST["idubicacion"];
		$result=mysql_query($sql);
		$Field=mysql_fetch_array($result);		
	   echo '{  "nombre": "' . $Utilitario->convertir_utf8($Field["nombre"]) . '",
				"horario_inicio": "' . $Field["horario_inicio"] . '",
				"horario_final": "' . $Field["horario_final"] . '",
				"caracteristicas": "' . $Utilitario->convertir_utf8($Field["caracteristicas"]) . '"
			 }';
	}	
	
	function GetList($pagina,$campo,$orden){
		global $Utilitario;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("Paginar");
		$ControlGrid->SetRegistrosAMostrar(20);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(4);
		//$ControlGrid->BotonEliminar("btn_Eliminar","Eliminar");//Nombre boton,nombre funcion		
		$sql=" SELECT u.idubicacion,u.nombre,u.horario_inicio,u.horario_final,u.caracteristicas, 
					   (select count(*) from salas s where s.idubicacion=u.idubicacion) TotalSalas
				FROM ubicaciones u ";
		
		//Ordenamiento
		$OrdenCodigo='asc';$OrdenNombre='asc';		
		$ImagenCodigo='';$ImagenNombre='';
		$IconoOrdenar=$orden=='asc'?'<img src="images/ico-arriba.gif" width="10" height="3" border="0">':'<img border="0" src="images/ico-abajo.gif" width="10" height="3">';
		$Desplazamiento=$orden=='asc'?'desc':'asc';
		if(!empty($campo)){
			$sql.=" order by $campo $orden ";
			if($campo=='nombre'){				
				$OrdenCodigo=$Desplazamiento;
				$ImagenCodigo=$IconoOrdenar;
			}
		}
		//Fin Ordenamiento
		
						
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows =  '<tr>
					<th width="200px" height="27"><a href="javascript:;" onclick="OrdenarGrid(\'nombre\',\''.$OrdenCodigo.'\')" >Nombre</a>'.$ImagenCodigo.'</th>
					<th width="115px">Horario Atenci&oacute;n</th>	
					<th width="451px">Caracter&iacute;sticas</th>
					<th width="144px" colspan="2" style="text-align:center;">Acci&oacute;n</th>
				  </tr>';		
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Checked=$ArrFila["acceso"]==1?' checked="checked" ':'';
			$DisabledEliminar='';
			if($ArrFila["TotalSalas"]>0){
				$DisabledEliminar=' disabled="disabled" ';
			}
			$Rows .= '<tr class="Registros" onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["nombre"]).'</td>';
			$Rows .= ' <td>'.$ArrFila["horario_inicio"].' - '.$ArrFila["horario_final"].'</td>';
			$Rows .= ' <td>'.htmlentities($ArrFila["caracteristicas"]).'</td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value=" Editar " onclick="Editar('.$ArrFila["idubicacion"].');" /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value=" Eliminar " onclick="Eliminar('.$ArrFila["idubicacion"].');" '.$DisabledEliminar.' /></td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}	
	
	function Eliminar(){
		mysql_query(" DELETE FROM ubicaciones WHERE idubicacion = ".$_POST['id']);			
		$this->GetList($_POST['pagina'],$_POST['campo'],$_POST['orden']);
	}	
	
	
}




?>
