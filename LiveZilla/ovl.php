<?php
/****************************************************************************************
* LiveZilla chat.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

@set_time_limit(15);

if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");
	
@ini_set('session.use_cookies', '0');
@error_reporting(E_ALL);
$content_frames = array("lz_chat_frame.3.2.lgin.1.0","lz_chat_frame.3.2.mail.1.0","lz_chat_frame.3.2.chat.1.0","lz_chat_frame.3.2.chat.0.0","lz_chat_frame.3.2.chat.2.0");

require(LIVEZILLA_PATH . "_lib/functions.external.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.external.inc.php");

@set_time_limit($CONFIG["timeout_clients"]);
if(!isset($_GET["file"]))
	@set_error_handler("handleError");
if(!isset($_GET["browid"]))
	exit();

languageSelect();
initData(true,true,false,true,false,false,false,true);

$USER = new Visitor(base64UrlDecode(getParam(GET_TRACK_USERID)));
$USER->Load();

array_push($USER->Browsers,new VisitorChat($USER->UserId,$USER->UserId . "_OVL"));
array_push($USER->Browsers,$BROWSER);

$GroupBuilder = new GroupBuilder($INTERNAL,$GROUPS,$CONFIG,$USER->Browsers[0]->DesiredChatGroup,$USER->Browsers[0]->DesiredChatPartner,false);
$GroupBuilder->Generate(null,true);

$USER->Browsers[0]->Overlay = true;
$USER->Browsers[0]->Load();

if($USER->Browsers[0]->FirstCall)
	$USER->AddFunctionCall("lz_chat_init_data_change(null,null);",false);

if(IS_FILTERED)
{
	$USER->Browsers[0]->CloseChat();
	$USER->Browsers[0]->Destroy();
	$USER->AddFunctionCall("lz_tracking_remove_overlay_chat();",true);
}

$USER->Browsers[0]->LoadForward(false);
$USER->Browsers[1]->LoadChatRequest();

if(!empty($USER->Browsers[0]->Forward) && (!$GROUPS[$USER->Browsers[0]->Forward->TargetGroupId]->IsHumanAvailable(true,true) || (!empty($USER->Browsers[0]->Forward->TargetSessId) && @$INTERNAL[$USER->Browsers[0]->Forward->TargetSessId]->UserStatus >= USER_STATUS_OFFLINE)))
{
	$USER->Browsers[0]->Forward->Destroy();
	$USER->Browsers[0]->Forward = null;
	$USER->Browsers[0]->ExternalClose();
	$USER->Browsers[0]->Save();
	$USER->Browsers[0]->Load();
}

if(defined("IGNORE_WM") && !empty($USER->Browsers[0]->DesiredChatPartner) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->IsBot)
	$USER->Browsers[0]->DesiredChatPartner = "";

if(!empty($USER->Browsers[1]->ChatRequest) && $USER->Browsers[1]->ChatRequest->Closed && !$USER->Browsers[1]->ChatRequest->Accepted && @$INTERNAL[$USER->Browsers[1]->ChatRequest->SenderSystemId]->UserStatus < USER_STATUS_OFFLINE && $INTERNAL[$USER->Browsers[1]->ChatRequest->SenderSystemId]->IsExternal($GROUPS,null,array($USER->Browsers[1]->ChatRequest->SenderGroupId),true))
{
	$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[1]->ChatRequest->SenderSystemId;
	$USER->Browsers[0]->DesiredChatGroup = $USER->Browsers[1]->ChatRequest->SenderGroupId;
	$OPERATOR_COUNT = 1;
}
else if(!(!empty($USER->Browsers[0]->Forward) && !empty($USER->Browsers[0]->DesiredChatGroup)))
{
	if(!empty($_GET[GET_EXTERN_INTERN_USER_ID]))
		$USER->Browsers[0]->DesiredChatPartner = getInternalSystemIdByUserId(base64UrlDecode(getParam(GET_EXTERN_INTERN_USER_ID)));
	if(!empty($USER->Browsers[0]->InitChatWith))
		$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->InitChatWith;
	if(!empty($USER->Browsers[0]->Forward))
		$USER->Browsers[0]->DesiredChatPartner = "";
	if(!(!empty($USER->Browsers[0]->DesiredChatPartner) && !empty($USER->Browsers[0]->DesiredChatGroup) && !empty($USER->Browsers[0]->InternalUser)))
		$USER->Browsers[0]->DesiredChatGroup = $GroupBuilder->GetTargetGroup($OPERATOR_COUNT,$USER->Browsers[0]->DesiredChatPartner);
	else
		$OPERATOR_COUNT = 1;
}
else
	$OPERATOR_COUNT = 1;
	
$HUMAN = false;
$BOTMODE = empty($USER->Browsers[0]->Forward);
$WELCOME_MANAGER = false;
$REPOLL = false;

$count = 0;
foreach($INTERNAL as $sysId => $internaluser)
{
	$isex = $internaluser->IsExternal($GROUPS, null, array($USER->Browsers[0]->DesiredChatGroup), true, $USER->Browsers[0]->DesiredChatPartner==$sysId, ($USER->Browsers[0]->DesiredChatPartner==$sysId && !empty($USER->Browsers[0]->Forward)));
	if($isex && $internaluser->Status < USER_STATUS_OFFLINE && !$internaluser->Deactivated)
	{
		$count++;
		if(!$internaluser->IsBot && !$WELCOME_MANAGER)
			$BOTMODE = false;
		if($internaluser->IsBot && $internaluser->WelcomeManager && !defined("IGNORE_WM"))
			$BOTMODE = $WELCOME_MANAGER = true;
		if(!$internaluser->IsBot)
		{
			$HUMAN = true;
			if(!empty($USER->Browsers[0]->InitChatWith) && $sysId == $USER->Browsers[0]->InitChatWith)
			{
				$BOTMODE = $WELCOME_MANAGER = false;
				break;
			}
		}
	}
}

if($count == 0)
{
	$BOTMODE = false;
	$HUMAN = false;
	$OPERATOR_COUNT = 0;
}

if(defined("IGNORE_WM") && (empty($USER->Browsers[0]->DesiredChatGroup) || !$HUMAN))
	$USER->AddFunctionCall("lz_chat_set_talk_to_human(false,false);",false);

$ponline = !empty($_GET["ca"]);
$conline = $OPERATOR_COUNT > 0;
$text = ($conline) ? $_GET["ovlt"] : $_GET["ovlto"];
$icw = false;
$chat = "";

if(!empty($_GET["pc"]) && $_GET["pc"] == 1)
{
	$chat = str_replace("<!--server-->",LIVEZILLA_URL,getFile(TEMPLATE_HTML_OVERLAY_CHAT));
	if(!empty($USER->Browsers[0]->DesiredChatGroup))
	{
		$pdm = getPredefinedMessage($GROUPS[$USER->Browsers[0]->DesiredChatGroup]->PredefinedMessages,$USER);
		if($pdm != null && !empty($pdm->TicketInformation))
			$chat = str_replace("<!--ticket_information-->",$pdm->TicketInformation,$chat);
	}
	$chat = str_replace("<!--ticket_information-->","<!--lang_client_ticket_information-->",$chat);
    $chat = applyReplacements($chat,true,false);
	$chat = str_replace("<!--shadow-->",((!empty($_GET["ovls"]))?"shadow_":""),$chat);
	$chat = str_replace("<!--bgc-->",base64UrlDecode($_GET["ovlc"]),$chat);
	$chat = str_replace("<!--tc-->",base64UrlDecode($_GET["ovlct"]),$chat);
	$chat = str_replace("<!--bcl-->",((empty($CONFIG["gl_pr_nbl"])) ? "" : "visibility:hidden;"),$chat);
	$chat = str_replace("<!--apo-->",((!empty($_GET["ovlapo"])) ? "" : "display:none;"),$chat);
	$chat = str_replace("<!--offer_transcript-->",((!empty($CONFIG["gl_soct"])) ? "":"none"),$chat);
}

if(($USER->Browsers[0]->Status > CHAT_STATUS_OPEN || !empty($USER->Browsers[0]->InitChatWith) || $USER->Browsers[0]->Waiting) && !$USER->Browsers[0]->Closed)
	$ACTIVE_OVLC = $conline = !$USER->Browsers[0]->Declined;
else if($USER->Browsers[0]->Closed && $USER->Browsers[0]->LastActive > (time()-$CONFIG["timeout_clients"]) || !empty($_GET["mi0"]))
	$ACTIVE_OVLC = !$USER->Browsers[0]->Declined;

if(!empty($USER->Browsers[0]->DesiredChatGroup) && !IS_FILTERED)
{
	$changed=false;
	$USER->Browsers[0]->Fullname = $USER->Browsers[1]->Fullname = getOptionalParam("en",$USER->Browsers[0]->Fullname,$changed);
	$USER->Browsers[0]->Email = $USER->Browsers[1]->Email = getOptionalParam("ee",$USER->Browsers[0]->Email,$changed);
	$USER->Browsers[0]->Company = getOptionalParam("ec",$USER->Browsers[0]->Company,$changed);
	$USER->Browsers[0]->Code = getOptionalParam("code",$USER->Browsers[0]->Code,$changed);
	$USER->Browsers[0]->Phone = getOptionalParam("ep",$USER->Browsers[0]->Phone,$changed);
	$USER->Browsers[0]->Question = getOptionalParam("eq",$USER->Browsers[0]->Question,$changed);
	
	if(empty($USER->Browsers[0]->Question) && !empty($_GET["mp0"]))
	{
		$USER->Browsers[0]->Question = cutString(base64UrlDecode($_GET["mp0"]),255);
		$changed = true;
	}
	
	if($changed)
	{
		$USER->Browsers[0]->SaveLoginData();
		$USER->Browsers[1]->SaveLoginData();
		$USER->Browsers[0]->ApplyCustomValues($_GET,"cf",true);
		$USER->UpdateOverlayDetails($USER->Browsers[0]->Fullname,$USER->Browsers[0]->Email);
	}

    if(!$conline && !empty($_GET["ovloo"]))
        $USER->AddFunctionCall("if(lz_session.OVLCState == '0')lz_tracking_remove_overlay_chat();",false);
    else if(!empty($_GET["pc"]) && $_GET["pc"] == 1)
	    $TRACKINGSCRIPT .= "lz_tracking_add_overlay_chat('".base64_encode($chat)."','".base64_encode(base64UrlDecode($text))."',280,378,".getOptionalParam("ovlml",0).",".getOptionalParam("ovlmt",0).",".getOptionalParam("ovlmr",0).",".getOptionalParam("ovlmb",0).",'".getOptionalParam("ovlp",21)."',true,".parseBool($conline).");";

    if(!empty($_GET[GET_TRACK_CLOSE_CHAT_WINDOW]) && $_GET[GET_TRACK_CLOSE_CHAT_WINDOW]=="1")
	{
		if((!empty($USER->Browsers[0]->InternalUser) && !$USER->Browsers[0]->InternalUser->IsBot) || $USER->Browsers[0]->Waiting)
		{
			$USER->Browsers[0]->ExternalClose();
			$USER->Browsers[0]->Destroy();
		}
	}

	$lpr = "null";
	$LMR = "null";
	
	$chat_available = $BOTMODE;
	$FULL = (!empty($_GET["full"]));
	
	$LPRFLAG = (!empty($_GET["lpr"])) ? base64UrlDecode($_GET["lpr"]) : "";
	$LMRFLAG = (!empty($_GET["lmr"])) ? base64UrlDecode($_GET["lmr"]) : "";
	$LASTPOSTER = (!empty($_GET["lp"])) ? base64UrlDecode($_GET["lp"]) : "";
	
	if($USER->Browsers[0]->Declined)
		$chat_available = false;
	else if($USER->Browsers[0]->Status > CHAT_STATUS_OPEN && !$USER->Browsers[0]->Closed)
	{
		$USER->Browsers[0]->UpdateArchive($USER->Browsers[0]->Email,$USER->Browsers[0]->Email,$USER->Browsers[0]->Fullname);
		$chat_available = true;
		if(!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->IsBot)
			if(($OPERATOR_COUNT > 0 && !$BOTMODE) && !$USER->Browsers[0]->ExternalClosed)
			{
				foreach($USER->Browsers[0]->Members as $sid => $member)
					if(!$INTERNAL[$sid]->IsBot)
						$USER->Browsers[0]->LeaveChat($sid);
				$USER->Browsers[0]->ExternalClose();
				$USER->Browsers[0]->Closed = true;
			}
		if($USER->Browsers[0]->Activated == CHAT_STATUS_ACTIVE && $USER->Browsers[0]->Status != CHAT_STATUS_ACTIVE)
			$USER->Browsers[0]->SetStatus(CHAT_STATUS_ACTIVE);
	}
	else
		$chat_available = $OPERATOR_COUNT > 0;
	
	$LANGUAGE = false;
	
	if(!$chat_available)
		$USER->AddFunctionCall("lz_chat_set_connecting(false,'".$USER->Browsers[0]->SystemId."');lz_chat_set_host(null,'".$USER->Browsers[0]->ChatId."','','',0);",false);
	
	$pc = 0;
	if(!empty($USER->Browsers[0]->QueuedPosts))
	{
		if(!$USER->Browsers[0]->Waiting)
		{
			while(!empty($_GET["mi".$pc])){$pc++;}
			foreach($USER->Browsers[0]->QueuedPosts as $id => $postar)
			{
				$_GET["mp".$pc] = $postar[0];
				$_GET["mi".$pc] = base64UrlEncode($id);
				$_GET["mrid".$pc] = $id;
				$_GET["mc".$pc++] = $postar[1];
				
				queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `id`='".@mysql_real_escape_string($id)."' LIMIT 1;");
			}
			$pc = 0;
			$USER->Browsers[0]->QueuedPosts = array();
		}
	}

	if(!empty($_GET["mi".$pc]) || $USER->Browsers[0]->Waiting || !empty($USER->Browsers[0]->InitChatWith) || (!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Received && $USER->Browsers[0]->Forward->Processed))
	{
		if($USER->Browsers[0]->Waiting && $BOTMODE && !empty($USER->Browsers[0]->QueuedPosts))
			$USER->Browsers[0]->QueuedPosts = array();
		else
		{
			initChat();
		}
			
		if(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Received && $USER->Browsers[0]->Forward->Processed)
		{
			$USER->Browsers[0]->Forward->Save(true,true);
			$ACTIVE_OVLC = !$USER->Browsers[0]->Declined;
		}
	}
	
	if(!empty($USER->Browsers[0]->ChatId))
		$USER->AddFunctionCall("lz_chat_id='".$USER->Browsers[0]->ChatId."';",false);
	
	$HTML = "";
	$USER->Browsers[0]->VisitId = $USER->VisitId;
	
	while(!empty($_GET["mi".$pc]))
	{
		$id = (!empty($_GET["mrid".$pc])) ? $_GET["mrid".$pc] : md5($USER->Browsers[0]->SystemId . $USER->Browsers[0]->ChatId . $_GET["mi".$pc]);
		$post = new Post($id,$USER->Browsers[0]->SystemId,"",base64UrlDecode($_GET["mp".$pc]),((!empty($_GET["mc".$pc]))?$_GET["mc".$pc]:time()),$USER->Browsers[0]->ChatId,$USER->Browsers[0]->Fullname);
		$post->BrowserId = $BROWSER->BrowserId;
		$saved = false;
			
		if(!$USER->Browsers[0]->Waiting)
		{
			foreach($GROUPS as $groupid => $group)
				if($group->IsDynamic && !empty($group->Members[$USER->Browsers[0]->SystemId]))
				{
					foreach($group->Members as $member)
						if($member != $USER->Browsers[0]->SystemId)
						{
							if(!empty($INTERNAL[$member]))
								processPost($id,$post,$member,$pc,$groupid,$USER->Browsers[0]->ChatId);
							else
								processPost($id,$post,$member,$pc,$groupid,getValueBySystemId($member,"chat_id",""));
							$saved = true;
						}
					$pGroup=$group;
				}
	
			foreach($USER->Browsers[0]->Members as $systemid => $member)
			{
				if(!empty($member->Declined))
					continue;
					
				if(!empty($INTERNAL[$systemid]) && !empty($pGroup->Members[$systemid]))
					continue;
					
				if(!(!empty($pGroup) && !empty($INTERNAL[$systemid])))
					$saved = processPost($id,$post,$systemid,$pc,$USER->Browsers[0]->SystemId,$USER->Browsers[0]->ChatId,$INTERNAL[$systemid]->IsBot);
			}
	
			if(!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->IsBot)
			{
				$rpost = new Post($id = getId(32),$USER->Browsers[0]->InternalUser->SystemId,$USER->Browsers[0]->SystemId,$answer=$USER->Browsers[0]->InternalUser->GetBotAnswer($post->Text,true,$USER->Browsers[0]),time(),$USER->Browsers[0]->ChatId,$USER->Browsers[0]->InternalUser->Fullname);
                $USER->AddFunctionCall("lz_chat_input_bot_state(true,false);",false);
                $rpost->ReceiverOriginal = $rpost->ReceiverGroup = $USER->Browsers[0]->SystemId;
				$rpost->Save();
				$saved = true;
				foreach($USER->Browsers[0]->Members as $opsysid => $member)
				{
					if($opsysid != $USER->Browsers[0]->InternalUser->SystemId)
					{
						$rpost = new Post($id,$USER->Browsers[0]->InternalUser->SystemId,$opsysid,$answer,time(),$USER->Browsers[0]->ChatId,$INTERNAL[$systemid]->Fullname);
						$rpost->ReceiverOriginal = $rpost->ReceiverGroup = $USER->Browsers[0]->SystemId;
						$rpost->Save();
					}
				}
			}
			if($saved)
				$USER->AddFunctionCall("lz_chat_release_post('".base64UrlDecode($_GET["mi".$pc])."');",false);
		}
		else
		{
			processPost($id,$post,"",$pc,$USER->Browsers[0]->SystemId,$USER->Browsers[0]->ChatId,false);
			$USER->Browsers[0]->QueuedPosts[$id] = array(0=>$_GET["mp".$pc],1=>time(),2=>$BROWSER->BrowserId);
			$USER->AddFunctionCall("lz_chat_release_post('".base64UrlDecode($_GET["mi".$pc])."');",false);
		}
		$pc++;
	}
	
	$startTime = 0;
	$isOp = false;
	if($USER->Browsers[0]->Status == CHAT_STATUS_ACTIVE)
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` WHERE `chat_id`='".@mysql_real_escape_string($USER->Browsers[0]->ChatId)."' ORDER BY `status` DESC, `dtime` DESC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			if(isset($INTERNAL[$row["user_id"]]))
			{
				$ChatMember = new ChatMember($row["user_id"],$row["status"],!empty($row["declined"]),$row["jtime"],$row["ltime"]);
				if($ChatMember->Status == 1 && $ChatMember->Joined >= $USER->Browsers[0]->LastActive)
				{
					$isOp = true;
					addHTML(str_replace("<!--message-->",str_replace("<!--intern_name-->",$INTERNAL[$ChatMember->SystemId]->Fullname,$LZLANG["client_intern_arrives"]),getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_STATUS)),"sys","LMMJ".$ChatMember->SystemId);
				}
				else if(($ChatMember->Status == 9 || $ChatMember->Status == 2) && $ChatMember->Left >= $USER->Browsers[0]->LastActive && $ChatMember->Joined > 0)
				{
					addHTML(leaveChatHTML(false,$INTERNAL[$ChatMember->SystemId]->Fullname),"sys","LCM01".$ChatMember->SystemId);
				}
				if($ChatMember->Status == 0)
				{
					$startTime = $ChatMember->Joined;
					$isOp = true;
				}
			}
	}
	else
		$isOp = true;
		
	$startTime = max($startTime,$USER->Browsers[0]->AllocatedTime);
	
	$USER->Browsers[0]->Typing = isset($_GET["typ"]);

	if(!$USER->Browsers[0]->Declined)
		$USER->Browsers[0]->Save();
		
	if(($USER->Browsers[0]->Waiting && $BOTMODE) || (empty($USER->Browsers[0]->InternalUser) && !empty($_GET["op"]) && !$INTERNAL[$_GET["op"]]->IsBot) || (!empty($_GET["op"]) && empty($USER->Browsers[0]->ChatId) && !$BOTMODE) || !$isOp || $USER->Browsers[0]->Closed || (!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->Status == USER_STATUS_OFFLINE))
	{
		if(!$USER->Browsers[0]->ExternalClosed)
		{
			$USER->Browsers[0]->ExternalClose();
			$USER->Browsers[0]->Save();
			$USER->Browsers[0]->Load();
		}
		$USER->Browsers[0]->Members = array();
		if(!empty($_GET["op"]) && !empty($INTERNAL[$_GET["op"]]) && $isOp)
		{
			addHTML(leaveChatHTML(true,$INTERNAL[$_GET["op"]]->Fullname),"sys","LCM01" . $_GET["op"]);
			$LMRFLAG = "null";
			$USER->Browsers[0]->InternalUser = null;
			$_GET["op"] = "";
			$REPOLL = true;
		}
	}

	if(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Invite && !empty($USER->Browsers[0]->Forward->TargetGroupId) && !$USER->Browsers[0]->Forward->Processed)
	{
		if(!$USER->Browsers[0]->Forward->Processed)
		{
			$USER->Browsers[0]->LeaveChat($USER->Browsers[0]->Forward->InitiatorSystemId);
			$USER->Browsers[0]->Forward->Save(true);
			$USER->Browsers[0]->ExternalClose();
			$USER->Browsers[0]->DesiredChatGroup = $USER->Browsers[0]->Forward->TargetGroupId;
			$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->Forward->TargetSessId;
			$USER->Browsers[0]->FirstActive=time();
			$USER->Browsers[0]->Save(true);
			$USER->Browsers[0]->SetCookieGroup();
			$USER->AddFunctionCall("lz_chat_set_host(null,'".$USER->Browsers[0]->ChatId."','','',4);",false);
		}
		if(!empty($INTERNAL[$USER->Browsers[0]->Forward->SenderSystemId]) && $USER->Browsers[0]->InternalActivation)
        {
			if(!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->IsBot)
                $USER->AddFunctionCall("lz_chat_set_talk_to_human(true,true);",false);
            else
                addHTML(leaveChatHTML(true,$INTERNAL[$USER->Browsers[0]->Forward->SenderSystemId]->Fullname,"&nbsp;" . $LZLANG["client_forwarding"]),"sys","LCM02");
        }
        $ACTIVE_OVLC = !$USER->Browsers[0]->Declined;
	}
	else if($chat_available && ((empty($USER->Browsers[0]->Forward) && !(!empty($USER->Browsers[1]->ChatRequest) && !$USER->Browsers[1]->ChatRequest->Closed) && empty($USER->Browsers[0]->InternalUser) && !$USER->Browsers[0]->Waiting) || (!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->IsBot && $LMRFLAG=="ONM01") || $FULL))
	{
		if(($LMRFLAG!="ONM01" || $FULL) && (!$BOTMODE || (!empty($USER->Browsers[0]->InternalUser) && !$USER->Browsers[0]->InternalUser->IsBot) || (!empty($USER->Browsers[1]->ChatRequest) && !$USER->Browsers[1]->ChatRequest->Closed)))
		{
			$USER->AddFunctionCall("lz_chat_set_talk_to_human(true,false);",false);
			addHTML(str_replace("<!--message-->",$LZLANG["client_chat_available"],getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_STATUS)),"sys","ONM01");
			
			if(!empty($USER->Browsers[0]->ChatId) && !$USER->Browsers[0]->InternalActivation && !empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Invite && !empty($USER->Browsers[0]->Forward->TargetGroupId) && $USER->Browsers[0]->Forward->Processed)
				addHTML(str_replace("<!--message-->",($LZLANG["client_forwarding"]) ,getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_STATUS)),"sys","ONM01");
		}
		else if($BOTMODE && (($LMRFLAG!="OBM01" || $FULL) && ( (empty($USER->Browsers[0]->InternalUser) && empty($_GET["op"])) || (!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->IsBot))))
		{
			getInternal("",0,null,true,true,true);
			if(!empty($INTERNAL[$USER->Browsers[0]->DesiredChatPartner]) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->IsBot)
			{
				$INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->LoadPictures();
				$text = ($HUMAN) ? @$LZLANG["client_now_speaking_to_va"] : @$LZLANG["client_now_speaking_to_va_offline"];
                $USER->AddFunctionCall("lz_chat_input_bot_state(true,false);",false);
				$image = "<img class=\"lz_overlay_chat_operator_picture\" src=\"".LIVEZILLA_URL . $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->GetOperatorPictureFile()."\" width=\"52\" height=\"39\">";
				addHTML(pictureHTML(str_replace("<!--operator_name-->",$INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->Fullname,$text),$image),"sys","OBM01");
				$USER->AddFunctionCall("lz_chat_set_host('" . $USER->Browsers[0]->DesiredChatPartner . "','".$USER->Browsers[0]->ChatId."','".$USER->Browsers[0]->DesiredChatGroup."','',1);",false);
			}
		}
	}

	$bottitle = ($BOTMODE && !empty($INTERNAL[$USER->Browsers[0]->DesiredChatPartner]) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->IsBot) ? base64_encode(str_replace(array("%name%","%operator_name%"),$INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->Fullname,$LZLANG["client_bot_overlay_title"])) : "";
	$USER->AddFunctionCall("lz_chat_set_application(".parseBool($chat_available).",".parseBool($BOTMODE && !$HUMAN && !(!empty($INTERNAL[$USER->Browsers[0]->DesiredChatPartner]) && !$INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->IsBot)).",".parseBool($HUMAN).",'".$bottitle."');",false);
	
	if(!empty($USER->Browsers[0]->Fullname) || !empty($USER->Browsers[0]->Email))
		$USER->AddFunctionCall("lz_chat_set_name('".base64_encode($USER->Browsers[0]->Fullname)."','".base64_encode($USER->Browsers[0]->Email)."');",false);
	
	if(!empty($USER->Browsers[1]->ChatRequest) && $INTERNAL[$USER->Browsers[1]->ChatRequest->SenderSystemId]->IsExternal($GROUPS,null,null,true))
	{
		if(!$USER->Browsers[1]->ChatRequest->Closed && !$USER->Browsers[1]->ChatRequest->Accepted)
		{
			if($FULL)
				$USER->Browsers[1]->ChatRequest->Displayed = false;
			if(!$USER->Browsers[1]->ChatRequest->Displayed)
			{
				$USER->Browsers[1]->ChatRequest->Load();
				addHTML(inviteHTML($USER->Browsers[1]->ChatRequest->SenderSystemId,$USER->Browsers[1]->ChatRequest->Text,$USER->Browsers[1]->ChatRequest->Id),"sys","");
				$USER->AddFunctionCall("lz_desired_operator='".$INTERNAL[$USER->Browsers[1]->ChatRequest->SenderSystemId]->UserId."';",false);
				$USER->AddFunctionCall("lz_chat_invite_timer=setTimeout('lz_chat_change_state(false,false);',5000);",false);
				$USER->AddFunctionCall("lz_chat_set_talk_to_human(true,false);",false);
				$USER->Browsers[1]->ChatRequest->SetStatus(true,false,false);
			}
			if(!empty($_GET["mi0"]))
			{
				$USER->Browsers[1]->ChatRequest->SetStatus(true,true,false,true);
				$USER->Browsers[1]->ForceUpdate();
			}
		}
	}
	
	$tymes = (!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->Typing==$USER->Browsers[0]->SystemId) ? "'".base64_encode(str_replace("<!--operator_name-->",$USER->Browsers[0]->InternalUser->Fullname,$LZLANG["client_representative_is_typing"]))."'" : "null";
	$USER->AddFunctionCall("lz_chat_set_typing(".$tymes.",false);",false);
	
	$maxposts = 30;
	$spkthtml = speakingToHTML();
	$posthtml = "";
	$pstrchngreq = $psound = $spkt = false;
	
	$oppostcount = 0;
	$LASTPOST = "";
	$lppflag = $LASTPOSTER;
	$rand = rand();

	if(!$USER->Browsers[0]->Declined && $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `chat_id`='".@mysql_real_escape_string($USER->Browsers[0]->ChatId)."' AND `chat_id`!='' AND (`receiver`='".@mysql_real_escape_string($USER->Browsers[0]->SystemId)."' OR (`sender`='".@mysql_real_escape_string($USER->Browsers[0]->SystemId)."' AND `repost`=0)) GROUP BY `id` ORDER BY `time` ASC, `micro` ASC;"))
	{
		$all = mysql_num_rows($result);
		$toshow = min($maxposts,$all);
		if($all > 0)
		{
			$count = $maxposts-$all;
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if($count++ >= 0)
				{
					$postobj = new Post($row);
					if(empty($INTERNAL[$postobj->Sender]))
						$postobj->Text = htmlentities($postobj->Text,ENT_QUOTES,'UTF-8');
					
					if($USER->Browsers[0]->AllocatedTime > 0 && $USER->Browsers[0]->AllocatedTime && !$spkt)
					{
						$lppflag = "sys";
						$posthtml .= $spkthtml;
						$spkt = true;
					}
					$post = postHTML($postobj->Text,($lppflag != $postobj->Sender || $pstrchngreq),$postobj->Sender != $USER->Browsers[0]->SystemId,(($postobj->Sender != $USER->Browsers[0]->SystemId) ? $postobj->SenderName : $USER->Browsers[0]->Fullname));
				
					$pstrchngreq = false;
					if($postobj->Sender != $USER->Browsers[0]->SystemId)
						$oppostcount++;
						
					if(!$postobj->Received && $postobj->Sender != $USER->Browsers[0]->SystemId)
						$psound = true;
					
					$postobj->MarkReceived($USER->Browsers[0]->SystemId);
					if($FULL || $postobj->Sender != $USER->Browsers[0]->SystemId || $postobj->BrowserId != $BROWSER->BrowserId)
						$lppflag = $postobj->Sender;
					if(empty($_GET["full"]) && $postobj->Id == $LPRFLAG)
					{
						$psound = false;
						$posthtml = $spkthtml;
						$spkt = true;
						$oppostcount = 0;
						$lppflag = (!empty($spkthtml)) ? "sys" : $LASTPOSTER;
						if($USER->Browsers[0]->AllocatedTime > 0 && $postobj->Created < $USER->Browsers[0]->AllocatedTime)
							$pstrchngreq = true;
					}
					else
					{
						if($FULL || $postobj->Sender != $USER->Browsers[0]->SystemId || $postobj->BrowserId != $BROWSER->BrowserId)
							$posthtml .= $post;
					}
						
					$lpr = "'".base64_encode($postobj->Id)."'";
					
					if($postobj->Sender == $USER->Browsers[0]->SystemId)
						$LASTPOST = $postobj->Text;
				}
			}
		}
	}

	if($FULL)
		$oppostcount=0;

	if($lppflag == $USER->Browsers[0]->SystemId)
		$oppostcount=-1;
		
	if(!empty($spkthtml) && !$spkt)
		addHTML($spkthtml,"sys","SPKT" . $USER->Browsers[0]->InternalUser->SystemId);
	
	if(!empty($posthtml))
		addHTML($posthtml,$lppflag);

	if(!empty($LASTPOST))
		$USER->AddFunctionCall("lz_chat_set_last_post('".base64_encode($LASTPOST)."');",false);
	
	if($psound)
		$USER->AddFunctionCall("lz_chat_play_sound();",false);
	
	if(!empty($_GET["tid"]))
	{
		if($ticket = $USER->SaveTicket($USER->Browsers[0]->DesiredChatGroup,base64UrlDecode($_GET["tin"]),base64UrlDecode($_GET["tie"]),"",$USER->GeoCountryISO2,"",false,base64UrlDecode($_GET["tim"]),true))
		{
			$USER->Browsers[0]->SaveLoginData();
			Visitor::SendTicketAutoresponder($ticket,$USER->Language,false);
		}
	}

	$HTML = str_replace("<!--server-->",LIVEZILLA_URL,$HTML);
	
	if($LANGUAGE)
		$HTML = applyReplacements($HTML,$LANGUAGE,false);

	if(!$chat_available && !$USER->Browsers[0]->Declined)
		addHTML(statusHTML(@$LZLANG["client_chat_not_available"]),"sys","OFM01");
	
	if(!empty($HTML))
		$USER->AddFunctionCall("lz_chat_add_html_element('".base64_encode($HTML)."',true,".$lpr.",".$LMR.",'".base64_encode($LASTPOSTER)."','".@$_GET["lp"]."',".$oppostcount.");",false);

	$USER->AddFunctionCall("lz_chat_set_connecting(".parseBool(!$BOTMODE && (!empty($USER->Browsers[0]->ChatId) && !$USER->Browsers[0]->InternalActivation && !$USER->Browsers[0]->Closed && !$USER->Browsers[0]->Declined)).",'".$USER->Browsers[0]->SystemId."',".parseBool(!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->Status==USER_STATUS_AWAY).");",false);

	if($REPOLL)
		$USER->AddFunctionCall("lz_tracking_poll_server(1211);",false);
		
	if($FULL)
		$USER->AddFunctionCall("lz_chat_change_fullname(lz_external.Username);",false);
}
/*
else if(!empty($_GET["ovloo"]))
{
    if(!empty($_GET["typ"]) || !empty($_GET["mi0"]) || !empty($USER->Browsers[0]->InternalUser))
    {
        addHTML(statusHTML(@$LZLANG["client_chat_not_available"]),"sys","OFM01");
        $USER->AddFunctionCall("lz_chat_add_html_element('".base64_encode($HTML)."',true,'OFM01','kk','".base64_encode("sys")."','".@$_GET["lp"]."',0);",false);
        $USER->AddFunctionCall("lz_chat_set_connecting(false,'".$USER->Browsers[0]->SystemId."',false);",false);
        $pc=0;
        while(!empty($_GET["mi".$pc]))
        {
            $USER->AddFunctionCall("lz_chat_release_post('".base64UrlDecode($_GET["mi".$pc])."');",false);
            $pc++;
        }
        $USER->Browsers[0]->CloseChat();
    }
    else
        $USER->AddFunctionCall("if(lz_session.OVLCState == '0')lz_tracking_remove_overlay_chat();",false);
}
*/

$OVLPAGE = $USER->Response;

function postHTML($_text,$_add,$_operator,$_name)
{
	global $LZLANG;
	$post = ($_add) ? ((!$_operator) ? getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_EXTERN) : getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_OPERATOR)) : getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_ADD);
	$post = str_replace("<!--name-->",($_operator) ? $_name : ((!empty($_name)) ? $_name : $LZLANG["client_guest"]),$post);
	$post = str_replace("<!--time-->",date("H:i"),$post);
	$post = str_replace("<!--color-->",($_operator) ? hexDarker(str_replace("#","",base64UrlDecode($_GET["ovlc"])),20) : "#000000",$post);
	return str_replace("<!--message-->",preg_replace('/(<(?!img)\w+[^>]+)(style="[^"]+")([^>]*)(>)/', '${1}${3}${4}', strip_tags($_text,"<a><br><b><ul><li><ol><b><i><u><strong><img>")),$post);
}

function speakingToHTML()
{
	global $USER,$LZLANG;
	$html = "";
	
	if(!empty($USER->Browsers[0]->InternalUser))
	{
		if(!empty($_GET["op"]) && $_GET["op"] != $USER->Browsers[0]->InternalUser->SystemId)
			$_GET["op"]="";
				
		if($USER->Browsers[0]->DesiredChatPartner != $USER->Browsers[0]->InternalUser->SystemId)
		{
			$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->InternalUser->SystemId;
			$USER->Browsers[0]->Save();
		}
		if(!$USER->Browsers[0]->Closed && $USER->Browsers[0]->InternalActivation && empty($_GET["op"]))
		{
			$text = $LZLANG["client_now_speaking_to"];
			if($USER->Browsers[0]->InternalUser->IsBot)
				return;
		
			$USER->Browsers[0]->InternalUser->LoadPictures();
			$image = "<img class=\"lz_overlay_chat_operator_picture\" align=\"left\" src=\"".LIVEZILLA_URL . $USER->Browsers[0]->InternalUser->GetOperatorPictureFile()."\" width=\"52\" height=\"39\">";
			$html .= pictureHTML(str_replace("<!--operator_name-->",$USER->Browsers[0]->InternalUser->Fullname,$text),$image);
			if(!$USER->Browsers[0]->ExternalActivation)
				$USER->Browsers[0]->ExternalActivate();
			$USER->AddFunctionCall("lz_chat_set_host('" . $USER->Browsers[0]->InternalUser->SystemId . "','".$USER->Browsers[0]->ChatId."','".$USER->Browsers[0]->DesiredChatGroup."','" . $USER->Browsers[0]->InternalUser->UserId . "',2);",false);
		}
	}
	return $html;
}

function addHTML($_html,$_poster,$_lmr="")
{
	global $LASTPOSTER,$HTML,$LMRFLAG,$LMR;
	if(!empty($_lmr) && $_lmr == $LMRFLAG)
		return;
	else if(!empty($_lmr))
		$LMR = "'".base64_encode($_lmr)."'";
	$HTML .= $_html;
	$LASTPOSTER = $_poster;
}

function pictureHTML($_text,$_image)
{
	$body = getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_PICTURE);
	$body = str_replace("<!--picture-->",$_image,$body);
	return str_replace("<!--message-->",$_text,$body);
}

function statusHTML($_text)
{
	$body = getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_STATUS);
	return str_replace("<!--message-->",$_text,$body);
}

function inviteHTML($_operatorID,$_text,$_crid)
{
	global $INTERNAL,$LANGUAGE;
	$html = getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_INVITE);
	$html = str_replace("<!--display_image-->","''",$html);
	$html = str_replace("<!--image-->","<img class=\"lz_overlay_chat_operator_picture\" align=\"left\" src=\"".LIVEZILLA_URL.$INTERNAL[$_operatorID]->GetOperatorPictureFile()."\" width=\"52\" height=\"39\">",$html);
	$html = str_replace("<!--font_color-->","#000000",$html);
	$html = str_replace("<!--id-->",$_crid,$html);
	$LANGUAGE = true;
	return str_replace("<!--message-->",str_replace("<!--intern_name-->",$INTERNAL[$_operatorID]->Fullname,$_text),$html);
}

function leaveChatHTML($_host,$_name,$_add="")
{
	global $LZLANG,$USER;
	$html = getFile(TEMPLATE_HTML_MESSAGE_OVERLAY_CHAT_STATUS);
	if($_host)
		$USER->AddFunctionCall("lz_chat_set_host(null,'".$USER->Browsers[0]->ChatId."','','',4);",false);
	return str_replace("<!--message-->",str_replace("<!--intern_name-->",$_name,$LZLANG["client_intern_left"]).$_add,$html);
}

function initChat()
{
	global $INTERNAL,$USER,$INTLIST,$INTBUSY,$CONFIG,$BOTMODE,$LZLANG;
	if(empty($USER->Browsers[0]->ChatId))
	{	
		$USER->Browsers[0]->SetChatId();
		$USER->AddFunctionCall("lz_closed=false;lz_chat_id='".$USER->Browsers[0]->ChatId."';",false);
	}

	if($USER->Browsers[0]->Status == CHAT_STATUS_OPEN)
	{
		if(!empty($USER->Browsers[0]->InitChatWith))
			$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->InitChatWith;
		if(!empty($USER->Browsers[0]->DesiredChatPartner) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->IsBot && !$BOTMODE)
			$USER->Browsers[0]->DesiredChatPartner = null;

		getInternal("",0,null,true,$BOTMODE,$BOTMODE);
		if((count($INTLIST) + $INTBUSY) > 0)
		{
			$chatPosition = getQueuePosition($USER->UserId,$USER->Browsers[0]->DesiredChatGroup);
			//$chatWaitingTime = getQueueWaitingTime($chatPosition,$INTBUSY);
			$USER->Browsers[0]->SetWaiting(!$BOTMODE && !($chatPosition == 1 && count($INTLIST) > 0 && !(!empty($USER->Browsers[0]->InternalUser) && $USER->Browsers[0]->InternalUser->Status == USER_STATUS_BUSY)));
			if(!$USER->Browsers[0]->Waiting)
			{
				if($CONFIG["gl_alloc_mode"] != ALLOCATION_MODE_ALL || !empty($USER->Browsers[0]->DesiredChatPartner))
				{
					$USER->Browsers[0]->CreateChat($INTERNAL[$USER->Browsers[0]->DesiredChatPartner],$USER,true,"","",false);
				}
				else
				{
					foreach($INTLIST as $intid => $am)
						$USER->Browsers[0]->CreateChat($INTERNAL[$intid],$USER,false,"","",false);
				}
				$USER->Browsers[0]->LoadMembers();
			}
			else
            {
                if(!empty($CONFIG["gl_mqwt"]) && (time()-$USER->Browsers[0]->FirstActive) > ($CONFIG["gl_mqwt"]*60))
			    {
				    $USER->AddFunctionCall("lz_chat_set_talk_to_human(false,false);lz_leave_message_required=true",false);
				    $USER->Browsers[0]->UpdateUserStatus(false,false,true,false,false);
			    }
                $USER->Browsers[0]->CreateArchiveEntry(null,$USER);
            }
		}
	}
	else
	{
		if(!$USER->Browsers[0]->ArchiveCreated && !empty($USER->Browsers[0]->DesiredChatPartner))
			$USER->Browsers[0]->CreateChat($INTERNAL[$USER->Browsers[0]->DesiredChatPartner],$USER,true);
	}
}

?>
