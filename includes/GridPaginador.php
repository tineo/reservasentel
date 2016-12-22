<?php

class GridPaginador{
 	var $BtnNomEliminar='';
	var $BtnFuncEliminar='';
	var $BtnValue='';
	var $BtnDisabled='';
	var $funcion='';
	var $columnas=0;
	var $RegistrosAMostrar=15;//Cantidad de Registro mostrar
	var $RegistrosAEmpezar=0;
	var $PagActual=1;
	var $MostrarHasta=0;
	var $NroRegistros=0;
	var $SelectQuery='';
	var $PaginadoHtmlExtra='';
    var $Tabla='<table class="grid-Tabla" cellpadding="0" cellspacing="1">';
	var $TablaNoting='<table class="grid-Tabla-Nothing">';
	
	function BotonEliminar($nombre,$funcion,$value='Eliminar',$disabled=" disabled='disabled' "){
    	$this->BtnNomEliminar = $nombre;
		$this->BtnFuncEliminar = $funcion;
		$this->BtnValue = $value;
		$this->BtnDisabled = $disabled;
    }
	
	function HtmlExtra($html){
    	$this->PaginadoHtmlExtra = $html;
    }
	
	function SetFunction($function){
    	$this->funcion = $function;
    }
	
	function SetColunas($colums){
    	$this->columnas = $colums;
    }
	
	function SetTabla($Atributos){
    	$this->Tabla = $Atributos;
    }
	
	function SetTablaNothing($Atributos){
    	$this->TablaNoting = $Atributos;
    }
	
	function SetRegistrosAMostrar($numeroRegistro){
    	$this->RegistrosAMostrar = $numeroRegistro;
    }
	
	function SetRegistrosAEmpezar($AEmpezar){
    	$this->RegistrosAEmpezar=$AEmpezar;		
		if($AEmpezar == 0){//estos valores los recibo por GET			
			$this->RegistrosAEmpezar=0;
			$this->PagActual=1;
		}else{//caso contrario los iniciamos
			$this->RegistrosAEmpezar=($AEmpezar-1) * $this->RegistrosAMostrar;
			$this->PagActual=$AEmpezar;			
		}
		
		$this->MostrarHasta = $this->RegistrosAEmpezar;
    }
		
	function GetResultados($query){
        $this->SelectQuery=$query;
		$result = mysql_query($query." LIMIT ". $this->RegistrosAEmpezar.",". $this->RegistrosAMostrar);
		$rows=mysql_num_rows($result);
        $this->MostrarHasta=$this->MostrarHasta + $rows;
		$resultTotal = mysql_query($query);
		$this->NroRegistros=mysql_num_rows($resultTotal);        
        //Si no hay registro a mostrar y si el pagina actual es mayor a 1
        if($rows==0 && $this->PagActual>1){
            $this->SetRegistrosAEmpezar($this->PagActual-1);
            $this->SelectQuery=$query;
            $result = mysql_query($query." LIMIT ". $this->RegistrosAEmpezar.",". $this->RegistrosAMostrar);
            $this->MostrarHasta=$this->MostrarHasta + mysql_num_rows($result);
            $resultTotal = mysql_query($query);
            $this->NroRegistros=mysql_num_rows($resultTotal);
        }
        return $result;
    }
		
	function Nothing(){
		$NothisHTML='<tr>';
		$NothisHTML.='<td style="text-align:center;padding:20px 0 20px 0;" bgcolor="#FFFFFF" class="texto"><img alt="" src="images/alerta.jpg" /><br />';
		$NothisHTML.='No se encontraron registros.</td>';
		$NothisHTML.='</tr>	';
		$NothisHTML.='</table>';
		return $this->TablaNoting.$NothisHTML;
	}
	
	function GetRegistros($Contenido,$Idioma='es'){
		//Si no se encontraron registros mostrar mensaje
		if ($this->NroRegistros == 0)
			return $this->Nothing();
		
		//Mostar el contenido
		$retorno = $this->Tabla . $Contenido;
		//Si el resultado es menor o igual al n�mero de paginaci�n; no mostrar el pie del desplazamiento		 
		if ($this->NroRegistros <= $this->RegistrosAMostrar){
			//return $retorno."</table>"; $this->PaginadoHtmlExtra
			if(!empty($this->BtnNomEliminar) || !empty($this->PaginadoHtmlExtra)){
			$retorno.="<tr>";
			$retorno.="<td colspan='".$this->columnas."'>";
			$retorno.="<table class='Paginado' border='0'>";
			$retorno.="<tr>";				
				if(!empty($this->BtnNomEliminar)){
					$retorno.="<td width='70px'>";
				    $retorno.="<input type='button' id='".$this->BtnNomEliminar."' value='".$this->BtnValue."' onclick=\"".$this->BtnFuncEliminar."();\" ".$this->BtnDisabled."  />";
				   $retorno.=" </td>";
				}
				if(!empty($this->PaginadoHtmlExtra)){
					$retorno.="<td align='left' valign='middle' height='30px' >".$this->PaginadoHtmlExtra."</td>";
				}
				
						  
			   $retorno.="<td colspan='". ($this->columnas - 1) ."' align='right'>". $this->RegistrosAEmpezar = $this->RegistrosAEmpezar + 1 ." - ".$this->MostrarHasta." de ".$this->NroRegistros." registros</td>";
			   $retorno.="</tr>";
			   $retorno.="</table>";
			   $retorno.="</td>";
			   $retorno.="</tr></table>";
			
			
			return $retorno;
			}else{
			  return $retorno."</table>";
			}
		}
		
    	//******--------determinar las p�ginas---------******//		
		$PagAnt=$this->PagActual-1;
		$PagSig=$this->PagActual+1;
		$PagUlt=$this->NroRegistros/$this->RegistrosAMostrar;
		
		//verificamos residuo para ver si llevar� decimales
		$Res=$this->NroRegistros%$this->RegistrosAMostrar;
		// si hay residuo usamos funcion floor para que me
		// devuelva la parte entera, SIN REDONDEAR, y le sumamos
		// una unidad para obtener la ultima pagina
		if($Res>0) $PagUlt=floor($PagUlt)+1;
		
		//Traducci�n del paginado 
		$Primero;$Anterior;$Siguiente;$Ultimo;$De;$Pagina;$Registros;
		if($Idioma=='es'){
			$Primero='Primero';
			$Anterior='Anterior';
			$Siguiente='Siguiente';
			$Ultimo='&Uacute;ltimo';
			$De='de';
			$Pagina='P&aacute;gina';
			$Registros='registros';
		}else if($Idioma=='en'){
			$Primero='First';
			$Anterior='Previous';
			$Siguiente='Next';
			$Ultimo='Last';
			$De='of';
			$Pagina='Page';
			$Registros='entries';
		}		
		
		//desplazamiento para la paginacion
		$retorno .= "<tr>";
		$retorno.="  <td colspan='".($this->columnas +1 )."' >";
		$retorno.="	  <table class='Paginado' border='0'>";		
		$retorno.="   <tr>";
		$retorno.="     <td>";
		$retorno.="      <table border='0'>";
		$retorno.="     <tr>";
		$retorno.="	   	<td width='40%' >";
							if($this->BtnNomEliminar!=""){
						        $retorno .= "<input type='button' id='$this->BtnNomEliminar' value='".$this->BtnValue."' class='grid-Paginado-TextoRow' onclick=\"$this->BtnFuncEliminar()\" ".$this->BtnDisabled."  />";
							}
		       $retorno .= "</td>";
					if($this->PagActual==1){
					 $retorno .= "<td>".$Primero."&nbsp;</td>";
					}else{
					 $retorno .= "<td><a onclick=\"$this->funcion(1)\" href='javascript:void(0)'>".$Primero." </a>&nbsp;</td>";
					}
					if($this->PagActual>1){
					 $retorno .= "<td><a onclick=\"$this->funcion($PagAnt)\" href='javascript:void(0)'>".$Anterior." </a>&nbsp;&nbsp;</td>";		 
					}else{
					 $retorno .= "<td>".$Anterior."&nbsp;&nbsp;</td>";
					}
					$retorno .= "<td width='23%' valign='top'>".$Pagina." ";
					$retorno .= "<input type='text' style='width:32px; text-align:center' value='".$this->PagActual."' OldValue='".$this->PagActual."' MaxValue='$PagUlt' onkeypress='PaginarValidar(event,this,$this->funcion)' maxlength='4' onkeyup='ValidEntero(this)' onblur='ValidEntero(this)' /> ".$De." ".$PagUlt." </td>";
					if($this->PagActual<$PagUlt){
					 $retorno .= "<td>&nbsp;&nbsp;<a onclick=\"$this->funcion($PagSig)\" href='javascript:;'>".$Siguiente."</a>&nbsp;</td>";
					}else{
					 $retorno .= "<td>&nbsp;&nbsp;".$Siguiente."&nbsp;</td>";		 
					}
					if($this->PagActual==$PagUlt){
					 $retorno .= "<td>".$Ultimo."</td>";
					}else{
					 $retorno .= "<td ><a onclick=\"$this->funcion($PagUlt)\" href='javascript:void(0)'>".$Ultimo."</a></td>";
					}		
		$retorno .=  "      </tr>";
		$retorno .=  "		         </table>	";	
		$retorno .=  "				   </td>";
		$retorno .=  "				  <td width='150px' align='right'>". $this->RegistrosAEmpezar = $this->RegistrosAEmpezar + 1 ." - ".$this->MostrarHasta." ".$De." ".$this->NroRegistros." ".$Registros."</td>";
		$retorno .=  "				 </tr>	";	
		$retorno .=  "				</table>";
		$retorno .=  "			   </td>";
		$retorno .=  "			  </tr>";		
		$retorno .=  "			 </table>";
		return $retorno;
    }
 	
 
 }


?>
