<?php

namespace Entities;

use Api\AppConfig;
use Api\Mt;
use Api\CPrivilege;
use Entities\Categories\CCategory;
use Entities\Companies\CCompany;
use Entities\Taxes\CTax;
use Ffw\Status\CStatus;
use const Entities\modulePrivilegesBase;

class CEntities {

    const ENTITY_PERMISSIONSBASE = modulePrivilegesBase;
    const ENTITY_ALL = self::ENTITY_PERMISSIONSBASE + CPrivilege::ALL;
    const ENTITY_READ = self::ENTITY_PERMISSIONSBASE + CPrivilege::READ;
    const ENTITY_WRITE = self::ENTITY_PERMISSIONSBASE + CPrivilege::WRITE;
    const ENTITY_CREATE = self::ENTITY_PERMISSIONSBASE + CPrivilege::CREATE;
    const ENTITY_DELETE = self::ENTITY_PERMISSIONSBASE + CPrivilege::DELETE;

    const CATEGORIES_PERMISSIONSBASE =  modulePrivilegesBase + 400;
    const CATEGORIES_ALL = self::CATEGORIES_PERMISSIONSBASE + CPrivilege::ALL;
    const CATEGORIES_READ = self::CATEGORIES_PERMISSIONSBASE + CPrivilege::READ;
    const CATEGORIES_WRITE = self::CATEGORIES_PERMISSIONSBASE + CPrivilege::WRITE;
    const CATEGORIES_CREATE = self::CATEGORIES_PERMISSIONSBASE + CPrivilege::CREATE;
    const CATEGORIES_DELETE = self::CATEGORIES_PERMISSIONSBASE + CPrivilege::DELETE;

    const TAXES_PERMISSIONSBASE =  modulePrivilegesBase + 600;
    const TAXES_ALL = self::TAXES_PERMISSIONSBASE + CPrivilege::ALL;
    const TAXES_READ = self::TAXES_PERMISSIONSBASE + CPrivilege::READ;
    const TAXES_WRITE = self::TAXES_PERMISSIONSBASE + CPrivilege::WRITE;
    const TAXES_CREATE = self::TAXES_PERMISSIONSBASE + CPrivilege::CREATE;
    const TAXES_DELETE = self::TAXES_PERMISSIONSBASE + CPrivilege::DELETE;

    const BASIC_PERMISSION = self::ENTITY_READ;

    public static $moduleName;
    public static $modulePrefix;
    public static $moduleDataPath;

    static function moduleDataPath($module) {
        return self::$moduleDataPath . "/$module";
    }

    static function tableName($module) {
        return self::$modulePrefix . "$module";
    }

    static function __setup() {
        self::$moduleName = "Entity";
        self::$modulePrefix = Mt::tableName("Entity_");
        self::$moduleDataPath = Mt::dataPath("Entity");
    }

    static function init($reset=false) {
        CEntity::init($reset);
        CEntityItem::init($reset);
    }
}

CEntities::__setup();

