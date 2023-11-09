<?php
namespace Ffw\Messaging;

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
use Ffw\Status\CStatus;

class CEmail
{
	var $hostAddress, $smtpPort, $username, $password, $receivers, $subject, $msg, $senderName, $senderEmail;

	function __construct($hostAddress, $smtpPort, $username, $password, $receivers, $subject, $message, $senderEmail='', $senderName="EnterprisaPro Mail  System")
	{
		$this->hostAddress = $hostAddress;
		$this->smtpPort = $smtpPort;
		$this->username= $username;
		$this->password = $password;
		$this->receivers = $receivers;
		$this->subject = $subject;
		$this->message = $message;
		$this->senderName = $senderName;
		$this->senderEmail = $senderEmail == '' ? $username : $senderEmail;
		$this->replyEmail = "";
	}

	function sendX()
	{
		require_once("PHPMailer/PHPMailerAutoload.php");
		$mail = new \PHPMailer();
		$mail->IsHTML(true);
		$mail->IsSMTP();  // telling the class to use SMTP
		$mail->SMTPAuth = true;

		if ($this->smtpPort == 465)
		    $mail->SMTPSecure = "ssl";
		else if ($this->smtpPort == 587 )
            $mail->SMTPSecure = "tls";
		else {

        }

		$mail->Host = $this->hostAddress;    // SMTP server
		$mail->Port = $this->smtpPort;
		$mail->Username = $this->username;
		$mail->Password = $this->password;
		$mail->SetFrom($this->senderEmail, $this->senderName);

		if ($this->replyEmail != "")
		    $mail->addReplyTo($this->replyEmail);

		$mail->Body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml">' . $this->message . '</html>';

		$numRecipients = count($this->receivers);

        if ($numRecipients == 1)
            $mail->AddAddress($this->receivers[0]);
        else {
            $errorEmails = "";
            for ($c = 0; $c < $numRecipients; $c++) {
                if (filter_var($this->receivers[$c], FILTER_VALIDATE_EMAIL))
                    $mail->AddBcc($this->receivers[$c]);
                else
                    $errorEmails .= $this->receivers[$c] . "; ";
            }
            if ($errorEmails != "") CStatus::pushError("Emails were not sent to the following" . $errorEmails);
        }

		$mail->Subject = $this->subject;
		$ret = $mail->Send();
		return $ret;
	}

	static function sendEmail($fromUser, $recipients, $subject, $message) {
        $email = new CEmail("mail.pulsezw.com", 465, "enterprisa@pulsezw.com", "5]ltDFN3i6{.", $recipients, $subject, $message,"enterprisa@pulsezw.com", $fromUser['name']);
//        print_r($recipients);
        die();
        return $email->sendX();
    }
}
