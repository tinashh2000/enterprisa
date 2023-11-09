<?php

namespace Admin;

use Admin\Companies\CCompany;
use Api\AppConfig;
use Api\CPrivilege;
use Api\Mt;

require_once(Mt::$appDir . "/Modules/Api/D.php");

use Admin\Customers\CCustomer;
use Admin\Clients\CClient;
use Admin\Suppliers\CSupplier;
use Admin\Invoices\CInvoice;
use Admin\Products\CBaseProduct;
use Admin\Taxes\CTax;
use Api\CImportMapping;
use Api\CDataFields;

class CAdmin {

    const BASIC_PERMISSION = CPrivilege::VIEW_USER;

    const COMPANIES_PERMISSIONSBASE =  modulePrivilegesBase + 200;
    const COMPANIES_ALL = self::COMPANIES_PERMISSIONSBASE + CPrivilege::ALL;
    const COMPANIES_READ = self::COMPANIES_PERMISSIONSBASE + CPrivilege::READ;
    const COMPANIES_WRITE = self::COMPANIES_PERMISSIONSBASE + CPrivilege::WRITE;
    const COMPANIES_CREATE = self::COMPANIES_PERMISSIONSBASE + CPrivilege::CREATE;
    const COMPANIES_DELETE = self::COMPANIES_PERMISSIONSBASE + CPrivilege::DELETE;


    const DATAFIELDS_PERMISSIONSBASE =  modulePrivilegesBase + 1000;
    const DATAFIELDS_ALL = self::DATAFIELDS_PERMISSIONSBASE + CPrivilege::ALL;
    const DATAFIELDS_READ = self::DATAFIELDS_PERMISSIONSBASE + CPrivilege::READ;
    const DATAFIELDS_WRITE = self::DATAFIELDS_PERMISSIONSBASE + CPrivilege::WRITE;
    const DATAFIELDS_CREATE = self::DATAFIELDS_PERMISSIONSBASE + CPrivilege::CREATE;
    const DATAFIELDS_DELETE = self::DATAFIELDS_PERMISSIONSBASE + CPrivilege::DELETE;


    const IMPORTMAPPING_PERMISSIONSBASE = modulePrivilegesBase + 2000;

    const IMPORTMAPPING_ALL = self::IMPORTMAPPING_PERMISSIONSBASE + CPrivilege::ALL;
    const IMPORTMAPPING_READ = self::IMPORTMAPPING_PERMISSIONSBASE + CPrivilege::READ;
    const IMPORTMAPPING_WRITE = self::IMPORTMAPPING_PERMISSIONSBASE + CPrivilege::WRITE;
    const IMPORTMAPPING_CREATE = self::IMPORTMAPPING_PERMISSIONSBASE + CPrivilege::CREATE;
    const IMPORTMAPPING_DELETE = self::IMPORTMAPPING_PERMISSIONSBASE + CPrivilege::DELETE;

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
        self::$moduleName = "Admin";
        self::$modulePrefix = Mt::tableName("Admin_");
        self::$moduleDataPath = Mt::dataPath("Admin");
    }

    static function init($reset=false) {
//        CCompany::init($reset);
        require_once("CDataFields.php");
        require_once("CImportMapping.php");

        CDataFields::init($reset);
        CImportMapping::init($reset);
    }
}

CAdmin::__setup();


