<?
if(!empty($_POST["command"]) || !empty($_REQUEST['identifier'])){
	include('../config.php');
	include("../includes/GridPaginador.php");
	include('../includes/util.php');
	include("../includes/EnvioCorreo.php");
	session_start();//para el idespecial
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
	$User->GetList($_POST['pagina'],$_POST['BuscarUsuario']);
}else if($_POST["command"]=='Acceso'){
	$User->Acceso();
}else if($_REQUEST['identifier']=='Usuarios'){
	$User->AutoComplete();
}else if($_REQUEST['command']=='PopUpUsuarios'){
	$User->PopUpUsuarios($_POST['pagina'],$_POST['TextoBuscar']);
}

class Usuarios{		

	function Guardar(){
		global $Utilitario;
		$idespecial = $_POST["idespecial"];		
		if($idespecial==0){
			$sql=sprintf(" INSERT INTO reservas_especiales(idusuario,fec_inicio,fec_final,cantidad,idadmin,reservados) values(%s,'%s','%s',%s,%s,0) ",
					 $_POST['IdUsuario'],$Utilitario->cambiaf_a_mysql($_POST['FecInicio']),$Utilitario->cambiaf_a_mysql($_POST['FecFinal']),$_POST['Cantidad'],$_SESSION["idusuario"]);		
		}else{
			$sql=sprintf(" UPDATE reservas_especiales set idusuario=%s,fec_inicio='%s',fec_final='%s',cantidad=%s WHERE idespecial=%s ",
				 	$_POST['IdUsuario'],$Utilitario->cambiaf_a_mysql($_POST['FecInicio']),$Utilitario->cambiaf_a_mysql($_POST['FecFinal']),$_POST['Cantidad'],$idespecial);
		}
		mysql_query($sql);
		
		//Enviar emial al usuario
		if($idespecial==0){
			$Email = new EnvioEmail();
			$Result=mysql_query("SELECT u.idusuario,u.codigo,u.nombres_apellidos nombres,email 
								 FROM seg_usuarios u where idusuario=".$_POST['IdUsuario']);
			$Campo=mysql_fetch_array($Result);
			if(!empty($Campo["email"])){
				$Contenido="Estimado/a: ".$Campo["nombres"]."<br /><br /> 
				Te comunicamos que tiene ".$_POST['Cantidad']." reserva(s) para realizar sin ninguna restricción desde el  ".$Utilitario->cambiaf_a_mysql($_POST['FecInicio'])." hasta el ".$Utilitario->cambiaf_a_mysql($_POST['FecFinal'])." ";
				$Email->Enviar($Campo["email"],'Reserva Especial',$Contenido);
			}			
		}
		
					
		$this->GetList($_POST["pagina"],$_POST['BuscarUsuario']);				
	}
	
	function Editar(){
		global $Utilitario;
		$sql = "select u.idusuario, u.nombres_apellidos nombres, u.email,date_format(re.fec_inicio,'%d/%m/%Y') fecInicio, date_format(re.fec_final,'%d/%m/%Y') fecFinal, re.cantidad 
				from reservas_especiales re, seg_usuarios u
				where u.idusuario=re.idusuario 
				and idespecial=".$_POST["idespecial"];
		$result=mysql_query($sql);
		$Field=mysql_fetch_array($result);		
	   echo '{ "idusuario": "' . $Field["idusuario"] . '",
	  		   "nombres": "' . $Field["idusuario"].' - '.$Utilitario->convertir_utf8($Field["nombres"]) . '",
			   "fecInicio": "' . $Field["fecInicio"] . '",
			   "fecFinal": "' . $Field["fecFinal"] . '",
			   "cantidad": "' . $Field["cantidad"] . '"
			 }';
	}	
	
	function GetList($pagina,$BuscarUsuario){
		global $Utilitario;
		$ControlGrid= new GridPaginador();
		$ControlGrid->SetFunction("Paginar");
		$ControlGrid->SetRegistrosAMostrar(20);
		$ControlGrid->SetRegistrosAEmpezar($pagina);
		$ControlGrid->SetColunas(7);
		//$ControlGrid->BotonEliminar("btn_Eliminar","Eliminar");//Nombre boton,nombre funcion		
		$sql=" select idespecial,u.idusuario, u.codigo,u.nombres_apellidos nombres, u.email,date_format(re.fec_inicio,'%d/%m/%Y') fecInicio, date_format(re.fec_final,'%d/%m/%Y') fecFinal, re.cantidad,
				datediff(fec_final,curdate()) dias_falta 
				from reservas_especiales re, seg_usuarios u
				where u.idusuario=re.idusuario";
		if(!empty($BuscarUsuario)){
			$sql .=" and locate('".$Utilitario->mixed_to_latin1($BuscarUsuario)."', CONCAT_WS('   ',u.codigo,u.nombres_apellidos,u.email)) > 0 " ;
		}
		$sql .=" order by re.fec_inicio desc";
		
		$Resultado = $ControlGrid->GetResultados($sql);		
		$Rows =  '<tr>
					<th width="100px" height="27">C&oacute;digo</th>
					<th width="347px">Usuario</th>
					<th width="122px">Fecha Inicio</th>
					<th width="123px">Fecha Final</th>
					<th width="74px" style="text-align:center;">Cantidad</th>
					<th width="144px" colspan="2" style="text-align:center;">Acci&oacute;n</th>
				  </tr>';		
		while($ArrFila=mysql_fetch_array($Resultado)){
			$Disabled=$ArrFila["dias_falta"]>0?'':' disabled="disabled" ';
			$Rows .= '<tr class="Registros" onmouseover="RowColor=this.style.backgroundColor;style.backgroundColor=\''.ColorFilaOver.'\';" onmouseout="style.backgroundColor=RowColor;" >';
			$Rows .= ' <td>'.$ArrFila["codigo"].'</td>';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["nombres"],ENT_QUOTES,"iso-8859-1").'</td>';
			$Rows .= ' <td>'.$ArrFila["fecInicio"].'</td>';
			$Rows .= ' <td>'.$ArrFila["fecFinal"].'</td>';
			$Rows .= ' <td align="center">'.$ArrFila["cantidad"].'</td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value="Editar" onclick="Editar('.$ArrFila["idespecial"].');" '.$Disabled.' /></td>';
			$Rows .= ' <td align="center"><input name="button" type="button" class="FRM" value="Eliminar" onclick="Eliminar('.$ArrFila["idespecial"].');" '.$Disabled.' /></td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}
		
	function Eliminar(){		
		mysql_query(" DELETE FROM reservas_especiales WHERE idespecial = ".$_POST['id']);			
		$this->GetList($_POST['pagina'],$_POST['BuscarUsuario']);
	}
	
	function AutoComplete(){
		if( isset( $_REQUEST['query'] ) && $_REQUEST['query'] != "" ){
			$q = strtoupper(mysql_real_escape_string( $_REQUEST['query'] ));
			$Result=mysql_query("SELECT CONCAT_WS('   ',idusuario,nombres_apellidos,email) nombres FROM reservas_especiales where locate('".$q."',CONCAT_WS('',codigo,nombres_apellidos,email)) > 0 order by locate('".$q."',CONCAT_WS('',codigo,nombres_apellidos,email)) limit 30");
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
			$Rows .= ' <td><input type="checkbox" key="'.$ArrFila["idusuario"].'" data="'.htmlentities($ArrFila["CodigoNombres"],ENT_QUOTES,"iso-8859-1").'" onclick="AsignarUsuario(this)" /></td>';
			$Rows .= ' <td>'.$ArrFila["codigo"].'</td>';
			$Rows .= ' <td height="27">'.htmlentities($ArrFila["nombres"],ENT_QUOTES,"iso-8859-1").'</td>';
			$Rows .= ' <td>'.$ArrFila["email"].'</td>';
			$Rows .= '</tr>';
		}
		echo $ControlGrid->GetRegistros($Rows);	
	}
	
	
}




?>
