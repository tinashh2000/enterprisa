<?php


namespace Modules\Admin\Api\Classes;


use Api\AppDB;
use Api\Mt;
use Ffw\Status\CStatus;

class CBackup
{
    var $backupDB,
        $dbDropExistingTables,
        $dbCreateTables,
        $dbCreateTablesIfNotExist,
        $backupFiles,
        $filesDataOnly;
    var $dbTablesList = "*";

    function __construct($backupDB = true, $dbDropExistingTables = true, $dbCreateTables = true, $dbCreateIfNotExists = true, $backupFiles = true, $filesDataOnly = true)
    {
        $this->backupDB = $backupDB;
        $this->dbDropExistingTables = $dbDropExistingTables;
        $this->dbCreateTables = $dbCreateTables;
        $this->dbCreateIfNotExists = $dbCreateIfNotExists;
        $this->backupFiles = $backupFiles;
        $this->filesDataOnly = $filesDataOnly;
    }

    function backup()
    {

        $backUpDir = Mt::$appDir . "/Data/Backup";
        $dataDir = Mt::$appDir . "/Data";
        $dt = gmdate("Y-m-d H.i.s", strtotime("now"));

        $c = 0;
        while (file_exists($backUpDir . "/b$dt")) {
            $dt = gmdate("YmdHis", strtotime("now")) . "_" . $c;
            $c++;
        }

        $d = $backUpDir . "/b$dt/Data";
        if (!file_exists($d . "/Private")) mkdir($d . "/Private", 0777, true);
        if (!file_exists("$dataDir/Private")) mkdir("$dataDir/Private", 0777, true);

        $filename = "{$d}/db-backup-".time().'-'.(md5($dt . Mt::generateUid(mt_rand(8888,9999999)))).'.sql';
        if ($this->backupDBdb) {
            if (!$fName = AppDB::db()->backup($filename, $this->dbTablesList, $this->dbDropExistingTables, $this->dbCreateTables, $this->dbCreateTablesIfNotExist)) {
                return CStatus::jsonError("Error while backing up database");
            }
        }

        require_once(__DIR__ . "/../Procs/RecursiveCopy.php");

        if (is_dir($dataDir)) {
            recursiveCopy("$dataDir/Private", "$d");

            $nb = gmdate("Y-m-d H.i.s", strtotime("now"));
            $c = 0;
            while (file_exists("$dataDir/Private.$nb")) {
                $nb = gmdate("Y-m-d H.i.s", strtotime("now"));
                if (file_exists("$dataDir/Private.$nb")) {
                    $nb .= "_" . $c;
                    $c++;
                }
            }

            try {
                rename("$dataDir/Private", "$dataDir/Private.$nb");
            } catch (\Exception $e) {
                recursiveCopy("$dataDir/Private", "$dataDir/Private.$nb", true);
            }
        }
        CStatus::pushStatus("Backup Done");
    }
}