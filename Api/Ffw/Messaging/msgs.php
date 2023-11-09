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

define('FOS_MSG_INBOX',1);
define('FOS_MSG_SENTBOX',2);

define('FOS_MSG_READ',1);
define('FOS_MSG_DELETED',4);

function fosInitMessages($username) {
	global $fosMsgDB;
	
	$q = "CREATE TABLE IF NOT EXISTS mt_fos_msgs_{$username}_box(
						Idx			BIGINT KEY AUTO_INCREMENT,
						Box			CHAR,
						Date		DATETIME,
						Status		BIGINT,
						ToFromUser	VARCHAR(32),
						Subject		VARCHAR(4096),
						Msg			VARCHAR(8192)
	)";
	return $fosMsgDB->query($q);
}

function fosSendEMail($toemail,$subject,$msg,$fromname="MT-FOS-Mailer",$fromemail="noreply@mtdevices.com") {

	$subject=fosRealEscapeString($subject);
	$msg=fosRealEscapeString($msg);
	$toemail=fosRealEscapeString($toemail);

	$headers = "From: $fromname <$fromemail>\r\n" .
				"Reply-To: $fromemail\r\n" .
				'X-Mailer: MT-FOS-Mailer';

//	ini_set("SMTP","localhost" );
//	ini_set('smtp_port',25);	
//	ini_set('sendmail_from', $fromemail); 

	return mail($toemail,$subject,$msg,$headers);
}

function fosSendEMailer($hostaddr,$smtpport,$username,$password,$toemail,$subject,$msg,$fromname="MT-FOS-Mailer",$fromemail="noreply@mtdevices.com") {

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

function fosSendMessage($subject,$msg,$toUsername) {
	global $fosMsgDB, $statusClass;
	if (!isLoggedIn() || ($toUsername == $_SESSION['username'])) return;
	$subject=fosRealEscapeString($subject);
	$msg=fosRealEscapeString($msg);
	$toUsername=fosRealEscapeString($toUsername);
	
	$dt = gmdate("Y-m-d H:i:s",strtotime("now +{$_SESSION['fosTimeZone']}"));
	
	if (isLoggedIn()) {
		$username = fosRealEscapeString($_SESSION['username']);
		if ($us = getUser($toUsername)) {
			if ($us['username'] == $toUsername) {
				$q = "INSERT INTO mt_fos_msgs_{$toUsername}_box SET
							Date='$dt',
							Box=".FOS_MSG_INBOX. ",
							Status=0,
							ToFromUser='$username',
							Subject='$subject',
							Msg='$msg'
							";
	
				if ($fosMsgDB->query($q)) {
					$q = "INSERT INTO mt_fos_msgs_{$username}_box SET
								Date='$dt',
								Box=". FOS_MSG_SENTBOX . ",
								Status=". FOS_MSG_READ .",
								ToFromUser='$toUsername',
								Subject='$subject',
								Msg='$msg'
								";
					if ($fosMsgDB->query($q)) {
						$statusClass->setStatusMsg("Message successfully sent!");
						return true;
					}
				}
			}
		}		
	}
	$statusClass->setErrorMsg("Message sending failed " . mysqli_error());
	return false;
}

function fosReadMessageById($msgIndex=0) {
	global $fosMsgDB;
	
	$msgIndex = intval($msgIndex);

	if (isLoggedIn()) {
		$username = fosRealEscapeString($_SESSION['username']);
		$q = "SELECT * FROM mt_fos_msgs_{$username}_box WHERE Idx=$msgIndex LIMIT 1";
		if ($msgs = $fosMsgDB->query($q)) {
			if ($msg = $fosMsgDB->fetchAssoc($msgs)) {
				$q = "UPDATE mt_fos_msgs_{$username}_box SET Status = Status |".FOS_MSG_READ." WHERE Idx=$msgIndex";
				$fosMsgDB->query($q);
				return $msg;
			}
		}		
	}
	return false;	
}

function fosReadMessage($whichBox=FOS_MSG_INBOX,$msgIndex=0) {
	
	global $fosMsgDB;
	
	$msgIndex = intval($msgIndex);
	$whichBox=intval($whichBox);

	if (isLoggedIn()) {
		$username = fosRealEscapeString($_SESSION['username']);
		$q = "SELECT * FROM mt_fos_msgs_{$username}_box WHERE Box=$whichBox AND Idx=$msgIndex LIMIT 1";
		if ($msgs = $fosMsgDB->query($q)) {
			if ($msg = $fosMsgDB->fetchAssoc($msgs)) {
				$q = "UPDATE mt_fos_msgs_{$username}_box SET Status = Status &~".FOS_MSG_READ." WHERE Box=$whichBox AND Idx=$msgIndex";
				$fosMsgDB->query($q);
				return $msg;
			}			
		}		
	}
	return false;
}

function fosGetMessageHeaders($whichBox=FOS_MSG_INBOX,$bUnread=0,$startIndex=0,$numHeaders=100) {
	global $fosMsgDB;
	
	if (isLoggedIn()) {
		$username = fosRealEscapeString($_SESSION['username']);
		$cond = "";
		if ($bUnread === TRUE) $cond = "(Status & 1) = 0 AND";
		else if ($bUnread === FALSE) $cond = "(Status & 1) = 1 AND";

		$q = "SELECT Date,ToFromUser, Subject, Status, Idx FROM mt_fos_msgs_{$username}_box WHERE $cond Box=$whichBox AND Idx>=$startIndex ORDER BY Date DESC LIMIT $numHeaders";
		return $fosMsgDB->query($q);		
	}
	return false;	
}


?>
