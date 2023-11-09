<?php


namespace Api;

use Entities\CEntities;
use Entities\CEntity;
use Entities\CEntityItem;
use Ffw\Status\CStatus;

class CPersonEntity extends CEntityItem
{
    static $entities = array("Categories", "Likes", "Types", "Professions");
    static function init($reset) {

        parent::init($reset);

        foreach(self::$entities as $entity) {
            self::createEntity($entity);
        }

        self::createType("User", CPerson::PERSON_TYPE_USER);
        self::createType("Contact", CPerson::PERSON_TYPE_CONTACT);
    }

    static function createType($title, $value) {
        return self::createItem("Types",$title, $value);
    }

    static function createCategory($title, $value) {
        return self::createItem("Categories", $title, $value);
    }

    static function createEntity($entity, $description="") {
        $e = new CEntity("Person.$entity", "Person", $entity, CEntity::TYPE_LIST, "", $description);
        $e->location = Mt::removePrefix(self::$defaultTable, Mt::tableName(""));
        return $e->exists() ? true : $e->create();
    }

    static function createItem($entity, $title, $value, $description = "") {
        if ($e = self::getItem($entity)) {
            if ($ei = new CPersonEntity($e['id'], "Person.$entity", $title, $value, $description)) {
                return $ei->exists() ? true : $ei->create();
            }else{
                echo "2";
            }
        }else{
            echo "1";
        }
        return false;
    }

    static function getItem($entity) {
        if ($e = CEntity::getEntity("Person.$entity", "Person", $entity)) {
            return CStatus::jsonSuccessItem($entity, $e);
        }
        return null;
    }

    static function getItems($entity) {
        if ($e = CEntity::getEntity("Person.$entity", "Person", $entity)) {
            $items = parent::getItems($e['id']);
            return CStatus::jsonSuccessItems("$entity", $items);
        }
        return null;
    }

    static function __setup() {
        self::$defaultTable = Mt::tableName("PersonEntities");
        self::$defaultPath = Mt::dataPath("PersonEntities");
        self::$moduleDescription = "CPersonEntity";
        self::$className = "CPersonEntity";
        self::$defaultRecordName = "personItem";
        self::$permissionsBase = 0;
        parent::_setupComplete();
    }
}

CPersonEntity::__setup();