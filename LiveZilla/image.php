<?php

/****************************************************************************************
* LiveZilla image.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors. 

* 
***************************************************************************************/ 

define("IN_LIVEZILLA",true);

if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");
	
@set_time_limit(30);

require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.protocol.inc.php");

@set_error_handler("handleError");
@error_reporting(E_ALL);

header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Keep-Alive: timeout=5, max=100");

setDataProvider();
$parameters = getTargetParameters();

$html = "";
if(!empty($_GET["id"]) && is_numeric($_GET["id"]))
{
	$prefix = ((!empty($_GET["type"]) && $_GET["type"] == "overlay") ? "overlay" : "inlay");
	if(operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"]) > 0)
		exit(getFileById($_GET["id"],true,$prefix));
	else
	{
		if(!empty($_GET["cboo"]))
		{
			header("Content-Type: image/gif;");
			exit(file_get_contents(PATH_IMAGES . "chat_blank.gif"));
		}
		else
			exit(getFileById($_GET["id"],false,$prefix));
	}
}
else if(!empty($_GET["tl"]) && !empty($_GET["srv"]))
{
	$html = "<a href=\\\"javascript:void(window.open('<!--server-->','','width=".$CONFIG["wcl_window_width"].",height=".$CONFIG["wcl_window_height"].",left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))\\\" <!--class-->><!--text--></a>";
	$html = str_replace("<!--server-->",htmlentities(base64UrlDecode($_GET["srv"]),ENT_QUOTES,"UTF-8"),$html);
	
	if(!empty($_GET["tlont"]) && operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"]) > 0)
	{
		if(!empty($_GET["tlonc"]))
			$html = str_replace("<!--class-->","class=\\\"".htmlentities(base64UrlDecode($_GET["tlonc"]),ENT_QUOTES,"UTF-8")."\\\"",$html);
		else
			$html = str_replace("<!--class-->","",$html);
		$html = processPlaceholders($html);
		$html = str_replace("<!--text-->",htmlentities(base64UrlDecode($_GET["tlont"]),ENT_QUOTES,"UTF-8"),$html);
	}
	else if(!empty($_GET["tloft"]) && empty($_GET["tloo"]))
	{
		if(!empty($_GET["tlofc"]))
			$html = str_replace("<!--class-->","class=\\\"".htmlentities(base64UrlDecode($_GET["tlofc"]),ENT_QUOTES,"UTF-8")."\\\"",$html);
		else
			$html = str_replace("<!--class-->","",$html);
		$html = processPlaceholders($html);
		$html = str_replace("<!--text-->",htmlentities(base64UrlDecode($_GET["tloft"]),ENT_QUOTES,"UTF-8"),$html);
	}
	else
		$html = "";
	if(!empty($html))
	{
		if(empty($_GET["xhtml"]))
			exit("document.write(\"".$html."\");");
		else
			exit("var newcontent = document.createElement('div');newcontent.style.padding='0px';newcontent.style.margin='0px';newcontent.innerHTML=\"".$html."\";var tlscr = document.getElementById('lz_textlink');tlscr.parentNode.insertBefore(newcontent, tlscr);");
	}
}

function processPlaceholders($html)
{
	$params = array(GET_EXTERN_USER_LANGUAGE,GET_EXTERN_USER_NAME,GET_EXTERN_USER_EMAIL,GET_EXTERN_USER_COMPANY,GET_TRACK_SPECIAL_AREA_CODE,GET_EXTERN_USER_QUESTION,GET_EXTERN_USER_HEADER);
	$placeholders = array("language","name","email","company","code","question","header_url");

	foreach($params as $key => $value)
	{
		if(!empty($_GET[$value]))
			$html = str_replace("&lt;!--replace_me_with_b64url_".$placeholders[$key]."--&gt;",base64UrlEncode(base64UrlDecode($_GET[$value])),$html);
		else
			$html = str_replace("&lt;!--replace_me_with_b64url_".$placeholders[$key]."--&gt;","",$html);
	}

	for($i=0;$i<10;$i++)
	{
		if(!empty($_GET["cf".$i]))
			$html = str_replace("&lt;!--replace_me_with_b64url_custom_".$i."--&gt;",base64UrlEncode(base64UrlDecode($_GET["cf".$i])),$html);
		else
			$html = str_replace("&lt;!--replace_me_with_b64url_custom_".$i."--&gt;","",$html);
	}
	return $html;
}

function getFileById($_id,$_online,$_type)
{
	$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_IMAGES."` WHERE `id`='".@mysql_real_escape_string($_id)."' AND `button_type`='".@mysql_real_escape_string($_type)."' AND `online`='".@mysql_real_escape_string(($_online) ? "1" : "0")."' LIMIT 1;");
	if($row = mysql_fetch_array($result, MYSQL_BOTH))
	{
		header("Content-Type: image/".$row["image_type"].";");
		return base64_decode($row["data"]);
	}
	else
	{
		header("Content-Type: image/gif;");
		return file_get_contents(PATH_IMAGES . "chat_blank.gif");
	}
}
unloadDataProvider();
?>