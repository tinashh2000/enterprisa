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

namespace Ffw\Database\Sql;

use Ffw\Database\MySqli;
use Ffw\Database\MySqli\MySqliInstance;
//use Ffw\Database\MySqli;

require_once("Sqli.php");

class SqlConfig {

	protected $database;
	protected $server;
	protected $databaseName;

	function __construct($databaseName, $hostName, $username, $password) {
		if ( isset($_POST['app']) ||  isset($_POST['fromApp'])) {
			$sql = new MySqliInstance(true,false);
		}
		else {
			$sql = new MySqliInstance(true,true);
		}

		$this->server 	= $sql->addServer($hostName, $username, $password);
		$this->database = $this->server->addDB($databaseName);

		$this->databaseName = $databaseName;
	}

	function server() {
		return $this->server;
	}

	function database() {
		return $this->database;
	}

	function databaseName() {
		return $this->databaseName;
	}
}


