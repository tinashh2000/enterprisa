<?php
declare(strict_types=1);
namespace Api;

use Api\Messaging\CMessage;
use Ffw\Crypt\CCrypt9;
use Ffw\Status\CStatus;
use Api\Mt;

require_once("Api/Bootstrap.php");





/**
 * Example: Get and parse all unseen emails with saving their attachments.
 *
 * @author Sebastian Krätzig <info@ts3-tools.info>
 */

require_once Mt::$appDir . '/Api/Ffw/Messaging/Imap/autoload.php';

use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use Api\Users\CurrentUser;


function syncMessages() {
    $dir = CAssistant::moduleDataPath(CurrentUser::getUsername()) . "/Imap";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $emailSettings = CAssistant::getEmailSettings();
    if ($emailSettings["data"] == "") return false;
    $settingsJson = CCrypt9::unScrambleText($emailSettings['data']);
    $settings = json_decode($settingsJson, true);
    $inboxPath = "{{$settings['incomingServer']}:{$settings['incomingPort']}/{$settings['incomingDelivery']}/{$settings['incomingSecurity']}}INBOX";
    $mailbox = new Mailbox(
        $inboxPath, // IMAP server and mailbox folder
        $settings['username'], // Username for the before configured mailbox
        $settings['password'], // Password for the before configured username
        $dir, // Directory, where attachments will be saved (optional)
        'US-ASCII' // Server encoding (optional)
    );

    try {
        $mail_ids = $mailbox->searchMailbox('ALL');
    } catch (ConnectionException $ex) {
        die('IMAP connection failed: '.$ex->getMessage());
    } catch (\Exception $ex) {
        die('An error occured: '.$ex->getMessage());
    }

    foreach ($mail_ids as $mail_id) {
        $email = $mailbox->getMailHeader($mail_id);

        echo "<br>---------------------------------------------------------------------------------------------------------";
        echo '<br>from-name:</b> '.(string) (isset($email->fromName) ? $email->fromName : $email->fromAddress)."\n";
        echo '<br><b>from-email:</b> '.(string) $email->fromAddress."\n";
        echo '<br><b>to:</b> '.(string) $email->toString."\n";
        echo '<br><b>subject:</b> '.(string) $email->subject."\n";
        echo '<br><b>message_id:</b>['  . (string) $email->messageId . "]" . $mail_id . ")";

        if (!empty($email->autoSubmitted)) {
            $mailbox->markMailAsRead($mail_id);
            echo "+------ IGNORING: Auto-Reply ------+\n";
        }

        if (!empty($email_content->precedence)) {
            // Mark email as "read" / "seen"
            $mailbox->markMailAsRead($mail_id);
            echo "+------ IGNORING: Non-Delivery Report/Receipt ------+\n";
        }
    }

    $mailbox->disconnect();
}


syncMessages();