<?php
/**
 * Created by PhpStorm.
 * User: tineo
 * Date: 23/12/16
 * Time: 08:20 PM
 */


require_once 'vendor/swiftmailer/swiftmailer/lib/swift_required.php';

include_once 'config.php';

//echo md5("hola");
function sendit($sala, $piso, $sede, $user, $motivo, $emails, $bcc)
{
// Create the message
    $message = Swift_Message::newInstance()
        // Give the message a subject
        ->setSubject('Test desde Heroku')
        // Set the From address with an associative array
        ->setFrom(array('bot@tineo.mobi' => 'Bot'))
        ->setBcc($bcc)
        // Set the To addresses with an associative array
        ->setTo($emails)
        // Give it a body
        ->setBody('Here is the message itself')
        // And optionally an alternative body
        ->addPart('<table width="528" border="0" align="left" cellpadding="0" cellspacing="0">
				  <tr>
					<td height="1" colspan="3" align="left"><img src="http://reservasentel.info/images/logo.png" width="187" height="52"/></td>
				  </tr>
                  <tr>
					<td height="15" colspan="3"></td>
				  </tr>
				  <tr>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td bgcolor="#FFFFFF" style="font-family: Arial, Helvetica, sans-serif;font-size: 12px;color: #3F3F3F;text-decoration: none;">
					 
					 El usuario '.$user.' ha solicitado una reserva en la sala '.$sala.' del piso '.$piso.' de la sede '.$sede.'. <br/>
					 '.$motivo.'
					 
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
					  <strong>' . NombreAplicativo . '</strong></td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
				  </tr>
				  <tr>
					<td bgcolor="#FFFFFF">&nbsp;</td>
					<td height="25" bgcolor="#FFFFFF"><div align="center" style="font-family: Arial, Helvetica, sans-serif;font-size: 11px;color: #3F3F3F;text-decoration: none;">
					  Todos los Derechos Reservados &reg; - ' . NombreAplicativo . '</div></td>
					<td bgcolor="#FFFFFF">&nbsp;</td>
				  </tr>
				</table>', 'text/html')

        // Optionally add any attachments
        //->attach(Swift_Attachment::fromPath('my-document.pdf'))
    ;

// Create the Transport

    if ("reservasentel.info" == $_SERVER['HTTP_HOST']) {
        $transport = Swift_MailTransport::newInstance();
    } else {
        $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername('unmailfulano@gmail.com')
            ->setPassword('996666567');
    }


    /*
    You could alternatively use a different transport such as Sendmail or Mail:

    // Sendmail
    $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

    // Mail
    $transport = Swift_MailTransport::newInstance();
    */

// Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);


// Send the message
    $result = $mailer->send($message);


    //echo $result;
    //echo $_SERVER['HTTP_HOST'];
}