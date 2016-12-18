<?php
include_once __DIR__.'/../class/class.phpmailer.php';
class EnvioEmail{
	
	function Enviar($email,$asunto,$contenido){
		//mail($email,$asunto,$this->Plantilla($contenido),"From: ".NombreAplicativo." <".EmailEnvio.">\r\nContent-type: text/html; charset=iso-8859-1\r\n");

        //error_log("¡email!", 0);
        $env = getenv("OPENSHIFT_HOMEDIR");
        if($env !=""){
        $mail = new PHPMailer;

        $mail->IsSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';                 // Specify main and backup server
        $mail->Port = 465;                                    // Set the SMTP port
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'unmailfulano@gmail.com';                // SMTP username
        $mail->Password = '996666567';                  // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable encryption, 'ssl' also accepted

        $mail->From = 'unmailfulano@gmail';
        $mail->FromName = 'Bot';
        //$mail->AddAddress($email, 'Josh Adams');  // Add a recipient
        //$mail->AddAddress($email);               // Name is optional
        $mail->AddAddress("itsudatte01@gmail.com");
        $mail->AddAddress("soporte@keinescorp.com");
        $mail->IsHTML(true);                                  // Set email format to HTML

        $mail->Subject = $asunto;
        $mail->Body    = $this->Plantilla($contenido);
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        //error_log("¡email2!", 0);
        if(!$mail->Send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
            exit;
        }
        //error_log("¡email3!", 0);
        }else{
            mail($email,$asunto,$this->Plantilla($contenido),"From: ".NombreAplicativo." <".EmailEnvio.">\r\nContent-type: text/html; charset=iso-8859-1\r\n");
        }


	}
	
	function Plantilla($contenido){
		return '<table width="528" border="0" align="left" cellpadding="0" cellspacing="0">
				  <tr>
					<td height="1" colspan="3" align="left"><img src="http://reservasentel.info/images/logo.png" width="187" height="52"/></td>
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