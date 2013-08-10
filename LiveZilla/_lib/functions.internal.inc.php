<?php
/****************************************************************************************
* LiveZilla functions.internal.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function validate()
{
	global $INTERNAL,$GROUPS,$RESPONSE,$CONFIG;
	if(!empty($CONFIG["gl_rhts"]) && getScheme() != SCHEME_HTTP_SECURE)
	{
		define("AUTH_RESULT",LOGIN_REPLY_HTTPS);
	}
	else if(DB_CONNECTION || SERVERSETUP)
	{
		if(!empty($_POST[POST_INTERN_AUTHENTICATION_USERID]) && !empty($_POST[POST_INTERN_AUTHENTICATION_PASSWORD]))
		{
			foreach($INTERNAL as $sysId => $internuser)
			{
				if(strtolower($internuser->UserId) == strtolower($_POST[POST_INTERN_AUTHENTICATION_USERID]))
				{
					if($internuser->SaveLoginAttempt(md5($_POST[POST_INTERN_AUTHENTICATION_PASSWORD])))
					{
						if(!empty($internuser->Password) && $internuser->Password == md5($_POST[POST_INTERN_AUTHENTICATION_PASSWORD]))
						{
							define("CALLER_SYSTEM_ID",$sysId);
							$internuser->DeleteLoginAttempts();
							$internuser->LoadPredefinedMessages($sysId,false);
							
							if(isset($_POST[POST_INTERN_NEW_PASSWORD]))
							{
								$INTERNAL[CALLER_SYSTEM_ID]->ChangePassword($_POST[POST_INTERN_NEW_PASSWORD],true,true);
								$RESPONSE->Authentications = "<val userid=\"".base64_encode(CALLER_SYSTEM_ID)."\" pass=\"".base64_encode($_POST[POST_INTERN_NEW_PASSWORD])."\" />\r\n";
							}
							if(empty($_POST["p_db_no_req"]) && !DB_CONNECTION)
							{
								define("AUTH_RESULT",LOGIN_REPLY_DB);
								break;
							}
							if(!LOGIN && !SERVERSETUP)
							{
                                if($internuser->Deactivated)
                                {
                                    define("AUTH_RESULT",LOGIN_REPLY_ACCOUNT_DEACTIVATED);
                                    break;
                                }
								if(!$internuser->ClientWeb && $internuser->LastActive < (time()-$CONFIG["timeout_clients"]) && $_POST[POST_INTERN_AUTHENTICATION_LOGINID] == $internuser->LoginId)
								{
									define("AUTH_RESULT",LOGIN_REPLY_SESSION_TIMEOUT);
									break;
								}
								if($internuser->SignOffRequest)
								{
									define("AUTH_RESULT",LOGIN_REPLY_SIGN_OFF_REQUEST);
									break;
								}
								if(!empty($internuser->LoginId) && !empty($_POST[POST_INTERN_AUTHENTICATION_LOGINID]) && $_POST[POST_INTERN_AUTHENTICATION_LOGINID] != $internuser->LoginId)
								{
									define("AUTH_RESULT",LOGIN_REPLY_BAD_COMBINATION);
									break;
								}
							}
							else if(LOGIN && !SERVERSETUP)
							{
                                $internuser->ClientMobile = !empty($_POST["p_app"]);
                                $internuser->ClientWeb = !empty($_POST["p_web"]);

                                if($internuser->Deactivated)
                                {
                                    define("AUTH_RESULT",LOGIN_REPLY_ACCOUNT_DEACTIVATED);
                                    break;
                                }
								else if($internuser->SignOffRequest)
								{
									$internuser->SignOff(false);
									define("AUTH_RESULT",LOGIN_REPLY_SIGN_OFF_REQUEST);
									break;
								}
								else if(empty($_POST[POST_INTERN_IGNORE_SIGNED_ON]) && $internuser->LastActive > (time()-$CONFIG["timeout_clients"]) && !empty($internuser->LoginId) && $_POST[POST_INTERN_AUTHENTICATION_LOGINID] != $internuser->LoginId)
								{
									define("AUTH_RESULT",LOGIN_REPLY_ALREADY_ONLINE);
									break;
								}
								else if($internuser->PasswordChangeRequest)
								{
									define("AUTH_RESULT",LOGIN_REPLY_CHANGE_PASS);
									break;
								}
							}
							else if(SERVERSETUP && $internuser->Level != USER_LEVEL_ADMIN)
							{
								if(!(in_array($CONFIG["gl_host"],$internuser->WebsitesUsers) && !empty($_POST[POST_INTERN_GET_MANAGEMENT])) && !(in_array($CONFIG["gl_host"],$internuser->WebsitesConfig) && empty($_POST[POST_INTERN_GET_MANAGEMENT])))
								{
									define("AUTH_RESULT",LOGIN_REPLY_NOADMIN);
									break;
								}
							}

							define("VALIDATED",true);
							
							if(!LOGOFF && isset($_POST[POST_INTERN_AUTHENTICATION_LOGINID]))
								$internuser->LoginId = $_POST[POST_INTERN_AUTHENTICATION_LOGINID];
							elseif(LOGOFF)
								$internuser->LoginId = null;

							define("AUTH_RESULT",LOGIN_REPLY_SUCCEEDED);
							break;
						}
						else
						{
							break;
						}
					}
				}
			}
		}
	}
	else
		define("AUTH_RESULT",LOGIN_REPLY_DB);

	if(isValidated() && LOGIN)
	{
		$INTERNAL[CALLER_SYSTEM_ID]->IP = getIP();
		$INTERNAL[CALLER_SYSTEM_ID]->FirstActive = time();
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes = array();
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload = array();
		$isex = !empty($INTERNAL[CALLER_SYSTEM_ID]->Groups) && $GROUPS[$INTERNAL[CALLER_SYSTEM_ID]->Groups[0]]->IsExternal;
		$RESPONSE->Login = $INTERNAL[CALLER_SYSTEM_ID]->GetLoginReply($isex,getTimeDifference($_POST[POST_INTERN_CLIENT_TIME]));
	}
	if(!defined("AUTH_RESULT"))
		define("AUTH_RESULT",LOGIN_REPLY_BAD_COMBINATION);
}

function receiveFile($id = FILE_ACTION_NONE)
{
	global $RESPONSE,$INTERNAL;
	if(isset($_POST[POST_INTERN_FILE_TYPE]) && $_POST[POST_INTERN_FILE_TYPE] == FILE_TYPE_USERFILE)
	{
		if(!empty($_GET["QRD_TFILE"]))
			$_FILES["file"]["name"] = base64_decode($_GET["QRD_TFILE"]);
	
		if(empty($_GET["QRD_TRESID"]))
			$fid = md5($_FILES["file"]["name"] . CALLER_SYSTEM_ID . time());
		else
			$fid = base64_decode($_GET["QRD_TRESID"]);
			
		$filemask = CALLER_SYSTEM_ID . "_" . $fid;
		
		if(empty($_GET["QRD_PARENT_ID"]))
		{
			createFileBaseFolders(CALLER_SYSTEM_ID,true);
			processResource(CALLER_SYSTEM_ID,CALLER_SYSTEM_ID,$INTERNAL[CALLER_SYSTEM_ID]->Fullname,0,$INTERNAL[CALLER_SYSTEM_ID]->Fullname,0,4,3);
			$parentId = CALLER_SYSTEM_ID;
			$rank = 4;
		}
		else
		{
			$parentId = $_GET["QRD_PARENT_ID"];
			$rank = $_GET["QRD_RANK"];
		}
		processResource(CALLER_SYSTEM_ID,$fid,$filemask,3,$_FILES["file"]["name"],0,$parentId,$rank,$_FILES["file"]["size"]);
		if(@move_uploaded_file($_FILES["file"]["tmp_name"], PATH_UPLOADS.$filemask))
			$id = FILE_ACTION_SUCCEEDED;
		else
			$id = FILE_ACTION_ERROR;
	}
	$RESPONSE->SetStandardResponse($id,base64_encode($fid));
}

function processActions()
{
	global $CONFIG,$INTERNAL;
	require(LIVEZILLA_PATH . "_lib/functions.internal.process.inc.php");
	processChatActions();
	processAuthentications();
	processStatus();
	processRequests();
	processForwards();
	processWebsitePushs();
	processBotFeedLists();
	processFilters();
	processProfile();
	processProfilePictures();
	processWebcamPictures();
	processAlerts();
	processPermissions();
	processTicketActions();
	processExternalReloads();
	processReceivedPosts();
	processCancelInvitation();
	processEvents();
	processGoals();
	if(SERVERSETUP && $INTERNAL[CALLER_SYSTEM_ID]->Level == USER_LEVEL_ADMIN || in_array($CONFIG["gl_host"],$INTERNAL[CALLER_SYSTEM_ID]->WebsitesConfig))
		processButtonIcons();
}

function buildSystem()
{
	global $INTERNAL;
	require_once(LIVEZILLA_PATH . "_lib/functions.internal.build.inc.php");
	$INTERNAL[CALLER_SYSTEM_ID]->GetExternalObjects();

	buildIntern();
	buildExtern();

    if(!$INTERNAL[CALLER_SYSTEM_ID]->ClientWeb)
    {
        buildFilters();
        buildEvents();
        buildActions();
        buildGoals();
    }

	if(!SERVERSETUP)
	{
		if(!LOGIN)
		{
			buildNewPosts();
			if(!isset($_POST[POST_GLOBAL_SHOUT]))
			{
                buildResources();
                if(!$INTERNAL[CALLER_SYSTEM_ID]->ClientWeb)
                {
                    buildRatings();
                    buildTickets();
                    buildArchive();
                    buildChatVouchers();
                }
			}
		}
	}
}

function listenXML($runs = 1)
{
	global $RESPONSE,$INTERNAL;
	processActions();
	
	if(!SERVERSETUP && !LOGIN && $INTERNAL[CALLER_SYSTEM_ID]->Status == USER_STATUS_OFFLINE)
		return;
		
	$start = time();

	$RESPONSE->XML = "<listen disabled=\"".base64_encode(((getAvailability(false)) ?  "0" : "1" ))."\" h=\"<!--gl_all-->\" ".((isset($_POST[POST_INTERN_XMLCLIP_HASH_EXECUTION_TIME])) ? "ex_time=\"<!--execution_time-->\"" : "").">\r\n";
	$RESPONSE->Typing = "";
	if($RESPONSE->Login != null)
		$RESPONSE->XML .= $RESPONSE->Login;
		
	buildSystem();
	processPosts();
	
	if(($hash = substr(md5($RESPONSE->Typing),0,5)) != @$_POST["p_gl_t"] && strlen($RESPONSE->Typing) > 0)
		$RESPONSE->XML .= "<gl_typ h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Typing . "</gl_typ>\r\n";
	if(($hash = substr(md5($RESPONSE->Events),0,5)) != @$_POST["p_gl_ev"])
		$RESPONSE->XML .= "<gl_ev h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Events . "</gl_ev>\r\n";
	if(($hash = substr(md5($RESPONSE->Exceptions),0,5)) != @$_POST["p_gl_e"] && strlen($RESPONSE->Exceptions) > 0)
		$RESPONSE->XML .= "<gl_e h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Exceptions . "</gl_e>\r\n";
	if(($hash = substr(md5($RESPONSE->Internals),0,5)) != @$_POST["p_int_r"] && strlen($RESPONSE->Internals) > 0)
		$RESPONSE->XML .= "<int_r h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Internals . "</int_r>\r\n";
	if(($hash = substr(md5($RESPONSE->Groups),0,5)) != @$_POST["p_int_d"] && strlen($RESPONSE->Groups) > 0)
		$RESPONSE->XML .= "<int_d h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Groups . "</int_d>\r\n";
	if(($hash = substr(md5($RESPONSE->Actions),0,5)) != @$_POST["p_int_ev"])
		$RESPONSE->XML .= "<int_ac h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Actions . "</int_ac>\r\n";
	if(($hash = substr(md5($RESPONSE->InternalVcards),0,5)) != @$_POST["p_int_v"])
		$RESPONSE->XML .= "<int_v h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalVcards . "</int_v>\r\n";
	if(($hash = substr(md5($RESPONSE->InternalWebcamPictures),0,5)) != @$_POST["p_int_wp"])
		$RESPONSE->XML .= "<int_wp h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalWebcamPictures . "</int_wp>\r\n";
	if(($hash = substr(md5($RESPONSE->Goals),0,5)) != @$_POST["p_int_t"])
		$RESPONSE->XML .= "<int_t h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Goals . "</int_t>\r\n";
	if(($hash = substr(md5($RESPONSE->Filter),0,5)) != @$_POST["p_ext_b"])
		$RESPONSE->XML .= "<ext_b h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Filter . "</ext_b>\r\n";
	if(($hash = substr(md5($RESPONSE->Tracking),0,5)) != @$_POST["p_ext_u"])
		$RESPONSE->XML .= "<ext_u h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Tracking . "</ext_u>\r\n";
	if(($hash = substr(md5($RESPONSE->Forwards),0,5)) != @$_POST["p_ext_f"])
		$RESPONSE->XML .= "<ext_f h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Forwards . "</ext_f>\r\n";
	if(($hash = substr(md5($RESPONSE->ChatVouchers),0,5)) != @$_POST["p_ext_ct"])
		$RESPONSE->XML .= "<ext_ct h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->ChatVouchers . "</ext_ct>\r\n";
	if($RESPONSE->Archive != null)
		$RESPONSE->XML .= "<ext_c>\r\n" . $RESPONSE->Archive . "</ext_c>\r\n";
	if($RESPONSE->Resources != null)
		$RESPONSE->XML .= "<ext_res>\r\n" . $RESPONSE->Resources . "</ext_res>\r\n";
	if($RESPONSE->Ratings != null)
		$RESPONSE->XML .= "<ext_r>\r\n" . $RESPONSE->Ratings . "</ext_r>\r\n";
	if($RESPONSE->Messages != null)
		$RESPONSE->XML .= "<ext_m>\r\n" . $RESPONSE->Messages . "</ext_m>\r\n";
	if(strlen($RESPONSE->Authentications) > 0)
		$RESPONSE->XML .= "<gl_auths>\r\n" . $RESPONSE->Authentications . "\r\n</gl_auths>\r\n";
	if(strlen($RESPONSE->Posts)>0)
		$RESPONSE->XML .=  "<usr_p>\r\n" . $RESPONSE->Posts . "</usr_p>\r\n";
	if(isset($_POST[POST_INTERN_ACCESSTEST]))
		$RESPONSE->XML .= "<permission>" . base64_encode(getFolderPermissions()) . "</permission>";

	if(SERVERSETUP || LOGIN || $INTERNAL[CALLER_SYSTEM_ID]->LastActive <= @filemtime(FILE_CONFIG))
		$RESPONSE->XML .= getConfig();
		
	$RESPONSE->XML .= "</listen>";
}

function getConfig($xml="")
{
	global $_CONFIG,$CONFIG,$INTERNAL;
	$skeys = array("gl_db_host","gl_db_user","gl_db_pass","gl_db_name");
	$hashfile = FILE_CONFIG;
	$ms = base64_decode($_CONFIG["gl_lzst"])==1;
	$cindex = 0;
	$cfiles = getDirectory(PATH_CONFIG,"config.inc.php");
	
	foreach($_CONFIG as $index => $server_val)
	{
		if(is_array($server_val))
		{
			$xml .= "<conf key=\"".base64_encode($index)."\">\r\n";
				foreach($server_val as $skey => $sval)
				{
					if(!is_array($sval))
						$xml .= "<sub key=\"".base64_encode($skey)."\">".($sval)."</sub>\r\n";
				}
			$xml .= "</conf>\r\n";
		}
		else if(!(is_int($index) && is_array($server_val)))
			$xml .= "<conf value=\"".($server_val)."\" key=\"".base64_encode($index)."\" />\r\n";
	}

	if(!empty($CONFIG["gl_root"]))
		$cfiles = array_merge(array("config.inc.php"),$cfiles);

	foreach($cfiles as $file)
	{
		if(substr($file,0,7) == "config." && strpos($file,".inc.php") == strlen($file)-8)
		{
			$chost = str_replace("inc.php","",str_replace("config.","",$file));
			$chost = (strlen($chost)>0) ? substr($chost,0,strlen($chost)-1):$chost;
			
			if(!$ms || ((empty($_GET["ws"]) && strtolower($_SERVER["HTTP_HOST"]) == strtolower($chost)) || (empty($chost) && strtolower($_SERVER["HTTP_HOST"]) == strtolower($CONFIG["gl_host"])) || (!empty($_GET["ws"]) && base64_decode($_GET["ws"]) == $chost) || (!empty($CONFIG["gl_root"]) && SERVERSETUP && !MANAGEMENT) || in_array($chost,$INTERNAL[CALLER_SYSTEM_ID]->WebsitesConfig) || in_array($chost,$INTERNAL[CALLER_SYSTEM_ID]->WebsitesUsers)))
			{
				if(!empty($chost) && file_exists(str_replace("config.inc","config.".$chost.".inc",FILE_CONFIG)))
				{
					$hashfile = str_replace("config.inc","config.".$chost.".inc",FILE_CONFIG);
                    requireDynamic($hashfile, LIVEZILLA_PATH . "_config/");
				}
				foreach($_CONFIG as $index => $server_val)
				{
					if(is_int($index) && is_array($server_val))
					{
						$xml .= "<site index=\"".base64_encode($cindex)."\">\r\n";
						foreach($server_val as $key => $site_val)
						{
							if(is_array($site_val))
							{
								$xml .= "<conf key=\"".base64_encode($key)."\">\r\n";
								foreach($site_val as $skey => $sval)
									$xml .= "<sub key=\"".base64_encode($skey)."\">".($sval)."</sub>\r\n";
								$xml .= "</conf>\r\n";
							}
							else if(!in_array($key,$skeys) || SERVERSETUP)
								$xml .= "<conf value=\"".($site_val)."\" key=\"".base64_encode($key)."\" />\r\n";
							else
								$xml .= "<conf value=\"".base64_encode("")."\" key=\"".base64_encode($key)."\" />\r\n";
						}
						$cindex++;
						if($CONFIG["gl_host"] == base64_decode($server_val["gl_host"]))
						{
							$xml .= "<db_conf>\r\n";
							if(!empty($CONFIG["db"]["cct"]))
							{
								$xml .= "<cct>\r\n";
								foreach($CONFIG["db"]["cct"] as $cct)
									$xml .= $cct->GetXML();
								$xml .= "</cct>\r\n";
							}
							if(!empty($CONFIG["db"]["ccpp"]))
							{
								$xml .= "<ccpp>\r\n";
								foreach($CONFIG["db"]["ccpp"] as $ccpp)
									$xml .= $ccpp->GetXML();
								$xml .= "</ccpp>\r\n";
							}

                            if(SERVERSETUP)
                                if(!empty($CONFIG["db"]["gl_email"]))
                                {
                                    $xml .= "<gl_email>\r\n";
                                    foreach($CONFIG["db"]["gl_email"] as $mb)
                                        $xml .= $mb->GetXML();
                                    $xml .= "</gl_email>\r\n";
                                }
							$xml .= "</db_conf>\r\n";
						}
						$xml .= "</site>\r\n";
					}
				}
			}
		}
	}

	if(SERVERSETUP)
	{
		$xml .= "<translations>\r\n";
		$files = getDirectory("./_language","index",true);
		foreach($files as $translation)
		{
			if(strpos($translation,".bak.")===false)
			{
				$lang = str_replace(".php","",str_replace("lang","",$translation));
				$parts = explode(".",$lang);
				if((ISSUBSITE && strpos($translation,SUBSITEHOST) !== false) || (!ISSUBSITE && substr_count($translation,".")==1))
					$xml .= "<language key=\"".base64_encode($parts[0])."\" blocked=\"".base64_encode((@filesize("./_language/".$translation) == 0) ? 1 : "0"). "\" />\r\n";
				else if(ISSUBSITE && strpos($translation,SUBSITEHOST) === false && !@file_exists(getLocalizationFileString($parts[0],false)))
					$xml .= "<language key=\"".base64_encode($parts[0])."\" derived=\"".base64_encode(1). "\" />\r\n";
			}
		}
		$xml .= "</translations>\r\n";
	}

	$xml .= "<php_cfg_vars post_max_size=\"".base64_encode(cfgFileSizeToBytes((!isnull(@get_cfg_var("post_max_size")))?get_cfg_var("post_max_size"):MAX_POST_SIZE_SAFE_MODE))."\" upload_max_filesize=\"".base64_encode(cfgFileSizeToBytes((!isnull(@get_cfg_var("upload_max_filesize")))?get_cfg_var("upload_max_filesize"):MAX_UPLOAD_SIZE_SAFE_MODE))."\" />\r\n";
	$xml .= "</gl_c>\r\n";
	return "<gl_c h=\"".base64_encode(substr(md5file($hashfile),0,5))."\">\r\n" . $xml;
}

function getFolderPermissions($message=null)
{
	$directories = Array(PATH_UPLOADS,PATH_CONFIG);
	foreach($directories as $key => $dir)
	{
		$result = testDirectory($dir);
			if(!$result)
				return 0;
	}
	return 1;
}

function ipIsInRange($_ip, $_range) 
{
	if (strpos($_range, '/') !== false) 
	{
		list($_range, $netmask) = explode('/', $_range, 2);
		if (strpos($netmask, '.') !== false) 
		{
			$netmask = str_replace('*', '0', $netmask);
			$netmask_dec = ip2long($netmask);
			return ((ip2long($_ip) & $netmask_dec) == (ip2long($_range) & $netmask_dec));
		}
		else
		{
			$x = explode('.', $_range);
			while(count($x)<4) $x[] = '0';
			list($a,$b,$c,$d) = $x;
			$_range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
			$range_dec = ip2long($_range);
			$ip_dec = ip2long($_ip);
			$wildcard_dec = pow(2,(32-$netmask)) - 1;
			$netmask_dec = ~ $wildcard_dec;
			return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
		}
	} 
	else 
	{
		if(strpos($_range, '*')!==false)
		{
			$lower = str_replace('*', '0', $_range);
			$upper = str_replace('*', '255', $_range);
			$_range = "$lower-$upper";
		}
		if(strpos($_range, '-')!==false) 
		{
			list($lower, $upper) = explode('-', $_range, 2);
			$lower_dec = (float)sprintf("%u",ip2long($lower));
			$upper_dec = (float)sprintf("%u",ip2long($upper));
			$ip_dec = (float)sprintf("%u",ip2long($_ip));
			return (($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
		}
		return false;
	}
}

function isValidated()
{
    return (defined("VALIDATED") && VALIDATED === true);
}

?>
