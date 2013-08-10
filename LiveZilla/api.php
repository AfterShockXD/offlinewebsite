<?php
/****************************************************************************************
* LiveZilla api.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

define("IN_LIVEZILLA",true);
if(!defined("LIVEZILLA_PATH"))
	exit("Error: 'LIVEZILLA_PATH' is not defined. Please define the constant 'LIVEZILLA_PATH'.");

class LiveZillaAPI
{
	function LiveZillaAPI()
	{
		global $CONFIG;

		require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
		require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
		require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
		require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");

		if(!setDataProvider())
			exit("Error: Database connection failed.");
			
		@register_shutdown_function('unloadDataProvider');
	}
	
	function IsOperatorAvailable()
	{
		return (operatorsAvailable() > 0);
	}
	
	function IsDeactivated()
	{
		return !getAvailability();
	}
	
	/** DEPRECATED **/
	function GetOperatorList()
	{
		return getOperatorList();
	}
	
	function GetOperators()
	{
		return getOperators();
	}
	
	function Base64UrlEncode($_value)
	{
		return base64UrlEncode($_value);
	}
	
	function CreateOperator($_loginId,$_fullName,$_email,$_permissions,$_webspace,$_passwordMD5,$_administrator,$_groups,$_language)
	{
		$operator = new Operator(getId(USER_ID_LENGTH),$_loginId);
		$operator->Fullname = $_fullName;
		$operator->Email = $_email;
		$operator->PermissionSet = $_permissions;
		$operator->Webspace = $_webspace;
		$operator->Password = $_passwordMD5;
		$operator->Level = $_administrator ? 1 : 0;
		$operator->Groups = $_groups;
		$operator->Language = $_language;
		$operator->Save(true);
		return $operator;
	}
	
	function DeleteOperator($_loginId)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATORS."` WHERE `id`='".@mysql_real_escape_string($_loginId)."' LIMIT 1;");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_PREDEFINED."` WHERE `internal_id`='".@mysql_real_escape_string($_groupId)."';");
	}
	
	function CreateGroup($_groupId,$_titles,$_internal,$_email,$_visitorFilters=null,$_openingHours=null,$_chatFunctions="111101")
	{
		$group = new UserGroup();
		$group->Id = $_groupId;
		$group->Descriptions = $_titles;
		$group->Created = time();
		$group->IsExternal = true;
		$group->IsInternal = !empty($_internal);
		$group->Email = $_email;
		
		if(!empty($_visitorFilters))
			$group->VisitorFilters = $_visitorFilters;
		else
			$group->VisitorFilters = array();

		if(!empty($_openingHours))
			$group->OpeningHours = $_openingHours;
		else
			$group->OpeningHours = array();
			
		$group->ChatFunctions = $_chatFunctions;
		$group->Save();
		return $group;
	}
	
	function DeleteGroup($_groupId)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_GROUPS."` WHERE `id`='".@mysql_real_escape_string($_groupId)."' LIMIT 1;");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_PREDEFINED."` WHERE `group_id`='".@mysql_real_escape_string($_groupId)."';");
	}
	
	function AddPredefinedMessageSet($_userSystemId,$_groupId,$_langISO,$_standard,$_chatInviteManual,$_chatInviteAuto,$_welcomeMessage,$_webSitePushManual,$_webSitePushAuto,$_chatTranscriptMail,$_ticketMail)
	{
		$result = queryDB(true,"SELECT MAX(`id`) AS `pcount` FROM `".DB_PREFIX.DATABASE_PREDEFINED."`;");
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$pdm = new PredefinedMessage($row["pcount"]+1,$_userSystemId, $_groupId, $_langISO, $_chatInviteManual, $_chatInviteAuto, $_welcomeMessage, $_webSitePushManual, $_webSitePushAuto,'', '', '1', (($_standard) ? '1' : '0'), '1', '1',$_chatTranscriptMail,$_ticketMail,'','','');
				$pdm->Save(DB_PREFIX);
				return $pdm;
			}
		return false;
	}
	
	public function GetChatBillingTypes()
	{
		global $CONFIG;
		return $CONFIG["db"]["cct"];
	}
}
?>