<?php
namespace Api;
use Api\AppDB;
use Api\Forensic\CForensic;
use Api\Mt;
use Api\Users\CurrentUser;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;

class CSlide extends ServiceClass
{

    static $orderByParams = array("name", "price", "owner");
    static $selectItems = array("id", "name");
    static $dbItems = array("*");
    static $searchParams = ["name", "year", "price"];
    static $defaultCondition;

    var $id;
    var $creationDate;
    var $lastUpdateDate;
    var $startDate;
    var $endDate;
    var $headingTheme;
//    var $buttonTheme;
//    var $textPosition;
    var $buttonCaption;
    var $buttonLink;
    var $bigHeading;
    var $smallHeading;
    var $text;
    var $name;
    var $position;
    var $clicks;
    var $flags;

    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    protected static $forensicPath = "Slides";

    static function __setup() {
        self::$defaultTable = Mt::tableName("Slides");
        self::$defaultPath = Mt::dataPath("Slides");
        self::$moduleDescription = "Slides";
        self::$className = "CSlide";
        self::$defaultRecordName = "slide";
        self::$permissionsBase = 0;
        self::$defaultCondition;
//        parent::_setupComplete();
    }

    function __construct($name, $startDate, $endDate,  $position, $flags = 0)
    {
        $this->creationDate =
        $this->lastUpdateDate = gmdate("Y-m-d H:i:s");
        $this->id = 0;
        $this->name = $name;
        $this->startDate=$startDate;
        $this->endDate=$endDate;
        $this->position=$position;
        $this->flags=$flags;
        $this->headingTheme =
        $this->buttonCaption=
            $this->buttonLink =
                $this->bigHeading =
                    $this->smallHeading =
                        $this->text = "";
    }

    static function init($reset=false)
    {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);
        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
            `id`            BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
			`creationDate`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `lastUpdateDate`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
			`startDate`     DATETIME,
			`endDate`       DATETIME,
			`position`  INT,
			`clicks`    INT,
			`headingTheme`  TEXT,
			`buttonCaption` TEXT,
			`buttonLink`    TEXT,
			`bigHeading`    TEXT,
			`smallHeading`  TEXT,
			`text`      TEXT,
			`name`      VARCHAR(64),
            `creator`   TEXT NOT NULL,
            `flags`     BIGINT UNSIGNED NOT NULL DEFAULT 0)";
        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                return CStatus::pushStatus("Slides created successfully");
            }
        } catch (Exception $e) {
        }
        return CStatus::pushError("Slides creation failed");
    }

    function sqlPrepare()
    {
        $this->id = CDecimal::integer64($this->id);
        $this->creationDate = $this->lastUpdateDate = gmdate("Y-m-d H:i:s", strtotime("now"));
        $this->startDate = gmdate("Y-m-d H:i:s", strtotime($this->startDate));
        $this->endDate = gmdate("Y-m-d H:i:s", strtotime($this->endDate));
        $this->position = CDecimal::integer64($this->position);
        $this->clicks = CDecimal::integer64($this->clicks);
        $this->creator = CurrentUser::getUsername();
        AppDB::ffwRealEscapeStringX($this->name);
        AppDB::ffwRealEscapeStringX($this->buttonCaption);
        AppDB::ffwRealEscapeStringX($this->buttonLink);
        AppDB::ffwRealEscapeStringX($this->headingTheme);
        AppDB::ffwRealEscapeStringX($this->bigHeading);
        AppDB::ffwRealEscapeStringX($this->smallHeading);
        AppDB::ffwRealEscapeStringX($this->text);
    }

    function exists() {
        $id = AppDB::ffwRealEscapeString($this->id);
        $name = AppDB::ffwRealEscapeString($this->name);

        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE name='$name' AND id != '$id' LIMIT 1")) {
            return AppDB::fetchAssoc($res);
        }
        return false;
    }

    function create()
    {
        $this->sqlPrepare();

        if ($this->exists()) {
            return CStatus::jsonError("Slide already exists");
        }

        $q = "INSERT INTO " . self::$defaultTable . " SET 
            `creationDate`  = UTC_TIMESTAMP(),
            `lastUpdateDate` = UTC_TIMESTAMP(),
			`startDate`='$this->startDate',
			`endDate`='$this->endDate',
			`position`='$this->position',
			`clicks`=0,
			`buttonCaption`='$this->buttonCaption',
			`buttonLink`='$this->buttonLink',
			`headingTheme`='$this->headingTheme',
			`bigHeading`='$this->bigHeading',
			`smallHeading`='$this->smallHeading',
			`text`='$this->text',
			`name`='$this->name',
			`creator`='$this->creator'";

        AppDB::beginTransaction();
        try {
            if ($res = AppDB::query($q)) {
                $this->id = AppDB::last_id($res);
                self::uploadPicture(false, "slides", $this->id, "slidePic");
                CForensic::log(self::$forensicPath, "Create Slide", $this->id, json_encode($this));
                AppDB::commit();
                return CStatus::jsonSuccess("Slide created successfully");
            }
        }
        catch(Exception $e) {
        }
        AppDB::rollback();
        return CStatus::jsonError("Slide creation failed");
    }

    function edit()
    {
        $this->sqlPrepare();

        if ($this->exists()) {
            return CStatus::jsonError("Slide already exists");
        }

        $q = "UPDATE " . self::$defaultTable . " SET 
            `lastUpdateDate` = UTC_TIMESTAMP(),
			`startDate`='$this->startDate',
			`endDate`='$this->endDate',
			`position`='$this->position',
			`buttonCaption`='$this->buttonCaption',
			`buttonLink`='$this->buttonLink',
			`headingTheme`='$this->headingTheme',
			`bigHeading`='$this->bigHeading',
			`smallHeading`='$this->smallHeading',
			`text`='$this->text',
			`name`='$this->name' WHERE id='$this->id'";

        if ($res = AppDB::query($q)) {
            self::uploadPicture(false, "slides", $this->id, "slidePic");
            CForensic::log(self::$forensicPath, "Edit Slide", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Slide updated successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Slide update failed");
    }
}

CSlide::__setup();