<?php

namespace Currencies;

use Api\CPrivilege;
use Api\ServiceClass;
use Api\AppDB;
use Api\Mt;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Hira\CHira;
use Modules\CModule;
use Api\Forensic\CForensic;

class CCurrency extends ServiceClass {

    const FLAG_DEFAULT  = 1 << 0;
    const FLAG_VIRTUAL  = 1 << 1;
    const FLAG_BANK  = 1 << 2;
    const FLAG_CRYPTO  = 1 << 3;

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $position;
    var $name;
    var $ratio;
    var $symbol;
    var $decimalSymbol;
    var $groupSymbol;
    var $trailingZeros;
    var $visibility;
    var $description;
    var $creator;
    var $flags;

    static $cache = array();

    const CURRENCIES_PERMISSIONSBASE = modulePrivilegesBase;
    const CURRENCIES_ALL = self::CURRENCIES_PERMISSIONSBASE + CPrivilege::ALL;
    const CURRENCIES_READ = self::CURRENCIES_PERMISSIONSBASE + CPrivilege::READ;
    const CURRENCIES_WRITE = self::CURRENCIES_PERMISSIONSBASE + CPrivilege::WRITE;
    const CURRENCIES_CREATE = self::CURRENCIES_PERMISSIONSBASE + CPrivilege::CREATE;
    const CURRENCIES_DELETE = self::CURRENCIES_PERMISSIONSBASE + CPrivilege::DELETE;

    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;

    static $orderByParams = array("name");
    static $selectItems = array("id", "name");
    static $dbItems = array("*");
    static $searchParams = ["name"];
    static $defaultCondition;

    function __construct($name, $ratio, $flags, $description)
    {
        $this->id=0;
        $this->name = $name;
        $this->ratio = $ratio; //CDecimal::decimal($ratio);
        $this->flags = $flags;
        $this->decimalSymbol = '.';
        $this->groupSymbol = ',';
        $this->trailingZeros = 0;
        $this->visibility = "*";
        $this->description = $description;
    }

    static function __setup() {
        self::$defaultTable = Mt::tableName("Currencies");
        self::$defaultPath = Mt::dataPath("Currencies");
        self::$moduleDescription = "Currencies";
        self::$className = "CCurrencies";
        self::$defaultRecordName = "currency";
        self::$permissionsBase = modulePrivilegesBase;
        parent::_setupComplete();
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `name`          VARCHAR(64) NOT NULL UNIQUE,
                ratio           DECIMAL(30,4) NOT NULL,
                symbol          VARCHAR(16) NOT NULL,
                trailingZeros   INT UNSIGNED NOT NULL DEFAULT 0,
                decimalPointSymbol   VARCHAR(4) NOT NULL DEFAULT '.',
                groupSymbol   VARCHAR(4) NOT NULL DEFAULT ',',
                visibility      TEXT NOT NULL,
                creator        TEXT NOT NULL,
                flags           BIGINT UNSIGNED NOT NULL DEFAULT 0,
                description     TEXT NOT NULL); ";

		try {
			if (AppDB::query($q)) {
				return CStatus::pushStatus("Currencies records creation successfully");
			}
		} catch (\Exception $e) {
			return CStatus::pushError("Currencies creation failed");
		}
		return CStatus::pushError("Currencies creation failed");
    }

    function sqlPrepare() {
        $this->creator = CurrentUser::getUsername();
        $this->id = CDecimal::integer64($this->id);
        $this->position = CDecimal::integer64($this->position);
        AppDB::ffwRealEscapeStringX($this->name);
        $this->ratio = CDecimal::decimal($this->ratio);
        AppDB::ffwRealEscapeStringX($this->symbol);
        AppDB::ffwRealEscapeStringX($this->decimalSymbol);
        AppDB::ffwRealEscapeStringX($this->groupSymbol);
        $this->trailingZeros = CDecimal::integer64($this->trailingZeros);
        AppDB::ffwRealEscapeStringX($this->visibility);
        AppDB::ffwRealEscapeStringX($this->description);
        AppDB::ffwRealEscapeStringX($this->creator);
        $this->flags = intval($this->flags);
    }

    function exists() {
        $name = AppDB::ffwRealEscapeString($this->name);
        $id = AppDB::ffwRealEscapeString($this->id);
        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE name='$name' AND id != '$id' LIMIT 1")) {
            return AppDB::numRows($res) > 0;
        }
        return false;
    }

    function create() {
        if (!self::checkPrivilege(CPrivilege::CREATE)) return false;
        $this->sqlPrepare();

        if (self::exists()) {
            CStatus::jsonError("Currency already exists");
        }

        if ($this->ratio <= 0) CStatus::jsonError("Invalid ratio :$this->ratio");

        $q = "INSERT INTO   " . self::$defaultTable . "  SET
            creationDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            ratio='$this->ratio',
            symbol='$this->symbol',
            decimalPointSymbol='$this->decimalSymbol',
            groupSymbol='$this->groupSymbol',
            trailingZeros='$this->trailingZeros',
            visibility='$this->visibility',
            description='$this->description',
            flags='$this->flags',
            creator='$this->creator'
        ";

        AppDB::beginTransaction();
        if ($res = AppDB::query($q)) {
            $idx = AppDB::last_id($res); //Last inserted item
            if ($this->flags & self::FLAG_DEFAULT) {
                AppDB::query("UPDATE " . self::$defaultTable . " SET flags = (flags & ~". self::FLAG_DEFAULT.") WHERE id != '$idx' AND (flags & " . self::FLAG_DEFAULT . ") > 0  LIMIT 1");
            }
            AppDB::commit();
            return true;
        }
        AppDB::rollback();
        return false;
    }

    function edit()
    {
        if (!self::checkPrivilege(CPrivilege::MODIFY)) return false;
        $this->sqlPrepare();

        if (self::exists()) {
            CStatus::jsonError("Currency already exists");
        }

        if ($this->ratio <= 0) CStatus::jsonError("Invalid ratio");

        AppDB::beginTransaction();
        if ($res = AppDB::query("UPDATE " . self::$defaultTable . "  SET
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            ratio='$this->ratio',
            symbol='$this->symbol',
            decimalPointSymbol='$this->decimalSymbol',
            groupSymbol='$this->groupSymbol',
            trailingZeros='$this->trailingZeros',
            visibility='$this->visibility',
            description='$this->description',
            flags='$this->flags' WHERE id='$this->id'
        ")) {

            if ($this->flags & self::FLAG_DEFAULT) {
                AppDB::query("UPDATE " . self::$defaultTable . " SET flags = (flags & ~" . self::FLAG_DEFAULT . ") WHERE id != '$this->id' AND (flags & " . self::FLAG_DEFAULT . ") > 0 LIMIT 1");
            }

            AppDB::commit();
            return true;
        }
        AppDB::rollback();
        return false;
    }

    static function search($query="", $start=0, $limit=25) {
        if (!self::checkPrivilege(CPrivilege::READ)) return false;
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . self::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . self::$defaultTable . " WHERE name LIKE '%$query%' OR description LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }

    static function getDefault() {
        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE (flags & " . self::FLAG_DEFAULT . ") > 0 LIMIT 1")) {
            if ($item = AppDB::fetchAssoc($res)) {
                return CStatus::jsonSuccessItem("currency", $item);
            }
        }
        return false;
    }

    static function getCached($id) {
        return static::get($id);
    }

    static function get($id, $cond='') {
        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE id='$id' LIMIT 1")) {
            if ($item = AppDB::fetchAssoc($res)) {
                return CStatus::jsonSuccessItem("currency", $item);
            }
        }
        return null;
    }

    static function toBase($amount, $currencyId) {
        CStatus::pushSettings(0);
        if ($c = self::getCached($currencyId)) {
            $amount = CDecimal::decimal($amount);
            $ratio = $c['ratio'];
            if ($ratio == 0) return 0;
            CStatus::popSettings();
            return CDecimal::div128( $amount, $ratio, 6 );
        }
        CStatus::popSettings();
        return null;
    }

    static function fromBase($amount, $currencyId=-1) {
        CStatus::pushSettings(0);
        if ($c = ($currencyId == -1 ? self::getDefault() : self::getCached($currencyId))) {
            $amount = CDecimal::decimal($amount);
            $ratio = $c['ratio'];
            CStatus::popSettings();
            return CDecimal::mul128($amount, $ratio, 6);
        }
        CStatus::popSettings();
        return null;
    }
}

CCurrency::__setup();