<?php
/****************************************************************************************
* LiveZilla objects.global.users.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
require(LIVEZILLA_PATH . "_lib/objects.global.inc.php");

class BaseUser
{
	public $IP;
	public $SessId;
	public $UserId;
	public $Language;
	public $SystemId;
	public $Messages = array();
	public $Status = USER_STATUS_OFFLINE;
	public $Type;
	public $Folder;
	public $SessionFile;
	public $FirstActive;
	public $LastActive;
	public $Fullname;
	public $Company;
	public $Phone;
	public $Question;
	public $Email;
	public $Typing = false;
	public $Customs;

	function BaseUser($_userid)
   	{
		$this->UserId = $_userid;
   	}
	
	function GetPosts()
	{
		$messageFileCount = 0;
		$rows = getPosts($this->SystemId);
		$posts = array();
		foreach($rows as $row)
		{
			array_push($posts,new Post($row));
			if(++$messageFileCount >= DATA_ITEM_LOADS && $posts[count($posts)-1]->Receiver == $posts[count($posts)-1]->ReceiverOriginal)
				break;
		}
		return $posts;
	}
	
	function AppendPersonalData()
	{
		global $INPUTS;

		if(isnull(getCookieValue("form_111")))
			$this->Fullname = base64UrlDecode(getParam(GET_EXTERN_USER_NAME));
			
		if(isnull(getCookieValue("form_112")))
			$this->Email = base64UrlDecode(getParam(GET_EXTERN_USER_EMAIL));
		
		if(isnull(getCookieValue("form_113")))
			$this->Company = base64UrlDecode(getParam(GET_EXTERN_USER_COMPANY));
		
		if(isnull(getCookieValue("form_114")))
			$this->Question = base64UrlDecode(getParam(GET_EXTERN_USER_QUESTION));
		
		if(isnull(getCookieValue("form_116")))
			$this->Phone = base64UrlDecode(getParam("ep"));
		
		if(empty($this->Email) && !isnull(getCookieValue("form_112")))
			$this->Email = (getCookieValue("form_112"));
		if(empty($this->Fullname) && !isnull(getCookieValue("form_111")))
			$this->Fullname = (getCookieValue("form_111"));
		if(empty($this->Company) && !isnull(getCookieValue("form_113")))
			$this->Company = (getCookieValue("form_113"));
		if(empty($this->Phone) && !isnull(getCookieValue("form_116")))
			$this->Phone = (getCookieValue("form_116"));
	}
}

class ChatMember
{
	public $SystemId;
	public $Status;
	public $Declined;
	public $Joined;
	public $Left;
	
	function ChatMember($_systemId, $_status, $_declined=false, $_joined=0, $_left=0)
	{
		$this->SystemId = $_systemId;
		$this->Status = $_status;
		$this->Declined = $_declined;
		$this->Joined = $_joined;
		$this->Left = $_left;
	}
}

class UserGroup
{
	public $Id;
	public $Descriptions;
	public $DescriptionArray;
	public $Description;
	public $IsExternal;
	public $IsInternal;
	public $IsStandard;
	public $PredefinedMessages = array();
    public $Signatures = array();
	public $Created;
	public $Email;
	public $ChatFunctions;
	public $VisitorFilters;
	public $ChatInputsHidden;
	public $ChatInputsMandatory;
	public $TicketInputsHidden;
	public $TicketInputsMandatory;
	public $ChatVouchersRequired;
	public $OpeningHours = array();
	public $MaxChats;
	public $Members;
	public $IsDynamic;
	public $Owner;
	public $PostHTML = "";
	public $PreHTML = "";
    public $TicketEmailOut;
    public $TicketEmailIn = array();
    public $TicketHandleUnknownEmails;
	
	function UserGroup()
	{
		global $CONFIG,$DEFAULT_BROWSER_LANGUAGE;
		if(func_num_args() > 0)
		{
			$this->Id = func_get_arg(0);
			$row = (func_num_args() > 1) ? func_get_arg(1) : null;
			
			if(!empty($row))
			{
				if(!empty($row["dynamic"]))
				{
					$this->Owner = $row["owner"];
					$this->IsDynamic = true;
					$this->Descriptions["EN"] = $row["name"];
					$this->LoadMembers();
				}
				else
				{
					$this->Descriptions = @unserialize($row["description"]);
					$this->DescriptionArray = $row["description"];
					
					if(!empty($DEFAULT_BROWSER_LANGUAGE) && isset($this->Descriptions[strtoupper($DEFAULT_BROWSER_LANGUAGE)]))
						$this->Description = base64_decode($this->Descriptions[strtoupper($DEFAULT_BROWSER_LANGUAGE)]);
					else if(isset($this->Descriptions[strtoupper($CONFIG["gl_default_language"])]))
						$this->Description = base64_decode($this->Descriptions[strtoupper($CONFIG["gl_default_language"])]);
					else if(isset($this->Descriptions["EN"]))
						$this->Description = base64_decode($this->Descriptions["EN"]);
					else if(is_array($this->Descriptions))
						$this->Description = base64_decode(current($this->Descriptions));
						
					$this->IsInternal = !empty($row["internal"]);
					$this->IsExternal = !empty($row["external"]);
					$this->IsStandard = !empty($row["standard"]);
					$this->MaxChats =  empty($row["max_chats"]) ? 999 : $row["max_chats"];
					$this->Created = $row["created"];
					$this->OpeningHours = @unserialize($row["opening_hours"]);
					$this->Email = $row["email"];
					
					if(!empty($row["pre_chat_html"]))
						$this->PreHTML = $row["pre_chat_html"];
					if(!empty($row["post_chat_html"]))
						$this->PostHTML = $row["post_chat_html"];
						
					$this->VisitorFilters = (!empty($row["visitor_filters"])) ? @unserialize($row["visitor_filters"]) : array();
					$this->ChatFunctions = str_split($row["functions"]);
					$this->ChatInputsHidden = @unserialize($row["chat_inputs_hidden"]);
					$this->ChatInputsMandatory = @unserialize($row["chat_inputs_required"]);
					$this->TicketInputsHidden = @unserialize($row["ticket_inputs_hidden"]);
					$this->TicketInputsMandatory = @unserialize($row["ticket_inputs_required"]);
					$this->ChatVouchersRequired = @unserialize($row["chat_vouchers_required"]);
                    $this->TicketEmailIn = @unserialize(@$row["ticket_email_in"]);
                    $this->TicketEmailOut = @$row["ticket_email_out"];
                    $this->TicketHandleUnknownEmails = @$row["ticket_handle_unknown"];
					$this->LoadPredefinedMessages();
                    $this->LoadSignatures();
				}
			}
		}
	}
	
	function LoadMembers()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_GROUP_MEMBERS."` WHERE `group_id`='".@mysql_real_escape_string($this->Id)."';");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->Members[$row["user_id"]] = $row["user_id"];
	}
	
	function IsOpeningHour()
	{
		initData(true);
		$sofday = time() - mktime(0,0,0);
		foreach($this->OpeningHours as $hour)
		{
			if(date("N") == $hour[0])
			{
				if($sofday >= $hour[1] && $sofday <= $hour[2])
					return true;
			}
		}
		return (count($this->OpeningHours) == 0);
	}
	
	function IsHumanAvailable($_ignoreExternal=false,$_ignoreOpeningHours=false)
	{
		global $INTERNAL,$GROUPS;
		foreach($INTERNAL as $sysId => $internaluser)
		{
			if(in_array($this->Id,$internaluser->Groups) && !$internaluser->IsBot)
			{
				$isex = $internaluser->IsExternal($GROUPS, null, array($this->Id), true, $_ignoreExternal,$_ignoreOpeningHours);
				if($isex && $internaluser->Status < USER_STATUS_OFFLINE)
				{
					return true;
				}
			}
		}
		return false;
	}

	function HasWelcomeManager()
	{
		global $INTERNAL;
		foreach($INTERNAL as $internaluser)
		{
			if(in_array($this->Id,$internaluser->Groups) && $internaluser->IsBot && $internaluser->WelcomeManager)
				return true;
		}
		return false;
	}
	
	function LoadPredefinedMessages()
	{
		if(DB_CONNECTION)
		{
            $isdefault = false;
			$this->PredefinedMessages = array();
			$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PREDEFINED."` WHERE `group_id`='".@mysql_real_escape_string($this->Id)."'");
			if($result)
            {
				while($row = mysql_fetch_array($result, MYSQL_BOTH))
                {
					$this->PredefinedMessages[strtolower($row["lang_iso"])] = new PredefinedMessage($row);
                    if($this->PredefinedMessages[strtolower($row["lang_iso"])]->IsDefault)
                        $isdefault = true;
                }

                if(!$isdefault)
                    if(!empty($this->PredefinedMessages["en"]))
                        $this->PredefinedMessages["en"]->IsDefault = true;
            }
        }
	}

    function LoadSignatures()
    {
        if(DB_CONNECTION)
        {
            $this->Signatures = array();
            $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_SIGNATURES."` WHERE `group_id`='".@mysql_real_escape_string($this->Id)."'");
            if($result)
                while($row = mysql_fetch_array($result, MYSQL_BOTH))
                    $this->Signatures[strtolower($row["id"])] = new Signature($row);
        }
    }

	function GetXML()
	{
		if($this->IsDynamic)
		{
			$xml = "<v i=\"".base64_encode($this->Id)."\" n=\"".base64_encode($this->Descriptions["EN"])."\" o=\"".base64_encode($this->Owner)."\">";
			foreach($this->Members as $member)
				$xml .= "<crm i=\"".base64_encode($member)."\" />";
		}
		else
		{
			$xml = "<v id=\"".base64_encode($this->Id)."\" desc=\"".base64_encode($this->DescriptionArray)."\" created=\"".base64_encode($this->Created)."\"  email=\"".base64_encode($this->Email)."\" post_html=\"".base64_encode($this->PostHTML)."\" pre_html=\"".base64_encode($this->PreHTML)."\" mc=\"".base64_encode($this->MaxChats)."\" external=\"".base64_encode($this->IsExternal)."\"  internal=\"".base64_encode($this->IsInternal)."\" standard=\"".base64_encode($this->IsStandard)."\" teo=\"".base64_encode($this->TicketEmailOut)."\" thue=\"".base64_encode($this->TicketHandleUnknownEmails)."\">\r\n";
			if(is_array($this->VisitorFilters))
				foreach($this->VisitorFilters as $filt => $ex)
					$xml .= "<vfilt ex=\"".base64_encode((is_array($ex))?serialize($ex):$ex)."\">".$filt."</vfilt>\r\n";
			
			if(is_array($this->ChatVouchersRequired))
				foreach($this->ChatVouchersRequired as $tid)
					$xml .= "<ctr id=\"".base64_encode($tid)."\" />\r\n";

            if(is_array($this->TicketEmailIn))
                foreach($this->TicketEmailIn as $teid)
                    $xml .= "<tei id=\"".base64_encode($teid)."\" />\r\n";
					
			if(is_array($this->PredefinedMessages))
				foreach($this->PredefinedMessages as $premes)
					$xml .= $premes->GetXML();

            if(is_array($this->Signatures))
                foreach($this->Signatures as $sig)
                    $xml .= $sig->GetXML();

			if(is_array($this->OpeningHours))
				foreach($this->OpeningHours as $hour)
					$xml .= "<oh open=\"".base64_encode($hour[1])."\" close=\"".base64_encode($hour[2])."\">".base64_encode($hour[0])."</oh>\r\n";
		}
		return $xml;
	}
	
	function Save()
	{
		if($this->IsDynamic)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_GROUPS."` (`id`, `name`, `owner`,`dynamic`, `description`, `opening_hours`,`chat_inputs_hidden`, `ticket_inputs_hidden`, `chat_inputs_required`, `ticket_inputs_required`, `visitor_filters`, `chat_vouchers_required`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->Descriptions["EN"])."','".@mysql_real_escape_string($this->Owner)."',1,'','','','','','','','');");
		else
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_GROUPS."` (`id`, `dynamic`, `description`, `external`, `internal`, `created`, `email`, `opening_hours`, `functions`, `chat_inputs_hidden`, `ticket_inputs_hidden`, `max_chats`, `chat_inputs_required`, `ticket_inputs_required`, `visitor_filters`, `chat_vouchers_required`) VALUES ('".@mysql_real_escape_string($this->Id)."',0, '".@mysql_real_escape_string(serialize($this->Descriptions))."',1,".(($this->IsInternal) ? 1 : 0).",".$this->Created.",'".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string(serialize($this->OpeningHours))."','".@mysql_real_escape_string($this->ChatFunctions)."','a:0:{}','a:0:{}',-1,'a:0:{}','a:0:{}','".@mysql_real_escape_string(serialize($this->VisitorFilters))."','".@mysql_real_escape_string(serialize($this->ChatVouchersRequired))."');");
	}
	
	function Destroy()
	{
		queryDB(false,"DELETE FROM `".DB_PREFIX.DATABASE_GROUPS."` WHERE `id` = '".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	function RemoveMember($_id)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_GROUP_MEMBERS."` WHERE `user_id` = '".@mysql_real_escape_string($_id)."' AND `group_id` = '".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	function AddMember($_id)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_GROUP_MEMBERS."` (`user_id`, `group_id`) VALUES ('".@mysql_real_escape_string($_id)."', '".@mysql_real_escape_string($this->Id)."');");
	}
}

class Operator extends BaseUser
{
	public $Level = 0;
	public $Webspace = 0;
	public $LoginId;
	public $Password;
	public $PasswordChange;
	public $PasswordChangeRequest;
	public $Description;
	public $LCAFile;
	public $Profile;
	public $ServerSetup = false;
	public $Authenticated = false;
	public $VisitorFileSizes;
	public $VisitorStaticReload;
	public $ExternalChats;
	public $PermissionSet;
	public $Groups;
	public $GroupsArray;
	public $GroupsAway;
	public $GroupsHidden;
	public $PredefinedMessages = array();
    public $Signatures = array();
	public $InExternalGroup;
	public $ProfilePicture;
	public $ProfilePictureTime;
	public $WebcamPicture;
	public $WebcamPictureTime;
	public $LastChatAllocation;
	public $FirstCall = true;
	public $CanAutoAcceptChats;
	public $LoginIPRange = "";
	public $Reposts;
	public $WebsitesUsers;
	public $WebsitesConfig;
	public $SignOffRequest;
	public $IsBot = false;
	public $WelcomeManager = false;
	public $WelcomeManagerOfferHumanChatAfter = 0;
	public $BotFeeds;
    public $ClientWeb = false;
    public $ClientMobile = false;
    public $Deactivated;
	
	function Operator($_systemId,$_userId)
   	{
		$this->LastActive = 0;
		$this->SystemId = $_systemId;
		$this->UserId = $_userId;
		$this->ExternalChats = array();
		$this->PasswordFile = PATH_USERS . $this->SystemId . FILE_EXTENSION_PASSWORD;
		$this->PasswordFileTXT = PATH_USERS . $this->SystemId . FILE_EXTENSION_PASSWORD_TXT;
		$this->ChangePasswordFile = PATH_USERS . $this->SystemId . FILE_EXTENSION_CHANGE_PASSWORD;
		$this->Type = USER_TYPE_OPERATOR;
		$this->VisitorFileSizes = array();
		$this->GroupsAway = array();
		$this->GroupsHidden = array();
		$this->VisitorStaticReload = array();
		$this->Reposts = array();
		$this->WebsitesUsers = array();
		$this->WebsitesConfig = array();
		if(DB_CONNECTION)
			$this->Load();
   	}
	
	function LoadBotFeeds()
	{
		$this->BotFeeds = array();
		$result = queryDB(false,"SELECT * FROM `".DB_PREFIX.DATABASE_BOT_FEEDS."` WHERE `bot_id`='".@mysql_real_escape_string($this->SystemId)."' ORDER BY `tags` ASC;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->BotFeeds[] = new ChatBotFeed($row);
	}
	
	function SignOff($_init=true)
	{
        global $CONFIG;
        if($_init && ($this->LastActive < (time()-$CONFIG["timeout_clients"])))
            queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `sign_off`=0,`status`=2,`login_id`='' WHERE `system_id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1; ");
        else if($_init && $this->Status != USER_STATUS_OFFLINE)
            queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `sign_off`=1,`status`=2,`login_id`='' WHERE `system_id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1; ");
        else if(!$_init)
            queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `sign_off`=0 WHERE `system_id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1; ");

    }
	
	function GetBotAnswer($_question,$_alternate=true,$_chat,$html="")
	{
		global $LZLANG,$DEFAULT_BROWSER_LANGUAGE,$GROUPS,$CONFIG;
		$answers = array();
		
		$_question = str_replace(array("!",",",".","?","=",")","(","-","_",":","#","~","�"),"",strtolower($_question));
		$result = queryDB(false,"SELECT * FROM `".DB_PREFIX.DATABASE_BOT_FEEDS."` WHERE `bot_id`='".@mysql_real_escape_string($this->SystemId)."';");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if($row["search_type"] != 5)
					$row["tags"] = str_replace(array("!",".","?","=",")","(","-","_",":","#","~","�"),"",strtolower($row["tags"]));
					
				if(strpos(strtolower($row["language"]),strtolower($DEFAULT_BROWSER_LANGUAGE))===false && !empty($row["language"]))
					continue;
			
				$tags = explode(",",$row["tags"]);
				$count=0;
				foreach($tags as $tag)
					if($row["search_type"] == 5)
					{
						if(@preg_match($row["tags"], $_question) === 1)
							$count++;
					}
					else if(($row["search_type"] < 4 && strpos($_question,$tag)!==false) || jokerCompare($tag,$_question))
						$count++;
				
				if(!in_array($row["resource_id"],$answers))
				{
					if(($row["search_type"]==0 && $count==(substr_count($row["tags"],",")+1)) || ($row["search_type"]>0 && $count >=$row["search_type"]) || ($row["search_type"]>=4 && $count>0))
					{
						if(empty($row["answer"]))
						{
							if(getResource($row["resource_id"]) !== null)
							{
								$answers[$count."-".count($answers)] = array($row["resource_id"],true,!empty($row["new_window"]));
							}
						}
						else
						{
							$html = $row["answer"]."<br><br>";
							$answers = array();
							break;
						}
					}
				}
			}

		$tth = ".";
		$bind = " " . $LZLANG["client_or"] . " ";


		if(!empty($GROUPS[$_chat->DesiredChatGroup]) && $GROUPS[$_chat->DesiredChatGroup]->IsHumanAvailable())
		{
			$resultpc = queryDB(false,"SELECT * FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `chat_id`='".@mysql_real_escape_string($_chat->ChatId)."' AND `repost`=0 AND `receiver`='".@mysql_real_escape_string($this->SystemId)."';");
			if($this->WelcomeManager && mysql_num_rows($resultpc) >= $this->WelcomeManagerOfferHumanChatAfter)
			{
				$tth = " " . $LZLANG["client_or"] . " <a class=\"lz_chat_human\" onclick=\"lz_chat_set_talk_to_human(true,true);\">".$LZLANG["client_talk_to_human"]."</a>.";
				$bind = ", ";
			}
		}

        $lm = (empty($CONFIG["gl_dtfbc"])) ? ($bind . " <a class=\"lz_chat_mail\" onclick=\"lz_chat_require_leave_message();\">" . $LZLANG["client_leave_a_message"]. "</a>") : "";

        if(count($answers)==0 && empty($html))
			return $LZLANG["client_bot_no_result_found"] . $lm . $tth;
		else if(count($answers)>0)
		{
			$html .= $LZLANG["client_your_result"] . "<br>";
			krsort($answers);
			$html .= "<ul class=\"lz_chat_bot_resource\">";
			foreach($answers as $qa)
			{
				$res = getResource($qa[0]);
				$target = ($qa[2]) ? "target=\"_blank\" " : "";
				$html .= "<li>";
			 	if($res["type"] == 2)
					$html .= "<a href=\"". $res["value"]. "\" ".$target.">" . $res["title"]. "</a>";
				else if($res["type"] == 3 || $res["type"] == 4)
					$html .= "<a href=\"". LIVEZILLA_URL . "getfile.php?id=" . $res["id"]. "\" ".$target.">" . $res["title"]. "</a>";
				else
					$html .= "<b>" . $res["title"]. "</b><br><br>" . str_replace("<a ", "<a ".$target,str_replace("<A","<a",$res["value"]));
				$html .= "</li>";
			}
			$html .= "</ul>";
		}
		return $html . (($_alternate) ? ($LZLANG["client_bot_result_found"] . $lm . $tth) : "");
	}
	
	function Save($_create=false)
	{
		if($_create)
		{
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_OPERATORS."` (`id`, `system_id`, `fullname`, `email`, `permissions`, `password`, `status`, `level`, `ip`, `typing`, `visitor_file_sizes`, `groups_status`, `reposts`, `groups`, `languages`, `groups_hidden`, `websites_users`, `websites_config`) VALUES ('".@mysql_real_escape_string($this->UserId)."','".@mysql_real_escape_string($this->SystemId)."','".@mysql_real_escape_string($this->Fullname)."','".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string($this->PermissionSet)."','".@mysql_real_escape_string($this->Password)."', '".@mysql_real_escape_string($this->Status)."', '".@mysql_real_escape_string($this->Level)."', '".@mysql_real_escape_string($this->IP)."', '".@mysql_real_escape_string($this->Typing)."', '".@mysql_real_escape_string(serialize($this->VisitorFileSizes))."', '".@mysql_real_escape_string(serialize($this->GroupsAway))."','".@mysql_real_escape_string(serialize($this->Reposts))."','".@mysql_real_escape_string(base64_encode(serialize($this->Groups)))."','".@mysql_real_escape_string($this->Language)."','YTowOnt9','YTowOnt9','YTowOnt9');");
		}
		else
		{
			$ca = (count($this->ExternalChats)==0) ? ",`last_chat_allocation`=0" : "";
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `first_active`='".@mysql_real_escape_string($this->FirstActive)."',`password`='".@mysql_real_escape_string($this->Password)."',`login_id`='".@mysql_real_escape_string($this->LoginId)."',`visitor_file_sizes`='".@mysql_real_escape_string(serialize($this->VisitorFileSizes))."',`groups_status`='".@mysql_real_escape_string(serialize($this->GroupsAway))."',`reposts`='".@mysql_real_escape_string(serialize($this->Reposts))."',`typing`='".@mysql_real_escape_string($this->Typing)."',`level`='".@mysql_real_escape_string($this->Level)."',`status`='".@mysql_real_escape_string($this->Status)."',`ip`='".@mysql_real_escape_string($this->IP)."',`lweb`='".@mysql_real_escape_string($this->ClientWeb?1:0)."',`lapp`='".@mysql_real_escape_string($this->ClientMobile?1:0)."',`last_active`='".@mysql_real_escape_string(time())."'".$ca." WHERE `system_id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1; ");
	    }
	}
	
	function Load()
	{
		$this->LoadPredefinedMessages();
        $this->LoadSignatures();
	}
	
	function SetLastChatAllocation()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `last_chat_allocation`='".@mysql_real_escape_string(time())."' WHERE `system_id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1; ");
	}
	
	function SetRepostTime($_systemId,$_time)
	{
		if(empty($this->Reposts[$_systemId]))
			$this->Reposts[$_systemId] = 0;
		$this->Reposts[$_systemId] = max($this->Reposts[$_systemId],$_time);
	}

	function GetExternalObjects()
	{
		global $CONFIG;
		$result = queryDB(false,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` AS `t2` ON `t1`.`chat_id`=`t2`.`chat_id` WHERE `t1`.`exit`=0 AND `t2`.`status`<9 AND `t2`.`user_id`='".@mysql_real_escape_string($this->SystemId)."';");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$chat = new VisitorChat($row);
				if($chat->LastActive<(time()-$CONFIG["timeout_clients"]) && !($chat->InternalUser != null && $chat->InternalUser->IsBot))
					$chat->ExternalClose();
				else
					$this->ExternalChats[$chat->SystemId] = $chat;
			}
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FILES."` ORDER BY `created` ASC;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$request = new FileUploadRequest($row);
				$rsid = $request->SenderUserId . "~" . $request->SenderBrowserId;
				if(isset($this->ExternalChats[$rsid]))
				{
					$this->ExternalChats[$rsid]->FileUploadRequest[] = $request;
				}
			}
	}
	
	function IsInGroupWith($_compUser)
	{
		foreach($this->Groups as $gid)
			if(in_array($gid,$_compUser->Groups))
				return true;
		return false;
	}
	
	function IsExternal($_groupList, $_exclude=null, $_include=null, $_dynamic=false, $_ignoreExternal=false, $_ignoreOpeningHours=false)
	{
		global $GROUPS;
		initData(false,true);
		if(!empty($this->Groups))
			foreach($this->Groups as $groupid)
				if(((($GROUPS[$groupid]->IsOpeningHour()||$_ignoreOpeningHours) && !in_array($groupid,$this->GroupsAway)) || !$_dynamic))
				{
					$group_incl = !empty($_include) && in_array($groupid,$_include);
					$group_excl = (!empty($_exclude) && in_array($groupid,$_exclude)) || (!$group_incl && empty($_exclude) && !empty($_include));
					
					if(!empty($_groupList[$groupid]) && ($_groupList[$groupid]->IsExternal || $_ignoreExternal) && ($group_incl || !$group_excl))
					{
						return $this->InExternalGroup=true;
					}
				}
		return $this->InExternalGroup=false;
	}
	
	function GetGroupList($_excludeAwayGroups=false)
	{
		if(!$_excludeAwayGroups)
			return $this->Groups;
		else
		{
			$groupl = array();
			foreach($this->Groups as $groupid)
				if(!in_array($groupid,$this->GroupsAway))
					$groupl[] = $groupid;
			return $groupl;
		}
	}
	
	function GetExternalChatAmount($amount=0)
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` AS `t2` ON `t1`.`chat_id`=`t2`.`chat_id` WHERE `t1`.`exit`=0 AND `t2`.`status`<9 AND `t2`.`user_id`='".@mysql_real_escape_string($this->SystemId)."';");
		if($result)
			return @mysql_num_rows($result);
		return 0;
	}
	
	function LoadPredefinedMessages()
	{
		if(DB_CONNECTION)
		{
			$this->PredefinedMessages = array();
			$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PREDEFINED."` WHERE `internal_id`='".@mysql_real_escape_string($this->SystemId)."'");
			if($result)
				while($row = mysql_fetch_array($result, MYSQL_BOTH))
					$this->PredefinedMessages[strtolower($row["lang_iso"])] = new PredefinedMessage($row);
		}
	}

    function LoadSignatures()
    {
        if(DB_CONNECTION)
        {
            $this->Signatures = array();
            $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_SIGNATURES."` WHERE `operator_id`='".@mysql_real_escape_string($this->SystemId)."'");
            if($result)
                while($row = mysql_fetch_array($result, MYSQL_BOTH))
                    $this->Signatures[strtolower($row["id"])] = new Signature($row);
        }
    }
	
	function LoadProfile()
	{
		$this->Profile = null;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PROFILES."` WHERE `id`='".@mysql_real_escape_string($this->SystemId)."'");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->Profile = new Profile($row);
	}
	
	function IsVisitorFiltered($_visitor,$urlmatch=false)
	{
		global $GROUPS;
		foreach($this->Groups as $groupid)
		{
			if(empty($GROUPS[$groupid]->VisitorFilters))
				return false;
			foreach($GROUPS[$groupid]->VisitorFilters as $value => $filter)
			{
				foreach($_visitor->Browsers as $BROWSER)
				{
					if(count($BROWSER->History)==0)
						$BROWSER->LoadHistory();
						
					for($i = 0;$i < count($BROWSER->History);$i++)
					 {
						if(strpos(strtolower($BROWSER->History[$i]->Url->GetAbsoluteUrl()),strtolower(base64_decode($value))) !== false)
						{
							$urlmatch = true;
							if($filter=="Blacklist")
							{
								return true;
							}
						}
					}
				}
				if($filter=="Whitelist" && !$urlmatch)
				{
					return true;
				}
			}
		}
		return false;
	}
	
	function LoadPictures($_sessiontime=0)
	{
		$found = false;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` WHERE `internal_id`='".@mysql_real_escape_string($this->SystemId)."' AND `time` >= ".@mysql_real_escape_string($_sessiontime));
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$found = true;
				if(empty($row["webcam"]))
				{
					$this->ProfilePicture = $row["data"];
					$this->ProfilePictureTime = $row["time"];
				}
				else
				{
					$this->WebcamPicture = $row["data"];
					$this->WebcamPictureTime = $row["time"];
				}
			}
		return $found;
	}

	function SaveLoginAttempt($_password)
	{
		if(DB_CONNECTION)
		{
			if(strlen($this->PasswordChange)==32 && LOGIN)
			{
				$this->Password = $this->PasswordChange;
				$this->ChangePassword($this->Password,false,false);
			}

			if(!empty($this->LoginIPRange))
			{
				$match = false;
				$ranges = explode(",",$this->LoginIPRange);
				foreach($ranges as $range)
					if(getIP(true) == trim($range) || ipIsInRange(getIP(true),trim($range)))
						$match = true;
				if(!$match)
					return false;
			}
		
			$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` WHERE ip='".@mysql_real_escape_string(getIP(true))."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."' AND `time` > '".@mysql_real_escape_string(time()-86400)."';");
			if(@mysql_num_rows($result) >= MAX_LOGIN_ATTEMPTS)
				return false;
			
			$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` WHERE ip='".@mysql_real_escape_string(getIP(true))."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."' AND `time` > '".@mysql_real_escape_string(time()-86400)."' AND `password`='".@mysql_real_escape_string($_password)."';");
			if(@mysql_num_rows($result) == 0)
				queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` (`id` ,`user_id` ,`ip` ,`time` ,`password`) VALUES ('".@mysql_real_escape_string(getId(32))."', '".@mysql_real_escape_string($this->UserId)."', '".@mysql_real_escape_string(getIP(true))."', '".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($_password)."');");
			return true;
		}
		return true;
	}
	
	function DeleteLoginAttempts()
	{
		if(DB_CONNECTION)
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_OPERATOR_LOGINS."` WHERE ip='".@mysql_real_escape_string(getIP(true))."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."';");
	}
	
	function ChangePassword($_password, $_fromSession, $_requestDone=false)
	{
		if(isValidated() || !$_fromSession)
		{
			if($_fromSession)
			{
				$this->PasswordChange = md5($_password);
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `password_change`='".@mysql_real_escape_string(md5($_password))."' WHERE `system_id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1;");
			}
			else
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `password`='".@mysql_real_escape_string($this->PasswordChange)."',`password_change`='' WHERE `system_id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1;");
			}
			if($_requestDone)
				$this->SetPasswordChangeNeeded(false);
		}
	}
	
	function SetPasswordChangeNeeded($_needed)
	{
        if(isValidated())
		{
			$this->PasswordChangeRequest = $_needed;
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OPERATORS."` SET `password_change_request`='".(($_needed) ? "1" : "0") ."' WHERE `system_id`='".@mysql_real_escape_string($this->SystemId)."' LIMIT 1;");
		}
	}
	
	function GetPermission($_type)
	{
		return substr($this->PermissionSet,$_type,1);
	}
	
	function GetOperatorPictureFile()
	{
		global $CONFIG;
		$url = "picture.php?intid=".base64UrlEncode($this->UserId)."&acid=".getId(3);
		if(empty($CONFIG["gl_root"]))
			$url .= "&ws=" . base64UrlEncode($CONFIG["gl_host"]);
		return $url;
	}

	function GetLoginReply($_extern,$_time,$_oocount=0)
	{
		global $INTERNAL;
		foreach($INTERNAL as $internaluser)
			if($internaluser->Status != USER_STATUS_OFFLINE && $internaluser->SystemId != CALLER_SYSTEM_ID)
				$_oocount++;
		return "<login>\r\n<login_return oo=\"".base64_encode($_oocount)."\" group=\"".base64_encode($this->GroupsArray)."\" name=\"".base64_encode($this->Fullname)."\" loginid=\"".base64_encode($this->LoginId)."\" level=\"".base64_encode($this->Level)."\" sess=\"".base64_encode($this->SystemId)."\" extern=\"".base64_encode($_extern)."\" timediff=\"".base64_encode($_time)."\" time=\"".base64_encode(time())."\" perms=\"".base64_encode($this->PermissionSet)."\" sm=\"".base64_encode(SAFE_MODE)."\" phpv=\"".base64_encode(@phpversion())."\" sip=\"".base64_encode(@$_SERVER["SERVER_ADDR"])."\" uip=\"".base64_encode(@$_SERVER["REMOTE_ADDR"])."\" /></login>";
	}
}

class Visitor extends BaseUser
{
	public $Browsers;
	public $Response;
	public $IsChat = false;
	public $ActiveChatRequest;
	public $SystemInfo;
	public $Resolution;
	public $Host;
	public $Email;
	public $Company;
	public $Visits = 1;
	public $VisitsDay = 1;
	public $VisitId;
	public $VisitLast;
	public $GeoCity;
	public $GeoCountryName;
	public $GeoCountryISO2;
	public $GeoRegion;
	public $GeoLongitude= -522;
	public $GeoLatitude= -522;
	public $GeoTimezoneOffset = "+00:00";
	public $GeoISP;
	public $GeoResultId = 0;
	public $StaticInformation = false;
	public $ExitTime;
	public $Browser;
	public $OperatingSystem;
	public $Javascript;
	public $Signature;
	public $SignatureMismatch;
	public $IsCrawler;
	public $ExtendSession = false;
	public $FirstCall = true;
	public $HasAcceptedChatRequest;
	public $HasDeclinedChatRequest;
	
	function Visitor()
   	{
		$this->VisitId = getId(7);
		$this->Browsers = Array();
		$this->UserId = func_get_arg(0);
		$this->FirstActive = time();
		$this->VisitLast = time();
   	}
	
	function Load()
	{
		if(func_num_args() == 1)
		{
			$this->SetDetails(func_get_arg(0),false);
		}
		else
		{
			$result = queryDB(true,"SELECT *,(SELECT count(*) FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id`='".@mysql_real_escape_string($this->UserId)."') as `dcount` FROM `".DB_PREFIX.DATABASE_VISITORS."` WHERE `id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `entrance` DESC;");
			if(@mysql_num_rows($result) >= 1)
				$this->SetDetails(mysql_fetch_array($result, MYSQL_BOTH),true);
		}
	}
	
	function SetDetails($_data,$_self)
	{
		global $CONFIG;
		$this->FirstCall = ($_data["last_active"] < (time()-((!empty($_data["js"])) ? $CONFIG["timeout_track"] : 7200)) && !$this->ExtendSession);
		$this->VisitId = $_data["visit_id"];
		
		if($_self && $this->FirstCall)
		{
			$this->Visits = $_data["visits"]+1;
			$this->VisitId = $_data["visit_id"]=getId(7);
			$this->VisitsDay = $_data["dcount"]+1;
			$this->FirstActive = time();
		}
		else
		{
			$this->Visits =	$_data["visits"];
			$this->VisitsDay = $_data["dcount"];
			$this->FirstActive = $_data["entrance"];
		}
		$this->VisitLast = $_data["visit_last"];
		$this->ExitTime = $_data["last_active"];
		$this->IP = $_data["ip"];
		$this->SystemInfo = $_data["system"];
		$this->Language = $_data["language"];
		$this->Resolution = $_data["resolution"];
		$this->Host = $_data["host"];
		$this->GeoTimezoneOffset = $_data["timezone"];
		
		if(!empty($_data["longitude"]))
		{
			$this->GeoLongitude = $_data["longitude"];
			$this->GeoLatitude = $_data["latitude"];
		}
		if(!empty($_data["city"]))
			$this->GeoCity = $_data["city"];
		
		$this->GeoCountryISO2 = $_data["country"];
		if(isset($_data["countryname"]))
			$this->GeoCountryName = $_data["countryname"];
		$this->GeoRegion = $_data["region"];

		$this->GeoResultId = $_data["geo_result"];
		$this->GeoISP = $_data["isp"];

		$this->Browser = $_data["browser"];
		$this->OperatingSystem = $_data["system"];
		$this->Javascript = $_data["js"];
	}
	
	function LoadBrowsers($_outdated=false)
	{
		global $CONFIG,$INTERNAL;
		$this->Browsers = Array();
		$limiter = (!$_outdated && !(defined("LOGIN") && LOGIN)) ? " AND `last_active` > ".(time()-$CONFIG["timeout_track"])." " : "";

		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` WHERE `visit_id`='".@mysql_real_escape_string($this->VisitId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."'".$limiter."ORDER BY `created` ASC;"))
		{
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(empty($row["is_chat"]))
				{
					$loadHistory = !(!$_outdated && (defined("NO_CLIPPING") && !NO_CLIPPING) && !isset($_POST[POST_INTERN_RESYNC]) && !empty($INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes[$row["visitor_id"]][$row["id"]]) && $row["last_update"] == $INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes[$row["visitor_id"]][$row["id"]]);
					$browser = new VisitorBrowser($row["id"],$row["visitor_id"],$loadHistory);
					$browser->Query = (!empty($row["query"])) ? getIdValue(DATABASE_VISITOR_DATA_QUERIES,"query",$row["query"]) : "";
					$browser->Email = $row["email"];
					$browser->Fullname = $row["fullname"];
					$browser->Company = $row["company"];
					$browser->Customs = @unserialize($row["customs"]);
					$browser->LastUpdate = $row["last_update"];
				}
				else
				{
					$browser = new VisitorChat($row["visitor_id"],$row["id"]);
					$browser->Load();
					if($browser->LastActive<(time()-$CONFIG["timeout_clients"]) && !empty($browser->InternalUser) && !$browser->InternalUser->IsBot)
					{
						$browser->CloseChat();
						$browser->CloseWindow();
					}
				}
				$browser->Overlay = !empty($row["overlay"]);
				$browser->OverlayContainer = !empty($row["overlay_container"]);
				if(!empty($browser))
				{
					$this->Browsers[$row["id"]] = $browser;
					$this->Browsers[$row["id"]]->LastActive = $row["last_active"];
				}
			}
		}
	}
	
	function IsInChatWith($_operator)
	{
		global $CONFIG;
		foreach($this->Browsers as $browser)
			if($browser->Type == BROWSER_TYPE_CHAT && $browser->LastActive > (time()-$CONFIG["timeout_track"]) && !$browser->Closed)
				if(isset($browser->Members[$_operator->SystemId]))
					return true;
		return false;
	}
	
	function KeepAlive()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITORS."` SET `last_active`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `entrance` DESC LIMIT 1;");
	}
	
	function Save($_config,$_resolution,$_color,$_timezone,$_lat,$_long,$_countryiso2,$_city,$_region,$_geotimezone,$_isp,$_geosspan,$_grid,$_js=true,$_fromCookie=false)
	{
		global $STATS,$COUNTRIES,$COUNTRY_ALIASES;
		if(!$this->FirstCall)
		{
			$this->KeepAlive();
		}
		else
		{
			if(!isnull(getCookieValue("visits")) && $this->Visits==1)
				$this->Visits = getCookieValue("visits")+1;
			setCookieValue("visits",$this->Visits);
			if(!isnull(getCookieValue("last_visit")))
				$this->VisitLast = getCookieValue("last_visit");
			setCookieValue("last_visit",time());

			$this->IP = getIP();
			$this->SystemInfo = ((!empty($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : "");
			
			$localization = getBrowserLocalization();
			$this->Language = $localization[0];
			
			if(!empty($localization[1]))
				$this->GeoCountryISO2 = $localization[1];
			$this->Resolution = (!empty($_resolution) && count($_resolution) == 2 && !empty($_resolution[0]) && !empty($_resolution[1])) ? $_resolution[0] . " x " . $_resolution[1] : "";
			$this->Resolution .= (!empty($_color)) ? " (" . $_color . " Bit)" : "";
			$this->GeoTimezoneOffset = getLocalTimezone($_timezone);
			$this->GeoResult = 0;
			
			if(!empty($_geosspan))
				createSSpanFile($_geosspan);

			if(!empty($_config["gl_pr_ngl"]) && $_js)
			{
				if(!empty($_lat) && base64_decode($_lat) > -180)
				{
					setCookieValue(GEO_LATITUDE,$this->GeoLatitude = base64_decode($_lat));
					setCookieValue(GEO_LONGITUDE,$this->GeoLongitude = base64_decode($_long));
					setCookieValue(GEO_COUNTRY_ISO_2,$this->GeoCountryISO2 = base64_decode($_countryiso2));
					setCookieValue(GEO_CITY,$this->GeoCity = base64_decode($_city));
					setCookieValue(GEO_REGION,$this->GeoRegion = base64_decode($_region));
					setCookieValue(GEO_TIMEZONE,$this->GeoTimezoneOffset = base64_decode($_geotimezone));
					setCookieValue(GEO_ISP,$this->GeoISP = utf8_decode(base64_decode($_isp)));
					setCookieValue("geo_data",time());
				}
				else if(isset($_lat) && !empty($_lat))
				{
					$this->GeoLatitude = base64_decode($_lat);
					$this->GeoLongitude = base64_decode($_long);
				}
				else if(!isnull(getCookieValue("geo_data")) && !isnull(getCookieValue(GEO_LATITUDE)))
				{
					$this->GeoLatitude = getCookieValue(GEO_LATITUDE);
					$this->GeoLongitude = getCookieValue(GEO_LONGITUDE);
					$this->GeoCountryISO2 = getCookieValue(GEO_COUNTRY_ISO_2);
					$this->GeoCity = getCookieValue(GEO_CITY);
					$this->GeoRegion = getCookieValue(GEO_REGION);
					$this->GeoTimezoneOffset = getCookieValue(GEO_TIMEZONE);
					$this->GeoISP = getCookieValue(GEO_ISP);
					$_fromCookie = true;
				}

				removeSSpanFile(false);
				if($_fromCookie)
					$this->GeoResultId = 6;
				else if(!isnull($span=getSpanValue()))
				{
					if($span > (time()+CONNECTION_ERROR_SPAN))
						$this->GeoResultId = 5;
					else
						$this->GeoResultId = 4;
				}
				else
				{
					if(base64_decode($_lat) == -777)
						$this->GeoResultId = 5;
					else if(base64_decode($_lat) == -522)
						$this->GeoResultId = 2;
					else if($_grid != 4)
						$this->GeoResultId = 3;
					else
						$this->GeoResultId = $_grid;
				}
			}
			else
				$this->GeoResultId = 7;
				
			initData(false,false,false,false,false,false,true);
			if(isset($COUNTRY_ALIASES[$this->GeoCountryISO2]))
				$this->GeoCountryISO2 = $COUNTRY_ALIASES[$this->GeoCountryISO2];
			else if(!isset($COUNTRIES[$this->GeoCountryISO2]) && DEBUG_MODE)
				logit($this->GeoCountryISO2,LIVEZILLA_PATH  . "_log/unknown_countries.txt");
			
			$detector = new DeviceDetector();
			$detector->DetectBrowser();

			if($detector->AgentType == AGENT_TYPE_BROWSER || $detector->AgentType == AGENT_TYPE_UNKNOWN)
			{
				$detector->DetectOperatingSystem();
				if(DEBUG_MODE && !empty($_SERVER["HTTP_USER_AGENT"]))
				{
				 	if($detector->OperatingSystemUnknown)
						logit("OS UNKNOWN: ".$_SERVER["HTTP_USER_AGENT"],LIVEZILLA_PATH  . "_log/unknown_os.txt");
					else if($detector->AgentType == AGENT_TYPE_UNKNOWN)
						logit("AGENT UNKNOWN: ".$_SERVER["HTTP_USER_AGENT"],LIVEZILLA_PATH  . "_log/unknown_ag.txt");
				}
				
				$bid = $this->GetBrowserId($detector->Browser,$detector->AgentType);
				$oid = $this->GetOSId($detector->OperatingSystem);
				$row = $this->CreateSignature();

				if(is_array($row) && $row["id"] != $this->UserId)
				{
					$this->UserId = $row["id"];
					$this->SignatureMismatch = true;
				}
				else
				{	
					queryDB(DEBUG_MODE,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITORS."` (`id`, `entrance`,`last_active`, `host`,`ip`,`system`,`browser`, `visits`,`visit_id`,`visit_last`,`resolution`, `language`, `country`, `city`, `region`, `isp`, `timezone`, `latitude`, `longitude`, `geo_result`, `js`, `signature`) VALUES ('".@mysql_real_escape_string($this->UserId)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($this->Host)."', '".@mysql_real_escape_string($this->IP)."', '".@mysql_real_escape_string($oid)."','".@mysql_real_escape_string($bid)."', '".@mysql_real_escape_string($this->Visits)."', '".@mysql_real_escape_string($this->VisitId)."','".@mysql_real_escape_string($this->VisitLast)."', '".@mysql_real_escape_string(getValueId(DATABASE_VISITOR_DATA_RESOLUTIONS,"resolution",$this->Resolution, false, 32))."', '".@mysql_real_escape_string(substr(strtoupper($this->Language),0,5))."','".@mysql_real_escape_string($this->GeoCountryISO2)."', '".@mysql_real_escape_string(getValueId(DATABASE_VISITOR_DATA_CITIES,"city",$this->GeoCity,false))."', '".@mysql_real_escape_string(getValueId(DATABASE_VISITOR_DATA_REGIONS,"region",$this->GeoRegion,false))."', '".@mysql_real_escape_string(getValueId(DATABASE_VISITOR_DATA_ISPS,"isp",utf8_encode($this->GeoISP),false))."', '".@mysql_real_escape_string($this->GeoTimezoneOffset)."', '".@mysql_real_escape_string($this->GeoLatitude)."', '".@mysql_real_escape_string($this->GeoLongitude)."', '".@mysql_real_escape_string($this->GeoResultId)."', '".@mysql_real_escape_string($_js?1:0)."', '".@mysql_real_escape_string($this->Signature)."');");
					if(mysql_affected_rows() == 1)
                    {
						queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITORS."` SET `visit_latest`=0 WHERE `id`='".@mysql_real_escape_string($this->UserId)."' AND `visit_id`!='".@mysql_real_escape_string($this->VisitId)."';");
				    }
                }
			}
			else if(STATS_ACTIVE)
			{
				$this->IsCrawler = true;
				$STATS->ProcessAction(ST_ACTION_LOG_CRAWLER_ACCESS,array($this->GetCrawlerId($detector->Browser),null));
			}
		}
	}
	
	function ResolveHost()
	{
		$this->Host = getHost();
		if(!empty($this->Host) && $this->Host != $this->IP)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITORS."` SET `host`='".@mysql_real_escape_string($this->Host)."' WHERE `id`='".@mysql_real_escape_string($this->UserId)."' AND `visit_latest`=1;");
	}
	
	function CreateSignature($rrow=null)
	{
		$sig = @$_SERVER["HTTP_USER_AGENT"].@$_SERVER["HTTP_ACCEPT"].@$_SERVER["HTTP_ACCEPT_LANGUAGE"].@$_SERVER["HTTP_ACCEPT_CHARSET"];
		$this->Signature = md5(getIP() . $sig);
		
		if(empty($_GET["ovlc"]) && isnull(getCookieValue("userid")))
		{
			$result = queryDB(true,"SELECT `t1`.`city`,`t1`.`region`,`t1`.`isp`,`t1`.`timezone`,`t1`.`id`,`t2`.`customs`,`t2`.`fullname`,`t2`.`email`,`t2`.`company` FROM `".DB_PREFIX.DATABASE_VISITORS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` AS `t2` ON `t1`.`id`=`t2`.`visitor_id` WHERE `t1`.`signature`='".@mysql_real_escape_string($this->Signature)."';");
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$rrow = $row;
				if(!empty($row["fullname"]))
					return $rrow;
			}
			return $rrow;
		}
		return null;
	}
	
	function GetCrawlerId($_crawler)
	{
		queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_CRAWLERS."` (`id`, `crawler`) VALUES (NULL, '".@mysql_real_escape_string($_crawler)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_CRAWLERS."` WHERE `crawler`='".@mysql_real_escape_string($_crawler)."';"), MYSQL_BOTH);
		return $row["id"];
	}

	function GetOSId($_osname)
	{
		queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_SYSTEMS."` (`id`, `system`) VALUES (NULL, '".@mysql_real_escape_string($_osname)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_SYSTEMS."` WHERE `system`='".@mysql_real_escape_string($_osname)."';"), MYSQL_BOTH);
		return $row["id"];
	}
	
	function GetBrowserId($_browser,$_type)
	{
		queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_BROWSERS."` (`id`, `browser`) VALUES (NULL, '".@mysql_real_escape_string($_browser)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_BROWSERS."` WHERE `browser`='".@mysql_real_escape_string($_browser)."';"), MYSQL_BOTH);
		return $row["id"];
	}

	function UpdateOverlayDetails($_name,$_email)
	{
		$lu = substr(md5(time()),0,2);
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `fullname`='".@mysql_real_escape_string($_name)."',`email`='".@mysql_real_escape_string($_email)."',`last_update`='".@mysql_real_escape_string($lu)."' WHERE `visit_id`='".@mysql_real_escape_string($this->VisitId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND (overlay=1 OR overlay_container=1);");
	}

	function SaveTicket($_group,$_name,$_email,$_company,$_country,$_phone,$_cmb=false,$_text,$_custom = true)
	{
		global $INPUTS,$CONFIG,$INTERNAL;

        $isSpam = (!empty($CONFIG["gl_sft"]) && createSPAMFilter());

		$ticket = new UserTicket(getObjectId("ticket_id",DATABASE_TICKETS),strtoupper((!empty($this->Language)) ? $this->Language : $CONFIG["gl_default_language"]));
		$ticket->Messages[0]->Id = $ticket->Id;
		$ticket->Messages[0]->IP = getIP();

		if(!isTicketFlood() && !$isSpam)
		{
			initData(false,false,false,false,false,false,false,true);
			
			$ticket->SenderUserId = $ticket->Messages[0]->SenderUserId = $this->UserId;
			$ticket->Group = $_group;
			
			$ticket->Messages[0]->Fullname = $this->Browsers[0]->Fullname = $_name;
			$ticket->Messages[0]->Email = $this->Browsers[0]->Email = $_email;
			$ticket->Messages[0]->Company = $this->Browsers[0]->Company = $_company;
			$ticket->Messages[0]->Phone = $this->Browsers[0]->Phone = $_phone;
			$ticket->Messages[0]->Text = $_text;
			$ticket->Messages[0]->CallMeBack = $_cmb;
			$ticket->Messages[0]->Country = $_country;
			$ticket->Messages[0]->EmailId = getId(32);
            $ticket->Messages[0]->ChannelId = getId(32);

			$this->Browsers[0]->DesiredChatGroup = $ticket->Group;
			$this->Browsers[0]->SetCookieGroup();
			
			if($_custom)
				foreach($INPUTS as $index => $input)
				{
					if($input->Active && $input->Custom)
					{
						if(isset($_POST["p_cf".$index]) && !isset($_group->TicketInputsHidden[$index]))
							$ticket->Messages[0]->Customs[$index] = $this->Browsers[0]->Customs[$index] = base64UrlDecode($_POST["p_cf".$index]);
						else if(isset($_GET["cf".$index]) && !isset($_group->TicketInputsHidden[$index]))
							$ticket->Messages[0]->Customs[$index] = $this->Browsers[0]->Customs[$index] = base64UrlDecode($_GET["cf".$index]);
						if($input->Cookie && !empty($ticket->Messages[0]->Customs[$index]))
							setCookieValue("cf_".$index,$ticket->Messages[0]->Customs[$index]);
					}
					else if($input->Active)
					{
						if($input->Index == 111 && $input->Cookie)
							setCookieValue("form_111",$ticket->Messages[0]->Fullname);
						else if($input->Index == 112 && $input->Cookie)
							setCookieValue("form_112",$ticket->Messages[0]->Email);
						else if($input->Index == 113 && $input->Cookie)
							setCookieValue("form_113",$ticket->Messages[0]->Company);
						else if($input->Index == 114 && $input->Cookie)
							setCookieValue("form_114",$ticket->Messages[0]->Text);
						else if($input->Index == 116 && $input->Cookie)
							setCookieValue("form_116",$ticket->Messages[0]->Phone);
					}
				}
			if($CONFIG["gl_adct"] == 1 || !(!empty($CONFIG["gl_rm_om"]) && $CONFIG["gl_rm_om_time"] == 0))
				$ticket->Save();
			$this->AddFunctionCall("lz_chat_mail_callback(true);",false);

            if(!empty($_POST[POST_EXTERN_REQUESTED_INTERNID]) && !empty($INTERNAL[getInternalSystemIdByUserId(base64UrlDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]))]))
            {
                $TicketEditor = new TicketEditor($ticket->Id);
                $TicketEditor->Editor = getInternalSystemIdByUserId(base64UrlDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]));
                $TicketEditor->Status = 0;
                $TicketEditor->GroupId = $ticket->Group;
                $TicketEditor->Save();
            }
			return $ticket;
		}
		else
			$this->AddFunctionCall("lz_chat_mail_callback(false);",false);
		return false;
	}
	
	static function SendTicketAutoresponder($_ticket,$_language,$_customs=true,$details="",$cv="",$pdm=null)
	{
		global $INPUTS,$CONFIG,$GROUPS;

		$message = "%details%\r\n\r\n%mailtext%\r\n\r\n";
        $wordcount = str_words_count($message);

        if(!empty($GROUPS[$_ticket->Group]->PredefinedMessages))
		{
			$pdm = getPredefinedMessage($GROUPS[$_ticket->Group]->PredefinedMessages,$_language);
			if(!empty($pdm->EmailTicket))
				$message = $pdm->EmailTicket;
		}

        if($INPUTS[111]->Active && !empty($_ticket->Messages[0]->Fullname))
			$details .= strip_tags($INPUTS[111]->Caption) ." " . $_ticket->Messages[0]->Fullname . "\r\n";
		if(!empty($_ticket->Email))
			$details .= strip_tags($INPUTS[112]->Caption) ." " . $_ticket->Messages[0]->Email . "\r\n";
		if($INPUTS[113]->Active && !empty($_ticket->Messages[0]->Company))
			$details .= strip_tags($INPUTS[113]->Caption) ." " . $_ticket->Messages[0]->Company . "\r\n";
		if(($INPUTS[116]->Active || $_ticket->Messages[0]->CallMeBack) && !empty($_ticket->Messages[0]->Phone))
			$details .= strip_tags($INPUTS[116]->Caption) ." " . $_ticket->Messages[0]->Phone . "\r\n";

		$message = str_replace("%mailtext%",$_ticket->Messages[0]->Text,$message);
		$message = str_replace("%external_phone%",$_ticket->Messages[0]->Phone,$message);
		$message = str_replace("%external_name%",$_ticket->Messages[0]->Fullname,$message);
		$message = str_replace("%external_email%",$_ticket->Messages[0]->Email,$message);
		$message = str_replace("%website_name%",$CONFIG["gl_site_name"],$message);

		if($_customs && ++$wordcount > 0)
			foreach($INPUTS as $index => $input)
				if($input->Active && $input->Custom && !isset($_ticket->Group->TicketInputsHidden[$index]))
				{
					if($input->Type == "CheckBox")
						$details .= strip_tags($input->Caption). " " . ($cv = ((!empty($_ticket->Messages[0]->Customs[$index])) ? "<!--lang_client_yes-->" : "<!--lang_client_no-->")) . "\r\n";
					else if(!empty($_ticket->Messages[0]->Customs[$index]) || $input->Type == "ComboBox")
						$details .= strip_tags($input->Caption). " " . ($cv = $input->GetClientValue($_ticket->Messages[0]->Customs[$index])) . "\r\n";
					$message = str_replace("%custom".$index."%",$cv,$message);
				}

		$message = str_replace("%localdate%",date("r"),$message);
        $message = str_replace(array("%group_name%","%group_id%"),$_ticket->Group,$message);
		$message = str_replace("%group_description%",$GROUPS[$_ticket->Group]->Description,$message);
		$message = str_replace("%details%",$details,$message);
		$message = str_replace("%external_ip%",getIP(),$message);
        $message = str_replace("%ticket_id%",$_ticket->Id,$message);

        $mailbox = Mailbox::GetDefaultOutgoing();
        $_ticket->SendAutoresponder($mailbox, applyReplacements($message),$pdm,array());
	}
	
	function StoreFile($_browserId,$_partner,$_fullname,$_chatId)
	{
		$filename = namebase($_FILES['userfile']['name']);

		if(!isValidUploadFile($filename))
			return false;

        if(empty($_fullname))
            $_fullname = getNoName($this->UserId.getIP());

		$fileid = md5($filename . $this->UserId . $_browserId);
		$fileurid = EX_FILE_UPLOAD_REQUEST . "_" . $fileid;
		$filemask = $this->UserId . "_" . $fileid;

		$request = new FileUploadRequest($fileurid,$_partner,$_chatId);
		$request->Load();

		if($request->Permission == PERMISSION_FULL)
		{
			if(move_uploaded_file($_FILES["userfile"]["tmp_name"], PATH_UPLOADS . $request->FileMask))
			{
				createFileBaseFolders($_partner,false);
				processResource($_partner,$this->UserId,$_fullname,0,$_fullname,0,5,3);
				processResource($_partner,$fileid,$filemask,4,$_FILES["userfile"]["name"],0,$this->UserId,4,$_FILES["userfile"]["size"]);
				
				$request->Download = true;
				$request->Save();
				return true;
			}
			else
			{
				$request->Error = true;
				$request->Save();
			}
		}
		return false;
	}
	
	function SaveRate($_internalId,$_config, $_chatId)
	{
        global $CONFIG;
        $isSpam = (!empty($CONFIG["gl_sfc"]) && createSPAMFilter());
		$rate = new Rating(time() . "_" . getIP());
		if(!$rate->IsFlood() && !$isSpam)
		{
			$rate->RateComment = base64UrlDecode($_POST[POST_EXTERN_RATE_COMMENT]);
			$rate->RatePoliteness = base64UrlDecode($_POST[POST_EXTERN_RATE_POLITENESS]);
			$rate->RateQualification = base64UrlDecode($_POST[POST_EXTERN_RATE_QUALIFICATION]);
			$rate->Fullname = base64UrlDecode($_POST[POST_EXTERN_USER_NAME]);
			$rate->Email = base64UrlDecode($_POST[POST_EXTERN_USER_EMAIL]);
			$rate->Company = base64UrlDecode($_POST[POST_EXTERN_USER_COMPANY]);
			$rate->UserId = base64UrlDecode($_POST[POST_EXTERN_USER_USERID]);
			$rate->InternId = $_internalId;
			if($_config["gl_adct"] == 1 || !(!empty($_config["gl_rm_rt"]) && $_config["gl_rm_rt_time"] == 0))
				saveRating($rate, $_chatId);
			$this->AddFunctionCall("lz_chat_send_rate_callback(true);",false);
		}
		else
			$this->AddFunctionCall("lz_chat_send_rate_callback(false);",false);
	}
	
	function AddFunctionCall($_call,$_overwrite)
	{
		if(empty($this->Response))
			$this->Response = "";
		if($_overwrite)
			$this->Response = $_call;
		else
			$this->Response .= $_call;
	}
	
	function IsActivity($_browser)
	{
		global $CONFIG;
		if($this->IsInChat())
			return true;
		if(count($_browser->History) > 0 && $_browser->History[count($_browser->History)-1]->Entrance >= (time()-(($CONFIG["gl_inti"]*60)-$CONFIG["timeout_track"])))
			return true;
		return false;
	}
	
	function IsInChat($_noBotChats=false,$_browser=null)
	{
		global $CONFIG,$INTERNAL;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `last_active` > ".(time()-$CONFIG["timeout_clients"])." AND (`status` > 0 OR `waiting`=1);");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if(!$_noBotChats || (!empty($INTERNAL[$row["request_operator"]]) && !$INTERNAL[$row["request_operator"]]->IsBot) || !empty($row["waiting"]))
			{	
				if(!(!empty($_browser) && $_browser->BrowserId != $row["browser_id"]) || substr($row["browser_id"], strlen($row["browser_id"])-strlen("_OVL")) === "_OVL")
				{
					return true;
				}
			}
		}
		return false;
	}

	function WasInChat($_noBotChats=false)
	{
		global $INTERNAL;
		$result = queryDB(true,"SELECT `chat_id`,`internal_id` FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `external_id` = '".@mysql_real_escape_string($this->UserId)."';");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			if(!$_noBotChats || (!empty($INTERNAL[$row["internal_id"]]) && !$INTERNAL[$row["internal_id"]]->IsBot))
				return true;
		return $this->IsInChat($_noBotChats);
	}
	
	function GetChatRequestResponses()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `closed` ASC,`created` DESC;"))
		{
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(!empty($row["declined"]))
					$this->HasDeclinedChatRequest = true;
				if(!empty($row["accepted"]))
					$this->HasAcceptedChatRequest = true;
			}
		}
	}
}

class VisitorBrowser extends BaseUser
{
	public $BrowserId;
	public $History;
	public $ChatRequest;
	public $WebsitePush;
	public $OverlayBox;
	public $Alert;
	public $Type = BROWSER_TYPE_BROWSER;
	public $Query;
	public $VisitId;
	public $LastUpdate;
	public $Overlay;
	public $OverlayContainer;
	private $FirstCall = true;
	
	function VisitorBrowser($_browserid,$_userid,$_history=true)
   	{
		$this->BrowserId = $_browserid;
		$this->UserId = $_userid;
		$this->SystemId = $this->UserId . "~" . $this->BrowserId;
		if($_history)
			$this->LoadHistory();
		$this->FirstCall = (count($this->History)==0);
   	}
	
	function IsFirstCall()
	{
		return $this->FirstCall;
	}
	
	function LoadHistory()
	{
		if($result = queryDB(true,"SELECT `trefcode`.`area_code` as `ref_area_code`,`turlcode`.`area_code` as `url_area_code`,`".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`title` as `url_title`,`treftitle`.`title` as `ref_title`,`turldom`.`domain` as `url_dom`,`turlpath`.`path` as `url_path`,`trefdom`.`domain` as `ref_dom`,`trefpath`.`path` as `ref_path`,`entrance`,`params`,`untouched`,`ref_untouched` FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS `turl` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`url`=`turl`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` AS `tref` ON `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`referrer`=`tref`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS `trefdom` ON `tref`.`domain`=`trefdom`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` AS `turldom` ON `turl`.`domain`=`turldom`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` AS `trefpath` ON `tref`.`path`=`trefpath`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_PATHS."` AS `turlpath` ON `turl`.`path`=`turlpath`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` AS `treftitle` ON `tref`.`title`=`treftitle`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_AREA_CODES."` AS `trefcode` ON `tref`.`area_code`=`trefcode`.`id` INNER JOIN `".DB_PREFIX.DATABASE_VISITOR_DATA_AREA_CODES."` AS `turlcode` ON `turl`.`area_code`=`turlcode`.`id` WHERE `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."`.`entrance` ASC;"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->History[] = new HistoryURL($row);
	}

	function LoadChatRequest()
	{
		$count = 0;
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `closed` ASC,`created` DESC;"))
		{
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(!empty($row["declined"]))
					$this->HasDeclinedChatRequest = true;
				if(!empty($row["accepted"]))
					$this->HasAcceptedChatRequest = true;
				if(++$count == count($result))
					$this->ChatRequest = new ChatRequest($row);
			}
		}
	}
	
	function LoadAlerts()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_ALERTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `accepted` ASC,`created` ASC;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->Alert = new Alert($row);
	}
	
	function LoadWebsitePush()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `displayed` ASC,`accepted` ASC,`declined` ASC,`created` ASC LIMIT 1;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->WebsitePush = new WebsitePush($row);
	}
	
	function LoadOverlayBoxes()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_OVERLAY_BOXES."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `displayed` ASC,`created` ASC LIMIT 1;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->OverlayBox = new OverlayBox($row);
	}
	
	function SetQuery($_referrer,$issearchengine=false,$parammatch=false,$encoding="")
	{
		$parts = parse_url(strtolower($_referrer));
		$uparts = explode("&",@$parts["query"]);
		foreach(HistoryUrl::$SearchEngines as $sparam => $engines)
			foreach($uparts as $param)
			{
				$kv = explode("=",$param);
				$parammatch = ($kv[0] == $sparam && !empty($kv[1]));
				
				foreach($engines as $engine)
				{
					if(jokerCompare($engine,$parts["host"]))
						$issearchengine = true;
						
					if($issearchengine && $parammatch)
					{
						if(empty($encoding))
							foreach(HistoryUrl::$SearchEngineEncodings as $enc => $eengines)
								foreach($eengines as $eengine)
									if($eengine==$engine)
										$encoding = $enc;
						$this->Query = (empty($encoding)) ? urldecode(trim($kv[1])) : html_entity_decode(@iconv($encoding,"UTF-8",urldecode(trim($kv[1]))), ENT_QUOTES, 'UTF-8');
						
						if(!empty($this->Query) && isnull(getCookieValue("sp")))
							setCookieValue("sp",$this->Query);
							
						queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `query`='".@mysql_real_escape_string($this->GetQueryId($this->Query,$_referrer))."' WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' LIMIT 1;");
						return true;
					}
				}
			}
		return $issearchengine;
	}
	
	function GetQueryId($_query,$_referrer,$_maxlength=255,$_self=false)
	{
		if(empty($_query))
			$_query = "";
	
		if(!$_self && $_maxlength != null && strlen($_query) > $_maxlength)
			$_query = substr($_query,0,$_maxlength);
		
		$result = queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_QUERIES."` (`id`, `query`) VALUES (NULL, '".@mysql_real_escape_string($_query)."');");
		if(!$_self && !empty($_query) && !$result && !isnull(mysql_errno()) && mysql_errno() != 1062)
			$this->GetQueryId(utf8_encode(urldecode($_query)),$_referrer,$_maxlength,true);

		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_QUERIES."` WHERE `query`='".@mysql_real_escape_string($_query)."';"), MYSQL_BOTH);
		return $row["id"];
	}
	
	function ForceUpdate()
	{
		$this->LastUpdate = substr(md5(time()),0,2);
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `last_update`='".@mysql_real_escape_string($this->LastUpdate)."' WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' LIMIT 1;");
	}
	
	function Save()
	{
		if(!($this->FirstCall && $res = queryDB(true,$tt="INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` (`id`, `visitor_id`, `visit_id`, `created`, `last_active`, `last_update`, `is_chat`,`customs`,`fullname`,`email`,`company`,`phone`,`pre_message`,`overlay`,`overlay_container`) VALUES ('".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->UserId)."','".@mysql_real_escape_string($this->VisitId)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string(substr(md5(time()),0,2))."','".@mysql_real_escape_string($this->Type)."','".@mysql_real_escape_string(serialize($this->Customs))."','".@mysql_real_escape_string($this->Fullname)."','".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string($this->Company)."','".@mysql_real_escape_string($this->Phone)."','',".($this->Overlay?1:0).",".($this->OverlayContainer?1:0).");")))
			if(!$this->FirstCall)
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `last_active`=".time().",`visit_id`='".@mysql_real_escape_string($this->VisitId)."' WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' LIMIT 1;");
    }
	
	function Destroy()
	{
		global $CONFIG;
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `last_active`=`last_active`-".@mysql_real_escape_string($CONFIG["timeout_track"])." WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' LIMIT 1;");
	}
	
	function SaveLoginData()
	{
		global $INPUTS;
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSERS."` SET `customs`='".@mysql_real_escape_string(serialize($this->Customs))."',`fullname`='".@mysql_real_escape_string($this->Fullname)."',`email`='".@mysql_real_escape_string($this->Email)."',`company`='".@mysql_real_escape_string($this->Company)."',`phone`='".@mysql_real_escape_string($this->Phone)."' WHERE `id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."';");
	}
}

class VisitorChat extends VisitorBrowser
{
	public $DesiredChatGroup;
	public $DesiredChatPartner;
	public $Forward;
	public $Waiting;
	public $Chat;
	public $Code = "";
	public $Type = BROWSER_TYPE_CHAT;
	public $ConnectingMessageDisplayed = null;
	public $Members;
	public $TranscriptEmail;
	public $ChatId;
	public $ResponseTime;
	public $ArchiveCreated;
	public $Activated;
	public $Closed;
	public $Declined = 0;
	public $InternalActivation;
	public $ExternalActivation;
	public $ExternalClosed;
	public $InternalClosed;
	public $InternalUser;
	public $FileUploadRequest = null;
	public $LastActive = 0;
	public $FirstCall = true;
	public $Priority = 2;
	public $AllocatedTime = 0;
	public $QueueMessageShown = false;
	public $ChatVoucherId = "";
	public $CallMeBack = false;
	public $QueuedPosts;
	public $InitChatWith;
	
	function VisitorChat()
   	{
		if(func_num_args() == 2)
		{
			$this->UserId = func_get_arg(0);
			$this->BrowserId = func_get_arg(1);
			$this->FirstCall = true;
			$this->QueuedPosts = array();
		}
		else if(func_num_args() == 10)
		{
			$this->UserId = func_get_arg(0);
			$this->BrowserId = func_get_arg(1);
			$this->Fullname = func_get_arg(2);
			$this->Email = func_get_arg(3);
			$this->Company = func_get_arg(4);

			$this->Question = func_get_arg(5);
			$this->Customs = func_get_arg(6);
			$this->DesiredChatGroup = func_get_arg(7);
			$this->DesiredChatPartner = func_get_arg(8);
			$this->FirstCall = true;

            $this->Phone = func_get_arg(9);
		}
		else if(func_num_args() == 1)
		{
			$this->SetValues(func_get_arg(0));
		}
		parent::__construct($this->BrowserId,$this->UserId);
   	}
	
	function GetParent()
	{
		return parent;
	}
	
	function SetCookieGroup()
	{
		if(!empty($this->DesiredChatGroup))
			setCookieValue("login_group",$this->DesiredChatGroup);
	}
	
	function RequestFileUpload($_user,$_filename)
	{
		$fileid = md5(namebase($_filename) . $this->UserId . $this->BrowserId);
		$filemask = $this->UserId . "_" . $fileid;
		$fileurid = EX_FILE_UPLOAD_REQUEST . "_" . $fileid;
		$request = new FileUploadRequest($fileurid,$this->DesiredChatPartner,$this->ChatId);
		$request->SenderUserId = $this->UserId;
		$request->FileName = namebase($_filename);
		$request->FileMask = $filemask;
		$request->FileId = $fileid;
		$request->SenderBrowserId = $this->BrowserId;
		$request->Load();

		if(!$request->FirstCall && !$request->Closed)
		{
			if($request->Permission == PERMISSION_FULL)
			{
				$_user->AddFunctionCall("lz_chat_file_start_upload('".$_filename."');",false);
			}
			else if($request->Permission == PERMISSION_NONE)
			{
				$_user->AddFunctionCall("lz_chat_file_stop();",false);
				$_user->AddFunctionCall("lz_chat_file_error(1);",false);
				$request->Close();
			}
		}
		else
		{
			$request->FirstCall = true;
			$request->Error = false;
			$request->Closed = false;
			$request->Permission = PERMISSION_VOID;
			if(!isValidUploadFile($_filename))
				$_user->AddFunctionCall("lz_chat_file_error(2);",false);
			else
			{
				$request->Save();
			}
		}
		return $_user;
	}
	
	public static function GetRelatedChatVouchers($_groupId,$_ticket)
	{
		global $CONFIG,$GROUPS;
		$vouchers = array();
		$result = queryDB(true,$d = "SELECT *,`t1`.`id` AS `ticketid` FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` AS `t2` ON `t1`.`tid`=`t2`.`id` WHERE `t1`.`id`='".@mysql_real_escape_string($_ticket->Id)."' OR (`t1`.`paid`=1 AND `voided`=0 AND (`t1`.`id`='".@mysql_real_escape_string($_ticket->Id)."' OR `t1`.`extends`='".@mysql_real_escape_string($_ticket->Id)."' OR `t1`.`id`='".@mysql_real_escape_string($_ticket->Extends)."' OR (`t1`.`extends`!='' AND `t1`.`extends`='".@mysql_real_escape_string($_ticket->Extends)."'))) ORDER BY `created` DESC;");

		while($row = @mysql_fetch_array($result, MYSQL_BOTH))
		{
			if(!empty($CONFIG["db"]["cct"][$row["tid"]]))
			{
				$ticket = new CommercialChatVoucher($row);
				$ticket->CheckForVoid();
				if($_ticket->Id == $ticket->Id || (!$ticket->Voided && in_array($row["tid"],$GROUPS[$_groupId]->ChatVouchersRequired)))
					$vouchers[] = $ticket;
			}
		}
		return $vouchers;
	}
	
	public static function GetMatchingVoucher($_groupId,$_voucherid)
	{
		global $CONFIG,$GROUPS;
		initData(false,true);
		
		$ticket = new CommercialChatVoucher("",$_voucherid);
		$ticket->Load();

		$result = queryDB(true,$d = "SELECT *,`t1`.`id` AS `ticketid`, SUM(`chat_time_max`) AS `chat_time_max`, SUM(`chat_time`) AS `chat_time`, SUM(`chat_sessions`) AS `chat_sessions`, SUM(`chat_sessions_max`) AS `chat_sessions_max`, MAX(`expires`) AS `expires` FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` AS `t2` ON `t1`.`tid`=`t2`.`id` WHERE `t1`.`id`='".@mysql_real_escape_string($_voucherid)."' ORDER BY `created` ASC;");

		while($row = @mysql_fetch_array($result, MYSQL_BOTH))
		{
			if(!empty($CONFIG["db"]["cct"][$row["tid"]]))
			{
				$ticket = new CommercialChatVoucher($row);
				if(in_array($row["tid"],$GROUPS[$_groupId]->ChatVouchersRequired))
				{
					return $ticket;
				}
			}
		}
		return null;
	}
	
	function SetCallMeBackStatus($_success)
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `call_me_back`='".@mysql_real_escape_string($_success)."' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `call_me_back`='".@mysql_real_escape_string($_success)."' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
	}
	
	function AbortFileUpload($_user,$_filename,$_error)
	{
		$fileid = md5(namebase($_filename) . $this->UserId . $this->BrowserId);
		$request = new FileUploadRequest(EX_FILE_UPLOAD_REQUEST . "_" . $fileid, $this->DesiredChatPartner,$this->ChatId);
		$request->Load();
		if(!$request->Closed)
		{
			$request->Error = $_error;
			$request->Save();
		}
		else
		{
			$_user->AddFunctionCall("lz_chat_file_reset();",false);
		}
		return $_user;
	}
	
	function Load()
	{
		$this->Status = CHAT_STATUS_OPEN;
		$this->LastActive = time();
		$this->Members = array();
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `first_active` DESC LIMIT 2;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(empty($row["external_close"]))
				{
					$this->FirstCall = !empty($row["exit"]);
					$this->SetValues($row);
				}
				if(empty($this->Customs))
					$this->Customs = @unserialize($row["customs"]);
					
				if(!empty($row["request_operator"]) && empty($this->DesiredChatPartner))
				{
					$this->DesiredChatPartner = $row["request_operator"];
				}
				if(!empty($row["request_group"]) && empty($this->DesiredChatGroup))
					$this->DesiredChatGroup = $row["request_group"];
			}
		$this->LoadMembers();
	}
	
	function LoadMembers()
	{
		global $INTERNAL;
		initData(true);

		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` WHERE `status`<9 AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' ORDER BY `status` DESC, `dtime` DESC;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			if(isset($INTERNAL[$row["user_id"]]))
			{
				$this->Members[$row["user_id"]] = new ChatMember($row["user_id"],$row["status"],!empty($row["declined"]),$row["jtime"],$row["ltime"]);

                if($row["status"] < 2)
					$this->InternalUser = $INTERNAL[$row["user_id"]];
				$this->Declined = $row["dtime"];
			}
	}

    function GetLastOperator(&$_waitingTime,&$_startMarker,&$_endMarker)
    {
        if(!empty($this->Declined))
        {
            $_startMarker = 2;
            $_waitingTime = $this->Declined-$this->FirstActive;
        }
        else if($this->InternalActivation)
        {
            $_startMarker = 1;
            $_waitingTime = $this->AllocatedTime-$this->FirstActive;
        }
        else
        {
            $_startMarker = 0;
            $_waitingTime = $this->LastActive-$this->FirstActive;
        }

        if(($this->InternalActivation && $this->InternalClosed) || $this->Declined)
            $_endMarker = 1;

        $_waitingTime = max($_waitingTime,0);

        if(!empty($this->InternalUser))
            return $this->InternalUser->SystemId;
        else
            return "";
    }
	
	function SetValues($row)
	{
		$this->LastActive = $row["last_active"];
		$this->Fullname = $row["fullname"];
		$this->Company = $row["company"];
		$this->Phone = $row["phone"];
		$this->Email = $row["email"];
		$this->AllocatedTime = $row["allocated"];
		$this->Waiting = $row["waiting"];
		$this->FirstActive = $row["first_active"];
		$this->Typing = !empty($row["typing"]);
		$this->Code = $row["area_code"];
		$this->ChatId = $row["chat_id"];
		$this->VisitId = $row["visit_id"];
		$this->QueuedPosts = @unserialize($row["queue_posts"]);
		$this->DesiredChatPartner = $row["request_operator"];
		$this->DesiredChatGroup = $row["request_group"];
		$this->Question = $row["question"];
		$this->Priority = $row["priority"];
		$this->ChatVoucherId = $row["chat_ticket_id"];
		$this->ArchiveCreated = $row["archive_created"];
		$this->InternalActivation = !empty($row["internal_active"]);
		$this->Declined = !empty($row["internal_declined"]);
		$this->Closed = !empty($row["exit"]);
		$this->CallMeBack = $row["call_me_back"];
		$this->ExternalActivation = !empty($row["external_active"]);
		$this->ExternalClosed = !empty($row["external_close"]);
		$this->InternalClosed = !empty($row["internal_closed"]);
		$this->LastActive = $row["last_active"];
		$this->InitChatWith = $row["init_chat_with"];
		$this->UserId = $row["visitor_id"];
		$this->BrowserId = $row["browser_id"];
		$this->Status = $row["status"];
		$this->Customs = @unserialize($row["customs"]);
		$this->QueueMessageShown = !empty($row["queue_message_shown"]);
		$this->Activated = (($this->ExternalActivation && $this->InternalActivation) ? CHAT_STATUS_ACTIVE : (($this->ExternalActivation || $this->InternalActivation) ? CHAT_STATUS_WAITING : CHAT_STATUS_OPEN));
	}
	
	function SetChatId()
	{
		if(isset($_POST[POST_EXTERN_CHAT_ID]) && $this->Status != CHAT_STATUS_OPEN)
		{
			$this->ChatId = base64UrlDecode($_POST[POST_EXTERN_CHAT_ID]);
		}
		else
		{
			$result = queryDB(true,"SELECT `chat_id` FROM `".DB_PREFIX.DATABASE_INFO."`");
			$row = mysql_fetch_array($result, MYSQL_BOTH);
			$cid = $row["chat_id"]+1;
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_INFO."` SET `chat_id`='".@mysql_real_escape_string($cid)."' WHERE `chat_id`='".@mysql_real_escape_string($row["chat_id"])."'");
			if(mysql_affected_rows() == 0)
			{
				$this->ChatId = $this->SetChatId();
				return;
			}
			else
			{
				$this->ChatId = $cid;
			}
		}
		$this->FirstActive = time();
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `last_active`='".@mysql_real_escape_string(time())."',`first_active`='".@mysql_real_escape_string(time())."',`chat_id`='".@mysql_real_escape_string($this->ChatId)."' WHERE `exit`=0 AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `first_active` DESC LIMIT 1;");
		return $this->ChatId;
	}
	
	function SetStatus($_status)
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `last_active`='".@mysql_real_escape_string(time())."',`status`='".@mysql_real_escape_string($_status)."' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
	}
	
	function SetWaiting($_waiting)
	{
		$this->Waiting=$_waiting;
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `waiting`='".@mysql_real_escape_string((($_waiting)?1:0))."' WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
	}
	
	function UpdateArchive($_tcemail,$_email="",$_name="")
	{
		if(!empty($_name))
			$_name = ",`fullname`='" . @mysql_real_escape_string($_name)."'";
		if(!empty($_email))
			$_email = ",`email`='" . @mysql_real_escape_string($_email)."'";
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `transcript_receiver`='".@mysql_real_escape_string($_tcemail)."'".$_name.$_email." WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
	}
	
	function LoadForward($_checkReceived=true,$_loadInvites=false)
	{
		$this->Forward = null;
		$_checkReceived = ($_checkReceived) ? " AND `received`=0" : "";
		$_loadInvites = (!$_loadInvites) ? " AND `invite`=0" : "";
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `browser_id`='".@mysql_real_escape_string($this->BrowserId)."'".$_checkReceived.$_loadInvites." ORDER BY `created` DESC LIMIT 1;");
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			$this->Forward = new Forward($row);
	}
	
	function JoinChat($_internalUser,$_invisible=false,$_rePost=false)
	{
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` (`chat_id`,`user_id`,`jtime`,`status`,`alloc`) VALUES ('".@mysql_real_escape_string($this->ChatId)."','".@mysql_real_escape_string($_internalUser)."',".(($_invisible) ? 0 : time()).",".(($_invisible) ? 2 : 1).",0);");
		if(@mysql_affected_rows() != 1)
		{
			$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string($_internalUser)."' LIMIT 1;");
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$jtime = ($_invisible && ($row["status"] == 1 || $row["status"] == 0)) ? "`jtime`" : (($_invisible) ? 0 : time());
				queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `ltime`=".(($_invisible) ? time() : 0).",`jtime`=".$jtime.",`dtime`=0,`declined`=0,`status`=".(($_invisible) ? 2 : 1)." WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string($_internalUser)."' LIMIT 1;");
			}
		}
		if($_rePost)
		{
			$this->RepostChatHistory(1,$this->ChatId,$_internalUser,0,0,"",$this->ChatId);
			return;
		}
	}
	
	function LeaveChat($_internalUser)
	{
		if(count($this->Members)>=2 && !empty($this->Members[$_internalUser]) && $this->Members[$_internalUser]->Status == 0)
			foreach($this->Members as $sysid => $member)
				if($member->Status == 1)
				{
					$this->SetHost($sysid);
					break;
				}

		queryDB(false,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string($_internalUser)."' AND `status`=2 LIMIT 1;");
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `status`=9,`ltime`=".time()." WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string($_internalUser)."' LIMIT 1;");
	}
	
	function SetHost($_internalUser)
	{
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `status`=0 WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string($_internalUser)."' LIMIT 1;");
		if(@mysql_affected_rows() != 1)
			queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` (`chat_id`,`user_id`,`status`) VALUES ('".@mysql_real_escape_string($this->ChatId)."','".@mysql_real_escape_string($_internalUser)."',0);");
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `status`=1 WHERE `status`=0 AND `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' AND `user_id`!='".@mysql_real_escape_string($_internalUser)."';");
	}
	
	function SetPriority($_priority)
	{
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `priority`='".@mysql_real_escape_string($_priority)."' WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
	}
	
	function SetTargetOperator($_internalUser)
	{
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `request_operator`='".@mysql_real_escape_string($_internalUser)."' WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
	}
	
	function RequestInitChat($_internalUser)
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `init_chat_with`='".@mysql_real_escape_string($_internalUser)."' WHERE `browser_id` = '".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id` = '".@mysql_real_escape_string($this->UserId)."';");
	}
	
	function SetTargetGroup($_groupId)
	{
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `request_group`='".@mysql_real_escape_string($_groupId)."' WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
	}
	
	function TakeChat($_internalUser,$_groupId)
	{
		$this->SetHost($_internalUser);
		$_groupId = (!empty($_groupId)) ? ",`request_group`='".@mysql_real_escape_string($_groupId)."'" : "";
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `status`=0,`declined`=0,`dtime`=0,`ltime`=0,`jtime`=".time()." WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string($_internalUser)."' LIMIT 1;");
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `status`=1,`waiting`=0,`request_operator`='".@mysql_real_escape_string($_internalUser)."'".$_groupId." WHERE `chat_id` = '".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
	}
	
	function ApplyCustomValues($_array,$_prefix,$_get=false)
	{
		global $INPUTS;
		foreach($INPUTS as $index => $input)
		{
			if($input->Active && $input->Custom)
			{
				if(isset($_array[$_prefix.$index]))
				{
					$this->Customs[$index] = base64UrlDecode($_array[$_prefix.$index]);
					if($input->Cookie)
						setCookieValue("cf_".$index,base64UrlDecode($_array[$_prefix.$index]));
				}
			}
			else if($index == 111 && $input->Cookie && ($_get || (isset($_array[POST_EXTERN_USER_NAME]) && !empty($_array[POST_EXTERN_USER_NAME]))))
				setCookieValue("form_" . $index,$this->Fullname);
			else if($index == 112 && $input->Cookie && ($_get || (isset($_array[POST_EXTERN_USER_EMAIL]) && !empty($_array[POST_EXTERN_USER_EMAIL]))))
				setCookieValue("form_" . $index,$this->Email);
			else if($index == 113 && $input->Cookie && isset($_array[POST_EXTERN_USER_COMPANY]) && !empty($_array[POST_EXTERN_USER_COMPANY]))
				setCookieValue("form_" . $index,$this->Company);
			else if($index == 114 && $input->Cookie && isset($_array[POST_EXTERN_USER_COMPANY]) && !empty($_array[POST_EXTERN_USER_QUESTION]))
				setCookieValue("form_" . $index,$this->Question);
			else if($index == 116 && $input->Cookie && isset($_array["p_phone"]) && !empty($_array["p_phone"]))
				setCookieValue("form_" . $index,$this->Phone);
		}
	}
	
	function CreateChat($_internalUser, $_visitor, $_host=false, $custom="", $etpl="", $_customsInTranscript=true, $_externalSelf=true, $pdm=null)
	{
        global $CONFIG;

        if(!empty($CONFIG["gl_sfc"]) && createSPAMFilter())
            return;

		$this->InternalUser = $_internalUser;
		$this->InternalUser->SetLastChatAllocation();
		$this->SetStatus(CHAT_STATUS_WAITING);
		initData(false,false,false,false,false,false,false,true);
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` (`chat_id`,`user_id`,`jtime`,`status`) VALUES ('".@mysql_real_escape_string($this->ChatId)."','".@mysql_real_escape_string($this->InternalUser->SystemId)."',".time().",".(($_host) ? 0 : 1).");");
		
        $this->CreateArchiveEntry($_internalUser, $_visitor, $custom, $etpl, $_customsInTranscript, $pdm);
		
		if($_internalUser->IsBot)
		{
			define("CALLER_SYSTEM_ID",$_internalUser->SystemId);
			$this->InternalActivate();
			$this->ExternalActivate();
			$this->SetStatus(CHAT_STATUS_ACTIVE);
		}
		
		if(!empty($_GET["acid"]))
		{
			$pchatid = base64UrlDecode($_GET["acid"]);
			$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `chat_id`='".@mysql_real_escape_string($pchatid)."' AND (`exit` > ".(time()-30)." OR `exit`=0) LIMIT 1;");
			if($result && @mysql_num_rows($result) == 1)
			{
				$row = mysql_fetch_array($result, MYSQL_BOTH);
				if(empty($row["waiting"]))
					$this->RepostChatHistory(2,$pchatid,$this->InternalUser->SystemId,0,0,$this->UserId."~".$this->UserId."_OVL",$this->ChatId,$this->SystemId,true,false,$_externalSelf);
			}
		}
	}

    function CreateArchiveEntry($_internalUser, $_visitor, $custom="", $etpl="", $_customsInTranscript=true, $pdm=null)
    {
        global $CONFIG,$INPUTS,$GROUPS,$LZLANG;

        if($this->ArchiveCreated)
            return;

        if($INPUTS[111]->Active)
            $custom .= strip_tags($INPUTS[111]->Caption) . "%efullname%\r\n";
        if($INPUTS[112]->Active)
            $custom .= strip_tags($INPUTS[112]->Caption) . "%eemail%\r\n";
        if(!empty($this->Company) && $INPUTS[113]->Active)
            $custom .= strip_tags($INPUTS[113]->Caption) . " " . trim($this->Company) . "\r\n";
        if(!empty($this->Question) && $INPUTS[114]->Active)
            $custom .= strip_tags($INPUTS[114]->Caption) . " " . trim($this->Question) . "\r\n";
        if(!empty($this->Phone) && $INPUTS[116]->Active)
            $custom .= strip_tags($INPUTS[116]->Caption) . " " . trim($this->Phone) . "\r\n";
        if(!empty($this->ChatVoucherId))
            $custom .= strip_tags($LZLANG["client_voucher_id"]) . " " . trim($this->ChatVoucherId) . "\r\n";

        $customs = array();
        if(is_array($this->Customs))
            foreach($this->Customs as $cind => $value)
                if($INPUTS[$cind]->Active && $INPUTS[$cind]->Custom)
                {
                    $customs[$INPUTS[$cind]->Name] = $value;
                    if(!isset($GROUPS[$this->DesiredChatGroup]->ChatInputsHidden[$cind]) && $_customsInTranscript)
                    {
                        if($INPUTS[$cind]->Type == "CheckBox")
                            $custom .= strip_tags($INPUTS[$cind]->Caption). " " . ((!empty($value)) ? "<!--lang_client_yes-->" : "<!--lang_client_no-->") . "\r\n";
                        else if($INPUTS[$cind]->Type == "ComboBox")
                            $custom .= strip_tags($INPUTS[$cind]->Caption). " " . $INPUTS[$cind]->GetClientValue($value) . "\r\n";
                        else
                            $custom .= strip_tags($INPUTS[$cind]->Caption). " " . $value . "\r\n";
                    }
                }

        if(!empty($GROUPS[$this->DesiredChatGroup]->PredefinedMessages))
        {
            $pdm = getPredefinedMessage($GROUPS[$this->DesiredChatGroup]->PredefinedMessages,$this);
            if(!empty($pdm->EmailChatTranscript))
                $etpl = $pdm->EmailChatTranscript;
        }

        $etpl = str_replace("%external_ip%",getIP(),$etpl);
        $etpl = str_replace("%chat_id%",$this->ChatId,$etpl);
        $etpl = str_replace("%website_name%",$CONFIG["gl_site_name"],$etpl);
        $etpl = str_replace("%details%",$custom,$etpl);
        $etpl = str_replace("%group_description%",$GROUPS[$this->DesiredChatGroup]->Description,$etpl);
        $etpl = str_replace(array("%group_name%","%group_id%"),$this->DesiredChatGroup,$etpl);

        if($this->InternalUser)
        {
            $etpl = str_replace("%operator_name%",$this->InternalUser->Fullname,$etpl);
            $etpl = str_replace("%operator_email%",$this->InternalUser->Email,$etpl);
        }

        $etpl = str_replace("%external_name%","%efullname%",$etpl);
        $etpl = str_replace("%external_email%","%eemail%",$etpl);
        $etpl = str_replace("%external_company%",$this->Company,$etpl);
        $etpl = str_replace("%external_phone%",$this->Phone,$etpl);
        $etpl = str_replace("%question%",$this->Question,$etpl);

        $subject = ($pdm != null) ? $pdm->SubjectChatTranscript : "";
        $subject = getSubject($subject,$this->Email,$this->Fullname,$this->DesiredChatGroup,$this->ChatId,$this->Company,$this->Phone,getIP(),$this->Question,$this->Customs);
        $internal = ($_internalUser != null && $_internalUser->IsBot) ? $_internalUser->SystemId : "";

        $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."';");
        if($result && @mysql_num_rows($result) == 0)
        {
            if($CONFIG["gl_adct"] == 1 || (!empty($CONFIG["gl_rm_chats_time"]) || empty($CONFIG["gl_rm_chats"])))
                queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` (`time`,`endtime`,`closed`,`chat_id`,`external_id`,`fullname`,`internal_id`,`group_id`,`area_code`,`html`,`plaintext`,`transcript_text`,`email`,`company`,`iso_language`,`iso_country`,`host`,`ip`,`gzip`,`transcript_sent`,`transcript_receiver`,`question`,`customs`,`subject`,`voucher_id`) VALUES ('".@mysql_real_escape_string($this->FirstActive)."',0,0,'".@mysql_real_escape_string($this->ChatId)."','".@mysql_real_escape_string($this->ExternalUser->UserId)."','','".@mysql_real_escape_string($internal)."','','".@mysql_real_escape_string($this->Code)."','','','".@mysql_real_escape_string($etpl)."','','','".@mysql_real_escape_string($_visitor->Language)."','".@mysql_real_escape_string($_visitor->GeoCountryISO2)."','".@mysql_real_escape_string($_visitor->Host)."','".@mysql_real_escape_string($_visitor->IP)."',0,0,'".@mysql_real_escape_string($this->ExternalUser->Email)."','','".@mysql_real_escape_string(@serialize($customs))."','".@mysql_real_escape_string($subject)."','".@mysql_real_escape_string($this->ChatVoucherId)."');");
            $this->ArchiveCreated = true;
            ChatRequest::AcceptAll($this->UserId);
        }
    }

	function RepostChatHistory($_caller,$_chatId,$_internalSystemId,$_from=0,$_last=0,$_receiverGroup="",$_targetChatId="",$_targetReceiverGroup="",$_external=false,$_botonly=false,$_externalSelf=true)
	{
		global $INTERNAL;
		
		if(empty($_receiverGroup))
			$_receiverGroup = $this->SystemId;
			
		if(!empty($INTERNAL[$_internalSystemId]->Reposts[$this->SystemId]))
			$_from = $INTERNAL[$_internalSystemId]->Reposts[$this->SystemId];
			
		if(empty($_targetChatId))
			$cidcrit = (!empty($_chatId)) ? " `chat_id` != '".@mysql_real_escape_string($_chatId)."' AND" : "";
		else
			$cidcrit = (!empty($_chatId)) ? " `chat_id` = '".@mysql_real_escape_string($_chatId)."' AND" : "";
			
		$reccrit = ($_external) ? "" : " AND `received`=1";
		$result = queryDB(true,$d = "SELECT * FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE".$cidcrit." `repost`=0".$reccrit." AND `receiver_group`='".@mysql_real_escape_string($_receiverGroup)."' AND `time`>".$_from." GROUP BY `id`;");

		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if($_botonly)
					if(!(isset($INTERNAL[$row["receiver_original"]]) && $INTERNAL[$row["receiver_original"]]->IsBot) && !(isset($INTERNAL[$row["sender"]]) && $INTERNAL[$row["sender"]]->IsBot))
						continue;

				$post = new Post(getId(32),$row["sender"],$_internalSystemId,$row["text"],$row["time"],(empty($_targetChatId) ? $row["chat_id"] : $_targetChatId),$row["sender_name"]);
				$post->Translation = $row["translation"];
				$post->ReceiverOriginal = $row["receiver_original"];
				$post->TranslationISO = $row["translation_iso"];
				$post->ReceiverGroup = (empty($_targetReceiverGroup)) ? $row["receiver_group"] : $_targetReceiverGroup;
				$post->Repost = true;
				$post->Save(array(0=>$row["micro"],1=>$row["time"]));
				$_last = max($_last,$row["time"]);
				
				if($_external && $_externalSelf)
				{
					$post->Id = getId(32);
					$post->Receiver = $_targetReceiverGroup;
					$post->Save(array(0=>$row["micro"],1=>$row["time"]));
				}
			}
		$INTERNAL[$_internalSystemId]->Reposts[$this->SystemId] = max($_last,$_from);
	}
	
	function PostsReceived($_sender)
	{
		$result = queryDB(true,"SELECT COUNT(*) as `pcount`,SUM(received) as `rcount` FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `sender`='".@mysql_real_escape_string($_sender)."' AND `receiver`='".@mysql_real_escape_string($this->SystemId)."' AND `repost`=0");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				return $row["pcount"]+$row["rcount"];
		return true;
	}
	
	function GetLastInvitationSender()
	{
		$result = queryDB(true,"SELECT `sender_system_id` FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `created` DESC LIMIT 1");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				return $row["sender_system_id"];
		return null;
	}
	
	function CloseChat($_reason=0)
	{
		$this->ExternalClose();
		$this->Closed=true;
	}
	
	function CloseWindow()
	{
		$this->ExternalClose();
		$this->Destroy();
	}
	
	function Save()
	{
		global $CONFIG;
		$_new = (func_num_args() > 0) ? func_get_arg(0) : false;
		if($_new)
		{
			$this->FirstCall = true;
			$this->Status = CHAT_STATUS_OPEN;
		}
		
		if(empty($this->FirstActive))
			$this->FirstActive = time();

		if($this->FirstCall)
			queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_CHATS."` (`visitor_id` ,`browser_id` ,`visit_id` ,`priority`,`fullname` ,`email` ,`company` , `phone`, `call_me_back`, `typing` ,`area_code` ,`first_active` ,`last_active` ,`request_operator` ,`request_group` ,`question` ,`customs`, `chat_ticket_id`, `queue_posts`) VALUES ('".@mysql_real_escape_string($this->UserId)."','".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->VisitId)."','".@mysql_real_escape_string($this->Priority)."','".@mysql_real_escape_string($this->Fullname)."','".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string($this->Company)."','".@mysql_real_escape_string($this->Phone)."',".@mysql_real_escape_string(($this->CallMeBack) ? 1 : 0).",0,'".@mysql_real_escape_string($this->Code)."','".@mysql_real_escape_string($this->FirstActive)."','".@mysql_real_escape_string($this->LastActive)."','".@mysql_real_escape_string($this->DesiredChatPartner)."','".@mysql_real_escape_string($this->DesiredChatGroup)."','".@mysql_real_escape_string($this->Question)."','".@mysql_real_escape_string(serialize($this->Customs))."','".@mysql_real_escape_string($this->ChatVoucherId)."','".@mysql_real_escape_string(serialize($this->QueuedPosts))."');");
        else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `typing`='".@mysql_real_escape_string(($this->Typing)?1:0)."',`queue_message_shown`='".@mysql_real_escape_string(($this->QueueMessageShown)?1:0)."',`archive_created`='".@mysql_real_escape_string(($this->ArchiveCreated)?1:0)."',`customs`='".@mysql_real_escape_string(serialize($this->Customs))."',`queue_posts`='".@mysql_real_escape_string(serialize($this->QueuedPosts))."',`request_operator`='".@mysql_real_escape_string($this->DesiredChatPartner)."',`chat_ticket_id`='".@mysql_real_escape_string($this->ChatVoucherId)."',`request_group`='".@mysql_real_escape_string($this->DesiredChatGroup)."',`last_active`='".@mysql_real_escape_string(time())."',`email`='".@mysql_real_escape_string($this->Email)."' WHERE `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");

        parent::Save();
		if(count($this->History) == 0)
		{
			$this->History[0] = new HistoryUrl(LIVEZILLA_URL . FILE_CHAT,$this->Code,$CONFIG["gl_site_name"],"",$this->FirstActive);
			$this->History[0]->Save($this->BrowserId,true);
		}
	}
	
	function SaveLoginData()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `call_me_back`='".@mysql_real_escape_string(($this->CallMeBack)?1:0)."',`customs`='".@mysql_real_escape_string(serialize($this->Customs))."',`company`='".@mysql_real_escape_string($this->Company)."',`phone`='".@mysql_real_escape_string($this->Phone)."',`question`='".@mysql_real_escape_string($this->Question)."',`email`='".@mysql_real_escape_string($this->Email)."',`fullname`='".@mysql_real_escape_string($this->Fullname)."',`request_operator`='".@mysql_real_escape_string($this->DesiredChatPartner)."',`last_active`='".@mysql_real_escape_string(time())."',`request_group`='".@mysql_real_escape_string($this->DesiredChatGroup)."' WHERE `browser_id`='".@mysql_real_escape_string($this->BrowserId)."' AND `visitor_id`='".@mysql_real_escape_string($this->UserId)."' AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
		parent::SaveLoginData();
	}
	
	function Destroy()
	{
		parent::Destroy();
	}
	
	function InternalDecline($_internal,$remopcount=0)
	{
		if(!isset($this->Members[$_internal]))
			$this->TakeChat($_internal,"");
			
		foreach($this->Members as $member)
			if(empty($member->Left))
				$remopcount++;

        if($this->Activated)
            return;

		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `declined`=1,`dtime`=".time().",`ltime`=".time()." WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string($_internal)."' LIMIT 1;");
		if($remopcount==1 || !isset($this->Members[$_internal]))
			$this->UpdateUserStatus(false,false,true,false,false);
		else if(count($this->Members)>1 && isset($this->Members[$_internal]) && $this->Members[$_internal]->Status==0)
			foreach($this->Members as $sysid => $member)
				if($_internal != $sysid)
				{
					$this->SetHost($sysid);
					break;
				}
	}
	
	function InternalClose($_internal)
	{
		$this->UpdateUserStatus(false,true,false,false,false);
	}
	
	function InternalActivate()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `time`='".@mysql_real_escape_string(time())."' WHERE `closed`=0 AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
		$this->UpdateUserStatus(true,false,false,false,false);
	}
	
	function ExternalActivate()
	{
		$this->UpdateUserStatus(false,false,false,true,false);
	}
		
	function ExternalClose()
	{
		$this->UpdateUserStatus(false,false,false,false,true);
	}
	
	function UpdateUserStatus($_internalActivated,$_internalClosed,$_internalDeclined,$_externalActivated,$_externalClose)
	{
		if(!empty($this->ChatId))
		{
			$this->Status = ($_externalClose || $_internalDeclined || $_internalClosed) ? CHAT_CLOSED : $this->Status;
			if($_internalActivated)
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET `internal_active`='1',`allocated`='".@mysql_real_escape_string(time())."' WHERE `internal_active`=0 AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
				if(@mysql_affected_rows() == 1)
				{
					queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `status`=0 WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' AND `user_id`='".@mysql_real_escape_string(CALLER_SYSTEM_ID)."';");
					queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHAT_OPERATORS."` SET `status`=9,`ltime`=".time().",`jtime`=0 WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' AND `user_id`!='".@mysql_real_escape_string(CALLER_SYSTEM_ID)."' AND `status`<=1;");
				}
			}
			else
			{
				if($_externalClose && empty($this->InternalClosed))
					$update = "`external_close`='1',`exit`='".@mysql_real_escape_string(time()+1)."'";
				else if($_externalClose && !empty($this->InternalClosed))
					$update = "`external_close`='1'";
				else if($_internalClosed && empty($this->InternalClosed))
					$update = "`internal_closed`='1',`exit`='".@mysql_real_escape_string(time()+1)."'";
				else if($_internalDeclined && empty($this->InternalDeclined))
					$update = "`internal_declined`='1'";
				else
					$update = "`external_active`='1'";
					
				if(($_internalClosed || $_externalClose) && !empty($this->AllocatedTime))
				{
					queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_GROUP_MEMBERS."` WHERE `user_id`='".@mysql_real_escape_string($this->SystemId)."';");
					$params = $this->CalculateChatResponseTime();
					$update .= ",`response_time`=" . $params[0] . ",`chat_posts`=" . $params[1];
				}
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_CHATS."` SET ".$update." WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
			}
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` SET `endtime`=".$this->LastActive.((!empty($this->AllocatedTime)) ? (",`time`=" . $this->AllocatedTime) : "")." WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' LIMIT 1;");
		}
		
		if(!empty($this->ChatVoucherId))
		{
			$ticket = new CommercialChatVoucher(null,$this->ChatVoucherId);
			$ticket->UpdateVoucherChatTime(0);
		}
	}
	
	function CalculateChatResponseTime($start=0,$postcount=0)
	{
		$durations = array();
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `chat_id`='".@mysql_real_escape_string($this->ChatId)."' ORDER BY `time` ASC;");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(empty($start) && strpos($row["sender"],"~") !== false)
					$start = max($this->AllocatedTime,$row["time"]);
				else if(strpos($row["sender"],"~") === false)
				{
					$postcount++;
					if(!empty($start))
					{
						$durations[] = $row["time"]-$start;
						$start = 0;
					}
				}
			}
		if(count($durations) > 0)
			return array(0=>floor(array_sum($durations) / count($durations)),1=>$postcount);
		else
			return array(0=>0,1=>$postcount);
	}
}
?>