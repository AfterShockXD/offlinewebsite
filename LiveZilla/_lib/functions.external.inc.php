<?php
/****************************************************************************************
* LiveZilla functions.external.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/

if(!defined("IN_LIVEZILLA"))
	die();

function listen($_user,$init=false)
{
	global $CONFIG,$GROUPS,$INTERNAL,$USER,$INTLIST,$INTBUSY,$VOUCHER;
	$USER = $_user;
	if(!IS_FILTERED)
	{
		if(!empty($_POST["p_tid"]))
		{
			$VOUCHER = VisitorChat::GetMatchingVoucher(base64UrlDecode($_POST[POST_EXTERN_USER_GROUP]),base64UrlDecode($_POST["p_tid"]));
			if($VOUCHER != null)
				$USER->Browsers[0]->ChatVoucherId = $VOUCHER->Id;
		}
		if(empty($USER->Browsers[0]->ChatId))
		{
			$result = queryDB(true,"SELECT `visit_id` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE `visitor_id`='".@mysql_real_escape_string($USER->Browsers[0]->UserId)."' AND `id`='".@mysql_real_escape_string($USER->Browsers[0]->BrowserId)."' LIMIT 1;");
			if($result && ($row = mysql_fetch_array($result, MYSQL_BOTH)) && $row["visit_id"] != $USER->Browsers[0]->VisitId && !empty($USER->Browsers[0]->VisitId))
			{
				$USER->Browsers[0]->CloseChat(2);
				$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_STOPPED);",false);
				$USER->AddFunctionCall("lz_chat_add_system_text(99,'".base64_encode("Your browser session has expired (" . $row["visit_id"] . "-" . $USER->Browsers[0]->VisitId . "). Please close this browser instance and try again.")."');",false);
				$USER->AddFunctionCall("lz_chat_stop_system();",false);
				return $USER;
			}
			$USER->Browsers[0]->SetChatId();
			$init = true;
		}
		
		if($USER->Browsers[0]->Status == CHAT_STATUS_OPEN)
		{
			initData(true,false,false,false);
			if(!empty($_POST[POST_EXTERN_USER_GROUP]) && (empty($USER->Browsers[0]->DesiredChatGroup) || $init))
				$USER->Browsers[0]->DesiredChatGroup = base64UrlDecode($_POST[POST_EXTERN_USER_GROUP]);
				
			$USER->Browsers[0]->SetCookieGroup();
			if(!getInternal() && $INTBUSY == 0)
			{
				$USER->AddFunctionCall("lz_chat_add_system_text(8,null);",false);
				$USER->AddFunctionCall("lz_chat_stop_system();",false);
			}
			else if((count($INTLIST) + $INTBUSY) > 0)
			{
				$USER->AddFunctionCall("lz_chat_set_id('".$USER->Browsers[0]->ChatId."');",false);
				$chatPosition = getQueuePosition($USER->UserId,$USER->Browsers[0]->DesiredChatGroup);
				$chatWaitingTime = getQueueWaitingTime($chatPosition,$INTBUSY);
				login();
				$USER->Browsers[0]->SetWaiting(!($chatPosition == 1 && count($INTLIST) > 0 && !(!empty($USER->Browsers[0]->DesiredChatPartner) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->Status == USER_STATUS_BUSY)));
				if(!$USER->Browsers[0]->Waiting)
				{
					$USER->AddFunctionCall("lz_chat_show_connected();",false);
					$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_ALLOCATED);",false);
					if($CONFIG["gl_alloc_mode"] != ALLOCATION_MODE_ALL || !empty($USER->Browsers[0]->DesiredChatPartner))
					{
						$USER->Browsers[0]->CreateChat($INTERNAL[$USER->Browsers[0]->DesiredChatPartner],$USER,true);
					}
					else
					{
						$run=0;
						foreach($INTLIST as $intid => $am)
							$USER->Browsers[0]->CreateChat($INTERNAL[$intid],$USER,false,"","",true,($run++==0));
					}
				}
				else
				{
					if(!empty($_GET["acid"]))
					{
                        $USER->AddFunctionCall("lz_chat_show_connected();",false);
						$pchatid = base64UrlDecode($_GET["acid"]);
						$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `visitor_id`='".@mysql_real_escape_string($USER->Browsers[0]->UserId)."' AND `chat_id`='".@mysql_real_escape_string($pchatid)."' AND (`exit` > ".(time()-30)." OR `exit`=0) LIMIT 1;");
						if($result && @mysql_num_rows($result) == 1)
						{
							$row = mysql_fetch_array($result, MYSQL_BOTH);
							if(!empty($row["waiting"]))
							{
								$posts = unserialize($row["queue_posts"]);
								foreach($posts as $post)
									$USER->AddFunctionCall("lz_chat_repost_from_queue('".$post[0]."');",false);
								$USER->AddFunctionCall("lz_chat_data.QueuePostsAdded = true;",false);
							}
						}
					}
				
					if(!empty($CONFIG["gl_mqwt"]) && (time()-$USER->Browsers[0]->FirstActive) > ($CONFIG["gl_mqwt"]*60))
					{
						displayDeclined();
						return $USER;
					}
					
					$pdm = getPredefinedMessage($GROUPS[$USER->Browsers[0]->DesiredChatGroup]->PredefinedMessages,$USER);
					if($pdm != null && !empty($pdm->QueueMessage) && (time()-$USER->Browsers[0]->FirstActive) > $pdm->QueueMessageTime && !$USER->Browsers[0]->QueueMessageShown)
					{
						$USER->Browsers[0]->QueueMessageShown = true;
                        if(empty($_GET["acid"]))
						    $USER->AddFunctionCall("lz_chat_add_system_text(99,'".base64_encode($pdm->QueueMessage)."');",false);
					}

                    if(empty($_GET["acid"]))
					    $USER->AddFunctionCall("lz_chat_show_queue_position(".$chatPosition.",".min($chatWaitingTime,30).");",false);

                    $USER->Browsers[0]->CreateArchiveEntry(null,$USER);
				}
			}
		}
		else
		{
			if(!$USER->Browsers[0]->ArchiveCreated && !empty($USER->Browsers[0]->DesiredChatPartner))
				$USER->Browsers[0]->CreateChat($INTERNAL[$USER->Browsers[0]->DesiredChatPartner],$USER,true);
			activeListen();
		}
	}
	else
		displayFiltered();
	return $USER;
}

function activeListen($runs=1,$isPost=false)
{
	global $CONFIG,$USER,$VOUCHER;
	$USER->Browsers[0]->Typing = isset($_POST[POST_EXTERN_TYPING]);
	
	if(isset($_POST["p_tc_declined"]))
		$USER->Browsers[0]->UpdateArchive("");
	else if(isset($_POST["p_tc_email"]))
		$USER->Browsers[0]->UpdateArchive(base64UrlDecode($_POST["p_tc_email"]));
	
	if($USER->Browsers[0]->InternalUser->Status == USER_STATUS_OFFLINE)
		$USER->Browsers[0]->CloseChat(4);
	else
	{
		foreach($USER->Browsers[0]->Members as $sid => $member)
			if($USER->Browsers[0]->InternalUser->Status == USER_STATUS_OFFLINE)
				$USER->Browsers[0]->LeaveChat($sid);
				
		if($USER->Browsers[0]->InternalUser->SystemId != $USER->Browsers[0]->DesiredChatPartner)
			$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->InternalUser->SystemId;
	}
	
	//while($runs == 1)
	//{
		processForward();
		if(!empty($USER->Browsers[0]->Declined))
		{
			if($USER->Browsers[0]->Declined < (time()-($CONFIG["poll_frequency_clients"]*2)))
				displayDeclined();
			return $USER;
		}
		else if($USER->Browsers[0]->Closed || empty($USER->Browsers[0]->InternalUser))
		{
			displayQuit();
			return $USER;
		}
		else if($USER->Browsers[0]->Activated == CHAT_STATUS_WAITING && !(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
		{
			beginnConversation();
		}
		
		if($USER->Browsers[0]->Activated >= CHAT_STATUS_WAITING && !(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
		{
			refreshPicture();
			processTyping();
		}

		if($runs == 1 && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]) && !isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]) && !(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
			$USER = $USER->Browsers[0]->RequestFileUpload($USER,base64UrlDecode($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]));
		else if($runs == 1 && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]) && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]))
			$USER = $USER->Browsers[0]->AbortFileUpload($USER,namebase(base64UrlDecode($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME])),base64UrlDecode($_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]));

		if(isset($_POST[POST_GLOBAL_SHOUT]))
			processPosts();

		if($USER->Browsers[0]->Activated == CHAT_STATUS_ACTIVE)
		{
			$isPost = getNewPosts();
			$USER->Browsers[0]->SetStatus(CHAT_STATUS_ACTIVE);
			if(!empty($VOUCHER))
			{
				if((time()-$USER->Browsers[0]->LastActive) > 0)
					$VOUCHER->UpdateVoucherChatTime(time()-$USER->Browsers[0]->LastActive);
				if(!(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
					$VOUCHER->UpdateVoucherChatSessions($USER->Browsers[0]->ChatId);
				$vouchers = VisitorChat::GetRelatedChatVouchers(base64UrlDecode($_POST[POST_EXTERN_USER_GROUP]),$VOUCHER);
				$USER->AddFunctionCall("lz_chat_add_update_vouchers_init('".base64_encode(getChangeVoucherHTML($vouchers))."');",false);
				
				foreach($vouchers as $tonlist)
					$USER->AddFunctionCall("lz_chat_add_available_voucher('".$tonlist->Id."',".$tonlist->ChatTime.",".$tonlist->ChatTimeMax.",".$tonlist->ChatSessions.",".$tonlist->ChatSessionsMax.",".$tonlist->VoucherAutoExpire.",".parseBool($tonlist->VoucherAutoExpire < time()).");",false);
			}
			else
				$USER->AddFunctionCall("lz_chat_add_update_vouchers_init('".base64_encode("")."');",false);
		}

		if(isset($_POST[POST_GLOBAL_SHOUT]) || isset($_POST[POST_GLOBAL_NO_LONG_POLL]) || $isPost || (!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed))
		{
			//break;
		}
		else if(md5($USER->Response) != base64UrlDecode($_POST[POST_GLOBAL_XMLCLIP_HASH_ALL]))
		{
			$_POST[POST_GLOBAL_XMLCLIP_HASH_ALL] = md5($USER->Response);
			$USER->AddFunctionCall("lz_chat_listen_hash('". md5($USER->Response) . "','".getId(5)."');",false);
			//break;
		}
		else
		{
			$USER->Response = "";
			//break;
		}
	//}
}

function getChangeVoucherHTML($_vouchers)
{
	global $VOUCHER;
	$voucherHTML = getFile(PATH_TEMPLATES . "chat_voucher_change_item.tpl");
	$tableHTML = getFile(PATH_TEMPLATES . "chat_voucher_change_table.tpl");
	$vouchersHTML = "";
	
	foreach($_vouchers as $voucher)
	{
		$vouchersHTML .= $voucherHTML;
		$vouchersHTML = str_replace("<!--id-->",(($voucher->Id == $VOUCHER->Id) ? "<b>".$voucher->Id."</b>" : $voucher->Id),$vouchersHTML);
		$vouchersHTML = str_replace("<!--selected-->",(($voucher->Id == $VOUCHER->Id) ? "CHECKED" : ""),$vouchersHTML);
		if($voucher->ChatSessionsMax > -1)
			$vouchersHTML = str_replace("<!--sessions-->",($voucher->ChatSessions . " / " . $voucher->ChatSessionsMax),$vouchersHTML);
		else
			$vouchersHTML = str_replace("<!--display_sessions-->","none",$vouchersHTML);
			
		if($voucher->ChatTimeMax > -1)
			$vouchersHTML = str_replace("<!--time-->",formatTimeSpan($voucher->ChatTimeRemaining),$vouchersHTML);
		else
			$vouchersHTML = str_replace("<!--display_time-->","none",$vouchersHTML);
			
		if($voucher->VoucherAutoExpire > -1)
		{
			$parts = explode(date("Y",$voucher->VoucherAutoExpire),date("r",$voucher->VoucherAutoExpire));
			$vouchersHTML = str_replace("<!--expires-->",$parts[0] . date("Y",$voucher->VoucherAutoExpire),$vouchersHTML);
		}
		else
			$vouchersHTML = str_replace("<!--display_expires-->","none",$vouchersHTML);
			
		if(($voucher->ChatSessionsMax - $voucher->ChatSessions) > 0)
			$vouchersHTML = str_replace("<!--class_sessions-->","lz_chat_com_chat_panel_value",$vouchersHTML);
		else if(($voucher->ChatSessionsMax - $voucher->ChatSessions) <= 0)
			$vouchersHTML = str_replace("<!--class_sessions-->","lz_chat_com_chat_panel_value_over",$vouchersHTML);
			
		if($voucher->ChatTimeRemaining > 0)
			$vouchersHTML = str_replace("<!--class_time-->","lz_chat_com_chat_panel_value",$vouchersHTML);
		else if($voucher->ChatTimeRemaining <= 0)
			$vouchersHTML = str_replace("<!--class_time-->","lz_chat_com_chat_panel_value_over",$vouchersHTML);
			
		if($voucher->VoucherAutoExpire > time())
			$vouchersHTML = str_replace("<!--class_expires-->","lz_chat_com_chat_panel_value",$vouchersHTML);
		else if($voucher->VoucherAutoExpire > 0)
			$vouchersHTML = str_replace("<!--class_expires-->","lz_chat_com_chat_panel_value_over",$vouchersHTML);
			
		$vouchersHTML = str_replace("<!--display_sessions-->","''",$vouchersHTML);
		$vouchersHTML = str_replace("<!--display_time-->","''",$vouchersHTML);
		$vouchersHTML = str_replace("<!--display_expires-->","''",$vouchersHTML);
	}

	$tableHTML = str_replace("<!--vouchers-->",$vouchersHTML,$tableHTML);
	$tableHTML = str_replace("<!--server-->",LIVEZILLA_URL,$tableHTML);
	$tableHTML = str_replace("<!--vouchers-->",$vouchersHTML,$tableHTML);
	return applyReplacements($tableHTML,true,false);
}

function processForward()
{
	global $USER,$CONFIG,$GROUPS,$VOUCHER;
	$USER->Browsers[0]->LoadForward();
	if(!empty($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Invite && !empty($USER->Browsers[0]->Forward->TargetGroupId) && !$USER->Browsers[0]->Forward->Processed)
	{
		if(!empty($VOUCHER) && !in_array($VOUCHER->TypeId ,$GROUPS[$USER->Browsers[0]->Forward->TargetGroupId]->ChatVouchersRequired))
			$USER->AddFunctionCall("lz_chat_switch_com_chat_box(false);",false);
		else if(!empty($GROUPS[$USER->Browsers[0]->Forward->TargetGroupId]->ChatVouchersRequired))
		{
			$VOUCHER = VisitorChat::GetMatchingVoucher($USER->Browsers[0]->Forward->TargetGroupId,base64UrlDecode($_POST["p_tid"]));
			if($VOUCHER != null)
				$USER->AddFunctionCall("lz_chat_switch_com_chat_box(true);",false);
		}
		$USER->AddFunctionCall("lz_chat_initiate_forwarding('".base64_encode($USER->Browsers[0]->Forward->TargetGroupId)."');",false);
		$USER->Browsers[0]->LeaveChat($USER->Browsers[0]->Forward->InitiatorSystemId);
		$USER->Browsers[0]->Forward->Save(true);
		$USER->Browsers[0]->ExternalClose();
		$USER->Browsers[0]->DesiredChatGroup = $USER->Browsers[0]->Forward->TargetGroupId;
		$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->Forward->TargetSessId;
		$USER->Browsers[0]->FirstActive=time();
		$USER->Browsers[0]->Save(true);
		$USER->Browsers[0]->SetCookieGroup();
	}
}

function getNewPosts()
{
	global $USER,$LZLANG;
	$isPost = false;
	foreach($USER->Browsers[0]->GetPosts() as $post)
	{
		$senderName = (!empty($post->SenderName)) ? $post->SenderName : ($LZLANG["client_guest"] . " " . getNoName($USER->UserId.getIP()));
		$USER->AddFunctionCall($post->GetCommand($senderName),false);
		$isPost = true;
	}
	return $isPost;
}

function processPosts($counter=0)
{
	global $USER,$STATS,$GROUPS,$INTERNAL;
	while(isset($_POST["p_p" . $counter]))
	{
		if(STATS_ACTIVE)
			$STATS->ProcessAction(ST_ACTION_EXTERNAL_POST);

		$id = md5($USER->Browsers[0]->SystemId . base64UrlDecode($_POST[POST_EXTERN_CHAT_ID]) . base64UrlDecode($_POST["p_i" . $counter]));
		$post = new Post($id,$USER->Browsers[0]->SystemId,"",base64UrlDecode($_POST["p_p" . $counter]),time(),$USER->Browsers[0]->ChatId,$USER->Browsers[0]->Fullname);
		
		foreach($GROUPS as $groupid => $group)
			if($group->IsDynamic && !empty($group->Members[$USER->Browsers[0]->SystemId]))
			{
				foreach($group->Members as $member)
					if($member != $USER->Browsers[0]->SystemId)
					{
						if(!empty($INTERNAL[$member]))
							processPost($id,$post,$member,$counter,$groupid,$USER->Browsers[0]->ChatId);
						else
							processPost($id,$post,$member,$counter,$groupid,getValueBySystemId($member,"chat_id",""));
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
				processPost($id,$post,$systemid,$counter,$USER->Browsers[0]->SystemId,$USER->Browsers[0]->ChatId);
		}
		$USER->AddFunctionCall("lz_chat_release_post('".base64UrlDecode($_POST["p_i" . $counter])."');",false);
		$counter++;
	}
	
	$counter=0;
	while(isset($_POST["pr_i" . $counter]))
	{
		$post = new Post(base64UrlDecode($_POST["pr_i" . $counter]),"","","","","","");
		$post->MarkReceived($USER->Browsers[0]->SystemId);
		$USER->AddFunctionCall("lz_chat_message_set_received('".base64UrlDecode($_POST["pr_i" . $counter])."');",false);
		$counter++;
	}
}

function processPost($id,$post,$systemid,$counter,$rgroup,$chatid,$_received=false)
{
	$post->Id = $id;

	if(isset($_POST["p_pt" . $counter]))
	{
		$post->Translation = base64UrlDecode($_POST["p_pt" . $counter]);
		$post->TranslationISO = base64UrlDecode($_POST["p_ptiso" . $counter]);
	}
	$post->ChatId = $chatid;
	$post->ReceiverOriginal =
	$post->Receiver = $systemid;
	$post->ReceiverGroup = $rgroup;
	$post->Received=$_received;
	$post->Save();
	return true;
}

function login()
{
	global $USER,$INPUTS;
	initData(false,false,false,false,false,false,false,true);
	if(empty($_POST[POST_EXTERN_USER_NAME]) && !isnull(getCookieValue("form_111")) && $INPUTS[111]->Cookie)
		$USER->Browsers[0]->Fullname = cutString(getCookieValue("form_111"),255);
	else if(!empty($_POST[POST_EXTERN_USER_NAME]))
		$USER->Browsers[0]->Fullname = cutString(base64UrlDecode($_POST[POST_EXTERN_USER_NAME]),255);
		
	if(empty($_POST[POST_EXTERN_USER_EMAIL]) && !isnull(getCookieValue("form_112")) && $INPUTS[112]->Cookie)
		$USER->Browsers[0]->Email = cutString(getCookieValue("form_112"),255);
	else
		$USER->Browsers[0]->Email = cutString(base64UrlDecode($_POST[POST_EXTERN_USER_EMAIL]),255);
		
	if(empty($_POST[POST_EXTERN_USER_COMPANY]) && !isnull(getCookieValue("form_113")) && $INPUTS[113]->Cookie)
		$USER->Browsers[0]->Company = cutString(getCookieValue("form_113"),255);
	else
		$USER->Browsers[0]->Company = cutString(base64UrlDecode($_POST[POST_EXTERN_USER_COMPANY]),255);
		
	if(empty($_POST[POST_EXTERN_USER_QUESTION]) && !isnull(getCookieValue("form_114")) && $INPUTS[114]->Cookie)
		$USER->Browsers[0]->Question = cutString(getCookieValue("form_114"),16777216);
	else
		$USER->Browsers[0]->Question = cutString(base64UrlDecode($_POST[POST_EXTERN_USER_QUESTION]),16777216);
		
	if(empty($_POST["p_phone"]) && !isnull(getCookieValue("form_116")) && $INPUTS[116]->Cookie)
		$USER->Browsers[0]->Phone = cutString(getCookieValue("form_116"),255);
	else
		$USER->Browsers[0]->Phone = cutString(base64UrlDecode($_POST["p_phone"]),255);

	$USER->Browsers[0]->CallMeBack = !empty($_POST["p_cmb"]);
	$USER->Browsers[0]->ApplyCustomValues($_POST,"p_cf");
	$USER->Browsers[0]->SaveLoginData();
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_INIT);",false);
}

function replaceLoginDetails($_user,$values="",$keys="",$comma="")
{
	global $INPUTS;
	initData(false,false,false,false,false,false,false,true);
	foreach($INPUTS as $index => $input)
	{
		$data = $input->GetValue($_user->Browsers[0]);
		//$data = (!empty($_GET[$input->GetIndexName()])) ? base64UrlDecode($_GET[$input->GetIndexName()]) : ((!empty($data)) ? $data : (($input->Cookie && !isnull($input->GetCookieValue())) ? $input->GetCookieValue() : ""));
		$data = (!empty($data)) ? $data : (($input->Cookie && !isnull($input->GetCookieValue())) ? $input->GetCookieValue() : ((!empty($_GET[$input->GetIndexName()])) ? base64UrlDecode($_GET[$input->GetIndexName()]) : ""));
		
		$values .= $comma . $input->GetJavascript($data);
		$keys .= $comma . "'".$index."'";
		$comma = ",";
	}
	$_user->AddFunctionCall("if(lz_chat_data.InputFieldIndices==null)lz_chat_data.InputFieldIndices = new Array(".$keys.");",false);
	$_user->AddFunctionCall("if(lz_chat_data.InputFieldValues==null)lz_chat_data.InputFieldValues = new Array(".$values.");",false);
	return $_user;
}

function getChatLoginInputs($_html,$_maxlength,$inputshtml="")
{
	global $INPUTS;
	initData(false,false,false,false,false,false,false,true);
	foreach($INPUTS as $index => $dinput)
	{
        if($index == 115)
            $dinput->InfoText ="<!--lang_client_start_chat_comm_information-->";
		$inputshtml .= $dinput->GetHTML($_maxlength,($index == 115 || ($index == 116 && !empty($_GET["cmb"]))) ? true : $dinput->Active);
	}
	return str_replace("<!--chat_login_inputs-->",$inputshtml,$_html);
}

function refreshPicture()
{
	global $CONFIG,$USER;
	$USER->Browsers[0]->InternalUser->LoadPictures();
	if(!empty($USER->Browsers[0]->InternalUser->WebcamPicture))
		$edited = $USER->Browsers[0]->InternalUser->WebcamPictureTime;
	else if(!empty($USER->Browsers[0]->InternalUser->ProfilePicture))
		$edited = $USER->Browsers[0]->InternalUser->ProfilePictureTime;
	else
		$edited = 0;
	$USER->AddFunctionCall("lz_chat_set_intern_image(".$edited.",'" . $USER->Browsers[0]->InternalUser->GetOperatorPictureFile() . "',false);",false);
	$USER->AddFunctionCall("lz_chat_set_config(".$CONFIG["timeout_clients"].",".$CONFIG["poll_frequency_clients"].");",false);
}

function processTyping($_dgroup="")
{
	global $USER,$GROUPS,$INTERNAL,$LZLANG;
	$USER->Browsers[0]->InternalUser->LoadProfile();
	$groupname = addslashes($GROUPS[$USER->Browsers[0]->DesiredChatGroup]->Description);
	foreach($GROUPS as $group)
		if($group->IsDynamic && !empty($group->Members[$USER->Browsers[0]->SystemId]))
		{
			$_dgroup = $group->Descriptions["EN"];
			foreach($group->Members as $member)
				if($member != $USER->Browsers[0]->SystemId)
				{
					if(!empty($INTERNAL[$member]))
					{
						if($INTERNAL[$member]->Status==USER_STATUS_OFFLINE)
							continue;
						$name = $INTERNAL[$member]->Fullname;
					}
					else
                    {
						$name = getValueBySystemId($member,"fullname",$LZLANG["client_guest"]);
                        if(empty($name))
                            $name = $LZLANG["client_guest"];
                    }

					$USER->AddFunctionCall("lz_chat_set_room_member('".base64_encode($member)."','".base64_encode($name)."');",false);
				}
		}
	foreach($USER->Browsers[0]->Members as $sysid => $chatm)
		if($chatm->Status < 2 && empty($chatm->Declined))
			$USER->AddFunctionCall("lz_chat_set_room_member('".base64_encode($sysid)."','".base64_encode($INTERNAL[$sysid]->Fullname)."');",false);

	$USER->AddFunctionCall("lz_chat_set_host(\"".base64_encode($USER->Browsers[0]->InternalUser->UserId)."\",\"".base64_encode(addslashes($USER->Browsers[0]->InternalUser->Fullname))."\",\"". base64_encode($groupname)."\",\"".strtolower($USER->Browsers[0]->InternalUser->Language)."\",".parseBool($USER->Browsers[0]->InternalUser->Typing==$USER->Browsers[0]->SystemId).",".parseBool(!empty($USER->Browsers[0]->InternalUser->Profile) && $USER->Browsers[0]->InternalUser->Profile->Public).",\"". base64_encode($_dgroup)."\");",false);
}

function beginnConversation()
{
	global $USER,$CONFIG,$LZLANG;
	$USER->Browsers[0]->ExternalActivate();
	if(!empty($CONFIG["gl_save_op"]))
		setCookieValue("internal_user",$USER->Browsers[0]->InternalUser->UserId);
	$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->InternalUser->SystemId;
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_ACTIVE);",false);
	$USER->AddFunctionCall("lz_chat_shout(1);",false);

    if($CONFIG["gl_rddt"] > -1)
        $USER->AddFunctionCall("lz_chat_init_rating_drop_down(".($CONFIG["gl_rddt"]*60).");",false);

    if(!empty($_GET["ofc"]))
        $USER->AddFunctionCall("lz_chat_call_back_info('".base64_encode($LZLANG["client_thank_you"]." ".str_replace(array("<b>","</b>"),"",str_replace("<!--operator_name-->",$USER->Browsers[0]->InternalUser->Fullname,$LZLANG["client_now_speaking_to"])."</b>"))."');",false);
}

function displayFiltered()
{
	global $FILTERS,$USER;
	$USER->Browsers[0]->CloseChat(0);
	$USER->AddFunctionCall("lz_chat_set_host('','','','',false,false,'');",false);
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_STOPPED);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(2,'".base64_encode("&nbsp;<b>".$FILTERS->Filters[ACTIVE_FILTER_ID]->Reason."</b>")."');",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
}

function displayQuit()
{
	global $USER;
	$USER->Browsers[0]->CloseChat(1);
	$USER->AddFunctionCall("lz_chat_set_host('','','','',false,false,'');",false);
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_STOPPED);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(3,null);",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
}

function displayDeclined()
{
	global $USER;
	$USER->Browsers[0]->CloseChat(2);
	$USER->AddFunctionCall("lz_chat_set_host('','','','',false,false,'');",false);
	$USER->AddFunctionCall("lz_chat_set_status(lz_chat_data.STATUS_STOPPED);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(4,null);",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
}

function buildLoginErrorField($error="",$addition = "")
{
	global $FILTERS,$LZLANG,$CONFIG;
	if(!getAvailability())
		return $LZLANG["client_error_deactivated"];
		
	if(!DB_CONNECTION || !empty($CONFIG["gl_stmo"]))
		return $LZLANG["client_error_unavailable"];

	if(IS_FILTERED)
	{
		$error = $LZLANG["client_error_unavailable"];
		if(isset($FILTERS->Message) && strlen($FILTERS->Message) > 0)
			$addition = "<br><br>" . $FILTERS->Message;
	}
	return $error . $addition;
}

function reloadGroups($_user)
{
	global $CONFIG,$INTERNAL,$GROUPS;
	initData(true,false,false,true);
	if(!empty($_GET[GET_EXTERN_GROUP]))
		$_user->Browsers[0]->DesiredChatGroup = base64UrlDecode(getParam(GET_EXTERN_GROUP));
		
	if(!empty($_GET[GET_EXTERN_INTERN_USER_ID]))
		$_user->Browsers[0]->DesiredChatPartner = base64UrlDecode(getParam(GET_EXTERN_INTERN_USER_ID));
	
	$groupbuilder = new GroupBuilder($INTERNAL,$GROUPS,$CONFIG,$_user->Browsers[0]->DesiredChatGroup,$_user->Browsers[0]->DesiredChatPartner);
	$groupbuilder->Generate($_user);
	
	if(isset($_POST[POST_EXTERN_REQUESTED_INTERNID]) && !empty($_POST[POST_EXTERN_REQUESTED_INTERNID]))
		$_user->Browsers[0]->DesiredChatPartner = getInternalSystemIdByUserId(base64UrlDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]));

    $groupsAvailable = parseBool(($groupbuilder->GroupAvailable || (isset($_POST[GET_EXTERN_RESET]) && strlen($groupbuilder->ErrorHTML) <= 2)));

	$_user->AddFunctionCall("lz_chat_set_groups(" . $groupsAvailable . ",\"" . $groupbuilder->Result . "\" ,". $groupbuilder->ErrorHTML .");",false);
	$_user->AddFunctionCall("lz_chat_release(" . $groupsAvailable . ",".$groupbuilder->ErrorHTML.");",false);

	return $_user;
}

function getInternal($desired="",$util=0,$fromCookie=null,$result=true,$_allowBots=false,$_requireBot=false)
{
	global $CONFIG,$INTERNAL,$GROUPS,$USER,$INTLIST,$INTBUSY,$WELCOME_MANAGER,$BOTMODE;
	$INTLIST = array();
	$INTBUSY = 0;
	$backup_target = null;
	$fromDepartment = $fromDepartmentBusy = false;
		
	if(!empty($USER->Browsers[0]->DesiredChatPartner) && isset($INTERNAL[$USER->Browsers[0]->DesiredChatPartner]) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->Status < USER_STATUS_OFFLINE)
	{
		if(!(!empty($USER->Browsers[0]->DesiredChatGroup) && !in_array($USER->Browsers[0]->DesiredChatGroup,$INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->GetGroupList(true))))
			$desired = $USER->Browsers[0]->DesiredChatPartner;
	}
	else
	{
		$USER->Browsers[0]->DesiredChatPartner = null;
		if(!empty($_POST[POST_EXTERN_REQUESTED_INTERNID]))
			$desired = getInternalSystemIdByUserId(base64UrlDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]));
		else if(!empty($_GET[GET_EXTERN_INTERN_USER_ID]))
		{
			$desired = getInternalSystemIdByUserId(base64UrlDecode($_GET[GET_EXTERN_INTERN_USER_ID]));
		}
		else if(!isnull(getCookieValue("internal_user")) && !empty($CONFIG["gl_save_op"]))
		{
			$desired = getInternalSystemIdByUserId(getCookieValue("internal_user"));
			if(!empty($INTERNAL[$desired]) && !(!empty($USER->Browsers[0]->DesiredChatGroup) && !in_array($USER->Browsers[0]->DesiredChatGroup,$INTERNAL[$desired]->GetGroupList(true))))
			{
				$fromCookie = $desired;
			}
			else
				$desired = "";
		}
	}
	
	if(!empty($desired) && !$_allowBots && $INTERNAL[$desired]->IsBot)
		$USER->Browsers[0]->DesiredChatPartner = $desired = "";
	else if(!empty($desired) && $_requireBot && !$INTERNAL[$desired]->IsBot)
		$USER->Browsers[0]->DesiredChatPartner = $desired = "";

	foreach($GROUPS as $id => $group)
		$utilization[$id] = 0;
	foreach($INTERNAL as $sessId => $internal)
	{
		if(!$internal->Deactivated && $internal->Status != USER_STATUS_OFFLINE && ($_allowBots || !$internal->IsBot) && (!$_requireBot || $internal->IsBot))
		{
			$intstatus = $internal->Status;
			$group_chats[$sessId] = $internal->GetExternalChatAmount();
			$group_names[$sessId] = $internal->Fullname;
			$group_available[$sessId] = GROUP_STATUS_UNAVAILABLE;
			if(in_array($USER->Browsers[0]->DesiredChatGroup,$internal->GetGroupList(true)))
			{
				if($WELCOME_MANAGER && $internal->IsBot && $internal->WelcomeManager)
					$USER->Browsers[0]->DesiredChatPartner = $sessId;
				if(!$internal->IsBot && $intstatus < USER_STATUS_OFFLINE && $GROUPS[$USER->Browsers[0]->DesiredChatGroup]->MaxChats > -1 && $GROUPS[$USER->Browsers[0]->DesiredChatGroup]->MaxChats <= $group_chats[$sessId])
					$intstatus = GROUP_STATUS_BUSY;
				if($intstatus == USER_STATUS_ONLINE && ($internal->LastChatAllocation < (time()-30) || $internal->IsBot))
					$group_available[$sessId] = GROUP_STATUS_AVAILABLE;
				elseif($intstatus == USER_STATUS_BUSY || ($internal->LastChatAllocation >= (time()-30) && !$internal->IsBot))
				{
					$group_available[$sessId] = GROUP_STATUS_BUSY;
					$INTBUSY++;
					if(empty($fromCookie) && $desired == $sessId)
						return;
				}
			}
			else
			{
				if($intstatus == USER_STATUS_ONLINE)
					$backup_target = $internal;
				else if($intstatus == USER_STATUS_BUSY && empty($backup_target))
					$backup_target = $internal;
					
				if(!empty($USER->Browsers[0]->DesiredChatPartner) && $USER->Browsers[0]->DesiredChatPartner == $sessId)
					$USER->Browsers[0]->DesiredChatPartner = null;
			}
			$igroups = $internal->GetGroupList(true);
			for($count=0;$count<count($igroups);$count++)
			{
				if($USER->Browsers[0]->DesiredChatGroup == $igroups[$count])
				{
					if(!is_array($utilization[$igroups[$count]]))
						$utilization[$igroups[$count]] = Array();
					if($group_available[$sessId] == GROUP_STATUS_AVAILABLE)
						$utilization[$igroups[$count]][$sessId] = $group_chats[$sessId];
				}
			}
		}
	}
	
	if(isset($utilization[$USER->Browsers[0]->DesiredChatGroup]) && is_array($utilization[$USER->Browsers[0]->DesiredChatGroup]))
	{
		arsort($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		reset($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		$util = end($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		$INTLIST = $utilization[$USER->Browsers[0]->DesiredChatGroup];
	}
	
	if(isset($group_available) && is_array($group_available) && in_array(GROUP_STATUS_AVAILABLE,$group_available))
		$fromDepartment = true;
	elseif(isset($group_available) && is_array($group_available) && in_array(GROUP_STATUS_BUSY,$group_available))
		$fromDepartmentBusy = true;

	if(isset($group_chats) && is_array($group_chats) && isset($fromDepartment) && $fromDepartment)
		foreach($group_chats as $sessId => $amount)
		{
			if(($group_available[$sessId] == GROUP_STATUS_AVAILABLE  && $amount <= $util) || ((!empty($USER->Browsers[0]->Forward) && $USER->Browsers[0]->Forward->Processed) && isset($desired) && $sessId == $desired))
			{
				$available_internals[] = $sessId;
			}
		}

	if($fromDepartment && sizeof($available_internals) > 0)
	{
		if(is_array($available_internals))
		{
			if(!empty($desired) && (in_array($desired,$available_internals) || $INTERNAL[$desired]->Status == USER_STATUS_ONLINE))
				$matching_internal = $desired;
			else
			{
				if(!isnull($inv_sender = $USER->Browsers[0]->GetLastInvitationSender()) && in_array($inv_sender,$available_internals))
				{
					$matching_internal = $inv_sender;
				}
				else
				{
					$matching_internal = array_rand($available_internals,1);
					$matching_internal = $available_internals[$matching_internal];
				}
			}
		}
		if($CONFIG["gl_alloc_mode"] != ALLOCATION_MODE_ALL || $fromCookie == $matching_internal || $INTERNAL[$matching_internal]->IsBot)
			$USER->Browsers[0]->DesiredChatPartner = $matching_internal;
	}
	elseif($fromDepartmentBusy)
	{	
		if(!$USER->Browsers[0]->Waiting)
		{
			$USER->Browsers[0]->Waiting = true;
		}
	}
	else
	{
		$result = false;
		$USER->Browsers[0]->CloseChat(3);
		$INTLIST = null;
	}
	return $result;
}

function getSessionId()
{
	global $CONFIG;
	if(!isnull(getCookieValue("userid")))
		$session = substr(getCookieValue("userid"),0,USER_ID_LENGTH);
	else if(!empty($_GET[GET_TRACK_USERID]))
		$session = base64UrlDecode(getParam(GET_TRACK_USERID));
	else
		setCookieValue("userid",$session = getId(USER_ID_LENGTH));
	return $session;
}

function getQueueWaitingTime($_position,$_intamount,$min=1)
{
	global $CONFIG;
	if($_intamount == 0)
		$_intamount++;
		
	$result = queryDB(true,"SELECT AVG(`endtime`-`time`) AS `waitingtime` FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` AS `db1` INNER JOIN `".DB_PREFIX.DATABASE_OPERATORS."` as `db2` ON `db1`.`internal_id`=`db2`.`system_id` WHERE `bot`=0 AND `endtime`>0 AND `endtime`>`time` AND `endtime`-`time` < 3600;");
	if($result)
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		if(!empty($row["waitingtime"]))
			$min = ($row["waitingtime"]/60)/$_intamount;
		else
			$min = $min/$_intamount;
		$minb = $min;
		for($i = 1;$i < $_position; $i++)
		{
			$minb *= 0.9;
			$min += $minb;
		}
		$min /= $CONFIG["gl_sim_ch"];
		$min -= (time() - CHAT_START_TIME) / 60;
		if($min <= 0)
			$min = 1;
	}
	return min(10,ceil($min));
}

function getQueuePosition($_creatorId,$_targetGroup,$_startTime=0,$_position = 1)
{
	global $CONFIG,$USER;
	$USER->Browsers[0]->SetStatus(CHAT_STATUS_OPEN);
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `qpenalty`=`qpenalty`+60 WHERE `last_active`>".(time()-$CONFIG["timeout_clients"])." AND `status`=0 AND `exit`=0 AND `last_active`<" . @mysql_real_escape_string(time()-max(20,($CONFIG["poll_frequency_clients"]*2))));
	$result = queryDB(true,"SELECT `priority`,`request_operator`,`request_group`,`chat_id`,`first_active`,`qpenalty`+`first_active` as `sfirst` FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `status`='0' AND `exit`='0' AND `chat_id`>0 AND `last_active`>".(time()-$CONFIG["timeout_clients"])." ORDER BY `priority` DESC,`sfirst` ASC;");
	
	$all = mysql_num_rows($result);
	if($result)
	{
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if($row["chat_id"] == $USER->Browsers[0]->ChatId)
			{
				$_startTime = $row["sfirst"];
				break;
			}
			else if($row["request_group"]==$_targetGroup && $row["request_operator"]==$USER->Browsers[0]->DesiredChatPartner)
			{
				$_position++;
			}
			else if($row["request_group"]==$_targetGroup && ($row["request_operator"]!=$USER->Browsers[0]->DesiredChatPartner && empty($row["request_operator"])))
			{
				$_position++;
			}
			else if(!empty($USER->Browsers[0]->DesiredChatPartner) && $USER->Browsers[0]->DesiredChatPartner==$row["request_operator"])
			{
				$_position++;
			}
		}
	}
	define("CHAT_START_TIME",$_startTime);
	return $_position;
}

function isRatingFlood()
{
	$result = queryDB(true,"SELECT count(id) as rating_count FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE time>".@mysql_real_escape_string(time()-86400)." AND ip='".@mysql_real_escape_string(getIP())."';");
	if($result)
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		return ($row["rating_count"] >= MAX_RATES_PER_DAY);
	}
	else
		return true;
}

function saveRating($_rating,$_chatId)
{
	$time = time();
	while(true)
	{
		queryDB(true,"SELECT time FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE time=".@mysql_real_escape_string($time).";");
		if(@mysql_affected_rows() > 0)
			$time++;
		else
			break;
	}
	queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_RATINGS."` (`id` ,`time` ,`user_id` ,`internal_id` ,`fullname` ,`email` ,`company` ,`qualification` ,`politeness` ,`comment`, `ip`, `chat_id`) VALUES ('".@mysql_real_escape_string($_rating->Id)."', ".@mysql_real_escape_string($time)." , '".@mysql_real_escape_string($_rating->UserId)."', '".@mysql_real_escape_string($_rating->InternId)."', '".@mysql_real_escape_string($_rating->Fullname)."', '".@mysql_real_escape_string($_rating->Email)."', '".@mysql_real_escape_string($_rating->Company)."', '".@mysql_real_escape_string($_rating->RateQualification)."', '".@mysql_real_escape_string($_rating->RatePoliteness)."', '".@mysql_real_escape_string($_rating->RateComment)."', '".@mysql_real_escape_string(getIP())."', '".@mysql_real_escape_string($_chatId)."');");
}

function isTicketFlood()
{
	$result = queryDB(true,"SELECT count(id) as ticket_count FROM `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE time>".@mysql_real_escape_string(time()-86400)." AND ip='".@mysql_real_escape_string(getIP())."';");
	if($result)
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		return ($row["ticket_count"] > MAX_MAIL_PER_DAY);
	}
	else
		return true;
}

function getChatVoucherTemplate($html = "")
{
	global $CONFIG,$COUNTRIES,$LZLANG;
	
	if(!empty($CONFIG["db"]["ccpp"]["Custom"]))
		return "";
	
	if(!empty($CONFIG["gl_ccac"]))
		foreach($CONFIG["db"]["cct"] as $type)
			$html .= $type->GetTemplate();

	$cchtml = getFile(PATH_TEMPLATES . "chat_voucher_checkout.tpl");
	$mycountry = "";
	$replacements = array("<!--lp_company-->"=>"","<!--lp_firstname-->"=>"","<!--lp_email-->"=>"","<!--lp_lastname-->"=>"","<!--lp_taxid-->"=>"","<!--lp_business_type-->"=>"","<!--lp_address_1-->"=>"","<!--lp_address_2-->"=>"","<!--lp_city-->"=>"","<!--lp_state-->"=>"","<!--lp_country-->"=>"","<!--lp_phone-->"=>"","<!--lp_zip-->"=>"");
	$prefillco = (!empty($_GET["co"])) ? " OR id='".@mysql_real_escape_string(base64URLDecode($_GET["co"]))."'" : "";
	
	if(!isnull(getCookieValue("userid")) || !empty($prefillco))
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` WHERE `visitor_id`='".@mysql_real_escape_string(getCookieValue("userid"))."'".$prefillco." ORDER BY `created` DESC LIMIT 1;");
		if($result)
		{
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$replacements = array("<!--lp_company-->"=>$row["company"],"<!--lp_firstname-->"=>$row["firstname"],"<!--lp_lastname-->"=>$row["lastname"],"<!--lp_taxid-->"=>$row["tax_id"],"<!--lp_email-->"=>$row["email"],"<!--lp_business_type-->"=>$row["business_type"],"<!--lp_address_1-->"=>$row["address_1"],"<!--lp_address_2-->"=>$row["address_2"],"<!--lp_city-->"=>$row["city"],"<!--lp_state-->"=>$row["state"],"<!--lp_country-->"=>$row["country"],"<!--lp_phone-->"=>$row["phone"],"<!--lp_zip-->"=>$row["zip"]);
				$mycountry = $row["country"];
			}
		}
	}
	$clist = $COUNTRIES;
	asort($clist);
	$countrieshtml = "";
	foreach($clist as $isokey => $value)
		if(!empty($isokey))
			$countrieshtml .= ($isokey == $mycountry) ? "<option value=\"".$isokey."\" SELECTED>".utf8_encode($value)."</option>" : "<option value=\"".$isokey."\">".utf8_encode($value)."</option>";
	$cchtml = str_replace("<!--countries-->",$countrieshtml,$cchtml);
	
	foreach($replacements as $key => $value)
		$cchtml = str_replace($key,$value,$cchtml);
	
	$cchtml = str_replace("<!--show_VAT-->",(!empty($CONFIG["gl_ccsv"])) ? "''" : "none",$cchtml);
	$cchtml = str_replace("<!--voucher_form-->",$html,$cchtml);
	
	if(!empty($CONFIG["db"]["ccpp"]["PayPal"]->LogoURL))
		$cchtml = str_replace("<!--pp_logo_url-->"," src=\"".$CONFIG["db"]["ccpp"]["PayPal"]->LogoURL."\"",$cchtml);
	else
		$cchtml = str_replace("<!--pp_logo_url-->","",$cchtml);
	
	$cchtml = str_replace("<!--extends_voucher-->",((!empty($_GET["co"]) && strlen(base64UrlDecode($_GET["co"])) == 16) ? base64UrlDecode($_GET["co"]) : ""),$cchtml);
	$cchtml = str_replace("<!--VAT-->",str_replace("<!--VAT-->",$CONFIG["gl_ccva"],$LZLANG["client_voucher_include_vat"]),$cchtml);
	return $cchtml;
}
?>
