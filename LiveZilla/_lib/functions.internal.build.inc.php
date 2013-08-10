<?php
/****************************************************************************************
* LiveZilla intern.build.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function buildFilters()
{
	global $FILTERS,$RESPONSE;
	$RESPONSE->Filter = "";
	foreach($FILTERS->Filters as $filter)
	{
		if($filter->Expiredate != -1 && ($filter->Expiredate + $filter->Created) < time())
			$filter->Destroy();
		else
			$RESPONSE->Filter .= $filter->GetXML();
	}
}

function buildEvents()
{
	global $EVENTS,$RESPONSE,$INTERNAL;
	$RESPONSE->Events = "";

    $permission = $INTERNAL[CALLER_SYSTEM_ID]->GetPermission(4);
    if($permission == PERMISSION_NONE)
        return;

	if(!empty($EVENTS))
		foreach($EVENTS->Events as $event)
			$RESPONSE->Events .= $event->GetXML();
}

function buildActions()
{
	global $RESPONSE;
	$RESPONSE->Actions = "";
	if($result = queryDB(true,"SELECT `trigger_id`,`action_id` FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."` INNER JOIN `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` ON `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."`.`trigger_id`=`".DB_PREFIX.DATABASE_EVENT_TRIGGERS."`.`id` WHERE `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."`.`receiver_user_id` = '".@mysql_real_escape_string(CALLER_SYSTEM_ID)."' GROUP BY `action_id` ORDER BY `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."`.`created` ASC"))
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$internalaction = new EventActionInternal($row);
			$RESPONSE->Actions .= $internalaction->GetXML();
		}
	queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."` WHERE `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."`.`receiver_user_id` = '".@mysql_real_escape_string(CALLER_SYSTEM_ID)."';");
}

function buildGoals()
{
	global $RESPONSE,$INTERNAL;
	$RESPONSE->Goals = "";

    if($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_REPORTS) == PERMISSION_NONE)
        return;

	if(STATS_ACTIVE)
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_GOALS."` ORDER BY `ind` ASC"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$goal = new Goal($row);
				$RESPONSE->Goals .= $goal->GetXML();
			}
}

function buildChatVouchers($typecond="")
{
	global $RESPONSE,$INTERNAL,$GROUPS;

    if(empty($_POST["p_ct_r"]))
        return;

    if($_POST["p_ct_r"] == XML_CLIP_NULL)
		$_POST["p_ct_r"] = 0;
		
	$RESPONSE->ChatVouchers = "";
	$types = array();
	foreach($INTERNAL[CALLER_SYSTEM_ID]->Groups as $gid)
		foreach($GROUPS[$gid]->ChatVouchersRequired as $vid)
		{
			if(!isset($types[$vid]))
			{
				$types[$vid] = $vid;
				if(!empty($typecond))
					$typecond .= " OR ";
				$typecond .= "`t1`.`tid`='" . $vid . "'";
			}
		}
		
	if(!empty($typecond) && is_numeric($_POST["p_ct_r"]))
		if($result = queryDB(true,$d = "SELECT *,`t1`.`id` AS `ticketid` FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` AS `t2` ON `t1`.`tid`=`t2`.`id` WHERE `t1`.`edited` > ".$_POST["p_ct_r"]." AND (" . $typecond . ") ORDER BY `t1`.`edited` ASC LIMIT " . DATA_ITEM_LOADS . ";"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$voucher = new CommercialChatVoucher($row);
				$RESPONSE->ChatVouchers .= $voucher->GetXML();
			}
}

function buildReports($xml="")
{
	global $RESPONSE,$STATS,$INTERNAL;
    if(empty($_POST[POST_INTERN_XMLCLIP_REPORTS_END_TIME]))
        return;
	if(empty($STATS->CurrentDay) || $INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_REPORTS) == PERMISSION_NONE)
		return;
	if($_POST[POST_INTERN_XMLCLIP_REPORTS_END_TIME] == XML_CLIP_NULL)
		$_POST[POST_INTERN_XMLCLIP_REPORTS_END_TIME] = "0_0";
	$parts = explode("_",$_POST[POST_INTERN_XMLCLIP_REPORTS_END_TIME]);

	if($result = queryDB(true,"SELECT *,(SELECT MAX(`time`) FROM `".DB_PREFIX.DATABASE_STATS_AGGS."`) AS `maxtime`,(SELECT MAX(`mtime`) FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE `maxtime`=`time`) AS `maxmtime` FROM `".DB_PREFIX.DATABASE_STATS_AGGS."` WHERE (`time` = ".@mysql_real_escape_string($parts[0])." AND `mtime` > ".@mysql_real_escape_string($parts[1]).") OR (`time` > ".@mysql_real_escape_string($parts[0]).") ORDER BY `time` ASC,`mtime` ASC LIMIT 1"))
	{
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if($row["month"]==0)
				$report = new StatisticYear($row["year"],0,0);
			else if($row["day"]==0)
				$report = new StatisticMonth($row["year"],$row["month"],0);
			else
				$report = new StatisticDay($row["year"],$row["month"],$row["day"]);
				
			$type = -1;
			$update = false;
			$value = "";
			
			if($report->Type == STATISTIC_PERIOD_TYPE_DAY)
			{
				if($_POST[POST_INTERN_PROCESS_UPDATE_REPORT_TYPE]==1)
				{
					if($STATS->CurrentDay->CreateVisitorList)
					{
						if(empty($row["aggregated"]) && (!@file_exists($report->GetFilename(true,true)) || ($row["time"] < (time()-StatisticProvider::$AutoUpdateTime))))
							$report->SaveVisitorListToFile();
						if(@file_exists($report->GetFilename(true,true)))
							$value = getFile($report->GetFilename(true,true));
					}
					$type = 1;
				}
				else if($_POST[POST_INTERN_PROCESS_UPDATE_REPORT_TYPE]==0)
				{
					if($STATS->CurrentDay->CreateReport)
					{
						if(empty($row["aggregated"]) && (!@file_exists($report->GetFilename(true,false)) || ($row["time"] < (time()-StatisticProvider::$AutoUpdateTime))))
						{
							$update = true;
							$report->SaveReportToFile();
						}
						else if(@file_exists($report->GetFilename(true,false)))
							$value = getFile($report->GetFilename(true,false));
					}
					$type = 0;
				}
			}
			else
			{
				if(empty($row["aggregated"]) && (!@file_exists($report->GetFilename(true,false)) || ($row["time"] < (time()-StatisticProvider::$AutoUpdateTime))))
					$report->SaveReportToFile();
				if(@file_exists($report->GetFilename(true,false)))
					$value = getFile($report->GetFilename(true,false));
				$type = ($report->Type == STATISTIC_PERIOD_TYPE_MONTH) ? 2 : 3;
			}
			if($type > -1)
			{
				$convrate = ($row["sessions"]>0) ? round(((100*$row["conversions"])/$row["sessions"]),StatisticProvider::$RoundPrecision) : 0;
				$chats = $chatsd = 0;
				
				$qmonth = ($report->Type == STATISTIC_PERIOD_TYPE_YEAR) ? "" : " AND `month`='".@mysql_real_escape_string($row["month"])."'";
				$qday = ($report->Type != STATISTIC_PERIOD_TYPE_DAY) ? "" : " AND `day`='".@mysql_real_escape_string($row["day"])."'";
				
				if($results = queryDB(true,"SELECT (SUM(`amount`)-SUM(`multi`)) AS `samount` FROM `".DB_PREFIX.DATABASE_STATS_AGGS_CHATS."` WHERE `year`='".@mysql_real_escape_string($row["year"])."'".$qmonth.$qday.""))
					if(mysql_num_rows($results) == 1)
					{
						$rows = mysql_fetch_array($results, MYSQL_BOTH);
						if(is_numeric($rows["samount"]))
							$chats = $rows["samount"];
					}
				$xml .= "<r cid=\"".base64_encode(getId(3))."\" ragg=\"".base64_encode(empty($row["aggregated"]) ? 0 : 1)."\" rtype=\"".base64_encode($type)."\" convrate=\"".base64_encode($convrate)."\" chats=\"".base64_encode($chats)."\" update=\"".base64_encode(($update)?1:0)."\" visitors=\"".base64_encode($row["sessions"])."\" time=\"".base64_encode($row["time"])."\" mtime=\"".base64_encode($row["mtime"])."\" year=\"".base64_encode($row["year"])."\" month=\"".base64_encode($row["month"])."\" day=\"".base64_encode($row["day"])."\">".base64_encode($value)."</r>\r\n";
			}
			$xml .= "<ri maxtime=\"".base64_encode($row["maxtime"])."\" maxmtime=\"".base64_encode($row["maxmtime"])."\" />";
		}
	}
	$RESPONSE->SetStandardResponse(1,$xml);
}

function buildResources($xml="",$count=0,$last=0)
{
	global $RESPONSE,$INTERNAL;
	
	if($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_RESOURCES) == PERMISSION_NONE)
		return;
	
	$resources = array();
	if($_POST[POST_INTERN_XMLCLIP_RESSOURCES_END_TIME] == XML_CLIP_NULL)
		$_POST[POST_INTERN_XMLCLIP_RESSOURCES_END_TIME] = 0;

	if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE `edited` > ".@mysql_real_escape_string($_POST[POST_INTERN_XMLCLIP_RESSOURCES_END_TIME])." AND `edited`<".@mysql_real_escape_string(time())." AND parentid<>100 ORDER BY `edited` ASC"))
	{
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			$resources[] = $row;
	}
	
	foreach($resources as $res)
	{
		if(++$count <= DATA_ITEM_LOADS || $res["edited"] == $last)
			$xml .= "<r rid=\"".base64_encode($res["id"])."\" si=\"".base64_encode($res["size"])."\" di=\"".base64_encode($res["discarded"])."\" oid=\"".base64_encode($res["owner"])."\" eid=\"".base64_encode($res["editor"])."\" ty=\"".base64_encode($res["type"])."\" ti=\"".base64_encode($res["title"])."\" t=\"".base64_encode($res["tags"])."\" ed=\"".base64_encode($last = $res["edited"])."\" pid=\"".base64_encode($res["parentid"])."\" ra=\"".base64_encode($res["rank"])."\">".base64_encode($res["value"])."</r>\r\n";
		else
			break;
	}
	$RESPONSE->Resources = (strlen($xml) > 0) ? $xml : null;
}

function buildArchive($xml="",$count=0,$last=0)
{
	global $RESPONSE,$INTERNAL,$INPUTS;
	initData(false,false,false,false,false,false,false,true);

	$chats = array();
	if($_POST[POST_INTERN_XMLCLIP_ARCHIVE_END_TIME] == XML_CLIP_NULL)
		$_POST[POST_INTERN_XMLCLIP_ARCHIVE_END_TIME] = 0;
	
	if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `html`!='0' AND `closed` > ".@mysql_real_escape_string($_POST[POST_INTERN_XMLCLIP_ARCHIVE_END_TIME])." AND `closed` < ".@mysql_real_escape_string(time())." AND `internal_id` !='0' ORDER BY `closed` ASC LIMIT " . (DATA_ITEM_LOADS*2)))
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			$chats[] = $row;
	
	foreach($chats as $chat)
	{
		if(++$count <= DATA_ITEM_LOADS || $chat["closed"] == $last)
		{
			$xml .= "<c full=\"".base64_encode("true")."\" cid=\"".base64_encode($chat["chat_id"])."\" v=\"".base64_encode($chat["voucher_id"])."\" iid=\"".base64_encode($chat["internal_id"])."\" gid=\"".base64_encode($chat["group_id"])."\" cmb=\"".base64_encode($chat["call_me_back"])."\" eid=\"".base64_encode($chat["external_id"])."\" en=\"".base64_encode($chat["fullname"])."\" ts=\"".base64_encode($chat["time"])."\" cl=\"".base64_encode($last = $chat["closed"])."\" te=\"".base64_encode($chat["endtime"])."\" em=\"".base64_encode($chat["email"])."\" cp=\"".base64_encode($chat["phone"])."\" ac=\"".base64_encode($chat["area_code"])."\" co=\"".base64_encode($chat["company"])."\" il=\"".base64_encode($chat["iso_language"])."\" ic=\"".base64_encode($chat["iso_country"])."\" ho=\"".base64_encode($chat["host"])."\" ip=\"".base64_encode($chat["ip"])."\" gzip=\"".base64_encode($chat["gzip"])."\" wt=\"".base64_encode($chat["wait"])."\" sr=\"".base64_encode($chat["accepted"])."\" er=\"".base64_encode($chat["ended"])."\">\r\n";
			if($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_CHAT_ARCHIVE) == PERMISSION_FULL || CALLER_SYSTEM_ID == $chat["internal_id"])
			{
				$xml .= "<chtml>".base64_encode($chat["html"])."</chtml>\r\n";
                $xml .= "<cplain>".base64_encode($chat["plaintext"])."</cplain>\r\n";
				if(!empty($chat["customs"]))
					foreach(@unserialize($chat["customs"]) as $custname => $value)
						foreach($INPUTS as $input)
							if($input->Name == $custname && $input->Active && $input->Custom)
								$xml .= "<cc cuid=\"".base64_encode($custname)."\">".base64_encode($input->GetClientValue($value))."</cc>\r\n";
			}
			$xml .= "</c>\r\n";
		}
		else
			break;
	}
	$RESPONSE->Archive = (strlen($xml) > 0) ? $xml : null;
}

function buildRatings($xml="")
{
	global $RESPONSE,$INTERNAL;

    if(empty($_POST[POST_INTERN_XMLCLIP_RATING_END_TIME]))
        return;

	$permission = $INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_RATINGS);
	if($_POST[POST_INTERN_XMLCLIP_RATING_END_TIME] == XML_CLIP_NULL)
		$_POST[POST_INTERN_XMLCLIP_RATING_END_TIME] = 0;

	$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE time>".@mysql_real_escape_string($_POST[POST_INTERN_XMLCLIP_RATING_END_TIME])." ORDER BY `time` ASC LIMIT ".@mysql_real_escape_string(DATA_ITEM_LOADS).";");
	if($result)
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$rating = new Rating($row["id"],$row);
			$xml .= $rating->GetXML($INTERNAL,(($rating->InternId == $INTERNAL[CALLER_SYSTEM_ID]->UserId && $permission != PERMISSION_NONE) || $permission == PERMISSION_FULL));
		}
	queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_RATINGS."` WHERE time<".@mysql_real_escape_string(DATA_LIFETIME).";");
	$RESPONSE->Ratings = $xml;
}

function buildTickets($xml="",$dle_xml="",$ticketCount=0)
{
	global $RESPONSE,$INTERNAL;

    if(empty($_POST[POST_INTERN_XMLCLIP_TICKETS_END_TIME]))
        return;

	initData(false,false,false,false,false,false,false,true);

	$permission = $INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_MESSAGES);

	if($_POST[POST_INTERN_XMLCLIP_TICKETS_END_TIME] == XML_CLIP_NULL)
		$_POST[POST_INTERN_XMLCLIP_TICKETS_END_TIME] = 0;

	$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_TICKETS."` INNER JOIN `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` ON `".DB_PREFIX.DATABASE_TICKETS."`.`id`=`".DB_PREFIX.DATABASE_TICKET_MESSAGES."`.`ticket_id` WHERE `time` >".@mysql_real_escape_string($_POST[POST_INTERN_XMLCLIP_TICKETS_END_TIME])." ORDER BY `time` ASC LIMIT ".@mysql_real_escape_string(DATA_ITEM_LOADS).";");
	if($result)
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$ticket = new UserTicket($row);
			$full = ((in_array($ticket->Group,$INTERNAL[CALLER_SYSTEM_ID]->Groups) && $permission != PERMISSION_NONE) || $permission == PERMISSION_FULL);
            if($full)
			{
				$ticket->Messages[0]->LoadCustoms();
				$ticket->Messages[0]->LoadAttachments();
			}
			$xml .= $ticket->GetXML($full);
            $ticketCount++;
		}
		
	if($_POST[POST_INTERN_XMLCLIP_EMAILS_END_TIME] == XML_CLIP_NULL)
		$_POST[POST_INTERN_XMLCLIP_EMAILS_END_TIME] = 0;

    $mailcount = $last = 0;
	$result = queryDB(true,"SELECT `t1`.*,`t2`.`email` AS `receiver_mail` FROM `".DB_PREFIX.DATABASE_TICKET_EMAILS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_MAILBOXES."` AS `t2` ON `t1`.`mailbox_id`=`t2`.`id` WHERE `edited` >".@mysql_real_escape_string($_POST[POST_INTERN_XMLCLIP_EMAILS_END_TIME])." ORDER BY `edited` ASC LIMIT ".@mysql_real_escape_string(DATA_ITEM_LOADS*5).";");
	if($result)
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
            $permissione = $INTERNAL[CALLER_SYSTEM_ID]->GetPermission(22);
            $full = $permissione != PERMISSION_NONE && ((in_array($row["group_id"],$INTERNAL[CALLER_SYSTEM_ID]->Groups) && $permission != PERMISSION_NONE) || $permission == PERMISSION_FULL);
            $email = new TicketEmail($row);
            $email->LoadAttachments();
			$xml .= $email->GetXML($full);

            if($last != $row["edited"] && ++$mailcount >= DATA_ITEM_LOADS)
                break;

            $last = $row["edited"];
		}

    if($_POST[POST_INTERN_XMLCLIP_TICKETS_STATUS_END_TIME] == XML_CLIP_NULL)
        $_POST[POST_INTERN_XMLCLIP_TICKETS_STATUS_END_TIME] = 0;

    if($ticketCount != DATA_ITEM_LOADS)
    {
        $scount = $last = 0;
        $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_TICKET_EDITORS."` WHERE `time` >".@mysql_real_escape_string($_POST[POST_INTERN_XMLCLIP_TICKETS_STATUS_END_TIME])." ORDER BY `time` ASC;");
        if($result)
            while($row = mysql_fetch_array($result, MYSQL_BOTH))
            {
                if($scount++ >= DATA_ITEM_LOADS && $last != $row["time"])
                    break;

                $ticket = new TicketEditor($row["ticket_id"],$row);
                $xml .= $ticket->GetXML($row["time"],$row["status"]);
                $last = $row["time"];
            }
    }
	$RESPONSE->Messages = $xml;
	$RESPONSE->Messages .= $dle_xml;
}

function buildNewPosts()
{
	global $INTERNAL,$RESPONSE;
	foreach($INTERNAL[CALLER_SYSTEM_ID]->GetPosts() as $post)
	{
		$RESPONSE->Posts .= $post->GetXml();
		$INTERNAL[CALLER_SYSTEM_ID]->SetRepostTime($post->ReceiverGroup,$post->Created);
	}
}

function buildIntern()
{
	global $INTERNAL,$GROUPS,$RESPONSE;
	$builder = new InternalXMLBuilder($INTERNAL[CALLER_SYSTEM_ID],$INTERNAL,$GROUPS);
	$builder->Generate();

	$RESPONSE->Internals = $builder->XMLInternal;
	$RESPONSE->Typing .= $builder->XMLTyping;
	$RESPONSE->InternalProfilePictures = $builder->XMLProfilePictures;
	$RESPONSE->InternalWebcamPictures = $builder->XMLWebcamPictures;
	$RESPONSE->Groups = $builder->XMLGroups;
	$RESPONSE->InternalVcards = $builder->XMLProfiles;
}

function buildExtern($objectCount=0)
{
	global $CONFIG,$VISITOR,$INTERNAL,$GROUPS,$RESPONSE;
	$RESPONSE->Tracking = "";

	$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `received`=0 ORDER BY `created` ASC;");
	while($row = mysql_fetch_array($result, MYSQL_BOTH))
	{
		$forward = new Forward($row);
		$RESPONSE->Forwards .= $forward->GetXml();
		
		if(!empty($VISITOR[$forward->ReceiverUserId]) && !empty($VISITOR[$forward->ReceiverUserId]->Browsers[$forward->ReceiverBrowserId]))
		{
			if(!$forward->Invite)
				$VISITOR[$forward->ReceiverUserId]->Browsers[$forward->ReceiverBrowserId]->Forward=$forward;
			else if(CALLER_SYSTEM_ID == $forward->TargetSessId)
				$forward->Save(true,false);
		}
	}
	
	$isex = !empty($INTERNAL[CALLER_SYSTEM_ID]->Groups) && $GROUPS[$INTERNAL[CALLER_SYSTEM_ID]->Groups[0]]->IsExternal;

	$builder = new ExternalXMLBuilder($INTERNAL[CALLER_SYSTEM_ID],$VISITOR,(NO_CLIPPING || isset($_POST[POST_INTERN_RESYNC])),$isex);
	$builder->SessionFileSizes = $INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes;
	$builder->StaticReload = $INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload;

	$base = (!empty($INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes["discarded"])) ? $INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes["discarded"] : array();
	$builder->SetDiscardedObject($base);
	$builder->Generate();
	$RESPONSE->Tracking = $builder->XMLCurrent;
	foreach($builder->DiscardedObjects as $uid => $list)
	{
		$RESPONSE->Tracking .= "<cd id=\"".base64_encode($uid)."\">\r\n";
		if($list != null)
			foreach($list as $bid)
			{
				$RESPONSE->Tracking .= " <bd id=\"".base64_encode($bid)."\" />\r\n";
			}
		$RESPONSE->Tracking .= "</cd>\r\n";
	}
	$RESPONSE->Typing .= $builder->XMLTyping;
	$INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes = $builder->SessionFileSizes;
	$INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload = $builder->StaticReload;
	if($builder->GetAll && !LOGIN)
		$RESPONSE->Tracking .= "<resync />\r\n";
	$objectCount = $builder->ObjectCounter;
	
	if(count($VISITOR) == 0)
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes = array();
}
?>
