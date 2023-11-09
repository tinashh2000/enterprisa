<?php

namespace Api;

use Api\Forensic\CForensic;
use Api\Mt;
use Api\Roles\CRole;
use Api\Users\CUser;
use Entities\CEntities;
use Ffw\Status\CStatus;
class CEnterprisa {

    const USERS_PERMISSIONSBASE = 0;
    const ROLES_PERMISSIONSBASE = 100;

    const PEOPLE_PERMISSIONSBASE = 0;   //modulePrivilegesBase ;
    const PEOPLE_ALL = self::PEOPLE_PERMISSIONSBASE + CPrivilege::ALL;
    const PEOPLE_READ = self::PEOPLE_PERMISSIONSBASE + CPrivilege::READ;
    const PEOPLE_WRITE = self::PEOPLE_PERMISSIONSBASE + CPrivilege::WRITE;
    const PEOPLE_CREATE = self::PEOPLE_PERMISSIONSBASE + CPrivilege::CREATE;
    const PEOPLE_DELETE = self::PEOPLE_PERMISSIONSBASE + CPrivilege::DELETE;

    const SCHEDULES_PERMISSIONSBASE = 1000;
    const SCHEDULES_ALL = self::SCHEDULES_PERMISSIONSBASE + CPrivilege::ALL;
    const SCHEDULES_READ = self::SCHEDULES_PERMISSIONSBASE + CPrivilege::READ;
    const SCHEDULES_WRITE = self::SCHEDULES_PERMISSIONSBASE + CPrivilege::WRITE;
    const SCHEDULES_CREATE = self::SCHEDULES_PERMISSIONSBASE + CPrivilege::CREATE;
    const SCHEDULES_DELETE = self::SCHEDULES_PERMISSIONSBASE + CPrivilege::DELETE;

    public static $moduleName;
    public static $modulePrefix;
    public static $moduleDataPath;

    public static $sqliteEncryption = "Thhh129a998";

    static function __setup() {
        self::$moduleName = "Base";
        self::$modulePrefix = Mt::tableName("");
        self::$moduleDataPath = Mt::dataPath("");
    }

    static function tableName($name) {
        return self::$modulePrefix . "$name";
    }

    static function moduleDataPath($name) {
        return self::$moduleDataPath . "/$name";
    }

    static function init($reset=false) {
        CEntities::init($reset);    //Entities are initialized here
        CForensic::init($reset);
        CPerson::init($reset);
        CUser::init($reset);
        CRole::init($reset);
        CSettings::init($reset);
    }

}

CEnterprisa::__setup();
