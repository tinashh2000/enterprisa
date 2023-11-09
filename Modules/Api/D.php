<?php
	namespace Modules;
	require_once("Bootstrap.php");
	const parentPackage="Enterprisa Pro";
	const parentPath="/enterprisa";
	const parentCapabilities=1;
	const parentUiCapabilities=1;
	const modulePath="*";
	const MODULE_DIRECTORY = array(

	"Admin" => array("Name"=>"Administrator Panel", "Label"=>"Admin", "Description"=>"Administrative Panel","Location" =>"Admin", "PrivilegesBase" =>"20000", "AutoPrivilege" => true),

	"Assistant" => array("Name"=>"Assistant", "Label"=>"Assistant", "Description"=>"Assistant Package","Location" =>"Assistant", "PrivilegesBase" =>"30000", "AutoPrivilege" => false),

    "Currencies" => array("Name"=>"Currencies", "Label"=>"Currencies", "Description"=>"Currencies Package","Location" =>"Currencies", "PrivilegesBase" =>"50000", "AutoPrivilege" => true),

    "Entities" => array("Name"=>"Entities", "Label"=>"Entities", "Description"=>"Entities Package","Location" =>"Entities", "PrivilegesBase" =>"90000", "AutoPrivilege" => true),

    "TariAds" => array("Name"=>"TariAds", "Label"=>"TariAds", "Description"=>"Tari Ads Package","Location" =>"TariAds", "PrivilegesBase" =>"150000", "AutoPrivilege" => false),

);
