<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Mt;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Messaging\CMessage;
use Ffw\Crypt\CCrypt8;
use Ffw\Status\CStatus;
use Helpers\HtmlHelper;
use Helpers\UserHelper;
use Helpers\PersonHelper;
use Helpers\ProductHelper;
use Api\Users\CurrentUser;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Ffw\Messaging\CEmail;

require_once("Bootstrap.php");
if (!isset($_POST['r']) && !isset($_GET['r'])) {
    die('{"status": "Error", "message": "Resource not found"}');
    return false;
}

$postMethod = isset($_POST['r']) ? true : 0;

$rq = $postMethod ? $_POST['r'] : $_GET['r'];

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

switch ($rq) {
    case 'send':
        if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['message']) && isset($_POST['details'])) {
            $dd = $_POST['key'];

            $ilen = strlen($dd);
            $ndd = "";
            for($i=0;$i < $ilen; $i++) {
                $ch = ord(substr($dd, $i, 1));
                if ($ch >= 46 && $ch <=55) {
                    $ch += 2;
                    $ndd .= chr($ch);
                }
                else {
                    $ch-=2;
                    $ndd .= chr($ch);
                }
            }

            $str = CCrypt8::unScrambleText($ndd);
            if (substr($str, -9) == "Authentik") {
                $dt = substr($str, 0, 19);
                if ( (strtotime("now") - strtotime($dt)) < 10800){  //Should send within 3 hours
                    $subject = AppDB::ffwRealEscapeString($_POST['subject']);
                    $message = AppDB::ffwRealEscapeString($_POST['message']);


                    //$hostAddress, $smtpPort, $username, $password, $receivers, $subject, $message, $senderEmail='', $senderName="EnterprisaPro Mail  System"

                    require_once ("Ffw/Messaging/CEmail.php");
                    if (CEmail::sendContactEmail($subject, $message)) {
                        return CStatus::jsonSuccess("Message sent successfully");
                    } else
                        return CStatus::jsonError("Email sending failed");

                } else
                    return CStatus::jsonError("Session expired. Refresh this page and resend your message");
            }
            return CStatus::jsonError("Error. Message not sent");
        }
        break;
}
CStatus::jsonError("Resource not found");

