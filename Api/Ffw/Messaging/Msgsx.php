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

function fosSendEMailerX($hostaddr,$smtpport,$username,$password,$toemail,$subject,$msg,$fromname="MT-FOS-Mailer",$fromemail="noreply@mtdevices.com") {

//	$subject=fosRealEscapeString($subject);
//	$msg=fosRealEscapeString($msg);
//	$toemail=fosRealEscapeString($toemail);
    global $errormsg;

	$headers = "From: $fromname <$fromemail>\r\n" .
				"Reply-To: $fromemail\r\n" .
				'X-Mailer: MT-FOS-Mailer';

            
	require_once("phpmailer/PHPMailerAutoload.php");
	
	$mail = new PHPMailer();
	
	$mail->IsSMTP();  // telling the class to use SMTP
	$mail->IsHTML(true);
	$mail->Host     = $hostaddr;	// SMTP server	
	$mail->Port = $smtpport;
	
	$mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "tls";
    $mail->Host = "send.one.com";
    $mail->Port = 587;
	
	//Whether to use SMTP authentication
	if ($hostaddr != "")
		$mail->SMTPAuth = true;
	//Username to use for SMTP authentication
	$mail->Username = $username;
	//Password to use for SMTP authentication
	$mail->Password = $password;
	
	$mail->SetFrom($fromemail,$fromname);


	$mail->Body     = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml">' . $msg . '</html>';


	$mail->AddAddress($toemail[0]);

    $erroremails = "";
	for ($c=1;$c < count($toemail);$c++) {
        if (filter_var($toemail[$c], FILTER_VALIDATE_EMAIL))
		    $mail->AddBcc($toemail[$c]); 
		else
		    $erroremails .= $toemail[$c]. "; ";
		//echo $toemail[$c]."<br>";
	}

if ($erroremails != "") $errormsg .=" <br><br><b class='text-danger'>Emails were not sent to the following :<b> " . $erroremails;

	$mail->Subject  = $subject;
	$mail->Body     = $msg;	
	

	$ret = $mail->Send();
    return $ret;
}
?>


