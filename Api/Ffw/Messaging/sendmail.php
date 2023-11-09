<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  $NeedScramble=1;
  $NeedStatus = 1;
  $NeedSql = 1;

  require_once("procs/stub.php");
  require_once("procs/login/check_login.php");

    $statusmsg= "";
    $errormsg = "";


        require_once("ffw/msg/msgsx.php");

$e = "";
$cm = "";
function addEmailsQ($class) {
    global $e, $cm, $dDB;
    
    ffwRealEscapeStringX($class);
    $e .= "$cm class='$class'";
    $cm = " OR ";
}

function getEmails() {
    global $e, $dDB;
    $res = $dDB->query("SELECT GROUP_CONCAT(DISTINCT (email) SEPARATOR ';') as emails FROM mt_fwbc_reg $e");
            if ($item = $dDB->fetchAssoc($res)) {
                $emails = explode(";", $item['emails']);
                return $emails;
            }
            return array();
}

if (isset($_POST['message']) && isset($_POST['subject']) && isset($_POST['sendToWho']) && trim($_POST['message']) != "" && trim($_POST['subject'] != "")) {


    $emails = null;

    if ($_POST['sendToWho'] == "sendToSpecific" && trim($_POST['recipients']) != "") {
        $recipients = str_replace(",", ";", $_POST['recipients']);
        $emailx = explode(";", $_POST['recipientList']);
        
// firstyear
// secondyear
// thirdyear
// fourthyear
// online        
        $emails = array();
        $setE=0;

        foreach($emailx as $curE) {
            if ($curE == "firstyear" && ($setE & 1) == 0) {
                $setE |= 1;
                addEmailsQ("First Year");
            } else if ($curE == "secondyear" && ($setE & 2) == 0) {
                $setE |= 2;
                addEmailsQ("Second Year");
            } else if ($curE == "secondyear" && ($setE & 4) == 0) {
                $setE |= 4;
                addEmailsQ("Third Year");
            } else if ($curE == "thirdyear" && ($setE & 8) == 0) {
                $setE |= 8;
                addEmailsQ("Fourth Year");
            } else if ($curE == "fourthyear" && ($setE & 16) == 0) {
                $setE |= 16;
                addEmailsQ("Online");
            } else {
                array_push($emails, $curE);
            }
        }
        
        if ($e != "" ) { 
            $e = "WHERE " . $e;
            $ea = getEmails();
            $emails = array_merge($ea, $emails);
        }
//        print_r($emails);
//        die();

    } else if ($_POST['sendToWho'] == 'sendToAll') {
        if ($dbResult = $dDB->query("SELECT  GROUP_CONCAT(DISTINCT (email) SEPARATOR ';') as emails FROM mt_fwbc_reg")) {
    
            if ($item = $dDB->fetchAssoc($dbResult)) {
                $emails = explode(";", $item['emails']);
            }
            $statusmsg = "Messages sent";
        }
    } else {
        echo "Nothing to do";
    }

    if (is_array($emails) && count($emails) > 0 ) {
//        die("Tests are underway. Please try again later");
        fosSendEMailerX("", 465, "", "", $emails, $_POST['subject'], $_POST['message'], "", $fromemail="");
    } else {
        $errormsg = "No recipients specified";
    }

}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>FWBC Admin</title>
<?php require_once("scripts/cssincludes.php"); ?>
<style type="text/css">
    html,
    body,
    header,
    .carousel {
      height: 40vh;
    }

    @media (max-width: 740px) {
      html,
      body,
      header,
      .carousel {
        height: 100vh;
      }
    }

    @media (min-width: 800px) and (max-width: 850px) {
      html,body,header,.carousel {
        height: 100vh;
      }
    }

    @media (min-width: 800px) and (max-width: 850px) {
      .navbar:not(.top-nav-collapse) {
        background: #929FBA !important;
      }
    }
</style>
    <link rel='stylesheet' href='assets/select2/css/select2.min.css'>

</head>

<body>

<div><img src='img/fwbc.jpg' width='100%'/></div>
<section class="content">
    <div class="container-fluid v-align">
        <div class="row  d-flex align-self-center justify-content-center">
            <div class='col-lg-7 col-sm-12 col-sm-10 p-0 m-5'>
                <div>
<h1 class="h4">Send Message</h1>

<p><a class='text-danger'>Warning: If you send a message, it will be transmitted to all registered students. Proceed with caution</a></p>

<p><a class='text-primary'><?php echo $statusmsg; ?></a></p>
<p><a class='text-danger'><?php echo $errormsg; ?></a></p>

<form action="sendmail" onsubmit='return submitForm()' method="post" id='mailForm' >
    <input type='hidden' name='recipientList' />
                                        <div class="row mb-1">
                                           <select class='select2 col-md-4 col-sm-12' id='sendToWho' name='sendToWho' placeholder='Select your targetted recipients' data-placeholder='Select your targeted recipients' required>
                                               <option value=''></option>
                                                <optgroup label='Target recipients'>
                                                <option value='sendToSpecific'>Specific</option>
                                                <option value='sendToAll'>All Students</option>
                                                </optgroup>
                                           </select>
                                        </div>
                                        
                                        <div class="row mb-1">
                                            <select class='form-control col-12 d-none' multiple id='recipients' name='recipients' data-placeholder='Recipients'>
                                                <option value='firstyear'>First Year Students</option>
                                                <option value='secondyear'>Second Year Students</option>
                                                <option value='thirdyear'>Third Year Students</option>
                                                <option value='fourthyear'>Fourth Year Students</option>
                                                <option value='online'>Online Students</option>
                                            </select>
                                        </div>

                                        <div class="row mb-1">
                                            <input class='form-control col-12' name='subject' placeholder='Subject' required />
                                            </div>
                    <div class="row mb-5">
                        <label>Message</label>
                    <textarea id='message-area' class="form-control col-12" name="message" placeholder='Message' required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
                </div></div></div></div></section>

</body>
<script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript" src="js/bootstrap.js"></script>
<script type="text/javascript" src="js/mdb.min.js"></script>
<script type="text/javascript" src="assets/select2/js/select2.full.min.js"></script>

<script src='assets/jquery-validation/jquery.validate.min.js'></script>
<script src='assets/jquery.resizableColumns/jquery.resizableColumns.min.js'></script>
<script src='assets/bootstrap-table/bootstrap-table.min.js'></script>
<script src='assets/bootstrap-table/extensions/resizable/bootstrap-table-resizable.min.js'></script>
<script src='assets/bootstrap-table/extensions/editable/bootstrap-table-editable.js'></script>
<script src='assets/bootstrap-table/extensions/print/bootstrap-table-print-custom.js'></script>
<script src='assets/bootstrap-table/extensions/export/bootstrap-table-export.min.js'></script>
<script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
<script>
var frm = document.getElementById("mailForm");
    $(function () {

    CKEDITOR.replace('message-area');
    CKEDITOR.config.height = 200;
    CKEDITOR.config.width = '100%';
    
    $('.select2').select2({
        minimumResultsForSearch: 10,
        width: '100%',
    });

    
    $('#recipients').select2({
        minimumResultsForSearch: 10,
        width: '100%',
        tags: true
    });

    
    $("#sendToWho").on("change", (e)=>{
        if ($("#sendToWho").val()=="sendToAll") {
            $(frm.recipients).addClass("d-none");
        } else {
            $(frm.recipients).removeClass("d-none");
        }
    });
    
});

function submitForm() {
    var frm =document.getElementById("mailForm");
    frm.recipientList.value = $("#recipients").val().join(";");
    return true;
}
</script>
</html>
