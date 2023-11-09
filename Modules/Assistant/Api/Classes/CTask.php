<?php
namespace Api;

use Api\AppConfig;
use Api\Users\CurrentUser;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;
use Api\Forensic\CForensic;
use Api\TaskClass;
//require_once ("TaskClass.php");
class CTask extends TaskClass {

    protected static $forensicPath = "PersonalTasks";

    static function __setup() {
        static::$defaultTable = CAssistant::tableName("PersonalTasks");
        static::$defaultPath = CAssistant::moduleDataPath("PersonalTasks");
        static::$moduleDescription = "Assistant\PersonalTask";
        static::$className = "CTask";
        static::$defaultRecordName = "task";
        static::$permissionsBase = 0;
        $currentUser = CurrentUser::getUsername();
        static::$defaultCondition = "creator='{$currentUser}' OR FIND_IN_SET('{$currentUser}', participants) > 0";
        parent::_setupComplete();
    }
}

CTask::__setup();
//CTask::init(true);