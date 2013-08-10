<?php
/****************************************************************************************
* LiveZilla functions.tracking.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
function abortTracking($_code)
{
	exit("lz_tracking_stop_tracking(".$_code.");");
}

function savePassThruToCookie($_fullname,$_email,$_company,$_question,$_phone)
{
	global $INPUTS;
	initData(false,false,false,false,false,false,false,true);
	
	if(!empty($_fullname) && $INPUTS[111]->Cookie && $INPUTS[111]->Active)
		setCookieValue("form_111",cutString(base64UrlDecode($_fullname),255));
	if(!empty($_email) && $INPUTS[112]->Cookie && $INPUTS[112]->Active)
		setCookieValue("form_112",cutString(base64UrlDecode($_email),255));
	if(!empty($_company) && $INPUTS[113]->Cookie && $INPUTS[113]->Active)
		setCookieValue("form_113",cutString(base64UrlDecode($_company),255));
	if(!empty($_question) && $INPUTS[114]->Cookie && $INPUTS[114]->Active)
		setCookieValue("form_114",base64UrlDecode($_question));
	if(!empty($_phone) && $INPUTS[116]->Cookie && $INPUTS[116]->Active)
		setCookieValue("form_116",base64UrlDecode($_phone));
		
	foreach($INPUTS as $index => $input)
	{
		if($input->Custom && $input->Active && !empty($_GET["cf".$index]) && $input->Cookie)
			setCookieValue("cf_".$index,cutString(base64UrlDecode($_GET["cf".$index]),255));
	}
}

function triggerEvents($chatRequest=false,$alert=false,$websitePush=false)
{
	global $BROWSER,$EVENTS,$EXTERNALUSER,$STATS,$GROUPS;
	if(count($EVENTS)==0)
		return;

	$actionData = "";
	$url = $BROWSER->History[count($BROWSER->History)-1];
	$previous = (count($BROWSER->History) > 1) ? $BROWSER->History[count($BROWSER->History)-2]->Url->GetAbsoluteUrl() : "";
	$EXTERNALUSER->GetChatRequestResponses();
	
	foreach($EVENTS->Events as $event)
	{
		if(!$event->IsActive || empty($url))
			continue;

		$urlor = (count($event->FunnelUrls) == 0 && $event->MatchesURLCriterias($url->Url->GetAbsoluteUrl(),$url->Referrer->GetAbsoluteUrl(),$previous,time()-($url->Entrance)));
		$urlfunnel = (count($event->FunnelUrls) > 0 && $event->MatchesURLFunnelCriterias($BROWSER->History));
		$global = $event->MatchesGlobalCriterias(count($BROWSER->History),($EXTERNALUSER->ExitTime-$EXTERNALUSER->FirstActive),$EXTERNALUSER->HasAcceptedChatRequest,$EXTERNALUSER->HasDeclinedChatRequest,$EXTERNALUSER->WasInChat(),$BROWSER->Query);

		if($global && ($urlfunnel || $urlor))
		{
			foreach(array($event->Goals,$event->Actions) as $elements)
				foreach($elements as $action)
				{
					$EventTrigger = new EventTrigger(CALLER_USER_ID,CALLER_BROWSER_ID,$action->Id,time(),1);
					$EventTrigger->Load();
					$aexists = $action->Exists(CALLER_USER_ID,CALLER_BROWSER_ID);
					if(!$EventTrigger->Exists || ($EventTrigger->Exists && $event->MatchesTriggerCriterias($EventTrigger)))
					{
						if(!$aexists)
						{
							if($event->SaveInCookie)
							{
								if(!isnull(getCookieValue("ea_" . $action->Id)))
									continue;
								else
									setCookieValue("ea_" . $action->Id,time());
							}
							$EventTrigger->Save($event->Id);
							if($action->Type < 2)
							{
								foreach($action->GetInternalReceivers() as $user_id)
								{
									$intaction = new EventActionInternal($user_id, $EventTrigger->Id);
									$intaction->Save();
								}
							}
							else if($action->Type == 2 && !defined("EVENT_INVITATION"))
							{
								$sender = getActionSender($action->Invitation->Senders,true);
								initData(false,true);
								$BROWSER->LoadChatRequest();
								if(!empty($sender) && !empty($GROUPS[$sender->GroupId]) && $GROUPS[$sender->GroupId]->IsHumanAvailable(false) && !($BROWSER->ChatRequest != null && !$BROWSER->ChatRequest->Closed) && !$EXTERNALUSER->IsInChat())
								{
									define("EVENT_INVITATION",true);
									$chatrequest = new ChatRequest($sender->UserSystemId,$sender->GroupId,CALLER_USER_ID,CALLER_BROWSER_ID,getActionText($sender,$action));
									$chatrequest->EventActionId = $action->Id;
									$chatrequest->Save();
									if(!$chatrequest->Displayed)
										$BROWSER->ForceUpdate();
									$BROWSER->LoadChatRequest();
								}
							}
							else if($action->Type == 3 && !defined("EVENT_ALERT"))
							{
								define("EVENT_ALERT",true);
								$alert = new Alert(CALLER_USER_ID,CALLER_BROWSER_ID,$action->Value);
								$alert->EventActionId = $action->Id;
								$alert->Save();
								$BROWSER->LoadAlerts();
							}
							else if($action->Type == 4 && !defined("EVENT_WEBSITE_PUSH"))
							{
								define("EVENT_WEBSITE_PUSH",true);
								$sender = getActionSender($action->WebsitePush->Senders,false);
								$websitepush = new WebsitePush($sender->UserSystemId,$sender->GroupId,CALLER_USER_ID,CALLER_BROWSER_ID,getActionText($sender,$action),$action->WebsitePush->Ask,$action->WebsitePush->TargetURL);
								$websitepush->EventActionId = $action->Id;
								$websitepush->Save();
								$BROWSER->LoadWebsitePush();
							}
							else if($action->Type == 5 && !defined("EVENT_OVERLAY_BOX"))
							{
								define("EVENT_OVERLAY_BOX",true);
								$overlaybox = new OverlayBox(CALLER_USER_ID,CALLER_BROWSER_ID,$action->Value);
								$overlaybox->EventActionId = $action->Id;
								$overlaybox->Save();
								$BROWSER->LoadOverlayBoxes();
							}
							else if($action->Type == 9 && STATS_ACTIVE)
							{
								$STATS->ProcessAction(ST_ACTION_GOAL,array(CALLER_USER_ID,$action->Id,(($EXTERNALUSER->Visits==1)?1:0),$BROWSER->GetQueryId(getCookieValue("sp"),null,255,true)));
							}
						}
					}
					if($EventTrigger->Exists && $aexists)
						$EventTrigger->Update();
				}
		}
	}
	return $actionData;
}

function getActionSender($_senders,$_checkOnline,$maxPriority=0,$minChats=100)
{
	global $CONFIG,$INTERNAL,$GROUPS;
	initData(true,true);
	foreach($_senders as $sender)
		if(isset($INTERNAL[$sender->UserSystemId]) && (!$_checkOnline || (($INTERNAL[$sender->UserSystemId]->LastActive > (time()-$CONFIG["timeout_clients"])) && $INTERNAL[$sender->UserSystemId]->Status == USER_STATUS_ONLINE)))
		{
			$maxPriority = max($maxPriority,$sender->Priority);
			if($maxPriority == $sender->Priority)
			{
				$INTERNAL[$sender->UserSystemId]->GetExternalObjects();
				$minChats = min($minChats, count($INTERNAL[$sender->UserSystemId]->ExternalChats));
				$asenders[] = $sender;
			}
		}
	if(!empty($asenders) && count($asenders)==1)
		return $asenders[0];
	else if(empty($asenders))
		return null;
	foreach($asenders as $sender)
		if($minChats == count($INTERNAL[$sender->UserSystemId]->ExternalChats))
			$fsenders[] = $sender;
	return $fsenders[array_rand($fsenders,1)];
}

function getActionText($_sender,$_action,$break=false,$sel_message = null)
{
	global $EXTERNALUSER,$INTERNAL,$GROUPS,$BROWSER,$INPUTS;
	if(!empty($_action->Value))
		return $_action->Value;

	$available = array($INTERNAL[$_sender->UserSystemId]->PredefinedMessages,$GROUPS[$_sender->GroupId]->PredefinedMessages);
	foreach($available as $list)
	{
		foreach($list as $message)
		{
			if(($message->IsDefault && (!$message->BrowserIdentification || empty($EXTERNALUSER->Language))) || ($message->BrowserIdentification && $EXTERNALUSER->Language == $message->LangISO))
			{
				$sel_message = $message;
				$break = true;
				break;
			}
			else if($message->IsDefault && empty($_action->Value))
			{
				$sel_message = $message;
			}
		}
		if($break)
			break;
	}

	if($_action->Type == 2 && $sel_message != null)
		$_action->Value = $sel_message->InvitationAuto;
	else if($_action->Type == 4)
	{
		$_action->Value = $sel_message->WebsitePushAuto;
		$_action->Value = str_replace("%target_url%",$_action->WebsitePush->TargetURL,$_action->Value);
	}
	$_action->Value = str_replace("%external_name%",$BROWSER->Fullname,$_action->Value);
	$_action->Value = str_replace("%external_email%",$BROWSER->Email,$_action->Value);
	$_action->Value = str_replace("%external_company%",$BROWSER->Company,$_action->Value);
	$_action->Value = str_replace("%external_phone%",$BROWSER->Phone,$_action->Value);
	$_action->Value = str_replace("%external_ip%",getIP(),$_action->Value);
	$_action->Value = str_replace("%searchstring%",$BROWSER->Query,$_action->Value);
	$_action->Value = str_replace("%page_title%",$BROWSER->History[count($BROWSER->History)-1]->Url->PageTitle,$_action->Value);
	$_action->Value = str_replace("%url%",$BROWSER->History[count($BROWSER->History)-1]->Url->GetAbsoluteUrl(),$_action->Value);
	$_action->Value = str_replace(array("%name%","%operator_name%"),$INTERNAL[$_sender->UserSystemId]->Fullname,$_action->Value);
	$_action->Value = str_replace(array("%email%","%operator_email%"),$INTERNAL[$_sender->UserSystemId]->Email,$_action->Value);
	
	foreach($INPUTS as $index => $input)
		if($input->Active && $input->Custom)
			$_action->Value = str_replace("%custom".$index."%",$input->GetClientValue($BROWSER->Customs[$index]),$_action->Value);
	
	return $_action->Value;
}

function processActions($actionData = "")
{
	global $BROWSER,$CONFIG,$INTERNAL,$EVENTS,$GROUPS;
	if(!empty($BROWSER->ChatRequest))
	{
		initData(true,true);
		if(($INTERNAL[$BROWSER->ChatRequest->SenderSystemId]->LastActive < (time()-$CONFIG["timeout_clients"])) || $INTERNAL[$BROWSER->ChatRequest->SenderSystemId]->Status != USER_STATUS_ONLINE || !$INTERNAL[$BROWSER->ChatRequest->SenderSystemId]->IsExternal($GROUPS, null, null, true) || $BROWSER->ChatRequest->Closed || $BROWSER->ChatRequest->Canceled)
		{
			if(!$BROWSER->ChatRequest->Closed)
			{
				$BROWSER->ChatRequest->SetStatus(false,false,!$BROWSER->ChatRequest->Canceled,true);
				$BROWSER->ForceUpdate();
			}
			$actionData .= "top.lz_tracking_close_request('".$BROWSER->ChatRequest->Id."');";
		}
		else if(isset($_GET["decreq"]) || isset($_GET["accreq"]))
		{
			if(isset($_GET["decreq"]))
				$BROWSER->ChatRequest->SetStatus(false,false,true,true);
			if(isset($_GET["accreq"]))
				$BROWSER->ChatRequest->SetStatus(false,true,false,true);
			if(isset($_GET["clreq"]))
				$actionData .= "top.lz_tracking_close_request();";
			if(!$BROWSER->ChatRequest->Closed)
				$BROWSER->ForceUpdate();
		}
		else if(!$BROWSER->ChatRequest->Accepted && !$BROWSER->ChatRequest->Declined)
		{
			if(!empty($_GET["ovlc"]) && !empty($_GET["ovlt"]))
			{
				$BROWSER->ChatRequest->Displayed = true;
			}
			else if(empty($BROWSER->ChatRequest->EventActionId))
			{
				$invitationSettings = @unserialize(base64_decode($CONFIG["gl_invi"]));
				array_walk($invitationSettings,"b64dcode");
				$invitationHTML = applyReplacements($BROWSER->ChatRequest->CreateInvitationTemplate($invitationSettings[13],$CONFIG["gl_site_name"],$CONFIG["wcl_window_width"],$CONFIG["wcl_window_height"],LIVEZILLA_URL,$INTERNAL[$BROWSER->ChatRequest->SenderSystemId],$invitationSettings[0]));
				$BROWSER->ChatRequest->Invitation = new Invitation($invitationHTML,$BROWSER->ChatRequest->Text,$invitationSettings);
			}
			else if(!isnull($action = $EVENTS->GetActionById($BROWSER->ChatRequest->EventActionId)))
			{
				$invitationHTML = applyReplacements($BROWSER->ChatRequest->CreateInvitationTemplate($action->Invitation->Style,$CONFIG["gl_site_name"],$CONFIG["wcl_window_width"],$CONFIG["wcl_window_height"],LIVEZILLA_URL,$INTERNAL[$BROWSER->ChatRequest->SenderSystemId],$action->Invitation->CloseOnClick));
				$BROWSER->ChatRequest->Invitation = $action->Invitation;
				$BROWSER->ChatRequest->Invitation->Text = $BROWSER->ChatRequest->Text;
				$BROWSER->ChatRequest->Invitation->HTML = $invitationHTML;
			}
			if(!$BROWSER->ChatRequest->Displayed)
			{
				$BROWSER->ForceUpdate();
				$BROWSER->ChatRequest->SetStatus(true,false,false,false);
			}
			if(!empty($BROWSER->ChatRequest->Invitation) && !$BROWSER->OverlayContainer)
			{
				$actionData .= $BROWSER->ChatRequest->Invitation->GetCommand($BROWSER->ChatRequest->Id);
			}
		}
	}
	if(!empty($BROWSER->WebsitePush))
	{
		if(isset($_GET[GET_TRACK_WEBSITE_PUSH_DECLINED]))
		{
			$BROWSER->WebsitePush->SetStatus(false,false,true);
		}
		else if(isset($_GET[GET_TRACK_WEBSITE_PUSH_ACCEPTED]) || (!$BROWSER->WebsitePush->Ask && !$BROWSER->WebsitePush->Displayed))
		{
			$BROWSER->WebsitePush->SetStatus(false,true,false);
			$actionData .= $BROWSER->WebsitePush->GetExecCommand();
		}
		else if($BROWSER->WebsitePush->Ask && !$BROWSER->WebsitePush->Accepted && !$BROWSER->WebsitePush->Declined)
		{
			$BROWSER->WebsitePush->SetStatus(true,false,false);
			$actionData .= $BROWSER->WebsitePush->GetInitCommand();
		}
	}
	if(!empty($BROWSER->Alert) && !$BROWSER->Alert->Accepted)
	{
		if(isset($_GET["confalert"]))
			$BROWSER->Alert->SetStatus(false,true);
		else
			$actionData .= $BROWSER->Alert->GetCommand();
	}
	if(!empty($BROWSER->OverlayBox) && !$BROWSER->OverlayBox->Closed && !isnull($action = $EVENTS->GetActionById($BROWSER->OverlayBox->EventActionId)))
	{
		if(isset($_GET["confol"]))
			$BROWSER->OverlayBox->SetStatus(true);
		else
		{
			$boxHTML = applyReplacements($BROWSER->OverlayBox->CreateOverlayTemplate($action->OverlayBox->Style,$CONFIG["gl_site_name"],$action->OverlayBox->Width,$action->OverlayBox->Height,LIVEZILLA_URL));
			$BROWSER->OverlayBox->OverlayElement = $action->OverlayBox;
			$BROWSER->OverlayBox->OverlayElement->HTML = $boxHTML;
			$actionData .= $BROWSER->OverlayBox->OverlayElement->GetCommand();
		}
	}
	return $actionData;
}

function getPollFrequency()
{
	global $CONFIG,$EXTERNALUSER;
	if($EXTERNALUSER->IsInChat(true))
		return min(5,$CONFIG["poll_frequency_tracking"]);
	//if(!empty($CONFIG["gl_stmo"]))
		//return $CONFIG["poll_frequency_tracking"];
	//$row = mysql_fetch_array(queryDB(true,"SELECT COUNT(*) AS `ocount` FROM `".DB_PREFIX.DATABASE_OPERATORS ."` WHERE `last_active` > ".(time()-$CONFIG["timeout_clients"])), MYSQL_BOTH);
	//if($row["ocount"] > 0)
		return $CONFIG["poll_frequency_tracking"];
	//return $CONFIG["timeout_track"]-10;
}

function getJSCustomArray($_getCustomParams="",$_fromHistory=null)
{
	global $INPUTS;
	initData(false,false,false,false,false,false,false,true);
	$valArray=array();
	foreach($INPUTS as $index => $input)
	{
		if($input->Active && $input->Custom)
		{
			if(isset($_GET["cf".$input->Index]))
				$valArray[$index] = "'" . getParam("cf".$input->Index) . "'";
			else if(!isnull(getCookieValue("cf_" . $input->Index)) && $input->Cookie)
				$valArray[$index] = "'" . base64UrlEncode(getCookieValue("cf_" . $input->Index)) . "'";
			else if(is_array($_fromHistory) && isset($_fromHistory[$input->Index]) && !empty($_fromHistory[$input->Index]))
				$valArray[$index] = "'" . base64UrlEncode($_fromHistory[$input->Index]) . "'";
			else
				$valArray[$index] = "''";
		}
		else if($input->Custom)
			$valArray[$index] = "''";
	}
	ksort($valArray);
	foreach($valArray as $param)
	{
		if(!empty($_getCustomParams))
			$_getCustomParams .= ",";
		$_getCustomParams .= $param;
	}
	return $_getCustomParams;
}

function getCustomParams($_getParams="",$_fromHistory=null)
{
	foreach($_GET as $key => $value)
		if(strlen($key) == 3 && substr($key,0,2) == "cf")
			$_getParams.=  "&" . $key ."=" . base64UrlEncode(base64UrlDecode($value));
			
	if($_getParams=="" && is_array($_fromHistory))
		foreach($_fromHistory as $key => $value)
			if(!empty($value))
				$_getParams.=  "&cf" . $key ."=" . base64UrlEncode($value);
	return $_getParams;
}
?>