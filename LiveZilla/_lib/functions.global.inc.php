<?php

/****************************************************************************************
* LiveZilla functions.global.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function defineURL($_file)
{
	global $CONFIG;
	if(!empty($_SERVER['REQUEST_URI']) && !empty($_CONFIG["gl_root"]))
	{
		$parts = parse_url($_SERVER['REQUEST_URI']);
		define("LIVEZILLA_URL",getScheme() . $CONFIG["gl_host"] . str_replace($_file,"",$parts["path"]));
	}
	else
		define("LIVEZILLA_URL",getScheme() . $_SERVER["HTTP_HOST"] . str_replace($_file,"",htmlentities($_SERVER["PHP_SELF"],ENT_QUOTES,"UTF-8")));
}

function disableMagicQuotes()
{
	if (get_magic_quotes_gpc()) 
	{
	    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	    while (list($key, $val) = each($process)) {
	        foreach ($val as $k => $v) {
	            unset($process[$key][$k]);
	            if (is_array($v)) {
	                $process[$key][stripslashes($k)] = $v;
	                $process[] = &$process[$key][stripslashes($k)];
	            } else {
	                $process[$key][stripslashes($k)] = stripslashes($v);
	            }
	        }
	    }
	    unset($process);
	}
}

function initStatisticProvider()
{
	global $STATS;
	require(LIVEZILLA_PATH . "_lib/objects.stats.inc.php");
	$STATS = new StatisticProvider();
}

function hexDarker($_color,$_change=30,$rgb="")
{
	$_color = str_replace("#", "", $_color);
    if(strlen($_color) != 6)
		return "#000000";
    for ($x=0;$x<3;$x++)
	{
        $c = hexdec(substr($_color,(2*$x),2)) - $_change;
        $c = ($c < 0) ? 0 : dechex($c);
        $rgb .= (strlen($c) < 2) ? "0".$c : $c;
    }
    return "#".$rgb;
}

function getBrightness($hex)
{
    $hex = str_replace('#', '', $hex);
    $c_r = hexdec(substr($hex, 0, 2));
    $c_g = hexdec(substr($hex, 2, 2));
    $c_b = hexdec(substr($hex, 4, 2));
    return (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;
}

function requireDynamic($_file,$_trustedFolder)
{
    global $CONFIG, $_CONFIG, $LZLANG; // ++
    if(strpos($_file, "..") !== false)
        return;
    if(strpos(realpath($_file),realpath($_trustedFolder)) !== 0)
        return;
    if(file_exists($_file))
        require($_file);
}

function loadConfig()
{
	global $CONFIG,$_CONFIG;
	
	$mtimes = array("ftp"=>@filemtime(FILE_FTP_CONFIG),"web"=>@filemtime(FILE_CONFIG));
    if(file_exists(FILE_FTP_CONFIG) && (!file_exists(FILE_CONFIG) || ($mtimes["ftp"]!==false && $mtimes["ftp"]>$mtimes["web"])))
        require(FILE_FTP_CONFIG);
    else if(file_exists(FILE_CONFIG))
        require(FILE_CONFIG);

    if(base64_decode($_CONFIG["gl_lzst"])==1)
    {
        requireDynamic(str_replace("config.inc","config.".strtolower($_SERVER["HTTP_HOST"]).".inc",FILE_CONFIG), LIVEZILLA_PATH . "_config/");
        if(!empty($_GET["ws"]))
            requireDynamic(str_replace("config.inc","config.".strtolower(base64_decode($_GET["ws"])).".inc",FILE_CONFIG), LIVEZILLA_PATH . "_config/");
    }

    foreach($_CONFIG as $key => $value)
		if(is_array($value) && is_int($key))
		{
			foreach($value as $skey => $svalue)
			{
				if(is_array($svalue))
				{
					foreach($svalue as $sskey => $ssvalue)
						$CONFIG[$skey][$sskey]=base64_decode($ssvalue);
				}
				else
					$CONFIG[$skey] = base64_decode($svalue);
			}
		}
		else if(is_array($value))
		{
			foreach($value as $skey => $svalue)
				$CONFIG[$key][$skey]=base64_decode($svalue);
		}
		else
			$CONFIG[$key]=base64_decode($value);
	
	if(empty($CONFIG["gl_host"]))
		$CONFIG["gl_host"] = $_SERVER["HTTP_HOST"];
		
	if(!empty($CONFIG["gl_stmo"]) && !(defined("SERVERSETUP") && SERVERSETUP))
	{
		$CONFIG["poll_frequency_tracking"] = 86400;
		$CONFIG["timeout_track"] = 0;
	}
	
    define("STATS_ACTIVE", !empty($CONFIG["gl_stp"]));
	define("ISSUBSITE",empty($CONFIG["gl_root"]) || !empty($_POST["p_host"]));
	define("SUBSITEHOST",((ISSUBSITE) ? ((!empty($_POST["p_host"]) && strpos($_POST["p_host"],"..")===false) ? $_POST["p_host"] : $CONFIG["gl_host"]) : ""));
	
	if(function_exists("date_default_timezone_set"))
	{
		if(getSystemTimezone() !== false)
			@date_default_timezone_set(getSystemTimezone());
		else
			@date_default_timezone_set('Europe/Dublin');
	}
}

function loadDatabaseConfig()
{
	global $CONFIG;
	$CONFIG["dyn"] = array();
	if(!empty($CONFIG["gl_ccac"]))
	{
		$CONFIG["db"]["cct"] = array();
		$result = queryDB(true,"SELECT *,`t1`.`id` AS `typeid` FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_LOCALIZATIONS."` AS `t2` ON `t1`.`id`=`t2`.`tid` ORDER BY `t1`.`price`;");
		while($row = @mysql_fetch_array($result, MYSQL_BOTH))
		{
			if(!isset($CONFIG["db"]["cct"][$row["typeid"]]))
				$CONFIG["db"]["cct"][$row["typeid"]] = new CommercialChatBillingType($row);
			$ccli = new CommercialChatVoucherLocalization($row);
			$CONFIG["db"]["cct"][$row["typeid"]]->Localizations[$row["language"]]=$ccli;
		}
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_PROVIDERS."`;");
		while($row = @mysql_fetch_array($result, MYSQL_BOTH))
			if($row["name"] == "Custom")
				$CONFIG["db"]["ccpp"]["Custom"] = new CommercialChatPaymentProvider($row);
			else
				$CONFIG["db"]["ccpp"][$row["name"]] = new CommercialChatPaymentProvider($row);
	}

    $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_MAILBOXES."`;");
    while($row = @mysql_fetch_array($result, MYSQL_BOTH))
        $CONFIG["db"]["gl_email"][$row["id"]] = new Mailbox($row);

}

function isServerSetup()
{
    if(defined("SERVERSETUP") && SERVERSETUP)
        return true;
    return isset($_POST[POST_INTERN_ADMINISTRATE]) || (isset($_POST[POST_INTERN_SERVER_ACTION]) && ($_POST[POST_INTERN_SERVER_ACTION] == INTERN_ACTION_GET_BANNER_LIST || $_POST[POST_INTERN_SERVER_ACTION] == INTERN_ACTION_DOWNLOAD_TRANSLATION));
}

function handleError($_errno, $_errstr, $_errfile, $_errline)
{
    global $RESPONSE;
	if(error_reporting()!=0)
		errorLog(date("d.m.y H:i") . " ERR# " . $_errno." ".$_errstr." ".$_errfile." IN LINE ".$_errline."\r");
}

function getAvailability($_serverOnly=false)
{
	//global $CONFIG;
	//if(!$_serverOnly && !empty($CONFIG["gl_deac"]))
	//	return false;
	return (@file_exists(FILE_SERVER_DISABLED)) ? false : true;
}

function slashesStrip($_value)
{
	if (@get_magic_quotes_gpc() == 1 || strtolower(@get_magic_quotes_gpc()) == "on")
        return stripslashes($_value);
    return $_value; 
}

function getIdle()
{
	if(file_exists(FILE_SERVER_IDLE) && @filemtime(FILE_SERVER_IDLE) < (time()-15))
		@unlink(FILE_SERVER_IDLE);
	return file_exists(FILE_SERVER_IDLE);
}

function getIP($_dontmask=false,$_forcemask=false,$ip="")
{
	global $CONFIG;
	$params = array($CONFIG["gl_sipp"]);
	foreach($params as $param)
		if(!empty($_SERVER[$param]))
		{
			$ipf = $_SERVER[$param];
			if(strpos($ipf,",") !== false)
			{
				$parts = explode(",",$ipf);
				foreach($parts as $part)
					if(substr_count($part,".") == 3 || substr_count($part,":") >= 3)
						$ip = trim($part);
			}
			else if(substr_count($ipf,".") == 3 || substr_count($ipf,":") >= 3)
				$ip = trim($ipf);
		}
	if(empty($ip))
		$ip = $_SERVER["REMOTE_ADDR"];
	if((!$CONFIG["gl_maskip"] || $_dontmask) && !$_forcemask)
		return $ip;
	else
	{
		$split = (substr_count($ip,".") > 0) ? "." : ":";
		$parts = explode($split,$ip);
		$val="";
		for($i=0;$i<count($parts)-1;$i++)
			$val.= $parts[$i].$split;
		return $val . "xxx";
	}
}

function getHost()
{
	global $CONFIG;
	$ip = getIP(true);
	$host = @utf8_encode(@gethostbyaddr($ip));
	if($CONFIG["gl_maskip"])
	{
		$parts = explode(".",$ip);
		if(!empty($parts[3]))
			return str_replace($parts[3],"xxx",$host);
	}
	return $host;
}

function getTimeDifference($_time)
{
	$_time = (time() - $_time);
	if(abs($_time) <= 5)
		$_time = 0;
	return $_time;
}

function parseBool($_value,$_toString=true)
{
	if($_toString)
		return ($_value) ? "true" : "false";
	else
		return ($_value) ? "1" : "0";
}

function namebase($_path)
{
	$file = basename($_path);
	if(strpos($file,'\\') !== false)
	{
		$tmp = preg_split("[\\\]",$file);
		$file = $tmp[count($tmp) - 1];
		return $file;
	}
	else
		return $file;
}

function getScheme()
{
	$scheme = SCHEME_HTTP;
	if(!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on")
		$scheme = SCHEME_HTTP_SECURE;
	if(!empty($_SERVER["HTTP_X_FORWARDED_PROTO"]) && strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"]) == "https")
		$scheme = SCHEME_HTTP_SECURE;
	else if(!empty($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443)
		$scheme = SCHEME_HTTP_SECURE;
	return $scheme;
}

function applyReplacements($_toReplace,$_language=true,$_config=true)
{
	global $CONFIG,$LZLANG;

	languageSelect();
	$to_replace = array();
	if($_language)
		$to_replace["lang"] = $LZLANG;
	if($_config)
		$to_replace["config"] = $CONFIG;
	
	foreach($to_replace as $type => $values)
        if(is_array($values))
            foreach($values as $short => $value)
                if(!is_array($value))
                {
                    $_toReplace = str_replace("<!--".$type."_".$short."-->",$value,$_toReplace);
                }
                else
                    foreach($value as $subKey => $subValue)
                    {
                        if(!is_array($subValue))
                            $_toReplace = str_replace("<!--".$type."_".$subKey."-->",$subValue,$_toReplace);
                    }

	if($_language)
		for($i=1;$i<=10;$i++)
			$_toReplace = str_replace("<!--lang_client_custom_".str_pad($i, 2, "0", STR_PAD_LEFT)."-->","",$_toReplace);
					
	return str_replace("<!--file_chat-->",FILE_CHAT,$_toReplace);
}

function getGeoURL()
{
	global $CONFIG;
	if(!empty($CONFIG["gl_pr_ngl"]))
		return CONFIG_LIVEZILLA_GEO_PREMIUM;
	else
		return "";
}

function geoReplacements($_toReplace, $jsa = "")
{
	global $CONFIG;
	$_toReplace = str_replace("<!--geo_url-->",getGeoURL() . "?aid=" . $CONFIG["wcl_geo_tracking"]."&sid=".base64_encode($CONFIG["gl_lzid"])."&dbp=".$CONFIG["gl_gtdb"],$_toReplace);
	if(!isnull(trim($CONFIG["gl_pr_ngl"])))
	{
		$jsc = "var chars = new Array(";
		$jso = "var order = new Array(";
		$chars = str_split(sha1($CONFIG["gl_pr_ngl"] . date("d"),false));
		$keys = array_keys($chars);
		shuffle($keys);
		foreach($keys as $key)
		{
			$jsc .= "'" . $chars[$key] . "',";
			$jso .= $key . ",";
		}
		$jsa .= $jsc . "0);\r\n";$jsa .= $jso . "0);\r\n";
		$jsa .= "while(lz_oak.length < (chars.length-1))for(var f in order)if(order[f] == lz_oak.length)lz_oak += chars[f];\r\n";
	}
	$_toReplace = str_replace("<!--calcoak-->",$jsa,$_toReplace);
	$_toReplace = str_replace("<!--mip-->",getIP(false,true),$_toReplace);
	return $_toReplace;
}

function processHeaderValues()
{
	if(!empty($_GET["INTERN_AUTHENTICATION_USERID"]))
	{
		$_POST[POST_INTERN_AUTHENTICATION_USERID] = base64_decode($_GET["INTERN_AUTHENTICATION_USERID"]);
		$_POST[POST_INTERN_AUTHENTICATION_PASSWORD] = base64_decode($_GET["INTERN_AUTHENTICATION_PASSWORD"]);
		$_POST[POST_INTERN_FILE_TYPE] = base64_decode($_GET["INTERN_FILE_TYPE"]);
		$_POST["p_request"] = base64_decode($_GET["SERVER_REQUEST_TYPE"]);
		$_POST[POST_INTERN_SERVER_ACTION] = base64_decode($_GET["INTERN_SERVER_ACTION"]);
	}
	if(!empty($_SERVER["ADMINISTRATE"]))
		$_POST[POST_INTERN_ADMINISTRATE] = base64_decode($_GET["ADMINISTRATE"]);
}

function getInternalSystemIdByUserId($_userId)
{
	global $INTERNAL;
	foreach($INTERNAL as $sysId => $intern)
		if($intern->UserId == $_userId)
			return $sysId;
	return null;
}

function md5file($_file)
{
	$md5file = @md5_file($_file);
	if(gettype($md5file) != 'boolean' && $md5file != false)
		return $md5file;
}

function getFile($_file,$data="")
{
	if(@file_exists($_file) && strpos($_file,"..") === false)
	{
		$handle = @fopen($_file,"r");
		if($handle)
		{
		   	$data = @fread($handle,@filesize($_file));
			@fclose ($handle);
		}
		return $data;
	}
}

function getParam($_getParam)
{
	if(isset($_GET[$_getParam]))
		return base64UrlEncode(base64UrlDecode($_GET[$_getParam]));
	else
		return null;
}

function getOptionalParam($_getParam,$_default,&$_changed=false)
{
	if(isset($_GET[$_getParam]))
	{
		if(base64UrlDecode($_GET[$_getParam]) != $_default)
			$_changed = true;
		return base64UrlDecode($_GET[$_getParam]);
	}
	else
		return $_default;
}

function getParams($_getParams="",$_allowed=null)
{
	foreach($_GET as $key => $value)
		if($key != "template" && !($_allowed != null && !isset($_allowed[$key])))
		{
			$value = !($_allowed != null && !$_allowed[$key]) ? base64UrlEncode(base64UrlDecode($value)) : base64UrlEncode($value);
			$_getParams.=((strlen($_getParams) == 0) ? $_getParams : "&") . urlencode($key) ."=" . $value;
		}
	return $_getParams;
}

function getCustomArray($_getCustomParams=null)
{
	global $INPUTS;
	initData(false,false,false,false,false,false,false,true);
	
	if(empty($_getCustomParams))
		$_getCustomParams = array('','','','','','','','','','');

	for($i=0;$i<=9;$i++)
	{
		if(isset($_GET["cf" . $i]))
			$_getCustomParams[$i] = base64UrlDecode($_GET["cf" . $i]);
		else if(isset($_POST["p_cf" . $i]) && !empty($_POST["p_cf" . $i]))
			$_getCustomParams[$i] = base64UrlDecode($_POST["p_cf" . $i]);
		else if(isset($_POST["form_" . $i]) && !empty($_POST["form_" . $i]))
			$_getCustomParams[$i] = $_POST["form_" . $i];
		else if(!isnull(getCookieValue("cf_" . $i)) && $INPUTS[$i]->Cookie)
			$_getCustomParams[$i] = getCookieValue("cf_" . $i);
		else if(($INPUTS[$i]->Type == "CheckBox" || $INPUTS[$i]->Type == "ComboBox") && empty($_getCustomParams[$i]))
			$_getCustomParams[$i] = "0";
	}
	return $_getCustomParams;
}

function cfgFileSizeToBytes($_configValue) 
{
	$_configValue = strtolower(trim($_configValue));
	$last = substr($_configValue,strlen($_configValue)-1,1);
	switch($last) 
	{
	    case 'g':
	        $_configValue *= 1024;
	    case 'm':
	        $_configValue *= 1024;
	    case 'k':
	        $_configValue *= 1024;
	}
	return floor($_configValue);
}

function createFile($_filename,$_content,$_recreate,$_backup=true)
{
	administrationLog("createFile",$_filename,((defined("CALLER_SYSTEM_ID")) ? CALLER_SYSTEM_ID : ""));
	if(strpos($_filename,"..") === false)
	{
		if(file_exists($_filename))
		{
			if($_recreate)
			{
				if(file_exists($_filename.".bak.php"))
					@unlink($_filename.".bak.php");
				if($_backup)
					@rename($_filename,$_filename.".bak.php");
				else
					@unlink($_filename);
			}
			else
				return 0;
		}
		$handle = @fopen($_filename,"w");
		if(strlen($_content)>0)
			@fputs($handle,$_content);
		@fclose($handle);
		return 1;
	}
	return 0;
}

function b64dcode(&$_a,$_b)
{
	$_a = base64_decode($_a);
}

function b64ecode(&$_a,$_b)
{
	$_a = base64_encode($_a);
}

function base64UrlDecode($_input,$_check=false)
{
    if(is_array($_input))
        logit("base64UrlDecode of array:" . serialize($_input));

    return base64_decode(str_replace(array('_','-',','),array('=','+','/'),$_input));
}

function base64UrlEncode($_input)
{
    return str_replace(array('=','+','/'),array('_','-',','),base64_encode($_input));
}

function isBase64Encoded($_data)
{
	if(preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $_data))
		return true;
	else
		return false;
}

function cutString($_string,$_maxlength)
{
	if(strlen($_string)>$_maxlength)
		return substr($_string,0,$_maxlength);
	return $_string;
}

function base64ToFile($_filename,$_content)
{
	administrationLog("base64ToFile",$_filename,CALLER_SYSTEM_ID);
	if(@file_exists($_filename))
		@unlink($_filename);
	$handle = @fopen($_filename,"wb");
	@fputs($handle,base64_decode($_content));
	@fclose($handle);
}

function fileToBase64($_filename)
{
	if(@filesize($_filename) == 0)
		return "";
	$handle = @fopen($_filename,"rb");
	$content = @fread($handle,@filesize($_filename));
	@fclose($handle);
	return base64_encode($content);
}

function initData($_internal=false,$_groups=false,$_visitors=false,$_filters=false,$_events=false,$_languages=false,$_countries=false,$_inputs=false)
{
	global $INTERNAL,$GROUPS,$LANGUAGES,$COUNTRIES,$FILTERS,$EVENTS,$VISITORS,$INPUTS;
	if($_internal && empty($INTERNAL))loadInternals();
	if($_groups && empty($GROUPS))loadGroups();
	if($_languages && empty($LANGUAGES))loadLanguages();
	if($_countries && empty($COUNTRIES))loadCountries();
	if($_filters && empty($FILTERS))loadFilters();
	if($_events && empty($EVENTS))loadEvents();
	if($_visitors && empty($VISITORS))loadVisitors();
	if($_inputs && empty($INPUTS))loadInputs();
}

function getData($_internal,$_groups,$_visitors,$_filters,$_events=false)
{
	if($_internal)loadInternals();
	if($_groups)loadGroups();
	if(DB_CONNECTION)
	{
		if($_visitors)loadVisitors();
		if($_filters)loadFilters();
		if($_events)loadEvents();
	}
}

function loadInputs($count=0)
{
	global $CONFIG,$INPUTS;
	if(!empty($CONFIG["gl_input_list"]))
		foreach($CONFIG["gl_input_list"] as $values)
		{
			$input = new DataInput($values);
			$sorter[($input->Position+10)."-".$count++] = $input;
		}
	$sorter[($input->Position+10)."-".$count++] = new DataInput(null); //+
	ksort($sorter);
	foreach($sorter as $input)
		$INPUTS[$input->Index] = $input;
}

function loadLanguages()
{
    global $LANGUAGES; //+
    require(LIVEZILLA_PATH . "_lib/objects.languages.inc.php");
}

function loadCountries()
{
    global $COUNTRIES,$COUNTRY_ALIASES; //+
    require(LIVEZILLA_PATH . "_lib/objects.countries.inc.php");
}

function loadFilters()
{
	global $FILTERS;
	$FILTERS = new FilterList();
}

function loadEvents()
{
	global $EVENTS;
	$EVENTS = new EventList();
	$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENTS."` WHERE `priority`>=0 ORDER BY `priority` DESC;");
	while($row = @mysql_fetch_array($result, MYSQL_BOTH))
	{
		$Event = new Event($row);
		$result_urls = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_URLS."` WHERE `eid`='".@mysql_real_escape_string($Event->Id)."';");
		while($row_url = @mysql_fetch_array($result_urls, MYSQL_BOTH))
		{
			$EventURL = new EventURL($row_url);
			$Event->URLs[$EventURL->Id] = $EventURL;
		}
		
		$result_funnel_urls = queryDB(true,"SELECT `ind`,`uid` FROM `".DB_PREFIX.DATABASE_EVENT_FUNNELS."` WHERE `eid`='".@mysql_real_escape_string($Event->Id)."';");
		while($funnel_url = @mysql_fetch_array($result_funnel_urls, MYSQL_BOTH))
		{
			$Event->FunnelUrls[$funnel_url["ind"]] = $funnel_url["uid"];
		}
		$result_actions = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTIONS."` WHERE `eid`='".@mysql_real_escape_string($Event->Id)."';");
		while($row_action = @mysql_fetch_array($result_actions, MYSQL_BOTH))
		{
			$EventAction = new EventAction($row_action);
			$Event->Actions[$EventAction->Id] = $EventAction;
			
			if($EventAction->Type==2)
			{
				$result_action_invitations = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_OVERLAYS."` WHERE `action_id`='".@mysql_real_escape_string($EventAction->Id)."';");
				$row_invitation = @mysql_fetch_array($result_action_invitations, MYSQL_BOTH);
				$EventAction->Invitation = new Invitation($row_invitation);
				$result_senders = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_SENDERS."` WHERE `pid`='".@mysql_real_escape_string($EventAction->Invitation->Id)."' ORDER BY `priority` DESC;");
				while($row_sender = @mysql_fetch_array($result_senders, MYSQL_BOTH))
				{
					$InvitationSender = new EventActionSender($row_sender);
					$EventAction->Invitation->Senders[$InvitationSender->Id] = $InvitationSender;
				}
			}
			else if($EventAction->Type==5)
			{
				$result_action_overlaybox = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_OVERLAYS."` WHERE `action_id`='".@mysql_real_escape_string($EventAction->Id)."';");
				$row_overlaybox = @mysql_fetch_array($result_action_overlaybox, MYSQL_BOTH);
				$EventAction->OverlayBox = new OverlayElement($row_overlaybox);
			}
			else if($EventAction->Type==4)
			{
				$result_action_website_pushs = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_WEBSITE_PUSHS."` WHERE `action_id`='".@mysql_real_escape_string($EventAction->Id)."';");
				$row_website_push = @mysql_fetch_array($result_action_website_pushs, MYSQL_BOTH);
				$EventAction->WebsitePush = new WebsitePush($row_website_push,true);
				
				$result_senders = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_SENDERS."` WHERE `pid`='".@mysql_real_escape_string($EventAction->WebsitePush->Id)."' ORDER BY `priority` DESC;");
				while($row_sender = @mysql_fetch_array($result_senders, MYSQL_BOTH))
				{
					$WebsitePushSender = new EventActionSender($row_sender);
					$EventAction->WebsitePush->Senders[$WebsitePushSender->Id] = $WebsitePushSender;
				}
			}
			else if($EventAction->Type<2)
			{
				$result_receivers = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_RECEIVERS."` WHERE `action_id`='".@mysql_real_escape_string($EventAction->Id)."';");
				while($row_receiver = @mysql_fetch_array($result_receivers, MYSQL_BOTH))
					$EventAction->Receivers[$row_receiver["receiver_id"]] = new EventActionReceiver($row_receiver);
			}
		}
		if(STATS_ACTIVE)
		{
			$result_goals = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_GOALS."` WHERE `event_id`='".@mysql_real_escape_string($Event->Id)."';");
			while($row_goals = @mysql_fetch_array($result_goals, MYSQL_BOTH))
				$Event->Goals[$row_goals["goal_id"]] = new EventAction($row_goals["goal_id"],9);
		}
		$EVENTS->Events[$Event->Id] = $Event;
	}
}

function loadInternals()
{
	global $CONFIG,$INTERNAL;
	if(DB_CONNECTION)
	{
		$result = queryDB(false,"SELECT * FROM `".DB_PREFIX.DATABASE_OPERATORS."` ORDER BY `bot` ASC, `fullname` ASC;");
		if(!$result)
			$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_OPERATORS."`;");
		while($row = @mysql_fetch_array($result, MYSQL_BOTH))
		{
			if(!empty($row["system_id"]))
			{
				$INTERNAL[$row["system_id"]] = new Operator($row["system_id"],$row["id"]);
				$INTERNAL[$row["system_id"]]->Email = $row["email"];
				$INTERNAL[$row["system_id"]]->Webspace = $row["webspace"];
				$INTERNAL[$row["system_id"]]->Level = $row["level"];
				$INTERNAL[$row["system_id"]]->Description = $row["description"];
				$INTERNAL[$row["system_id"]]->Fullname = $row["fullname"];
				$INTERNAL[$row["system_id"]]->Language = $row["languages"];
				$INTERNAL[$row["system_id"]]->Groups = @unserialize(base64_decode($row["groups"]));
				if(!empty($INTERNAL[$row["system_id"]]->Groups))
					array_walk($INTERNAL[$row["system_id"]]->Groups,"b64dcode");
				$INTERNAL[$row["system_id"]]->GroupsHidden = @unserialize(base64_decode($row["groups_hidden"]));
				if(!empty($INTERNAL[$row["system_id"]]->GroupsHidden))
					array_walk($INTERNAL[$row["system_id"]]->GroupsHidden,"b64dcode");
				$INTERNAL[$row["system_id"]]->GroupsArray = $row["groups"];
				$INTERNAL[$row["system_id"]]->PermissionSet = $row["permissions"];
				$INTERNAL[$row["system_id"]]->CanAutoAcceptChats = (isset($row["auto_accept_chats"])) ? $row["auto_accept_chats"] : 1;
				$INTERNAL[$row["system_id"]]->LoginIPRange = $row["login_ip_range"];
				$INTERNAL[$row["system_id"]]->IsBot = !empty($row["bot"]);
                $INTERNAL[$row["system_id"]]->ClientMobile = !empty($row["lapp"]);
                $INTERNAL[$row["system_id"]]->ClientWeb = !empty($row["lweb"]);
				$INTERNAL[$row["system_id"]]->FirstCall = ($row["first_active"]<(time()-$CONFIG["timeout_clients"]));
				$INTERNAL[$row["system_id"]]->LoginId = $row["login_id"];
                $INTERNAL[$row["system_id"]]->Deactivated = ($row["sign_off"]==2);
				$INTERNAL[$row["system_id"]]->FirstActive = ($row["first_active"]<(time()-$CONFIG["timeout_clients"]))?time():$row["first_active"];
				$INTERNAL[$row["system_id"]]->Password = $row["password"];

                $INTERNAL[$row["system_id"]]->Status = $row["status"];
                if($row["status"] != USER_STATUS_OFFLINE)
                    if($row["last_active"]<(time()-$CONFIG["timeout_clients"]))
                    {
                        if($INTERNAL[$row["system_id"]]->ClientWeb && ($row["last_active"]>(time()-($CONFIG["timeout_clients"]*10))))
                            $INTERNAL[$row["system_id"]]->Status = USER_STATUS_AWAY;
                        else
                            $INTERNAL[$row["system_id"]]->Status = USER_STATUS_OFFLINE;
                    }

				$INTERNAL[$row["system_id"]]->Level = $row["level"];
				$INTERNAL[$row["system_id"]]->IP = $row["ip"];
				$INTERNAL[$row["system_id"]]->Typing = $row["typing"];
				$INTERNAL[$row["system_id"]]->SignOffRequest = !empty($row["sign_off"]);
				$INTERNAL[$row["system_id"]]->VisitorFileSizes = @unserialize($row["visitor_file_sizes"]);
				$INTERNAL[$row["system_id"]]->Reposts = @unserialize(@$row["reposts"]);
				if(!empty($row["groups_status"]))
					$INTERNAL[$row["system_id"]]->GroupsAway = @unserialize($row["groups_status"]);
				$INTERNAL[$row["system_id"]]->LastActive = $row["last_active"];
				$INTERNAL[$row["system_id"]]->LastChatAllocation = $row["last_chat_allocation"];
				$INTERNAL[$row["system_id"]]->PasswordChange = $row["password_change"];
				$INTERNAL[$row["system_id"]]->PasswordChangeRequest = !empty($row["password_change_request"]);
				$INTERNAL[$row["system_id"]]->WebsitesUsers = @unserialize(base64_decode(@$row["websites_users"]));
				if(!empty($INTERNAL[$row["system_id"]]->WebsitesUsers))
					array_walk($INTERNAL[$row["system_id"]]->WebsitesUsers,"b64dcode");
				$INTERNAL[$row["system_id"]]->WebsitesConfig = @unserialize(base64_decode(@$row["websites_config"]));
				if(!empty($INTERNAL[$row["system_id"]]->WebsitesConfig))
					array_walk($INTERNAL[$row["system_id"]]->WebsitesConfig,"b64dcode");
					
				if($INTERNAL[$row["system_id"]]->IsBot)
				{
					$INTERNAL[$row["system_id"]]->FirstCall =
					$INTERNAL[$row["system_id"]]->FirstActive = 
					$INTERNAL[$row["system_id"]]->LastActive = time();
					$INTERNAL[$row["system_id"]]->Status = USER_STATUS_ONLINE;
					$INTERNAL[$row["system_id"]]->WelcomeManager = !empty($row["wm"]);
					$INTERNAL[$row["system_id"]]->WelcomeManagerOfferHumanChatAfter = $row["wmohca"];
				}
			}
		}
	}
	if(empty($INTERNAL))
	{
		$INTERNAL = array();
		if(!empty($CONFIG["gl_insu"]) && !empty($CONFIG["gl_insp"]))
		{
			$INTERNAL[$CONFIG["gl_insu"]] = new Operator($CONFIG["gl_insu"],$CONFIG["gl_insu"]);
			$INTERNAL[$CONFIG["gl_insu"]]->Level = USER_LEVEL_ADMIN;
			$INTERNAL[$CONFIG["gl_insu"]]->Password = $CONFIG["gl_insp"];
		}
	}
}

function loadGroups()
{
	global $GROUPS,$INTERNAL;
	if(DB_CONNECTION)
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_GROUPS."`;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(empty($GROUPS[$row["id"]]))
					$GROUPS[$row["id"]] = new UserGroup($row["id"],$row);
					
				if((!empty($row["hide_chat_group_selection"]) || isset($_GET["hcgs"])) && !defined("HideChatGroupSelection"))
					define("HideChatGroupSelection",true);
				if((!empty($row["hide_ticket_group_selection"]) || isset($_GET["htgs"])) && !defined("HideTicketGroupSelection"))
					define("HideTicketGroupSelection",true);
			}
	}

	if(!empty($_POST["p_groups_0_id"]) && empty($GROUPS) && defined("SERVERSETUP") && SERVERSETUP && !empty($INTERNAL))
		$GROUPS["DEFAULT"] = new UserGroup("DEFAULT");
}

function loadVisitors($_fullList=false,$_sqlwhere="",$_limit="",$count=0)
{
	global $VISITOR,$CONFIG,$COUNTRIES;
	$VISITOR = array();
	
	if(!$_fullList)
		$_sqlwhere = " WHERE `last_active`>".@mysql_real_escape_string(time()-$CONFIG["timeout_track"]);

	$result = queryDB(true,"SELECT *,`t1`.`id` AS `id` FROM `".DB_PREFIX.DATABASE_VISITORS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_BROWSERS."` AS `t2` ON `t1`.`browser`=`t2`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_CITIES."` AS `t3` ON `t1`.`city`=`t3`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_REGIONS."` AS `t4` ON `t1`.`region`=`t4`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_ISPS."` AS `t5` ON `t1`.`isp`=`t5`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_SYSTEMS."` AS `t6` ON `t1`.`system`=`t6`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_RESOLUTIONS."` AS `t8` ON `t1`.`resolution`=`t8`.`id`".$_sqlwhere." ORDER BY `entrance` ASC".$_limit.";");
	if($result)
	{
		initData(false,false,false,false,false,false,true);
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			if(!isset($VISITOR[$row["id"]]))
			{
                if(!empty($COUNTRIES[$row["country"]]))
				    $row["countryname"] = $COUNTRIES[$row["country"]];

				if(!isset($vcount[$row["id"]]))
					$vcount[$row["id"]]=0;
				$vcount[$row["id"]]++;
				$row["dcount"] = $vcount[$row["id"]];
				$index = ($_fullList) ? $count++ : $row["id"];
				$VISITOR[$index] = new Visitor($row["id"]);
				$VISITOR[$index]->Load($row);
				$VISITOR[$index]->LoadBrowsers($_fullList);
			}
		$visitors = $VISITOR;
		$VISITOR = array();
		foreach($visitors as $vid => $visitor)
		{
			if(count($visitor->Browsers) > 0 || $_fullList)
			{
				$VISITOR[$vid] = $visitor;
			}
		}
	}
}

function getTargetParameters($allowCOM=true)
{
	global $GROUPS;
	$parameters = array("exclude"=>null,"include_group"=>null,"include_user"=>null);
	
	if(isset($_GET[GET_EXTERN_HIDDEN_GROUPS]))
	{
		$groups = base64UrlDecode($_GET[GET_EXTERN_HIDDEN_GROUPS]);
		if(strlen($groups) > 1)
			$parameters["exclude"] = explode("?",$groups);
		if(isset($_GET[GET_EXTERN_GROUP]))
			$parameters["include_group"] = array(base64UrlDecode($_GET[GET_EXTERN_GROUP]));
		if(isset($_GET[GET_EXTERN_INTERN_USER_ID]))
			$parameters["include_user"] = base64UrlDecode($_GET[GET_EXTERN_INTERN_USER_ID]);
		if(strlen($groups) == 1 && is_array($GROUPS))
			foreach($GROUPS as $gid => $group)
				if(!in_array($gid,$parameters["include_group"]))
					$parameters["exclude"][] = $gid;
	}
	
	if(!$allowCOM)
	{
		initData(false,true);
		foreach($GROUPS as $gid => $group)
			if(!empty($GROUPS[$gid]->ChatVouchersRequired) && !(is_array($parameters["exclude"]) && in_array($gid,$parameters["exclude"])))
				$parameters["exclude"][] = $gid;
	}
	return $parameters;
}

function operatorsAvailable($_amount=0, $_exclude=null, $include_group=null, $include_user=null, $_allowBots=false)
{
	global $CONFIG,$INTERNAL,$GROUPS;
	if(!DB_CONNECTION)
		return 0;
	initData(true,true);
	if(!empty($include_user))
		$include_group = $INTERNAL[getInternalSystemIdByUserId($include_user)]->GetGroupList(true);

	foreach($INTERNAL as $sysId => $internaluser)
	{
		$isex = $internaluser->IsExternal($GROUPS, $_exclude, $include_group, true);
		if($isex && $internaluser->Status < USER_STATUS_OFFLINE)
		{
			if($_allowBots || !$internaluser->IsBot)
				$_amount++;
		}
	}
	return $_amount;
}

function getOperatorList()
{
	global $INTERNAL,$GROUPS;
	$array = array();
	initData(true,true,false,false);
	foreach($INTERNAL as $sysId => $internaluser)
		if($internaluser->IsExternal($GROUPS))
			$array[utf8_decode($internaluser->Fullname)] = $internaluser->Status;
	return $array;
}

function getOperators()
{
	global $INTERNAL,$GROUPS;
	$array = array();
	initData(true,true,false,false);
	foreach($INTERNAL as $sysId => $internaluser)
	{
		$internaluser->IsExternal($GROUPS);
		$array[$sysId] = $internaluser;
	}
	return $array;
}

function isValidUploadFile($_filename)
{
	global $CONFIG;
	if(!empty($CONFIG["wcl_upload_blocked_ext"]))
	{
		$extensions = explode(",",str_replace("*.","",$CONFIG["wcl_upload_blocked_ext"]));
		foreach($extensions as $ext)
			if(strlen($_filename) > strlen($ext) && substr($_filename,strlen($_filename)-strlen($ext),strlen($ext)) == $ext)
				return false;
	}
	return true;
}

function getLocalizationFileString($_language,$_checkForExistance=true)
{
	$file = LIVEZILLA_PATH . "_language/lang" . strtolower($_language) . ((ISSUBSITE)? ".".SUBSITEHOST:"") . ".php";
	if($_checkForExistance && !@file_exists($file))
		$file = LIVEZILLA_PATH . "_language/lang" . strtolower($_language) . ".php";
	return $file;
}

function languageSelect($_mylang="",$_require=false)
{
	global $CONFIG,$INTERNAL,$LANGUAGES,$DEFAULT_BROWSER_LANGUAGE,$LANG_DIR,$LZLANG; //++
	initData(false,false,false,false,false,true);

    if(!$_require && !empty($DEFAULT_BROWSER_LANGUAGE))
        return;

	requireDynamic(getLocalizationFileString("en"),LIVEZILLA_PATH . "_language/");
		
	if(empty($_mylang))
	{
		if(defined("CALLER_TYPE") && CALLER_TYPE == CALLER_TYPE_INTERNAL && defined("CALLER_SYSTEM_ID"))
			$_mylang = strtolower($INTERNAL[CALLER_SYSTEM_ID]->Language);
		else
		{
			$_mylang = getBrowserLocalization();
			$_mylang = strtolower($_mylang[0]);
		}
	}

	if(!empty($CONFIG["gl_on_def_lang"]) && file_exists($tfile=getLocalizationFileString($CONFIG["gl_default_language"])) && @filesize($tfile)>0)
	{
		$DEFAULT_BROWSER_LANGUAGE = $CONFIG["gl_default_language"];
        requireDynamic(getLocalizationFileString($CONFIG["gl_default_language"]),LIVEZILLA_PATH . "_language/");
	}
	else if(empty($_mylang) || (!empty($_mylang) && strpos($_mylang,"..") === false))
	{
		if(!empty($_mylang) && strlen($_mylang) >= 5 && substr($_mylang,2,1) == "-" && file_exists($tfile=getLocalizationFileString(substr($_mylang,0,5))) && @filesize($tfile)>0)
            requireDynamic(getLocalizationFileString($s_browser_language=strtolower(substr($_mylang,0,5))),LIVEZILLA_PATH . "_language/");
		else if(!empty($_mylang) && strlen($_mylang) > 1 && file_exists($tfile=getLocalizationFileString(substr($_mylang,0,2))) && @filesize($tfile)>0)
            requireDynamic(getLocalizationFileString($s_browser_language=strtolower(substr($_mylang,0,2))),LIVEZILLA_PATH . "_language/");
		else if(file_exists($tfile=getLocalizationFileString($CONFIG["gl_default_language"])) && @filesize($tfile)>0)
            requireDynamic(getLocalizationFileString($s_browser_language=$CONFIG["gl_default_language"]),LIVEZILLA_PATH . "_language/");
			
		if(isset($s_browser_language))
			$DEFAULT_BROWSER_LANGUAGE = $s_browser_language;
	}
	else if(file_exists(getLocalizationFileString($CONFIG["gl_default_language"])))
        requireDynamic(getLocalizationFileString($CONFIG["gl_default_language"]),LIVEZILLA_PATH . "_language/");
	if(empty($DEFAULT_BROWSER_LANGUAGE) && file_exists(getLocalizationFileString("en")))
		$DEFAULT_BROWSER_LANGUAGE = "en";
	if(empty($DEFAULT_BROWSER_LANGUAGE) || (!empty($DEFAULT_BROWSER_LANGUAGE) && !@file_exists(getLocalizationFileString($DEFAULT_BROWSER_LANGUAGE))))
		exit("Localization error: default language is not available.");
	$LANG_DIR = (($LANGUAGES[strtoupper($DEFAULT_BROWSER_LANGUAGE)][2]) ? "rtl":"ltr");

    if($_require)
        loadInputs();
}

function getLongPollRuntime()
{
	global $CONFIG;
	if(SAFE_MODE)
		$value = 10;
	else
	{
		$value = $CONFIG["timeout_clients"] - $CONFIG["poll_frequency_clients"] - 55;
		if(!isnull($ini = @ini_get('max_execution_time')) && $ini > $CONFIG["poll_frequency_clients"] && $ini < $value)
			$value = $ini-$CONFIG["poll_frequency_clients"];
		if($value > 20)
			$value = 20;
		if($value < 1)
			$value = 1;
	}
	return $value;
}

function checkPhpVersion($_ist,$_ond,$_ird)
{
	$array = explode(".",phpversion());
	if($array[0] >= $_ist)
	{
		if($array[1] > $_ond || ($array[1] == $_ond && $array[2] >= $_ird))
			return true;
		return false;
	}
	return false;
}

function getAlertTemplate()
{
	global $CONFIG;
	$html = str_replace("<!--server-->",LIVEZILLA_URL,getFile(TEMPLATE_SCRIPT_ALERT));
	$html = str_replace("<!--title-->",$CONFIG["gl_site_name"],$html);
	return $html;
}

function formLanguages($_lang)
{
	if(strlen($_lang) == 0)
		return "";
	$array_lang = explode(",",$_lang);
	foreach($array_lang as $key => $lang)
		if($key == 0)
		{
			$_lang = strtoupper(substr(trim($lang),0,2));
			break;
		}
	return (strlen($_lang) > 0) ? $_lang : "";
}

function administrationLog($_type,$_value,$_user)
{
	if(DB_CONNECTION)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_ADMINISTRATION_LOG."` (`id`,`type`,`value`,`time`,`user`) VALUES ('".@mysql_real_escape_string(getId(32))."','".@mysql_real_escape_string($_type)."','".@mysql_real_escape_string($_value)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($_user)."');");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_ADMINISTRATION_LOG."` WHERE `time`<'".@mysql_real_escape_string(time()-2592000)."';");
	}
}

function logit($_id,$_file=null)
{
	if(empty($_file))
		$_file = LIVEZILLA_PATH . "_log/debug.txt";
	
	if(@file_exists($_file) && @filesize($_file) > 5000000)
		@unlink($_file);
		
	$handle = @fopen($_file,"a+");
	@fputs($handle,$_id."\r\n");
	@fclose($handle);
}

function errorLog($_message)
{
	global $RESPONSE;
	if(defined("FILE_ERROR_LOG"))
	{
		if(file_exists(FILE_ERROR_LOG) && @filesize(FILE_ERROR_LOG) > 500000)
			@unlink(FILE_ERROR_LOG);
		$handle = @fopen(FILE_ERROR_LOG,"a+");
		if($handle)
		{
			@fputs($handle,$_message . "\r");
			@fclose($handle);
		}
		if(!empty($RESPONSE))
		{
			if(!isset($RESPONSE->Exceptions))
				$RESPONSE->Exceptions = "";
			$RESPONSE->Exceptions .= "<val err=\"".base64_encode(trim($_message))."\" />";
		}
	}
	else
		$RESPONSE->Exceptions = "";
}

function getValueBySystemId($_systemid,$_value,$_default)
{
	$value = $_default;
	$parts = explode("~",$_systemid);
	
	$result = queryDB(true,"SELECT `".@mysql_real_escape_string($_value)."` FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `visitor_id`='".@mysql_real_escape_string($parts[0])."' AND `browser_id`='".@mysql_real_escape_string($parts[1])."' ORDER BY `last_active` DESC LIMIT 1;");
	if($result)
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		$value = $row[$_value];
	}
	return $value;
}

function getId($_length,$start=0)
{
	$id = md5(uniqid(rand(),1));
	if($_length != 32)
		$start = rand(0,(31-$_length));
	$id = substr($id,$start,$_length);
	return $id;
}

function createFloodFilter($_ip,$_userId)
{
	global $FILTERS;
	initData(false,false,false,true);
	foreach($FILTERS->Filters as $currentFilter)
		if($currentFilter->IP == $_ip && $currentFilter->Activeipaddress == 1 && $currentFilter->Activestate == 1)
			return;
	
	createFilter($_ip,$_userId,"AUTO FLOOD FILTER");
}

function createFilter($_ip,$_userId,$_reason)
{
    $filter = new Filter(md5(uniqid(rand())));
    $filter->Creator = "SYSTEM";
    $filter->Created = time();
    $filter->Editor = "SYSTEM";
    $filter->Edited = time();
    $filter->IP = $_ip;
    $filter->Expiredate = 172800;
    $filter->Userid = $_userId;
    $filter->Reason = "";
    $filter->Filtername = $_reason;
    $filter->Activestate = 1;
    $filter->Exertion = 0;
    $filter->Languages = "";
    $filter->Activeipaddress = 1;
    $filter->Activeuserid = (!empty($_userId)) ? 1 : 0;
    $filter->Activelanguage = 0;
    $filter->Save();
}

function createSPAMFilter()
{
    global $CONFIG;
    if(!empty($CONFIG["gl_sfa"]))
    {
        $filterkeys = array(0=>$CONFIG["gl_sfv"]);
        if(strpos($CONFIG["gl_sfv"],",") !== -1)
            $filterkeys = explode(",",$CONFIG["gl_sfv"]);

        foreach($filterkeys as $fvalue)
        {
            foreach($_GET as $gvalue)
            {
                if(jokerCompare($fvalue,base64UrlDecode($gvalue)))
                {
                    createFilter(getIP(),null,"AUTO SPAM Filter: " . $fvalue);
                    return true;
                }
            }

            foreach($_POST as $pvalue)
            {
                if(jokerCompare($fvalue,base64UrlDecode($pvalue)))
                {
                    createFilter(getIP(),null,"AUTO SPAM Filter: " . $fvalue);
                    return true;
                }
            }
        }
    }
    return false;


}

function isFlood($_ip,$_userId)
{
	global $CONFIG;
	if(empty($CONFIG["gl_atflt"]))
		return false;

	$sql = "SELECT * FROM `".DB_PREFIX.DATABASE_VISITORS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` AS t2 ON t1.id=t2.visitor_id WHERE t1.`ip`='".@mysql_real_escape_string($_ip)."' AND `t2`.`created`>".(time()-FLOOD_PROTECTION_TIME) . " AND `t1`.`visit_latest`=1";
	if($result = queryDB(true,$sql));
		if(@mysql_num_rows($result) >= FLOOD_PROTECTION_SESSIONS)
		{
			createFloodFilter($_ip,$_userId);
			return true;
		}
	return false;
}

function removeSSpanFile($_all)
{
	if($_all || (getSpanValue() < time()))
		setSpanValue(0);
}

function isSSpanFile()
{
	return !isnull(getSpanValue());
}

function getSpanValue()
{
	if(DB_CONNECTION && $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_INFO."`"))
		if($row = mysql_fetch_array($result, MYSQL_BOTH))
			return $row["gtspan"];
	return time();
}

function setSpanValue($_value)
{
	if(DB_CONNECTION)
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_INFO."` SET `gtspan`='".@mysql_real_escape_string($_value)."'");
}

function createSSpanFile($_sspan)
{
	if($_sspan >= CONNECTION_ERROR_SPAN && $_sspan <=600)
		setSpanValue((time()+$_sspan));
}

function getLocalTimezone($_timezone,$ltz=0)
{
	$template = "%s%s%s:%s%s";
	if(isset($_timezone) && !empty($_timezone))
	{
		$ltz = $_timezone;
		if($ltz == ceil($ltz))
		{
			if($ltz >= 0 && $ltz < 10)
				$ltz = sprintf($template,"+","0",$ltz,"0","0");
			else if($ltz < 0 && $ltz > -10)
				$ltz = sprintf($template,"-","0",$ltz*-1,"0","0");
			else if($ltz >= 10)
				$ltz = sprintf($template,"+",$ltz,"","0","0");
			else if($ltz <= -10)
				$ltz = sprintf($template,"",$ltz,"","0","0");
		}
		else
		{
			$split = explode(".",$ltz);
			$split[1] = (60 * $split[1]) / 100;
			if($ltz >= 0 && $ltz < 10)
				$ltz = sprintf($template,"+","0",$split[0],$split[1],"0");
			else if($ltz < 0 && $ltz > -10)
				$ltz = sprintf($template,"","0",$split[0],$split[1],"0");
				
			else if($ltz >= 10)
				$ltz = sprintf($template,"+",$split[0],"",$split[1],"0");
			
			else if($ltz <= -10)
				$ltz = sprintf($template,"",$split[0],"",$split[1],"0");
		}
	}
	return $ltz;
}

function isValidEmail($_email)
{
	return preg_match('/^([*+!.&#$�\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $_email);
}

function setCookieValue($_key,$_value)
{
	global $CONFIG;
	if(empty($CONFIG["gl_colt"]))
		setcookie("livezilla", "", time() - 3600);
	else
	{
		if(!isset($_COOKIE["livezilla"]))
			$c_array = Array();
		else
			$c_array = @unserialize(@base64_decode($_COOKIE["livezilla"]));
		if(!isset($c_array[$_key]) || (isset($c_array[$_key]) && $c_array[$_key] != $_value))
		{	
			$c_array[$_key] = $_value;
			$lifetime = ((empty($CONFIG["gl_colt"])) ? 0 : (time()+($CONFIG["gl_colt"]*86400)));
			setcookie("livezilla",($_COOKIE["livezilla"] = base64_encode(serialize($c_array))),$lifetime);
		}
	}
}

function getCookieValue($_key)
{
	global $CONFIG;
	if(empty($CONFIG["gl_colt"]))
		return null;
	if(isset($_COOKIE["livezilla"]))
		$c_array = @unserialize(base64_decode($_COOKIE["livezilla"]));
	if(isset($c_array[$_key]))
		return $c_array[$_key];
	else
		return null;
}

function hashFile($_file)
{
	$enfile = md5(base64_encode(file_get_contents($_file)));
	return $enfile;
}

function mTime()
{
	$time = str_replace(".","",microtime());
	$time = explode(" " , $time);
	return $time;
}

function microtimeFloat($_microtime)
{
   list($usec, $sec) = explode(" ", $_microtime);
   return ((float)$usec + (float)$sec);
}

function testDirectory($_dir)
{	
	if(!@is_dir($_dir))
		@mkdir($_dir);
	
	if(@is_dir($_dir))
	{
		$fileid = md5(uniqid(rand()));
		$handle = @fopen ($_dir . $fileid ,"a");
		@fputs($handle,$fileid."\r\n");
		@fclose($handle);
		
		if(!file_exists($_dir . $fileid))
			return false;
			
		@unlink($_dir . $fileid);
		if(file_exists($_dir . $fileid))
			return false;
			
		return true;
	}
	else
		return false;
}

function sendMail($_account,$_receiver,$_replyto,$_text,$_subject="",$_test=false,$_attachments=null,$return = "")
{
    if($_account == null)
       $_account=Mailbox::GetDefaultOutgoing();

    if($_account == null)
        return null;

    $EOL = ($_account->Type == "SMTP") ? "\r\n" : "\n";
    $headers  = "From: ".$_account->Email.$EOL;
    $headers .= "Reply-To: ".$_replyto.$EOL;
    $headers .= "Date: ".date("r").$EOL;
    $headers .= "MIME-Version: 1.0".$EOL;
    $headers .= "Content-Type: text/plain; charset=UTF-8; format=flowed".$EOL;
    $headers .= "Content-Transfer-Encoding: 8bit".$EOL;
    $headers .= "X-Mailer: LiveZilla.net/" . VERSION.$EOL;

    if($_account->Type == "SMTP")
        $return = smtpMail($_account->Host, $_account->Port, $_receiver, $_replyto, $_subject, $_text, $_account->Email, $_account->Password, $_account->Username, $_account->SSL, $_attachments, $_account->SenderName, $_test);
    else if($_account->Type == "PHPMail")
    {
        if(strpos($_receiver,",") !== false)
        {
            $emails = explode(",",$_receiver);
            foreach($emails as $mail)
                if(!empty($mail))
                    $return = sendMail($_account,trim($mail), $_replyto, $_text, $_subject, $_attachments, $return);
            return $return;
        }
        if(@mail($_receiver, $_subject, $_text, $headers))
            $return = 1;
        else
            $return = "The email could not be sent using PHP mail(). Please try another Return Email Address or use SMTP.";
    }
	return $return;
}

function smtpMail($_server, $_port, $_receiver, $_replyto, $_subject, $_text, $_from, $_password, $_account, $_secure, $_attachments=null, $_senderName="", $_test=false)
{
    try
    {
        loadLibrary("ZEND","Zend_Mail");
        loadLibrary("ZEND","Zend_Mail_Transport_Smtp");

        if(empty($_text))
            $_text = ">>";

        $config = array('auth' => 'login', 'username' => $_account,'password' => $_password, 'port' => $_port);

        if(!empty($_secure))
            $config['ssl'] = 'SSL';

        $transport = new Zend_Mail_Transport_Smtp($_server, $config);

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyText($_text);
        $mail->setFrom($_from, $_senderName);

        if(strpos($_receiver,",") !== false)
        {
            $emails = explode(",",$_receiver);
            $add = false;
            foreach($emails as $mailrec)
                if(!empty($mailrec))
                    if(!$add)
                    {
                        $add = true;
                        $mail->addTo($mailrec, $mailrec);
                    }
                    else
                        $mail->addBcc($mailrec, $mailrec);
        }
        else
            $mail->addTo($_receiver, $_receiver);

        $mail->setSubject($_subject);
        $mail->setReplyTo($_replyto, $name=null);

        if($_attachments != null)
            foreach($_attachments as $resId)
            {
                $res = getResource($resId);
                $at = $mail->createAttachment(file_get_contents("./uploads/" . $res["value"]));
                $at->type        = 'application/octet-stream';
                $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $at->encoding    = Zend_Mime::ENCODING_BASE64;
                $at->filename    = $res["title"];
            }
        $mail->send($transport);
    }
    catch (Exception $e)
    {
        if($_test)
            throw $e;
        else
            handleError("111",$_server . " send mail connection error: " . $e->getMessage(),"functions.global.inc.php",0);
        return 0;
    }
    return 1;
}

function downloadEmails($cronJob=false,$exists=false,$reload=false)
{
    global $GROUPS,$CONFIG;

    if(is_array($GROUPS))
        foreach($GROUPS as $group)
        {
            $gmbout = Mailbox::GetById($group->TicketEmailOut);
            if(is_array($group->TicketEmailIn))
                foreach($group->TicketEmailIn as $mid)
                    if(!empty($CONFIG["db"]["gl_email"][$mid]) && $CONFIG["db"]["gl_email"][$mid]->LastConnect < (time()-($CONFIG["db"]["gl_email"][$mid]->ConnectFrequency*60)))
                    {
                        $CONFIG["db"]["gl_email"][$mid]->SetLastConnect(time());

                        $newmails = downloadFromMailbox($reload,$CONFIG["db"]["gl_email"][$mid]->Type,$CONFIG["db"]["gl_email"][$mid]->Host,$CONFIG["db"]["gl_email"][$mid]->Port,$CONFIG["db"]["gl_email"][$mid]->Password,$CONFIG["db"]["gl_email"][$mid]->Username,$CONFIG["db"]["gl_email"][$mid]->SSL,$CONFIG["db"]["gl_email"][$mid]->Delete);

                        if($reload)
                            $CONFIG["db"]["gl_email"][$mid]->SetLastConnect(0);

                        if(!empty($newmails) && is_array($newmails))
                            foreach($newmails as $temail)
                            {
                                if(TicketEmail::Exists($temail->Id))
                                {
                                    continue;
                                }

                                $Ticket = null;

                                $temail->MailboxId = $mid;
                                $temail->GroupId = $group->Id;

                                if(preg_match_all("/\[[a-zA-Z\d]{12}\]/", $temail->Subject . $temail->Body, $matches))
                                {
                                    foreach($matches[0] as $match)
                                    {
                                        $id=$groupid=$language="";
                                        if($exists=UserTicket::Exists($match,$id,$groupid,$language))
                                        {
                                            $Ticket = new UserTicket($id,true);
                                            $Ticket->Group = $groupid;
                                            $Ticket->Language = strtoupper($language);
                                            $Ticket->Messages[0]->Type = ($temail->Email == $gmbout->Email || $temail->Email == $CONFIG["db"]["gl_email"][$mid]->Email) ? 1 : 3;
                                            $Ticket->Messages[0]->Text = $temail->Body;
                                            $Ticket->Messages[0]->Email = $temail->Email;
                                            $Ticket->Messages[0]->ChannelId = $temail->Id;
                                            $Ticket->Messages[0]->Fullname = $temail->Name;
                                            $Ticket->Messages[0]->Hash = strtoupper(str_replace(array("[","]"),"",$match));

                                            if(DEBUG_MODE)
                                                logit("SAVE EMAIL REPLY: " . $temail->Id . " - " . $temail->Subject);

                                            $Ticket->Messages[0]->Save($id,$temail->Created);
                                        }
                                    }
                                }

                                if(!$exists)
                                {
                                    if($group->TicketHandleUnknownEmails == 1)
                                    {
                                        if(DEBUG_MODE)
                                            logit("SAVE EMAIL: " . $temail->Id . " - " . $temail->Subject);
                                        $temail->Save();
                                    }
                                    else if($group->TicketHandleUnknownEmails == 0)
                                    {
                                        $Ticket = new UserTicket(getObjectId("ticket_id",DATABASE_TICKETS),true);
                                        $Ticket->Group = $group->Id;
                                        $Ticket->CreationType = 1;
                                        $Ticket->Language = strtoupper($CONFIG["gl_default_language"]);
                                        $Ticket->Messages[0]->Id = $Ticket->Id;
                                        $Ticket->Messages[0]->Type = 3;
                                        $Ticket->Messages[0]->Text = $temail->Body;
                                        $Ticket->Messages[0]->Email = $temail->Email;
                                        $Ticket->Messages[0]->ChannelId = $temail->Id;
                                        $Ticket->Messages[0]->Fullname = $temail->Name;
                                        $Ticket->Messages[0]->Created = $temail->Created;
                                        $Ticket->Save();

                                        languageSelect(strtolower($CONFIG["gl_default_language"]),true);

                                        Visitor::SendTicketAutoresponder($Ticket,strtoupper($CONFIG["gl_default_language"]),false);

                                        languageSelect("",true);
                                    }
                                }

                                foreach($temail->Attachments as $attid => $attdata)
                                {
                                    file_put_contents(PATH_UPLOADS.$attdata[0],$attdata[2]);
                                    processResource("SYSTEM",$attid,$attdata[0],3,$attdata[1],0,100,1);
                                    if(!$exists)
                                        $temail->SaveAttachment($attid);
                                    else if(!empty($Ticket))
                                        $Ticket->Messages[0]->ApplyAttachment($attid);
                                }
                            }
                        if(!$cronJob)
                            return;
                    }
        }
}

function mimeHeaderDecode($_string)
{
    if(strpos($_string,"=?") !== false)
    {
        $parts = explode("?",$_string);
        if(count($parts) >= 4)
        {
            if(strtolower($parts[2])=="b")
                $_string = @base64_decode($parts[3]);
            else if(strtolower($parts[2])=="q")
                $_string = @quoted_printable_decode($parts[3]);
            if(strtolower($parts[1])!="utf-8")
                $_string = @iconv(strtoupper($parts[1]),'UTF-8',$_string);
        }
        return $_string;
    }
    else
        return utf8_encode($_string);
}

function setTimeLimit($_time)
{
    @set_time_limit($_time);
    $_time = min(max(@ini_get('max_execution_time'),30),$_time);
    return $_time;
}

function downloadFromMailbox(&$_reload, $_type, $_server, $_port, $_password, $_account, $_secure, $_delete, $_test=false)
{
    global $CONFIG;
    $starttime = time();
    $executiontime = setTimeLimit(CALLER_TIMEOUT-10);
    loadLibrary("ZEND","Zend_Mail");
    $list = array();
    $config = array('host' => $_server, 'auth' => 'login', 'user' => $_account,'password' => $_password, 'port' => $_port);

    if(!empty($_secure))
        $config['ssl'] = 'SSL';

    try
    {
        if($_type == "IMAP")
        {
            loadLibrary("ZEND","Zend_Mail_Storage_Imap");
            $mail = new Zend_Mail_Storage_Imap($config);
        }
        else
        {
            loadLibrary("ZEND","Zend_Mail_Storage_Pop3");
            $mail = new Zend_Mail_Storage_Pop3($config);
        }
    }
    catch (Exception $e)
    {
        if($_test)
            throw $e;
        else
            handleError("111",$_server . " " . $_type . " mailbox connection error: " . $e->getMessage(),"functions.global.inc.php",0);
        return $list;
    }

    $message = null;
    $delete = array();
    $subject = "";
    try
    {
        $counter = 0;
        foreach ($mail as $mnum => $message)
        {
            if($_test)
                return count($mail);

            try
            {
                $temail = new TicketEmail();

                if($message->headerExists("subject"))
                    $subject = $temail->Subject = mimeHeaderDecode($message->Subject);

                if($message->headerExists("message-id"))
                    $temail->Id = str_replace(array("<",">"),"",$message->MessageId);

                if(empty($temail->Id))
                     $temail->Id = getId(32);

                if($_delete)
                    $delete[$mnum] = $temail->Id;

                if(strpos($message->From,"<") !== false)
                {
                    $fromparts = explode("<",str_replace(">","",$message->From));
                    if(!empty($fromparts[0]))
                        $temail->Name = str_replace(array("\""),"",mimeHeaderDecode(trim($fromparts[0])));
                    $temail->Email = trim($fromparts[1]);
                }
                else
                    $temail->Email = trim($message->From);

                if(strpos($message->To,"<") !== false)
                {
                    $toparts = explode("<",str_replace(">","",$message->To));
                    $temail->ReceiverEmail = trim($toparts[1]);
                }
                else
                    $temail->ReceiverEmail = trim($message->To);

                if($message->headerExists("reply-to"))
                    if(strpos($message->ReplyTo,"<") !== false)
                    {
                        $rtoparts = explode("<",str_replace(">","",$message->ReplyTo));
                        $temail->ReplyTo = trim($rtoparts[1]);
                    }
                    else
                        $temail->ReplyTo = trim($message->ReplyTo);



                $parts = array();
                if($message->isMultipart())
                    foreach (new RecursiveIteratorIterator($message) as $part)
                        $parts[] = $part;
                else
                    $parts[] = $message;

                foreach ($parts as $part)
                {
                    try
                    {
                        if($part->headerExists("content-type"))
                            $ctype = $part->contentType;
                        else
                            $ctype = 'text/html';

                        $charset = "";
                        $hparts = explode(";", str_replace(" ", "", $ctype));
                        foreach ($hparts as $hpart)
                            if (strpos(strtolower($hpart), "charset=") === 0)
                                $charset = trim(str_replace(array("charset=", "'", "\""), "", strtolower($hpart)));

                        if(DEBUG_MODE)
                            logit(" PROCESSING EMAIL / charset:" . $charset . " - " . $subject);

                        $isatt = (strpos(strtolower($ctype), "name=") !== false || strpos(strtolower($ctype), "filename=") !== false);

                        if (!$isatt && (($html = (strpos(strtolower($ctype), 'text/html') !== false)) || strpos(strtolower($ctype), 'text/plain') !== false))
                        {
                            $content = $part->getContent();

                            if($html)
                                $content = trim(html_entity_decode(strip_tags($content),ENT_COMPAT,"UTF-8"));

                            foreach ($part->getHeaders() as $name => $value)
                                if (strpos(strtolower($name), 'content-transfer-encoding') !== false && strpos(strtolower($value), 'quoted-printable') !== false)
                                    $content = quoted_printable_decode($content);
                                else if (strpos(strtolower($name), 'content-transfer-encoding') !== false && strpos(strtolower($value), 'base64') !== false)
                                    $content = base64_decode($content);

                            if (!$html || empty($temail->Body))
                            {
                                if(strpos(strtolower($charset), 'utf-8') === false && !empty($charset))
                                {
                                    if(DEBUG_MODE)
                                        logit(" PROCESSING EMAIL / iconv | " . strtoupper($charset) . " | " . 'UTF-8' . " | " . $subject);

                                    $temail->Body = @iconv(strtoupper($charset),'UTF-8',$content);

                                }
                                else if($html && empty($charset))
                                    $temail->Body = utf8_encode($content);
                                else
                                    $temail->Body = $content;
                            }

                        }
                        else
                        {

                            $fileid =
                            $filename = getId(32);
                            $filesid = $CONFIG["gl_lzid"] . "_" . $fileid;


                            foreach ($hparts as $hpart)
                                if (strpos(strtolower($hpart), "name=") === 0 || strpos(strtolower($hpart), "filename=") === 0)
                                    $filename = str_replace(array("name=", "'", "\""), "", strtolower($hpart));

                            $temail->Attachments[$fileid] = array($filesid, $filename, base64_decode($part->getContent()));
                        }

                    }
                    catch (Exception $e)
                    {
                        handleError("112",$_server . " imap Email Part Error: " . $e->getMessage() . ", email: " . $subject,"functions.global.inc.php",0);
                    }
                }

                $temail->Created = strtotime($message->Date);

                if((!is_numeric($temail->Created) || empty($temail->Created)) && $message->headerExists("delivery-date"))
                    $temail->Created = strtotime($message->DeliveryDate);

                if(!is_numeric($temail->Created) || empty($temail->Created))
                    $temail->Created = time();

                $list[] = $temail;

                if(((time()-$starttime) >= ($executiontime/2)) || $counter++ > DATA_ITEM_LOADS)
                {
                    $_reload = true;
                    break;
                }
            }
            catch(Exception $e)
            {
                if($_test)
                    throw $e;
                else
                    handleError("115",$_type . " Email Error: " . $e->getMessage() . ", email: " . $subject,"functions.global.inc.php",0);
            }
        }
        try
        {
            if(DEBUG_MODE)
                logit("DELETING EMAILS:" . count($delete));

            krsort($delete);
            foreach ($delete as $num => $id)
            {
                $mail->removeMessage($num);
            }
        }
        catch (Exception $e)
        {
            if($_test)
                throw $e;
            else
                handleError("114", $_type . " Email delete error: " . $e->getMessage() . ", email: " . $subject, "functions.global.inc.php", 0);
        }

    }
    catch(Exception $e)
    {
        if($_test)
            throw $e;
        else
            handleError("113",$_type . " Email Error: " . $e->getMessage() . ", email: " . $subject,"functions.global.inc.php",0);
    }
    return $list;
}

function loadLibrary($_type,$_name)
{
    if($_type == "ZEND")
    {
        if(!defined("LIB_ZEND_LOADED"))
        {
            define("LIB_ZEND_LOADED",true);
            $includePath = array();
            $includePath[] = './_lib/trdp/';
            $includePath[] = get_include_path();
            $includePath = implode(PATH_SEPARATOR,$includePath);
            set_include_path($includePath);
            require_once 'Zend/Loader.php';
        }
        if(!defined($_name))
        {
            define($_name,true);
            Zend_Loader::loadClass($_name);
        }
    }
}

function setDataProvider()
{
	global $CONFIG,$DB_CONNECTOR;
	if(!empty($CONFIG["gl_datprov"]))
	{
		define("DB_PREFIX",$CONFIG["gl_db_prefix"]);
		$DB_CONNECTOR = @mysql_connect($CONFIG["gl_db_host"], $CONFIG["gl_db_user"], $CONFIG["gl_db_pass"]);
		if($DB_CONNECTOR)
		{
			mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $DB_CONNECTOR);
			//mysql_set_charset('utf8', $DB_CONNECTOR); 
			//@mysql_query("SET NAMES 'utf8'", $DB_CONNECTOR);
			if(@mysql_select_db($CONFIG["gl_db_name"], $DB_CONNECTOR))
				define("DB_CONNECTION",true);
		}
	}
	
	if(!defined("DB_CONNECTION"))
		define("DB_CONNECTION",false);
	
	if(DB_CONNECTION)
		loadDatabaseConfig();
	
	return DB_CONNECTION;
}

function queryDB($_log,$_sql,$_serversetup=false,&$_errorCode=-1)
{
	global $DB_CONNECTOR,$DBA;
	if(!DB_CONNECTION && !(isServerSetup() && !empty($DB_CONNECTOR)))
	{
		if(DEBUG_MODE)
			logit("Query without connection: " . $_sql);

		return false;
	}

	$DBA++;
	
	$exectime = microtime();$exectime = explode(" ",$exectime);$exectime = $exectime[1] + $exectime[0];$starttime = $exectime;
	$result = @mysql_query($_sql, $DB_CONNECTOR);
	$exectime = microtime();$exectime = explode(" ",$exectime);$exectime = $exectime[1] + $exectime[0];	$endtime = $exectime;$totaltime = ($endtime - $starttime);
	
	$ignore = array("1146","1045","2003","1213","");
    if(!$result && !in_array(mysql_errno(),$ignore))
    {
        $_errorCode = mysql_errno();
	    if($_log)
		    logit(time() . " - " . $_errorCode . ": " . mysql_error() . "\r\n\r\nSQL: " . $_sql,LIVEZILLA_PATH  . "_log/sql.txt");
    }
	return $result;
}

function unloadDataProvider()
{
	global $DB_CONNECTOR;
	if($DB_CONNECTOR)
		@mysql_close($DB_CONNECTOR);
}

function is($_definition)
{
    if(defined($_definition))
        return constant($_definition);
    else
        return false;
}

function cronJobs($_asCronJob)
{
	global $CONFIG,$INTERNAL;
    $timeouts = array($CONFIG["poll_frequency_clients"] * 10,86400,86400*7,DATA_LIFETIME);
    $randoms = ($_asCronJob) ? array(0=>10,1=>10,2=>1): array(0=>150,1=>150,2=>5);

    if(rand(1,$randoms[0]) == 1)
	{
        if(!STATS_ACTIVE)
        {
            queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `last_active`<'".@mysql_real_escape_string(time()-$timeouts[1])."';");
            queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATOR_STATUS."` WHERE `".DB_PREFIX.DATABASE_OPERATOR_STATUS."`.`confirmed`<'".@mysql_real_escape_string(time()-$timeouts[1])."';");
        }
        else
            StatisticProvider::DeleteHTMLReports();

        queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id` = `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."`.`receiver_user_id`)");
        queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE (`html` = '0' OR `html` = '') AND `time` < " . @mysql_real_escape_string(time()-$timeouts[3]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `time` < " . @mysql_real_escape_string(time()-$timeouts[3]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `persistent` = '0' AND `time` < " . @mysql_real_escape_string(time()-$timeouts[1]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `repost` = '1' AND `time` < " . @mysql_real_escape_string(time()-$timeouts[0]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` WHERE `time` < ".@mysql_real_escape_string(time()-$timeouts[1]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."` WHERE `created` < " . @mysql_real_escape_string(time()-$timeouts[0]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` WHERE `webcam`=1 AND `time` < ".@mysql_real_escape_string(time()-$timeouts[0]));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_ALERTS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id` = `".DB_PREFIX.DATABASE_ALERTS."`.`receiver_user_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_FILES."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id` = `".DB_PREFIX.DATABASE_CHAT_FILES."`.`visitor_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `chat_id` = `".DB_PREFIX.DATABASE_CHAT_FORWARDS."`.`chat_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE `id` = `".DB_PREFIX.DATABASE_CHAT_REQUESTS."`.`receiver_browser_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_GOALS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_GOALS."` WHERE `id` = `".DB_PREFIX.DATABASE_STATS_AGGS_GOALS."`.`goal`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_STATS_AGGS_PAGES."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` WHERE `id` = `".DB_PREFIX.DATABASE_STATS_AGGS_PAGES."`.`url`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_TICKET_CUSTOMS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_TICKETS."` WHERE `id` = `".DB_PREFIX.DATABASE_TICKET_CUSTOMS."`.`ticket_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_TICKET_EDITORS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_TICKETS."` WHERE `id` = `".DB_PREFIX.DATABASE_TICKET_EDITORS."`.`ticket_id`)");
        queryDB(true,"DELETE `".DB_PREFIX.DATABASE_TICKETS."` FROM `".DB_PREFIX.DATABASE_TICKETS."` INNER JOIN `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` ON `".DB_PREFIX.DATABASE_TICKETS."`.`id`=`".DB_PREFIX.DATABASE_TICKET_MESSAGES."`.`ticket_id` WHERE `deleted`=1 AND `time` < " . @mysql_real_escape_string(time()-$timeouts[3]) .";");
        queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_TICKETS."` WHERE `id` = `".DB_PREFIX.DATABASE_TICKET_MESSAGES."`.`ticket_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `visit_id` = `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."`.`visit_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE `id` = `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`browser_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id` = `".DB_PREFIX.DATABASE_VISITOR_CHATS."`.`visitor_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `chat_id` = `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."`.`chat_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_GOALS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id` = `".DB_PREFIX.DATABASE_VISITOR_GOALS."`.`visitor_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE `id` = `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."`.`receiver_browser_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OVERLAY_BOXES."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE `id` = `".DB_PREFIX.DATABASE_OVERLAY_BOXES."`.`receiver_browser_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_GROUP_MEMBERS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_GROUPS."` WHERE `id` = `".DB_PREFIX.DATABASE_GROUP_MEMBERS."`.`group_id`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_LOCALIZATIONS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` WHERE `id` = `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_LOCALIZATIONS."`.`tid`)");
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` WHERE NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` WHERE `id` = `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."`.`tid`)");
        queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_TICKET_EMAILS."` WHERE `deleted`=1 AND `edited` < " . @mysql_real_escape_string(time()-$timeouts[3]));
        queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `closed`=0 AND NOT EXISTS (SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `chat_id` = `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."`.`chat_id`)");

        if($CONFIG["gl_adct"] != 1)
		{
			if(!empty($CONFIG["gl_rm_chats"]))
				queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_chats_time"]));
			if(!empty($CONFIG["gl_rm_rt"]))
				queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE `time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_rt_time"]));
			if(!empty($CONFIG["gl_rm_om"]))
			{
				queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_TICKET_EDITORS."` WHERE `time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_om_time"]));
				queryDB(true,"DELETE `".DB_PREFIX.DATABASE_TICKET_MESSAGES."`,`".DB_PREFIX.DATABASE_TICKETS."` FROM `".DB_PREFIX.DATABASE_TICKETS."` INNER JOIN `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE `".DB_PREFIX.DATABASE_TICKETS."`.`id` = `".DB_PREFIX.DATABASE_TICKET_MESSAGES."`.`ticket_id` AND `".DB_PREFIX.DATABASE_TICKET_MESSAGES."`.`time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_om_time"]));
			}
            if(!empty($INTERNAL) && !empty($CONFIG["gl_rm_bc"]))
                foreach($INTERNAL as $sid => $operator)
                    if($operator->IsBot)
                        queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `internal_id`='".@mysql_real_escape_string($sid)."' AND `time` < " . @mysql_real_escape_string(time()-$CONFIG["gl_rm_bc_time"]));
		}

		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `discarded`=1 AND `type` > 2 AND `edited` < " . @mysql_real_escape_string(time()-$timeouts[3])));
			while($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$resultb = queryDB(true,"SELECT count(value) as linked FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `value`='". @mysql_real_escape_string($row["value"])."';");
				$rowb = mysql_fetch_array($resultb, MYSQL_BOTH);
				if($rowb["linked"] == 1)
					@unlink(PATH_UPLOADS . $row["value"]);
			}
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `discarded`='1' AND `edited` < " . @mysql_real_escape_string(time()-$timeouts[3]));
    }

    if(rand(1,$randoms[2]) == 1)
	{
        if(empty($CONFIG["gl_rm_chats"]) || !empty($CONFIG["gl_rm_chats_time"]))
		    sendChatTranscripts();
        downloadEmails($_asCronJob);
	}
}

function closeArchiveEntry($_chatId,$_externalFullname,$_externalId,$_internalId,$_groupId,$_email,$_company,$_phone,$_host,$_ip,$_question,$_transcriptSent=false,$_waitingtime,$_startResult,$_endResult)
{
	global $CONFIG;
	$result = queryDB(true,"SELECT `voucher_id`,`endtime`,`transcript_text`,`iso_language`,`time` FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `chat_id`='".@mysql_real_escape_string($_chatId)."' LIMIT 1;");
	$row = mysql_fetch_array($result, MYSQL_BOTH);
	languageSelect($row["iso_language"]);

	$etpl = $row["transcript_text"];
    $etpl = applyReplacements($etpl,true,false);

    $filter = new ChatFilter();
    $filter->Generate($_chatId,$_externalFullname,true,true,$_question,$row["time"]);
    $filter->PlainText = applyReplacements($filter->PlainText,true,false);
    $filter->HTML = applyReplacements($filter->HTML,true,false);

	if(!empty($filter->PlainText))
	{
		$etpl = str_replace("%localdate%",date("r",$filter->FirstPost),$etpl);
		if(strpos($etpl,"%transcript%")===false && strpos($etpl,"%mailtext%")===false)
			$etpl .= $filter->PlainText;
		else if(strpos($etpl,"%transcript%")!==false)
			$etpl = str_replace("%transcript%",$filter->PlainText,$etpl);
		else if(strpos($etpl,"%mailtext%")!==false)
			$etpl = str_replace("%mailtext%",$filter->PlainText,$etpl);
	}
	else
		$etpl = "";

    $etpl = str_replace(array("%email%","%eemail%")," " . $_email,$etpl);
    $etpl = str_replace(array("%fullname%","%efullname%")," " . $_externalFullname,$etpl);
    $etpl = str_replace("%rating%",getRatingAVGFromChatId($_chatId),$etpl);

	$name = (!empty($_externalFullname)) ? ",`fullname`='".@mysql_real_escape_string($_externalFullname)."'" : "";

    queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `external_id`='".@mysql_real_escape_string($_externalId)."',`closed`='".@mysql_real_escape_string(time())."'".$name.",`internal_id`='".@mysql_real_escape_string($_internalId)."',`group_id`='".@mysql_real_escape_string($_groupId)."',`html`='".@mysql_real_escape_string($filter->HTML)."',`plaintext`='".@mysql_real_escape_string($filter->PlainText)."',`transcript_text`='".@mysql_real_escape_string($etpl)."',`email`='".@mysql_real_escape_string($_email)."',`company`='".@mysql_real_escape_string($_company)."',`phone`='".@mysql_real_escape_string($_phone)."',`host`='".@mysql_real_escape_string($_host)."',`ip`='".@mysql_real_escape_string($_ip)."',`gzip`=0,`wait`='".@mysql_real_escape_string($_waitingtime)."',`accepted`='".@mysql_real_escape_string($_startResult)."',`ended`='".@mysql_real_escape_string($_endResult)."',`transcript_sent`='".@mysql_real_escape_string(((((empty($CONFIG["gl_soct"]) && empty($CONFIG["gl_scct"]) && empty($CONFIG["gl_scto"]) && empty($CONFIG["gl_sctg"])) || empty($etpl) || $filter->ElementCount==0 || $_transcriptSent)) ? "1" : "0"))."',`question`='".@mysql_real_escape_string(cutString($_question,255))."' WHERE `chat_id`='".@mysql_real_escape_string($_chatId)."' AND `closed`=0 LIMIT 1;");

    $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE `channel_id`='".@mysql_real_escape_string($_chatId)."';");
    if($result && $rowc = mysql_fetch_array($result, MYSQL_BOTH))
    {
        $Ticket = new UserTicket($rowc["ticket_id"],true);
        $Ticket->LinkChat($rowc["channel_id"],$rowc["id"]);
    }
}

function getPredefinedMessage($_list, $_language)
{
    $sel_message = null;
    foreach($_list as $message)
    {
        if(($message->IsDefault && (!$message->BrowserIdentification || empty($_language))) || ($message->BrowserIdentification && !empty($_language) && $_language == $message->LangISO))
        {
            $sel_message = $message;
            break;
        }
        else if($message->IsDefault)
            $sel_message = $message;
    }
    return $sel_message;
}

function getSubject($_subject,$_email,$_username,$_group,$_chatid,$_company,$_phone,$_ip,$_question,$_customs=null)
{
    global $CONFIG,$INPUTS,$LZLANG,$GROUPS;
    $_subject = str_replace(array("%website_name%","%SERVERNAME%"),$CONFIG["gl_site_name"],$_subject);
    $_subject = str_replace(array("%external_name%","%USERNAME%"),$_username,$_subject);
    $_subject = str_replace(array("%external_email%","%USEREMAIL%"),$_email,$_subject);
    $_subject = str_replace(array("%external_company%","%USERCOMPANY%"),$_company,$_subject);
    $_subject = str_replace("%external_phone%",$_phone,$_subject);
    $_subject = str_replace("%external_ip%",$_ip,$_subject);
    $_subject = str_replace(array("%question%","%USERQUESTION%","%mailtext%"),$_question,$_subject);
    $_subject = str_replace(array("%group_name%","%group_id%","%TARGETGROUP%"),$_group,$_subject);
    $_subject = str_replace("%group_description%",((isset($GROUPS[$_group])) ? $GROUPS[$_group]->Description : $_group),$_subject);
    $_subject = str_replace(array("%chat_id%","%CHATID%"),$_chatid,$_subject);

    foreach($INPUTS as $index => $input)
        if($input->Active && $input->Custom)
        {
            if($input->Type == "CheckBox")
                $_subject = str_replace("%custom".($index)."%",((!empty($_customs[$index])) ? $LZLANG["client_yes"] : $LZLANG["client_no"]),$_subject);
            else if(!empty($_customs[$index]))
                $_subject = str_replace("%custom".($index)."%",$input->GetClientValue($_customs[$index]),$_subject);
            else
                $_subject = str_replace("%custom".($index)."%","",$_subject);
        }
        else
            $_subject = str_replace("%custom".($index)."%","",$_subject);

    return applyReplacements(str_replace("\n","",$_subject),true,false);
}

function closeChats()
{
	global $INTERNAL,$CONFIG;
	$result = queryDB(false,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `closed`=0 AND `transcript_sent`=0;");
    while($row = mysql_fetch_array($result, MYSQL_BOTH))
    {
        $results = queryDB(false,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `chat_id`='".@mysql_real_escape_string($row["chat_id"])."' AND (`exit`>0 OR `last_active`<".(time()-$CONFIG["timeout_track"]).");");
        if($results && $rows = mysql_fetch_array($results, MYSQL_BOTH))
        {
            $botchat = !empty($row["internal_id"]) && $INTERNAL[$row["internal_id"]]->IsBot;

            if((empty($rows["exit"]) && $botchat) || (!empty($rows["exit"]) && !$botchat))
            {
                $chat = new VisitorChat($rows);
                $chat->LoadMembers();

                $startResult = 0;
                $endResult = 0;
                $waitingtime = 0;

                if($botchat)
                {
                    $chat->CloseChat();
                    $lastOp = $row["internal_id"];
                    $waitingtime = 1;
                    $startResult = 1;
                }
                else
                {
                    $lastOp = $chat->GetLastOperator($waitingtime,$startResult,$endResult);
                }
                closeArchiveEntry($row["chat_id"],$rows["fullname"],$rows["visitor_id"],$lastOp,$rows["request_group"],$rows["email"],$rows["company"],$rows["phone"],$row["host"],$row["ip"],$rows["question"],(empty($CONFIG["gl_sctb"]) && $botchat),$waitingtime,$startResult,$endResult);
            }
        }
    }
}

function sendChatTranscripts($_custom=false)
{
	global $CONFIG,$INTERNAL,$GROUPS;
	initData(true,false,false,false,false,false,false,true);
	
	closeChats();
    $defmailbox=Mailbox::GetDefaultOutgoing();

	$result = queryDB(false,"SELECT `voucher_id`,`subject`,`customs`,`internal_id`,`transcript_text`,`transcript_receiver`,`email`,`chat_id`,`fullname`,`group_id` FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `endtime`>0 AND `closed`>0 AND `transcript_sent`=0 LIMIT 1;");
	if($result)
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `transcript_sent`=1 WHERE `chat_id`='". @mysql_real_escape_string($row["chat_id"])."' LIMIT 1;");
			$rcvs = str_replace(array("%fullname%","%efullname%")," " . $row["fullname"],$row["transcript_text"]);
			$rcvs = str_replace(array("%email%","%eemail%"),((!empty($row["email"])) ? " ":"") . $row["email"],$rcvs);
            $rcvs = str_replace("%rating%",getRatingAVGFromChatId($row["chat_id"]),$rcvs);
			$subject = $row["subject"];

			if(empty($CONFIG["gl_pr_nbl"]))
				$rcvs .= base64_decode("DQoNCg0KcG93ZXJlZCBieSBMaXZlWmlsbGEgTGl2ZSBTdXBwb3J0IFtodHRwOi8vd3d3LmxpdmV6aWxsYS5uZXRd");
			
			if((!empty($CONFIG["gl_soct"]) || $_custom) && !empty($row["transcript_receiver"]))
				sendMail($defmailbox,$row["transcript_receiver"],$defmailbox->Email,$rcvs,$subject);

			if(!empty($CONFIG["gl_scto"]) && !$_custom)
			{
				initData(true);
				$receivers = array();
				$resulti = queryDB(true,"SELECT `sender`,`receiver` FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `chat_id`='". @mysql_real_escape_string($row["chat_id"])."';");
				if($resulti)
					while($rowi = mysql_fetch_array($resulti, MYSQL_BOTH))
					{
						if(!empty($INTERNAL[$rowi["sender"]]) && !in_array($rowi["sender"],$receivers))
							$receivers[] = $rowi["sender"];
						else if(!empty($INTERNAL[$rowi["receiver"]]) && !in_array($rowi["receiver"],$receivers))
							$receivers[] = $rowi["receiver"];
						else
							continue;
						sendMail($defmailbox,$INTERNAL[$receivers[count($receivers)-1]]->Email,$defmailbox->Email,$rcvs,$subject);
					}
			}
			if(!empty($CONFIG["gl_sctg"]) && !$_custom)
			{
				initData(false,true);
				sendMail($defmailbox,$GROUPS[$row["group_id"]]->Email,$defmailbox->Email,$rcvs,$subject);
			}
			if(!empty($defmailbox) && !empty($CONFIG["gl_scct"]))
				sendMail($defmailbox,$CONFIG["gl_scct"],$defmailbox->Email,$rcvs,$subject);
			
			if(!empty($row["voucher_id"]))
			{
				$ticket = new CommercialChatVoucher(null,$row["voucher_id"]);
				$ticket->Load();
				$ticket->SendStatusEmail();
			}
		}
	if(!empty($CONFIG["gl_rm_chats"]) && $CONFIG["gl_rm_chats_time"] == 0)
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `transcript_sent` = '1';");
}

function getNoName($_basename)
{
    $mod = 111;
    for ($i = 0; $i < strlen($_basename); $i++)
    {
        $digit = substr($_basename,$i,1);

        if(is_numeric($digit))
        {
            $mod = ($mod + ($mod * (16 + $digit)) % 1000);
            if ($mod % 10 == 0)
                $mod += 1;
        }
    }
    return substr($mod,strlen($mod)-4,4);
}

function getRatingAVGFromChatId($_chatId,$ratav = "-")
{
    $resultr = queryDB(false,"SELECT * FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE `chat_id`='". @mysql_real_escape_string($_chatId)."' LIMIT 1;");
    if($resultr)
        if($rowr = mysql_fetch_array($resultr, MYSQL_BOTH))
        {
            $ratav = round((($rowr["qualification"]+$rowr["politeness"])/2),1);
        }
    return $ratav;
}

function getResource($_id)
{
	if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `id`='".@mysql_real_escape_string($_id)."' LIMIT 1;"))
		if($row = mysql_fetch_array($result, MYSQL_BOTH))
			return $row;
	return null;
}

function getPosts($_receiver)
{
	$posts = array();
	if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `receiver`='".@mysql_real_escape_string($_receiver)."' AND `received`=0 ORDER BY `time` ASC, `micro` ASC;"))
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			$posts[] = $row;
	return $posts;
}

function getDirectory($_dir,$_oddout,$_ignoreSource=false)
{
	$files = array();
	if(!@is_dir($_dir))
		return $files;
	$handle=@opendir($_dir);
	while ($filename = @readdir ($handle)) 
	   	if ($filename != "." && $filename != ".." && ($_oddout == false || !stristr($filename,$_oddout)))
			if($_oddout != "." || ($_oddout == "." && @is_dir($_dir . "/" . $filename)))
	       		$files[]=$filename;
	@closedir($handle);
	return $files;
}

function getValueId($_database,$_column,$_value,$_canBeNumeric=true,$_maxlength=null)
{
	if(!$_canBeNumeric && is_numeric($_value))
		return $_value;
		
	if($_maxlength != null && strlen($_value) > $_maxlength)
		$_value = substr($_value,0,$_maxlength);

	queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.$_database."` (`id`, `".$_column."`) VALUES (NULL, '".@mysql_real_escape_string($_value)."');");
	$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.$_database."` WHERE `".$_column."`='".@mysql_real_escape_string($_value)."';"), MYSQL_BOTH);
	
	if(is_numeric($row["id"]) || $_value == "INVALID_DATA")
		return $row["id"];
	else
		return getValueId($_database,$_column,"INVALID_DATA",$_canBeNumeric,$_maxlength);
}

function getIdValue($_database,$_column,$_id,$_unknown=false)
{
	$row = mysql_fetch_array(queryDB(true,"SELECT `".$_column."` FROM `".DB_PREFIX.$_database."` WHERE `id`='".@mysql_real_escape_string($_id)."' LIMIT 1;"));
	if($_unknown && empty($row[$_column]))
		return "<!--lang_stats_unknown-->";
	return $row[$_column];
}

function jokerCompare($_template,$_comparer)
{
	if($_template=="*")
		return true;
		
	$spacer = md5(rand());
	$_template = str_replace("?",$spacer,strtolower($_template));
	$_comparer = str_replace("?",$spacer,strtolower($_comparer));
	$_template = str_replace("*","(.*)",$_template);
	return (preg_match("(".$spacer.$_template.$spacer.")",$spacer.$_comparer.$spacer)>0);
}

function processResource($_userId,$_resId,$_value,$_type,$_title,$_disc,$_parentId,$_rank,$_size=0,$_tags="")
{
	if($_size == 0)
		$_size = strlen($_title);
	$result = queryDB(true,"SELECT `id`,`value` FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `id`='".@mysql_real_escape_string($_resId)."'");
	if(@mysql_num_rows($result) == 0)
		queryDB(true,$result = "INSERT INTO `".DB_PREFIX.DATABASE_RESOURCES."` (`id`,`owner`,`editor`,`value`,`edited`,`title`,`created`,`type`,`discarded`,`parentid`,`rank`,`size`,`tags`) VALUES ('".@mysql_real_escape_string($_resId)."','".@mysql_real_escape_string($_userId)."','".@mysql_real_escape_string($_userId)."','".@mysql_real_escape_string($_value)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($_title)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($_type)."','0','".@mysql_real_escape_string($_parentId)."','".@mysql_real_escape_string($_rank)."','".@mysql_real_escape_string($_size)."','".@mysql_real_escape_string($_tags)."')");
	else
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		queryDB(true,$result = "UPDATE `".DB_PREFIX.DATABASE_RESOURCES."` SET `value`='".@mysql_real_escape_string($_value)."',`editor`='".@mysql_real_escape_string($_userId)."',`tags`='".@mysql_real_escape_string($_tags)."',`title`='".@mysql_real_escape_string($_title)."',`edited`='".@mysql_real_escape_string(time())."',`discarded`='".@mysql_real_escape_string(parseBool($_disc,false))."',`parentid`='".@mysql_real_escape_string($_parentId)."',`rank`='".@mysql_real_escape_string($_rank)."',`size`='".@mysql_real_escape_string($_size)."' WHERE id='".@mysql_real_escape_string($_resId)."' LIMIT 1");
		if(!empty($_disc) && ($_type == RESOURCE_TYPE_FILE_INTERNAL || $_type == RESOURCE_TYPE_FILE_EXTERNAL) && @file_exists("./uploads/" . $row["value"]) && strpos($row["value"],"..")===false)
			@unlink("./uploads/" . $row["value"]);
	}
}

function getBrowserLocalization($country = "")
{
	global $LANGUAGES,$COUNTRIES;
	initData(false,false,false,false,false,true,true);
	$base = @$_SERVER["HTTP_ACCEPT_LANGUAGE"];
	
	$language = str_replace(array(",","_"," "),array(";","-",""),((!empty($_GET[GET_EXTERN_USER_LANGUAGE])) ? strtoupper(base64UrlDecode($_GET[GET_EXTERN_USER_LANGUAGE])) : ((!empty($base)) ? strtoupper($base) : "")));
	if(strlen($language) > 5 || strpos($language,";") !== false)
	{
		$parts = explode(";",$language);
		if(count($parts) > 0)
			$language = $parts[0];
		else
			$language = substr($language,0,5);
	}
	if(strlen($language) >= 2)
	{
		$parts = explode("-",$language);
		if(!isset($LANGUAGES[$language]))
		{
			$language = $parts[0];
			if(!isset($LANGUAGES[$language]))
			{
				if(DEBUG_MODE)
					logit(@$base . " - " . $language,LIVEZILLA_PATH . "_log/missing_language.txt");
				$language = "";
			}
		}
		if(count($parts)>1 && isset($COUNTRIES[$parts[1]]))
			$country = $parts[1];
	}
	else if(strlen($language) < 2)
		$language = "";
	return array($language,$country);
}

function createFileBaseFolders($_owner,$_internal)
{
	if($_internal)
	{
		processResource($_owner,3,"%%_Files_%%",0,"%%_Files_%%",0,1,1);
		processResource($_owner,4,"%%_Internal_%%",0,"%%_Internal_%%",0,3,2);
	}
	else
	{
		processResource($_owner,3,"%%_Files_%%",0,"%%_Files_%%",0,1,1);
		processResource($_owner,5,"%%_External_%%",0,"%%_External_%%",0,3,2);
	}
}

function getSystemTimezone()
{
	global $CONFIG;
	
	if(!empty($CONFIG["gl_tizo"]))
		return $CONFIG["gl_tizo"];

    $iTime = time();
    $arr = @localtime($iTime);
    $arr[5] += 1900;
    $arr[4]++;
	
	if(!empty($arr[8]))
		$arr[2]--;

    $iTztime = @gmmktime($arr[2], $arr[1], $arr[0], $arr[4], $arr[3], $arr[5]);
    $offset = doubleval(($iTztime-$iTime)/(60*60));
    $zonelist =
    array
    (
        'Kwajalein' => -12.00,
        'Pacific/Midway' => -11.00,
        'Pacific/Honolulu' => -10.00,
        'America/Anchorage' => -9.00,
        'America/Los_Angeles' => -8.00,
        'America/Denver' => -7.00,
        'America/Tegucigalpa' => -6.00,
        'America/New_York' => -5.00,
        'America/Caracas' => -4.30,
        'America/Halifax' => -4.00,
        'America/St_Johns' => -3.30,
        'America/Argentina/Buenos_Aires' => -3.00,
        'America/Sao_Paulo' => -3.00,
        'Atlantic/South_Georgia' => -2.00,
        'Atlantic/Azores' => -1.00,
        'Europe/Dublin' => 0,
        'Europe/Belgrade' => 1.00,
        'Europe/Minsk' => 2.00,
        'Asia/Kuwait' => 3.00,
        'Asia/Tehran' => 3.30,
        'Asia/Muscat' => 4.00,
        'Asia/Yekaterinburg' => 5.00,
        'Asia/Kolkata' => 5.30,
        'Asia/Katmandu' => 5.45,
        'Asia/Dhaka' => 6.00,
        'Asia/Rangoon' => 6.30,
        'Asia/Krasnoyarsk' => 7.00,
        'Asia/Brunei' => 8.00,
        'Asia/Seoul' => 9.00,
        'Australia/Darwin' => 9.30,
        'Australia/Canberra' => 10.00,
        'Asia/Magadan' => 11.00,
        'Pacific/Fiji' => 12.00,
        'Pacific/Tongatapu' => 13.00
    );
    $index = array_keys($zonelist, $offset);
    if(sizeof($index)!=1)
        return false;
    return $index[0];
}

function isnull($_var)
{
	return empty($_var);
}

function str_words_count(&$_text)
{
    global $CONFIG;
    if(empty($CONFIG["gl_pr_nbl"]))
        $_text .= base64_decode("DQoNCg0KcG93ZXJlZCBieSBMaXZlWmlsbGEgTGl2ZSBTdXBwb3J0IFtodHRwOi8vd3d3LmxpdmV6aWxsYS5uZXRd");
    return str_word_count($_text);
}

function isint($_int)
{
    return (preg_match( '/^\d*$/'  , $_int) == 1);
}

function getObjectId($_field,$_database)
{
	$result = queryDB(true,"SELECT `".$_field."`,(SELECT MAX(`id`) FROM `".DB_PREFIX.$_database."`) as `used_".$_field."` FROM `".DB_PREFIX.DATABASE_INFO."`");
	$row = mysql_fetch_array($result, MYSQL_BOTH);
	$max = max($row[$_field],$row["used_" . $_field]);
	$tid = $max+1;
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_INFO."` SET `".$_field."`='".@mysql_real_escape_string($tid)."';");
	return $tid;
}

function formatTimeSpan($_seconds,$_negative=false)
{
	if($_seconds < 0)
	{
		$_negative = true;
		$_seconds *= -1;
	}
	
	$days = floor($_seconds / 86400);
	$_seconds = $_seconds - ($days * 86400);
	$hours = floor($_seconds / 3600);
	$_seconds = $_seconds - ($hours * 3600);
	$minutes = floor($_seconds / 60);
	$_seconds = $_seconds - ($minutes * 60);
	
	$string = "";
	if($days > 0)$string .= $days.".";
	if($hours >= 10)$string .= $hours.":";
	else if($hours < 10)$string .= "0".$hours.":";
	if($minutes >= 10)$string .= $minutes.":";
	else if($minutes < 10)$string .= "0".$minutes.":";
	if($_seconds >= 10)$string .= $_seconds;
	else if($_seconds < 10)$string .= "0".$_seconds;
	
	if($_negative)
		return "-" . $string;
	return $string;
}
disableMagicQuotes();
loadConfig();
?>
