<?php
/****************************************************************************************
* LiveZilla track.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
require(LIVEZILLA_PATH . "_lib/functions.tracking.inc.php");

define("JAVASCRIPT",!(isset($_GET[GET_TRACK_OUTPUT_TYPE]) && $_GET[GET_TRACK_OUTPUT_TYPE] == "nojcrpt") && strpos($_SERVER["QUERY_STRING"],"nojcrpt") === false);

if(!empty($_GET[GET_TRACK_USERID]))
{
	define("CALLER_BROWSER_ID",base64UrlDecode(getParam(GET_TRACK_BROWSERID)));
	define("CALLER_USER_ID",base64UrlDecode(getParam(GET_TRACK_USERID)));
	if(isnull(getCookieValue("userid")) || (!isnull(getCookieValue("userid")) && getCookieValue("userid") != CALLER_USER_ID))
		setCookieValue("userid",CALLER_USER_ID);
}
else if(!isnull(getCookieValue("userid")))
{
	define("CALLER_BROWSER_ID",getId(USER_ID_LENGTH));
	define("CALLER_USER_ID",substr(getCookieValue("userid"),0,USER_ID_LENGTH));
}
if(!defined("CALLER_USER_ID"))
{
	if(!JAVASCRIPT)
	{
		define("CALLER_USER_ID",substr(md5(getIP()),0,USER_ID_LENGTH));
		define("CALLER_BROWSER_ID",substr(strrev(md5(getIP())),0,USER_ID_LENGTH));
	}
	else
	{
		define("CALLER_USER_ID",getId(USER_ID_LENGTH));
		define("CALLER_BROWSER_ID",getId(USER_ID_LENGTH));
	}
}

$EXTERNALUSER = new Visitor(CALLER_USER_ID);
$EXTERNALUSER->Load();
$EXTERNALUSER->AppendPersonalData();

if(isset($_GET[GET_TRACK_OUTPUT_TYPE]) && ($_GET[GET_TRACK_OUTPUT_TYPE] == "jscript" || $_GET[GET_TRACK_OUTPUT_TYPE] == "jcrpt"))
{
	$fullname = base64UrlEncode($EXTERNALUSER->Fullname);
	$email = base64UrlEncode($EXTERNALUSER->Email);
	$company = base64UrlEncode($EXTERNALUSER->Company);
	$question = base64UrlEncode($EXTERNALUSER->Question);
	$phone = base64UrlEncode($EXTERNALUSER->Phone);
	$customs = array();
	
	if(empty($_GET[GET_TRACK_NO_SEARCH_ENGINE]))
		exit(getFile(TEMPLATE_HTML_SUPPORT));
		
	$row = $EXTERNALUSER->CreateSignature();
	if(is_array($row) && $row["id"] != CALLER_USER_ID)
		$EXTERNALUSER->UserId = $row["id"];

	$TRACKINGSCRIPT = getFile(TEMPLATE_SCRIPT_TRACK);
	$TRACKINGSCRIPT = str_replace("<!--file_chat-->",FILE_CHAT,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--server_id-->",substr(md5($CONFIG["gl_lzid"]),5,5),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--server-->",LIVEZILLA_URL,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--website-->",getParam("ws"),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--area_code-->",(isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? getParam(GET_TRACK_SPECIAL_AREA_CODE) : "",$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--browser_id-->",htmlentities(CALLER_BROWSER_ID,ENT_QUOTES,"UTF-8"),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_id-->",htmlentities($EXTERNALUSER->UserId,ENT_QUOTES,"UTF-8"),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--connection_error_span-->",CONNECTION_ERROR_SPAN,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--poll_frequency-->",getPollFrequency(),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = geoReplacements($TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--geo_resolute-->",parseBool($EXTERNALUSER->UserId == CALLER_USER_ID && !empty($CONFIG["gl_use_ngl"]) && $EXTERNALUSER->FirstCall && !empty($CONFIG["gl_pr_ngl"]) && !(!isnull(getCookieValue("geo_data")) && getCookieValue("geo_data") > time()-2592000) && !isSSpanFile()),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--alert_html-->",base64_encode(getAlertTemplate()),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_company-->",$company,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_question-->",$question,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_phone-->",$phone,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_language-->",getParam(GET_EXTERN_USER_LANGUAGE),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_header-->",getParam(GET_EXTERN_USER_HEADER),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_customs-->",getJSCustomArray("",$customs),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--custom_params-->",getCustomParams("",$customs),$TRACKINGSCRIPT);
	
	$detector = new DeviceDetector();
	$detector->DetectBrowser();
    $MobileDetect = $detector->DetectOperatingSystem();

    $TRACKINGSCRIPT = str_replace("<!--is_tablet-->",parseBool($MobileDetect->isMobile()),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--is_ie-->",parseBool($detector->BrowserName == "Internet Explorer"),$TRACKINGSCRIPT);
	
	if(!empty($_GET["ovlc"]) && !($detector->BrowserName != "Internet Explorer" || $detector->BrowserVersion > 6))
		unset($_GET["ovlc"]);

	$TRACKINGSCRIPT = str_replace("<!--is_ovlpos-->",parseBool(($detector->BrowserName != "Internet Explorer" || $detector->BrowserVersion > 6)),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--is_ovlc-->",parseBool(!empty($_GET["ovlc"])),$TRACKINGSCRIPT);
	
	if(!empty($_GET["ovlc"]) && strlen(base64UrlDecode($_GET["ovlc"])) == 7)
	{
		$TRACKINGSCRIPT .= getFile(TEMPLATE_SCRIPT_OVERLAY_CHAT);
		$TRACKINGSCRIPT = str_replace("<!--header_online-->",base64UrlDecode($_GET["ovlt"]),$TRACKINGSCRIPT);
        $color = getBrightness($_GET["ovlc"]) < getBrightness($_GET["ovlct"]) ? $_GET["ovlct"] : $_GET["ovlc"];
        $TRACKINGSCRIPT = str_replace("<!--color-->",hexDarker(str_replace("#","",base64UrlDecode($color)),35),$TRACKINGSCRIPT);
		$TRACKINGSCRIPT = str_replace("<!--header_offline-->",base64UrlDecode($_GET["ovlto"]),$TRACKINGSCRIPT);
		$TRACKINGSCRIPT = str_replace("<!--tickets_external-->",parseBool(!empty($_GET["ovloe"])),$TRACKINGSCRIPT);
		$TRACKINGSCRIPT = str_replace("<!--offline_message_mode-->",$CONFIG["gl_om_mode"],$TRACKINGSCRIPT);
		$TRACKINGSCRIPT = str_replace("<!--offline_message_http-->",$CONFIG["gl_om_http"],$TRACKINGSCRIPT);
		$TRACKINGSCRIPT = str_replace("<!--post_html-->",base64_encode(getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_EXTERN)),$TRACKINGSCRIPT);
		$TRACKINGSCRIPT = str_replace("<!--add_html-->",base64_encode(getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_ADD)),$TRACKINGSCRIPT);
		$TRACKINGSCRIPT = str_replace("<!--offline_message_pop-->",parseBool(!empty($CONFIG["gl_om_pop_up"])),$TRACKINGSCRIPT);
		
		$ov = new VisitorChat($EXTERNALUSER->UserId,$EXTERNALUSER->UserId . "_OVL");
		$ov->Load();
		if(!empty($ov->Fullname))
			$fullname = base64UrlEncode($ov->Fullname);
		if(!empty($ov->Email))
			$email = base64UrlEncode($ov->Email);
		$TRACKINGSCRIPT = applyReplacements($TRACKINGSCRIPT,true,false);
	}
	savePassThruToCookie($fullname,$email,$company,$question,$phone);
	$TRACKINGSCRIPT = str_replace("<!--user_name-->",$fullname,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_email-->",$email,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--height-->",$CONFIG["wcl_window_height"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--width-->",$CONFIG["wcl_window_width"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--url_get_params-->",getParams("",array("ovlto"=>true,"ovlt"=>true,"ovlp"=>true,"ovlml"=>true,"ovlmr"=>true,"ovlmt"=>true,"ovlmb"=>true,"ovls"=>true,"ovloo"=>true,"ovlc"=>true,"ovlapo"=>true,"ovlct"=>true,"intgroup"=>true,"intid"=>true,"pref"=>true,"cboo"=>true,"hg"=>true,"fbpos"=>false,"fbw"=>false,"fbh"=>false,"fbshx"=>false,"fbshy"=>false,"fbshb"=>false,"fbshc"=>false,"fbmt"=>false,"fbmr"=>false,"fbmb"=>false,"fbml"=>false,"fboo"=>false)),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--server-->",LIVEZILLA_URL,$TRACKINGSCRIPT);
}
else
{
	$TRACKINGSCRIPT = "lz_tracking_set_sessid(\"".base64_encode(CALLER_USER_ID)."\",\"".base64_encode(CALLER_BROWSER_ID)."\");";
	if(isset($_GET[GET_TRACK_URL]) && strpos(base64UrlDecode($_GET[GET_TRACK_URL]),GET_INTERN_COBROWSE) !== false)
		abortTracking(1);

	$BROWSER = new VisitorBrowser(CALLER_BROWSER_ID,CALLER_USER_ID);
	
	//$hidevisitor = empty($_GET["ovlc"]) && (empty($CONFIG["gl_vmac"]) || (!empty($CONFIG["gl_hide_inactive"]) && !$EXTERNALUSER->IsActivity($BROWSER)));
	
	if($EXTERNALUSER->FirstCall && !$BROWSER->IsFirstCall())
		$EXTERNALUSER->FirstCall = false;
		
	initData(true,false,false,true,true);
	define("IS_FILTERED",$FILTERS->Match(getIP(),formLanguages(((!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : "")),CALLER_USER_ID));
	define("IS_FLOOD",$BROWSER->IsFirstCall() && isFlood(getIP(),CALLER_USER_ID));

	$BROWSER->Customs = getCustomArray($BROWSER->Customs);

	if(isset($_GET[GET_EXTERN_USER_NAME]) && !empty($_GET[GET_EXTERN_USER_NAME]))
		$BROWSER->Fullname = cutString(base64UrlDecode($_GET[GET_EXTERN_USER_NAME]),255);
	else if($INPUTS[111]->Cookie)
		$BROWSER->Fullname = getCookieValue("form_111");
	
	if(isset($_GET[GET_EXTERN_USER_EMAIL]) && !empty($_GET[GET_EXTERN_USER_EMAIL]))
		$BROWSER->Email = cutString(base64UrlDecode($_GET[GET_EXTERN_USER_EMAIL]),255);
	else if($INPUTS[112]->Cookie)
		$BROWSER->Email = getCookieValue("form_112");
		
	if(isset($_GET[GET_EXTERN_USER_COMPANY]) && !empty($_GET[GET_EXTERN_USER_COMPANY]))
		$BROWSER->Company = cutString(base64UrlDecode($_GET[GET_EXTERN_USER_COMPANY]),255);
	else if($INPUTS[113]->Cookie)
		$BROWSER->Company = getCookieValue("form_113");
		
	if(isset($_GET[GET_EXTERN_USER_QUESTION]) && !empty($_GET[GET_EXTERN_USER_QUESTION]))
		$BROWSER->Question = base64UrlDecode($_GET[GET_EXTERN_USER_QUESTION]);
	else if($INPUTS[114]->Cookie)
		$BROWSER->Question = getCookieValue("form_114");
		
	if(isset($_GET["ep"]) && !empty($_GET["ep"]))
		$BROWSER->Phone = base64UrlDecode($_GET["ep"]);
	else if($INPUTS[116]->Cookie)
		$BROWSER->Phone = getCookieValue("form_116");

	$referrer = (isset($_GET[GET_TRACK_REFERRER]) ? trim(slashesStrip(base64UrlDecode($_GET[GET_TRACK_REFERRER]))) : "");
	
	if(JAVASCRIPT)
	{
		if(isset($_GET[GET_TRACK_RESOLUTION_WIDTH]))
		{
			if(!isset($_GET[GET_TRACK_URL]))
				abortTracking(9);
			else if(empty($_GET[GET_TRACK_URL]))
				abortTracking(3);

			$currentURL = new HistoryURL(substr(base64UrlDecode($_GET[GET_TRACK_URL]),0,2083),((isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? base64UrlDecode($_GET[GET_TRACK_SPECIAL_AREA_CODE]) : ""),base64UrlDecode(@$_GET[GET_EXTERN_DOCUMENT_TITLE]),$referrer,time());

			if($currentURL->Referrer->IsInternalDomain())
				$currentURL->Referrer = new BaseUrl("");
			
			if($currentURL->Url->Excluded)
				abortTracking(4);
			
				if(!empty($CONFIG["gl_vmac"]) || !empty($_GET["ovlc"]))
					$EXTERNALUSER->Save($CONFIG,array($_GET[GET_TRACK_RESOLUTION_WIDTH],$_GET[GET_TRACK_RESOLUTION_HEIGHT]),$_GET[GET_TRACK_COLOR_DEPTH],$_GET[GET_TRACK_TIMEZONE_OFFSET],((isset($_GET[GEO_LATITUDE]))?$_GET[GEO_LATITUDE]:""),((isset($_GET[GEO_LONGITUDE]))?$_GET[GEO_LONGITUDE]:""),((isset($_GET[GEO_COUNTRY_ISO_2]))?$_GET[GEO_COUNTRY_ISO_2]:""),((isset($_GET[GEO_CITY]))?$_GET[GEO_CITY]:""),((isset($_GET[GEO_REGION]))?$_GET[GEO_REGION]:""),((isset($_GET[GEO_TIMEZONE]))?$_GET[GEO_TIMEZONE]:""),((isset($_GET[GEO_ISP]))?$_GET[GEO_ISP]:""),((isset($_GET[GEO_SSPAN]))?$_GET[GEO_SSPAN]:""),((isset($_GET[GEO_RESULT_ID]))?$_GET[GEO_RESULT_ID]:""));
		}
	}
	else if(!empty($_SERVER["HTTP_REFERER"]))
	{
		$currentURL = new HistoryURL(substr($_SERVER["HTTP_REFERER"],0,2083),((isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? base64UrlDecode($_GET[GET_TRACK_SPECIAL_AREA_CODE]) : ""),"","",time());
		if($currentURL->Url->Excluded)
			abortTracking(5);
		else if(!$currentURL->Url->IsInternalDomain())
			abortTracking(6);
		
			if(!empty($CONFIG["gl_vmac"]) || !empty($_GET["ovlc"]))
				$EXTERNALUSER->Save($CONFIG,null,"","",-522,-522,"","","","","","","",false);
	}
	else
		abortTracking(-1);

	if($EXTERNALUSER->IsCrawler)
		abortTracking(8);

	else if($EXTERNALUSER->SignatureMismatch)
	{
		$TRACKINGSCRIPT = "lz_tracking_set_sessid(\"".base64_encode($EXTERNALUSER->UserId)."\",\"".base64_encode(CALLER_BROWSER_ID)."\");";
		$TRACKINGSCRIPT .= "lz_tracking_callback(5);";
		$TRACKINGSCRIPT .= "lz_tracking_poll_server();";
	}
	else
	{
		if(isset($_GET[GET_TRACK_CLOSE_CHAT_WINDOW]))
		{
			$chat = new VisitorChat($EXTERNALUSER->UserId,$_GET[GET_TRACK_CLOSE_CHAT_WINDOW]);
			$chat->Load();
			$chat->ExternalClose();
			$chat->Destroy();
		}
		$BROWSER->LastActive = time();
		$BROWSER->VisitId = $EXTERNALUSER->VisitId;
		
		$parameters = getTargetParameters(false);
		if(!empty($_GET["tth"]) || $EXTERNALUSER->IsInChat(true,$BROWSER))
			define("IGNORE_WM",true);
		
		$conline = operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"],true) > 0;
		$BROWSER->OverlayContainer = !empty($_GET["ovlc"]);
		
			if(!empty($CONFIG["gl_vmac"]) || !empty($_GET["ovlc"]))
				$BROWSER->Save($EXTERNALUSER,@$_GET[GET_TRACK_URL]);

		if(isset($currentURL) && (count($BROWSER->History) == 0 || (count($BROWSER->History) > 0 && $BROWSER->History[count($BROWSER->History)-1]->Url->GetAbsoluteUrl() != $currentURL->Url->GetAbsoluteUrl())))
		{
			$BROWSER->History[] = $currentURL;
			if(!isnull($BROWSER->History[count($BROWSER->History)-1]->Referrer->GetAbsoluteUrl()))
				if($BROWSER->SetQuery($BROWSER->History[count($BROWSER->History)-1]->Referrer->GetAbsoluteUrl()))
					$BROWSER->History[count($BROWSER->History)-1]->Referrer->MarkSearchEngine();
					
				if(!empty($CONFIG["gl_vmac"]) || !empty($_GET["ovlc"]))
				{
					$BROWSER->History[count($BROWSER->History)-1]->Save(CALLER_BROWSER_ID,count($BROWSER->History)==1);
					$BROWSER->ForceUpdate();
				}
		}
		else if(count($BROWSER->History) == 0)
			abortTracking(11);

		$BROWSER->LoadWebsitePush();
		$BROWSER->LoadChatRequest();
		$BROWSER->LoadAlerts();
		$BROWSER->LoadOverlayBoxes();
		
		$TRACKINGSCRIPT .= triggerEvents();
		$TRACKINGSCRIPT .= processActions();

		$ACTIVE_OVLC = false;
		if(!empty($_GET["fbpos"]) && !empty($_GET["fbw"]) && is_numeric(base64UrlDecode($_GET["fbw"])))
		{
			$shadow=(!empty($_GET["fbshx"])) ? ("true,".base64UrlDecode($_GET["fbshb"]).",".base64UrlDecode($_GET["fbshx"]).",".base64UrlDecode($_GET["fbshy"]).",'".base64UrlDecode($_GET["fbshc"])."'") : "false,0,0,0,''";
			$margin=(!empty($_GET["fbmt"])) ? (",".base64UrlDecode($_GET["fbml"]).",".base64UrlDecode($_GET["fbmt"]).",".base64UrlDecode($_GET["fbmr"]).",".base64UrlDecode($_GET["fbmb"])) : ",0,0,0,0";
			if(!(!$conline && !empty($_GET["fboo"])))
				$TRACKINGSCRIPT .= "lz_tracking_add_floating_button(".base64UrlDecode($_GET["fbpos"]).",".$shadow.$margin.",".base64UrlDecode($_GET["fbw"]).",".base64UrlDecode($_GET["fbh"]).");";
		}
		if(!empty($_GET["ovlc"]) && strlen(base64UrlDecode($_GET["ovlc"])) == 7)
		{
			require(LIVEZILLA_PATH . "ovl.php");
			$TRACKINGSCRIPT .= @$OVLPAGE;
		}

		if(!empty($_GET["cboo"]) && !operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"],false))
			$TRACKINGSCRIPT .= "lz_tracking_remove_buttons();";

		$hidevisitor = (empty($CONFIG["gl_vmac"]) || (!empty($CONFIG["gl_hide_inactive"]) && !$EXTERNALUSER->IsActivity($BROWSER)));

        if(!empty($_SERVER['HTTP_DNT']) && $CONFIG["gl_dnt"] && empty($_GET["ovlc"]))
        {
            $BROWSER->Destroy();
            $TRACKINGSCRIPT .= "lz_tracking_stop_tracking(10);";
        }

        if(!$hidevisitor || !empty($ACTIVE_OVLC))
		{
			if(!getAvailability())
			{
				$BROWSER->Destroy();
				abortTracking(12);
			}
            else if(IS_FILTERED)
            {
                $BROWSER->Destroy();
                abortTracking(13);
            }
            else if(IS_FLOOD)
            {
                $BROWSER->Destroy();
                abortTracking(14);
            }
			if(isset($_GET[GET_TRACK_START]) && is_numeric($_GET[GET_TRACK_START]))
				$TRACKINGSCRIPT .= "lz_tracking_callback(" . ($ACTIVE_OVLC ? $CONFIG["poll_frequency_clients"] : getPollFrequency()) . ");";
			if(empty($EXTERNALUSER->Host) && $EXTERNALUSER->FirstCall)
				$EXTERNALUSER->ResolveHost();
		}
		else
		{
            $TRACKINGSCRIPT .= "lz_tracking_stop_tracking(13);";
		}
	}
}
?>
