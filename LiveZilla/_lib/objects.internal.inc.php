<?php
/****************************************************************************************
* LiveZilla objects.internal.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	


class InternalXMLBuilder
{
	public $Caller;
	public $InternalUsers;
	public $InternalGroups;

	public $XMLProfilePictures = "";
	public $XMLWebcamPictures = "";
	public $XMLProfiles = "";
	public $XMLInternal = "";
	public $XMLTyping = "";
	public $XMLGroups = "";
	
	function InternalXMLBuilder($_caller,$_internalusers,$_internalgroups)
	{
		$this->Caller = $_caller;
		$this->InternalUsers = $_internalusers;
		$this->InternalGroups = $_internalgroups;
	}
	
	function Generate()
	{
		foreach($this->InternalGroups as $groupId => $group)
		{
			if(!SERVERSETUP && in_array($groupId,$this->InternalUsers[CALLER_SYSTEM_ID]->GroupsHidden))
				continue;
			
			$this->XMLGroups .= $group->GetXML();

			if(SERVERSETUP && !$group->IsDynamic)
			{
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_sm")."\">".base64_encode($group->ChatFunctions[0])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_so")."\">".base64_encode($group->ChatFunctions[1])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_pr")."\">".base64_encode($group->ChatFunctions[2])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_ra")."\">".base64_encode($group->ChatFunctions[3])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_fv")."\">".base64_encode($group->ChatFunctions[4])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_fu")."\">".base64_encode($group->ChatFunctions[5])."</f>\r\n";
				
				$this->XMLGroups .= "<f key=\"".base64_encode("ci_hidden")."\">\r\n";
				foreach($group->ChatInputsHidden as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</f>\r\n";
				
				$this->XMLGroups .= "<f key=\"".base64_encode("ti_hidden")."\">\r\n";
				foreach($group->TicketInputsHidden as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</f>\r\n";
				
				$this->XMLGroups .= "<f key=\"".base64_encode("ci_mandatory")."\">\r\n";
				foreach($group->ChatInputsMandatory as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</f>\r\n";
				
				$this->XMLGroups .= "<f key=\"".base64_encode("ti_mandatory")."\">\r\n";
				foreach($group->TicketInputsMandatory as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</f>\r\n";
			}
			$this->XMLGroups .= "</v>\r\n";
		}
		foreach($this->InternalUsers as $sysId => $internaluser)
		{
			$b64sysId = base64_encode($sysId);
			$sessiontime = $this->Caller->LastActive;
			$this->InternalUsers[$sysId]->LoadPictures();
			
			if($sysId != CALLER_SYSTEM_ID && !empty($this->InternalUsers[$sysId]->WebcamPicture))
			{	
				if($_POST["p_int_pp"] == XML_CLIP_NULL || $this->InternalUsers[$sysId]->WebcamPictureTime >= $sessiontime)
					$this->XMLWebcamPictures .= "<v os=\"".$b64sysId."\" content=\"".$this->InternalUsers[$sysId]->WebcamPicture."\" />\r\n";
			}
			else
				$this->XMLWebcamPictures .= "<v os=\"".$b64sysId."\" content=\"".base64_encode("")."\" />\r\n";

            $DEAC = ($this->InternalUsers[$sysId]->Deactivated) ? " deac=\"".base64_encode(1)."\"" : "";
            $CPONL = ($this->InternalUsers[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN) ? " cponl=\"".base64_encode(($internaluser->PasswordChangeRequest) ? 1 : 0)."\"" : "";
			$PASSWORD = (SERVERSETUP) ? " pass=\"".base64_encode($this->InternalUsers[$sysId]->Password)."\"" : "";
			$WSCONFIG = $this->InternalUsers[$sysId]->WebsitesConfig;array_walk($WSCONFIG,"b64ecode");
			$WSUSERS = $this->InternalUsers[$sysId]->WebsitesUsers;array_walk($WSUSERS,"b64ecode");
			$botatts = ($this->InternalUsers[$sysId]->IsBot) ? " isbot=\"".base64_encode($this->InternalUsers[$sysId]->IsBot ? "1" : "0")."\" wm=\"".base64_encode($this->InternalUsers[$sysId]->WelcomeManager ? "1" : "0")."\" wmohca=\"".base64_encode($this->InternalUsers[$sysId]->WelcomeManagerOfferHumanChatAfter)."\"" : "";
			
			$this->XMLInternal .= "<v status=\"".base64_encode($this->InternalUsers[$sysId]->Status)."\" id=\"".$b64sysId."\" userid=\"".base64_encode($this->InternalUsers[$sysId]->UserId)."\"".$botatts." lang=\"".base64_encode($this->InternalUsers[$sysId]->Language)."\" email=\"".base64_encode($this->InternalUsers[$sysId]->Email)."\" websp=\"".base64_encode($this->InternalUsers[$sysId]->Webspace)."\" name=\"".base64_encode($this->InternalUsers[$sysId]->Fullname)."\" desc=\"".base64_encode($this->InternalUsers[$sysId]->Description)."\" perms=\"".base64_encode($this->InternalUsers[$sysId]->PermissionSet)."\" ip=\"".base64_encode($this->InternalUsers[$sysId]->IP)."\" lipr=\"".base64_encode($this->InternalUsers[$sysId]->LoginIPRange)."\" aac=\"".base64_encode($this->InternalUsers[$sysId]->CanAutoAcceptChats)."\" ws_users=\"".base64_encode(base64_encode(serialize($WSUSERS)))."\" ws_config=\"".base64_encode(base64_encode(serialize($WSCONFIG)))."\" level=\"".base64_encode($this->InternalUsers[$sysId]->Level)."\" ".$DEAC." ".$CPONL." ".$PASSWORD.">\r\n";
			
			if(!empty($this->InternalUsers[$sysId]->ProfilePicture))
				$this->XMLInternal .= "<pp>".$this->InternalUsers[$sysId]->ProfilePicture."</pp>\r\n";
			
			foreach($this->InternalUsers[$sysId]->Groups as $groupid)
				$this->XMLInternal .= "<gr>".base64_encode($groupid)."</gr>\r\n";
			
			foreach($this->InternalUsers[$sysId]->GroupsHidden as $groupid)
				$this->XMLInternal .= "<gh>".base64_encode($groupid)."</gh>\r\n";

			foreach($this->InternalGroups as $groupid => $group)
				if($group->IsDynamic)
					foreach($group->Members as $member)
						if($member == $sysId)
							$this->XMLInternal .= "<gr>".base64_encode($groupid)."</gr>\r\n";
			
			if(!empty($this->InternalUsers[$sysId]->GroupsAway))
				foreach($this->InternalUsers[$sysId]->GroupsAway as $groupid)
					$this->XMLInternal .= "<ga>".base64_encode($groupid)."</ga>\r\n";
			
			foreach($internaluser->PredefinedMessages as $premes)
				$this->XMLInternal .= $premes->GetXML();

            foreach($internaluser->Signatures as $sig)
                $this->XMLInternal .= $sig->GetXML();

            if($this->InternalUsers[$sysId]->ClientMobile)
                $this->XMLInternal .= "<cm />\r\n";

            if($this->InternalUsers[$sysId]->ClientWeb)
                $this->XMLInternal .= "<cw />\r\n";

			if($internaluser->IsBot && !(SERVERSETUP || $this->InternalUsers[CALLER_SYSTEM_ID]->GetPermission(20) == PERMISSION_NONE || (!$this->InternalUsers[CALLER_SYSTEM_ID]->IsInGroupWith($internaluser) && $this->InternalUsers[CALLER_SYSTEM_ID]->GetPermission(20) != PERMISSION_FULL)))
			{	
				$internaluser->LoadBotFeeds();
				foreach($internaluser->BotFeeds as $feed)
					$this->XMLInternal .= $feed->GetXML();
			}

			$this->XMLInternal .= "</v>\r\n";
			
			if($sysId!=$this->Caller->SystemId && $this->InternalUsers[$sysId]->Status != USER_STATUS_OFFLINE)
				$this->XMLTyping .= "<v id=\"".$b64sysId."\" tp=\"".base64_encode((($this->InternalUsers[$sysId]->Typing==CALLER_SYSTEM_ID)?1:0))."\" />\r\n";
			
			$internaluser->LoadProfile();
			if($internaluser->Profile != null)
				if((isset($_POST["p_int_v"]) && $_POST["p_int_v"] == XML_CLIP_NULL) || $internaluser->Profile->LastEdited >= $sessiontime)
					$this->XMLProfiles .= $internaluser->Profile->GetXML($internaluser->SystemId);
				else
					$this->XMLProfiles .= "<p os=\"".$b64sysId."\"/>\r\n";
		}
	}
}

class ExternalXMLBuilder
{
	public $CurrentStatics = array();
	public $ActiveBrowsers = array();
	public $AddedVisitors = array();
	
	public $SessionFileSizes = array();
	public $StaticReload = array();
	public $DiscardedObjects = array();
	public $IsDiscardedObject = false;
	public $ObjectCounter = 0;
	public $CurrentUser;
	public $CurrentFilesize;
	public $CurrentResponseType = DATA_RESPONSE_TYPE_KEEP_ALIVE;
	
	public $XMLVisitorOpen = false;
	public $XMLCurrentChat = "";
	public $XMLCurrentAliveBrowsers = "";
	public $XMLCurrentVisitor = "";
	public $XMLCurrentVisitorTag = "";
	public $XMLCurrent = "";
	public $XMLTyping = "";
	
	public $Caller;
	public $ExternUsers;
	public $GetAll;
	public $IsExternal;

	function ExternalXMLBuilder($_caller,$_visitors,$_getall,$_external)
	{
		$this->Caller = $_caller;
		$this->Visitors = $_visitors;
		$this->GetAll = $_getall;
		$this->IsExternal = $_external;
	}
	
	function SetDiscardedObject($_base)
	{
		global $CONFIG,$INTERNAL;
		$this->DiscardedObjects = $_base;
		if(!empty($this->SessionFileSizes))
			foreach($this->SessionFileSizes as $sfs_userid => $sfs_browsers)
				if(isset($this->Visitors[$sfs_userid]))
				{
					$filtered = ($this->Visitors[$sfs_userid]->IsInChatWith($INTERNAL[CALLER_SYSTEM_ID])) ? false : $INTERNAL[CALLER_SYSTEM_ID]->IsVisitorFiltered($this->Visitors[$sfs_userid]);
					foreach($sfs_browsers as $sfs_bid => $sfs_browser)
					{
						if(!isset($this->Visitors[$sfs_userid]->Browsers[$sfs_bid]) || $filtered)
						{
							if(!isset($this->DiscardedObjects[$sfs_userid]))
								$this->DiscardedObjects[$sfs_userid] = array($sfs_bid=>$sfs_bid);
							else if($this->DiscardedObjects[$sfs_userid] != null)
								$this->DiscardedObjects[$sfs_userid][$sfs_bid] = $sfs_bid;
						}
					}
				}
				else
				{
					$this->DiscardedObjects[$sfs_userid] = null;
				}
			
		if(LOGIN && is_array($this->Visitors))
		{
			foreach($this->Visitors as $uid => $visitor)
				foreach($visitor->Browsers as $bid => $browser)
					if($browser->LastActive < (time()-$CONFIG["timeout_track"]))
					{
						if(!isset($this->DiscardedObjects[$uid]))
							$this->DiscardedObjects[$uid] = array($bid=>$bid);
						else if($this->DiscardedObjects[$uid] != null)
							$this->DiscardedObjects[$uid][$bid] = $bid;
					}
		}
	}
	
	function Generate()
	{
		global $BROWSER,$USER,$CONFIG,$INTERNAL,$GROUPS;
		if(is_array($this->Visitors))
			foreach($this->Visitors as $userid => $USER)
			{
				$icw = $USER->IsInChatWith($INTERNAL[CALLER_SYSTEM_ID]);
				if(!$icw && empty($INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes[$userid]))
				{
					if($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_MONITORING) == PERMISSION_RELATED)
						continue;
					if($INTERNAL[CALLER_SYSTEM_ID]->IsVisitorFiltered($USER))
						continue;
				}

				if($icw || !(!empty($CONFIG["gl_hvjd"]) && empty($USER->Javascript)))
				{
					$isactivebrowser = false;
					$this->XMLCurrentAliveBrowsers = 
					$this->XMLCurrentVisitor = "";
					$this->GetStaticInfo();
					$this->CurrentResponseType = ($USER->StaticInformation) ? DATA_RESPONSE_TYPE_STATIC : DATA_RESPONSE_TYPE_KEEP_ALIVE;
			
					foreach($USER->Browsers as $browserId => $BROWSER)
					{
						$this->ObjectCounter++;
						array_push($this->ActiveBrowsers,$BROWSER->BrowserId);
						$this->CurrentFilesize = $BROWSER->LastUpdate;
						$this->XMLCurrentChat = null;
						
						if($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_CHATS) != PERMISSION_FULL)
							foreach($GROUPS as $gid => $group)
								if(!empty($group->Members[CALLER_SYSTEM_ID]) && !empty($group->Members[$BROWSER->SystemId]))
									$iproom = true;
						
						if($BROWSER->Type == BROWSER_TYPE_CHAT && (!empty($iproom) || ($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_CHATS) == PERMISSION_FULL || ($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_CHATS) == PERMISSION_RELATED && in_array($BROWSER->DesiredChatGroup,$INTERNAL[CALLER_SYSTEM_ID]->Groups)) || ($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_CHATS) == PERMISSION_NONE && !empty($BROWSER->Members[CALLER_SYSTEM_ID])))))
						{
							$isactivebrowser = true;
							$this->BuildChatXML();
							$this->SessionFileSizes[$userid][$browserId] = $this->CurrentFilesize;
						}
						else if(!isset($this->SessionFileSizes[$userid]) || !empty($BROWSER->ChatRequest) || $this->CurrentResponseType == DATA_RESPONSE_TYPE_STATIC || (isset($this->SessionFileSizes[$userid]) && (!isset($this->SessionFileSizes[$userid][$browserId]) || (isset($this->SessionFileSizes[$userid][$browserId]) && $this->SessionFileSizes[$userid][$browserId] != $this->CurrentFilesize))))
						{
							$BROWSER->LoadChatRequest();
							$isactivebrowser = true;
							if($this->CurrentResponseType == DATA_RESPONSE_TYPE_KEEP_ALIVE)
								$this->CurrentResponseType = DATA_RESPONSE_TYPE_BASIC;
							$this->SessionFileSizes[$userid][$browserId] = $this->CurrentFilesize;
						}
						else
						{
							$this->CurrentResponseType = DATA_RESPONSE_TYPE_KEEP_ALIVE;
						}
						$this->BuildVisitorXML();
						$USER->Browsers[$browserId] = $BROWSER;
					}
					$this->XMLCurrentVisitor .= $this->XMLCurrentAliveBrowsers;
					if($this->XMLVisitorOpen)
					{
						if($this->IsDiscardedObject || $isactivebrowser)
							$this->XMLCurrent .= $this->XMLCurrentVisitorTag . $this->XMLCurrentVisitor . "</v>\r\n";
						$this->XMLVisitorOpen = false;
					}
				}
			}
		$this->RemoveFileSizes($this->ActiveBrowsers);
	}
	
	function BuildVisitorXML()
	{
		global $USER,$BROWSER,$INPUTS,$INTERNAL,$CONFIG;
		initData(false,false,false,false,false,false,false,true);
		$visitorDetails = Array("userid" => " id=\"".base64_encode($USER->UserId)."\"","resolution" => null,"ip" => null,"lat" => null,"long" => null,"city" => null,"ctryi2" => null,"region" => null,"system" => null,"language" => null,"ka" => null,"requested" => null,"target" => null,"declined" => null,"accepted" => null,"cname" => null,"cemail" => null,"ccompany" => null,"waiting" => null,"timezoneoffset" => null,"visits" => null,"host"=>null,"grid"=>null,"isp"=>null,"cf0"=>null,"cf1"=>null,"cf2"=>null,"cf3"=>null,"cf4"=>null,"cf5"=>null,"cf6"=>null,"cf7"=>null,"cf8"=>null,"cf9"=>null,"sys"=>null,"bro"=>null,"js"=>null,"visitlast"=>null);
		if($this->CurrentResponseType != DATA_RESPONSE_TYPE_KEEP_ALIVE)
		{
			$visitorDetails["ka"] = " ka=\"".base64_encode(true)."\"";
			
			if(empty($USER->ActiveChatRequest) || $BROWSER->LastActive>(time()-$CONFIG["timeout_track"]))
				$USER->ActiveChatRequest = $BROWSER->ChatRequest;
			
			$visitorDetails["requested"] = (!empty($USER->ActiveChatRequest) && !$USER->ActiveChatRequest->Accepted && !$USER->ActiveChatRequest->Declined && !$USER->ActiveChatRequest->Closed) ? " req=\"".base64_encode($USER->ActiveChatRequest->SenderUserId)."\"" : "";
			$visitorDetails["declined"] = (!empty($USER->ActiveChatRequest) && $USER->ActiveChatRequest->Declined) ? " dec=\"".base64_encode("1")."\"" : "";
			$visitorDetails["accepted"] = (!empty($USER->ActiveChatRequest) && $USER->ActiveChatRequest->Accepted) ? " acc=\"".base64_encode("1")."\"" : "";
			$visitorDetails["target"] = (!empty($USER->ActiveChatRequest)) ? " tbid=\"".base64_encode($BROWSER->BrowserId)."\"" : "";
		}
		if($this->CurrentResponseType == DATA_RESPONSE_TYPE_STATIC)
		{
			$visitorDetails["resolution"] = " res=\"".base64_encode($USER->Resolution)."\"";
			$visitorDetails["ip"] = " ip=\"".base64_encode($USER->IP)."\"";
			$visitorDetails["timezoneoffset"] = " tzo=\"".base64_encode($USER->GeoTimezoneOffset)."\"";
			$visitorDetails["lat"] = " lat=\"".base64_encode($USER->GeoLatitude)."\"";
			$visitorDetails["long"] = " long=\"".base64_encode($USER->GeoLongitude)."\"";
			$visitorDetails["city"] = " city=\"".base64_encode($USER->GeoCity)."\"";
			$visitorDetails["ctryi2"] = " ctryi2=\"".base64_encode($USER->GeoCountryISO2)."\"";
			$visitorDetails["region"] = " region=\"".base64_encode($USER->GeoRegion)."\"";
			$visitorDetails["js"] = " js=\"".base64_encode($USER->Javascript)."\"";
			$visitorDetails["language"] = " lang=\"".base64_encode($USER->Language)."\"";
			$visitorDetails["visits"] = " vts=\"".base64_encode($USER->Visits)."\"";
			$visitorDetails["host"] = " ho=\"".base64_encode($USER->Host)."\"";
			$visitorDetails["grid"] = " gr=\"".base64_encode($USER->GeoResultId)."\"";
			$visitorDetails["isp"] = " isp=\"".base64_encode($USER->GeoISP)."\"";
			$visitorDetails["sys"] = " sys=\"".base64_encode($USER->OperatingSystem)."\"";
			$visitorDetails["bro"] = " bro=\"".base64_encode($USER->Browser)."\"";
			$visitorDetails["visitlast"] = " vl=\"".base64_encode($USER->VisitLast)."\"";
			
		}
		
		if(!empty($BROWSER->DesiredChatPartner) && !empty($INTERNAL[$BROWSER->DesiredChatPartner]) && !in_array($BROWSER->DesiredChatGroup,$INTERNAL[$BROWSER->DesiredChatPartner]->Groups))
			$BROWSER->DesiredChatPartner = "";

		$visitorDetails["waiting"] = ($BROWSER->Type == BROWSER_TYPE_CHAT && $BROWSER->Waiting && in_array($BROWSER->DesiredChatGroup,$INTERNAL[CALLER_SYSTEM_ID]->Groups) && (empty($BROWSER->DesiredChatPartner) || $BROWSER->DesiredChatPartner == CALLER_SYSTEM_ID)) ? " w=\"".base64_encode(1)."\"" : "";
		if(!in_array($USER->UserId,$this->AddedVisitors) || (!empty($BROWSER->ChatRequest) && $BROWSER->ChatRequest == $USER->ActiveChatRequest))
		{
			array_push($this->AddedVisitors, $USER->UserId);
			$this->XMLVisitorOpen = true;
			$this->XMLCurrentVisitorTag =  "<v".$visitorDetails["userid"].$visitorDetails["resolution"].$visitorDetails["ip"].$visitorDetails["lat"].$visitorDetails["long"].$visitorDetails["region"].$visitorDetails["city"].$visitorDetails["ctryi2"].$visitorDetails["visits"].$visitorDetails["declined"].$visitorDetails["accepted"].$visitorDetails["target"].$visitorDetails["system"].$visitorDetails["language"].$visitorDetails["requested"].$visitorDetails["cname"].$visitorDetails["cemail"].$visitorDetails["ccompany"].$visitorDetails["timezoneoffset"].$visitorDetails["host"].$visitorDetails["grid"].$visitorDetails["isp"].$visitorDetails["cf0"].$visitorDetails["cf1"].$visitorDetails["cf2"].$visitorDetails["cf3"].$visitorDetails["cf4"].$visitorDetails["cf5"].$visitorDetails["cf6"].$visitorDetails["cf7"].$visitorDetails["cf8"].$visitorDetails["cf9"].$visitorDetails["sys"].$visitorDetails["bro"].$visitorDetails["js"].$visitorDetails["visitlast"].">\r\n";
		}

		if($BROWSER->Overlay && empty($this->XMLCurrentChat))
			$BROWSER->History = null;
			
		if($this->CurrentResponseType != DATA_RESPONSE_TYPE_KEEP_ALIVE && count($BROWSER->History)>0)
		{
			$referrer = ($BROWSER->History[0]->Referrer != null) ? " ref=\"".base64_encode($BROWSER->History[0]->Referrer->GetAbsoluteUrl())."\"" : "";
			$sstring = (!$BROWSER->Overlay) ? " ss=\"".base64_encode($BROWSER->Query)."\"" : "";
			$personal = " cname=\"".base64_encode($BROWSER->Fullname)."\"";
			$personal .= " cemail=\"".base64_encode($BROWSER->Email)."\"";
			$personal .= " ccompany=\"".base64_encode($BROWSER->Company)."\"";
			$personal .= " cphone=\"".base64_encode($BROWSER->Phone)."\"";
			
			if(is_array($BROWSER->Customs))
				foreach($BROWSER->Customs as $index => $value)
					if($INPUTS[$index]->Active && $INPUTS[$index]->Custom)
						$personal .= " cf".$index."=\"".base64_encode($INPUTS[$index]->GetClientValue($BROWSER->Customs[$index]))."\"";

			$this->XMLCurrentVisitor .=  " <b id=\"".base64_encode($BROWSER->BrowserId)."\" ol=\"".base64_encode($BROWSER->Overlay?1:0)."\" olc=\"".base64_encode($BROWSER->OverlayContainer?1:0)."\"".$sstring.$visitorDetails["ka"].$referrer.$visitorDetails["waiting"].$personal.">\r\n";
				if(!$BROWSER->Overlay)
					for($i = 0;$i < count($BROWSER->History);$i++)
						$this->XMLCurrentVisitor .=  "  <h time=\"".base64_encode($BROWSER->History[$i]->Entrance)."\" url=\"".base64_encode($BROWSER->History[$i]->Url->GetAbsoluteUrl())."\" title=\"".base64_encode(@$BROWSER->History[$i]->Url->PageTitle)."\" code=\"".base64_encode( ($BROWSER->Type == BROWSER_TYPE_CHAT) ? $BROWSER->Code : $BROWSER->History[$i]->Url->AreaCode )."\" cp=\"".base64_encode($BROWSER->Type)."\" />\r\n";
			if(!empty($this->XMLCurrentChat))
				$this->XMLCurrentVisitor .= $this->XMLCurrentChat;
			$this->XMLCurrentVisitor .=  " </b>\r\n";
		}
	}
	
	function BuildChatXML()
	{
		global $USER,$BROWSER,$GROUPS,$INPUTS;
		initData(false,false,false,false,false,false,false,true);
		if($this->CurrentResponseType == DATA_RESPONSE_TYPE_KEEP_ALIVE)
			$this->CurrentResponseType = DATA_RESPONSE_TYPE_BASIC;
		if($this->GetAll)
			$this->CurrentResponseType = DATA_RESPONSE_TYPE_STATIC;

		if(!$BROWSER->Closed && ($BROWSER->Status > CHAT_STATUS_OPEN || $BROWSER->Waiting))
		{
			if(!empty($BROWSER->DesiredChatGroup))
			{
				$pra = (!empty($BROWSER->Members[CALLER_SYSTEM_ID])) ? " pra=\"".base64_encode($BROWSER->PostsReceived(CALLER_SYSTEM_ID))."\"" : "";
				$cti = "";

				$USER->IsChat = true;
				$this->XMLCurrentChat =  "  <chat id=\"".base64_encode($BROWSER->ChatId)."\" d=\"".base64_encode(!empty($BROWSER->Declined) ? 1 : 0)."\" p=\"".base64_encode($BROWSER->Priority)."\" f=\"".base64_encode($BROWSER->FirstActive)."\" q=\"".base64_encode(($BROWSER->Status > CHAT_STATUS_OPEN) ? "0" : "1")."\" cmb=\"".base64_encode($BROWSER->CallMeBack)."\" st=\"".base64_encode($BROWSER->Activated)."\" fn=\"" . base64_encode($BROWSER->Fullname) . "\" em=\"" . base64_encode($BROWSER->Email) . "\" eq=\"" . base64_encode($BROWSER->Question) . "\" gr=\"".base64_encode($BROWSER->DesiredChatGroup)."\" dcp=\"".base64_encode($BROWSER->DesiredChatPartner)."\" at=\"".base64_encode($BROWSER->AllocatedTime)."\" cp=\"" . base64_encode($BROWSER->Phone)."\" co=\"" . base64_encode($BROWSER->Company) . "\"".$pra.$cti.">\r\n";
				
				foreach($GROUPS as $groupid => $group)
					if($group->IsDynamic)
						foreach($group->Members as $member)
							if($member == $BROWSER->SystemId)
								$this->XMLCurrentChat .= "		<gr>".base64_encode($groupid)."</gr>\r\n";
				
				if(is_array($BROWSER->Customs))
					foreach($BROWSER->Customs as $index => $value)
						if($INPUTS[$index]->Active && $INPUTS[$index]->Custom)
							$this->XMLCurrentChat .=  "   <cf index=\"" . base64_encode($index) . "\">".base64_encode($INPUTS[$index]->GetClientValue($value))."</cf>\r\n";
				
				$this->XMLCurrentChat .=  "   <pn acc=\"".base64_encode(($BROWSER->Activated) ? "1" : "0")."\">\r\n";
				foreach($BROWSER->Members as $systemid => $member)
					$this->XMLCurrentChat .=  "    <member id=\"" . base64_encode($systemid) . "\" st=\"".base64_encode($member->Status)."\" dec=\"".base64_encode(($member->Declined)?1:0)."\" />\r\n";
				$this->XMLCurrentChat .=  "   </pn>\r\n";
				
				if(!empty($BROWSER->ChatVoucherId))
				{
					$chatticket = VisitorChat::GetMatchingVoucher($BROWSER->DesiredChatGroup,$BROWSER->ChatVoucherId);
					if(!empty($chatticket))
						$this->XMLCurrentChat .= "<cticket>" . $chatticket->GetXML(true) . "</cticket>\r\n";
				}
				
				$v_tp = 0;
				$v_pm = "";
				if(!empty($BROWSER->Members[CALLER_SYSTEM_ID]))
				{
					if($BROWSER->Activated == 0)
					{
						$BROWSER->LoadForward(false,true);
						if(!empty($BROWSER->Forward) && ($BROWSER->Forward->TargetSessId == CALLER_SYSTEM_ID || empty($BROWSER->Forward->TargetSessId)))
						{
							$BROWSER->RepostChatHistory(3,$BROWSER->ChatId,CALLER_SYSTEM_ID,0,0,"","","",false,false);
							//$BROWSER->RepostChatHistory(4,$BROWSER->Forward->ChatId,CALLER_SYSTEM_ID,0,0,"",$BROWSER->ChatId);
							$BROWSER->Forward->Destroy();
						}
						else
						{
							$BROWSER->RepostChatHistory(3,$BROWSER->ChatId,CALLER_SYSTEM_ID,0,0,"","","",false,false);
						}
					}
					$v_tp = ($BROWSER->Typing) ? 1 : 0;
				}
				if(isset($this->Caller->ExternalChats[$BROWSER->SystemId]) && !empty($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest))
				{
					foreach($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest as $index => $request)
					{
						if($request->Error && $request->Permission != PERMISSION_NONE)
						{
							if(!$request->Closed)
								$request->Close();
							$this->XMLCurrentChat .=  "   <fupr id=\"".base64_encode($request->Id)."\" cr=\"".base64_encode($request->Created)."\" fm=\"".base64_encode($request->FileMask)."\" fn=\"".base64_encode($request->FileName)."\" fid=\"".base64_encode($request->FileId)."\" cid=\"".base64_encode($request->ChatId)."\" error=\"".base64_encode(true)."\" />\r\n";
						}
						else if($request->Download)
							$this->XMLCurrentChat .=  "   <fupr pm=\"".base64_encode($request->Permission)."\" id=\"".base64_encode($request->Id)."\" cr=\"".base64_encode($request->Created)."\" fm=\"".base64_encode($request->FileMask)."\" fn=\"".base64_encode($request->FileName)."\" cid=\"".base64_encode($request->ChatId)."\" fid=\"".base64_encode($request->FileId)."\" download=\"".base64_encode(true)."\" size=\"".base64_encode(@filesize($request->GetFile()))."\" />\r\n";
						else if($request->Permission == PERMISSION_VOID)
							$this->XMLCurrentChat .=  "   <fupr id=\"".base64_encode($request->Id)."\" cr=\"".base64_encode($request->Created)."\" fm=\"".base64_encode($request->FileMask)."\" fn=\"".base64_encode($request->FileName)."\" fid=\"".base64_encode($request->FileId)."\" cid=\"".base64_encode($request->ChatId)."\" />\r\n";
						else if($request->Permission == PERMISSION_NONE)
							$this->XMLCurrentChat .=  "   <fupr pm=\"".base64_encode($request->Permission)."\" id=\"".base64_encode($request->Id)."\" cr=\"".base64_encode($request->Created)."\" fm=\"".base64_encode($request->FileMask)."\" fn=\"".base64_encode($request->FileName)."\" cid=\"".base64_encode($request->ChatId)."\" fid=\"".base64_encode($request->FileId)."\" />\r\n";
						else if($request->Permission == PERMISSION_CHAT_ARCHIVE)
							$this->XMLCurrentChat .=  "   <fupr pm=\"".base64_encode($request->Permission)."\" id=\"".base64_encode($request->Id)."\" cr=\"".base64_encode($request->Created)."\" fm=\"".base64_encode($request->FileMask)."\" fn=\"".base64_encode($request->FileName)."\" cid=\"".base64_encode($request->ChatId)."\" fid=\"".base64_encode($request->FileId)."\" />\r\n";
					}
				}
				$this->XMLCurrentChat .=  "  </chat>\r\n";
				$this->XMLTyping .= "<v id=\"".base64_encode($BROWSER->UserId . "~" . $BROWSER->BrowserId)."\" tp=\"".base64_encode($v_tp)."\" />\r\n";
			}
			else
				$this->XMLCurrentChat = "  <chat />\r\n";
		}
	}
	
	function GetStaticInfo($found = false)
	{
		global $USER;
		foreach($USER->Browsers as $browserId => $BROWSER)
			if(isset($this->SessionFileSizes[$USER->UserId][$browserId]))
			{
				$found = true;
				break;
			}
		
		if($this->GetAll || isset($this->StaticReload[$USER->UserId]) || !$found || ($this->Caller->LastActive <= $USER->LastActive && !in_array($USER->UserId,$this->CurrentStatics)))
		{
			if(isset($this->StaticReload[$USER->UserId]))
				unset($this->StaticReload[$USER->UserId]);
			
			array_push($this->CurrentStatics,$USER->UserId);
			$USER->StaticInformation = true;
		}
		else
			$USER->StaticInformation = false;
	}

	function RemoveFileSizes($_browsers)
	{
		if(!empty($this->SessionFileSizes))
			foreach($this->SessionFileSizes as $userid => $browsers)
				if(is_array($browsers) && count($browsers) > 0)
				{
					foreach($browsers as $BROWSER => $size)
						if(!in_array($BROWSER,$_browsers))
							unset($this->SessionFileSizes[$userid][$BROWSER]);
				}
				else
					unset($this->SessionFileSizes[$userid]);
	}
}
?>
