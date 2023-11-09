<?php
namespace Api;

use Accounta\Audit\CAudit;
use Api\AppDB;
use Api\CPerson;
use Api\Mt;
use Api\Users\CurrentUser;
use Crm\Customers\CCustomer;
use Companies\CCompany;
use Sales\CSales;
use Inventory\Products\CPackage;
use Currencies\CCurrency;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Crm\CCrm;
use Modules\CModule;
use Api\Forensic\CForensic;
use Api\CPrivilege;
use Api\ServiceClass;

use const Sales\modulePrivilegesBase;
use Api\Users\CUser;
abstract class CommentClass extends ServiceClass {

    const FLAG_POSTED = 1 << 0;
    const FLAG_ACCEPTED = 1 << 1;

    var $creationDate;
    var $id;
    var $entityId;
    var $message;
    var $userUid;
    var $date;
    var $flags;
    var $creator;

    public static $entityName="entityId"; //Change it in the child class
    public static $entityDB;
    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    protected static $forensicPath = "Assistant_Comment";

    static $joinElements = array('%1.entityId=%2.id');
    static $joinItems = "%1.*,%1.id as commentId";
    static $joinDBs = null; //Initialized in __setup

    function __construct($entityId, $message, $userUid, $date, $flags = 0)
    {
        $this->id=0;
        $this->creationDate= gmdate("Y-m-d H:i:s");
        $this->entityId = $entityId;
        $this->message = $message;
        $this->userUid = $userUid;
        $this->date = $date;
        $this->flags = $flags;
        $this->creator = CurrentUser::getUsername();
    }

    static function __setup() {
        static::$defaultTable = CAssistant::tableName("Comments");
        static::$defaultPath = CAssistant::moduleDataPath("Comments");
        static::$moduleDescription = "Comments";
        static::$className = "CComments";
        static::$defaultRecordName = "comment";
        parent::_setupComplete();
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . static::$defaultTable);

        CModule::use_module("Inventory");

        $q = "CREATE TABLE IF NOT EXISTS " . static::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                date    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()," .
            static::$entityName . "Id          BIGINT UNSIGNED, " .
            ((static::$entityName != "entity" && static::$entityDB != "") ? //Only use a foreign key if the entity is not called entity and if there is a joining DB
                "FOREIGN KEY(" . static::$entityName . "Id) REFERENCES " . static::$entityDB . "(id)," : "") .
                "`message`      MEDIUMTEXT, 
                userUid         VARCHAR(250),
                FOREIGN KEY(userUid) REFERENCES " . CPerson::$defaultTable . "(uid),
                parent          BIGINT UNSIGNED,
                `creator`       TEXT NOT NULL,
                `flags`         BIGINT UNSIGNED NOT NULL DEFAULT 0);";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(static::$forensicPath);
                return CStatus::pushStatus("Comments created successfully");
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return CStatus::pushError("Comments creation failed");
    }

    function sqlPrepare() {
        $this->creationDate = $this->lastModifiedDate=gmdate("Y-m-d H:i:s", strtotime("now"));
        $this->id = CDecimal::integer64($this->id);
        AppDB::ffwRealEscapeStringX($this->creationDate);
        AppDB::ffwRealEscapeStringX(static::$entityName);
        $this->entityId = CDecimal::integer64($this->entityId);
        AppDB::ffwRealEscapeStringX($this->userUid);
        $this->message = AppDB::ffwRealEscapeString(trim($this->message));
        $this->flags = CDecimal::integer64($this->flags);
        $this->creator = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
    }

    function exists() {
        $id = AppDB::ffwRealEscapeString($this->id);
        $entityName = AppDB::ffwRealEscapeString(static::$entityName);
        $entityId = AppDB::ffwRealEscapeString($this->entityId);
        $userUid = AppDB::ffwRealEscapeString($this->userUid);
        $message = trim(AppDB::ffwRealEscapeString($this->message));
        if ($res = AppDB::query("SELECT * FROM " . static::$defaultTable . " WHERE ({$entityName}Id='$entityId' AND userUid='$userUid') AND `message`='$message' AND id != '$id' LIMIT 1")) {
            return AppDB::numRows($res) > 0;
        }
        return false;
    }

    function create() {
        $this->sqlPrepare();

        if ($this->exists()) {
            CStatus::jsonError("Sale already exists");
        }

        $q = "INSERT INTO " . static::$defaultTable . "  SET
            `creationDate` = UTC_TIMESTAMP(),
            `lastModifiedDate` = UTC_TIMESTAMP(),
            `date`      = UTC_TIMESTAMP()," .
            static::$entityName."Id='$this->entityId',
            `message`='$this->message',
            `userUid`='$this->userUid',
            `parent`='0',
            `flags` = '$this->flags',
            `creator` = '$this->creator'
        ";

        AppDB::beginTransaction();
        if ($res = AppDB::query($q)) {
            $this->id = AppDB::last_id($res);
            CForensic::log(static::$forensicPath, "Create Sale", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("");
        }
        AppDB::rollback();
        return CStatus::jsonError("");
    }

    function edit()
    {
        $this->sqlPrepare();

        if (static::exists()) {
            CStatus::jsonError("Comment already exists");
        }

        if ($res = AppDB::query("UPDATE " . static::$defaultTable . "  SET
            `lastModifiedDate`  = UTC_TIMESTAMP(),            
            `date`      = UTC_TIMESTAMP()," .
            static::$entityName."Id='$this->entityId',
            `message`='$this->message',
            `userUid`='$this->userUid',
            `parent`='0',
            `flags` = '$this->flags' WHERE id='$this->id'")) {
            CForensic::log(static::$forensicPath, "Edit Sale", $this->id, json_encode($this));
            return CStatus::jsonSuccess("");
        }
        return CStatus::jsonError("");
    }

    static function search($query="", $start=0, $limit=25) {
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . static::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . static::$defaultTable . " WHERE name LIKE '%$query%' OR notes LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }

    static function moveToTop($saleId) {
        if ($item = static::get($saleId)) {
            $q = "UPDATE " . static::$defaultTable . " SET `lastModifiedDate`  = UTC_TIMESTAMP(), position=0 WHERE id='{$item['id']}'";
            if (AppDB::query($q)) {
                return CStatus::jsonSuccess("OK");
            }
        }
        return CStatus::jsonError("Comment moved successfully");
    }

    static function moveUp($saleId) {
        if ($item = static::get($saleId)) {

            if ($resItemAboveThis = AppDB::query("SELECT * FROM " . static::$defaultTable . " WHERE " . static::accessCondition() . " AND position <= '{$item['position']}' AND lastModifiedDate >= '{$item['lastModifiedDate']}' AND status='{$item['status']}' AND id != '{$item['id']} AND ()' ORDER BY position DESC, lastModifiedDate ASC LIMIT 1 ")) {
                if ($itemAboveThis = AppDB::fetchAssoc($resItemAboveThis)) {
                    $newPos = CDecimal::integer($itemAboveThis['position']);
                    if ($newPos > 0) $newPos--;

                    $q = "UPDATE " . static::$defaultTable . " SET `lastModifiedDate`  = UTC_TIMESTAMP(), position='{$newPos}' WHERE id='{$item['id']}'";
                    if (AppDB::query($q)) {
                        return CStatus::jsonSuccess("OK");
                    }
                }
            } else {
                $q = "UPDATE " . static::$defaultTable . " SET `lastModifiedDate`  = UTC_TIMESTAMP(), position=0 WHERE id='{$item['id']}'";
                if (AppDB::query($q)) {
                    return CStatus::jsonSuccess("OK");
                }
            }

        }
        return CStatus::jsonError("Comment not moved");
    }

    static function moveDown($saleId) {
        if ($item = static::get($saleId)) {
            if ($resItemBelowThis = AppDB::query("SELECT * FROM " . static::$defaultTable . " WHERE  " . static::accessCondition() . " AND  position >= '{$item['position']}' AND lastModifiedDate <= '{$item['lastModifiedDate']}' AND status='{$item['status']}' AND id != '{$item['id']}' ORDER BY position ASC, lastModifiedDate DESC LIMIT 1")) {   //Look for the next item after this, and move one step from it.

                if ($itemBelowThis = AppDB::fetchAssoc($resItemBelowThis)) {
                    $newPos = CDecimal::integer($itemBelowThis['position']);
                    if ($newPos > 0) $newPos++;

                    $q = "UPDATE " . static::$defaultTable . " SET `lastModifiedDate`  = UTC_TIMESTAMP(), position='{$newPos}' WHERE id='{$item['id']}'";
                    if (AppDB::query($q)) {
                        return CStatus::jsonSuccess("OK");
                    }
                }
            } else {
                $q = "UPDATE " . static::$defaultTable . " SET `lastModifiedDate` = UTC_TIMESTAMP(), position=1000000 WHERE id='{$item['id']}'";
                if (AppDB::query($q)) {
                    return CStatus::jsonSuccess("OK");
                }
            }
        }
        return CStatus::jsonError("Comment not moved");
    }
}

CommentClass::__setup();
