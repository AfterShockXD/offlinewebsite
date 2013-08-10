<?php
/****************************************************************************************
* LiveZilla objects.external.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
class GroupBuilder
{
	public $InternalGroups;
	public $InternalUsers;
	public $Config;
	public $GroupAvailable = false;
	public $GroupValues = array();
	public $Result;
	public $ErrorHTML = "''";
	public $ReqGroup;
	public $ReqOperator;
	public $Parameters;
	
	function GroupBuilder($_internalUsers,$_internalGroups,$_config,$_reqGroup="",$_reqOperator="",$allowCom=true)
	{
		$this->ReqGroup = (!empty($_GET[GET_EXTERN_GROUP])) ? base64UrlDecode($_GET[GET_EXTERN_GROUP]) : $_reqGroup;
		$this->ReqOperator = (!empty($_GET[GET_EXTERN_INTERN_USER_ID])) ? base64UrlDecode($_GET[GET_EXTERN_INTERN_USER_ID]) : $_reqOperator;
		$this->InternalUsers = $_internalUsers;
		$this->InternalGroups = $_internalGroups;
		$this->Config = $_config;
		$this->GroupValues["groups_online"] = Array();
		$this->GroupValues["groups_offline"] = Array();
		$this->GroupValues["groups_online_amounts"] = Array();
		$this->GroupValues["groups_output"] = Array();
		$this->GroupValues["groups_hidden"] = Array();
		$this->GroupValues["set_by_get_user"] = null;
		$this->GroupValues["set_by_get_group"] = null;
		$this->GroupValues["set_by_cookie"] = null;
		$this->GroupValues["set_by_standard"] = null;
		$this->GroupValues["set_by_online"] = null;
		$this->GroupValues["req_for_user"] = !empty($this->ReqOperator);
		$this->GroupValues["req_for_group"] = !empty($this->ReqGroup);
		
		$this->Parameters = getTargetParameters($allowCom);

		if($this->Parameters["include_group"] != null || $this->Parameters["include_user"] != null)
		{
			foreach($_internalGroups as $gid => $group)
				if(!($this->Parameters["include_group"] != null && in_array($gid,$this->Parameters["include_group"])))
				{
					if(!($this->Parameters["include_user"] != null && in_array($gid,$_internalUsers[getInternalSystemIdByUserId($this->Parameters["include_user"])]->GetGroupList(false))))
						$this->GroupValues["groups_hidden"][] = $gid;
				}
		}
		if($this->Parameters["exclude"] != null)
			$this->GroupValues["groups_hidden"] = $this->Parameters["exclude"];
	}
	
	function GetTargetGroup(&$_operatorCount,$_prInternalId="",$offdef = null,$offdefocunt=0)
	{	
		$groups = array_merge($this->GroupValues["groups_output"],$this->GroupValues["groups_offline"]);
		if(!empty($_prInternalId) && !empty($this->InternalUsers[$_prInternalId]) && $this->InternalUsers[$_prInternalId]->Status < USER_STATUS_OFFLINE)
			foreach($this->InternalUsers[$_prInternalId]->GetGroupList(true) as $c => $id)
				if($this->InternalGroups[$id]->IsExternal && !in_array($id,$this->GroupValues["groups_hidden"]) && $this->InternalGroups[$id]->IsOpeningHour())
				{
					$_operatorCount = (!empty($this->GroupValues["groups_online_amounts"][$id])) ? $this->GroupValues["groups_online_amounts"][$id] : 0;
					return $id;
				}
			
		if(defined("IGNORE_WM") || empty($this->GroupValues["set_by_get_group"]))
		{
			$_operatorCount = 0;
			foreach($groups as $id => $values)
				if($this->InternalGroups[$id]->IsExternal && !in_array($id,$this->GroupValues["groups_hidden"]) && $this->InternalGroups[$id]->IsOpeningHour() && $this->InternalGroups[$id]->IsHumanAvailable() /*&& !$this->InternalGroups[$id]->HasWelcomeManager()*/)
				{
					$_operatorCount = (!empty($this->GroupValues["groups_online_amounts"][$id])) ? $this->GroupValues["groups_online_amounts"][$id] : 0;
					return $id;
				}
		}

		$_operatorCount = 0;
		foreach($groups as $id => $values)
			if($this->InternalGroups[$id]->IsExternal && !in_array($id,$this->GroupValues["groups_hidden"]) && $this->InternalGroups[$id]->IsOpeningHour())
			{
				$_operatorCount = (!empty($this->GroupValues["groups_online_amounts"][$id])) ? $this->GroupValues["groups_online_amounts"][$id] : 0;
				return $id;
			}
			else if($this->InternalGroups[$id]->IsStandard || empty($offdef))
			{
				$offdefocunt = (!empty($this->GroupValues["groups_online_amounts"][$id])) ? $this->GroupValues["groups_online_amounts"][$id] : 0;
				$offdef = $id;
			}
		$_operatorCount = $offdefocunt;
		return $offdef;
	}
	
	function GetHTML()
	{
		$html_groups = "";
		foreach($this->InternalGroups as $id => $group)
		{
			$name = (strlen($group->Description) > 0) ? $group->Description : $id;
			if($group->IsExternal && !in_array($id,$this->GroupValues["groups_hidden"]))
				$html_groups .= "<option value=\"".$id."\">".$name."</option>";
		}
		return $html_groups;
	}
	
	function Generate($_user=null,$_allowBots=false)
	{
		foreach($this->InternalUsers as $internaluser)
		{
			if($internaluser->LastActive > (time()-$this->Config["timeout_clients"]) && $internaluser->Status < USER_STATUS_OFFLINE && ($_allowBots || !$internaluser->IsBot))
			{
				$igroups = $internaluser->GetGroupList(true);
				for($count=0;$count<count($igroups);$count++)
				{
					if($internaluser->UserId == $this->ReqOperator)
						if(!($this->GroupValues["req_for_group"] && $igroups[$count] != $this->ReqGroup) || (isset($_GET[GET_EXTERN_PREFERENCE]) && base64UrlDecode($_GET[GET_EXTERN_PREFERENCE]) == "user"))
							$this->GroupValues["set_by_get_user"] = $igroups[$count];

					if(!isset($this->GroupValues["groups_online_amounts"][$igroups[$count]]))
						$this->GroupValues["groups_online_amounts"][$igroups[$count]] = 0;
						
					if($internaluser->IsBot)
						$this->GroupValues["groups_online_amounts"][$igroups[$count]]+=1;
					else
						$this->GroupValues["groups_online_amounts"][$igroups[$count]]+=2;
				}
			}
		}
		$counter = 0;
		foreach($this->InternalGroups as $id => $group)
		{
			if(!$group->IsExternal)
				continue;
				
			$used = false;
			$amount = (isset($this->GroupValues["groups_online_amounts"]) && is_array($this->GroupValues["groups_online_amounts"]) && array_key_exists($id,$this->GroupValues["groups_online_amounts"]) && $group->IsOpeningHour()) ? $this->GroupValues["groups_online_amounts"][$id] : 0;
			$transport = base64_encode($id) . "," . base64_encode($amount) . "," . base64_encode($group->Description) . "," . base64_encode($group->Email);
		
			if($this->GroupValues["req_for_group"] && $id == $this->ReqGroup)
				{$this->GroupValues["set_by_get_group"] = $id;$used=true;}
			elseif(getCookieValue("login_group") != null && $id == getCookieValue("login_group") && !isset($requested_group))
				{$this->GroupValues["set_by_cookie"] = $id;$used=true;}
			elseif($group->IsStandard)
				{$this->GroupValues["set_by_standard"] = $id;$used=true;}
			elseif(empty($this->GroupValues["set_by_online"]))
				{$this->GroupValues["set_by_online"] = $id;$used=true;}

			if(!in_array($id,$this->GroupValues["groups_hidden"]) && ($group->IsExternal || $used))
			{
				$counter++;
				if($amount > 0)
				{
					$this->GroupAvailable = true;
					$this->GroupValues["groups_online"][$id] = $transport;
				}
				else
				{
					if($group->IsStandard)
					{
						$na[$id] = $transport;
						$na = array_merge($na,$this->GroupValues["groups_offline"]);
						$this->GroupValues["groups_offline"] = $na;
					}
					else
						$this->GroupValues["groups_offline"][$id] = $transport;
				}
			}
		}
		if(isset($_GET[GET_EXTERN_PREFERENCE]) && base64UrlDecode($_GET[GET_EXTERN_PREFERENCE]) == "group")
		{
			if(isset($this->GroupValues["groups_online_amounts"][$this->ReqGroup]) && $this->GroupValues["groups_online_amounts"][$this->ReqGroup] > 0)
			{
				$this->GroupValues["set_by_get_user"] = null;
				$this->GroupValues["req_for_user"] = false;
			}
		}

		if(!empty($this->GroupValues["set_by_get_user"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_get_user"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_get_user"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_get_user"]];
		else if(!empty($this->GroupValues["set_by_get_group"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_get_group"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_get_group"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_get_group"]];
		else if(!empty($this->GroupValues["set_by_cookie"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_cookie"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_cookie"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_cookie"]];
		else if(!empty($this->GroupValues["set_by_standard"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_standard"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_standard"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_standard"]];
		else if(!empty($this->GroupValues["set_by_online"]) && isset($this->GroupValues["groups_online"][$this->GroupValues["set_by_online"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_online"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_online"]];
		else if(!empty($this->GroupValues["set_by_cookie"]) && empty($this->GroupValues["groups_output"]) && !empty($this->GroupValues["groups_offline"][$this->GroupValues["set_by_cookie"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_cookie"]] = $this->GroupValues["groups_offline"][$this->GroupValues["set_by_cookie"]];
		else if(!empty($this->GroupValues["set_by_get_group"]) && empty($this->GroupValues["groups_output"]) && !empty($this->GroupValues["groups_offline"][$this->GroupValues["set_by_get_group"]]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_get_group"]] = $this->GroupValues["groups_offline"][$this->GroupValues["set_by_get_group"]];
		
		foreach($this->GroupValues["groups_online"] as $id => $transport)
			if(!isset($this->GroupValues["groups_output"][$id]))
				$this->GroupValues["groups_output"][$id] = $transport;

		if(empty($this->GroupValues["set_by_get_group"]) || empty($this->GroupValues["groups_online_amounts"][$this->GroupValues["set_by_get_group"]]) /*|| (!empty($this->GroupValues["groups_online_amounts"][$this->GroupValues["set_by_get_group"]]) && $this->GroupValues["groups_online_amounts"][$this->GroupValues["set_by_get_group"]] == )1*/)
		{
			$ngroups = array();
			foreach($this->GroupValues["groups_output"] as $id => $group)
			{
				$ngroups[$id] = (!empty($this->GroupValues["groups_online_amounts"][$id])) ? $this->GroupValues["groups_online_amounts"][$id] : 0;
				
				if($id == $this->GroupValues["set_by_standard"])
					$ngroups[$id] = 10000;
			}
			arsort($ngroups);
			$nsgroups = array();
			foreach($ngroups as $id => $amount)
				$nsgroups[$id] = $this->GroupValues["groups_output"][$id];
			$this->GroupValues["groups_output"] = $nsgroups;
		}

		$result = array_merge($this->GroupValues["groups_output"],$this->GroupValues["groups_offline"]);
		
		foreach($result as $key => $value)
		{
			$chat_input_fields = "new Array(";
			$count = 0;
			foreach($this->InternalGroups[$key]->ChatInputsHidden as $index)
			{
				if($count > 0)$chat_input_fields.=",";
				$chat_input_fields.="'".$index."'";
				$count++;
			}
			$value .= ",".base64_encode($chat_input_fields . ");");
			$chat_input_fields = "new Array(";
			$count = 0;
			foreach($this->InternalGroups[$key]->ChatInputsMandatory as $index)
			{
				if($count > 0)$chat_input_fields.=",";
				$chat_input_fields.="'".$index."'";
				$count++;
			}
			$value .= ",".base64_encode($chat_input_fields . ");");
		
			$ticket_input_fields = "new Array(";
			$count = 0;
			foreach($this->InternalGroups[$key]->TicketInputsHidden as $index)
			{
				if($count > 0)$ticket_input_fields.=",";
				$ticket_input_fields.="'".$index."'";
				$count++;
			}
			$value .= ",".base64_encode($ticket_input_fields . ");");
			$ticket_input_fields = "new Array(";
			$count = 0;
			foreach($this->InternalGroups[$key]->TicketInputsMandatory as $index)
			{
				if($count > 0)$ticket_input_fields.=",";
				$ticket_input_fields.="'".$index."'";
				$count++;
			}
			$value .= ",".base64_encode($ticket_input_fields . ");");
			
			$mes = getPredefinedMessage($this->InternalGroups[$key]->PredefinedMessages,$_user);
			if($mes != null)
			{
				$value .= ",".base64_encode($mes->ChatInformation);
				$value .= ",".base64_encode($mes->CallMeBackInformation);
				$value .= ",".base64_encode($mes->TicketInformation);
			}
			else
			{
				$value .= ",".base64_encode("");
				$value .= ",".base64_encode("");
				$value .= ",".base64_encode("");
			}
			
			$count = 0;
			$com_tickets_allowed = "new Array(";
			foreach($this->InternalGroups[$key]->ChatVouchersRequired as $cttid)
			{
				if($count > 0)$com_tickets_allowed.=",";
				$com_tickets_allowed.="'".$cttid."'";
				$count++;
			}
			$value .= ",".base64_encode($com_tickets_allowed. ");");
			
			if(!empty($this->Result))
				$this->Result .= ";" . $value;
			else
				$this->Result = $value;
		}
		if($counter == 0)
			$this->ErrorHTML = "lz_chat_data.Language.ClientErrorGroups";
	}
}

class RatingGenerator
{
	public $Fields;
	
	function RatingGenerator()
	{
		$this->Generate();
	}
	
	function Generate()
	{
		$this->Fields = array(4);
		for($int = 0;$int < 4;$int++)
			$this->Fields[$int]= str_replace("<!--box_id-->",$int,getFile(TEMPLATE_HTML_RATEBOX));
	}
}
?>
