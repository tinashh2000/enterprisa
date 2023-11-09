<?php

/***************************************************************************************
* Copyright (C) 2019 Tinashe Mutandagayi                                               *
*                                                                                      *
* This file is part of the Cademia Pro source code. The author(s) of this file     *
* is/are not liable for any damages, loss or loss of information, deaths, sicknesses   *
* or other bad things resulting from use of this file or software, either direct or    *
* indirect.                                                                            *
* Terms and conditions for use and distribution can be found in the license file named *
* LICENSE.TXT. If you distribute this file or continue using it,                       *
* it means you understand and agree with the terms and conditions in the license file. *
* binding this file.                                                                   *
*                                                                                      *
* Happy Coding :)                                                                      *
****************************************************************************************/



function ffwSendEMail($toemail,$subject,$msg,$fromname="MT-FOS-Mailer",$fromemail="noreply@mtdevices.com") {

	$subject=ffwRealEscapeString($subject);
	$msg=ffwRealEscapeString($msg);
	$toemail=ffwRealEscapeString($toemail);

	$headers = "From: $fromname <$fromemail>\r\n" .
				"Reply-To: $fromemail\r\n" .
				'X-Mailer: MT-FOS-Mailer';

//	ini_set("SMTP","localhost" );
//	ini_set('smtp_port',25);	
//	ini_set('sendmail_from', $fromemail); 

	return mail($toemail,$subject,$msg,$headers);
}

function ffwSendEMailer($hostaddr,$smtpport,$username,$password,$toemail,$subject,$msg,$fromname="MT-FOS-Mailer",$fromemail="noreply@mtdevices.com") {

//	$subject=fosRealEscapeString($subject);
//	$msg=fosRealEscapeString($msg);
//	$toemail=fosRealEscapeString($toemail);

	$headers = "From: $fromname <$fromemail>\r\n" .
				"Reply-To: $fromemail\r\n" .
				'X-Mailer: MT-FOS-Mailer';

	require_once("phpmailer/PHPMailerAutoload.php");
	
	$mail = new PHPMailer();
	
	$mail->IsSMTP();  // telling the class to use SMTP
	$mail->IsHTML(true);
	$mail->Host     = $hostaddr;	// SMTP server	
	$mail->Port = $smtpport;
	
	//Whether to use SMTP authentication
	if ($hostaddr != "")
		$mail->SMTPAuth = true;
	//Username to use for SMTP authentication
	$mail->Username = $username;
	//Password to use for SMTP authentication
	$mail->Password = $password;
	
	$mail->SetFrom($fromemail,$fromname);
	$mail->AddAddress($toemail);
	
	$mail->Subject  = $subject;
	$mail->Body     = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">' . $msg . '</html>';	

	$mail->Body     = $msg;	

	return $mail->Send();
}

//fosSendEMailer("mailserver",25,"senderemail","password","recepient","subject","msg","fromname","fromemail");
//fosSendEMail("recepient","subject","msg","fromname","fromemail");

?>