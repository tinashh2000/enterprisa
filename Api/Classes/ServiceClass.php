<?php

namespace Api;

use Cademia\CaDB;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Api\Forensic\CForensic;
use Api\CaServiceClass;

abstract class ServiceClass
{

    const FLAGS_REMOTE = 1 << 61;

    const MODIFY_DB = 1;
    const NO_JSON = 128;
    const STORE_FULL_PATH = 256;
    const STORE_RELATIVE_PATH = 512;
    const MAKE_THUMBNAIL = 1024;
    const NO_IMAGE_CONVERT = 2048;

    public static $searchItems = "*";
    public static $defaultCondition;
    public static $readPermissionCheck;

    abstract static function __setup(); //Sets up the class for use. It should set up the above variables

    abstract static function init($reset);  //Is used when initializing the module for the first time.

    abstract function sqlPrepare(); //Used to initialize and validate data before a database operation

    abstract function create(); //Creates a record

    abstract function edit(); //Edits an existing record

    //abstract static function search($query = "", $start = 0, $limit = 25);    //Search for matching records

    /*
     * Called after a class has finished initializing thru its __setup function
     */
    static function _setupComplete() {
        if (isset(static::$joinDBs)) {

            $numJoinDBs = count(static::$joinDBs);
            $search = [];

            for($c=1;$c<$numJoinDBs+1;$c++) {
                array_push($search, "%{$c}");
            }

            if (isset(static::$orderByParams)) {
                static::$orderByParams = str_replace($search, static::$joinDBs, static::$orderByParams);
            }

            if (isset(static::$searchParams)) {
                static::$searchParams = str_replace($search, static::$joinDBs, static::$searchParams);
            }

            if (isset(static::$joinElements)) {
                static::$joinElements= str_replace($search, static::$joinDBs, static::$joinElements);
            }

            if (isset(static::$joinItems)) {
                static::$joinItems = str_replace($search, static::$joinDBs, static::$joinItems);
            }
        }
    }

    /*
     * Set the starting number for the auto increment field in a table
     */
    static function setAutoIncrementStart($startingNumber = 1)
    {
        $startingNumber = CDecimal::integer($startingNumber);
        if ($startingNumber < 1) $startingNumber = 1;
        AppDB::query("ALTER TABLE " . static::$defaultTable . " AUTO_INCREMENT=$startingNumber;");
    }

    /*
     * Checks if a certain privilege required by a class is present
     */
    static function checkPrivilege($i)
    {
        if (static::$permissionsBase == 0) return true;
        if (CPrivilege::checkList(static::$permissionsBase + $i)) {
            return true;
        }

        return CStatus::jsonError("Access denied");
    }

    /*
     * Fetch records from a table
     */

    static function fetch($start = 0, $limit = 25)
    {    //Fetch for records
        if (!self::checkPrivilege(CPrivilege::READ)) return false;
        $start = intval($start);
        $limit = intval($limit);
        if ($res = AppDB::query("SELECT * FROM " . static::$defaultTable . " LIMIT $start, $limit")) {
            return CStatus::jsonSuccessDB(static::$defaultRecordName . "x", $res);
        }
        return CStatus::jsonError("Unexpected error!");
    }

    /*
     * Fetch records with left join
     */
    static function fetchN($start = 0, $limit = 25, $sortOrder = null, $orderBy = null, $query="", $groupBy=null)
    {   //Search records. Return also the total count of matching records


        if (isset(static::$joinElements) && static::$joinElements != "")
            return self::searchLJ($start, $limit, $sortOrder, $orderBy, $query, $groupBy);

        return self::searchX($start, $limit, $sortOrder, $orderBy, $query, $groupBy);
    }

    static function addConditionOr($cond) {
        if (!isset(static::$defaultCondition) || trim(static::$defaultCondition)=="" ) {
            static::$defaultCondition = $cond;
            return true;
        }

        static::$defaultCondition = "(" . static::$defaultCondition . ") OR ($cond)";
        return true;
    }

    static function addCondition($cond) {
        $op = "AND";    //Operation which joins existing and new conditions
        if (!isset(static::$defaultCondition) || trim(static::$defaultCondition)=="" ) {
            static::$defaultCondition = "";
            $op = "";   //Nothing should be joined since its the first condition
        } else
            static::$defaultCondition = "(" . static::$defaultCondition . ")";

        $nq = "";   //New query will be placed here

        if (is_string($cond)) { //If condition is a string
            $nq = $cond;    //Do this
        } else {    //Otherwise, new query is an array and we must run through the list
            foreach($cond as $c)  {
                if ($c != "")
                    $nq .= " AND " . $c; //Add each item
            }

            if ($nq != "") $nq = substr($nq, 4);
        }

        if ($nq != "") static::$defaultCondition .= " $op " . "($nq)";
        return true;
    }

    static function buildSearchQuery($query) {
        if (!isset(static::$searchParams) || !is_array(static::$searchParams) || $query=="") return "";
        $q = "";
        AppDB::ffwRealEscapeStringX($query);
        foreach (static::$searchParams as $p) {
            $q .= " OR $p LIKE '%$query%'";
        }
        return $q == "" ? "" : substr($q, 3);
    }

    static function fetchS($start = 0, $limit = 25, $sortOrder = null, $orderBy = null, $query="")
    {   //Search records. Return also the total count of matching records

        if (isset(static::$joinElements) && static:: $joinElements != "")
            return self::fetchSLJ($start, $limit, $sortOrder, $orderBy, $query);

        return self::fetchSX($start, $limit, $sortOrder, $orderBy, $query);
    }


    static function fetchSX($start = 0, $limit = 25, $sortOrder=null, $orderBy=null, $query = "") {
        if (!self::checkPrivilege(CPrivilege::READ)) return false;

        $table1 = static::$defaultTable;
        $cond = "";
        if ($query != "" && isset(static::$searchParams)) {
            $cond = self::buildSearchQuery($query);
        }

        if (isset(static::$defaultCondition) && static::$defaultCondition != "")
            $cond = ($cond == "") ? "(".static::$defaultCondition.")" : "(".static::$defaultCondition.") AND ($cond)";

//        $total = AppDB::count($table1, $cond);

        if ($orderBy == null)
            $orderBy = static::$searchParams[0];
        else {
            if (isset(static::$orderByParams) && isset(static::$orderByParams[$orderBy])) {
                $orderBy =  static::$orderByParams[$orderBy];
            }
        }

        if ($sortOrder == null) $sortOrder =  "ASC";
        $orderStatement = " ORDER BY $orderBy $sortOrder";

        $q = "SELECT " . static::$selectItems[0] . " AS id, " . static::$selectItems[1]. " AS name FROM " . static::$defaultTable . ($cond == "" ? "" : " WHERE $cond") . " $orderStatement LIMIT $start, $limit";

        if ($res = AppDB::query($q)) {
            echo '{"results": [';
            $cm = "";
            while ($item = AppDB::fetchAssoc($res))
            {
                echo $cm . "{\"id\":\"{$item['id']}\", \"text\":\"{$item['name']}\"}";
                $cm = ",";
            }
            echo ']}';
            die();
        }
    }

    static function fetchSLJ($start = 0, $limit = 25, $sortOrder=null, $orderBy=null, $query = "")
    {
        if (!self::checkPrivilege(CPrivilege::READ)) return false;

        $table1 = static::$joinDBs[0];
        $table2 = static::$joinDBs[1];
        $cond = "";
        if ($query != "") {
            $cond = self::buildSearchQuery($query);
        }

        if (isset(static::$defaultCondition) && static::$defaultCondition != "")
            $cond = ($cond == "") ? "(".static::$defaultCondition.")" : "(".static::$defaultCondition.") AND ($cond)";

        if ($orderBy == null)
            $orderBy = static::$searchParams[0];
        else {
            if (isset(static::$orderByParams) && isset(static::$orderByParams[$orderBy])) {
                $orderBy =  static::$orderByParams[$orderBy];
            }
        }
        if ($sortOrder == null) $sortOrder =  "ASC";
        $orderStatement = " ORDER BY $orderBy $sortOrder";

        $q = AppDB::leftJoinX($table1, $table2, static::$joinElements[0], static::$selectItems[0] . " AS id, " . static::$selectItems[1]. " AS name") . ($cond == "" ? "" : " WHERE $cond") . " $orderStatement LIMIT $start, $limit";

        if ($res = AppDB::query($q)) {
            echo '{"results": [';
            $cm = "";
            while ($item = AppDB::fetchAssoc($res))
            {
                echo $cm . "{\"id\":\"{$item['id']}\", \"text\":\"{$item['name']}\"}";
                $cm = ",";
            }
            echo ']}';
            die();
        }
    }

    static function canRead() {
        if (isset(static::$readPermissionCheck) && static::$readPermissionCheck > 0) {
            static::$readPermissionCheck--;
            return true;
        } else if (!self::checkPrivilege(CPrivilege::READ))
            return false;
    }

    /*
     * Fetch records with left join
     */
    static function searchLJ($start = 0, $limit = 25, $sortOrder=null, $orderBy=null, $query = "", $groupBy=null)
    {
        if (isset(static::$readPermissionCheck) && static::$readPermissionCheck > 0) {
            static::$readPermissionCheck--;
        } else if (!self::checkPrivilege(CPrivilege::READ))
            return false;

        $cond = "";
        if ($query != "" && isset(static::$searchParams)) {
            $cond = self::buildSearchQuery($query);
        }

//        if (isset(self::$defaultCondition) && self::$defaultCondition != "")
//            $cond = ($cond == "") ? "(".self::$defaultCondition.")" : "(".self::$defaultCondition.") AND ($cond)";

       if (isset(static::$defaultCondition) && static::$defaultCondition != "")
            $cond = ($cond == "") ? "(".static::$defaultCondition.")" : "(".static::$defaultCondition.") AND ($cond)";

        $q = AppDB::constructLeftJoin(static::$joinDBs, static::$joinElements, "COUNT(*) AS count"). ($cond == "" ? "" : " WHERE $cond");
        if ($q == "") return CStatus::jsonError("Class structure error on " . static::$className . ". Contact your administrator" );

        $total = AppDB::countX($q);

        if ($orderBy == null)
            $orderBy = static::$orderByParams[0];
        else {
            if (isset(static::$orderByParams) && isset(static::$orderByParams[$orderBy])) {
                $orderBy =  static::$orderByParams[$orderBy];
            }
        }
        if ($sortOrder == null) $sortOrder =  "ASC";

        $orderStatement = "";
        if ($groupBy != null) {
            $orderStatement .= " GROUP BY " . AppDB::ffwRealEscapeString($groupBy);
        }

        $orderStatement .= " ORDER BY $orderBy $sortOrder";

        $q = AppDB::constructLeftJoin(static::$joinDBs, static::$joinElements, static::$joinItems) . ($cond == "" ? "" : " WHERE $cond") . " $orderStatement LIMIT $start, $limit";
        if ($q == "") return CStatus::jsonError("Class structure error. Contact your administrator");

        if ($res = AppDB::query($q)) {
            return CStatus::jsonSuccessDB("rows", $res, $total);
        }
    }

    static function searchX($start = 0, $limit = 25, $sortOrder=null, $orderBy=null, $query = "", $groupBy=null)
    {

        if (isset(static::$readPermissionCheck) && static::$readPermissionCheck > 0) {
            static::$readPermissionCheck--;
        } else if (!self::checkPrivilege(CPrivilege::READ))
            return false;

        $table1 = static::$defaultTable;
        $cond = "";
        if ($query != "" && isset(static::$searchParams)) {
            foreach (static::$searchParams as $k => $s) {
                $cond .= " OR $s LIKE '%$query%'";
            }
            if ($cond != "") $cond = substr($cond, 3);
        }

        if (isset(static::$defaultCondition) && static::$defaultCondition != "")
            $cond = ($cond == "") ? "(".static::$defaultCondition.")" : "(".static::$defaultCondition.") AND $cond";

        $total = AppDB::count($table1, $cond);

        if ($orderBy == null  && isset(static::$searchParams) && count(static::$searchParams) > 0)
            $orderBy = static::$searchParams[0];
        else {
            if (isset(static::$orderByParams) && isset(static::$orderByParams[$orderBy])) {
                $orderBy =  static::$orderByParams[$orderBy];
            }
        }

        $orderStatement = "";
        if ($groupBy != null) {
            $orderStatement .= " GROUP BY " . AppDB::ffwRealEscapeString($groupBy);
        }

        if ($orderBy != null) {
            if ($sortOrder == null) $sortOrder = "ASC";
            $orderStatement .= " ORDER BY $orderBy $sortOrder";
        }

        $q = "SELECT " . static::$searchItems . " FROM " . static::$defaultTable . ($cond == "" ? "" : " WHERE $cond") . " $orderStatement LIMIT $start, $limit";
        if ($res = AppDB::query($q)) {
            return CStatus::jsonSuccessDB("rows", $res, $total);
        }
    }

    static function get($id, $condition = "")
    {  //Get one record based on id
        if (!self::checkPrivilege(CPrivilege::READ)) return false;

        $cond = static::$defaultTable . ".id='$id'";
        if (isset(static::$defaultCondition) && trim(static::$defaultCondition) != "")
            $cond = "(".static::$defaultCondition.") AND ($cond)";

        if ($condition != "")
            $cond .= " AND ($condition)";

        AppDB::ffwRealEscapeStringX($id);

        if (isset(static::$joinDBs))
            $q = AppDB::constructLeftJoin(static::$joinDBs, static::$joinElements, static::$joinItems);
        else
            $q = "SELECT * FROM " . static::$defaultTable;

        if ($q != "") {
            $q .= " WHERE $cond LIMIT 1";
            if ($res = AppDB::query($q)) {
                return CStatus::jsonSuccessItem(static::$defaultRecordName, $res);
            }
        }
        return CStatus::jsonError();
    }

    /*
     * Delete item by condition. This should only be used for records that rely on a certain condition instead of a certain permission
     * For example, a user is allowed to access and delete his messages only. That should be by the condition and not by a specific permission
     */
    static function getC($id, $cond)
    {  //Get one record based on id

        AppDB::ffwRealEscapeStringX($id);
        $q = "SELECT * FROM " . static::$defaultTable . " WHERE id='$id' AND ($cond) LIMIT 1";
        if ($res = AppDB::query($q)) {
            return CStatus::jsonSuccessItem(static::$defaultRecordName, $res);
        }
        return CStatus::jsonError();
    }

    /*
     * Delete item by privilege
     */
    static function delete($id)
    {   //Delete a record based on id
        if (!self::checkPrivilege(CPrivilege::DELETE)) return false;

        try {
            AppDB::beginTransaction();
            $id = CDecimal::integer64($id);

            CStatus::pushSettings(0);
            $item = self::get($id);
            CStatus::popSettings();

            if (!$item || $item['id'] != $id)
                throw new \Exception("Delete error");

            self::forensicLog("Delete item", json_encode($item), $id);
            self::forensicDelete($id, json_encode($item));
            $q = "DELETE FROM " . static::$defaultTable . " WHERE id='$id' LIMIT 1;";
            if (AppDB::query($q)) {
                AppDB::commit();
                return CStatus::jsonSuccess(static::$defaultRecordName . " deleted successfully!@");
            }
            self::forensicLog("Deletion failed", $id);
            AppDB::rollback();
            return CStatus::jsonError();
        } catch (\Exception $e) {
            AppDB::rollback();
            return CStatus::jsonError($e->getMessage());
        }
        return 0;
    }

    /*
     * Delete item by condition. This should only be used for records that rely on a certain condition instead of a certain permission
     * For example, a user is allowed to access and delete his messages only. That should be by the condition and not by a specific permission
     */

    static function forensicLog($description, $details, $itemId="")
    {
        CForensic::log(static::$forensicPath ?? static::$moduleName, $description, $itemId, $details);
    }

    /*
     * Get the total number of records in a table
     */

    static function count($condition="")
    {   //Get number of records
        if (!self::checkPrivilege(CPrivilege::READ)) return false;
        $cond = "";
        if (isset(static::$defaultCondition) && trim(static::$defaultCondition) != "")
            $cond .= "(".static::$defaultCondition.")";

        if ($condition != "")
            $cond .= ($cond != "" ? " AND " : "")  .  " ($condition)";

        AppDB::ffwRealEscapeStringX($id);

        if (isset(static::$joinDBs)) {
            $q = AppDB::constructLeftJoin(static::$joinDBs, static::$joinElements, "COUNT(*) as count");
        }
        else {
            $q = "SELECT COUNT(*) as count FROM " . static::$defaultTable;
        }

        if ($q != "" && $cond != "") {
            $q .= " WHERE $cond ";
        }

        if ($res = AppDB::query($q)) {
            if ($item = AppDB::fetchAssoc($res)) {
                return $item['count'];
            }
        }
        return CStatus::jsonError();
    }

    function uploadFile($flags, $directory, $filename = '', $fileVar = 'file') {
        $curDir = $directory;
        if (isset($_FILES[$fileVar]['name']) && isset($_FILES[$fileVar]['error']) && $_FILES[$fileVar]['error'] == 0) {
            $curDir = $curDir == "" ? "" : "/$curDir";
            $absFileDir = static::$defaultPath . "{$curDir}";  //Directory. Slash added above
            if (!file_exists($absFileDir)) @mkdir($absFileDir, 0777, true);
            require_once("UploadFile.php");
            $userFile = uploadFile($absFileDir, "", 536870912, $fileVar);

            if ($userFile) {
                $pi = pathinfo($userFile);
                $newFileName = $absFileDir . "/" . $filename;

                if (file_exists($newFileName)) {    //Thumbnail must be renamed as well
                    $renFile = $newFileName . "." . time(). ".bak";
                    if (file_exists($renFile)) {
                        unlink($renFile);
                    }
                    rename($newFileName, $renFile);
                }
                rename(static::$defaultPath . "/$curDir/$userFile", $newFileName);
                $ffName = ($flags & self::STORE_FULL_PATH) ? $newFileName :
                    (($flags & self::STORE_RELATIVE_PATH) ? Mt::removePrefix($newFileName, static::$defaultPath) : $filename);

                if (!($flags & self::NO_JSON)) {
                    $files = array("file" => $ffName);
                    $val = json_encode($files);
                } else {
                    $val = $ffName;
                }

                if ($flags & self::MODIFY_DB) {
                    $name = isset($this->file) ? "file" : "files";
                    if (isset($this->file))
                        $this->file = $val;
                    else
                        $this->files = $val;

                    AppDB::query("UPDATE " . static::$defaultTable . " SET `$name` = '$val' WHERE id='$this->id'");
                }
                return $newFileName;
            }
        }
    }

    /*
     * Upload a picture
     */
    function uploadPicture($flags, $directory, $filename = '', $fileVar = 'profilePic')
    {
        $curDir = $directory;
        if (isset($_FILES[$fileVar]['name']) && isset($_FILES[$fileVar]['error']) && $_FILES[$fileVar]['error'] == 0) {
            $curDir = $curDir == "" ? "" : "/$curDir";
            $absImgDir = static::$defaultPath . "{$curDir}";  //Directory. Slash added above
            if (!file_exists($absImgDir)) mkdir($absImgDir, 0777, true);
            require_once("UploadFile.php");
            $result = uploadPic($absImgDir, "", 700000, $fileVar, $flags & (self::MAKE_THUMBNAIL | self::NO_IMAGE_CONVERT));
            if ($result && isset($result["imagePath"])) {
                $userPic = $result["imagePath"];
                $pi = pathinfo($userPic);
                $fExt = ".jpg";
                if (isset($pi['extension'])) $fExt = "." . $pi['extension'];
                $newFileName = $absImgDir . "/" . $filename . $fExt;

                if (file_exists($newFileName)) {    //Thumbnail must be renamed as well
                    $renFile = $newFileName . "." . time(). ".bak";
                    if (file_exists($renFile)) {
                        unlink($renFile);
                    }
                    rename($newFileName, $renFile);
                }
                rename(static::$defaultPath . "/$curDir/$userPic", $newFileName);
                $ffName = ($flags & self::STORE_FULL_PATH) ? $newFileName :
                    (($flags & self::STORE_RELATIVE_PATH) ? Mt::removePrefix($newFileName, static::$defaultPath) : $filename . $fExt);

                if (!($flags & self::NO_JSON)) {
                    $pics = array("profile" => $ffName);
                    $val = json_encode($pics);
                } else {
                    $val = $ffName;
                }

                if ($flags & self::MODIFY_DB) {
                    $name = isset($this->pic) ? "pic" : "pics";
                    if (isset($this->pic))
                        $this->pic = $val;
                    else
                        $this->pics = $val;

                    AppDB::query("UPDATE " . static::$defaultTable . " SET `$name` = '$val' WHERE id='$this->id'");
                }
                return $newFileName;
            }
        }
    }
}
