<?
if(!empty($_POST["command"]) || !empty($_REQUEST['identifier'])){
	include('../config.php');//Si utiliza ajax es requerido importar la configuracion
	include("../includes/GridPaginador.php");
	include('../includes/util.php');
	session_start();//para el idusuario
}else{
	include("includes/GridPaginador.php");
	include('includes/util.php');
}

 
$Utilitario = new Utilitario;
$User = new Usuarios;

if($_POST["command"]=='Guardar'){
	$User->Guardar();
}else if($_POST["command"]=='Editar'){
	$User->Editar();
}else if($_POST["command"]=='Eliminar'){
	$User->Eliminar();
}else if($_POST["command"]=='Buscar'){
	$User->GetList($_POST['pagina'],$_POST['campo'],$_POST['orden'],$_POST['BuscarUsuario']);
}else if($_POST["command"]=='Acceso'){
	$User->Acceso();
}else if($_REQUEST['identifier']=='Usuarios'){
	$User->AutoComplete();
}

class Usuarios{		

	function Guardar(){
		global $Utilitario;
		$idusuario = $_POST["idusuario"];		
		if($idusuario==0){
			//Validar si no existe el usuario
			$query = " SELECT count(*) existe FROM seg_usuarios WHERE codigo = '".$_POST['codigo']."' "; 	  
			$result=mysql_query($query);
			$Field=mysql_fetch_array($result);
			mysql_free_result($result);		
			$exite=$Field["existe"];
			if($exite==0){				
				$sql=sprintf(" INSERT INTO seg_usuarios(codigo,nombres_apellidos,email,telefonos,tipo,acceso) values('%s','%s','%s','%s','S',0) ",
					 $_POST['codigo'],$Utilitario->mixed_to_latin1($_POST['nombres']),$_POST['email'],$Utilitario->mixed_to_latin1($_POST['telefono']));
				mysql_query($sql);
				$idusuario=mysql_insert_id();	
				mysql_query(" INSERT INTO seg_menu_usuarios(idusuario,idmenu) values(".$idusuario.",6)");
				mysql_query(" INSERT INTO seg_menu_usuarios(idusuario,idmenu) values(".$idusuario.",7)");
				$this->GetList($_POST["pagina"],$_POST['campo'],$_POST['orden'],$_POST['BuscarUsuario']);
			}else{
					echo"Existe";
			}							    
		}else{
			if($_POST['CambioCodigo']=='SI'){
				$query = " SELECT count(*) existe FROM seg_usuarios WHERE codigo = '".$_POST['codigo']."' "; 	  
				$result=mysql_query($query);
				$Field=mysql_fetch_array($result);
				mysql_free_result($result);		
				$exite=$Field["existe"];
				if($exite>0){
					echo"Existe";
					die();
				}			
			}
			$sql=sprintf(" UPDATE seg_usuarios set codigo='%s',nombres_apellidos='%s',email='%s',telefonos='%s' WHERE idusuario=%s ",
				 	$_POST['codigo'],$Utilitario->mixed_to_latin1($_POST['nombres']),$_POST['email'],$Utilitario->mixed_to_latin1($_POST['telefono']),$idusuario);
			mysql_query($sql);			
			$this->GetList($_POST["pagina"],$_POST['campo'],$_POST['orden'],$_POST['BuscarUsuario']);
		}				
	}
	
	function Editar(){
		global $Utilitario;
		$sql = "SELECT codigo,nombres_apellidos nombres,email,telefonos FROM seg_usuarios WHERE idusuario=".$_POST["idusuario"];
		$result=mysql_query($sql);
		$Field=mysql_fetch_array($result);		
	   echo '{ "codigo": "' . $Field["codigo"] . '",
				"nombres": "' . $Utilitario->convertir_utf8($Field["nombres"]) . '",
				"email": "' . $Field["email"] . '",
				"telefono": "' . $Field["telefonos"] . '"
			 }';
	}	
	
	function GetList($pagina,$campo,$orden,$BuscarUsuario){
		global $Utilitario;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("Paginar");
		$ControlGrid->SetRegistrosAMostrar(20);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(6);
		//$ControlGrid->BotonEliminar("btn_Eliminar","Eliminar");//Nombre boton,nombre funcion		
		$sql=" SELECT u.idusuario,u.codigo,u.nombres_apellidos nombres,email,acceso 
					FROM seg_usuarios u where 0=0 ";
		if(!empty($BuscarUsuario)){
			$sql .=" and locate('".$Utilitario->mixed_to_latin1($BuscarUsuario)."', CONCAT_WS('   ',codigo,nombres_apellidos,email)) > 0 " ;
		}
		
		//Ordenamiento
		$OrdenCodigo='asc';$OrdenNombre='asc';		
		$ImagenCodigo='';$ImagenNombre='';
		$IconoOrdenar=$orden=='asc'?'<img src="images/ico-arriba.gif" width="10" height="3" border="0">':'<img border="0" src="images/ico-abajo.gif" width="10" height="3">';
		$Desplazamiento=$orden=='asc'?'desc':'asc';
		if(!empty($campo)){
			$sql.=" order by $campo $orden ";
			if($campo=='codigo'){				
				$OrdenCodigo=$Desplazamiento;
				$ImagenCodigo=$IconoOrdenar;
			}else if($campo=='nombres'){				
				$OrdenNombre=$Desplazamiento;
				$ImagenNombre=$IconoOrdenar;
			}
		}
						
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows =  '<tr>
					<th width="100px" height="27"><a href="javascript:;" onclick="OrdenarGrid(\'codigo\',\''.$OrdenCodigo.'\')" >C&oacute;digo</a>'.$ImagenCodigo.'</th>
					<th width="347px"><a href="javascript:;" onclick="OrdenarGrid(\'nombres\',\''.$OrdenNombre.'\')" >Usuario</a>'.$ImagenNombre.'</th>
					<th width="245px">Email</th>
					<th width="74px" style="text-align:center;">Acceso</th>
					<th width="144px" colspan="2" style="text-align:center;">Acci&oacute;n</th>
				  </tr>';		
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Disabled='';
			$Checked=$ArrFila["acceso"]==1?' checked="checked" ':'';
			if($ArrFila["idusuario"]==1){
				$Disabled=' disabled="disabled" ';
			}
			$Rows .= '<tr class="Registros" onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td>'.$ArrFila["codigo"].'</td>';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["nombres"],ENT_QUOTES,"iso-8859-1").'</td>';
			$Rows .= ' <td>'.$ArrFila["email"].'</td>';
			$Rows .= ' <td align="center"><input type="checkbox" onclick="Acceso(this,'.$ArrFila["idusuario"].');" '.$Checked.$Disabled.' /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value="Editar" onclick="Editar('.$ArrFila["idusuario"].');" /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value="Eliminar" onclick="Eliminar('.$ArrFila["idusuario"].');" '.$Disabled.' /></td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}
	
	function Acceso(){
		mysql_query("update seg_usuarios set acceso=".$_POST["acceso"]." where idusuario=".$_POST["idusuario"]);
		//$this->GetList($_POST['pagina'],$_POST['campo'],$_POST['orden']);
	}
	
	function Eliminar(){
		mysql_query(" DELETE FROM reservas_complementos WHERE idusuario = ".$_POST['id']);
		mysql_query(" DELETE FROM reservas WHERE idusuario = ".$_POST['id']);
		mysql_query(" DELETE FROM seg_menu_usuarios WHERE idusuario = ".$_POST['id']);
		mysql_query(" DELETE FROM seg_usuarios WHERE idusuario = ".$_POST['id']);			
		$this->GetList($_POST['pagina'],$_POST['campo'],$_POST['orden'],$_POST['BuscarUsuario']);
	}
	
	function AutoComplete(){
		if( isset( $_REQUEST['query'] ) && $_REQUEST['query'] != "" ){
			$q = strtoupper(mysql_real_escape_string( $_REQUEST['query'] ));
			$Result=mysql_query("SELECT CONCAT_WS('   ',codigo,nombres_apellidos,email) nombres FROM seg_usuarios where locate('".$q."',CONCAT_WS('',codigo,nombres_apellidos,email)) > 0 order by locate('".$q."',CONCAT_WS('',codigo,nombres_apellidos,email)) limit 30");
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
