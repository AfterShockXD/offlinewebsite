<?php
/****************************************************************************************
* LiveZilla objects.global.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();


class BaseObject
{
	public $Id;
	public $Created;
	public $Edited;
	public $Creator;
	public $Editor;
	public $FirstCall;
	public $Status;
}

class Action
{
	public $Id;
	public $URL = "";
	public $ReceiverUserId;
	public $ReceiverBrowserId;
	public $SenderSystemId;
	public $SenderUserId;
	public $SenderGroupId;
	public $Text;
	public $BrowserId;
	public $Status;
	public $TargetFile;
	public $Extension;
	public $Created;
	public $Displayed;
	public $Accepted;
	public $Declined;
	public $Closed;
	public $Exists;
	public $EventActionId = "";
}

class Post extends BaseObject
{
	public $Receiver;
	public $ReceiverGroup;
	public $ReceiverOriginal;
	public $Sender;
	public $SenderName;
	public $Persistent = false;
	public $Repost = false;
	public $ChatId;
	public $Translation = "";
	public $TranslationISO = "";
	public $HTML;
	public $Received;
	public $BrowserId = "";
	
	function Post()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
			$this->Id = $row["id"];
			$this->Sender = $row["sender"];
			$this->SenderName = $row["sender_name"];
			$this->Receiver = $row["receiver"];
			$this->ReceiverOriginal = $row["receiver_original"];
			$this->ReceiverGroup = $row["receiver_group"];
			$this->Received = !empty($row["received"]);
			$this->Text = $row["text"];
			$this->Created = $row["time"];
			$this->ChatId = $row["chat_id"];
			$this->Repost = !empty($row["repost"]);
			$this->Translation = $row["translation"];
			$this->TranslationISO = $row["translation_iso"];
			$this->BrowserId = $row["browser_id"];
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->Sender = func_get_arg(1);
			$this->Receiver = 
			$this->ReceiverOriginal = func_get_arg(2);
			$this->Text = func_get_arg(3);
			$this->Created = func_get_arg(4);
			$this->ChatId = func_get_arg(5);
			$this->SenderName = func_get_arg(6);
		}
   	}
	
	function GetXml()
	{
		$translation = (!empty($this->Translation)) ? " tr=\"".base64_encode($this->Translation)."\" triso=\"".base64_encode($this->TranslationISO)."\"" : "";
		return "<val id=\"".base64_encode($this->Id)."\" rp=\"".base64_encode(($this->Repost) ? 1 : 0)."\" sen=\"".base64_encode($this->Sender)."\" rec=\"".base64_encode($this->ReceiverGroup)."\" reco=\"".base64_encode($this->ReceiverOriginal)."\" date=\"".base64_encode($this->Created)."\"".$translation.">".base64_encode($this->Text)."</val>\r\n";
	}
	
	function GetCommand($_name)
	{
		global $LZLANG;
		if($this->Repost && empty($_name))
			$_name = $LZLANG["client_guest"];
	
		if(!empty($this->Translation))
			return "lz_chat_add_internal_text(\"".base64_encode($this->Translation."<div class=\"lz_message_translation\">".$this->Text."</div>")."\" ,\"".base64_encode($this->Id)."\",\"".base64_encode($_name)."\", ".parseBool($this->Repost).");";
		else
			return "lz_chat_add_internal_text(\"".base64_encode($this->Text)."\" ,\"".base64_encode($this->Id)."\",\"".base64_encode($_name)."\", ".parseBool($this->Repost).");";
	}
	
	function Save($_mTime=0)
	{
		if($_mTime==0)
		{
			$_mTime = mTime();
			$this->Created = $_mTime[1];
		}
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_POSTS."` (`id`,`chat_id`,`time`,`micro`,`sender`,`receiver`,`receiver_group`,`receiver_original`,`text`,`translation`,`translation_iso`,`received`,`persistent`,`repost`,`sender_name`,`browser_id`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->ChatId)."',".@mysql_real_escape_string($this->Created).",".@mysql_real_escape_string($_mTime[0]).",'".@mysql_real_escape_string($this->Sender)."','".@mysql_real_escape_string($this->Receiver)."','".@mysql_real_escape_string($this->ReceiverGroup)."','".@mysql_real_escape_string($this->ReceiverOriginal)."','".@mysql_real_escape_string($this->Text)."','".@mysql_real_escape_string($this->Translation)."','".@mysql_real_escape_string($this->TranslationISO)."','".@mysql_real_escape_string($this->Received?1:0)."','".@mysql_real_escape_string($this->Persistent?1:0)."','".@mysql_real_escape_string($this->Repost?1:0)."','".@mysql_real_escape_string($this->SenderName)."','".@mysql_real_escape_string($this->BrowserId)."');");
	}
	
	function MarkReceived($_systemId)
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_POSTS."` SET `received`='1',`persistent`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' AND `receiver`='".@mysql_real_escape_string($_systemId)."';");
	}
}

class ChatFilter
{
    public $PlainText = "";
    public $HTML = "";
    public $FirstPost;
    public $ElementCount = 0;

    function GetPlainPost($_post,$_translation,$_time,$_senderName)
    {
        $post = (empty($_translation)) ? $_post : $_translation." (".$_post.")";
        $post = str_replace("<br>","\r\n",trim($post));
        preg_match_all("/<a.*href=\"([^\"]*)\".*>(.*)<\/a>/iU", $post, $matches);
        $count = 0;
        foreach($matches[0] as $match)
        {
            if(strpos($matches[1][$count],"javascript:")===false)
                $post = str_replace($matches[0][$count],$matches[2][$count] . " (" . $matches[1][$count].") ",$post);
            $count++;
        }
        $post = html_entity_decode(strip_tags($post),ENT_COMPAT,"UTF-8");
        return "| " . date("d.m.Y H:i:s",$_time) . " | " . $_senderName .  ": " . trim($post);
    }

    function GetHTMLPost($_post,$_translation,$_time,$_senderName,$_senderId)
    {
        global $INTERNAL;
        $post = (empty($_translation)) ? $_post : $_translation."<div class=\"lz_message_translation\">".$_post."</div>";
        $file = (empty($INTERNAL[$_senderId])) ? getFile(TEMPLATE_HTML_MESSAGE_INTERN) : getFile(TEMPLATE_HTML_MESSAGE_EXTERN);
        $html = str_replace("<!--dir-->","ltr",$file);
        $html = str_replace("<!--message-->",$post,$html);
        $html = str_replace("<!--name-->",$_senderName,$html);
        $html = str_replace("<!--time-->",date(DATE_RFC822,$_time),$html);
        return $html;
    }

    function GetPlainFile($_permission,$_download,$_externalFullname,$_fileCreated,$_fileName,$_fileId)
    {
        $result = (($_permission==PERMISSION_VOID)?" (<!--lang_client_rejected-->)":($_permission!=PERMISSION_FULL && empty($_download))?" (<!--lang_client_rejected-->)":" (" . LIVEZILLA_URL . "getfile.php?id=" . $_fileId . ")");
        return "| " . date("d.m.Y H:i:s",$_fileCreated) . " | " . $_externalFullname .  ": " . html_entity_decode(strip_tags($_fileName),ENT_COMPAT,"UTF-8") . $result;
    }

    function GetHTMLFile($_permission,$_download,$_externalFullname,$_fileCreated,$_fileName,$_fileId)
    {
        $post = (($_permission==PERMISSION_VOID)?" (<!--lang_client_rejected-->)":($_permission!=PERMISSION_FULL && empty($_download))? $_fileName . " (<!--lang_client_rejected-->)":"<a class=\"lz_chat_file\" href=\"". LIVEZILLA_URL . "getfile.php?id=" . $_fileId ."\" target=_\"blank\">" . $_fileName. "</a>");
        $file = getFile(TEMPLATE_HTML_MESSAGE_INTERN);
        $html = str_replace("<!--dir-->","ltr",$file);
        $html = str_replace("<!--message-->",$post,$html);
        $html = str_replace("<!--name-->",$_externalFullname,$html);
        $html = str_replace("<!--time-->",date(DATE_RFC822,$_fileCreated),$html);
        return $html;
    }

    function GetPlainForward($_created,$_targetOperatorId,$_targetGroupId)
    {
        global $GROUPS,$INTERNAL;
        if(!empty($INTERNAL[$_targetOperatorId]))
            return "| " . date("d.m.Y H:i:s",$_created) . " | <!--lang_client_forwarding_to--> " . $INTERNAL[$_targetOperatorId]->Fullname . " ...";
        else
            return "| " . date("d.m.Y H:i:s",$_created) . " | <!--lang_client_forwarding_to--> " . $GROUPS[$_targetGroupId]->Description . " ...";
    }

    function GetHTMLForward($_created,$_senderOperatorId,$_targetOperatorId,$_targetGroupId)
    {
        global $GROUPS,$INTERNAL;
        if(!empty($INTERNAL[$_targetOperatorId]))
            $post = "<!--lang_client_forwarding_to--> " . $INTERNAL[$_targetOperatorId]->Fullname . " ...";
        else
            $post = "<!--lang_client_forwarding_to--> " . $GROUPS[$_targetGroupId]->Description . " ...";

        $file = getFile(TEMPLATE_HTML_MESSAGE_EXTERN);
        $html = str_replace("<!--dir-->","ltr",$file);
        $html = str_replace("<!--message-->",$post,$html);
        $html = str_replace("<!--name-->",$INTERNAL[$_senderOperatorId]->Fullname,$html);
        $html = str_replace("<!--time-->",date(DATE_RFC822,$_created),$html);
        return $html;
    }

    function Generate($_chatId,$_externalFullname,$_plain=false,$_html=false,$_question="",$_startTime=0, $firstpost="")
    {
        $this->FirstPost = time();
        $entries_html = array();
        $entries_plain = array();
        $_externalFullname = (empty($_externalFullname)) ? "<!--lang_client_guest-->" : $_externalFullname;

        $result_posts = queryDB(true,"SELECT `sender_name`,`text`,`sender`,`time`,`micro`,`translation` FROM `".DB_PREFIX.DATABASE_POSTS."` WHERE `repost`=0 AND `receiver`=`receiver_original` AND `chat_id` = '". @mysql_real_escape_string($_chatId)."' GROUP BY `id` ORDER BY `time` ASC, `micro` ASC;");
        while($row_post = mysql_fetch_array($result_posts, MYSQL_BOTH))
        {
            $this->ElementCount++;
            $this->FirstPost = min($this->FirstPost,$row_post["time"]);
            $firstpost = (empty($firstpost)) ? $row_post["text"] : $firstpost;
            $sender_name = (empty($row_post["sender_name"])) ? "<!--lang_client_guest-->" : $row_post["sender_name"];

            if($_plain)
                $entries_plain[$row_post["time"]."apost".str_pad($row_post["micro"],10,"0",STR_PAD_LEFT)] = $this->GetPlainPost($row_post["text"],$row_post["translation"],$row_post["time"],$sender_name);
            if($_html)
                $entries_html[$row_post["time"]."apost".str_pad($row_post["micro"],10,"0",STR_PAD_LEFT)] = $this->GetHTMLPost($row_post["text"],$row_post["translation"],$row_post["time"],$sender_name,$row_post["sender"]);
        }

        $result_files = queryDB(true,"SELECT `created`,`file_name`,`file_id`,`permission`,`download` FROM `".DB_PREFIX.DATABASE_CHAT_FILES."` WHERE `chat_id` = '". @mysql_real_escape_string($_chatId)."' ORDER BY `created` ASC;");
        while($row_file = mysql_fetch_array($result_files, MYSQL_BOTH))
        {
            $this->ElementCount++;
            $this->FirstPost = min($this->FirstPost,$row_file["created"]);
            if($_plain)
                $entries_plain[$row_file["created"]."bfile"] = $this->GetPlainFile($row_file["permission"],$row_file["download"],$_externalFullname,$row_file["created"],$row_file["file_name"],$row_file["file_id"]);
            if($_html)
                $entries_html[$row_file["created"]."bfile"] = $this->GetHTMLFile($row_file["permission"],$row_file["download"],$_externalFullname,$row_file["created"],$row_file["file_name"],$row_file["file_id"]);
        }

        $result_forwards = queryDB(true,"SELECT `initiator_operator_id`,`invite`,`target_group_id`,`target_operator_id`,`created` FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `invite`=0 AND `chat_id` = '". @mysql_real_escape_string($_chatId)."' ORDER BY `created` ASC;");
        while($row_forward = mysql_fetch_array($result_forwards, MYSQL_BOTH))
        {
            $this->ElementCount++;
            $this->FirstPost = min($this->FirstPost,$row_forward["created"]);
            if($_plain)
                $entries_plain[$row_forward["created"]."zforward"] = $this->GetPlainForward($row_forward["created"],$row_forward["target_operator_id"],$row_forward["target_group_id"]);
            if($_html)
                $entries_html[$row_forward["created"]."zforward"] = $this->GetHTMLForward($row_forward["created"],$row_forward["initiator_operator_id"],$row_forward["target_operator_id"],$row_forward["target_group_id"]);
        }

        ksort($entries_plain);
        foreach($entries_plain as $row)
        {
            if(!empty($this->PlainText))
                $this->PlainText .= "\r\n";
            $this->PlainText .= trim($row);
        }

        ksort($entries_html);
        foreach($entries_html as $row)
        {
            if(!empty($this->HTML))
                $this->HTML .= "<br>";
            $this->HTML .= trim($row);
        }

        if(!empty($_question) && $firstpost != $_question && !empty($_externalFullname))
            $this->HTML = $this->GetHTMLPost($_question,"",$_startTime,$_externalFullname,$_externalFullname) . $this->HTML;

        if(!empty($this->HTML))
            $this->HTML = "<div style=\"margin:10px 6px 6px 6px;width:100%;\">".$this->HTML."</div>";
    }
}

class FilterList
{
	public $Filters;
	public $Message;
	
	function FilterList()
   	{
		$this->Filters = Array();
		$this->Populate();
   	}
	
	function Populate()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_FILTERS."`;"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$filter = new Filter($row["id"]);
				$filter->SetValues($row);
				$this->Filters[$filter->Id] = $filter;
			}
	}
	
	function Match($_ip,$_languages,$_userid,$_chat=false)
	{
        global $DEFAULT_BROWSER_LANGUAGE;
		foreach($this->Filters as $filter)
		{
			if($filter->Activestate == FILTER_TYPE_INACTIVE)
				continue;
				
			if($_chat && $filter->AllowChats)
				continue;
			
			$this->Message = $filter->Reason;
			$compare["match_ip"] = jokerCompare($filter->IP,$_ip);
			if(empty($DEFAULT_BROWSER_LANGUAGE))
				$compare["match_lang"] = $this->LangCompare($_languages,$filter->Languages);
			else
				$compare["match_lang"] = $this->LangCompare($DEFAULT_BROWSER_LANGUAGE,$filter->Languages);
			$compare["match_id"] = ($filter->Userid == $_userid);
			if($compare["match_ip"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activeipaddress == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if(!$compare["match_ip"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activeipaddress == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if($compare["match_lang"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activelanguage == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if(!$compare["match_lang"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activelanguage == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if($compare["match_id"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activeuserid == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			else if(!$compare["match_id"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activeuserid == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->Id);
			if(defined("ACTIVE_FILTER_ID"))
				return true;
		}
		return false;
	}
	
	function IpCompare($_ip, $_comparer)
	{
		$array_ip = explode(".",$_ip);
		$array_comparer = explode(".",$_comparer);
		if(count($array_ip) == 4 && count($array_comparer) == 4)
		{
			foreach($array_ip as $key => $octet)
			{
				if($array_ip[$key] != $array_comparer[$key])
				{
					if($array_comparer[$key] == -1)
						return true;
					return false;
				}
			}
			return true;
		}
		else
			return false;
	}
	
	function LangCompare($_lang, $_comparer)
	{
		$array_lang = explode(",",$_lang);
		$array_comparer = explode(",",$_comparer);
		foreach($array_lang as $key => $lang)
			foreach($array_comparer as $keyc => $langc)
				if(strtoupper($array_lang[$key]) == strtoupper($langc))
					return true;
		return false;
	}
}

class EventList
{
	public $Events;
	
	function EventList()
   	{
		$this->Events = Array();
   	}
	function GetActionById($_id)
	{
		foreach($this->Events as $event)
		{
			foreach($event->Actions as $action)
				if($action->Id == $_id)
					return $action;
		}
		return null;
	}
}

class HistoryUrl
{
	public $Url;
	public $Referrer;
	public $Entrance;
	public static $SearchEngines = array("s"=>array("*nigma*"),"blocked"=>array("*doubleclick.net*"),"q"=>array("*search.*","*searchatlas*","*suche.*","*google.*","*bing.*","*ask*","*alltheweb*","*altavista*","*gigablast*"),"p"=>array("*search.yahoo*"),"query"=>array("*hotbot*","*lycos*"),"key"=>array("*looksmart*"),"text"=>array("*yandex*"),"wd"=>array("*baidu.*"),"searchTerm"=>array("*search.*"),"debug"=>array("*127.0.0.1*"));
	public static $SearchEngineEncodings = array("gb2312"=>array("*baidu.*"));
	public static $ExternalCallers = array("*.google.*","*.googleusercontent.*","*.translate.ru*","*.youdao.com*","*.bing.*","*.yahoo.*");
	
	function HistoryURL()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Url = new BaseURL($_row["url_dom"],$_row["url_path"],$_row["url_area_code"],$_row["url_title"]);
			$this->Url->Params = $_row["params"];
			$this->Url->Untouched = $_row["untouched"];
			$this->Url->MarkInternal();
			$this->Referrer = new BaseURL($_row["ref_dom"],$_row["ref_path"],$_row["ref_area_code"],$_row["ref_title"]);
			$this->Referrer->Untouched = $_row["ref_untouched"];
			$this->Entrance = $_row["entrance"];
		}
		else
		{
			$this->Url = new BaseURL(func_get_arg(0));
			$this->Url->AreaCode = func_get_arg(1);
			$this->Url->PageTitle = cutString(func_get_arg(2),255);
			$this->Url->MarkInternal();
			$this->Referrer = new BaseURL(func_get_arg(3));
			$this->Entrance = func_get_arg(4);
		}
	}
	
	function Destroy($_browserId)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` WHERE `browser_id`='".@mysql_real_escape_string($_browserId)."' AND `entrance`='".@mysql_real_escape_string($this->Entrance)."' LIMIT 1;");
	}
	
	function Save($_browserId,$_entrance)
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` SET `is_exit`=0 WHERE `browser_id`='".@mysql_real_escape_string($_browserId)."';");
		queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_BROWSER_URLS."` (`browser_id`, `entrance`, `referrer`, `url`, `params`, `untouched`, `title`, `ref_untouched`, `is_entrance`, `is_exit`) VALUES ('".@mysql_real_escape_string($_browserId)."', '".@mysql_real_escape_string($this->Entrance)."', '".@mysql_real_escape_string($this->Referrer->Save())."', '".@mysql_real_escape_string($this->Url->Save())."', '".@mysql_real_escape_string($this->Url->Params)."', '".@mysql_real_escape_string($this->Url->Untouched)."', '".@mysql_real_escape_string($this->Url->PageTitle)."', '".@mysql_real_escape_string($this->Referrer->Untouched)."', ".@mysql_real_escape_string($_entrance ? 1 : 0).", 1);");
	}
}

class BaseURL
{
	public $Path = "";
	public $Params = "";
	public $Domain = "";
	public $AreaCode = "";
	public $PageTitle = "";
	public $IsExternal = true;
	public $IsSearchEngine = false;
	public $Excluded;
	public $Untouched = "";

	function BaseURL($_url)
	{
		global $CONFIG;
		if(func_num_args() == 1)
		{
			if(!isnull(func_get_arg(0)))
			{
				$this->Untouched = func_get_arg(0);
				$parts = $this->ParseURL($this->Untouched);
				$this->Domain = $parts[0];
				$this->Path = substr($parts[1],0,255);
				$this->Params = $parts[2];
			}
			else
				$this->MarkInternal();
		}
		else
		{
			$this->Domain = func_get_arg(0);
			$this->Path = func_get_arg(1);
			$this->AreaCode = func_get_arg(2);
			$this->PageTitle = cutString(func_get_arg(3),255);
		}
		
		$domains = explode(",",$CONFIG["gl_doma"]);
		if(!empty($CONFIG["gl_doma"]) && !empty($this->Domain) && is_array($domains))
		{
			foreach($domains as $bldom)
			{
				$match = jokerCompare($bldom,$this->Domain);
				if((!empty($CONFIG["gl_bldo"]) && $match) || (empty($CONFIG["gl_bldo"]) && !$match))
				{
					$this->Excluded = true;
					break;
				}
			}
		}
	}
	
	function GetAbsoluteUrl()
	{
		if(!empty($this->Untouched))
			return $this->Untouched;
		else
			return $this->Domain . $this->Path;
	}

	function Save()
	{
		if($this->IsExternal)
			$pid = getValueId(DATABASE_VISITOR_DATA_PATHS,"path",$this->Path.$this->Params,false,255);
		else
			$pid = getValueId(DATABASE_VISITOR_DATA_PATHS,"path",$this->Path,false,255);
		$did = $this->GetDomainId();
		$cid = getValueId(DATABASE_VISITOR_DATA_AREA_CODES,"area_code",$this->AreaCode);
		$tid = $this->GetTitleId($did,$pid,$cid);
		queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` (`id`, `path`, `domain`,  `title`, `area_code`) VALUES (NULL, '".@mysql_real_escape_string($pid)."',  '".@mysql_real_escape_string($did)."',  '".@mysql_real_escape_string($tid)."', '".@mysql_real_escape_string($cid)."');");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id`,`title` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` WHERE `path`='".@mysql_real_escape_string($pid)."' AND `domain`='".@mysql_real_escape_string($did)."';"), MYSQL_BOTH);
		if(STATS_ACTIVE && $tid != $row["title"])
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_DATA_PAGES."` SET `title`=(SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` WHERE id='".@mysql_real_escape_string($tid)."' OR id='".@mysql_real_escape_string($row["title"])."' ORDER BY `confirmed` DESC LIMIT 1) WHERE `path`='".@mysql_real_escape_string($pid)."' AND `domain`='".@mysql_real_escape_string($did)."';");
		return $row["id"];
	}
	
	function MarkInternal()
	{
		foreach(HistoryUrl::$ExternalCallers as $value)
			if(jokerCompare($value,$this->Domain))
				return;
		$this->IsExternal = false;
	}
	
	function MarkSearchEngine()
	{
		$this->IsSearchEngine = true;
		$this->Params =
		$this->Path = "";
	}
	
	function GetTitleId()
	{
		queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` (`id`, `title`) VALUES (NULL, '".@mysql_real_escape_string($this->PageTitle)."');");
		if(STATS_ACTIVE && !empty($this->PageTitle))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` SET `confirmed`=`confirmed`+1 WHERE `title`='".@mysql_real_escape_string($this->PageTitle)."' LIMIT 1;");
		$row = mysql_fetch_array(queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_TITLES."` WHERE `title`='".@mysql_real_escape_string($this->PageTitle)."';"), MYSQL_BOTH);
		return $row["id"];
	}
	
	function GetDomainId()
	{
		queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` (`id`, `domain`, `search`) VALUES (NULL, '".@mysql_real_escape_string($this->Domain)."', '".@mysql_real_escape_string((!$this->IsExternal && $this->IsSearchEngine)?1:0)."');");
		if(!$this->IsExternal)
		{
			$row = mysql_fetch_array(queryDB(true,"SELECT `id`,`external`,`search` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';"), MYSQL_BOTH);
			if(!empty($row["external"]))
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` SET `external`=0 WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';");
			}
		}
		else
		{
			$row = mysql_fetch_array(queryDB(true,"SELECT `id`,`search` FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';"), MYSQL_BOTH);
		}
		if($this->IsExternal && $this->IsSearchEngine && empty($row["search"]))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` SET `search`=1 WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';");
		return $row["id"];
	}
	
	function IsInternalDomain()
	{
		$row = mysql_fetch_array($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_VISITOR_DATA_DOMAINS."` WHERE `domain`='".@mysql_real_escape_string($this->Domain)."';"), MYSQL_BOTH);
		if(mysql_num_rows($result) == 1 && empty($row["external"]))
			return true;
		return false;
	}
	
	function ParseURL($_url,$allowedParams="",$cutParams="",$domain="",$path="")
	{
		$allowed = (STATS_ACTIVE) ? StatisticProvider::$AllowedParameters : array();
		$igfilenames = (STATS_ACTIVE) ? StatisticProvider::$HiddenFilenames : array();
		$parts = parse_url(str_replace("///","//",$_url));
		$uparts = explode("?",$_url);
		if(count($allowed)>0 && count($uparts)>1)
		{
			$pparts = explode("&",$uparts[1]);
			foreach($pparts as $part)
			{
				$paramparts = explode("=",$part);
				if(in_array(strtolower($paramparts[0]),$allowed))
				{
					if(empty($allowedParams))
						$allowedParams .= "?";
					else
						$allowedParams .= "&";
						
					$allowedParams .= $paramparts[0];
					if(count($paramparts)>1)
						$allowedParams .= "=".$paramparts[1];
				}
				else
				{
					if(!empty($cutParams))
						$cutParams .= "&";
					$cutParams .= $paramparts[0];
					if(count($paramparts)>1)
						$cutParams .= "=".$paramparts[1];
				}
			}
		}
		if(!empty($cutParams) && empty($allowedParams))
			$cutParams = "?" . $cutParams;
		else if(!empty($cutParams) && !empty($allowedParams))
			$cutParams = "&" . $cutParams;
		else if(empty($cutParams) && empty($allowedParams) && count($uparts) > 1)
			$cutParams = "?" . $uparts[1];
			
		$partsb = @explode($parts["host"],$_url);
		
		if(!isset($parts["host"]))
			$parts["host"] = "localhost";
		
		$domain = $partsb[0].$parts["host"];
		$path = substr($uparts[0],strlen($domain),strlen($uparts[0])-strlen($domain));
		$path = str_replace($igfilenames,"",$path);
		return array($domain,$path.$allowedParams,$cutParams);
	}
}

class Filter extends BaseObject
{
	public $IP;
	public $Expiredate;
	public $Userid;
	public $Reason;
	public $Filtername;
	public $Activestate;
	public $Exertion;
	public $Languages;
	public $Activeipaddress;
	public $Activeuserid;
	public $Activelanguage;
	public $AllowChats;
	
	function Filter($_id)
   	{
		$this->Id = $_id;
		$this->Edited = time();
   	}
	
	function GetXML()
	{
		return "<val active=\"".base64_encode($this->Activestate)."\" ac=\"".base64_encode(($this->AllowChats) ? "1" : "0")."\" edited=\"".base64_encode($this->Edited)."\" editor=\"".base64_encode($this->Editor)."\" activeipaddresses=\"".base64_encode($this->Activeipaddress)."\" activeuserids=\"".base64_encode($this->Activeuserid)."\" activelanguages=\"".base64_encode($this->Activelanguage)."\" expires=\"".base64_encode($this->Expiredate)."\" creator=\"".base64_encode($this->Creator)."\" created=\"".base64_encode($this->Created)."\" userid=\"".base64_encode($this->Userid)."\" ip=\"".base64_encode($this->IP)."\" filtername=\"".base64_encode($this->Filtername)."\" filterid=\"".base64_encode($this->Id)."\" reason=\"".base64_encode($this->Reason)."\" exertion=\"".base64_encode($this->Exertion)."\" languages=\"".base64_encode($this->Languages)."\" />\r\n";
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_FILTERS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
			$this->SetValues($row);
	}
	
	function SetValues($_row)
	{
		$this->Creator = $_row["creator"];
		$this->Created = $_row["created"];
		$this->Editor = $_row["editor"];
		$this->Edited = $_row["edited"];
		$this->IP = $_row["ip"];
		$this->Expiredate = $_row["expiredate"];
		$this->Userid = $_row["visitor_id"];
		$this->Reason = $_row["reason"];
		$this->Filtername = $_row["name"];
		$this->Id = $_row["id"];
		$this->Activestate = $_row["active"];
		$this->Exertion = $_row["exertion"];
		$this->Languages = $_row["languages"];
		$this->Activeipaddress = $_row["activeipaddress"];
		$this->Activeuserid = $_row["activevisitorid"];
		$this->Activelanguage = $_row["activelanguage"];
		$this->AllowChats = !empty($_row["allow_chats"]);
	}
	
	function Save()
	{
		$this->Destroy();
		queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_FILTERS."` (`creator`, `created`, `editor`, `edited`, `ip`, `expiredate`, `visitor_id`, `reason`, `name`, `id`, `active`, `exertion`, `languages`, `activeipaddress`, `activevisitorid`, `activelanguage`, `allow_chats`) VALUES ('".@mysql_real_escape_string($this->Creator)."', '".@mysql_real_escape_string($this->Created)."','".@mysql_real_escape_string($this->Editor)."', '".@mysql_real_escape_string($this->Edited)."','".@mysql_real_escape_string($this->IP)."', '".@mysql_real_escape_string($this->Expiredate)."','".@mysql_real_escape_string($this->Userid)."', '".@mysql_real_escape_string($this->Reason)."','".@mysql_real_escape_string($this->Filtername)."', '".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->Activestate)."', '".@mysql_real_escape_string($this->Exertion)."','".@mysql_real_escape_string($this->Languages)."', '".@mysql_real_escape_string($this->Activeipaddress)."','".@mysql_real_escape_string($this->Activeuserid)."', '".@mysql_real_escape_string($this->Activelanguage)."', ".(($this->AllowChats) ? 1 : 0).");");
	}
	
	function Destroy()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_FILTERS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class Rating extends Action
{
	public $Fullname = "";
	public $Email="";
	public $Company="";
	public $InternId="";
	public $UserId="";
	public $RateQualification=0;
	public $RatePoliteness=0;
	public $RateComment=0;
    public $ChatId=0;

	function Rating()
	{
		$this->Id = func_get_arg(0);
		if(func_num_args() == 2)
		{
			$row = func_get_arg(1);
			$this->RateComment = $row["comment"];
			$this->RatePoliteness = $row["politeness"];
			$this->RateQualification = $row["qualification"];
			$this->Fullname = $row["fullname"];
			$this->Email = $row["email"];
			$this->Company = $row["company"];
			$this->InternId = $row["internal_id"];
			$this->UserId = $row["user_id"];
			$this->Created = $row["time"];
            $this->ChatId = $row["chat_id"];
		}
	}
	
	function IsFlood()
	{
		return isRatingFlood();
	}
	
	function GetXML($_internal,$_full)
	{
		if($_full)
		{
			$intern = (isset($_internal[getInternalSystemIdByUserId($this->InternId)])) ? $_internal[getInternalSystemIdByUserId($this->InternId)]->Fullname : $this->InternId;
			return "<val id=\"".base64_encode($this->Id)."\" cr=\"".base64_encode($this->Created)."\" rc=\"".base64_encode($this->RateComment)."\" rp=\"".base64_encode($this->RatePoliteness)."\" rq=\"".base64_encode($this->RateQualification)."\" fn=\"".base64_encode($this->Fullname)."\" em=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" ii=\"".base64_encode($intern)."\" ui=\"".base64_encode($this->UserId)."\" />\r\n";
		}
		else
			return "<val id=\"".base64_encode($this->Id)."\" cr=\"".base64_encode($this->Created)."\" />\r\n";
	}
}

class TicketEditor extends BaseObject
{
    public $GroupId = "";

	function TicketEditor()
	{
		$this->Id = func_get_arg(0);
		if(func_num_args() == 2)
		{
			$row = func_get_arg(1);
			$this->Editor = $row["editor_id"];
            $this->GroupId = $row["group_id"];
		}
	}
	
	function GetXML($_time,$_status)
	{
		return "<cl id=\"".base64_encode($this->Id)."\" st=\"".base64_encode($_status)."\" ed=\"".base64_encode($this->Editor)."\" g=\"".base64_encode($this->GroupId)."\" ti=\"".base64_encode($_time)."\"/>\r\n";
	}
	
	function Save()
	{
		queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_TICKET_EDITORS."` SET `editor_id`='".@mysql_real_escape_string($this->Editor)."',`group_id`='".@mysql_real_escape_string($this->GroupId)."',`status`='".@mysql_real_escape_string($this->Status)."',`time`='".@mysql_real_escape_string(time())."' WHERE `ticket_id`='".@mysql_real_escape_string($this->Id)."';");
		if(@mysql_affected_rows() <= 0)
			queryDB(false,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_TICKET_EDITORS."` (`ticket_id` ,`editor_id` ,`group_id` ,`status`,`time`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->Editor)."','".@mysql_real_escape_string($this->GroupId)."', '".@mysql_real_escape_string($this->Status)."', '".@mysql_real_escape_string(time())."');");
	}
}

class TicketChat extends TicketMessage
{
    function TicketChat()
    {
        global $INPUTS;

        $this->Id = func_get_arg(1);
        if(func_num_args() == 3)
        {
            $this->ChannelId = func_get_arg(0);
            $this->Type = "2";
        }
        else
        {
            $row = func_get_arg(0);
            $this->Text = str_replace(array("%eemail%","%efullname%"),array($row["email"],$row["fullname"]),$row["transcript_text"]);
            $this->Type = "2";
            $this->Fullname = $row["fullname"];
            $this->Email = $row["email"];
            $this->Company = $row["company"];
            $this->ChannelId = $row["chat_id"];
            $this->IP = $row["ip"];
            $this->SenderUserId = $row["external_id"];
            $this->Edited = time();
            $this->Created = $row["time"];
            $this->Country = $row["iso_country"];
            $this->Phone = $row["phone"];

            if(!empty($row["customs"]))
                foreach(@unserialize($row["customs"]) as $custname => $value)
                    foreach($INPUTS as $input)
                        if($input->Name == $custname && $input->Active && $input->Custom)
                            $this->Customs[$input->Index] = $value;
        }
    }
}

class TicketMessage extends Action
{
	public $Type = 0;
	public $Fullname = "";
	public $Email = "";
	public $Phone = "";
	public $Company = "";
	public $IP = "";
	public $Customs= "";
	public $Country= "";
	public $CallMeBack = false;
	public $ChannelId = "";
	public $Attachments = array();
    public $Edited = 0;
    public $Hash = "";

	function TicketMessage()
	{
		if(func_num_args() == 2)
		{
			$this->Id = func_get_arg(0);
		}
		else
		{
			$row = func_get_arg(0);
			$this->Id = $row["id"];
			$this->Text = $row["text"];
			$this->Type = $row["type"];
			$this->Fullname = $row["fullname"];
		 	$this->Email = $row["email"];
			$this->Company = $row["company"];
			$this->ChannelId = $row["channel_id"];
			$this->IP = $row["ip"];
			$this->Edited = $row["time"];
            $this->Created = $row["created"];
			$this->Country = $row["country"];
			$this->Phone = $row["phone"];
            $this->Hash = $row["hash"];
			$this->SenderUserId = $row["sender_id"];
			$this->CallMeBack = !empty($row["call_me_back"]);
		}
	}
	
	function ApplyAttachment($_id)
	{
    	queryDB(true,$d = "REPLACE INTO `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."` (`parent_id`,`res_id`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($_id)."');");
	}
	
	function Save($_ticketId,$_overwrite=false)
	{
		global $INPUTS;
        $time = time();
        while(true)
        {
            queryDB(true, "SELECT `time` FROM `" . DB_PREFIX . DATABASE_TICKET_MESSAGES . "` WHERE `time`=" . @mysql_real_escape_string($time) . ";");
            if (@mysql_affected_rows() > 0)
                $time++;
            else
                break;
        }

        if(empty($this->Created))
            $this->Created = $time;

        $do = ($_overwrite) ? "REPLACE" : "INSERT";
        $errorCode = -1;

        $result = queryDB(true, $do . " INTO `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` (`id` ,`time` ,`created` ,`ticket_id` ,`text` ,`fullname` ,`email` ,`company` ,`ip`, `phone` ,`call_me_back`,`country`,`type`,`sender_id`,`channel_id`,`hash`) VALUES ('".@mysql_real_escape_string($this->Id)."', ".@mysql_real_escape_string($time).",".@mysql_real_escape_string($this->Created).", '".@mysql_real_escape_string($_ticketId)."', '".@mysql_real_escape_string($this->Text)."', '".@mysql_real_escape_string($this->Fullname)."', '".@mysql_real_escape_string($this->Email)."', '".@mysql_real_escape_string($this->Company)."', '".@mysql_real_escape_string($this->IP)."', '".@mysql_real_escape_string($this->Phone)."', ". (($this->CallMeBack) ? 1 : 0).", '".@mysql_real_escape_string($this->Country)."', '".@mysql_real_escape_string($this->Type)."', '".@mysql_real_escape_string($this->SenderUserId)."', '".@mysql_real_escape_string($this->ChannelId)."', '".@mysql_real_escape_string($this->Hash)."');",false,$errorCode);

        if(!$result && $errorCode == 1366)
            $result = queryDB(true, $do . " INTO `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` (`id` ,`time` ,`created` ,`ticket_id` ,`text` ,`fullname` ,`email` ,`company` ,`ip`, `phone` ,`call_me_back`,`country`,`type`,`sender_id`,`channel_id`,`hash`) VALUES ('".@mysql_real_escape_string($this->Id)."', ".@mysql_real_escape_string($time).",".@mysql_real_escape_string($this->Created).", '".@mysql_real_escape_string($_ticketId)."', '".@mysql_real_escape_string(utf8_encode($this->Text))."', '".@mysql_real_escape_string($this->Fullname)."', '".@mysql_real_escape_string($this->Email)."', '".@mysql_real_escape_string($this->Company)."', '".@mysql_real_escape_string($this->IP)."', '".@mysql_real_escape_string($this->Phone)."', ". (($this->CallMeBack) ? 1 : 0).", '".@mysql_real_escape_string($this->Country)."', '".@mysql_real_escape_string($this->Type)."', '".@mysql_real_escape_string($this->SenderUserId)."', '".@mysql_real_escape_string($this->ChannelId)."', '".@mysql_real_escape_string($this->Hash)."');",false,$errorCode);

        if($result)
        	if(is_array($this->Customs))
				foreach($this->Customs as $i => $value)
				    queryDB(true,"REPLACE INTO `".DB_PREFIX.DATABASE_TICKET_CUSTOMS."` (`ticket_id` ,`message_id`, `custom_id` ,`value`) VALUES ('".@mysql_real_escape_string($_ticketId)."','".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($INPUTS[$i]->Name)."', '".@mysql_real_escape_string($value)."');");
    }
	
	function LoadAttachments()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."` INNER JOIN `".DB_PREFIX.DATABASE_RESOURCES."` ON `".DB_PREFIX.DATABASE_RESOURCES."`.`id`=`".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."`.`res_id` WHERE `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."`.`parent_id`='".@mysql_real_escape_string($this->Id)."';");
		if($result)
			while($rowc = mysql_fetch_array($result, MYSQL_BOTH))
				$this->Attachments[$rowc["res_id"]] = $rowc["title"];
	}
	
	function LoadCustoms()
	{
		global $INPUTS;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_TICKET_CUSTOMS."` WHERE `message_id`='".@mysql_real_escape_string($this->Id)."';");
		if($result)
			while($rowc = mysql_fetch_array($result, MYSQL_BOTH))
				foreach($INPUTS as $input)
					if($input->Name == $rowc["custom_id"] && $input->Active)
						$this->Customs[$rowc["custom_id"]] = $input->GetClientValue($rowc["value"]);
	}
}

class TicketEmail
{
    public $Id = "";
    public $Name = "";
    public $Email = "";
    public $Subject = "";
    public $Body = "";
    public $Created = 0;
    public $Deleted = false;
    public $MailboxId = "";
    public $Edited = "";
    public $GroupId = "";
    public $ReplyTo = "";
    public $ReceiverEmail = "";
    public $Attachments = array();
    public $EditorId = "";

    function TicketEmail()
    {
        if(func_num_args() == 3)
        {
            $this->Id = func_get_arg(0);
            $this->Deleted = func_get_arg(1);
            $this->EditorId = func_get_arg(2);
        }
        else if(func_num_args() == 1)
        {
            $row = func_get_arg(0);
            $this->Id = $row["email_id"];
            $this->Name = $row["sender_name"];
            $this->Email = $row["sender_email"];
            $this->Subject = $row["subject"];
            $this->Body = $row["body"];
            $this->Created = $row["created"];
            $this->Deleted = !empty($row["deleted"]);
            $this->MailboxId = $row["mailbox_id"];
            $this->Edited = $row["edited"];
            $this->GroupId = $row["group_id"];
            $this->ReplyTo = $row["sender_replyto"];
            $this->ReceiverEmail = $row["receiver_email"];
            $this->EditorId = $row["editor_id"];
        }
    }

    function LoadAttachments()
    {
        $this->Attachments = array();
        $result = queryDB(true,"SELECT `res_id` FROM `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."` WHERE `parent_id`='".@mysql_real_escape_string($this->Id)."';");
        if($result)
            while($row = mysql_fetch_array($result, MYSQL_BOTH))
                $this->Attachments[] = getResource($row["res_id"]);
    }

    function SaveAttachment($_id)
    {
        queryDB(true,$d="REPLACE INTO `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."` (`res_id`, `parent_id`) VALUES ('".@mysql_real_escape_string($_id)."','".@mysql_real_escape_string($this->Id)."');");
    }

    function SetStatus()
    {
        $ownership = (!empty($this->EditorId)) ? "(editor_id='".@mysql_real_escape_string($this->EditorId)."' OR editor_id='') AND " : "";
        queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_TICKET_EMAILS."` SET `deleted`=".($this->Deleted ? 1 : 0).",`edited`=".(time()).",`editor_id`='".@mysql_real_escape_string($this->EditorId)."' WHERE ".$ownership."`email_id`='" . @mysql_real_escape_string($this->Id) . "' LIMIT 1;");
    }

    static function RemoveDiscardedAttachments($_emailId)
    {
        if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."` WHERE `parent_id`='".@mysql_real_escape_string($_emailId)."';"))
        {
            while($row = mysql_fetch_array($result, MYSQL_BOTH))
                processResource(CALLER_SYSTEM_ID,$row["res_id"],"",RESOURCE_TYPE_FILE_INTERNAL,"",true,100,1,0);
            queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."` WHERE `parent_id`='".@mysql_real_escape_string($_emailId)."';");
        }
    }

    function GetXML($_full=true)
    {
        if($this->Deleted)
            $xml = "<e id=\"".base64_encode($this->Id)."\" ed=\"".base64_encode($this->Edited)."\" ei=\"".base64_encode($this->EditorId)."\" d=\"".base64_encode($this->Deleted)."\">\r\n";
        else if($_full)
        {
            $xml = "<e id=\"".base64_encode($this->Id)."\" ei=\"".base64_encode($this->EditorId)."\" r=\"".base64_encode($this->ReceiverEmail)."\" g=\"".base64_encode($this->GroupId)."\" e=\"".base64_encode($this->Email)."\" rt=\"".base64_encode($this->ReplyTo)."\" ed=\"".base64_encode($this->Edited)."\" s=\"".base64_encode($this->Subject)."\" n=\"".base64_encode($this->Name)."\" c=\"".base64_encode($this->Created)."\" d=\"".base64_encode($this->Deleted)."\" m=\"".base64_encode($this->MailboxId)."\"><c>".base64_encode($this->Body)."</c>\r\n";
            foreach($this->Attachments as $res)
                $xml .= "<a n=\"".base64_encode($res["title"])."\">".base64_encode($res["id"])."</a>\r\n";
        }
        else
            $xml = "<e id=\"".base64_encode($this->Id)."\" ed=\"".base64_encode($this->Edited)."\">\r\n";
        return $xml . "</e>\r\n";
    }

    function Save()
    {
        if ($this->Deleted)
            $this->Destroy();
        else
        {
            $time = time();
            while(true)
            {
                queryDB(true, "SELECT `edited` FROM `" . DB_PREFIX . DATABASE_TICKET_EMAILS . "` WHERE `edited`=" . @mysql_real_escape_string($time) . ";");
                if (@mysql_affected_rows() > 0)
                    $time++;
                else
                    break;
            }
            queryDB(true, "REPLACE INTO `" . DB_PREFIX . DATABASE_TICKET_EMAILS . "` (`email_id`, `mailbox_id`, `sender_email`, `sender_name`,`sender_replyto`,`receiver_email`, `created`, `edited`, `deleted`, `subject`, `body`, `group_id`) VALUES ('" . @mysql_real_escape_string($this->Id) . "', '" . @mysql_real_escape_string($this->MailboxId) . "', '" . @mysql_real_escape_string($this->Email) . "', '" . @mysql_real_escape_string($this->Name) . "', '" . @mysql_real_escape_string($this->ReplyTo) . "','" . @mysql_real_escape_string($this->ReceiverEmail) . "', '" . @mysql_real_escape_string($this->Created) . "', '" . @mysql_real_escape_string($time) . "', '" . @mysql_real_escape_string($this->Deleted ? 1 : 0) . "', '" . @mysql_real_escape_string($this->Subject) . "', '" . @mysql_real_escape_string($this->Body) . "', '" . @mysql_real_escape_string($this->GroupId) . "');");
        }
    }

    function Destroy()
    {
        queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_TICKET_EMAILS."` SET `deleted`=1,`edited`='".time()."' WHERE `email_id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
        queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."` WHERE `parent_id`='".@mysql_real_escape_string($this->Id)."';");
        queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_RESOURCES."` SET `discard`=1 WHERE `id`='".@mysql_real_escape_string($this->Id)."';");
    }

    static function Exists($_id,$_inEmails=true,$_inMessages=true)
    {
        if($_inEmails)
        {
            $result = queryDB(true,"SELECT `email_id` FROM `".DB_PREFIX.DATABASE_TICKET_EMAILS."` WHERE `email_id`='".@mysql_real_escape_string($_id)."';");
            if($result && @mysql_num_rows($result) > 0)
                return true;
        }
        if($_inMessages)
        {
            $result = queryDB(true,"SELECT `channel_id` FROM `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE `channel_id`='".@mysql_real_escape_string($_id)."';");
            if($result && @mysql_num_rows($result) > 0)
                return true;
        }
        return false;
    }
}

class UserTicket extends Action
{
	public $Messages = array();
	public $Group = "";
	public $CreationType = 0;
    public $Language = "";
    public $Deleted = false;

	function UserTicket()
	{
		if(func_num_args() == 2)
		{
			$this->Id = func_get_arg(0);
			$this->Messages[0] = new TicketMessage(getId(32),true);
            $this->Language = strtoupper(func_get_arg(1));
		}
		else if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
			$this->Id = $row["ticket_id"];
			$this->Group = $row["target_group_id"];
			$this->CreationType = $row["creation_type"];
            $this->Language = $row["iso_language"];
            $this->Deleted = !empty($row["deleted"]);
			$this->Messages[0] = new TicketMessage($row);
		}
	}

    static function Exists($_hash, &$id, &$group, &$language)
    {
        $_hash = strtoupper(str_replace(array("[","]"),"",$_hash));
        $result = queryDB(true,"SELECT `dbt`.`id`,`dbt`.`target_group_id`,`dbt`.`iso_language` FROM `".DB_PREFIX.DATABASE_TICKETS."` AS `dbt` INNER JOIN `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` AS `dbm` ON `dbt`.`id`=`dbm`.`ticket_id` WHERE (`dbt`.`hash`='".@mysql_real_escape_string($_hash)."' OR `dbm`.`hash`='".@mysql_real_escape_string($_hash)."') AND `deleted`=0 LIMIT 1;");
        if($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
        {
            $id=$row["id"];
            $group=$row["target_group_id"];
            $language=$row["iso_language"];
        }
        return (@mysql_num_rows($result) == 1);
    }

	
	function GetHash($_brackets=false)
	{
		global $CONFIG;
		$hash = substr(strtoupper(md5($this->Id.$CONFIG["gl_lzid"])),0,12);
        return ($_brackets) ? "[" . $hash . "]" : $hash;
	}

	function GetXML($_full)
	{
		if($_full)
		{
			$xml = "<val id=\"".base64_encode($this->Id)."\" del=\"".base64_encode($this->Deleted ? "1" : "0")."\" gr=\"".base64_encode($this->Group)."\" l=\"".base64_encode($this->Language)."\" h=\"".base64_encode($this->GetHash())."\" t=\"".base64_encode($this->CreationType)."\">\r\n";
			foreach($this->Messages as $message)
			{
				$xml .= "<m id=\"".base64_encode($message->Id)."\" sid=\"".base64_encode($message->SenderUserId)."\" t=\"".base64_encode($message->Type)."\" c=\"".base64_encode($message->Country)."\" ci=\"".base64_encode($message->ChannelId)."\" ct=\"".base64_encode($message->Created)."\" e=\"".base64_encode($message->Edited)."\" p=\"".base64_encode($message->Phone)."\" cmb=\"".base64_encode(($message->CallMeBack) ? 1 : 0)."\" mt=\"".base64_encode($message->Text)."\" fn=\"".base64_encode($message->Fullname)."\" em=\"".base64_encode($message->Email)."\" co=\"".base64_encode($message->Company)."\" ui=\"".base64_encode($this->SenderUserId)."\" ip=\"".base64_encode($message->IP)."\">\r\n";
				if(is_array($message->Customs))
					foreach($message->Customs as $i => $value)
						$xml .= "<c id=\"".base64_encode($i)."\">".base64_encode($value)."</c>\r\n";
						
				if(is_array($message->Attachments))
					foreach($message->Attachments as $i => $value)
						$xml .= "<a id=\"".base64_encode($i)."\">".base64_encode($value)."</a>\r\n";
				$xml .= "</m>";
			}
			$xml .= "</val>";
		}
		else
		{
			foreach($this->Messages as $message)
			{
				$xml = "<val id=\"".base64_encode($this->Id)."\" e=\"".base64_encode($message->Edited)."\" />\r\n";
				break;
			}
		}
		return $xml;
	}

    function LinkChat($_chatId, $messageId)
    {
        $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_ARCHIVE."` WHERE `chat_id`='".@mysql_real_escape_string(trim($_chatId))."' AND `closed`>0 LIMIT 1;");
        if($row = mysql_fetch_array($result, MYSQL_BOTH))
            $chatref = new TicketChat($row, $messageId);
        else
            $chatref = new TicketChat($_chatId, $messageId, true);
        $chatref->Save($this->Id,true);
    }

    function LinkTicket($_linkTicketId)
    {
        $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_TICKETS."` INNER JOIN `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` ON `".DB_PREFIX.DATABASE_TICKETS."`.`id`=`".DB_PREFIX.DATABASE_TICKET_MESSAGES."`.`ticket_id` WHERE `ticket_id` = '".@mysql_real_escape_string($_linkTicketId)."'");
        while($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
        {
            $Ticket = new UserTicket($row);
            if(!$Ticket->Deleted)
            {
                $tm = $Ticket->Messages[0];
                $nid = getId(32);

                queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_TICKET_CUSTOMS."` SET `message_id`='".@mysql_real_escape_string($nid)."' WHERE `message_id` = '".@mysql_real_escape_string($tm->Id)."';");
                queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_TICKET_ATTACHMENTS."` SET `parent_id`='".@mysql_real_escape_string($nid)."' WHERE `parent_id` = '".@mysql_real_escape_string($tm->Id)."';");

                $tm->Id = $nid;
                $tm->ChannelId = getId(32);
                $tm->Save($this->Id);

                queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_TICKET_CUSTOMS."` SET `ticket_id`='".@mysql_real_escape_string($this->Id)."' WHERE `ticket_id` = '".@mysql_real_escape_string($_linkTicketId)."';");
                $Ticket->Destroy();
            }
        }
    }

    function SendEditorReply($mailbox, $_message, $_pdm, $_att=null, $_subject="")
    {
        if($mailbox != null)
        {
            if(empty($_subject))
            {
                $_subject = ($_pdm != null) ? $_pdm->SubjectTicket : "";
                $_subject = str_replace("%ticket_hash%",$this->GetHash(true),$_subject);
            }
            $_message = str_replace("%ticket_hash%",$this->GetHash(true),$_message);
            sendMail($mailbox,str_replace(";",",",$this->Messages[0]->Email),$mailbox->Email,$_message,getSubject($_subject,$this->Messages[0]->Email,$this->Messages[0]->Fullname,$this->Group,"",$this->Messages[0]->Company,$this->Messages[0]->Phone,$this->Messages[0]->IP,$this->Messages[0]->Text,$this->Messages[0]->Customs),false,$_att);
        }
    }
    
    function SendAutoresponder($mailbox, $_message, $_pdm, $_att=null, $_subject="")
    {
        global $CONFIG,$GROUPS;

        if($mailbox != null)
        {
            $replytoint = (isValidEmail($this->Messages[0]->Email)) ? $this->Messages[0]->Email : $mailbox->Email;
            $replytoex = $mailbox->Email;

            if(empty($_subject))
            {
                $_subject = ($_pdm != null) ? $_pdm->SubjectTicket : "";
                $_subject = str_replace("%ticket_hash%",$this->GetHash(true),$_subject);
            }

            $_message = str_replace("%ticket_hash%",$this->GetHash(true),$_message);

            if(!empty($CONFIG["gl_scom"]) /*&& ($CONFIG["gl_teh"]==2 || ($_outgoing && $CONFIG["gl_teh"]==1) || (!$_outgoing && $CONFIG["gl_teh"]==0)) */)
                sendMail($mailbox,$CONFIG["gl_scom"],$replytoint,$_message,getSubject($_subject,$this->Messages[0]->Email,$this->Messages[0]->Fullname,$this->Group,"",$this->Messages[0]->Company,$this->Messages[0]->Phone,$this->Messages[0]->IP,$this->Messages[0]->Text,$this->Messages[0]->Customs),$_att);
            if(!empty($CONFIG["gl_sgom"]))
                sendMail($mailbox,$GROUPS[$this->Group]->Email,$replytoint,$_message,getSubject($_subject,$this->Messages[0]->Email,$this->Messages[0]->Fullname,$this->Group,"",$this->Messages[0]->Company,$this->Messages[0]->Phone,$this->Messages[0]->IP,$this->Messages[0]->Text,$this->Messages[0]->Customs),$_att);
            if(!empty($CONFIG["gl_ssom"]) && isValidEmail($this->Messages[0]->Email))
                sendMail($mailbox,str_replace(";",",",$this->Messages[0]->Email),$replytoex,$_message,getSubject($_subject,$this->Messages[0]->Email,$this->Messages[0]->Fullname,$this->Group,"",$this->Messages[0]->Company,$this->Messages[0]->Phone,$this->Messages[0]->IP,$this->Messages[0]->Text,$this->Messages[0]->Customs),$_att);
        }
    }

    function GetUniqueMessageTime()
    {
        $time = time();
        while(true)
        {
            queryDB(true,"SELECT `time` FROM `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE `time`=".@mysql_real_escape_string($time).";");
            if(@mysql_affected_rows() > 0)
                $time++;
            else
                break;
        }
        return $time;
    }

	function Save()
	{
		if(queryDB(true,"INSERT IGNORE INTO `".DB_PREFIX.DATABASE_TICKETS."` (`id`,`user_id`,`target_group_id`,`hash`,`creation_type`,`iso_language`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->SenderUserId)."', '".@mysql_real_escape_string($this->Group)."', '".@mysql_real_escape_string($hash = $this->GetHash())."', '".@mysql_real_escape_string($this->CreationType)."', '".@mysql_real_escape_string($this->Language)."');"))
        {
            $this->Messages[0]->Hash = $hash;
            $this->Messages[0]->Save($this->Id);
        }
	}

    function UpdateMessageTime()
    {
        if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` WHERE `ticket_id`='".@mysql_real_escape_string($this->Id)."';"))
            while($row = mysql_fetch_array($result, MYSQL_BOTH))
                queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_TICKET_MESSAGES."` SET `time`=".$this->GetUniqueMessageTime()." WHERE `id` = '".@mysql_real_escape_string($row["id"])."' LIMIT 1;");
    }

    function SetLanguage($_language)
    {
        queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_TICKETS."` SET `iso_language` = '".@mysql_real_escape_string($_language)."' WHERE `id`= '".@mysql_real_escape_string($this->Id)."';");
        $this->UpdateMessageTime();
    }

    function Destroy()
    {
        queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_TICKETS."` SET `deleted`=1 WHERE `id` = '".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
        $this->UpdateMessageTime();
    }
}

class Response
{
	public $XML = "";
	public $Internals="";
	public $Groups="";
	public $InternalProfilePictures="";
	public $InternalWebcamPictures="";
	public $InternalVcards="";
	public $Typing="";
	public $Exceptions="";
	public $Filter="";
	public $Events="";
	public $EventTriggers="";
	public $Authentications="";
	public $Posts="";
	public $Login;
	public $Ratings="";
	public $Messages="";
	public $Archive="";
	public $Resources="";
	public $ChatVouchers="";
	public $GlobalHash;
	public $Actions="";
	public $Goals="";
	public $Forwards="";
	
	function SetStandardResponse($_code,$_sub)
	{
		$this->XML = "<response><value id=\"".base64_encode($_code)."\" />" . $_sub . "</response>";
	}
	
	function SetValidationError($_code,$_addition="")
	{
		if(!empty($_addition))
			$this->XML = "<validation_error value=\"".base64_encode($_code)."\" error=\"".base64_encode($_addition)."\" />";
		else
			$this->XML = "<validation_error value=\"".base64_encode($_code)."\" />";
	}
	
	function GetXML()
	{
		return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><livezilla_xml><livezilla_version>".base64_encode(VERSION)."</livezilla_version>" . $this->XML . "</livezilla_xml>";
	}
}

class FileEditor
{
	public $Result;
	public $TargetFile;
	
	function FileEditor($_file)
	{
		$this->TargetFile = $_file;
	}
	
	function Load()
	{
		if(file_exists($this->TargetFile))
		{
			$handle = @fopen ($this->TargetFile, "r");
			while (!@feof($handle))
	   			$this->Result .= @fgets($handle, 4096);
			
			$length = strlen($this->Result);
			$this->Result = @unserialize($this->Result);
			@fclose($handle);
		}
	}

	function Save($_data)
	{
		if(strpos($this->TargetFile,"..") === false)
		{
			$handle = @fopen($this->TargetFile, "w");
			if(!empty($_data))
				$length = @fputs($handle,serialize($_data));
			@fclose($handle);
		}
	}
}

class FileUploadRequest extends Action
{
	public $Error = false;
	public $Download = false;
	public $FileName;
	public $FileMask;
	public $FileId;
	public $Permission = PERMISSION_VOID;
	public $FirstCall = true;
	public $ChatId;
	public $Closed;
	
	function FileUploadRequest()
	{
		if(func_num_args() == 3)
		{
			$this->Id = func_get_arg(0);
			$this->ReceiverUserId = func_get_arg(1);
            $this->ChatId = func_get_arg(2);
			$this->Load();
		}
		else if(func_num_args() == 1)
		{
			$this->SetValues(func_get_arg(0));
		}
	}
	    
	function Save()
	{
		if($this->FirstCall)
			queryDB(true,"REPLACE INTO `".DB_PREFIX.DATABASE_CHAT_FILES."`  (`id` ,`created`,`file_name` ,`file_mask` ,`file_id` ,`chat_id`,`visitor_id` ,`browser_id` ,`operator_id`,`error` ,`permission` ,`download`,`closed`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($this->FileName)."', '".@mysql_real_escape_string($this->FileMask)."', '".@mysql_real_escape_string($this->FileId)."', '".@mysql_real_escape_string($this->ChatId)."', '".@mysql_real_escape_string($this->SenderUserId)."', '".@mysql_real_escape_string($this->SenderBrowserId)."', '".@mysql_real_escape_string($this->ReceiverUserId)."','".@mysql_real_escape_string(($this->Error)?1:0)."', '".@mysql_real_escape_string($this->Permission)."', '".@mysql_real_escape_string(($this->Download)?1:0)."', ".@mysql_real_escape_string(($this->Closed)?1:0).");");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_FILES."` SET `download`='".@mysql_real_escape_string(($this->Download)?1:0)."',`error`='".@mysql_real_escape_string(($this->Error) ? 1 : 0)."',`permission`='".@mysql_real_escape_string($this->Permission)."' WHERE `created`='".@mysql_real_escape_string($this->Created)."' ORDER BY `created` DESC LIMIT 1; ");
	}
	
	function Close()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_FILES."` SET `closed`=1 WHERE `id`='".@mysql_real_escape_string($this->Id)."' AND `created`='".@mysql_real_escape_string($this->Created)."';");
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FILES."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' AND `chat_id`='".@mysql_real_escape_string($this->ChatId)."' ORDER BY `created` DESC LIMIT 1;");
		if($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			$this->SetValues($row);
		}
		else
			$this->FirstCall = true;
	}
	
	function SetValues($row)
	{	
		$this->FirstCall = false;
		$this->Id = $row["id"];
		$this->FileName = $row["file_name"];
		$this->FileMask = $row["file_mask"];
		$this->FileId = $row["file_id"];
		$this->ChatId = $row["chat_id"];
		$this->SenderUserId = $row["visitor_id"];
		$this->SenderBrowserId = $row["browser_id"];
		$this->ReceiverUserId = $row["operator_id"];
		$this->Error = !empty($row["error"]);
		$this->Permission = $row["permission"];
		$this->Download = !empty($row["download"]);
		$this->Closed = !empty($row["closed"]);
		$this->Created = $row["created"];
	}
	
	function GetFile()
	{
		return PATH_UPLOADS . $this->FileMask;
	}
}

class Forward extends Action
{
	public $InitiatorSystemId;
	public $TargetSessId;
	public $TargetGroupId;
	public $Processed = false;
	public $Invite = false;
	public $ChatId;
	
	function Forward()
	{
		$this->Id = getId(32);
		if(func_num_args() == 2)
		{
			$this->ChatId = func_get_arg(0);
			$this->SenderSystemId = func_get_arg(1);
			$this->Load();
		}
		else if(func_num_args() == 1)
		{
			$this->SetValues(func_get_arg(0));
		}
	} 
	
	function Save($_processed=false,$_received=false)
	{
		if(!$_processed)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` (`id`, `created`, `initiator_operator_id`,`sender_operator_id`, `target_operator_id`, `target_group_id`, `chat_id`,`visitor_id`,`browser_id`, `info_text`, `invite`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->InitiatorSystemId)."','".@mysql_real_escape_string($this->SenderSystemId)."', '".@mysql_real_escape_string($this->TargetSessId)."', '".@mysql_real_escape_string($this->TargetGroupId)."', '".@mysql_real_escape_string($this->ChatId)."', '".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->ReceiverBrowserId)."', '".@mysql_real_escape_string($this->Text)."', '".@mysql_real_escape_string(($this->Invite) ? "1" : "0")."');");
		else if($_received)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` SET `received`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1; ");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` SET `processed`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1; ");
	}
	
	function Load()
	{
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' AND `received`=0 LIMIT 1;");
		if($result && $row = mysql_fetch_array($result, MYSQL_BOTH))
			$this->SetValues($row);
	}
	
	function SetValues($_row)
	{
		$this->Id = $_row["id"];
		$this->InitiatorSystemId = $_row["initiator_operator_id"];
		$this->SenderSystemId = $_row["sender_operator_id"];
		$this->TargetSessId = $_row["target_operator_id"];
		$this->TargetGroupId = $_row["target_group_id"];
		$this->ReceiverUserId = $_row["visitor_id"];
		$this->ReceiverBrowserId = $_row["browser_id"];
		$this->ChatId = $_row["chat_id"];
		$this->Created = $_row["created"];
		$this->Received = $_row["received"];
		$this->Text = $_row["info_text"];
		$this->Processed = !empty($_row["processed"]);
		$this->Invite = !empty($_row["invite"]);
	}
	
	function GetXml()
	{
		return "<fw id=\"".base64_encode($this->Id)."\" pr=\"".base64_encode(($this->Processed) ? "1" : "0")."\" cr=\"".base64_encode($this->Created)."\" u=\"".base64_encode($this->ReceiverUserId."~".$this->ReceiverBrowserId)."\" c=\"".base64_encode($this->ChatId)."\" i=\"".base64_encode($this->InitiatorSystemId)."\" s=\"".base64_encode($this->SenderSystemId)."\" t=\"".base64_encode($this->Text)."\" r=\"".base64_encode($this->TargetSessId)."\"  g=\"".base64_encode($this->TargetGroupId)."\" inv=\"".base64_encode(($this->Invite) ?  "1" : "0")."\" />\r\n";
	}
	
	function Destroy()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_FORWARDS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class WebsitePush extends Action
{
	public $TargetURL;
	public $Ask;
	public $ActionId;
	public $Senders;
	
	function WebsitePush()
	{
		if(func_num_args() == 7)
		{
			$this->Id = getId(32);
			$this->SenderSystemId = func_get_arg(0);
			$this->SenderGroupId = func_get_arg(1);
			$this->ReceiverUserId = func_get_arg(2);
			$this->BrowserId = func_get_arg(3);
			$this->Text = func_get_arg(4);
			$this->Ask = func_get_arg(5);
			$this->TargetURL = func_get_arg(6);
			$this->Senders = array();
		}
		else if(func_num_args() == 3)
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->TargetURL = func_get_arg(1);
			$this->Ask = func_get_arg(2);
			$this->Senders = array();
		}
		else if(func_num_args() == 2)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->Ask = $_row["ask"];
			$this->TargetURL = $_row["target_url"];
			$this->Senders = array();
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->SenderSystemId = $_row["sender_system_id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->Text = $_row["text"];
			$this->Ask = $_row["ask"];
			$this->TargetURL = $_row["target_url"];
			$this->Accepted = $_row["accepted"];
			$this->Declined = $_row["declined"];
			$this->Displayed = $_row["displayed"];
			$this->Senders = array();
		}
	}

	function SaveEventConfiguration()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_WEBSITE_PUSHS."` (`id`, `action_id`, `target_url`,`ask`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."','".@mysql_real_escape_string($this->TargetURL)."','".@mysql_real_escape_string($this->Ask)."');");
	}
	
	function SetStatus($_displayed,$_accepted,$_declined)
	{
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='0',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='1',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_declined)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='0',`declined`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` (`id`, `created`, `sender_system_id`, `receiver_user_id`, `receiver_browser_id`, `text`, `ask`, `target_url`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->SenderSystemId)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->Text)."','".@mysql_real_escape_string($this->Ask)."','".@mysql_real_escape_string($this->TargetURL)."');");
	}

	function GetInitCommand()
	{
		return "lz_tracking_init_website_push('".base64_encode(str_replace("%target_url%",$this->TargetURL,$this->Text))."',".time().");";
	}
	
	function GetExecCommand()
	{
		return "lz_tracking_exec_website_push('".base64_encode($this->TargetURL)."');";
	}
	
	function GetXML()
	{
		$xml = "<evwp id=\"".base64_encode($this->Id)."\" url=\"".base64_encode($this->TargetURL)."\" ask=\"".base64_encode($this->Ask)."\">\r\n";
		
		foreach($this->Senders as $sender)
			$xml .= $sender->GetXML();

		return $xml . "</evwp>\r\n";
	}
}

class EventActionInternal extends Action
{
	public $TriggerId;
	function EventActionInternal()
	{
		if(func_num_args() == 2)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->TriggerId = func_get_arg(1);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->TriggerId = $_row["trigger_id"];
			$this->EventActionId = $_row["action_id"];
		}
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."` (`id`, `created`, `trigger_id`, `receiver_user_id`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($this->TriggerId)."', '".@mysql_real_escape_string($this->ReceiverUserId)."');");
	}

	function GetXml()
	{
		return "<ia time=\"".base64_encode(time())."\" aid=\"".base64_encode($this->EventActionId)."\" />\r\n";
	}
}

class Alert extends Action
{
	function Alert()
	{
		if(func_num_args() == 3)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->BrowserId = func_get_arg(1);
			$this->Text = func_get_arg(2);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->Text = $_row["text"];
			$this->EventActionId = $_row["event_action_id"];
			$this->Displayed = !empty($_row["displayed"]);
			$this->Accepted = !empty($_row["accepted"]);
		}
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_ALERTS."` (`id`, `created`, `receiver_user_id`, `receiver_browser_id`,`event_action_id`, `text`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->EventActionId)."','".@mysql_real_escape_string($this->Text)."');");
	}
	
	function SetStatus($_displayed,$_accepted)
	{
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_ALERTS."` SET `displayed`='1',`accepted`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_ALERTS."` SET `displayed`='1',`accepted`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function GetCommand()
	{
		return "lz_tracking_send_alert('".$this->Id."','".base64_encode($this->Text)."');";
	}
}

class OverlayBox extends Action
{
	public $OverlayElement;
	function OverlayBox()
   	{
		if(func_num_args() == 3)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->BrowserId = func_get_arg(1);
			$parts = func_get_arg(2);
			$parts = explode(";",$parts);
			if($parts[0] == "1")
				$this->Text = base64_decode($parts[1]);
			else
				$this->URL = base64_decode($parts[1]);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->EventActionId = $_row["event_action_id"];
			$this->Text = $_row["content"];
			$this->URL = $_row["url"];
			$this->Displayed = !empty($_row["displayed"]);
			$this->Closed = !empty($_row["closed"]);
		}
	}
	
	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_OVERLAY_BOXES."` (`id`, `created`, `receiver_user_id`,`receiver_browser_id`,`event_action_id`, `url`,`content`, `displayed`, `closed`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->EventActionId)."','".@mysql_real_escape_string($this->URL)."','".@mysql_real_escape_string($this->Text)."',0,0);");
	}
	
	function CreateOverlayTemplate($_style,$_siteName,$_cwWidth,$_cwHeight,$_serverURL)
	{
		global $CONFIG;
		$fheight = (!empty($CONFIG["gl_pr_nbl"])) ? 10 : 20;
		$bheight = 12;
		$template = getFile(TEMPLATE_SCRIPT_OVERLAYS . $_style . "/content.tpl");
		$template = str_replace("<!--site_name-->",$_siteName,$template);
		$template = str_replace("<!--template-->",$_style,$template);
		$template = str_replace("<!--width-->",$_cwWidth,$template);
		$template = str_replace("<!--dleft-->",$_cwWidth-10,$template);
		$template = str_replace("<!--dtop-->",-35,$template);
		$template = str_replace("<!--height-->",$_cwHeight,$template);
		$template = str_replace("<!--server-->",$_serverURL,$template);
		$content = (empty($CONFIG["gl_pr_nbl"])) ? ("<table cellpadding=\"0\" cellspacing=\"0\" style=\"height:".($_cwHeight-$bheight)."px;width:100%;\"><tr><td style=\"height:".($_cwHeight-$fheight-$bheight)."px;vertical-align:top;\"><!--content--></td></tr><tr><td height=\"".$fheight."\" style=\"vertical-align:bottom;text-align:center;\">" . base64_decode("PGEgaHJlZj0iaHR0cDovL3d3dy5saXZlemlsbGEubmV0IiB0YXJnZXQ9Il9ibGFuayIgc3R5bGU9ImNvbG9yOiNBQUFBQUE7Zm9udC1zaXplOjlweDtmb250LWZhbWlseTp2ZXJkYW5hLGFyaWFsO3RleHQtYWxpZ246Y2VudGVyO3RleHQtZGVjb3JhdGlvbjpub25lOyI+UG93ZXJlZCBieSBMaXZlWmlsbGE8L2E+")."</td></tr></table>") : "<table cellpadding=\"0\" cellspacing=\"0\" style=\"height:".($_cwHeight-$bheight)."px;width:100%;\"><tr><td style=\"height:".($_cwHeight-$bheight)."px;vertical-align:top;\"><!--content--></td></tr></table>";
		if(!empty($this->URL))
			$template = str_replace(base64_decode("PCEtLWNvbnRlbnQtLT4="),str_replace(base64_decode("PCEtLWNvbnRlbnQtLT4="),"<iframe frameBorder=\"0\" style=\"padding:0px;margin:0px;border:0px;height:".($_cwHeight-$fheight-$bheight)."px;width:100%;\" src=\"".$this->URL."\"></iframe>",$content),$template);
		else
			$template = str_replace(base64_decode("PCEtLWNvbnRlbnQtLT4="),str_replace(base64_decode("PCEtLWNvbnRlbnQtLT4="),$this->Text,$content),$template);
		return $template;
	}
	
	function SetStatus($_closed=true)
	{
		if($_closed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_OVERLAY_BOXES."` SET `displayed`='1',`closed`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class ChatRequest extends Action
{
	public $Invitation;
	public $Canceled;
	function ChatRequest()
   	{
		if(func_num_args() == 5)
		{
			$this->Id = getId(32);
			$this->SenderSystemId = func_get_arg(0);
			$this->SenderGroupId = func_get_arg(1);
			$this->ReceiverUserId = func_get_arg(2);
			$this->BrowserId = func_get_arg(3);
			$this->Text = func_get_arg(4);
		}
		else if(func_num_args() == 2)
		{
			$this->Id = func_get_arg(0);
			$this->Load();
		}
		else
		{
			$row = func_get_arg(0);
			$this->SetValues($row);
		}
   	}
	
	function SetStatus($_displayed,$_accepted,$_declined,$_closed=false)
	{
		$_closed = ($_accepted || $_declined || $_closed);
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`accepted`='0',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`accepted`='1' WHERE `declined`=0 AND `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_declined)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`declined`='1' WHERE `accepted`=0 AND `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($_closed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `closed`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	public static function AcceptAll($_userId)
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($_userId)."';"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$request = new ChatRequest($row);
				$request->SetStatus(false,true,false,true);
				$browser = new VisitorBrowser($row["receiver_browser_id"],$_userId,false);
				$browser->ForceUpdate();
			}
	}
	
	function SetValues($_row)
	{
		$this->Id = $_row["id"];
		$this->SenderSystemId = $_row["sender_system_id"];
		$this->SenderUserId = $_row["sender_system_id"];
		$this->SenderGroupId = $_row["sender_group_id"];
		$this->ReceiverUserId = $_row["receiver_user_id"];
		$this->BrowserId = $_row["receiver_browser_id"];
		$this->EventActionId = $_row["event_action_id"];
		$this->Created = $_row["created"];
		$this->Text = $_row["text"];
		$this->Displayed = !empty($_row["displayed"]);
		$this->Accepted = !empty($_row["accepted"]);
		$this->Declined = !empty($_row["declined"]);
		$this->Closed = !empty($_row["closed"]);
		$this->Canceled = !empty($_row["canceled"]);
	
	}
	
	function Load()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."';"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->SetValues($row);
	}
	
	function Save()
	{
		global $INTERNAL,$GROUPS;
		if($INTERNAL[$this->SenderSystemId]->IsExternal($GROUPS,null,null,true))
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` (`id`, `created`, `sender_system_id`, `sender_group_id`,`receiver_user_id`, `receiver_browser_id`,`event_action_id`, `text`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->SenderSystemId)."','".@mysql_real_escape_string($this->SenderGroupId)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->EventActionId)."','".@mysql_real_escape_string($this->Text)."');");
	}
	
	function Destroy()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function CreateInvitationTemplate($_style,$_siteName,$_cwWidth,$_cwHeight,$_serverURL,$_sender,$_closeOnClick)
	{
		global $CONFIG;
		$template = ((!empty($CONFIG["gl_caii"])) && @file_exists(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation_header.tpl")) ? getFile(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation_header.tpl") : getFile(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation.tpl");
		$template = str_replace("<!--logo-->","<img src=\"". $CONFIG["gl_caii"]."\" border=\"0\">",$template);
		$template = str_replace("<!--site_name-->",$_siteName,$template);
		$template = str_replace("<!--intern_name-->",$_sender->Fullname,$template);
		$template = str_replace("<!--template-->",$_style,$template);
		$template = str_replace("<!--group_id-->",base64UrlEncode($this->SenderGroupId),$template);
		$template = str_replace("<!--user_id-->",base64UrlEncode($_sender->UserId),$template);
		$template = str_replace("<!--width-->",$_cwWidth,$template);
		$template = str_replace("<!--height-->",$_cwHeight,$template);
		$template = str_replace("<!--server-->",$_serverURL,$template);
		$template = str_replace("<!--intern_image-->",$_sender->GetOperatorPictureFile(),$template);
		$template = str_replace("<!--close_on_click-->",$_closeOnClick,$template);
		return $template;
	}
}

class OverlayElement extends BaseObject
{
	public $DisplayPosition = "11";
	public $Speed = 8;
	public $Effect = 5;
	public $Width;
	public $Height;
	public $Margin;
	public $CloseOnClick;
	public $HTML;
	public $Style = "classic";
	public $Shadow;
	public $ShadowPositionX;
	public $ShadowPositionY;
	public $ShadowBlur;
	public $ShadowColor;
	public $Background;
	public $BackgroundColor;
	public $BackgroundOpacity;
	
	function OverlayElement()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Style = $_row["style"];
			$this->Id = $_row["id"];
			$this->Position = $_row["position"];
			$this->Margin = Array($_row["margin_left"],$_row["margin_top"],$_row["margin_right"],$_row["margin_bottom"]);
			$this->Speed = $_row["speed"];
			$this->Effect = $_row["slide"];
			$this->CloseOnClick = $_row["close_on_click"];
			$this->Shadow = !empty($_row["shadow"]);
			$this->ShadowPositionX = $_row["shadow_x"];
			$this->ShadowPositionY = $_row["shadow_x"];
			$this->ShadowBlur = $_row["shadow_blur"];
			$this->ShadowColor = $_row["shadow_color"];
			$this->Width = $_row["width"];
			$this->Height = $_row["height"];
			$this->Background = !empty($_row["background"]);
			$this->BackgroundColor = $_row["background_color"];
			$this->BackgroundOpacity = $_row["background_opacity"];
		}
		else if(func_num_args() == 20)
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->Position = func_get_arg(1);
			$this->Margin = Array(func_get_arg(2),func_get_arg(3),func_get_arg(4),func_get_arg(5));
			$this->Speed = func_get_arg(6);
			$this->Style = func_get_arg(7);
			$this->Effect = func_get_arg(8);
			$this->CloseOnClick = func_get_arg(9);
			$this->Shadow = func_get_arg(10);
			$this->ShadowPositionX = func_get_arg(11);
			$this->ShadowPositionY = func_get_arg(12);
			$this->ShadowBlur = func_get_arg(13);
			$this->ShadowColor = func_get_arg(14);
			$this->Width = func_get_arg(15);
			$this->Height = func_get_arg(16);
			$this->Background = !isnull(func_get_arg(17));
			$this->BackgroundColor = func_get_arg(18);
			$this->BackgroundOpacity = func_get_arg(19);
		}
	}
	
	function GetXML()
	{
		return "<evolb id=\"".base64_encode($this->Id)."\" bgo=\"".base64_encode($this->BackgroundOpacity)."\" bgc=\"".base64_encode($this->BackgroundColor)."\" bg=\"".base64_encode($this->Background)."\" h=\"".base64_encode($this->Height)."\" w=\"".base64_encode($this->Width)."\" ml=\"".base64_encode($this->Margin[0])."\" mt=\"".base64_encode($this->Margin[1])."\" mr=\"".base64_encode($this->Margin[2])."\" mb=\"".base64_encode($this->Margin[3])."\" pos=\"".base64_encode($this->Position)."\" speed=\"".base64_encode($this->Speed)."\" eff=\"".base64_encode($this->Effect)."\" style=\"".base64_encode($this->Style)."\" coc=\"".base64_encode($this->CloseOnClick)."\" sh=\"".base64_encode($this->Shadow)."\"  shpx=\"".base64_encode($this->ShadowPositionX)."\"  shpy=\"".base64_encode($this->ShadowPositionY)."\"  shb=\"".base64_encode($this->ShadowBlur)."\"  shc=\"".base64_encode($this->ShadowColor)."\" />\r\n";
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_OVERLAYS."` (`id`, `action_id`, `position`, `speed`, `slide`, `margin_left`, `margin_top`, `margin_right`, `margin_bottom`, `style`, `close_on_click`, `shadow`, `shadow_x`, `shadow_y`, `shadow_blur`, `shadow_color`, `width`, `height`, `background`, `background_color`, `background_opacity`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."','".@mysql_real_escape_string($this->Position)."', '".@mysql_real_escape_string($this->Speed)."', '".@mysql_real_escape_string($this->Effect)."', '".@mysql_real_escape_string($this->Margin[0])."', '".@mysql_real_escape_string($this->Margin[1])."', '".@mysql_real_escape_string($this->Margin[2])."', '".@mysql_real_escape_string($this->Margin[3])."', '".@mysql_real_escape_string($this->Style)."', '".@mysql_real_escape_string($this->CloseOnClick)."', '".@mysql_real_escape_string(($this->Shadow)?"1":"0")."', '".@mysql_real_escape_string($this->ShadowPositionX)."', '".@mysql_real_escape_string($this->ShadowPositionY)."', '".@mysql_real_escape_string($this->ShadowBlur)."', '".@mysql_real_escape_string($this->ShadowColor)."', '".@mysql_real_escape_string($this->Width)."', '".@mysql_real_escape_string($this->Height)."', '".@mysql_real_escape_string($this->Background ? 1 : 0)."', '".@mysql_real_escape_string($this->BackgroundColor)."', '".@mysql_real_escape_string($this->BackgroundOpacity)."');";
	}
	
	function GetCommand($_id=null)
	{
		return "lz_tracking_add_overlay_box('".base64_encode($this->Id)."','".base64_encode($this->HTML)."',".$this->Position.",".$this->Speed."," . $this->Effect . ",".parseBool($this->Shadow)."," . $this->ShadowBlur . "," . $this->ShadowPositionX . "," . $this->ShadowPositionY . ",'" . $this->ShadowColor . "',".$this->Margin[0].",".$this->Margin[1].",".$this->Margin[2].",".$this->Margin[3].",".$this->Width.",".$this->Height.",".parseBool($this->Background).",'".$this->BackgroundColor."',".$this->BackgroundOpacity.");";
	}
}

class Invitation extends OverlayElement
{
	public $ActionId;
	public $Senders;
	public $Text;
	
	function Invitation()
	{
		global $CONFIG;
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Style = $_row["style"];
			$this->Id = $_row["id"];
			$this->Position = $_row["position"];
			$this->Margin = Array($_row["margin_left"],$_row["margin_top"],$_row["margin_right"],$_row["margin_bottom"]);
			$this->Speed = $_row["speed"];
			$this->Effect = $_row["slide"];
			$this->CloseOnClick = $_row["close_on_click"];
			$this->Shadow = $_row["shadow"];
			$this->ShadowPositionX = @$_row["shadow_x"];
			$this->ShadowPositionY = @$_row["shadow_x"];
			$this->ShadowBlur = @$_row["shadow_blur"];
			$this->ShadowColor = $_row["shadow_color"];
			$this->Background = !empty($_row["background"]);
			$this->BackgroundColor = @$_row["background_color"];
			$this->BackgroundOpacity = @$_row["background_opacity"];
		}
		else if(func_num_args() == 2)
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
            $values = func_get_arg(1);
            $this->CloseOnClick = $values[0];
            $this->Position = $values[1];
            $this->Margin = Array($values[2],$values[3],$values[4],$values[5]);
            $this->Effect = $values[6];
            $this->Shadow = $values[7];
            $this->ShadowBlur = $values[8];
            $this->ShadowColor = $values[9];
            $this->ShadowPositionX = $values[10];
            $this->ShadowPositionY = $values[11];
            $this->Speed = $values[12];
            $this->Style = $values[13];
            $this->Background = $values[14];
            $this->BackgroundColor = $values[15];
            $this->BackgroundOpacity = str_replace(",",".",$values[16]);
		}
		else
		{
			$this->HTML = func_get_arg(0);
			$this->Text = func_get_arg(1);
			$values = func_get_arg(2);
           	$this->CloseOnClick = $values[0];
            $this->Position = $values[1];
            $this->Margin = Array($values[2],$values[3],$values[4],$values[5]);
            $this->Effect = $values[6];
            $this->Shadow = $values[7];
            $this->ShadowBlur = $values[8];
            $this->ShadowColor = $values[9];
            $this->ShadowPositionX = $values[10];
			$this->ShadowPositionY = $values[11];
            $this->Speed = $values[12];
            $this->Style = $values[13];
			$this->Background = $values[14];
			$this->BackgroundColor = $values[15];
			$this->BackgroundOpacity = str_replace(",",".",$values[16]);
		}
		
		if(!empty($this->Style))
		{
			$dimensions = (!empty($CONFIG["gl_caii"]) && @file_exists(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions_header.txt")) ? explode(",",getFile(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions_header.txt")) : explode(",",getFile(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions.txt"));
			$this->Width = @$dimensions[0];
			$this->Height = @$dimensions[1];

			$settings_string = (@file_exists(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/settings.txt")) ? getFile(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/settings.txt") : "";
			
			if(strpos($settings_string,"noshadow") !== false)
				$this->Shadow = false;
		}
		
		
		$this->Senders = Array();
	}

	function GetXML()
	{
		$xml = "<evinv id=\"".base64_encode($this->Id)."\" bgo=\"".base64_encode($this->BackgroundOpacity)."\" bgc=\"".base64_encode($this->BackgroundColor)."\" bg=\"".base64_encode($this->Background)."\" ml=\"".base64_encode($this->Margin[0])."\" mt=\"".base64_encode($this->Margin[1])."\" mr=\"".base64_encode($this->Margin[2])."\" mb=\"".base64_encode($this->Margin[3])."\" pos=\"".base64_encode($this->Position)."\" speed=\"".base64_encode($this->Speed)."\" eff=\"".base64_encode($this->Effect)."\" style=\"".base64_encode($this->Style)."\" coc=\"".base64_encode($this->CloseOnClick)."\" sh=\"".base64_encode($this->Shadow)."\"  shpx=\"".base64_encode($this->ShadowPositionX)."\"  shpy=\"".base64_encode($this->ShadowPositionY)."\"  shb=\"".base64_encode($this->ShadowBlur)."\"  shc=\"".base64_encode($this->ShadowColor)."\">\r\n";
		
		foreach($this->Senders as $sender)
			$xml .= $sender->GetXML();
			
		return $xml . "</evinv>\r\n";
	}
	
	function GetCommand($_id=null)
	{
		return "lz_tracking_request_chat('" . base64_encode($_id) . "','". base64_encode($this->Text) ."','". base64_encode($this->HTML) ."',".$this->Width.",".$this->Height.",".$this->Margin[0].",".$this->Margin[1].",".$this->Margin[2].",".$this->Margin[3].",'" . $this->Position . "',".$this->Speed."," . $this->Effect . "," . parseBool($this->Shadow) . "," . $this->ShadowBlur . "," . $this->ShadowPositionX . "," . $this->ShadowPositionY . ",'" . $this->ShadowColor . "',".parseBool($this->Background).",'".$this->BackgroundColor."',".$this->BackgroundOpacity.");";
	}
}

class EventTrigger
{
	public $Id;
	public $ActionId;
	public $ReceiverUserId;
	public $ReceiverBrowserId;
	public $Triggered;
	public $TriggerTime;
	public $Exists = false;
	
	function EventTrigger()
	{
		if(func_num_args() == 5)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->ReceiverBrowserId = func_get_arg(1);
			$this->ActionId = func_get_arg(2);
			$this->TriggerTime = func_get_arg(3);
			$this->Triggered = func_get_arg(4);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->ReceiverBrowserId = $_row["receiver_browser_id"];
			$this->ActionId = $_row["action_id"];
			$this->Triggered = $_row["triggered"];
			$this->TriggerTime = $_row["time"];
		}
	}
	
	function Load()
	{
		$this->Exists = false;
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->ReceiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->ReceiverBrowserId)."' AND `action_id`='".@mysql_real_escape_string($this->ActionId)."' ORDER BY `time` ASC;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$this->Id = $row["id"];
				$this->TriggerTime = $row["time"];
				$this->Triggered = $row["triggered"];
				$this->Exists = true;
			}
	}
	
	function Update()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` SET `time`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function Save($_eventId)
	{
		if(!$this->Exists)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` (`id`, `receiver_user_id`, `receiver_browser_id`, `action_id`, `time`, `triggered`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->ReceiverBrowserId)."','".@mysql_real_escape_string($this->ActionId)."', '".@mysql_real_escape_string($this->TriggerTime)."','".@mysql_real_escape_string($this->Triggered)."');");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` SET `triggered`=`triggered`+1, `time`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class EventAction
{
	public $Id = "";
	public $EventId = "";
	public $Type = "";
	public $Value = "";
	public $Invitation;
	public $OverlayBox;
	public $WebsitePush;
	public $Receivers;
	
	function EventAction()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->EventId = $_row["eid"];
			$this->Type = $_row["type"];
			$this->Value = $_row["value"];
		}
		else if(func_num_args() == 2)
		{
			$this->Id = func_get_arg(0);
			$this->Type = func_get_arg(1);
		}
		else
		{
			$this->EventId = func_get_arg(0);
			$this->Id = func_get_arg(1);
			$this->Type = func_get_arg(2);
			$this->Value = func_get_arg(3);
		}
		$this->Receivers = Array();
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTIONS."` (`id`, `eid`, `type`, `value`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->EventId)."','".@mysql_real_escape_string($this->Type)."', '".@mysql_real_escape_string($this->Value)."');";
	}

	function GetXML()
	{
		$xml =  "<evac id=\"".base64_encode($this->Id)."\" type=\"".base64_encode($this->Type)."\" val=\"".base64_encode($this->Value)."\">\r\n";
		
		if(!empty($this->Invitation))
			$xml .= $this->Invitation->GetXML();
		
		if(!empty($this->OverlayBox))
			$xml .= $this->OverlayBox->GetXML();
			
		if(!empty($this->WebsitePush))
			$xml .= $this->WebsitePush->GetXML();
			
		foreach($this->Receivers as $receiver)
			$xml .= $receiver->GetXML();
			
		return $xml . "</evac>\r\n";
	}
	
	function Exists($_receiverUserId,$_receiverBrowserId)
	{
		if($this->Type == 2)
		{
			if($result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($_receiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($_receiverBrowserId)."' AND `event_action_id`='".@mysql_real_escape_string($this->Id)."' AND `accepted`='0' AND `declined`='0' LIMIT 1;"))
				if($row = mysql_fetch_array($result, MYSQL_BOTH))
					return true;
		}
		else if($this->Type == 3)
		{
			if($result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_ALERTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($_receiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($_receiverBrowserId)."' AND `event_action_id`='".@mysql_real_escape_string($this->Id)."' AND `accepted`='0' LIMIT 1;"))
				if($row = mysql_fetch_array($result, MYSQL_BOTH))
					return true;
		}
		return false;
	}
	
	function GetInternalReceivers()
	{
		$receivers = array();
		if($result = queryDB(true,"SELECT `receiver_id` FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_RECEIVERS."` WHERE `action_id`='".@mysql_real_escape_string($this->Id)."';"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$receivers[]=$row["receiver_id"];
		return $receivers;
	}
}

class EventActionSender
{
	public $Id = "";
	public $ParentId = "";
	public $UserSystemId = "";
	public $GroupId = "";
	public $Priority = "";
	
	function EventActionSender()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ParentId = $_row["pid"];
			$this->UserSystemId = $_row["user_id"];
			$this->GroupId = $_row["group_id"];
			$this->Priority = $_row["priority"];
		}
		else if(func_num_args() == 4)
		{
			$this->Id = getId(32);
			$this->ParentId = func_get_arg(0);
			$this->UserSystemId = func_get_arg(1);
			$this->GroupId = func_get_arg(2);
			$this->Priority = func_get_arg(3);
		}
	}
	
	function SaveSender()
	{
		return queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_SENDERS."` (`id`, `pid`, `user_id`, `group_id`, `priority`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ParentId)."','".@mysql_real_escape_string($this->UserSystemId)."','".@mysql_real_escape_string($this->GroupId)."', '".@mysql_real_escape_string($this->Priority)."');");
	}

	function GetXML()
	{
		return "<evinvs id=\"".base64_encode($this->Id)."\" userid=\"".base64_encode($this->UserSystemId)."\" groupid=\"".base64_encode($this->GroupId)."\" priority=\"".base64_encode($this->Priority)."\" />\r\n";
	}
}

class EventActionReceiver
{
	public $Id = "";
	public $ReceiverId = "";
	
	function EventActionReceiver()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ActionId = $_row["action_id"];
			$this->ReceiverId = $_row["receiver_id"];
		}
		else
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->ReceiverId = func_get_arg(1);
		}
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_RECEIVERS."` (`id`, `action_id`, `receiver_id`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."', '".@mysql_real_escape_string($this->ReceiverId)."');";
	}

	function GetXML()
	{
		return "<evr id=\"".base64_encode($this->Id)."\" rec=\"".base64_encode($this->ReceiverId)."\" />\r\n";
	}
}

class EventURL
{
	public $Id = "";
	public $EventId = "";
	public $URL = "";
	public $Referrer = "";
	public $TimeOnSite = "";
	public $Blacklist;
	
	function EventURL()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->URL = $_row["url"];
			$this->Referrer = $_row["referrer"];
			$this->TimeOnSite = $_row["time_on_site"];
			$this->Blacklist = !empty($_row["blacklist"]);
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->EventId = func_get_arg(1);
			$this->URL = strtolower(func_get_arg(2));
			$this->Referrer = strtolower(func_get_arg(3));
			$this->TimeOnSite = func_get_arg(4);
			$this->Blacklist = func_get_arg(5);
		}
	}
	
	function GetSQL()
	{
		return "INSERT IGNORE INTO `".DB_PREFIX.DATABASE_EVENT_URLS."` (`id`, `eid`, `url`, `referrer`, `time_on_site`, `blacklist`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->EventId)."','".@mysql_real_escape_string($this->URL)."', '".@mysql_real_escape_string($this->Referrer)."', '".@mysql_real_escape_string($this->TimeOnSite)."', '".@mysql_real_escape_string($this->Blacklist)."');";
	}

	function GetXML()
	{
		return "<evur id=\"".base64_encode($this->Id)."\" url=\"".base64_encode($this->URL)."\" ref=\"".base64_encode($this->Referrer)."\" tos=\"".base64_encode($this->TimeOnSite)."\" bl=\"".base64_encode($this->Blacklist)."\" />\r\n";
	}
}

class Event extends BaseObject
{
	public $Name = "";
	public $PagesVisited = "";
	public $TimeOnSite = "";
	public $Receivers;
	public $URLs;
	public $Actions;
	public $NotAccepted;
	public $NotDeclined;
	public $TriggerTime;
	public $SearchPhrase = "";
	public $TriggerAmount;
	public $NotInChat;
	public $Priority;
	public $IsActive;
	public $SaveInCookie;
	public $Goals;
	public $FunnelUrls;
	
	function Event()
	{
		$this->FunnelUrls = array();
		$this->Goals = array();
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			
			$this->Id = $_row["id"];
			$this->Name = $_row["name"];
			$this->Edited = $_row["edited"];
			$this->Editor = $_row["editor"];
			$this->Created = $_row["created"];
			$this->Creator = $_row["creator"];
			$this->TimeOnSite = $_row["time_on_site"];
			$this->PagesVisited = $_row["pages_visited"];
			$this->NotAccepted = $_row["not_accepted"];
			$this->NotDeclined = $_row["not_declined"];
			$this->NotInChat = $_row["not_in_chat"];
			$this->TriggerAmount = $_row["max_trigger_amount"];
			$this->TriggerTime = $_row["trigger_again_after"];
			$this->SearchPhrase = $_row["search_phrase"];
			$this->Priority = $_row["priority"];
			$this->IsActive = !empty($_row["is_active"]);
			$this->SaveInCookie = !empty($_row["save_cookie"]);
			$this->URLs = array();
			$this->Actions = array();
			$this->Receivers = array();
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->Name = func_get_arg(1);
			$this->Edited = func_get_arg(2);
			$this->Created = func_get_arg(3);
			$this->Editor = func_get_arg(4);
			$this->Creator = func_get_arg(5);
			$this->TimeOnSite = func_get_arg(6);
			$this->PagesVisited = func_get_arg(7);
			$this->NotAccepted = func_get_arg(8);
			$this->NotDeclined = func_get_arg(9);
			$this->TriggerTime = func_get_arg(10);
			$this->TriggerAmount = func_get_arg(11);
			$this->NotInChat = func_get_arg(12);
			$this->Priority = func_get_arg(13);
			$this->IsActive = func_get_arg(14);
			$this->SearchPhrase = func_get_arg(15);
			$this->SaveInCookie = func_get_arg(16);
		}
	}
	
	function MatchesTriggerCriterias($_trigger)
	{
		$match = true;
		if($this->TriggerTime > 0 && $_trigger->TriggerTime >= (time()-$this->TriggerTime))
			$match = false;
		else if($this->TriggerAmount == 0 || ($this->TriggerAmount > 0 && $_trigger->Triggered > $this->TriggerAmount))
			$match = false;
		return $match;
	}
	
	function MatchesGlobalCriterias($_pageCount,$_timeOnSite,$_invAccepted,$_invDeclined,$_inChat,$_searchPhrase="")
	{
		$match = true;
		if($_timeOnSite<0)
			$_timeOnSite = 0;

        $_result = array("pv"=>($this->PagesVisited > 0 && $_pageCount < $this->PagesVisited));
        $_result["tos"] = ($this->TimeOnSite > 0 && $_timeOnSite < $this->TimeOnSite);
        $_result["inva"] = (!empty($this->NotAccepted) && $_invAccepted);
        $_result["invd"] = (!empty($this->NotDeclined) && $_invDeclined);
        $_result["nic"] = (!empty($this->NotInChat) && $_inChat);

		if($this->PagesVisited > 0 && $_pageCount < $this->PagesVisited)
			$match = false;
		else if($this->TimeOnSite > 0 && $_timeOnSite < $this->TimeOnSite)
			$match = false;
		else if(!empty($this->NotAccepted) && $_invAccepted)
			$match = false;
		else if(!empty($this->NotDeclined) && $_invDeclined)
			$match = false;
		else if(!empty($this->NotInChat) && $_inChat)
			$match = false;
			
		if(!empty($this->SearchPhrase))
		{
			if(empty($_searchPhrase))
				$match = false;
			else
			{
				$spmatch=false;
				$phrases = explode(",",$this->SearchPhrase);
				foreach($phrases as $phrase)
					if(jokerCompare($phrase,$_searchPhrase))
					{
						$spmatch = true;
						break;
					}
				if(!$spmatch)
					$match = false;
			}
		}
		return $match;
	}
	
	function MatchesURLFunnelCriterias($_history)
	{
		$startpos = -1;
		$count = 0;
		$pos = 0;
		foreach($_history as $hpos => $hurl)
		{
			$fuid = "";
			$fcount = 0;
			$fuid = $this->FunnelUrls[$count];
			
			if($this->MatchUrls($this->URLs[$fuid],$hurl->Url->GetAbsoluteUrl(),$hurl->Referrer->GetAbsoluteUrl(),time()-($hurl->Entrance)) === true)
			{
				if($startpos==-1)
					$startpos = $pos;
					
				if($startpos+$count==$pos)
					$count++;
				else
				{
					$count = 0;
					$startpos=-1;
				}
				if($count==count($this->FunnelUrls))
					break;
			}
			else
			{
				$count = 0;
				$startpos=-1;
			}
			$pos++;
		}
		return $count==count($this->FunnelUrls);
	}
	
	function MatchesURLCriterias($_url,$_referrer,$_previous,$_timeOnUrl)
	{
		if(count($this->URLs) == 0)
			return true;
		$_url = @strtolower($_url);
		$_referrer = @strtolower($_referrer);
		$_previous = @strtolower($_previous);
		foreach($this->URLs as $url)
		{
			$match = $this->MatchUrls($url,$_url,$_referrer,$_timeOnUrl);
			if($match !== -1)
				return $match;
				
			$match = $this->MatchUrls($url,$_url,$_previous,$_timeOnUrl);
			if($match !== -1)
				return $match;
		}
		return false;
	}
	
	function MatchUrls($_eurl,$_url,$_referrer,$_timeOnUrl)
	{
		if($_eurl->TimeOnSite > 0 && $_eurl->TimeOnSite > $_timeOnUrl)
			return -1;
		$valid = true;
		if(!empty($_eurl->URL))
			$valid=jokerCompare($_eurl->URL,$_url);
		if((!empty($_eurl->URL) && $valid || empty($_eurl->URL)) && !empty($_eurl->Referrer))
			$valid=jokerCompare($_eurl->Referrer,$_referrer);
		if($valid)
			return !$_eurl->Blacklist;
		else
			return -1;
	}

	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENTS."` (`id`, `name`, `created`, `creator`, `edited`, `editor`, `pages_visited`, `time_on_site`, `max_trigger_amount`, `trigger_again_after`, `not_declined`, `not_accepted`, `not_in_chat`, `priority`, `is_active`, `search_phrase`, `save_cookie`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->Name)."','".@mysql_real_escape_string($this->Created)."','".@mysql_real_escape_string($this->Creator)."','".@mysql_real_escape_string($this->Edited)."', '".@mysql_real_escape_string($this->Editor)."', '".@mysql_real_escape_string($this->PagesVisited)."','".@mysql_real_escape_string($this->TimeOnSite)."','".@mysql_real_escape_string($this->TriggerAmount)."','".@mysql_real_escape_string($this->TriggerTime)."', '".@mysql_real_escape_string($this->NotDeclined)."', '".@mysql_real_escape_string($this->NotAccepted)."', '".@mysql_real_escape_string($this->NotInChat)."', '".@mysql_real_escape_string($this->Priority)."', '".@mysql_real_escape_string($this->IsActive)."', '".@mysql_real_escape_string($this->SearchPhrase)."', '".@mysql_real_escape_string(($this->SaveInCookie) ? 1 : 0)."');";
	}

	function GetXML()
	{
		$xml = "<ev id=\"".base64_encode($this->Id)."\" sc=\"".base64_encode($this->SaveInCookie)."\" nacc=\"".base64_encode($this->NotAccepted)."\" ndec=\"".base64_encode($this->NotDeclined)."\" name=\"".base64_encode($this->Name)."\" prio=\"".base64_encode($this->Priority)."\" created=\"".base64_encode($this->Created)."\" nic=\"".base64_encode($this->NotInChat)."\" creator=\"".base64_encode($this->Creator)."\" editor=\"".base64_encode($this->Editor)."\" edited=\"".base64_encode($this->Edited)."\" tos=\"".base64_encode($this->TimeOnSite)."\" ta=\"".base64_encode($this->TriggerAmount)."\" tt=\"".base64_encode($this->TriggerTime)."\" pv=\"".base64_encode($this->PagesVisited)."\" ia=\"".base64_encode($this->IsActive)."\" sp=\"".base64_encode($this->SearchPhrase)."\">\r\n";
		
		foreach($this->Actions as $action)
			$xml .= $action->GetXML();
		
		foreach($this->URLs as $url)
			$xml .= $url->GetXML();
			
		foreach($this->Goals as $act)
			$xml .= "<evg id=\"".base64_encode($act->Id)."\" />";
			
		foreach($this->FunnelUrls as $ind => $uid)
			$xml .= "<efu id=\"".base64_encode($uid)."\">".base64_encode($ind)."</efu>";

		return $xml . "</ev>\r\n";
	}
}

class Goal
{
	public $Id;
	public $Title;
	public $Description;
	public $Conversion;
	
	function Goal()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->Title = $_row["title"];
			$this->Description = $_row["description"];
			$this->Conversion = !empty($_row["conversion"]);
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->Title = func_get_arg(1);
			$this->Description = func_get_arg(2);
			$this->Conversion = func_get_arg(3);
		}
	}
	
	function Save()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_GOALS."` (`id`, `title`, `description`, `conversion`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->Title)."','".@mysql_real_escape_string($this->Description)."', '".@mysql_real_escape_string($this->Conversion)."');";
	}

	function GetXML()
	{
		return "<tgt id=\"".base64_encode($this->Id)."\" title=\"".base64_encode($this->Title)."\" desc=\"".base64_encode($this->Description)."\" conv=\"".base64_encode($this->Conversion)."\" />\r\n";
	}
}

class Signature
{
    public $Id;
    public $Name;
    public $Signature;
    public $Default;
    public $Deleted;
    public $OperatorId;
    public $GroupId;

    function Signature()
    {
        if(func_num_args() == 1)
        {
            $_row = func_get_arg(0);
            $this->Id = $_row["id"];
            $this->Name = $_row["name"];
            $this->Signature = $_row["signature"];
            $this->OperatorId = $_row["operator_id"];
            $this->GroupId = $_row["group_id"];
            $this->Default = $_row["default"];
        }
    }

    function Save($_prefix)
    {
        queryDB(true,"DELETE FROM `".$_prefix.DATABASE_SIGNATURES."` WHERE `operator_id`='".@mysql_real_escape_string($this->OperatorId)."' AND `group_id`='".@mysql_real_escape_string($this->GroupId)."' AND `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
        if(!$this->Deleted)
            queryDB(true,"INSERT INTO `".$_prefix.DATABASE_SIGNATURES."` (`id` ,`name` ,`signature` ,`operator_id`,`group_id`,`default`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->Name)."','".@mysql_real_escape_string($this->Signature)."', '".@mysql_real_escape_string($this->OperatorId)."', '".@mysql_real_escape_string($this->GroupId)."', '".@mysql_real_escape_string($this->Default)."');");
    }

    function XMLParamAlloc($_param,$_value)
    {
        if($_param =="a")
            $this->Id = $_value;
        else if($_param =="b")
            $this->Default = $_value;
        else if($_param =="c")
            $this->Deleted = $_value;
        else if($_param =="d")
            $this->Name = $_value;
        else if($_param =="e")
            $this->Signature = $_value;
        else if($_param =="f")
            $this->OperatorId = $_value;
        else if($_param =="g")
            $this->GroupId = $_value;
    }

    function GetXML()
    {
        return "<sig i=\"".base64_encode($this->Id)."\" n=\"".base64_encode($this->Name)."\" o=\"".base64_encode($this->OperatorId)."\" g=\"".base64_encode($this->GroupId)."\" d=\"".base64_encode($this->Default ? 1 : 0)."\">".base64_encode($this->Signature)."</sig>\r\n";
    }
}

class Mailbox
{
    public $Type = "IMAP";
    public $Id = "";
    public $Username = "";
    public $Password = "";
    public $Port = 110;
    public $Host = "";
    public $ExecOperatorId = "";
    public $Delete = 2;
    public $Email = "";
    public $SSL = false;
    public $Authentication = "";
    public $SenderName = "";
    public $Default = false;
    public $ConnectFrequency = 15;
    public $LastConnect = 0;

    function Mailbox()
    {
        if(func_num_args() == 2)
        {
            $this->Id = $_POST["p_cfg_es_i_" . func_get_arg(0)];
            $this->Email = $_POST["p_cfg_es_e_" . func_get_arg(0)];
            $this->Host = $_POST["p_cfg_es_h_" . func_get_arg(0)];
            $this->Username = $_POST["p_cfg_es_u_" . func_get_arg(0)];
            $this->Password = $_POST["p_cfg_es_p_" . func_get_arg(0)];
            $this->Port = $_POST["p_cfg_es_po_" . func_get_arg(0)];
            $this->SSL = !empty($_POST["p_cfg_es_s_" . func_get_arg(0)]);
            $this->Authentication = $_POST["p_cfg_es_a_" . func_get_arg(0)];
            $this->Delete = !empty($_POST["p_cfg_es_d_" . func_get_arg(0)]);
            $this->Type = $_POST["p_cfg_es_t_" . func_get_arg(0)];
            $this->SenderName = $_POST["p_cfg_es_sn_" . func_get_arg(0)];
            $this->Default = !empty($_POST["p_cfg_es_de_" . func_get_arg(0)]);
            $this->ConnectFrequency = $_POST["p_cfg_es_c_" . func_get_arg(0)];
        }
        else
        {
            $row = func_get_arg(0);
            $this->Id = $row["id"];
            $this->Type = $row["type"];
            $this->Email = $row["email"];
            $this->Username = $row["username"];
            $this->Password = $row["password"];
            $this->Port = $row["port"];
            $this->Host = $row["host"];
            $this->ExecOperatorId = $row["exec_operator_id"];
            $this->Delete = !empty($row["delete"]);
            $this->SenderName = $row["sender_name"];
            $this->Authentication = $row["authentication"];
            $this->SSL = !empty($row["ssl"]);
            $this->Default = !empty($row["default"]);
            $this->ConnectFrequency = $row["connect_frequency"];
            $this->LastConnect = $row["last_connect"];
        }
    }

    function SetExecOperator($_operatorId)
    {
        $this->ExecOperatorId = $_operatorId;
        queryDB(false,"UPDATE `".DB_PREFIX.DATABASE_MAILBOXES."` SET `exec_operator_id`='".@mysql_real_escape_string($_operatorId)."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
    }

    function GetXML($_groupId="")
    {
        return "<tes g=\"".base64_encode($_groupId)."\" e=\"".base64_encode($this->Email)."\" c=\"".base64_encode($this->ConnectFrequency)."\" i=\"".base64_encode($this->Id)."\" a=\"".base64_encode($this->Authentication)."\" s=\"".base64_encode($this->SSL ? "1" : "0")."\" de=\"".base64_encode($this->Default ? "1" : "0")."\" sn=\"".base64_encode($this->SenderName)."\" t=\"".base64_encode($this->Type)."\" u=\"".base64_encode($this->Username)."\" p=\"".base64_encode($this->Password)."\" po=\"".base64_encode($this->Port)."\" d=\"".base64_encode(1)."\" h=\"".base64_encode($this->Host)."\" />\r\n";
    }

    function Save()
    {
        queryDB(true,"REPLACE INTO `".DB_PREFIX.DATABASE_MAILBOXES."` (`id`,`email`,`exec_operator_id`,`username`,`password`,`type`,`host`,`port`,`delete`,`authentication`,`sender_name`,`ssl`,`default`,`last_connect`,`connect_frequency`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->Email)."', '".@mysql_real_escape_string($this->ExecOperatorId)."', '".@mysql_real_escape_string($this->Username)."', '".@mysql_real_escape_string($this->Password)."', '".@mysql_real_escape_string($this->Type)."', '".@mysql_real_escape_string($this->Host)."', '".@mysql_real_escape_string($this->Port)."',1, '".@mysql_real_escape_string($this->Authentication)."', '".@mysql_real_escape_string($this->SenderName)."', '".@mysql_real_escape_string($this->SSL ? 1 : 0)."', '".@mysql_real_escape_string($this->Default ? 1 : 0)."','".@mysql_real_escape_string($this->LastConnect)."','".@mysql_real_escape_string($this->ConnectFrequency)."');");
    }

    function SetLastConnect($_time)
    {
        queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_MAILBOXES."` SET `last_connect`=".$_time." WHERE `id`='".@mysql_real_escape_string($this->Id)."'");
    }

    static function UnsetExecOperator($_operatorId)
    {
        queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_MAILBOXES."` SET `exec_operator_id`='' WHERE `exec_operator_id`='".@mysql_real_escape_string($_operatorId)."';");
    }

    static function GetDefaultOutgoing()
    {
        $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_MAILBOXES."` WHERE `default`=1 AND `type`<>'POP';");
        while($row = @mysql_fetch_array($result, MYSQL_BOTH))
            return new Mailbox($row);
        return null;
    }

    static function GetById($_id)
    {
        $result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_MAILBOXES."` WHERE `id`='".@mysql_real_escape_string($_id)."' LIMIT 1;");
        while($row = @mysql_fetch_array($result, MYSQL_BOTH))
            return new Mailbox($row);
        return null;
    }
}

class PredefinedMessage
{
	public $Id = 0;
	public $LangISO = "";
	public $InvitationAuto = "";
	public $InvitationManual = "";
	public $Welcome = "";
	public $WebsitePushAuto = "";
	public $WebsitePushManual = "";
	public $BrowserIdentification = "";
	public $IsDefault;
	public $AutoWelcome;
	public $GroupId = "";
	public $UserId = "";
	public $Editable;
	public $TicketInformation = "";
	public $ChatInformation = "";
	public $CallMeBackInformation = "";
	public $EmailChatTranscript = "";
	public $EmailTicket = "";
	public $QueueMessage = "";
	public $QueueMessageTime = 120;
	public $WelcomeCallMeBack = "";
	public $Deleted = false;
    public $SubjectChatTranscript = "";
    public $SubjectTicket = "";

	function PredefinedMessage()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->LangISO = $_row["lang_iso"];
			$this->InvitationAuto = @$_row["invitation_auto"];
			$this->InvitationManual = @$_row["invitation_manual"];
			$this->Welcome = $_row["welcome"];
			
			if(!empty($_row["welcome_call_me_back"]))
				$this->WelcomeCallMeBack = $_row["welcome_call_me_back"];
				
			$this->WebsitePushAuto = @$_row["website_push_auto"];
			$this->WebsitePushManual = @$_row["website_push_manual"];
			$this->BrowserIdentification = !empty($_row["browser_ident"]);
			$this->IsDefault = !empty($_row["is_default"]);
			$this->AutoWelcome = !empty($_row["auto_welcome"]);
			$this->Editable = !empty($_row["editable"]);
			$this->TicketInformation = @$_row["ticket_info"];
			$this->ChatInformation = @$_row["chat_info"];
			$this->CallMeBackInformation = @$_row["call_me_back_info"];
			$this->EmailChatTranscript = @$_row["email_chat_transcript"];
			$this->EmailTicket = @$_row["email_ticket"];
			$this->QueueMessage = @$_row["queue_message"];
			$this->QueueMessageTime = @$_row["queue_message_time"];
            $this->SubjectChatTranscript = @$_row["subject_chat_transcript"];
            $this->SubjectTicket = @$_row["subject_ticket"];
		}
		else if(func_num_args() == 17)
		{
			$this->Id = func_get_arg(0);
			$this->UserId = func_get_arg(1);
			$this->GroupId = func_get_arg(2);
			$this->LangISO = func_get_arg(3);
			$this->InvitationManual = func_get_arg(4);
			$this->InvitationAuto = func_get_arg(5);
			$this->Welcome = func_get_arg(6);
			$this->WebsitePushManual = func_get_arg(7);
			$this->WebsitePushAuto = func_get_arg(8);
			$this->ChatInformation = func_get_arg(9);
			$this->TicketInformation = func_get_arg(10);
			$this->BrowserIdentification = func_get_arg(11)==1;
			$this->IsDefault = func_get_arg(12)==1;
			$this->AutoWelcome = func_get_arg(13)==1;
			$this->Editable = func_get_arg(14)==1;
			$this->EmailChatTranscript = func_get_arg(15);
			$this->EmailTicket = func_get_arg(16);
			$this->WelcomeCallMeBack = func_get_arg(20);
			$this->CallMeBackInformation = func_get_arg(21);
            $this->SubjectChatTranscript = func_get_arg(22);
            $this->SubjectTicket = func_get_arg(23);
		}
	}
	
	function XMLParamAlloc($_param,$_value)
	{
		if($_param =="inva")
			$this->InvitationAuto = $_value;
		else if($_param =="invm")
			$this->InvitationManual = $_value;
		else if($_param =="wpa")
			$this->WebsitePushAuto = $_value;
		else if($_param =="wpm")
			$this->WebsitePushManual = $_value;
		else if($_param =="bi")
			$this->BrowserIdentification = $_value;
		else if($_param =="wel")
			$this->Welcome = $_value;
		else if($_param =="welcmb")
			$this->WelcomeCallMeBack = $_value;
		else if($_param =="def")
			$this->IsDefault = $_value;
		else if($_param =="aw")
			$this->AutoWelcome = $_value;
		else if($_param =="edit")
			$this->Editable = $_value;
		else if($_param =="ci")
			$this->ChatInformation = $_value;
		else if($_param =="ccmbi")
			$this->CallMeBackInformation = $_value;
		else if($_param =="ti")
			$this->TicketInformation = $_value;
		else if($_param =="ect")
			$this->EmailChatTranscript = $_value;
		else if($_param =="et")
			$this->EmailTicket = $_value;
		else if($_param =="qm")
			$this->QueueMessage = $_value;
		else if($_param =="qmt")
			$this->QueueMessageTime = $_value;
		else if($_param =="del")
			$this->Deleted = !empty($_value);
        else if($_param =="sct")
            $this->SubjectChatTranscript = $_value;
        else if($_param =="st")
            $this->SubjectTicket = $_value;
	}
	
	function Save($_prefix)
	{
        if($this->Deleted)
		    queryDB(true,"DELETE FROM `".$_prefix.DATABASE_PREDEFINED."` WHERE `internal_id`='".@mysql_real_escape_string($this->UserId)."' AND `group_id`='".@mysql_real_escape_string($this->GroupId)."' AND `lang_iso`='".@mysql_real_escape_string($this->LangISO)."' LIMIT 1;");
		else
			queryDB(true,"REPLACE INTO `".$_prefix.DATABASE_PREDEFINED."` (`id` ,`internal_id` ,`group_id` ,`lang_iso` ,`invitation_manual`,`invitation_auto` ,`welcome` ,`welcome_call_me_back`,`website_push_manual` ,`website_push_auto`,`chat_info`,`call_me_back_info`,`ticket_info` ,`browser_ident` ,`is_default` ,`auto_welcome`,`editable`,`email_chat_transcript`,`email_ticket`,`queue_message`,`queue_message_time`,`subject_chat_transcript`,`subject_ticket`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->UserId)."','".@mysql_real_escape_string($this->GroupId)."', '".@mysql_real_escape_string($this->LangISO)."', '".@mysql_real_escape_string($this->InvitationManual)."', '".@mysql_real_escape_string($this->InvitationAuto)."','".@mysql_real_escape_string($this->Welcome)."','".@mysql_real_escape_string($this->WelcomeCallMeBack)."', '".@mysql_real_escape_string($this->WebsitePushManual)."', '".@mysql_real_escape_string($this->WebsitePushAuto)."',  '".@mysql_real_escape_string($this->ChatInformation)."',  '".@mysql_real_escape_string($this->CallMeBackInformation)."', '".@mysql_real_escape_string($this->TicketInformation)."','".@mysql_real_escape_string($this->BrowserIdentification ? '1' : '0')."', '".@mysql_real_escape_string($this->IsDefault ? '1' : '0')."', '".@mysql_real_escape_string($this->AutoWelcome ? '1' : '0')."', '".@mysql_real_escape_string($this->Editable ? '1' : '0')."', '".@mysql_real_escape_string($this->EmailChatTranscript)."', '".@mysql_real_escape_string($this->EmailTicket)."', '".@mysql_real_escape_string($this->QueueMessage)."', '".@mysql_real_escape_string($this->QueueMessageTime)."', '".@mysql_real_escape_string($this->SubjectChatTranscript)."', '".@mysql_real_escape_string($this->SubjectTicket)."');");
	}

	function GetXML($_serversetup=true)
	{
        if($_serversetup)
            return "<pm et=\"".base64_encode($this->EmailTicket)."\" ect=\"".base64_encode($this->EmailChatTranscript)."\" ti=\"".base64_encode($this->TicketInformation)."\" ci=\"".base64_encode($this->ChatInformation)."\" st=\"".base64_encode($this->SubjectTicket)."\" sct=\"".base64_encode($this->SubjectChatTranscript)."\" ccmbi=\"".base64_encode($this->CallMeBackInformation)."\" lang=\"".base64_encode($this->LangISO)."\" invm=\"".base64_encode($this->InvitationManual)."\" inva=\"".base64_encode($this->InvitationAuto)."\" wel=\"".base64_encode($this->Welcome)."\" welcmb=\"".base64_encode($this->WelcomeCallMeBack)."\" wpa=\"".base64_encode($this->WebsitePushAuto)."\" wpm=\"".base64_encode($this->WebsitePushManual)."\" bi=\"".base64_encode($this->BrowserIdentification)."\" def=\"".base64_encode($this->IsDefault)."\" aw=\"".base64_encode($this->AutoWelcome)."\" edit=\"".base64_encode($this->Editable)."\" qm=\"".base64_encode($this->QueueMessage)."\" qmt=\"".base64_encode($this->QueueMessageTime)."\" />\r\n";
        else
		    return "<pm lang=\"".base64_encode($this->LangISO)."\" invm=\"".base64_encode($this->InvitationManual)."\" wel=\"".base64_encode($this->Welcome)."\" welcmb=\"".base64_encode($this->WelcomeCallMeBack)."\" wpa=\"".base64_encode($this->WebsitePushAuto)."\" bi=\"".base64_encode($this->BrowserIdentification)."\" def=\"".base64_encode($this->IsDefault)."\" aw=\"".base64_encode($this->AutoWelcome)."\" edit=\"".base64_encode($this->Editable)."\" />\r\n";
	}
}

class ChatBotFeed
{
	public $Id;
	public $ResourceId;
	public $Tags;
	public $SearchType = 0;
	public $Answer;
	public $Languages;
	public $NewWindow = false;
	
	function ChatBotFeed()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
            $this->Id = $row["id"];
            $this->ResourceId = $row["resource_id"];
            $this->Tags = $row["tags"];
			$this->Languages = $row["language"];
			$this->SearchType = $row["search_type"];
			$this->Answer = $row["answer"];
			$this->NewWindow = !empty($row["new_window"]);
		}
		else if(func_num_args() == 7)
		{
            $this->Id = func_get_arg(0);
            $this->ResourceId = func_get_arg(1);
            $this->Tags = func_get_arg(2);
            $this->SearchType = func_get_arg(3);
			$this->Answer = func_get_arg(4);
			$this->NewWindow = func_get_arg(5)=="1";
			$this->Languages = func_get_arg(6);
		}
   	}
	
	function GetXML()
	{
		return "<bf i=\"".base64_encode($this->Id)."\" l=\"".base64_encode($this->Languages)."\" n=\"".base64_encode($this->NewWindow ? 1 : 0)."\" r=\"".base64_encode($this->ResourceId)."\" s=\"".base64_encode($this->SearchType)."\" a=\"".base64_encode($this->Answer)."\">".base64_encode($this->Tags)."</bf>\r\n";
	}

	function Save($_botId)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_BOT_FEEDS."` (`id` ,`resource_id` ,`bot_id` ,`tags` ,`search_type`,`answer`,`new_window`,`language`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->ResourceId)."','".@mysql_real_escape_string($_botId)."','".@mysql_real_escape_string($this->Tags)."','".@mysql_real_escape_string($this->SearchType)."','".@mysql_real_escape_string($this->Answer)."','".@mysql_real_escape_string($this->NewWindow ? 1 : 0)."','".@mysql_real_escape_string($this->Languages)."');");
	}
}

class Profile
{
	public $LastEdited;
	public $Firstname;
	public $Name;
	public $Email;
	public $Company;
	public $Phone;
	public $Fax;
	public $Department;
	public $Street;
	public $City;
	public $ZIP;
	public $Country;
	public $Languages;
	public $Comments;
	public $Public;
	
	function Profile()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
            $this->Firstname = $row["first_name"];
            $this->Name = $row["last_name"];
            $this->Email = $row["email"];
            $this->Company = $row["company"];
            $this->Phone = $row["phone"];
            $this->Fax = $row["fax"];
            $this->Department = $row["department"];
            $this->Street = $row["street"];
            $this->City = $row["city"];
            $this->ZIP = $row["zip"];
            $this->Country = $row["country"];
            $this->Languages = $row["languages"];
            $this->Gender = $row["gender"];
            $this->Comments = $row["comments"];
			$this->Public = $row["public"];
			$this->LastEdited = $row["edited"];
		}
		else
		{
            $this->Firstname = func_get_arg(0);
            $this->Name = func_get_arg(1);
            $this->Email = func_get_arg(2);
            $this->Company = func_get_arg(3);
            $this->Phone = func_get_arg(4);
            $this->Fax = func_get_arg(5);
            $this->Department = func_get_arg(6);
            $this->Street = func_get_arg(7);
            $this->City = func_get_arg(8);
            $this->ZIP = func_get_arg(9);
            $this->Country = func_get_arg(10);
            $this->Languages = func_get_arg(11);
            $this->Gender = func_get_arg(12);
            $this->Comments = func_get_arg(13);
			$this->Public = func_get_arg(14);
		}
   	}
	
	function GetXML($_userId)
	{
		return "<p os=\"".base64_encode($_userId)."\" fn=\"".base64_encode($this->Firstname)."\" n=\"".base64_encode($this->Name)."\" e=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" p=\"".base64_encode($this->Phone)."\" f=\"".base64_encode($this->Fax)."\" d=\"".base64_encode($this->Department)."\" s=\"".base64_encode($this->Street)."\" z=\"".base64_encode($this->ZIP)."\" c=\"".base64_encode($this->Country)."\" l=\"".base64_encode($this->Languages)."\" ci=\"".base64_encode($this->City)."\" g=\"".base64_encode($this->Gender)."\" com=\"".base64_encode($this->Comments)."\" pu=\"".base64_encode($this->Public)."\" />\r\n";
	}

	function Save($_userId)
	{
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_PROFILES."` (`id` ,`edited` ,`first_name` ,`last_name` ,`email` ,`company` ,`phone`  ,`fax` ,`street` ,`zip` ,`department` ,`city` ,`country` ,`gender` ,`languages` ,`comments` ,`public`) VALUES ('".@mysql_real_escape_string($_userId)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->Firstname)."','".@mysql_real_escape_string($this->Name)."','".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string($this->Company)."','".@mysql_real_escape_string($this->Phone)."','".@mysql_real_escape_string($this->Fax)."','".@mysql_real_escape_string($this->Street)."','".@mysql_real_escape_string($this->ZIP)."','".@mysql_real_escape_string($this->Department)."','".@mysql_real_escape_string($this->City)."','".@mysql_real_escape_string($this->Country)."','".@mysql_real_escape_string($this->Gender)."','".@mysql_real_escape_string($this->Languages)."','".@mysql_real_escape_string($this->Comments)."','".@mysql_real_escape_string($this->Public)."');");
	}
}

class DataInput
{
	public $Index;
	public $Caption = "";
    public $InfoText = "";
	public $Type;
	public $Active;
	public $InputValue = "";
	public $Cookie;
	public $Custom;
	public $Name;
	public $Position;
	public $Validate;
	public $ValidationURL;
	public $ValidationTimeout = 15;
	public $ValidationContinueOnTimeout;

	function DataInput($_values)
	{
		global $LZLANG;
		if($_values != null)
		{
			$_values = @unserialize(base64_decode($_values));
			array_walk($_values,"b64dcode");
			$this->Index = $_values[0];
			$this->Caption = (strpos($_values[1],"<!--lang") !== false) ? applyReplacements($_values[1],true,false) : $_values[1];
			$this->Name = $_values[2];
			$this->Type = $_values[3];
			$this->Active = (empty($_GET["ofc"]) || $this->Index!=116) ? !empty($_values[4]) : true;
			$this->Cookie = !empty($_values[5]);
			$this->Position = $_values[6];
			$this->InputValue = (strpos($_values[7],"<!--lang") !== false) ? applyReplacements($_values[7],true,false) : $_values[7];
			$this->Custom = ($this->Index<100);
			$this->Validate = !empty($_values[8]);
			$this->ValidationURL = $_values[9];
			$this->ValidationTimeout = $_values[10];
			$this->ValidationContinueOnTimeout = !empty($_values[11]);

            if(count($_values) > 12)
                $this->InfoText = $_values[12];
		}
		else
		{
			$this->Index = 115;
			$this->Caption = @$LZLANG["client_voucher_id"];
			$this->Name = "chat_voucher_id";
			$this->Custom = false;
			$this->Position = 10000;
			$this->Cookie = false;
			$this->Active = true;
			$this->Validate = false;
			$this->Type = "Text";
		}
	}
	
	function GetHTML($_maxlength,$_active)
	{
		$template = (($this->Type == "Text") ? getFile(PATH_TEMPLATES . "login_input.tpl") : (($this->Type == "TextArea") ? getFile(PATH_TEMPLATES . "login_area.tpl") : (($this->Type == "ComboBox") ? getFile(PATH_TEMPLATES . "login_combo.tpl") : getFile(PATH_TEMPLATES . "login_check.tpl"))));
		$template = str_replace("<!--maxlength-->",$_maxlength,$template);
		$template = str_replace("<!--caption-->",$this->Caption,$template);
        $template = str_replace("<!--info_text-->",$this->InfoText,$template);
		$template = str_replace("<!--name-->",$this->Index,$template);
		$template = str_replace("<!--active-->",parseBool($_active),$template);
		if($this->Type == "ComboBox")
		{
			$options = "";
			$parts = explode(";",$this->InputValue);
			foreach($parts as $ind => $part)
				$options .= "<option value=\"".$ind."\">".$part."</option>";
			$template = str_replace("<!--options-->",$options,$template);
		}
		return $template;
	}
	
	function GetValue($_browser)
	{
		if($this->Custom && !empty($_browser->Customs[$this->Index]))
			return $_browser->Customs[$this->Index];
		else if($this->Index == 111)
			return $_browser->Fullname;
		else if($this->Index == 112)
			return $_browser->Email;
		else if($this->Index == 113)
			return $_browser->Company;
		else if($this->Index == 114)
			return $_browser->Question;
		else if($this->Index == 116)
			return $_browser->Phone;
		else
			return "";
	}
	
	function GetClientValue($_userInput)
	{
		if($this->Type == "ComboBox" && !empty($this->InputValue) && is_numeric($_userInput))
		{
			$parts = explode(";",$this->InputValue);
			return $parts[$_userInput];
		}
		return $_userInput;
	}
	
	function GetJavascript($_value)
	{
		return "new lz_chat_input(".$this->Index.",".parseBool($this->Active).",'".base64_encode($this->Caption)."','".base64_encode($this->InfoText)."','".base64_encode($this->Name)."','".$this->Type."','".base64_encode($this->GetPreselectionValue($_value))."',".parseBool($this->Validate).",'".base64_encode($this->ValidationURL)."',".$this->ValidationTimeout.",".parseBool($this->ValidationContinueOnTimeout).")";
	}
	
	function GetIndexName()
	{
		$getIndex = array(111=>GET_EXTERN_USER_NAME,112=>GET_EXTERN_USER_EMAIL,113=>GET_EXTERN_USER_COMPANY,114=>GET_EXTERN_USER_QUESTION,115=>"vc",116=>"ep");
		if(isset($getIndex[$this->Index]))
			return $getIndex[$this->Index];
		else
			return null;
	}
	
	function GetPreselectionValue($_value)
	{
		if($this->Type == "CheckBox" || $this->Type == "ComboBox")
		{
			return (!empty($_value)) ? $_value : "0";
		}
		else
		{
			if(empty($_value) && !empty($this->InputValue))
				return $this->InputValue;
			return $_value;
		}
	}
	
	function GetCookieValue()
	{
		return ((!$this->Custom) ? getCookieValue("form_" . $this->Index) : getCookieValue("cf_" . $this->Index));
	}
}


class CommercialChatPaymentProvider extends BaseObject
{
	public $Name;
	public $Account;
	public $URL;
	public $LogoURL;
	
	function CommercialChatPaymentProvider()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
			$this->Id = $row["id"];
            $this->Name = $row["name"];
            $this->Account = $row["account"];
			$this->URL = $row["URL"];
			$this->LogoURL = $row["logo"];
		}
		else
		{
            $this->Id = func_get_arg(0);
            $this->Name = func_get_arg(1);
            $this->Account = func_get_arg(2);
            $this->URL = func_get_arg(3);
			$this->LogoURL = func_get_arg(4);
		}
   	}
	
	function GetXML()
	{
		return "<ccpp id=\"".base64_encode($this->Id)."\" n=\"".base64_encode($this->Name)."\" l=\"".base64_encode($this->LogoURL)."\" a=\"".base64_encode($this->Account)."\" u=\"".base64_encode($this->URL)."\" />\r\n";
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_PROVIDERS."` (`id`, `name`, `account`, `URL`, `logo`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->Name)."','".@mysql_real_escape_string($this->Account)."','".@mysql_real_escape_string($this->URL)."','".@mysql_real_escape_string($this->LogoURL)."');");
		if(@mysql_affected_rows() <= 0)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_PROVIDERS."` SET `name`='".@mysql_real_escape_string($this->Name)."',`account`='".@mysql_real_escape_string($this->Account)."', `URL`='".@mysql_real_escape_string($this->URL)."', `logo`='".@mysql_real_escape_string($this->LogoURL)."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}


class CommercialChatVoucherLocalization extends BaseObject
{
	public $LanguageISOTwoLetter;
	public $Title;
	public $Description;
	public $Terms;
	public $EmailVoucherCreated;
	public $EmailVoucherPaid;
	public $EmailVoucherUpdate;
	public $ExtensionRequest;
	
	function CommercialChatVoucherLocalization()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
			$this->Id = $row["id"];
            $this->LanguageISOTwoLetter = $row["language"];
            $this->Title = $row["title"];
			$this->Description = $row["description"];
			$this->Terms = $row["terms"];
			$this->EmailVoucherCreated = $row["email_voucher_created"];
			$this->EmailVoucherPaid = $row["email_voucher_paid"];
			$this->EmailVoucherUpdate = $row["email_voucher_update"];
			$this->ExtensionRequest = $row["extension_request"];
		}
		else
		{
            $this->Id = func_get_arg(0);
            $this->LanguageISOTwoLetter = func_get_arg(1);
            $this->Title = func_get_arg(2);
			$this->Description = func_get_arg(3);
			$this->Terms = func_get_arg(4);
			$this->EmailVoucherCreated = func_get_arg(5);
			$this->EmailVoucherPaid = func_get_arg(6);
			$this->EmailVoucherUpdate = func_get_arg(7);
			$this->ExtensionRequest = func_get_arg(8);
		}
   	}
	
	function GetXML()
	{
		return "<cctl id=\"".base64_encode($this->Id)."\" litl=\"".base64_encode($this->LanguageISOTwoLetter)."\" t=\"".base64_encode($this->Title)."\" d=\"".base64_encode($this->Description)."\" emvc=\"".base64_encode($this->EmailVoucherCreated)."\" exr=\"".base64_encode($this->ExtensionRequest)."\" emvp=\"".base64_encode($this->EmailVoucherPaid)."\" emvu=\"".base64_encode($this->EmailVoucherUpdate)."\">".base64_encode($this->Terms)."</cctl>";
	}

	function Save($_parentId)
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_LOCALIZATIONS."` (`id`, `tid`, `language`, `title`, `description`, `terms`, `email_voucher_created`, `email_voucher_paid`,`email_voucher_update`, `extension_request`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($_parentId)."','".@mysql_real_escape_string($this->LanguageISOTwoLetter)."','".@mysql_real_escape_string($this->Title)."','".@mysql_real_escape_string($this->Description)."','".@mysql_real_escape_string($this->Terms)."','".@mysql_real_escape_string($this->EmailVoucherCreated)."','".@mysql_real_escape_string($this->EmailVoucherPaid)."','".@mysql_real_escape_string($this->EmailVoucherUpdate)."','".@mysql_real_escape_string($this->ExtensionRequest)."');");
	}
}

class CommercialChatBillingType extends BaseObject
{
	public $Localizations;
	public $ChatSessionsMax;
	public $ChatTimeMax;
	public $VoucherAutoExpire;
	public $VoucherTimeVoidByOperator;
	public $VoucherSessionVoidByOperator;
	public $VoucherExpireVoidByOperator;
	public $CurrencyISOThreeLetter;
	public $Price;
	public $VAT = 0;
	
	function CommercialChatBillingType()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
			$this->Localizations = array();
			$this->Id = $row["typeid"];
            $this->ChatSessionsMax = $row["number_of_chats"];
            $this->ChatTimeMax = $row["total_length"];
            $this->VoucherAutoExpire = $row["auto_expire"];
            $this->VoucherTimeVoidByOperator = !empty($row["total_length_void"]);
			$this->VoucherSessionVoidByOperator = !empty($row["number_of_chats_void"]);
			$this->VoucherExpireVoidByOperator = !empty($row["auto_expire_void"]);
			$this->CurrencyISOThreeLetter = $row["currency"];
            $this->Price = $row["price"];
		}
		else
		{
            $this->Id = func_get_arg(0);
            $this->ChatSessionsMax = func_get_arg(1);
            $this->ChatTimeMax = func_get_arg(2);
            $this->VoucherAutoExpire = func_get_arg(3);
            $this->VoucherTimeVoidByOperator = !isnull(func_get_arg(4));
			$this->VoucherSessionVoidByOperator = !isnull(func_get_arg(5));
			$this->VoucherExpireVoidByOperator = !isnull(func_get_arg(6));
			$this->CurrencyISOThreeLetter = func_get_arg(7);
			$price = func_get_arg(8);
            $this->Price = str_replace(",",".",$price);
		}
   	}
	
	function GetLocalization($_language="")
	{
		global $CONFIG,$DEFAULT_BROWSER_LANGUAGE;
		$loc = null;
		if(!empty($DEFAULT_BROWSER_LANGUAGE) && isset($this->Localizations[strtoupper($DEFAULT_BROWSER_LANGUAGE)]))
			$loc = $this->Localizations[strtoupper($DEFAULT_BROWSER_LANGUAGE)];
		else if(!empty($_language) && isset($this->Localizations[strtoupper($_language)]))
			$loc = $this->Localizations[strtoupper($_language)];
		else if(isset($this->Localizations[strtoupper($CONFIG["gl_default_language"])]))
			$loc = $this->Localizations[strtoupper($CONFIG["gl_default_language"])];
		else
		{
			foreach($this->Localizations as $localization)
			{
				$loc = $localization;
				break;
			}
		}
		return $loc;
	}
	
	function GetTemplate()
	{
		global $CONFIG;
		$loc = $this->GetLocalization();
		$html = str_replace("<!--title-->",$loc->Title,getFile(PATH_TEMPLATES . "chat_voucher_type.tpl"));
		$html = str_replace("<!--price-->",number_format($this->Price,2),$html);
		$html = str_replace("<!--vat_amount-->",number_format(((!empty($CONFIG["gl_ccsv"])) ? ($this->Price*($CONFIG["gl_ccva"]/100)) : 0),2),$html);
		$html = str_replace("<!--price_unformatted-->",$this->Price,$html);
		$html = str_replace("<!--description-->",$loc->Description,$html);
		$html = str_replace("<!--terms-->",base64_encode($loc->Terms),$html);
		$html = str_replace("<!--currency-->",$this->CurrencyISOThreeLetter,$html);
		$html = str_replace("<!--id-->",$this->Id,$html);
		return $html;
	}
	
	function GetXML()
	{
		$xml = "<cctt id=\"".base64_encode($this->Id)."\" citl=\"".base64_encode($this->CurrencyISOThreeLetter)."\" p=\"".base64_encode($this->Price)."\" mnoc=\"".base64_encode($this->ChatSessionsMax)."\" mtl=\"".base64_encode($this->ChatTimeMax)."\" tae=\"".base64_encode($this->VoucherAutoExpire)."\" svbo=\"".base64_encode(($this->VoucherSessionVoidByOperator) ? "1" : "0")."\" tvbo=\"".base64_encode(($this->VoucherTimeVoidByOperator) ? "1" : "0")."\" evbo=\"".base64_encode(($this->VoucherExpireVoidByOperator) ? "1" : "0")."\">\r\n";
		foreach($this->Localizations as $loki)
			$xml .= $loki->GetXML();
		return $xml . "</cctt>\r\n";
	}

	function Save()
	{
		queryDB(true,"REPLACE INTO `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` (`id`, `number_of_chats`,`number_of_chats_void`, `total_length`, `total_length_void`, `auto_expire`,`auto_expire_void`, `delete`, `price`, `currency`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->ChatSessionsMax)."','".@mysql_real_escape_string(($this->VoucherSessionVoidByOperator) ? 1 : 0)."','".@mysql_real_escape_string($this->ChatTimeMax)."','".@mysql_real_escape_string(($this->VoucherTimeVoidByOperator) ? 1 : 0)."','".@mysql_real_escape_string($this->VoucherAutoExpire)."','".@mysql_real_escape_string(($this->VoucherExpireVoidByOperator) ? 1 : 0)."','0','".@mysql_real_escape_string($this->Price)."','".@mysql_real_escape_string($this->CurrencyISOThreeLetter)."');");
		if(@mysql_affected_rows() <= 0)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` SET `number_of_chats`='".@mysql_real_escape_string($this->ChatSessionsMax)."',`total_length`='".@mysql_real_escape_string($this->ChatTimeMax)."', `auto_expire`='".@mysql_real_escape_string($this->VoucherAutoExpire)."', `currency`='".@mysql_real_escape_string($this->CurrencyISOThreeLetter)."',`price`='".@mysql_real_escape_string($this->Price)."', `auto_expire_void`='".@mysql_real_escape_string(($this->VoucherExpireVoidByOperator) ? 1 : 0)."', `total_length_void`='".@mysql_real_escape_string(($this->VoucherTimeVoidByOperator) ? 1 : 0)."', `number_of_chats_void`='".@mysql_real_escape_string(($this->VoucherSessionVoidByOperator) ? 1 : 0)."', `delete`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class CommercialChatVoucher extends CommercialChatBillingType
{
	public $Voided;
	public $ChatTime;
	public $ChatDays;
	public $ChatSessions;
	public $ChatTimeRemaining;
	public $ChatDaysRemaining;
	public $ChatSessionsMax;
	public $ChatIdList;
	public $TypeId;
	public $Created;
	public $Edited;
	public $Email;
	public $LastUsed;
	public $FirstUsed;
	public $VisitorId;
	public $BusinessType;
	public $Company;
	public $TaxID;
	public $Paid;
	public $Firstname;
	public $Lastname;
	public $TransactionId;
	public $Address1;
	public $Address2;
	public $ZIP;
	public $State;
	public $Country;
	public $Phone;
	public $City;
	public $PayerId;
	public $PaymentDetails;
	public $Language;
	public $Extends;
	
	function CommercialChatVoucher()
   	{
		if(func_num_args() == 1)
		{
			$this->SetDetails(func_get_arg(0));
		}
		else if(func_num_args() == 2)
		{
			$this->TypeId = func_get_arg(0);
			$this->Id = func_get_arg(1);
		}
	}
	
	function SetDetails($row)
	{
		$this->Id = $row["ticketid"];
		$this->Created = $row["created"];
		$this->LastUsed = $row["last_used"];
		$this->FirstUsed = $row["first_used"];
		$this->TypeId = $row["id"];
		$this->Email = $row["email"];
		$this->Language = $row["language"];
		$this->Voided = !empty($row["voided"]);
		$this->Edited = $row["edited"];
		$this->Extends = $row["extends"];
		if(!empty($row["chat_time_max"]))
		{
			$this->ChatTimeRemaining = $row["chat_time_max"]-$row["chat_time"];
			$this->ChatTimeMax = $row["chat_time_max"];
		}
		else
		{
			$this->ChatTimeMax = -1;
			$this->ChatTimeRemaining = -1;
		}
		
		if(!empty($row["chat_sessions_max"]))
		{
			$this->ChatSessionsMax = $row["chat_sessions_max"];
		}
		else
		{
			$this->ChatSessionsMax = -1;
		}
			
		if(!empty($row["expires"]))
		{
			$this->ChatDaysRemaining = floor(($row["expires"]-time())/86400);
			$this->VoucherAutoExpire = $row["expires"];
		}
		else
		{
			$this->ChatDaysRemaining =
			$this->VoucherAutoExpire = -1;
		}
		$this->ChatDays = floor((time()-$row["created"])/86400);
		$this->ChatTime = $row["chat_time"];
		$this->ChatSessions = $row["chat_sessions"];
		
		$this->Voided = !empty($row["voided"]);
		$this->Paid = !empty($row["paid"]);
		$this->ChatIdList = @unserialize($row["chat_list"]);
		
        $this->VoucherTimeVoidByOperator = !empty($row["total_length_void"]);
		$this->VoucherSessionVoidByOperator = !empty($row["number_of_chats_void"]);
		$this->VoucherExpireVoidByOperator = !empty($row["auto_expire_void"]);
		$this->VisitorId = $row["visitor_id"];
		$this->BusinessType = $row["business_type"];
		$this->Company = $row["company"];
		$this->TaxID = $row["tax_id"];
		$this->Firstname = $row["firstname"];
		$this->Lastname = $row["lastname"];
		$this->Address1 = $row["address_1"];
		$this->Address2 = $row["address_2"];
		$this->TransactionId = $row["tn_id"];
		$this->ZIP = $row["zip"];
		$this->Price = $row["price"];
		$this->VAT = $row["vat"];
		$this->State = $row["state"];
		$this->Country = $row["country"];
		$this->Phone = $row["phone"];
		$this->City = $row["city"];
		$this->PayerId = $row["payer_id"];
		$this->PaymentDetails = $row["payment_details"];
		$this->CurrencyISOThreeLetter = $row["currency"];
	}
	
	function Load()
	{
		if($result = queryDB(true,"SELECT *,`t1`.`id` AS `ticketid` FROM `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` AS `t1` INNER JOIN `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_TYPES."` AS `t2` ON `t1`.`tid`=`t2`.`id` WHERE `t1`.`id`='".@mysql_real_escape_string($this->Id)."';"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$this->SetDetails($row);
				return true;
			}
		return false;
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` (`id`, `extends`, `tid`, `email`, `info`, `created`, `expires`, `edited`, `chat_sessions_max`, `chat_time_max`,
		`chat_list`, `visitor_id`, `company`, `tax_id`, `firstname`, `lastname`, `address_1`, `address_2`, `zip`, `state`, `phone`, `city`, `country`, `tn_id`, `price`, `currency`, `vat`, `payment_details`, `language`) 
		VALUES (
		'".@mysql_real_escape_string($this->Id)."',
		'".@mysql_real_escape_string($this->Extends)."',
		'".@mysql_real_escape_string($this->TypeId)."',
		'".@mysql_real_escape_string($this->Email)."',
		'".@mysql_real_escape_string("")."',
		'".@mysql_real_escape_string(time())."',
		'".@mysql_real_escape_string(0)."',
		'".@mysql_real_escape_string(time())."',
		'".@mysql_real_escape_string($this->ChatSessionsMax)."',
		'".@mysql_real_escape_string($this->ChatTimeMax)."',
		'".@mysql_real_escape_string(@serialize($this->ChatIdList))."',
		'".@mysql_real_escape_string($this->VisitorId)."',
		'".@mysql_real_escape_string($this->Company)."',
		'".@mysql_real_escape_string($this->TaxID)."',
		'".@mysql_real_escape_string($this->Firstname)."',
		'".@mysql_real_escape_string($this->Lastname)."',
		'".@mysql_real_escape_string($this->Address1)."',
		'".@mysql_real_escape_string($this->Address2)."',
		'".@mysql_real_escape_string($this->ZIP)."',
		'".@mysql_real_escape_string($this->State)."',
		'".@mysql_real_escape_string($this->Phone)."',
		'".@mysql_real_escape_string($this->City)."',
		'".@mysql_real_escape_string($this->Country)."',
		'".@mysql_real_escape_string($this->TransactionId)."',
		'".@mysql_real_escape_string($this->Price)."',
		'".@mysql_real_escape_string($this->CurrencyISOThreeLetter)."',
		'".@mysql_real_escape_string($this->VAT)."',
		'".@mysql_real_escape_string($this->PaymentDetails)."',
		'".@mysql_real_escape_string($this->Language)."');");
	}
	
	function GetXml($_reduced=false)
	{
		if($_reduced)
			return "<val id=\"".base64_encode($this->Id)."\" />";
		else
		return "<val 
		id=\"".base64_encode($this->Id)."\" 
		ex=\"".base64_encode($this->Extends)."\" 
		pd=\"".base64_encode(($this->Paid) ? 1 : 0)."\" 
		vid=\"".base64_encode($this->VisitorId)."\" 
		bt=\"".base64_encode($this->BusinessType)."\" 
		cp=\"".base64_encode($this->Company)."\" 
		txid=\"".base64_encode($this->TaxID)."\" 
		fn=\"".base64_encode($this->Firstname)."\" 
		ln=\"".base64_encode($this->Lastname)."\" 
		a1=\"".base64_encode($this->Address1)."\" 
		a2=\"".base64_encode($this->Address2)."\" 
		zip=\"".base64_encode($this->ZIP)."\" 
		st=\"".base64_encode($this->State)."\" 
		ph=\"".base64_encode($this->Phone)."\" 
		cty=\"".base64_encode($this->City)."\" 
		ctry=\"".base64_encode($this->Country)."\" 
		cr=\"".base64_encode($this->Created)."\" 
		fu=\"".base64_encode($this->FirstUsed)."\" 
		lu=\"".base64_encode($this->LastUsed)."\" 
		ed=\"".base64_encode($this->Edited)."\" 
		em=\"".base64_encode($this->Email)."\" 
		tae=\"".base64_encode($this->VoucherAutoExpire)."\" 
		mtcl=\"".base64_encode($this->ChatTimeMax)."\" 
		tv=\"".base64_encode(($this->Voided) ? 1 : 0)."\" 
		tid=\"".base64_encode($this->TypeId)."\" 
		cd=\"".base64_encode($this->ChatDays)."\" 
		ct=\"".base64_encode($this->ChatTime)."\" 
		cs=\"".base64_encode($this->ChatSessions)."\" 
		cdr=\"".base64_encode($this->ChatDaysRemaining)."\" 
		ctr=\"".base64_encode($this->ChatTimeRemaining)."\" 
		txnid=\"".base64_encode($this->TransactionId)."\" 
		pr=\"".base64_encode($this->Price)."\" 
		pyi=\"".base64_encode($this->PayerId)."\" 
		vat=\"".base64_encode($this->VAT)."\" 
		cur=\"".base64_encode($this->CurrencyISOThreeLetter)."\" 
		csr=\"".base64_encode($this->ChatSessionsMax)."\">".base64_encode($this->PaymentDetails)."</val>\r\n";
	}
	
	function UpdateVoucherChatTime($_timeToAdd,$_firstUse=false)
	{
		if(is_numeric($_timeToAdd))
		{
			$this->ChatTimeRemaining -= $_timeToAdd;
			$this->ChatTime += $_timeToAdd;
			if(!empty($_timeToAdd))
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `chat_time`=`chat_time`+".$_timeToAdd." WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
			
			if(empty($_timeToAdd) || ($this->Edited < (time()-180)))
			{
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `edited`=UNIX_TIMESTAMP() WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
			}
			if($_firstUse)
				queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `first_used`=UNIX_TIMESTAMP() WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		}
	}
	
	function UpdateVoucherChatSessions($_chatId)
	{
		if(is_array($this->ChatIdList) && !empty($this->ChatIdList[$_chatId]))
			return;
			
		$this->ChatIdList[$_chatId] = true;
		if(!empty($this->ChatSessionsMax))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `edited`=UNIX_TIMESTAMP(),`last_used`=UNIX_TIMESTAMP(),`chat_sessions`=`chat_sessions`+1,`chat_list`='".@mysql_real_escape_string(@serialize($this->ChatIdList))."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	function CheckForVoid()
	{
		global $CONFIG;
		if(!$this->Voided)
		{
			if(($this->ChatSessionsMax-$this->ChatSessions) <= 0 && $this->ChatSessionsMax > -1 && !$this->VoucherSessionVoidByOperator)
				$this->Void();
			else if($this->ChatTime >= $this->ChatTimeMax && $this->ChatTimeMax > 0 && !$this->VoucherTimeVoidByOperator)
				$this->Void();
			else if($this->VoucherAutoExpire <= time() && $this->VoucherAutoExpire > 0 && !$this->VoucherExpireVoidByOperator)
				$this->Void();
		}
		if($this->VoucherAutoExpire <= 0 && !empty($CONFIG["db"]["cct"][$this->TypeId]->VoucherAutoExpire))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `expires`=".($this->VoucherAutoExpire=(time()+(86400*$CONFIG["db"]["cct"][$this->TypeId]->VoucherAutoExpire)))." WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		return $this->Voided;
	}
	
	function Void()
	{
		CommercialChatVoucher::SetVoucherParams(true,$this->Paid);
		$this->Voided = true;
	}
	
	function GetVoucherChatURL($_purchasedForGroup="")
	{
		global $CONFIG;
		if(!empty($_purchasedForGroup))
			$_purchasedForGroup = "&intgroup=" . base64UrlEncode($_purchasedForGroup);
		$ws = (empty($CONFIG["gl_root"])) ? "&ws=" . base64UrlEncode($CONFIG["gl_host"]) : "";
		return LIVEZILLA_URL . FILE_CHAT . "?vc=" .  base64UrlEncode($this->Id) . $_purchasedForGroup . $ws;
	}
	
	function SendPaidEmail($_purchasedForGroup="")
	{
		global $CONFIG,$LZLANG;
		$loc = $CONFIG["db"]["cct"][$this->TypeId]->GetLocalization($this->Language);
		if($loc != null && !empty($loc->EmailVoucherPaid))
		{
			$email = $loc->EmailVoucherPaid;
			$email = str_replace("%buyer_first_name%",$this->Firstname,$email);
			$email = str_replace("%buyer_last_name%",$this->Lastname,$email);
			$email = str_replace("%voucher_code%",$this->Id,$email);
			$email = str_replace("%website_name%",$CONFIG["gl_site_name"],$email);
			$email = str_replace("%chat_url%",$this->GetVoucherChatURL($_purchasedForGroup),$email);
			languageSelect($loc->LanguageISOTwoLetter);
            $defmailbox=Mailbox::GetDefaultOutgoing();
            if($defmailbox != null)
			    sendMail($defmailbox,$this->Email,$defmailbox->Email,$email,$LZLANG["client_voucher_email_subject_paid"]);
		}
	}
	
	function SendCreatedEmail()
	{
		global $CONFIG,$LZLANG;
		$loc = $CONFIG["db"]["cct"][$this->TypeId]->GetLocalization($this->Language);
		if($loc != null && !empty($loc->EmailVoucherCreated))
		{
			$email = $loc->EmailVoucherCreated;
			$email = str_replace("%buyer_first_name%",$this->Firstname,$email);
			$email = str_replace("%buyer_last_name%",$this->Lastname,$email);
			$email = str_replace("%voucher_code%",$this->Id,$email);
			$email = str_replace("%website_name%",$CONFIG["gl_site_name"],$email);
			$email = str_replace("%chat_url%",$this->GetVoucherChatURL(""),$email);
            $defmailbox=Mailbox::GetDefaultOutgoing();
            if($defmailbox != null)
			    sendMail($defmailbox,$this->Email,$defmailbox->Email,$email,$LZLANG["client_voucher_email_subject_created"]);
		}
	}
	
	function SendStatusEmail()
	{
		global $CONFIG,$LZLANG;
		if(!empty($CONFIG["db"]["cct"][$this->TypeId]))
		{
			$loc = $CONFIG["db"]["cct"][$this->TypeId]->GetLocalization($this->Language);
			if($loc != null && !empty($loc->EmailVoucherUpdate))
			{
				$email = $loc->EmailVoucherUpdate;
				$email = str_replace("%buyer_first_name%",$this->Firstname,$email);
				$email = str_replace("%buyer_last_name%",$this->Lastname,$email);
				$email = str_replace("%voucher_code%",$this->Id,$email);
				$email = str_replace("%voucher_remaining_time%",(($this->ChatTimeRemaining == -1) ? "-" : (($this->ChatTimeRemaining >=0) ? formatTimeSpan($this->ChatTimeRemaining) : formatTimeSpan(0))),$email);
				$email = str_replace("%voucher_remaining_sessions%",(($this->ChatSessionsMax == -1) ? "-" : (($this->ChatSessionsMax-$this->ChatSessions >=0) ? $this->ChatSessionsMax-$this->ChatSessions : 0)),$email);
				$email = str_replace("%voucher_expiration_date%",(($this->VoucherAutoExpire == -1) ? "-" : date("r",$this->VoucherAutoExpire)),$email);
				$email = str_replace("%website_name%",$CONFIG["gl_site_name"],$email);
                $defmailbox=Mailbox::GetDefaultOutgoing();
                if($defmailbox != null)
				    sendMail($defmailbox,$this->Email,$defmailbox->Email,$email,$LZLANG["client_voucher_email_subject_status_update"]);
			}
		}
	}
	
	function SetVoucherParams($_void=true, $_paid=false, $_addHour=false, $_addSession=false, $_addDay=false, $_email=false, $_purchasedForGroup="")
	{
		global $CONFIG;

		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `voided`=".(($_void) ? 1 : 0).",`paid`=".(($_paid) ? 1 : 0).",`edited`=UNIX_TIMESTAMP() WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($_addHour)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `chat_time_max`=`chat_time_max`+3600,`edited`=UNIX_TIMESTAMP() WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($_addSession)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `chat_sessions_max`=`chat_sessions_max`+1,`edited`=UNIX_TIMESTAMP() WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($_addDay)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `expires`=`expires`+86400,`edited`=UNIX_TIMESTAMP() WHERE `expires`>0 AND `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	
		if($_email)
			$this->SendPaidEmail($_purchasedForGroup);
		
		if($_paid && !$this->Paid && !empty($this->Extends))
		{
			$ex = ($this->VoucherAutoExpire <= 0 && !empty($CONFIG["db"]["cct"][$this->TypeId]->VoucherAutoExpire)) ? ",`expires`=".($this->VoucherAutoExpire=(time()+(86400*$CONFIG["db"]["cct"][$this->TypeId]->VoucherAutoExpire))) : "";
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `first_used`=UNIX_TIMESTAMP()".$ex." WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		}
	}
	
	function SetPaymentDetails($_transactionId,$_payerId,$_details)
	{
		$_details = $this->PaymentDetails . date("r") . ":\r\n..............................................................................................................................................\r\n" . $_details . "\r\n\r\n";
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_COMMERCIAL_CHAT_VOUCHERS."` SET `edited`=UNIX_TIMESTAMP(),`tn_id`='".@mysql_real_escape_string($_transactionId)."',`payer_id`='".@mysql_real_escape_string($_payerId)."',`payment_details`='".@mysql_real_escape_string($_details)."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}
?>