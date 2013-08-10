<?php
/****************************************************************************************
* LiveZilla index.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

define("IN_LIVEZILLA",true);
if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");
header("Content-Type: text/html; charset=UTF-8");

require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.protocol.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.index.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");

languageSelect();
@set_error_handler("handleError");
setDataProvider();
defineURL("index.php");
$scheme = getScheme();
$html = getFile(TEMPLATE_HTML_INDEX);
$errorbox = null;
$errors['write'] = getFolderPermissions();
$errors['php_version'] = getPhpVersion();
$errors['mysql'] = getMySQL();
$errors['disabled'] = getDisabledFunctions();

if(!empty($errors['write']) || !empty($errors['php_version']) || !empty($errors['mysql']) || !empty($errors['disabled']))
{
	$errorbox = getFile(TEMPLATE_HTML_INDEX_ERRORS);
	$errorbox = str_replace("<!--write_access-->",$errors['write'],$errorbox);
	if(strlen($errors['write']) > 0 && !empty($errors['php_version']))
		$errors['php_version'] = "<br><br>" . $errors['php_version'];
	if((strlen($errors['write']) > 0 || !empty($errors['php_version'])) && !empty($errors['mysql']))
		$errors['mysql'] = "<br><br>" . $errors['mysql'];
	if((strlen($errors['write']) > 0 || !empty($errors['php_version']) || !empty($errors['mysql'])) && !empty($errors['disabled']))
		$errors['disabled'] = "<br><br>" . $errors['disabled'];
	$errorbox = str_replace("<!--mysql-->",$errors['mysql'],$errorbox);
	$errorbox = str_replace("<!--php_version-->",$errors['php_version'],$errorbox);
	$errorbox = str_replace("<!--disabled-->",$errors['disabled'],$errorbox);
}

$html = str_replace("<!--index_errors-->",$errorbox,$html);
$html = str_replace("<!--height-->",$CONFIG["wcl_window_height"],$html);
$html = str_replace("<!--width-->",$CONFIG["wcl_window_width"],$html);
$html = str_replace("<!--lz_version-->",VERSION,$html);
$html = str_replace("<!--title-->",base64_decode($d[array_rand($d=array("TGl2ZVppbGxhIExpdmUgQ2hhdCBTb2Z0d2FyZQ==","TGl2ZVppbGxhIExpdmUgU3VwcG9ydCBTb2Z0d2FyZQ==","TGl2ZVppbGxhIExpdmUgQ2hhdCBTb2Z0d2FyZQ==","TGl2ZVppbGxhIExpdmUgSGVscCBTb2Z0d2FyZQ==","TGl2ZVppbGxhIExpdmUgQ2hhdCBTb2Z0d2FyZQ==","TGl2ZVppbGxhIEN1c3RvbWVyIFN1cHBvcnQ=","TGl2ZVppbGxhIE9ubGluZSBTdXBwb3J0","TGl2ZVppbGxhIExpdmUgQ2hhdCBTb2Z0d2FyZQ=="),1)]),$html);
echo(applyReplacements($html));

?>