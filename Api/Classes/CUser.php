<?php

namespace Api\Users;

use Accounta\CAccounta;
use Api\AppConfig;
use Api\CPrivilege;
use Api\Forensic\CForensic;
use Api\Mt;
use Api\AppDB;
use Api\ServiceClass;
use Api\Session\CSession;
use Api\SqlBuilder;
use Ffw\Crypt\CCrypt8;
use Ffw\Crypt\CCrypt9;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;
use Ffw\Messaging\CEmail;
use Api\CPerson;
use Api\Roles\CRole;
class CUser extends ServiceClass {
    const GENDER_MALE = 1;
    const GENDER_FEMALE=2;

    const MARITAL_SINGLE = 1;
    const MARITAL_MARRIED = 2;
    const MARITAL_WIDOW = 3;
    const MARITAL_OTHER = 4;

    var $comments;
    var $creationDate;
    var $id;
    var $lastUpdateDate;
    var $password;
    var $roles;
    var $privilegesList;
    var $privileges;
    var $username;
    var $personUid;
    var $creator;
    var $flags;

    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    protected static $forensicPath = "Api_Users";

//    static $orderByParams = array("name", "address", "city", "country", "phone", "email");
//    static $selectItems = array("classId", "name");
//    static $dbItems = array("*");
//    static $searchParams = array("name", "address", "city", "country", "email");

    static $orderByParams = array("%2.name", "%2.address", "%2.city", "%2.country", "%2.phone", "%2.email");
    static $selectItems = array("%1.username", "%2.name");
    static $joinElements = array("%1.personUid = %2.uid");
    static $joinItems = "%1.*, %1.id AS userId, %2.*, %2.id AS personId";
    static $joinDBs = null; //Initialized in __setup
    static $searchParams = ["%2.name", "%2.email", "%2.address", "%2.city", "%2.country"];
    static $defaultCondition;

    function __construct($username, $password, $privileges, $privilegesList, $roles) {
        $this->id = 0;
        $this->privileges = $privileges;
        $this->privilegesList = $privilegesList;
        $this->roles = $roles;
        $this->username = $username;
        $this->password = $password;
        $this->comments = "";
        $this->lastUpdateDate = "";
        $this->creationDate = gmdate("Y-m-d H:i:s");
        $this->creator = CurrentUser::getUsername();
        $this->flags = 0;
    }

    function nullify() {
        $this->privileges = null;
        $this->privilegesList = null;
        $this->username = null;
        $this->password = null;
        $this->comments = null;
        $this->lastUpdateDate = null;
        $this->creationDate = null;
    }
    static function __setup() {
        self::$defaultTable = Mt::tableName("Users");
        self::$defaultPath = Mt::dataPath("Users");
        self::$moduleDescription = "Users";
        self::$className = "CUser";
        self::$defaultRecordName = "user";
        self::$permissionsBase = 0;
        self::$joinDBs = [self::$defaultTable, CPerson::$defaultTable];

        $currentUser = CurrentUser::getUsername();
        self::$defaultCondition = CPerson::$defaultTable . ".creator='$currentUser' OR FIND_IN_SET('$currentUser', visibility) > 0 OR FIND_IN_SET('*', visibility) > 0";
        parent::_setupComplete();
    }

    static function setLoggedIn() {
        $username = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
        if ($username != "") {
            AppDB::query("UPDATE " . self::$defaultTable . " SET lastLoginDate=UTC_TIMESTAMP(), lastPingDate=NOW() WHERE username='$username'" );
        }
    }

    static function setLastPinged() {
        $username = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
        if ($username != "") {
            AppDB::query("UPDATE " . self::$defaultTable . " SET lastPingDate=UTC_TIMESTAMP() WHERE username='$username'" );
        }
    }

    static function init($reset) {
        if ($reset) AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . " (
                `id`                BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                `creationDate`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `lastUpdateDate`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `lastLoginDate`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `lastPingDate`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                personUid           VARCHAR(250) UNIQUE,
                FOREIGN KEY(personUid) REFERENCES " . CPerson::$defaultTable . "(uid),
                `creator`           VARCHAR(64) NOT NULL,
                `privileges`        BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `privilegesList`    MEDIUMTEXT,
                `roles`             MEDIUMTEXT, 
                `username`          VARCHAR(64) NOT NULL PRIMARY KEY UNIQUE,
                `password`          VARCHAR(128) NOT NULL,
                `comments`          VARCHAR(8192) NOT NULL DEFAULT '')";
        AppDB::beginTransaction();
        if (AppDB::query($q)) {
            CForensic::initModule(self::$forensicPath);
            AppDB::commit();
            return CStatus::pushStatus("User records creation successfully");
        }
        return CStatus::pushError("CUser creation failed");
    }

    function exists() {
        $id = AppDB::ffwRealEscapeString($this->id);
        $username = AppDB::ffwRealEscapeString($this->username);
        $personUid = AppDB::ffwRealEscapeString($this->personUid);
        $q = "SELECT * FROM " . self::$defaultTable . " WHERE (username='$username' || personUid='$personUid') AND id != '$id' LIMIT 1";

        if ($res = AppDB::query($q)) {
            if ($user = AppDB::fetchAssoc($res)) {
                return $user;
            }
        }
        return false;
    }

    function sqlPrepare() {
        AppDB::ffwRealEscapeStringX($this->username);
        AppDB::ffwRealEscapeStringX($this->creator);
        AppDB::ffwRealEscapeStringX($this->comments);
        $this->privileges = CDecimal::integer64($this->privileges);
        AppDB::ffwRealEscapeStringX($this->privilegesList);
        AppDB::ffwRealEscapeStringX($this->roles);
    }

    function edit() {

        $username = self::getRealUsername($this->username);
        if ($username != CurrentUser::getUsername()) {
            if (!CPrivilege::check(CPrivilege::ALTER_USER))  {
                CStatus::jsonError("Access Denied@");
                return false;
            }
        }

        if ($this->password !== null && $this->password != "")
            $this->password = crypt($this->password, "oqpioeqwpe". rand(0, 981123));

        $q = "UPDATE " . self::$defaultTable . " SET
            lastUpdateDate=UTC_TIMESTAMP(),";

            if (CPrivilege::isAdmin())
                $q .="privilegesList='$this->privilegesList', roles='$this->roles',";

            if ($this->password != "")
                $q .= "password = '$this->password',";

            $q .= "comments = '$this->comments'
            WHERE username = '{$this->username}'
        ";

        AppDB::beginTransaction();
        CStatus::pushSettings(0);
        if ($res = AppDB::query($q)) {
            CForensic::log(self::$forensicPath, "Edit User", $this->id, json_encode($this));
            AppDB::commit();
            CStatus::popSettings();
            return CStatus::jsonSuccess("User updated successfully");
        }
        AppDB::rollback();
        CStatus::popSettings();
        return CStatus::jsonError("Error");
    }

    function create($sendActivation = false) {
        $this->creator = CurrentUser::getUsername();
        if (!CPrivilege::check(CPrivilege::CREATE_USER))  {
            $this->privileges = 0;
            $this->privilegesList = "";
//            CStatus::jsonError("You do not have permission to create users");
//            return false;
        }

        $this->sqlPrepare();
        $this->password = crypt($this->password, "oqpioeqwpe". rand(0, 981123));

        if (CUser::exists()) {
            CStatus::jsonError("User already exists");
            return false;
        }

        $person = null;
        CStatus::pushSettings(0);
        AppDB::beginTransaction();

        $q = "INSERT INTO " . self::$defaultTable . " SET
        creationDate=UTC_TIMESTAMP(),
        lastUpdateDate=UTC_TIMESTAMP(),
        personUid = '$this->personUid',
        creator = '$this->creator',
        privileges='$this->privileges',
        privilegesList='$this->privilegesList',
        roles='$this->roles',
        username = '$this->username',
        password = '$this->password',
        comments = '$this->comments'
    ";

        try {
            if ($res = AppDB::query($q)) {
                $this->id = AppDB::last_id($res);
                CForensic::log(self::$forensicPath, "Create User", $this->id, json_encode($this));
                AppDB::commit();

                $activation = ", but activation email was not sent";
                if ($sendActivation) {
                    if (self::activateAccount($this->username)) {
                        $activation = ", and an activation email was sent to user";
                    }
                }
                CStatus::popSettings();

                return CStatus::jsonSuccess("User account created$activation ");
            }
        } catch (\Exception $e) {

        }
        CStatus::popSettings();
        AppDB::rollback();
        CStatus::jsonError("User creation failed " . AppDB::db()->getLastError());
    }

    static function getRealUsername($u) {
        switch ($u) {
            case "me":
                return CurrentUser::getUsername();
            case "admin":
                break;
        }
        return $u;
    }

    static function get($username, $cond="") {
        $username = CUser::getRealUsername($username);

        $currentUser = CurrentUser::getUsername();
        AppDB::ffwRealEscapeStringX($username);

        $q = AppDB::leftJoinX(CUser::$defaultTable, CPerson::$defaultTable, "%1.personUid=%2.uid", "%2.*, %1.*, %1.id AS userId, %2.id as personId") . " WHERE " . CUser::$defaultTable . ".username='$username' OR ". CPerson::$defaultTable . ".email='$username'" .
            (CPrivilege::isAdmin() ?
                "" :
                " AND  (FIND_IN_SET('*', visibility) > 0 OR FIND_IN_SET('$currentUser', visibility) > 0) ") .
            " LIMIT 1";

        if ($res = AppDB::query($q)) {
            if ($item = AppDB::fetchAssoc($res)) {
                unset($item['password']);
                return CStatus::jsonSuccessItem("user", $item);
            }

            if (AppDB::numRows($res) == 0) {
                return false; //CStatus::jsonError("Not found2->$username");
            }
        }
        return CStatus::jsonError("Error");
    }

    static function getByPersonUid($uid) {
        AppDB::ffwRealEscapeStringX($uid);

        $q = "SELECT id, username FROM " . self::$defaultTable. " WHERE personUid='$uid' LIMIT 1";

        if ($res = AppDB::query($q)) {
            if ($item = AppDB::fetchAssoc($res)) {
                return CStatus::jsonSuccessItem("user", $item);
            }

            if (AppDB::numRows($res) == 0) {
                return false; //CStatus::jsonError("Not found2->$username");
            }
        }
        return CStatus::jsonError("Error");
    }

    static function fetch($start=0, $limit=25) {
        if (!CPrivilege::check(CPrivilege::VIEW_USER)) return false;

        $start = CDecimal::integer64($start);
        $limit = CDecimal::integer64($limit);

        $q = "SELECT * FROM " . self::$defaultTable . " LIMIT $start, $limit";
        $cond = CPerson::$defaultCondition;
        $q = AppDB::leftJoinX(self::$defaultTable, CPerson::$defaultTable, "%1.personUid=%2.uid", "%1.*, %2.*") . ($cond != '' ? " WHERE $cond" : "") . " LIMIT $start, $limit";
        if ($res = AppDB::query($q)) {
            echo '{"status" : "OK", "users" : [';
            $cm = "";
            while($item = AppDB::fetchAssoc($res)) {
                echo $cm . '{"username" : "' . $item['username'] . '", "fullName" : "'. $item['name'] . '" , "phone" : "'. $item['phone'] . '" }';
                $cm = ",";
            }
            echo ']}';
            return true;
        }
    }

    static function fetchN($start=0, $limit=25, $sortOrder=null, $orderBy=null, $query="", $groupBy=null) {   //Search records. Return also the total count of matching records
        if (!CPrivilege::check(CPrivilege::VIEW_USER)) return CStatus::jsonError("You do not have privileges to access user records");
        parent::fetchN($start, $limit, $sortOrder, $orderBy, $query, $groupBy);

        return CStatus::jsonError("Unexpected error");
    }

    static function activeUsers() {
        if (!CPrivilege::check(CPrivilege::VIEW_USER)) return false;
        $dt = gmdate("Y-m-d H:i:s", strtotime("-20 minutes"));
        $q = "SELECT * FROM " . self::$defaultTable . " WHERE lastUpdateDate >= '$dt'";
        if ($res = AppDB::query($q)) {
            return AppDB::numRows($res);
        }
        return 0;
    }

    static function search($query="", $start=0, $limit=25) {
        if (!CPrivilege::check(CPrivilege::VIEW_USER))
            return CStatus::jsonError("Access denied");

        AppDB::ffwRealEscapeStringX($query);

        $q = AppDB::leftJoinX(self::$defaultTable, CPerson::$defaultTable, "%1.personUid=%2.uid", "%2.*, %1.username, %1.privileges, %1.privilegesList, %1.roles, %1.personUid, %1.comments");

        if ($query != "")
            $q .= " WHERE " . CPerson::$defaultTable . ".name LIKE '%$query%' OR " . CPerson::$defaultTable . ".email LIKE '%$query%' OR " . CUser::$defaultTable . ".username LIKE '%$query%' LIMIT $start, $limit";

        return $res = AppDB::query($q);
    }

    static function count($condition="") {
        if (!CPrivilege::check(CPrivilege::VIEW_USER)) return false;
        $q = "SELECT * FROM " . self::$defaultTable;
        if ($res = AppDB::query($q)) {
            return AppDB::numRows($res);
        }
        return 0;
    }

    static function getUsernamesWithPermission($permission) {

        AppDB::ffwRealEscapeStringX($permission);
        $permissionW = ($permission - ($permission % 100)) + CPrivilege::ALL;
        if ($permissionW == $permission) $permissionW = "";

        $q = AppDB::leftJoinX(self::$defaultTable, CRole::$defaultTable, "(FIND_IN_SET(%2.id, %1.roles) > 0)", "GROUP_CONCAT(DISTINCT (" . self::$defaultTable . ".username) SEPARATOR ',') as usernames ") .
        "WHERE " . self::$defaultTable . ".privileges=" . CPrivilege::ROLE_ADMINISTRATOR .
            " OR " . CRole::$defaultTable . ".privileges=1 
            OR FIND_IN_SET('$permission', " . self::$defaultTable . ".privilegesList) 
            OR FIND_IN_SET('$permission', " . CRole::$defaultTable . ".privilegesList)" .
            ($permissionW == "" ? "" :
                ( " OR FIND_IN_SET('$permissionW', " . self::$defaultTable . ".privilegesList) 
                    OR FIND_IN_SET('$permissionW', " . CRole::$defaultTable . ".privilegesList)"));
        if ($res = AppDB::query($q)) {
            if ($item = AppDB::fetchAssoc($res)) {
                return CStatus::jsonSuccessItems("users", $item['usernames']);
            }
        }
        return false;
    }

    static function getMany($users) {
        $u = array();
        AppDB::ffwRealEscapeStringX($users);
        $uArray = explode(",", str_replace(";", ",", $users));

        //"SELECT  name, email, username FROM " . self::$defaultTable .

        $q = AppDB::leftJoinX(self::$defaultTable, CPerson::$defaultTable, "%1.personUid=%2.uid","%2.name,%2.email,%1.username") . " WHERE ";
        $cm = "";
        foreach($uArray as $us) {
            $q .= "$cm username='$us'";
            $cm=" OR ";
        }

        if ($res = AppDB::query($q)) {
            while($item=AppDB::fetchAssoc($res)) {
                array_push($u, $item);
            }
            return $u;
        }
        return false;
    }

    static function recoverPassword($username, $message="")
    {

        $message = "<h3>Account Recovery</h3><br>
<p>You have requested to reset your password. If this process was not initiated by you, kindly ignore this message,
 otherwise click the link below to recover your password.</p><br>
";
        $subject = "Enterprisa ERP Account Recovery";

        if ($user = self::get($username)) {
            return self::activateAccountX($user, $subject, $message);
        }
        return CStatus::jsonError("User not found");

    }

    static function activateAccountX($user, $subject="", $message="") {
        require_once(__DIR__ . "/../Ffw/Messaging/CEmail.php");

        $dt = gmdate("YmdHis");
        $recoveryInformation = CCrypt9::scrambleText("$dt,{$user['username']},{$user['email']},576728,{$user['name']},{$user['lastUpdateDate']}");

        $root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $link = $root . Mt::$appRelDir."/RecoverPassword?key=$recoveryInformation";

        $name = $user['name'];
        if ($message == "") {
            $message = "<h3>Account Activation</h3><br><p>Hi, $name</p>
<p>A user account has been created for you on the Enterprisa ERP Platform. If you already have access, kindly ignore this message,
otherwise click on the link below to set your password and gain access to your account.</p><br>";
        }

        if ($subject == "") {
            $subject = "Enterprisa ERP Account Setup";
        }

        $message .= "<a href='$link'>Click here to set your password.</a> You can also copy this link and paste it 
in your browser to reset your password.<br><br>
This link is valid for 24-hours.<br><br>";

//            $email = new CEmail("mail.pulsezw.com", 465, "enterprisa@pulsezw.com", "5]ltDFN3i6{.", array($user['email']), "Enterprisa ERP Account Setup", $message);
        $email = new CEmail("mail.pulsezw.com", 465, "enterprisa@pulsezw.com", "5]ltDFN3i6{.", array($user['email']), $subject, $message,"enterprisa@pulsezw.com");
        if ($email->sendX())
            return CStatus::jsonSuccess("A link has been sent to your email address");
        else
            return CStatus::jsonError("Account recovery failed. Please try again");
    }

    static function activateAccount($username) {
        require_once(__DIR__ . "/../Ffw/Messaging/CEmail.php");

        AppDB::ffwRealEscapeStringX($username);

        CStatus::pushSettings(0);
        if ($user = self::get($username)) {
            self::activateAccountX($user);
        }
        CStatus::popSettings();
        return false;
    }

    static function activateAccounts() {
        $q = AppDB::leftJoinX(self::$defaultTable, CPerson::$defaultTable, "%1.personUid=%2.uid", "%1.id, %1.username, %2.email, %2.name, %2.phone") . " ";
        if ($res = AppDB::query($q)) {
            while ($item = AppDB::fetchAssoc($res)) {
                self::activateAccountX($item);
            }
        }
    }

    static function verifyRecoveryKey($key) {
        try {
            $items = CCrypt9::unScrambleText($key);
            $a = explode(",", $items);
            $currentDate = strtotime("now");
            $dateOfRequest = strtotime($a[0]);
            $timeSince = ($currentDate - $dateOfRequest);
            $username = AppDB::ffwRealEscapeString($a[1]);
            $email = AppDB::ffwRealEscapeString($a[2]);
            $mWord = AppDB::ffwRealEscapeString($a[3]);
            $name = AppDB::ffwRealEscapeString($a[4]);
            $lastUpdateOnRequest = strtotime(AppDB::ffwRealEscapeString($a[5]));

            if ($usr = self::get($username)) {
                $lastUpdateDate = strtotime($usr['lastUpdateDate']);

                //After updating the password, lastUpdateDate changes, so make sure we are only updating if
                //lastUpdateDate is equal to the one on request
                if ($timeSince > 0 && $timeSince < 86400 && $mWord == 576728 && $lastUpdateOnRequest == $lastUpdateDate)
                    return true;
            }
        } catch (\Exception $e) {

        }
        return false;
    }

    static function resetPassword($key, $newPassword) {
        try {
            $items = CCrypt9::unScrambleText($key);
            $a = explode(",", $items);
            $currentDate = strtotime("now");
            $dateOfRequest = strtotime($a[0]);
            $timeSince = ($currentDate - $dateOfRequest);
            $username = AppDB::ffwRealEscapeString($a[1]);
            $email = AppDB::ffwRealEscapeString($a[2]);
            $mWord = AppDB::ffwRealEscapeString($a[3]);
            $name = AppDB::ffwRealEscapeString($a[4]);
            $lastUpdateOnRequest = strtotime(AppDB::ffwRealEscapeString($a[5]));

            if ($usr = self::get($username)) {
                $lastUpdateDate = strtotime($usr['lastUpdateDate']);

                if ($timeSince <= 0 || $timeSince >= 86400 || $mWord != 576728 || $lastUpdateOnRequest != $lastUpdateDate)
                    return false;

                //After updating the password, lastUpdateDate changes, so make sure we are only updating if
                //lastUpdateDate is equal to the one on request
                $password = crypt($newPassword, "oqpioeqwpe" . rand(0, 981123));
                if ($res = AppDB::query("UPDATE " . CUser::$defaultTable . " SET lastUpdateDate=UTC_TIMESTAMP(), password='$password' WHERE username='$username' LIMIT 1")) {
                    if (AppDB::affectedRows() > 0)
                        return true;
                }
            }
        } catch  (\Exception $e) {

        }
        return false;
    }
}

CUser::__setup();
