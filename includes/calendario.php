<?
class Calendario{
	
	function Mostrar($dia,$mes,$ano,$Feriados){
		$mes_hoy=date("m");
		$ano_hoy=date("Y");
		if (($mes_hoy <> $mes) || ($ano_hoy <> $ano)){
			$hoy=0;
		}else{
			$hoy=date("d");
		}
		//Fecha para comparar si la fecha es anterior al actual
		$Fecha_Hoy=date("Y/m/d");				
		//tomo el nombre del mes que hay que imprimir
		$nombre_mes = $this->dame_nombre_mes($mes);
		
		//construyo la cabecera de la tabla
		echo '<table width="200" cellspacing="3" cellpadding="2" border="0"><tr><td colspan="7" align="center" class="tit">';
		echo '<table width="100%" cellspacing="2" cellpadding="2" border="0"><tr><td style="font-size:10pt;font-weight:bold;color:white">';
		//calculo el mes y ano del mes anterior
		$mes_anterior = $mes - 1;
		$ano_anterior = $ano;
		if ($mes_anterior==0){
			$ano_anterior--;
			$mes_anterior=12;
		}
		//echo "<a style=color:white;text-decoration:none href=index.php?dia=1&nuevo_mes=$mes_anterior&nuevo_ano=$ano_anterior>&lt;&lt;</a></td>";
		echo '<a style="color:white;text-decoration:none" href="javascript:;" onclick="CargarCalendario(1,'.$mes_anterior.','.$ano_anterior.')">&lt;&lt;</a></td>';
		echo '<td align="center" class="tit">'.$nombre_mes.' '.$ano.'</td>';
		echo '<td align="right" style="font-size:10pt;font-weight:bold;color:white">';
		//calculo el mes y ano del mes siguiente
		$mes_siguiente = $mes + 1;
		$ano_siguiente = $ano;
		if ($mes_siguiente==13){
			$ano_siguiente++;
			$mes_siguiente=1;
		}
		//echo "<a style=color:white;text-decoration:none href=index.php?dia=1&nuevo_mes=$mes_siguiente&nuevo_ano=$ano_siguiente>&gt;&gt;</a></td></tr></table></td></tr>";
		echo '<a style="color:white;text-decoration:none" href="javascript:;" onclick="CargarCalendario(1,'.$mes_siguiente.','.$ano_siguiente.')">&gt;&gt;</a></td></tr></table></td></tr>';
		echo '	<tr>
					<td width="14%" align="center" class="altn">Lu</td>
					<td width="14%" align="center" class="altn">Ma</td>
					<td width="14%" align="center" class="altn">Mi</td>
					<td width="14%" align="center" class="altn">Ju</td>
					<td width="14%" align="center" class="altn">Vi</td>
					<td width="14%" align="center" class="altn">S&aacute;</td>
					<td width="14%" align="center" class="altn">Do</td>
				</tr>';
		
		//Variable para llevar la cuenta del dia actual
		$dia_actual = 1;
		
		//calculo el numero del dia de la semana del primer dia
		$numero_dia = $this->calcula_numero_dia_semana(1,$mes,$ano);
		//echo "Numero del dia de demana del primer: $numero_dia <br>";
		
		//calculo el último dia del mes
		$ultimo_dia = $this->ultimoDia($mes,$ano);
		
		
		
	    //escribo la primera fila de la semana
		echo '<tr>';
		for ($i=0;$i<7;$i++){
			//Setear las fechas con ceros
			$newMes=$mes;
			$newDia=$dia_actual;
			if(strlen($newMes)==1) $newMes='0'.$mes;
			if(strlen($newDia)==1) $newDia='0'.$dia_actual;		
			$newFechaFormat=$newDia.'/'.$newMes.'/'.$ano;
			$LinkFechaPasado='0';
			if(strtotime($ano.'/'.$newMes.'/'.$newDia)<strtotime($Fecha_Hoy)) $LinkFechaPasado='1';
			
			$Fecha = array_search($dia_actual,$Feriados);
			if ($i < $numero_dia){
				//si el dia de la semana i es menor que el numero del primer dia de la semana no pongo nada en la celda
				echo '<td></td>';
			} else {
			if (($i == 5) || ($i == 6)){
					if ($dia_actual == $hoy){
						if($Fecha>0){
							echo '<td align="center" class="fertivo"><a class="fertivoLink" href="javascript:;" onclick="DiaFestivo(\''.$Fecha.'\',\'L\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}else{//Sábados y Domingos
							//echo '<td align="center" class="laboral"><a class="laboralLink" href="javascript:;" onclick="DiaFestivo(\''.$newFechaFormat.'\',\'F\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
							echo '<td class="feriado"><span>'.$dia_actual.'</span></td>';
						}
					}
					else{
						echo '<td class="feriado"><span>'.$dia_actual.'</span></td>';
					}
			}else{			
					if ($dia_actual == $hoy){
						if($Fecha>0){
							echo '<td align="center" class="fertivo"><a class="fertivoLink" href="javascript:;" onclick="DiaFestivo(\''.$Fecha.'\',\'L\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}else{
							echo '<td align="center" class="hoy"><a class="hoyLink" href="javascript:;" onclick="DiaFestivo(\''.$newFechaFormat.'\',\'F\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}
					}else{					
						if($Fecha>0){
							echo '<td align="center" class="fertivo"><a class="fertivoLink" href="javascript:;" onclick="DiaFestivo(\''.$Fecha.'\',\'L\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}else{
							echo '<td align="center" class="laboral"><a class="laboralLink" href="javascript:;" onclick="DiaFestivo(\''.$newFechaFormat.'\',\'F\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}
					}
			}
				$dia_actual++;
			}
		}
		echo '</tr>';
		
		//recorro todos los demás días hasta el final del mes
		$numero_dia = 0;
		while ($dia_actual <= $ultimo_dia){
			//Setear las fechas con ceros
			$newMes=$mes;
			$newDia=$dia_actual;
			if(strlen($newMes)==1) $newMes='0'.$mes;
			if(strlen($newDia)==1) $newDia='0'.$dia_actual;		
			$newFechaFormat=$newDia.'/'.$newMes.'/'.$ano;
			$LinkFechaPasado='0';
			if(strtotime($ano.'/'.$newMes.'/'.$newDia)<strtotime($Fecha_Hoy)) $LinkFechaPasado='1';
			
			//si estamos a principio de la semana escribo el <TR>
			$Fecha = array_search($dia_actual,$Feriados);
			if ($numero_dia == 0)
				echo "<tr>";
			//si es el uñtimo de la semana, me pongo al principio de la semana y escribo el </tr>
	
				if (($numero_dia == 5) || ($numero_dia == 6)){
					if ($dia_actual == $hoy){
						if($Fecha>0){
							echo '<td align="center" class="fertivo"><a class="fertivoLink" href="javascript:;" onclick="DiaFestivo(\''.$Fecha.'\',\'L\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}else{//Sábados y Domingos
							//echo '<td align="center" class="laboral"><a class="laboralLink" href="javascript:;" onclick="DiaFestivo(\''.$newFechaFormat.'\',\'F\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
							echo '<td class="feriado"><span>'.$dia_actual.'</span></td>';
						}
					}else{
						echo '<td class="feriado"><span>'.$dia_actual.'</span></td>';
					}
				}else{		
					if ($dia_actual == $hoy){
						if($Fecha>0){
							echo '<td align="center" class="fertivo"><a class="fertivoLink" href="javascript:;" onclick="DiaFestivo(\''.$Fecha.'\',\'L\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}else{
							echo '<td align="center" class="hoy"><a class="hoyLink" href="javascript:;" onclick="DiaFestivo(\''.$newFechaFormat.'\',\'F\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}
					}else{
						if($Fecha>0){
							echo '<td align="center" class="fertivo"><a class="fertivoLink" href="javascript:;" onclick="DiaFestivo(\''.$Fecha.'\',\'L\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}else{
							echo '<td align="center" class="laboral"><a class="laboralLink" href="javascript:;" onclick="DiaFestivo(\''.$newFechaFormat.'\',\'F\','.$LinkFechaPasado.')">'.$dia_actual.'</a></td>';
						}
					}
				}
	
				$dia_actual++;
				$numero_dia++;
				if ($numero_dia == 7){
					$numero_dia = 0;
					echo '</tr>';
				}
	
		}
		
		//compruebo que celdas me faltan por escribir vacias de la última semana del mes
		for ($i=$numero_dia;$i<7;$i++){
			echo '<td></td>';
		}
		
		echo '</tr>';
		echo '</table>';
	}
	
	private function calcula_numero_dia_semana($dia,$mes,$ano){
		$numerodiasemana = date('w', mktime(0,0,0,$mes,$dia,$ano));
		if ($numerodiasemana == 0) 
			$numerodiasemana = 6;
		else
			$numerodiasemana--;
		return $numerodiasemana;
	}
	
	//funcion que devuelve el último día de un mes y año dados
	private function ultimoDia($mes,$ano){ 
		$ultimo_dia=28; 
		while (checkdate($mes,$ultimo_dia + 1,$ano)){ 
		   $ultimo_dia++; 
		} 
		return $ultimo_dia; 
	} 
	
	private function dame_nombre_mes($mes){
		 switch ($mes){
			case 1:
				$nombre_mes="Enero";
				break;
			case 2:
				$nombre_mes="Febrero";
				break;
			case 3:
				$nombre_mes="Marzo";
				break;
			case 4:
				$nombre_mes="Abril";
				break;
			case 5:
				$nombre_mes="Mayo";
				break;
			case 6:
				$nombre_mes="Junio";
				break;
			case 7:
				$nombre_mes="Julio";
				break;
			case 8:
				$nombre_mes="Agosto";
				break;
			case 9:
				$nombre_mes="Septiembre";
				break;
			case 10:
				$nombre_mes="Octubre";
				break;
			case 11:
				$nombre_mes="Noviembre";
				break;
			case 12:
				$nombre_mes="Diciembre";
				break;
		}
		return $nombre_mes;
	}

}
?>