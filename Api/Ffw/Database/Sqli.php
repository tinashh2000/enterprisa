<?php

/***************************************************************************************
* Copyright (C) 2020 Tinashe Mutandagayi                                               *
*                                                                                      *
* This file is part of the PayrollPro source code. The author(s) of this file     *
* is/are not liable for any damages, loss or loss of information, deaths, sicknesses   *
* or other bad things resulting from use of this file or software, either direct or    *
* indirect.                                                                            *
* Terms and conditions for use and distribution can be found in the license file named *
* LICENSE.TXT. If you distribute this file or continue using it,                       *
* it means you understand and agree with the terms and conditions in the license file. *
* binding this file.                                                                   *
*                                                                                      *
* Happy Coding :)                                                                      *
****************************************************************************************/

namespace Ffw\Database\MySqli;

use Api\AppConfig;
use Api\Mt;

class MySqliServer {
	var $servername;
	var $username;
	var $password;
	var $dbs;
	var $defaultdb;
	var $handle;
	var $baseclass;

	function __construct($servername,$username,$password,&$baseclass)
	{
		if (isset($servername) && $servername) $this->servername = $servername;
		if (isset($username) && $username) $this->username = $username;
		if (isset($password) && $password) $this->password = $password;
		$this->dbs = array();
		$this->defaultdb = -1;
		$this->baseclass = $baseclass;
	}

	function addDB($dbname)
	{
		if (($idx = $this->findDB($dbname)) != -1) return $this->dbs[$idx];
		$idx = count($this->dbs);
		$this->dbs[$idx] = new MySqliDatabase($dbname,$this);

		return $this->dbs[$idx];
	}

	function createDB($dbName) {
		$this->query("CREATE DATABASE IF NOT EXISTS $dbName");
	}

	function findDB($dbName)
	{
		for ($c=0;$c<count($this->dbs);$c++)
		{
			if (strcasecmp($this->dbs[$c]->name,$dbName) == 0) return $c;
		}
		return -1;
	}

	function dbExists($dbName)
	{
		$dbName = fosRealEscapeString($dbName);
		$q = "SHOW DATABASES LIKE '$dbName'";
		if ($res = $this->query($q))
		{
			return (mysqli_num_rows($res) > 0);
		}
		return false;
	}

	function connect()
	{
		try {
			if ($this->handle)
				return $this->handle;
			return $this->handle = mysqli_connect($this->servername, $this->username, $this->password);
		} catch (\Exception $e) {
//			if ($this->baseclass->errorreport) $this->baseclass->error($this->servername,$e->getMessage(),"connect()","");
		}
		return false;
	}

	function query($q)
	{
		if ($res = $this->connect())
		{
			$res  = mysqli_query($this->handle,$q);
			$pr = "Query : $q, Result : , Error :" . mysqli_error($this->handle);

			if (!$res) {
				if ($this->baseclass->debug) $this->baseclass->log($this->servername, "NO DB SELECTED", "mysqli_query", $pr);
				if ($this->baseclass->errorreport && !$res) $this->baseclass->error($this->servername, "NO DB SELECTED", "mysqli_query", $pr);
			}
			return $res;
		}

		$pr = "Result : $res, Error :" . mysqli_error($this->handle);
		if ($this->baseclass->errorreport) $this->baseclass->error($this->servername,"NO DB SELECTED","connect()",$pr);
		return 0;
	}

	function multiquery($q)
	{
		if ($res = $this->connect())
		{
			$res  = mysqli_multi_query($this->handle,$q);
			$pr = "Query : $q, Result : , Error :" . mysqli_error($this->handle);

			if ($this->baseclass->debug) $this->baseclass->log($this->servername,"NO DB SELECTED","mysqli_query",$pr );
			if ($this->baseclass->errorreport && !$res) $this->baseclass->error($this->servername,"NO DB SELECTED","mysqli_query",$pr);
			return $res;
		}

		$pr = "Result : $res, Error :" . mysqli_error($this->handle);
		if ($this->baseclass->errorreport) $this->baseclass->error($this->servername,"NO DB SELECTED","connect()",$pr);
		return 0;
	}

	function sqlRealEscapeString($str) {
		if ($res = $this->connect())
			return mysqli_real_escape_string($this->handle, $str);
	}


	function fetchArray($ar) {return mysqli_fetch_array($ar);}
	function fetchAssoc($ar) {return mysqli_fetch_assoc($ar);}
	function fetchRow($ar) {return mysqli_fetch_row($ar);}
	function numRows($ar) {return mysqli_num_rows($ar);}
}

class MySqliDatabase {
	public $server;
	var $name;
	var $baseclass;
	var $error;

	function __construct($name,&$server)
	{
		$this->name = $name;
		$this->server = $server;
		$this->baseclass = $server->baseclass;
		$this->error = "";
	}

	function changeDB($dbname)
	{
		$this->name = $dbname;
	}

	function addTable($tbl,$params)
	{
		return ($r = $this->query($q));
	}

	function tableExists($tbl)
	{
		$tbl = fosRealEscapeString($tbl);
		$q = "DESCRIBE $tbl";
		return ($this->query($q));
	}

	function checkTableExists($tbl) {
		$tbl = fosRealEscapeString($tbl);
		$q = "SHOW TABLES FROM " . $this->name . "LIKE '$tbl'";
		return ($this->numRows($this->query($q))) ;
	}

	function insertToTable($tbl,$params)
	{
		$q = "INSERT INTO $tblname SET $params";
		return ($r = $this->query($q));
	}

	function deleteFromTable($tbl,$condition)
	{
		if ($condition !="") $condition = "WHERE $condition";
		$q = "DELETE FROM $tbl $condition";
		return ($r = $this->query($q));	
	}

	function sqlRealEscapeString($strx) {

		if ($this->select()) {
			return mysqli_real_escape_string($this->server->handle,$strx);
		}
		return $strx;
	}

	function hasSpecialSQLChars($st)
	{
		$ar = array("/","\\","'",'"',">","<","=","%");
		return ($st != str_replace($ar,"_",$st));
	}

	function createDB($reset=false) {
		if (!$this->server->handle) $this->server->connect();
		if (!$this->server->handle) return 0;
		return mysqli_query($this->server->handle, ($reset ? "DROP DATABASE IF EXISTS {$this->name};" : "") . "CREATE DATABASE IF NOT EXISTS {$this->name}");
	}

	function select()
	{
		try {
			if (!$this->server->handle) $this->server->connect();
			if (!$this->server->handle) return 0;
			return mysqli_select_db($this->server->handle, $this->name);
		} catch(\Exception $e) {
		}
		return false;
	}

	function getLastError() {
		return $this->error;
	}

	function last_id() {
		return mysqli_insert_id($this->server->handle);
	}

	function query($q)
	{
		$pr = "";
		if ($res = $this->select())
		{
			try {

				$res = mysqli_query($this->server->handle, $q);
				$this->error = mysqli_error($this->server->handle);

				$pr = "Query : $q, Result :, Error :" . mysqli_error($this->server->handle) . "....";
				if ($this->baseclass->debug) $this->baseclass->log($this->server->servername, $this->name, "mysqli_query", $pr);
				if ($this->baseclass->errorreport && !$res) $this->baseclass->error($this->server->servername, $this->name, "mysqli_query", $pr);
				return $res;
			} catch(\Exception $e) {

				$pr = "Query : $q, Result : " . ($res == null ? "Error" : "OK") . ", Error :" . mysqli_error($this->server->handle) . "...." . $e->getMessage() . $e->getTraceAsString();
				if ($this->baseclass->errorreport) $this->baseclass->error($this->server->servername,$this->name,"select()",$pr);
				return 0;
			}
		}

		$pr = "Result :  " . ($res == null ? "Error" : "OK") . ", Error :" . ($this->server->handle != null ? mysqli_error($this->server->handle) : "No handle");
		if ($this->baseclass->errorreport) $this->baseclass->error($this->server->servername,$this->name,"select()",$pr);
		return 0;
	}

	function beginTransaction() {
		if ($res = $this->select())
		{
			mysqli_autocommit($this->server->handle, TRUE);
			$res = mysqli_begin_transaction($this->server->handle, MYSQLI_TRANS_START_READ_WRITE);
			return $res;
		}
	}

	function commit() {
		return mysqli_commit($this->server->handle);
	}

	function rollback() {
		return mysqli_rollback($this->server->handle);
	}


	function multiquery($q)
	{
		if ($res = $this->select())
		{
			$res  = mysqli_multi_query($this->server->handle, $q);
			$this->error = mysqli_error($this->server->handle);
			$pr = "Query : $q, Result :, Error :" . mysqli_error($this->server->handle);
			if ($this->baseclass->debug) $this->baseclass->log($this->server->servername,$this->name,"mysqli_query",$pr );
			if ($this->baseclass->errorreport && !$res) $this->baseclass->error($this->server->servername,$this->name,"mysqli_query",$pr);
			return $res;
		}

		$pr = "Result : $res, Error :" . mysqli_error($this->server->handle);
		if ($this->baseclass->errorreport) $this->baseclass->error($this->server->servername,$this->name,"select()",$pr);
		return 0;
	}

	function fetchArray($ar) {return mysqli_fetch_array($ar);}
	function fetchAssoc($ar) {return mysqli_fetch_assoc($ar);}
	function fetchRow($ar) {return mysqli_fetch_row($ar);}
	function numRows($ar) {return mysqli_num_rows($ar);}
	function affectedRows() {return mysqli_affected_rows($this->server->handle);}
	function GetNumRows($tbl) {
		$q = "SELECT COUNT(*) FROM $tbl";
		if ($res = $this->query($q)) {
			if ($rows = $this->fetchRow($res)) {
				return $rows[0];
			}
		}
		return 0;
	}

	function ffwRealEscapeString($strx) {
		if ($strx == null) return $strx;

		if ($this->select()) {
			return mysqli_real_escape_string($this->server->handle, $strx);
		}
		return $strx;
	}

	function ffwRealEscapeStringX(&$strx) {
		if ($strx == null) return $strx;

		try {
			if ($this->select()) {
				$strx = mysqli_real_escape_string($this->server->handle, $strx);
			}
			return $strx;
		} catch(\Exception $e) {

		}

		return $strx;
	}

	//Core function
	function backup($filename='', $tables = '*', $dropTables = true, $createNewTables=true, $createTablesIfNotExist=true) {
		if (!$this->select())
			return false;

		$link = $this->server->handle;

		mysqli_query($link, "SET NAMES 'utf8'");

		//get all of the tables
		if($tables == '*')
		{
			$tables = array();
			$result = mysqli_query($link, 'SHOW TABLES');
			while($row = mysqli_fetch_row($result))
			{
				$tables[] = $row[0];
			}
		}
		else
		{
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}

		$return = '';
		//cycle through
		foreach($tables as $table)
		{
			$result = mysqli_query($link, 'SELECT * FROM '.$table);
			$num_fields = mysqli_num_fields($result);
			$num_rows = mysqli_num_rows($result);

			if ($dropTables) $return.= 'DROP TABLE IF EXISTS '.$table.';';

			if ($createNewTables) {
				$row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE ' . $table));
				$return .= "\n\n" . $row2[1] . ";\n\n";
			} else if ($createTablesIfNotExist) {
				$row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE ' . $table));
				$return .= "\n\n" . $row2[1] . ";\n\n";
			}

			$counter = 1;

			//Over tables
			for ($i = 0; $i < $num_fields; $i++)
			{   //Over rows
				while($row = mysqli_fetch_row($result))
				{
					if($counter == 1){
						$return.= 'INSERT INTO '.$table.' VALUES(';
					} else{
						$return.= '(';
					}

					//Over fields
					for($j=0; $j<$num_fields; $j++)
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}

					if($num_rows == $counter){
						$return.= ");\n";
					} else{
						$return.= "),\n";
					}
					++$counter;
				}
			}
			$return.="\n\n\n";
		}

		//save file
		if ($filename =='') $filename = 'db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql';
		$handle = fopen($filename,'w+');
		fwrite($handle,$return);
		if(fclose($handle)){
			return $filename;
		}
	}
}

class MySqliServerDatabase {
	var $server;
	var $db;
	function __construct($server,$db)
	{
		$this->server=$server;
		$this->db=$db;
	}
}

class MySqliInstance {
	var $servers;
	var $defaultserver;
	var $debug;
	var $errorreport;

	function __construct($debug,$errorreport)
	{
 		$this->servers = array();
		$this->debug=$debug;
		$this->errorreport=$errorreport;
 	}

	function addServer($servername,$username,$password)
	{
		if (($c = $this->findServer($servername)) != -1)
			return $this->servers[$c];
		else
		{
			$idx = count($this->servers);
			$this->servers[$idx] = new MySqliServer($servername,$username,$password,$this);
			return $this->servers[$idx];
		}
	}

	function setDefaultServer($servername)
	{
		$this->defaultserver = $this->findServer($servername);
	}

	function findServer($servername)
	{
		for ($c=0;$c<count($this->servers);$c++)
		{
			if (strcasecmp($this->servers[$c]->servername,$servername) == 0)
			{
				return $c;
			}
		}
		return -1;
	}

	function findDB($servername,$dbName)
	{
		if (isset($servername) && $servername)
			$sidx = $this->findServer($servername);
		else
			$sidx = $this->defaultserver;

		if ($sidx == -1) return -1;

		$idx = $this->servers[$sidx]->findDB($dbName);
		if ($idx != -1) return new MySqliServerDatabase($sidx,$idx);

		if (!isset($servername) || !$servername)
		{
			for ($sidx =0;$sidx < count($this->servers);$sidx++)
			{
				if ($sidx == $this->defaultserver) continue;
				$idx = $this->servers[$sidx]->findDB($dbName);
				if ($idx != -1) return new MySqliServerDatabase($sidx,$idx);
			}
		}
		return -1;
	}

	function sqlError($fn="")
	{
		return " $fn ". mysqli_error();
	}

	function mDBSelect($dbH,$dbName)
	{
		if (!$dbH)
		{
			$idx = $this->findDB($dbName);
			if ($idx = -1) return 0;
			$dbH = mysqli_connect($this->servers[$idx]->servername, $this->servers[$idx]->username, $this->servers[$idx]->password);
			if (!$dbH) return 0;
		}
		if ($dbName) {mysqli_select_db($dbH, $dbName) or die(sqlError('mDBSelect') );}
	}

	function mDBConnect($dbName,$server)
	{
		$res = $this->findDB($dbName,$server);
		if ($res == -1) return 0;
		$srv = $this->servers[$res->server];
		$db = $this->servers[$res->server]->dbs[$res->db];
		$dbHandle = mysqli_connect($srv->servername,$srv->username,$srv->password);
		if (!$dbHandle)	die(sqlError('Connect->Connect'));
		if (isset($dbName)) {mysqli_select_db($dbHandle,$dbName) or die(sqlError('Connect->SelectDB') );}
		return $dbHandle;
	}

	function error($server,$database,$function,$parameters)
	{
		$pr = "Error Report<br>\r\n============================<br>\r\nServer :$server<br>\r\nDatabase :$database<br>\r\nFunction :$function<br>\r\n\r\nParameters: $parameters<br>\r\n\r\n";
		echo $pr;
		$hf = fopen(Mt::$dataPath . "/Sqli/sqlr.php","a+");
		fwrite($hf,$pr);
		fclose($hf);
	}
	
	function log($server,$database,$function,$parameters)
	{
		if (!is_dir(Mt::$dataPath ."/Sqli"))
			@mkdir(Mt::$dataPath ."/Sqli");

		$hf = fopen(Mt::$dataPath . "/Sqli/sqlr.php","a+");
		$date = gmdate("Y m d H:i:s");
		$pr = "Log\r\n===============================\r\nFile: {$_SERVER['REQUEST_URI']}\r\nDate:$date\r\nServer :$server\r\nDatabase :$database\r\nFunction :$function\r\n\r\nParameters: $parameters\r\n\r\n";
		fwrite($hf,$pr);
		fclose($hf);
	}
}

?>
