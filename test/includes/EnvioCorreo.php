<?php 

class EnvioEmail{
	
	function Enviar($email,$asunto,$contenido){
		mail($email,$asunto,$this->Plantilla($contenido),"From: ".NombreAplicativo." <".EmailEnvio.">\r\nContent-type: text/html; charset=iso-8859-1\r\n");
	
	}
	
	function Plantilla($contenido){
		return '<table width="528" border="0" align="left" cellpadding="0" cellspacing="0">
				  <tr>
					<td height="1" colspan="3" align="left"><img src="http://www.reservanextel.com/images/mail.png" width="187" height="52"/></td>
				  </tr>
                  <tr>
					<td height="15" colspan="3"></td>
				  </tr>
				  <tr>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td bgcolor="#FFFFFF" style="font-family: Arial, Helvetica, sans-serif;font-size: 12px;color: #3F3F3F;text-decoration: none;">
					 '.$contenido.'
				    </td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
				  </tr>
				  <tr>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
				  </tr>
				  <tr>
					<td bgcolor="#FFFFFF"></td>
					<td height="1" bgcolor="#757575"></td>
					<td bgcolor="#FFFFFF"></td>
				  </tr>
                   <tr>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
				  </tr>
				  
				  <tr>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td align="center" bgcolor="#FFFFFF" style="font-family: Arial, Helvetica, sans-serif;font-size: 11px;color: #3F3F3F;text-decoration: none;"><br />
					  <strong>'.NombreAplicativo.'</strong></td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
				  </tr>
				  <tr>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td height="25" bgcolor="#FFFFFF"><div align="center" style="font-family: Arial, Helvetica, sans-serif;font-size: 11px;color: #3F3F3F;text-decoration: none;">
					  Todos los Derechos Reservados &reg; - '.NombreAplicativo.'</div></td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
				  </tr>
				</table>';
	
	}
}

?>