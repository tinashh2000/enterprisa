<?php
namespace Api;

use Accounta\Audit\CAudit;
use Api\AppDB;
use Api\CPrivilege;
use Api\Mt;
use Api\Users\CurrentUser;
use Entities\CEntities;
use Entities\CEntityItem;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Api\CAssistant;
use Modules\CModule;
use Api\Forensic\CForensic;
use Api\ServiceClass;
use const People\modulePrivilegesBase;
use Api\Users\CUser;
use Api\SqlBuilder;
class CPerson extends ServiceClass {

    const FLAG_DEFAULT  = 1 << 0;
    const FLAG_VIRTUAL  = 1 << 1;

    const PERSON_TYPE_USER= "User";
    const PERSON_TYPE_CONTACT= "Contact";
    const PERSON_ATTR_ADMIN = "Admin";

    const MARITAL_STATUS_SINGLE = 1;
    const MARITAL_STATUS_MARRIED = 2;
    const MARITAL_STATUS_DIVORCED = 3;
    const MARITAL_STATUS_WIDOW = 4;
    const MARITAL_STATUS_OTHER = 5;

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    static $orderByParams = array("name", "address", "city", "country", "phone", "email");
    static $selectItems = array("id", "name");
    static $dbItems = array("*");
    static $searchParams = ["name", "email", "address", "city", "country"];
    static $defaultCondition;

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $uid; //Unique id
    var $title;
    var $name;
    var $email;
    var $gender;
    var $maritalStatus;
    var $address;
    var $city;
    var $country;
    var $phone;
    var $mobilePhone;
    var $website;
    var $dob;
    var $fax;
    var $postalCode;
    var $notes;
    var $type;
    var $idNumber;
    var $attributes;
    var $categories;
    var $likes;
    var $profile;
    var $visibility;
    var $details;
    var $pics;
    var $user;
    var $flags;
    var $creator;

    static $cache = array();

    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    private static $temporaryPermission = false;
    protected static $forensicPath = "People";

    function __construct($name, $email, $gender, $maritalStatus, $phone, $type, $categories, $address, $city, $country, $notes, $flags = 0)
    {
        $this->creationDate=
        $this->lastModifiedDate=gmdate("Y-m-d H:i:s");
        $this->id=0;
        $this->title = "Mr(s)";
        $this->name=$name;
        $this->email=$email;
        $this->gender=$gender;
        $this->maritalStatus= $maritalStatus;
        $this->address=$address;
        $this->city=$city;
        $this->country=$country;
        $this->phone=$phone;
        $this->mobilePhone="";
        $this->website="";
        $this->dob="1800-01-01";
        $this->fax="";
        $this->postalCode="";
        $this->idNumber=null;
        $this->notes=$notes;
        $this->attributes="";
        $this->type=$type;
        $this->categories=$categories;
        $this->likes="";
        $this->pics="";
        $this->profile="";
        $this->details="";
        $this->visibility="*";
        $this->flags=$flags;
        $this->user = null;
        $this->creator = CurrentUser::getUsername();
    }

    static function temporaryElevate() {
        static::$temporaryPermission = true;
    }
    
    static function deElevate() {
        static::$temporaryPermission = false;
    }

    static function __setup() {
        self::$defaultTable = Mt::tableName("People");
        self::$defaultPath = Mt::dataPath("People");
        self::$moduleDescription = "People";
        self::$className = "CPerson";
        self::$defaultRecordName = "person";
        self::$permissionsBase = 0;

//        $currentUser = CurrentUser::getUsername();
        self::$defaultCondition = self::getVisibilityConnective(true); //"creator='$currentUser' OR FIND_IN_SET('$currentUser', visibility) > 0 OR FIND_IN_SET('*', visibility) > 0";
        parent::_setupComplete();
    }

    static function init($reset) {

        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                uid             VARCHAR(250) UNIQUE,
                creationDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `title`         VARCHAR(220) NOT NULL,
                `name`          VARCHAR(220) NOT NULL,
                `email`         VARCHAR(220) UNIQUE,
                `gender`        INT UNSIGNED DEFAULT 1,
                `maritalStatus` INT UNSIGNED DEFAULT 1,
                `dob`           DATE NOT NULL,
                `idNumber`      VARCHAR(220),
                `website`       TEXT NOT NULL,
                `phone`         VARCHAR(64) UNIQUE,
                `mobilePhone`   VARCHAR(64),
                `address`       TEXT NOT NULL,
                `city`          VARCHAR(220) NOT NULL DEFAULT '',
                `country`       VARCHAR(220) NOT NULL DEFAULT '',
                `fax`           VARCHAR(64),
                `postalCode`    VARCHAR(64),
                `type`          TEXT NOT NULL,
                `categories`    MEDIUMTEXT NOT NULL,
                `attributes`    MEDIUMTEXT NOT NULL,
                `likes`         MEDIUMTEXT NOT NULL,
                `details`       MEDIUMTEXT NOT NULL,
                `pics`          MEDIUMTEXT NOT NULL,             
                `creator`       TEXT NOT NULL,
                `visibility`    MEDIUMTEXT NOT NULL,
                `personFlags`   BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `personNotes`   TEXT NOT NULL); ";
        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                CPersonEntity::init($reset);
                return CStatus::jsonSuccess("Person(s) created successfully");
            }
        } catch (\Exception $e) {
        }
        return CStatus::jsonError("Person(s) creation failed");
    }

    function sqlPrepare() {
        $this->creationDate = $this->lastModifiedDate=gmdate("Y-m-d H:i:s", strtotime("now"));
        $this->id = CDecimal::integer64($this->id);
        AppDB::ffwRealEscapeStringX($this->title);
        AppDB::ffwRealEscapeStringX($this->name);

        if ($this->attributes != null) AppDB::ffwRealEscapeStringX($this->attributes);
        if ($this->categories != null) AppDB::ffwRealEscapeStringX($this->categories);
        if ($this->type != null) AppDB::ffwRealEscapeStringX($this->type);

        AppDB::ffwRealEscapeStringX($this->likes);
        AppDB::ffwRealEscapeStringX($this->email);
        $this->gender = intval($this->gender);
        $this->maritalStatus = intval($this->maritalStatus);
        AppDB::ffwRealEscapeStringX($this->website);
        AppDB::ffwRealEscapeStringX($this->idNumber);
        AppDB::ffwRealEscapeStringX($this->address);
        AppDB::ffwRealEscapeStringX($this->city);
        AppDB::ffwRealEscapeStringX($this->country);
        AppDB::ffwRealEscapeStringX($this->phone);
        AppDB::ffwRealEscapeStringX($this->mobilePhone);
        AppDB::ffwRealEscapeStringX($this->fax);
        AppDB::ffwRealEscapeStringX($this->postalCode);
        AppDB::ffwRealEscapeStringX($this->details);
        AppDB::ffwRealEscapeStringX($this->visibility);
        AppDB::ffwRealEscapeStringX($this->pics);
        AppDB::ffwRealEscapeStringX($this->notes);

//        echo "...." . $this->dob . ".....";

        $this->dob = gmdate("Y-m-d", strtotime($this->dob));
        $this->flags = CDecimal::integer64($this->flags);
        $this->creator = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
    }

    static function updateProfile($username, $items) {
        try {
            if (!is_array($items)) return;
            $username = self::getRealUsername($username);
            if (is_string($items)) $items = json_decode($items, true);
            if ($username != CurrentUser::getUsername() && !CPrivilege::checkList(CPrivilege::L_CREATE_USER))
                return CStatus::jsonError("Access denied");

            if ($usr = self::get($username)) {
                $p = json_decode($usr['profile'], true);
                foreach ($items as $k => $v) {
                    $p[$k] = $v;
                }
                $user['profile'] = AppDB::ffwRealEscapeString(json_encode($p));
                $q = "UPDATE " . self::$defaultTable . " SET profile='{$user['profile']}' WHERE id='$username';";
                if (AppDB::query($q)) {
                    return CStatus::jsonSuccess("Done");
                }
            } else {
                echo $username . " Not found!";
            }
        } catch (\Exception $e) {
        }
        return CStatus::jsonError("Error");
    }

    function addCategories($categories) {
        $this->categories = self::addEntity($this->categories, $categories);
    }

    function addType($types) {    //Add types to a CPerson
        $this->type = self::addEntity($this->type, $types);
    }

    static function addEntity($e, $values) {    //Add types to a CPerson
        $typesArray = explode(",", $e);

        $values = is_string($values) ? explode(',', $values) : explode(",", implode(",", $values));

        foreach ($values as $v) {
            if (array_search($v, $typesArray) === FALSE && $v != "")
                array_push($typesArray, $v);
        }
        return implode(",", $typesArray);
    }

    function exists() {
        $id = CDecimal::integer64($this->id);
        $name = AppDB::ffwRealEscapeString($this->name);
        $email = AppDB::ffwRealEscapeString($this->email);
        $phone = AppDB::ffwRealEscapeString($this->phone);

        $con = "";
        if ($email != "")
            $con .= " OR email = '$email'";

        if ($phone != "")
            $con .= " OR phone = '$phone'";

        if ($this->idNumber != "" && $this->idNumber != null) {
            $idNumber = AppDB::ffwRealEscapeString($this->idNumber);
            $con .= " OR idNumber = '$idNumber'";
        }

        if ($con != "")
            $con = "(".substr($con, 4) . ") AND ";
        else
            return false;   //if there was no email address or phone or idNumber, then it doesnt exist

        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE $con id != '$id' LIMIT 1")) {
            if ($p = AppDB::fetchAssoc($res)) {
                return $p;
            }
        }
        return false;
    }

    function create() {
        $this->sqlPrepare();

        if ($this->user != null && filter_var($this->user->username, FILTER_VALIDATE_EMAIL)) {    //If it was email
            if ($this->user->username != $this->email) {
                return CStatus::jsonError("Username and email mismatch");
            }
        }

        if ($this->exists()) {
            return CStatus::jsonError("Person already exists");
        }

        $canEdit = true; //CPrivilege::checkList(CEnterprisa::PEOPLE_WRITE);
        $isAdmin = CPrivilege::isAdmin();

        if (!$canEdit && CurrentUser::getUid() != $this->uid && static::$temporaryPermission !== true) {
            return CStatus::jsonError("You cannot create new users");
        }
        static::$temporaryPermission = false;

        $q = "INSERT INTO " . self::$defaultTable . "  SET
            creationDate    = UTC_TIMESTAMP(),
            lastModifiedDate= UTC_TIMESTAMP(),
            `uid`           = NULL,
            `title`         = '$this->title',
            `name`          = '$this->name',
            `email`         = " . (($this->email == '') ?  "NULL" :"'$this->email'") . ",
            `gender`        = '$this->gender',
            `maritalStatus` = '$this->maritalStatus',
            `dob`           = '$this->dob',
            `idNumber`      = " . (($this->idNumber == '') ?  "NULL" :"'$this->idNumber'") . ",
            `website`       = '$this->website',
            `address`       = '$this->address',
            `city`          = '$this->city',
            `country`       = '$this->country',
            `phone`         = " . (($this->phone == '') ?  "NULL" :"'$this->phone'") . ",
            `mobilePhone`   = '$this->mobilePhone',
            `fax`           = '$this->fax',";

            if ($isAdmin) {
                if ($this->type != null) $this->type = "";
                if ($this->attributes != null) $this->attributes = "";
                if ($this->categories != null) $this->categories = "";

                $q .= "`type`= '$this->type',  `attributes` = '$this->attributes', `categories` = '$this->categories',";
            } else {
                $q .= "`attributes` = '',`categories` = '',`type` = '',";
            }

            $q.="
            `postalCode`    = '$this->postalCode',
            `likes`         = '$this->likes',
            `details`       = '$this->details',
            `visibility`    = '$this->visibility',
            `pics`          = '',
            `personFlags`   = '$this->flags',
            `personNotes`   = '$this->notes',
            `creator`       = '$this->creator'
        ";

        AppDB::beginTransaction();
        try {
            if ($res = AppDB::query($q)) {
                $this->id = AppDB::last_id($res);
                $this->uid = Mt::generateUid($this->id);

                if (!AppDB::query("UPDATE " . self::$defaultTable . " SET uid='{$this->uid}' WHERE id='{$this->id}'" ))
                    throw new \Exception("Error while creating person record");

                self::uploadPicture(self::MODIFY_DB, "pics", $this->uid."_pp");

                $userInfo = "";

                CStatus::pushSettings(0);
                if ($this->user != null) {
                    if ($this->user->id == 0) {
                        $this->user->personUid = $this->uid;
                        if (!$this->user->create()) {
                            throw new \Exception ("Error while creating user 908");
                        }
                    } else {
                        throw new \Exception ("Error while creating user 909 {$this->user->id}");
                    }
                }

                CStatus::popSettings();
                CForensic::log(self::$forensicPath, "Create Person", $this->id, json_encode($this));
                AppDB::commit();
                return CStatus::jsonSuccess("Person created successfully");
            }
        }
        catch(\Exception $e) {

        }
        AppDB::rollback();
        return CStatus::jsonError("Person creation failed");
    }

    function edit()
    {
        $this->sqlPrepare();

        if (self::exists()) {
            CStatus::jsonError("Person already exists");
        }

        AppDB::beginTransaction();

        $canEdit = CPrivilege::checkList(CEnterprisa::PEOPLE_WRITE);
        $isAdmin = CPrivilege::isAdmin();
        if (!$canEdit && CurrentUser::getUid() != $this->uid && static::$temporaryPermission !== true) {
            return CStatus::jsonError("Access Denied!");
        }

        static::$temporaryPermission = false;

        $sql = new SqlBuilder(self::$defaultTable, SqlBuilder::UPDATE);
        $sql->addN("lastModifiedDate","UTC_TIMESTAMP()", SqlBuilder::NO_QUOTES | SqlBuilder::ADDITIONAL);
        $sql->addN("title",$this->name);
        $sql->addN("name",$this->name);
        $sql->addN("email",$this->email, $sql::NO_BLANK);
        $sql->addN("gender",$this->gender);
        $sql->addN("maritalStatus",$this->maritalStatus);
        $sql->addN("dob",$this->dob);
        $sql->addN("idNumber",$this->idNumber, $sql::NO_BLANK);
        $sql->addN("address",$this->address);
        $sql->addN("city",$this->city);
        $sql->addN("country",$this->country);
        $sql->addN("phone",$this->phone, $sql::NO_BLANK);
        $sql->addN("mobilePhone",$this->mobilePhone);
        $sql->addN("postalCode",$this->postalCode);
        $sql->addN("fax",$this->fax);
        $sql->addN("likes",$this->likes);
        $sql->addN("details",$this->details);
        $sql->addN("visibility",$this->visibility);
        $sql->addN("personFlags",$this->flags);
        $sql->addN("personNotes",$this->notes);

        if ($isAdmin) {
            $sql->addN("type",$this->type);
            $sql->addN("attributes",$this->attributes);
            $sql->addN("categories",$this->categories);
        }

        $sql->condition("id", "$this->id", SqlBuilder::O_EQ, SqlBuilder::C_NONE);
        $q = $sql->get();

        if ($res = AppDB::query($q)) {

            self::uploadPicture(self::MODIFY_DB, "pics", $this->uid."_pp");
            $userMsg = "";
            CStatus::pushSettings(0);
            if ($this->user != null) {
                if ($this->user->id == 0) {
                    $this->user->personUid = $this->uid;
                    if (!$this->user->create()) $userMsg = "but user was not created.";
                }
                else if ($this->user->personUid == $this->uid) {
                    if (!$this->user->edit()) $userMsg = "but user was not updated.";
                }
//                else {
//                    echo "Error";
//                    print_r($this);
//                }
            }
            CStatus::popSettings();

            CForensic::log(self::$forensicPath, "Edit Person", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Person updated successfully $userMsg");

        }

        AppDB::rollback();
        return CStatus::jsonError("Person update failed");
    }

    static function get($id, $cond="") {   //Search records. Return also the total count of matching records
        $currentUser = CurrentUser::getUsername();
        $uid = CurrentUser::getUid();
        if ($res = AppDB::query("SELECT * FROM " . static::$defaultTable . "  WHERE (id='$id' OR uid='$id') AND (creator='$currentUser' OR FIND_IN_SET('$currentUser', visibility) > 0 OR FIND_IN_SET('*', visibility) > 0 OR uid='$uid') LIMIT 1")) {
            return CStatus::jsonSuccessItem("person", $res);
        }
        CStatus::jsonError("Unexpected error");
    }

    static function getByName($name) {   //Search records. Return also the total count of matching records
        $currentUser = CurrentUser::getUsername();
        if ($res = AppDB::query("SELECT * FROM " . static::$defaultTable . "  WHERE " . static::$defaultTable . ".name='$name' " . self::getVisibilityConnective() . " LIMIT 1")) {
            return CStatus::jsonSuccessItem("person", $res);
        }
        CStatus::jsonError("Unexpected error");
    }

    static function getByEntities($categories, $types, $fields= "name, email") {

        AppDB::ffwRealEscapeStringX($username);

        $conditions = "";
        $cats = explode(",", $categories);
        $types = explode(",", $types);

        $cm = "";
        foreach($cats as $cat) {
            if ($cat == '') continue;
            $conditions .= "{$cm}FIND_IN_SET('$cat', categories)>0";
            $cm = " OR ";
        }

        foreach($types as $type){
            if ($type == '') continue;
            $conditions .= "{$cm}FIND_IN_SET('$type', type)>0";
            $cm = " OR ";
        }

        if ($conditions == "") return null;

        $q = "SELECT $fields FROM " . self::$defaultTable . " WHERE ($conditions) " . self::getVisibilityConnective();

        if ($res = AppDB::query($q)) {
            return $res;
        }
        return CStatus::jsonError("Error");
    }

    static function getByEmail($email) {   //Search records. Return also the total count of matching records
        $currentUser = CurrentUser::getUsername();
        if ($res = AppDB::query("SELECT * FROM " . static::$defaultTable . "  WHERE email='$email' " . self::getVisibilityConnective() . " LIMIT 1")) {
            return CStatus::jsonSuccessItem("person", $res);
        }
        CStatus::jsonError("Unexpected error");
    }

    static function getVisibilityConnective($onlyVisibiility = false) {
        $currentUser = CurrentUser::getUsername();
        $uid = CurrentUser::getUid();
        $isAdmin = CPrivilege::isAdmin();
        $con = ($onlyVisibiility) ? " " : " AND ";
//        $searchCon = $currentUser == "" ? "" : (static::$defaultTable . ".creator='$currentUser' OR FIND_IN_SET('$currentUser', " . static::$defaultTable . ".visibility) > 0 OR " . static::$defaultTable . ".uid='$uid' OR ");
        $searchCon = (static::$defaultTable . ".creator='$currentUser' OR FIND_IN_SET('$currentUser', " . static::$defaultTable . ".visibility) > 0 OR " . static::$defaultTable . ".uid='$uid' OR ");
        return $isAdmin ? "" : " $con ({$searchCon} FIND_IN_SET('*', ". static::$defaultTable . ".visibility) > 0) ";
    }

    function getByAnyMeans() {
        $q = "SELECT *, id FROM " . self::$defaultTable . " WHERE "; // . self::getVisibilityConnective();

        if ($this->email != "")
            $q .= " email='{$this->email}' OR ";
        if ($this->phone != "")
            $q .= " phone='{$this->phone}' OR ";
        if ($this->idNumber != "")
            $q .= " idNumber='{$this->idNumber}' OR ";
        if ($this->name != "")
            $q .= " name='{$this->name}'";
        else
            return null;

        if ($res = AppDB::query($q)) {
            return AppDB::fetchAssoc($res);
        }
        return null;
    }

    static function search($query="", $start=0, $limit=25) {
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . self::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . self::$defaultTable . " WHERE name LIKE '%$query%' OR email LIKE '%$query%' OR notes LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }

    static function crossGetPics($id, $table, $key="personUid") {
        AppDB::ffwRealEscapeStringX($key);
        AppDB::ffwRealEscapeStringX($key);
        $q = AppDB::leftJoinX(self::$defaultTable, $table, "%2.$key=%1.id", "%1.*"). " WHERE $table.id='$id' " . self::getVisibilityConnective(false) . " LIMIT 1" ;
        if ($res = AppDB::query($q)) {
            return AppDB::fetchAssoc($res);
        }
        return null;
    }
}

CPerson::__setup();