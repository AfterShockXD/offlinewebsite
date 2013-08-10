<?php
/****************************************************************************************
* LiveZilla functions.data.db.update.inc.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

function updateDatabase($_version,$_link,$_prefix)
{
	$versions = array("3.1.8.1","3.1.8.2","3.1.8.3","3.1.8.4","3.1.8.5","3.1.8.6","3.2.0.0","3.2.0.1","3.2.0.2","3.2.0.3","3.3.0.0","3.3.1.0","3.3.1.1","3.3.1.2","3.3.1.3","3.3.2.0","3.3.2.1","3.3.2.2","3.4.0.0","4.0.0.0","4.0.1.0","4.0.1.1","4.0.1.2","4.1.0.0","4.1.0.1","4.1.0.2","4.1.0.3","4.1.0.4","4.2.0.0","4.2.0.1","4.2.0.2","4.2.0.3","4.2.0.4","4.2.0.5","5.0.0.0","5.0.1.0");
	if(!in_array($_version,$versions))
		return "Invalid version! (".$_version.")";
	
	while($_version != VERSION)
	{
		if($_version == $versions[3])$_version = $versions[4];
		if($_version == $versions[4])$_version = $versions[5];
		if($_version == $versions[5])
		{
			$result = up_3186_3200($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[6];
			else
				return $result;
		}
		if($_version == $versions[6])
		{
			$result = up_3200_3201($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[7];
			else
				return $result;
		}
		if($_version == $versions[7])$_version = $versions[9];
		if($_version == $versions[8])$_version = $versions[9];
		if($_version == $versions[9])
		{
			$result = up_3203_3300($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[10];
			else
				return $result;
		}
		if($_version == $versions[10])
		{
			$result = up_3300_3310($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[11];
			else
				return $result;
		}
		if($_version == $versions[11])
		{
			$result = up_3310_3311($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[12];
			else
				return $result;
		}
		if($_version == $versions[12])
		{
			$result = up_3311_3312($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[13];
			else
				return $result;
		}
		if($_version == $versions[13])$_version = $versions[14];
		if($_version == $versions[14])
		{
			$result = up_3313_3320($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[15];
			else
				return $result;
		}
		if($_version == $versions[15])$_version = $versions[16];
		if($_version == $versions[16])$_version = $versions[17];
		if($_version == $versions[17])
		{
			$result = up_3322_3400($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[18];
			else
				return $result;
		}
		if($_version == $versions[18])
		{
			$result = up_3400_4000($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[19];
			else
				return $result;
		}
		if($_version == $versions[19])
		{
			$result = up_4000_4010($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[20];
			else
				return $result;
		}
		if($_version == $versions[20])$_version = $versions[21];
		if($_version == $versions[21])$_version = $versions[22];
		if($_version == $versions[22])
		{
			$result = up_4012_4100($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[23];
			else
				return $result;
		}
		if($_version == $versions[23])$_version = $versions[24];
		if($_version == $versions[24])
		{
			$result = up_4101_4102($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[25];
			else
				return $result;
		}
		if($_version == $versions[25])
		{
			$result = up_4102_4103($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[26];
			else
				return $result;
		}
		if($_version == $versions[26])$_version = $versions[27];
		if($_version == $versions[27])
		{
			$result = up_4104_4200($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[28];
			else
				return $result;
		}
		if($_version == $versions[28])
		{
			$result = up_4200_4201($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[29];
			else
				return $result;
		}
		if($_version == $versions[29])
		{
			$result = up_4201_4202($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[30];
			else
				return $result;
		}
		if($_version == $versions[30])$_version = $versions[31];
		if($_version == $versions[31])$_version = $versions[32];
		if($_version == $versions[32])$_version = $versions[33];
		if($_version == $versions[33])
		{
			$result = up_4205_5000($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[34];
			else
				return $result;
		}
        if($_version == $versions[34])$_version = $versions[35];
	}
	@mysql_query("UPDATE `".@mysql_real_escape_string($_prefix)."info` SET `version`='" . VERSION . "'",$_link);
	return true;
}

function processCommandList($_commands,$_link)
{
	foreach($_commands as $parts)
	{
		$result = @mysql_query($parts[1],$_link);
		if(!$result && mysql_errno() != $parts[0] && $parts[0] != 0 && count($parts) == 2)
			return mysql_errno() . ": " . mysql_error() . "\r\n\r\nMySQL Query: " . $parts[1];
	}
	return true;
}

function up_4205_5000($_prefix,$_link)
{
    global $CONFIG;
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` CHANGE `id` `id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1054,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` CHANGE `internal_fullname` `editor_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD `sender_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD `channel_id` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."ticket_messages` SET `channel_id`=`id`;");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD `created` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `time`;");
    $commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."ticket_messages` SET `created`=`time`;");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_customs` ADD `message_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `ticket_id`;");
    $commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."ticket_customs` SET `message_id`=`ticket_id`;");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_customs` DROP PRIMARY KEY ,ADD PRIMARY KEY ( `ticket_id` , `custom_id` , `message_id` );");
    $commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD UNIQUE `channel_id` (`channel_id`);");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."tickets` ADD `hash` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."tickets` ADD `creation_type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."tickets` ADD `iso_language` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."tickets` ADD `deleted` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
    $commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."tickets` SET `iso_language`='".@mysql_real_escape_string(strtoupper($CONFIG["gl_default_language"]))."' WHERE `iso_language`='';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `ticket_email_out` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `ticket_email_in` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `ticket_handle_unknown` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."resources` ADD `tags` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."signatures` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `name` varchar(256) COLLATE utf8_bin NOT NULL DEFAULT '', `signature` text COLLATE utf8_bin NOT NULL, `operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `group_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `default` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."ticket_emails` (`email_id` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `mailbox_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`sender_email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`sender_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`sender_replyto` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`receiver_email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0',`edited` int(10) unsigned NOT NULL DEFAULT '0',`deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',`subject` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`body` text COLLATE utf8_bin NOT NULL, `group_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`editor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`email_id`,`group_id`), KEY `mailbox_id` (`mailbox_id`), KEY `edited` (`edited`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."ticket_attachments` (`res_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `parent_id` varchar(128) COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`res_id`), KEY `parent_id` (`parent_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
    $commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."mailboxes` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `exec_operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `username` varchar(128) COLLATE utf8_bin NOT NULL DEFAULT '', `password` varchar(128) COLLATE utf8_bin NOT NULL DEFAULT '', `type` varchar(16) COLLATE utf8_bin NOT NULL DEFAULT '', `host` varchar(128) COLLATE utf8_bin NOT NULL DEFAULT '', `port` mediumint(8) unsigned NOT NULL DEFAULT '0', `delete` smallint(5) NOT NULL DEFAULT '-1', `authentication` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `sender_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `ssl` tinyint(1) unsigned NOT NULL DEFAULT '0', `default` tinyint(1) unsigned NOT NULL DEFAULT '0',`last_connect` int(10) unsigned NOT NULL DEFAULT '0',`connect_frequency` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
    $commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs` CHANGE `aggregated` `aggregated` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `subject_chat_transcript` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `subject_ticket` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
    $commands[] = array(1054,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `plain` `transcript_text` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `plaintext` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `html`;");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `wait` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `accepted` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '3';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `ended` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` ADD `group_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
    $commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` CHANGE `permissions` `permissions` VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `lweb` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `lapp` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ratings` ADD `chat_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';");
    $commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ratings` ADD INDEX `chat_id` ( `chat_id` );");
    $commands[] = array(1025,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` DROP FOREIGN KEY `visitor_browsers_ibfk_1`;");
    $commands[] = array(1025,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` DROP FOREIGN KEY `event_triggers_ibfk_1`;");
    $commands[] = array(1025,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_goals` DROP FOREIGN KEY `visitor_goals_ibfk_1`;");
    $commands[] = array(1025,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` DROP FOREIGN KEY `visitor_chats_ibfk_1`;");
    $commands[] = array(1025,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` DROP FOREIGN KEY `ticket_messages_ibfk_1`;");
    $commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD `hash` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
    $commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD INDEX `hash` ( `hash` );");
    $res = processCommandList($commands,$_link);
	return $res;
}

function up_4201_4202($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `wm` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `wmohca` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `browser_id` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_chats` ADD `multi` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD `alloc` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1';");
	$res = processCommandList($commands,$_link);
	return $res;
}

function up_4200_4201($_prefix,$_link)
{
	$commands[] = array(1091,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."bot_feeds` DROP INDEX `resource_id`");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."bot_feeds` ADD `language` varchar(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$res = processCommandList($commands,$_link);
	return $res;
}

function up_4104_4200($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `chat_ticket_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD UNIQUE `group_id_2` ( `group_id` ,`lang_iso` ,`internal_id`);");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `chat_vouchers_required` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD `phone` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD `overlay` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD `overlay_container` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `phone` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `company`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `call_me_back` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `phone`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `phone` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `company`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `call_me_back` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `phone`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `welcome_call_me_back` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `welcome`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `call_me_back_info` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `chat_info`;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `id` `id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `voucher_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `response_time` INT( 11 ) NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_chats` ADD `avg_response_time` INT( 11 ) NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD `jtime` INT( 11 ) NOT NULL DEFAULT '0' AFTER `ltime`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `chat_posts` INT( 11 ) NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `queue_posts` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `init_chat_with` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `pre_chat_html` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `post_chat_html` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_chats` ADD `chat_posts` INT( 11 ) NOT NULL DEFAULT '0';");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD INDEX `overlay` ( `overlay` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD INDEX `overlay_container` ( `overlay_container` );");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `bot` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."administration_log` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `type` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',  `value` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,  `time` int(10) unsigned NOT NULL DEFAULT '0',  `user` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',  PRIMARY KEY (`id`),  KEY `time` (`time`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."bot_feeds` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`resource_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`bot_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`tags` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`search_type` tinyint(1) unsigned NOT NULL DEFAULT '0',`answer` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`new_window` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', PRIMARY KEY (`id`), UNIQUE KEY `resource_id` (`resource_id`,`bot_id`),  KEY `tags` (`tags`),  KEY `bot_id` (`bot_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."com_chat_localizations` ( `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `tid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `language` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',  `title` text COLLATE utf8_bin NOT NULL,  `description` text COLLATE utf8_bin NOT NULL,  `terms` longtext COLLATE utf8_bin NOT NULL,`email_voucher_created` text COLLATE utf8_bin NOT NULL,`email_voucher_paid` text COLLATE utf8_bin NOT NULL,`email_voucher_update` text COLLATE utf8_bin NOT NULL,`extension_request` text COLLATE utf8_bin NOT NULL,PRIMARY KEY (`id`),KEY `tid` (`tid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=0 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."com_chat_providers` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`name` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`account` varchar(128) COLLATE utf8_bin NOT NULL DEFAULT '',`URL` varchar(256) COLLATE utf8_bin NOT NULL DEFAULT '',`logo` varchar(256) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=0 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."com_chat_vouchers` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`extends` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`tid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`email` varchar(128) COLLATE utf8_bin NOT NULL DEFAULT '',`info` text COLLATE utf8_bin NOT NULL,`voided` tinyint(1) unsigned NOT NULL DEFAULT '0',`paid` tinyint(1) unsigned NOT NULL DEFAULT '0',`created` int(10) unsigned NOT NULL DEFAULT '0',`first_used` int(10) unsigned NOT NULL DEFAULT '0',`last_used` int(10) unsigned NOT NULL DEFAULT '0',`expires` int(10) unsigned NOT NULL DEFAULT '0',`edited` int(10) unsigned NOT NULL DEFAULT '0',`chat_time` int(10) unsigned NOT NULL DEFAULT '0',`chat_time_max` int(10) unsigned NOT NULL DEFAULT '0',`chat_sessions` int(10) unsigned NOT NULL DEFAULT '0',`chat_sessions_max` int(10) unsigned NOT NULL DEFAULT '0',`chat_list` text COLLATE utf8_bin NOT NULL,`visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`business_type` tinyint(1) unsigned NOT NULL DEFAULT '0',`company` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`tax_id` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`firstname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`lastname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`address_1` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`address_2` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`city` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`state` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`zip` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`country` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',`phone` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`tn_id` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`price` double unsigned NOT NULL DEFAULT '0',`currency` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',`vat` double unsigned NOT NULL DEFAULT '0',`payer_id` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`payment_details` text COLLATE utf8_bin NOT NULL,`language` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),KEY `tid` (`tid`),KEY `created` (`created`),KEY `edited` (`edited`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=0 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."com_chat_types` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`number_of_chats` int(10) unsigned NOT NULL DEFAULT '0',`number_of_chats_void` tinyint(1) unsigned NOT NULL DEFAULT '0',`total_length` int(10) unsigned NOT NULL DEFAULT '0',`total_length_void` tinyint(1) unsigned NOT NULL DEFAULT '0',`auto_expire` int(10) unsigned NOT NULL DEFAULT '0',`auto_expire_void` tinyint(1) unsigned NOT NULL DEFAULT '0',`delete` tinyint(1) unsigned NOT NULL DEFAULT '0',`price` double unsigned NOT NULL DEFAULT '0',`currency` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=0 COLLATE=utf8_bin;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD `phone` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `ip`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD `call_me_back` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `phone`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD `country` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."profile_pictures` ADD INDEX `internal_id` ( `internal_id` );");
	$res = processCommandList($commands,$_link);
	return $res;
}

function up_4102_4103($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."filters` ADD `allow_chats` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_cities` ADD INDEX `city` ( `city` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_isps` ADD INDEX `isp` ( `isp` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_referrers` ADD INDEX `referrer` ( `referrer` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_queries` ADD INDEX `query` ( `query` );");
	$res = processCommandList($commands,$_link);
	return $res;
}

function up_4101_4102($_prefix,$_link)
{
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `browser` ( `browser` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `resolution` ( `resolution` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `language` ( `language` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `country` ( `country` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `timezone` ( `timezone` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `ip` ( `ip` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `visit_latest` ( `visit_latest` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD INDEX `created` ( `created` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD INDEX `last_active` ( `last_active` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD INDEX `last_update` ( `last_update` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD INDEX `is_chat` ( `is_chat` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_data_pages` ADD INDEX `path` ( `path` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_data_pages` ADD INDEX `title` ( `title` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_data_pages` ADD INDEX `area_code` ( `area_code` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_data_pages` ADD INDEX `domain` ( `domain` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_data_domains` ADD INDEX `search` ( `search` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_data_domains` ADD INDEX `external` ( `external` );");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD `is_entrance` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ref_untouched`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD `is_exit` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_entrance`;");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD INDEX `is_exit` ( `is_exit` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD INDEX `is_entrance` ( `is_entrance` );");
	$res = processCommandList($commands,$_link);
	return $res;
}

function up_4012_4100($_prefix,$_link)
{
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."operators`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `groups` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `last_chat_allocation`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `groups_hidden` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `groups_status`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `fullname` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `login_id`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `fullname`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `fullname`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `permissions` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `email`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `webspace` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `permissions`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `languages` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `auto_accept_chats` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `login_ip_range` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `password_change` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `password`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `password_change_request` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `system_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `id`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `websites_users` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `websites_config` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `sign_off` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_goals` ADD `query` INT( 11 ) unsigned NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `dynamic` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `description` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `external` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `internal` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `created` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `standard` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `opening_hours` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `functions` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `chat_inputs_hidden` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `ticket_inputs_hidden` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `chat_inputs_required` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `ticket_inputs_required` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `max_chats` INT( 11 ) NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `hide_chat_group_selection` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `hide_ticket_group_selection` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."groups` ADD `visitor_filters` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."images` (`id` INT UNSIGNED NOT NULL DEFAULT '0',`online` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',`button_type` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`image_type` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`data` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`,`button_type`,`image_type`,`online`)) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_goals_queries` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`goal` int(10) unsigned NOT NULL DEFAULT '0',`query` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`goal`,`query`),KEY `target` (`goal`),KEY `query` (`query`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD `pre_message` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$res = processCommandList($commands,$_link);
	
	// import buttons
	importButtons(LIVEZILLA_PATH . "banner/",$_prefix,$_link);
	
	return $res;
}

function up_4000_4010($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `sender_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `repost`;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."groups` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',`owner` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."group_members` (`user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`group_id` varchar(32) COLLATE utf8_bin NOT NULL,PRIMARY KEY (`user_id`,`group_id`),KEY `group_id` (`group_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."group_members` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `queue_message` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `email_ticket`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `queue_message_time` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `queue_message`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `queue_message_shown` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `exit`;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `fullname` `fullname` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `area_code` `area_code` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `email` `email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `company` `company` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	return processCommandList($commands,$_link);
}

function up_3400_4000($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `email_chat_transcript` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `editable`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `email_ticket` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `email_chat_transcript`;");
	$commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `email_chat_transcript`='".@mysql_real_escape_string("Chat Transcript\r\n%website_name% / %group_description%\r\n\r\nDate: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nChat reference number: %chat_id%\r\n-------------------------------------------------------------\r\n%mailtext%")."' WHERE `internal_id`='';");
	$commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `email_chat_transcript`='".@mysql_real_escape_string("Mitschrift Ihres Chats\r\n%website_name% / %group_description%\r\n\r\nDatum: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nChat Referenz-Nummer: %chat_id%\r\n-------------------------------------------------------------\r\n%mailtext%")."' WHERE `internal_id`='' AND `lang_iso`='DE';");
	$commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `email_ticket`='".@mysql_real_escape_string("Thank you, we have received your message!\r\nWe will get in touch with you as soon as possible.\r\n-------------------------------------------------------------\r\nDate: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nGroup: %group_description%\r\n-------------------------------------------------------------\r\n%mailtext%")."' WHERE `internal_id`='';");
	$commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `email_ticket`='".@mysql_real_escape_string("Vielen Dank, wir haben Ihre Nachricht erhalten und werden uns umgehend mit Ihnen in Verbindung setzen.\r\n-------------------------------------------------------------\r\nDatum: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nAbteilung: %group_description%\r\n-------------------------------------------------------------\r\n%mailtext%")."' WHERE `internal_id`='' AND `lang_iso`='DE';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_overlays` CHANGE `background_opacity` `background_opacity` DOUBLE UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` CHANGE `question` `question` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `archive_created` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `allocated`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` DROP PRIMARY KEY ,ADD PRIMARY KEY ( `id` , `receiver`, `micro` );");
	$commands[] = array(0,"UPDATE `".@mysql_real_escape_string($_prefix)."chat_archive` SET `endtime`=`closed` WHERE `endtime`=0;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` CHANGE `fullname` `fullname` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` CHANGE `email` `email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` CHANGE `company` `company` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` CHANGE `ip` `ip` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` CHANGE `ip` `ip` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `ip` `ip` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."filters` CHANGE `ip` `ip` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` CHANGE `ip` `ip` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` CHANGE `typing` `typing` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ratings` CHANGE `ip` `ip` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_logins` CHANGE `ip` `ip` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	return processCommandList($commands,$_link);
}

function up_3322_3400($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_overlays` ADD `shadow` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',ADD `shadow_x` TINYINT NOT NULL DEFAULT '0',ADD `shadow_y` TINYINT NOT NULL DEFAULT '0',ADD `shadow_blur` TINYINT UNSIGNED NOT NULL DEFAULT '0',ADD `shadow_color` VARCHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',ADD `width` INT UNSIGNED NOT NULL DEFAULT '0',ADD `height` INT UNSIGNED NOT NULL DEFAULT '0',ADD `background` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',ADD `background_opacity` DECIMAL UNSIGNED NOT NULL DEFAULT '0',ADD `background_color` VARCHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."overlay_boxes` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `receiver_user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `receiver_browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `url` text COLLATE utf8_bin NOT NULL, `content` text COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `closed` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."overlay_boxes` ADD INDEX `receiver_browser_id` ( `receiver_browser_id` );");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."overlay_boxes` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."overlay_boxes_ibfk_1` FOREIGN KEY (`receiver_browser_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `subject` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."events` ADD `save_cookie` TINYINT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `groups_status` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operators` ADD `reposts` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` CHANGE `city` `city` INT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` CHANGE `region` `region` INT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` CHANGE `isp` `isp` INT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD `title` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD `status` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD `ltime` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `dtime`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD `ref_untouched` text COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1091,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_forwards` DROP `conversation`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_forwards` ADD `initiator_operator_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `created`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `receiver_original` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `receiver_group`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_forwards` ADD `invite` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD `priority` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '2' AFTER `chat_id`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` DROP PRIMARY KEY ,ADD PRIMARY KEY ( `id` , `receiver` );");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `repost` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ADD `canceled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `chat_info` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `website_push_auto`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `ticket_info` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `chat_info`;");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_internals` ADD INDEX `created`( `created` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_internals` ADD INDEX `receiver_user_id`( `receiver_user_id` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `id` ( `id` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `signature`( `signature` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `city` ( `city` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `region` ( `region` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `isp` ( `isp` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `system` ( `system` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `resolution` ( `resolution` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `entrance` ( `entrance` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitors` ADD INDEX `last_active` ( `last_active` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD INDEX `visitor_id` ( `visitor_id` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD INDEX `query` ( `query` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD INDEX `url` ( `url` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD INDEX `entrance` ( `entrance` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD INDEX `referrer` ( `referrer` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD INDEX `exit` ( `exit` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD INDEX `user_id` ( `user_id` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_data_titles` ADD INDEX `confirmed` ( `confirmed` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."resources` ADD INDEX `edited`  ( `edited` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs` ADD INDEX `aggregated`  ( `aggregated` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs` ADD INDEX `time`  ( `time` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs` ADD INDEX `mtime`  ( `mtime` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_pages_exit` ADD INDEX `url`  ( `url` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_pages_entrance` ADD INDEX `url` ( `url` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` ADD INDEX `time` ( `time` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` ADD INDEX `internal_id` ( `internal_id` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD INDEX `closed` ( `closed` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD INDEX `time` ( `time` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` ADD INDEX `time` ( `time` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ratings` ADD INDEX `time` ( `time` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."goals` ADD INDEX `ind` ( `ind` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_files` ADD INDEX `operator_id` ( `operator_id` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD INDEX `internal_id` ( `internal_id` );",true);
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD INDEX `group_id` ( `group_id` );",true);
	return processCommandList($commands,$_link);
}

function up_3313_3320($_prefix,$_link)
{
	$commands[] = array(1050,"RENAME TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` TO `".@mysql_real_escape_string($_prefix)."event_action_overlays`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."events` ADD `search_phrase` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	return processCommandList($commands,$_link);
}

function up_3311_3312($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `translation_iso` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `translation`;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `endtime` `endtime` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` CHANGE `closed` `closed` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';");
	return processCommandList($commands,$_link);
}

function up_3310_3311($_prefix,$_link)
{
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD `untouched` text COLLATE utf8_bin NOT NULL;");
	return processCommandList($commands,$_link);
}

function up_3300_3310($_prefix,$_link)
{
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_visitors` CHANGE `js` `js` INT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD `dtime` INT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `iso_country` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `iso_language`;");
	$commands[] = array(1025,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_goals` DROP FOREIGN KEY `visitor_goals_ibfk_2`;");
	return processCommandList($commands,$_link);
}

function up_3203_3300($_prefix,$_link)
{
	$commands[] = array(1051,"DROP TABLE `".@mysql_real_escape_string($_prefix)."chat_rooms`;");
	$commands[] = array(1051,"DROP TABLE `".@mysql_real_escape_string($_prefix)."data`;");
	$commands[] = array(1050,"RENAME TABLE `".@mysql_real_escape_string($_prefix)."chats` TO `".@mysql_real_escape_string($_prefix)."chat_archive`;");
	$commands[] = array(1050,"RENAME TABLE `".@mysql_real_escape_string($_prefix)."internal` TO `".@mysql_real_escape_string($_prefix)."operator_status`;");
	$commands[] = array(1050,"RENAME TABLE `".@mysql_real_escape_string($_prefix)."logins` TO `".@mysql_real_escape_string($_prefix)."operator_logins`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."alerts`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."chat_requests`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."chat_posts`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."events`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_actions`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_urls`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_senders`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."event_triggers`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."operator_status`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."tickets`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."chat_requests`;");
	$commands[] = array(1050,"TRUNCATE TABLE `".@mysql_real_escape_string($_prefix)."website_pushs`;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."alerts` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."events` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_actions` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_internals` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_senders` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."tickets` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_urls` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ENGINE = InnoDB;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` ENGINE = InnoDB;");
	$commands[] = array(1091,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` DROP `id`;");
	$commands[] = array(1091,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` DROP `id`;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` CHANGE `sender` `sender` VARCHAR( 65 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` CHANGE `receiver` `receiver` VARCHAR( 65 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `lang_iso` `lang_iso` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."profiles` CHANGE `languages` `languages` VARCHAR( 1024 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1050,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` CHANGE `ticket_id` `ticket_id` VARCHAR( 32 ) NOT NULL DEFAULT '';");
	$commands[] = array(0,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` ADD `time_confirmed` INT( 11 ) UNSIGNED NOT NULL;");
	$commands[] = array(0,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` CHANGE `time_confirmed` `confirmed` INT( 11 ) UNSIGNED NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `translation` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `text`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ADD `closed` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `declined`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `transcript_receiver` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `transcript_sent`;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD `customs` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;");
	$commands[] = array(1060,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."info` ADD `gtspan` INT UNSIGNED NOT NULL DEFAULT '0';");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."event_funnels` (`eid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `uid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `ind` smallint(5) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`eid`,`uid`),  KEY `uid` (`uid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."event_goals` (`event_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `goal_id` int(10) unsigned NOT NULL DEFAULT '0', UNIQUE KEY `prim` (`event_id`,`goal_id`), KEY `target_id` (`goal_id`),  KEY `event_id` (`event_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."goals` ( `id` int(10) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) COLLATE utf8_bin NOT NULL, `description` text COLLATE utf8_bin NOT NULL, `conversion` tinyint(1) unsigned NOT NULL DEFAULT '0', `ind` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`), UNIQUE KEY `title` (`title`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs` ( `year` smallint(4) unsigned NOT NULL DEFAULT '0',  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',  `time` int(10) unsigned NOT NULL DEFAULT '0',  `mtime` int(10) unsigned NOT NULL DEFAULT '0',`sessions` int(10) unsigned NOT NULL DEFAULT '0',`visitors_unique` int(10) unsigned NOT NULL DEFAULT '0', `conversions` int(10) unsigned NOT NULL DEFAULT '0',  `aggregated` tinyint(1) unsigned NOT NULL DEFAULT '0',  `chats_forwards` int(10) unsigned NOT NULL DEFAULT '0',  `chats_posts_internal` int(10) unsigned NOT NULL DEFAULT '0',  `chats_posts_external` int(10) unsigned NOT NULL DEFAULT '0',  `avg_time_site` double unsigned NOT NULL DEFAULT '0',  PRIMARY KEY (`year`,`month`,`day`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_availabilities` ( `year` smallint(5) unsigned NOT NULL DEFAULT '0',  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',  `hour` tinyint(2) unsigned NOT NULL DEFAULT '0',  `user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `status` tinyint(1) unsigned NOT NULL DEFAULT '0',  `seconds` int(4) unsigned NOT NULL DEFAULT '0',  PRIMARY KEY (`year`,`month`,`day`,`user_id`,`hour`,`status`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_browsers` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`browser` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`browser`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_chats` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`hour` tinyint(2) unsigned NOT NULL DEFAULT '0',`user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`amount` int(10) unsigned NOT NULL DEFAULT '0',`accepted` int(10) unsigned NOT NULL DEFAULT '0',`declined` int(10) unsigned NOT NULL DEFAULT '0',`avg_duration` double unsigned NOT NULL DEFAULT '0',`avg_waiting_time` double unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`user_id`,`hour`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_cities` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`city` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`city`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_countries` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`country` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`country`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_crawlers` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`crawler` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`crawler`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_domains` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`domain` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`domain`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_durations` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`duration` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`duration`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_goals` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`goal` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`goal`),KEY `target` (`goal`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_isps` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`isp` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`isp`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_languages` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`language` varchar(5) COLLATE utf8_bin NOT NULL,`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`language`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_pages` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`url` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`url`),KEY `url_id` (`url`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_pages_entrance` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`url` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`url`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_pages_exit` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`url` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`url`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_queries` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`query` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`query`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_referrers` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`referrer` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`referrer`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_regions` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`region` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`region`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_resolutions` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`resolution` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`resolution`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_search_engines` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`domain` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`domain`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_systems` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`system` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`system`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_visitors` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`hour` tinyint(3) unsigned NOT NULL DEFAULT '0',`visitors_unique` int(10) unsigned NOT NULL DEFAULT '0',`page_impressions` int(10) unsigned NOT NULL DEFAULT '0',`visitors_recurring` int(10) unsigned NOT NULL DEFAULT '0',`bounces` int(10) unsigned NOT NULL DEFAULT '0',`search_engine` int(10) unsigned NOT NULL DEFAULT '0',`from_referrer` int(10) unsigned NOT NULL DEFAULT '0',`browser_instances` int(10) unsigned NOT NULL DEFAULT '0',`js` int(10) unsigned NOT NULL DEFAULT '0',`on_chat_page` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`hour`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."stats_aggs_visits` (`year` smallint(5) unsigned NOT NULL DEFAULT '0',`month` tinyint(3) unsigned NOT NULL DEFAULT '0',`day` tinyint(3) unsigned NOT NULL DEFAULT '0',`visits` int(10) unsigned NOT NULL DEFAULT '0',`amount` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`year`,`month`,`day`,`visits`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitors` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`entrance` int(10) unsigned NOT NULL DEFAULT '0',`last_active` int(10) unsigned NOT NULL DEFAULT '0',`host` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '',`system` smallint(5) unsigned NOT NULL DEFAULT '0',`browser` smallint(5) unsigned NOT NULL DEFAULT '0',`visits` smallint(5) unsigned NOT NULL DEFAULT '0',`visit_id` varchar(7) COLLATE utf8_bin NOT NULL DEFAULT '',`visit_latest` tinyint(1) unsigned NOT NULL DEFAULT '1',`visit_last` int(10) unsigned NOT NULL DEFAULT '0',`resolution` smallint(5) unsigned NOT NULL DEFAULT '0',`language` varchar(5) COLLATE utf8_bin NOT NULL,`country` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',`city` smallint(5) unsigned NOT NULL DEFAULT '0',`region` smallint(5) unsigned NOT NULL DEFAULT '0',`isp` smallint(5) unsigned NOT NULL DEFAULT '0',`timezone` varchar(24) COLLATE utf8_bin NOT NULL DEFAULT '',`latitude` double NOT NULL DEFAULT '0',`longitude` double NOT NULL DEFAULT '0',`geo_result` int(10) unsigned NOT NULL DEFAULT '0',`js` tinyint(1) unsigned NOT NULL DEFAULT '0',`signature` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`,`entrance`),UNIQUE KEY `visit_id` (`visit_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`visit_id` varchar(7) COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0',`last_active` int(10) unsigned NOT NULL DEFAULT '0',`last_update` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',`is_chat` tinyint(1) unsigned NOT NULL DEFAULT '0',`query` int(10) unsigned NOT NULL DEFAULT '0',`fullname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`company` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`customs` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`url_entrance` int(10) unsigned NOT NULL DEFAULT '0',`url_exit` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),KEY `visit_id` (`visit_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` (`browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`entrance` int(10) unsigned NOT NULL DEFAULT '0',`referrer` int(10) unsigned NOT NULL DEFAULT '0',`url` int(10) unsigned NOT NULL DEFAULT '0',`params` text COLLATE utf8_bin NOT NULL,PRIMARY KEY (`entrance`,`browser_id`),KEY `browser_id` (`browser_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_area_codes` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`area_code` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `area_code` (`area_code`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_browsers` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`browser` varchar(255) COLLATE utf8_bin NOT NULL,`type` tinyint(1) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),UNIQUE KEY `browser` (`browser`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_cities` (`id` int(11) NOT NULL AUTO_INCREMENT,`city` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `city` (`city`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_crawlers` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`crawler` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `crawler` (`crawler`),UNIQUE KEY `crawler_2` (`crawler`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_domains` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`domain` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`external` tinyint(1) unsigned NOT NULL DEFAULT '1',`search` tinyint(1) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),UNIQUE KEY `domain` (`domain`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_isps` (`id` int(11) NOT NULL AUTO_INCREMENT,`isp` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `isp` (`isp`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_pages` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`domain` int(10) unsigned NOT NULL DEFAULT '0',`path` int(10) unsigned NOT NULL DEFAULT '0',`title` int(10) unsigned NOT NULL DEFAULT '0',`area_code` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),UNIQUE KEY `UNIQ` (`domain`,`path`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_paths` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`path` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `path` (`path`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_queries` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`query` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `query` (`query`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_regions` (`id` int(11) NOT NULL AUTO_INCREMENT,`region` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `region` (`region`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_resolutions` (`id` int(11) NOT NULL AUTO_INCREMENT,`resolution` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',PRIMARY KEY (`id`),UNIQUE KEY `resolution` (`resolution`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_systems` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`system` varchar(255) COLLATE utf8_bin NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `os` (`system`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_data_titles` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`title` varchar(255) COLLATE utf8_bin NOT NULL,`confirmed` int(10) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`id`),UNIQUE KEY `title` (`title`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_goals` (`visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',`goal_id` int(10) unsigned NOT NULL DEFAULT '0',`time` int(10) unsigned NOT NULL DEFAULT '0',`first_visit` tinyint(1) unsigned NOT NULL DEFAULT '0',PRIMARY KEY (`visitor_id`,`goal_id`),KEY `visitor_id` (`visitor_id`),KEY `target_id` (`goal_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."ticket_customs` (`ticket_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `custom_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `value` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`ticket_id`,`custom_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_chats` (`visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `visit_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `chat_id` int(11) unsigned NOT NULL DEFAULT '0', `fullname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `company` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `status` tinyint(1) unsigned NOT NULL DEFAULT '0', `typing` tinyint(1) unsigned NOT NULL DEFAULT '0', `waiting` tinyint(1) unsigned NOT NULL DEFAULT '0', `area_code` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `first_active` int(10) unsigned NOT NULL DEFAULT '0', `last_active` int(10) unsigned NOT NULL DEFAULT '0',`qpenalty` int(10) unsigned NOT NULL DEFAULT '0',`request_operator` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `request_group` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `question` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`customs` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`allocated` int(11) unsigned NOT NULL DEFAULT '0', `internal_active` tinyint(1) unsigned NOT NULL DEFAULT '0', `internal_closed` tinyint(1) unsigned NOT NULL DEFAULT '0', `internal_declined` tinyint(1) unsigned NOT NULL DEFAULT '0', `external_active` tinyint(1) unsigned NOT NULL DEFAULT '0', `external_close` tinyint(1) unsigned NOT NULL DEFAULT '0', `exit` int(11) unsigned NOT NULL DEFAULT '0',  PRIMARY KEY (`visitor_id`,`browser_id`,`visit_id`,`chat_id`),  KEY `chat_id` (`chat_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` (`chat_id` int(10) unsigned NOT NULL DEFAULT '0', `user_id` varchar(32) COLLATE utf8_bin NOT NULL,`declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`user_id`,`chat_id`), KEY `chat_id` (`chat_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."chat_files` ( `id` varchar(64) COLLATE utf8_bin NOT NULL, `created` int(10) unsigned NOT NULL DEFAULT '0',`file_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `file_mask` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `file_id` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',`chat_id` int(10) unsigned NOT NULL DEFAULT '0', `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `error` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `permission` tinyint(1) NOT NULL DEFAULT '-1', `download` tinyint(1) unsigned NOT NULL DEFAULT '0',`closed` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`,`created`), KEY `visitor_id` (`visitor_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."chat_forwards` ( `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `target_operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `target_group_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',  `chat_id` int(11) unsigned NOT NULL DEFAULT '0',  `conversation` mediumtext COLLATE utf8_bin NOT NULL,  `info_text` mediumtext COLLATE utf8_bin NOT NULL,  `processed` tinyint(1) unsigned NOT NULL DEFAULT '0',`received` tinyint(1) unsigned NOT NULL DEFAULT '0',  PRIMARY KEY (`id`),  KEY `chat_id` (`chat_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."operators` (`id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `login_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `first_active` int(10) unsigned NOT NULL DEFAULT '0', `last_active` int(10) unsigned NOT NULL DEFAULT '0', `password` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `status` tinyint(1) unsigned NOT NULL DEFAULT '0', `level` tinyint(1) unsigned NOT NULL DEFAULT '0', `ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '', `typing` tinyint(1) unsigned NOT NULL DEFAULT '0', `visitor_file_sizes` mediumtext COLLATE utf8_bin NOT NULL, `last_chat_allocation` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1050,"CREATE TABLE IF NOT EXISTS `".@mysql_real_escape_string($_prefix)."filters` ( `creator` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `editor` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(10) unsigned NOT NULL DEFAULT '0', `ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '', `expiredate` int(10) NOT NULL DEFAULT '0', `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `reason` text COLLATE utf8_bin NOT NULL, `name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '', `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '', `active` tinyint(1) unsigned NOT NULL DEFAULT '0', `exertion` tinyint(1) unsigned NOT NULL DEFAULT '0', `languages` text COLLATE utf8_bin NOT NULL, `activeipaddress` tinyint(3) unsigned NOT NULL DEFAULT '0', `activevisitorid` tinyint(3) unsigned NOT NULL DEFAULT '0', `activelanguage` tinyint(3) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."alerts` ADD INDEX `receiver_user_id` ( `receiver_user_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_actions` ADD INDEX `event_id` ( `eid` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ADD INDEX `action_id` ( `action_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` ADD INDEX `action_id` ( `action_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ADD INDEX `action_id` ( `action_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` ADD INDEX `receiver_user_id` ( `receiver_user_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` ADD INDEX `ticket_id` ( `ticket_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD INDEX `ticket_id` ( `ticket_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_archive` ADD INDEX `chat_id` ( `chat_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ADD INDEX `receiver_browser_id` ( `receiver_browser_id` );");
	$commands[] = array(1061,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` ADD INDEX `receiver_browser_id` ( `receiver_browser_id` );");
	$commands[] = array(1068,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."operator_status` ADD PRIMARY KEY ( `time` , `internal_id` , `status` );");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."alerts` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."alerts_ibfk_1` FOREIGN KEY ( `receiver_user_id` ) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_funnels` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_funnels_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `".@mysql_real_escape_string($_prefix)."event_urls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_funnels` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_funnels_ibfk_2` FOREIGN KEY (`eid`) REFERENCES `".@mysql_real_escape_string($_prefix)."events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_actions` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_actions_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `".@mysql_real_escape_string($_prefix)."events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_action_invitations_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_action_receivers_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_action_website_pushs_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_triggers_ibfk_2` FOREIGN KEY (`receiver_user_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_goals` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_goals_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_goals` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_goals_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."goals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."event_urls` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."event_urls_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `".@mysql_real_escape_string($_prefix)."events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_goals` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."stats_aggs_goals_ibfk_1` FOREIGN KEY (`goal`) REFERENCES `".@mysql_real_escape_string($_prefix)."goals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."stats_aggs_pages` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."stats_aggs_pages_ibfk_1` FOREIGN KEY (`url`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_data_pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."ticket_editors_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."ticket_customs` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."ticket_customs_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browsers` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_browsers_ibfk_1` FOREIGN KEY (`visit_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`visit_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_browser_urls` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_browser_urls_ibfk_1` FOREIGN KEY (`browser_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_goals` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_goals_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chats` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_chats_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."visitor_chat_operators` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."visitor_chat_operators_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_chats` (`chat_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_files` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."chat_files_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_forwards` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."chat_forwards_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_chats` (`chat_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."chat_requests_ibfk_1` FOREIGN KEY (`receiver_browser_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	$commands[] = array(1005,"ALTER TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` ADD CONSTRAINT `".@mysql_real_escape_string($_prefix)."website_pushs_ibfk_1` FOREIGN KEY (`receiver_browser_id`) REFERENCES `".@mysql_real_escape_string($_prefix)."visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	
	return processCommandList($commands,$_link);
}

function up_3200_3201($_prefix,$_link)
{
	$commands = Array();
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `question` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';";
	$allowedecs = Array(1060);
	foreach($commands as $key => $command)
	{
		$result = @mysql_query($command,$_link);
		if(!$result && mysql_errno() != $allowedecs[$key])
			return mysql_errno() . ": " . mysql_error() . "\r\n\r\nMySQL Query: " . $commands[$key];
	}
	return true;
}

function up_3186_3200($_prefix,$_link)
{
	$commands = Array();
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."alerts` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `invitation_auto` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `invitation`";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `invitation` `invitation_manual` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `website_push` `website_push_manual` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `website_push_auto` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `website_push_manual`";
	$commands[] = "UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `invitation_auto`=`invitation_manual`";
	$commands[] = "UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `website_push_auto`=`website_push_manual`";
 	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."rooms` TO `".@mysql_real_escape_string($_prefix)."chat_rooms`;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_rooms` ADD `creator` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT ''";
 	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."posts`  TO `".@mysql_real_escape_string($_prefix)."chat_posts`;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `chat_id` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `id` ";
	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."res`  TO `".@mysql_real_escape_string($_prefix)."resources`;";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `group_id` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `internal_id`";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."logins` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`time` int(11) unsigned NOT NULL DEFAULT '0', `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_system_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `sender_group_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."events` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`name` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0', `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(10) unsigned NOT NULL DEFAULT '0', `editor` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `pages_visited` int(10) unsigned NOT NULL DEFAULT '0', `time_on_site` int(10) unsigned NOT NULL DEFAULT '0', `max_trigger_amount` int(10) unsigned NOT NULL DEFAULT '0', `trigger_again_after` int(10) unsigned NOT NULL DEFAULT '0', `not_declined` tinyint(1) unsigned NOT NULL DEFAULT '0', `not_accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `not_in_chat` tinyint(1) unsigned NOT NULL DEFAULT '0', `priority` int(10) unsigned NOT NULL DEFAULT '0', `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_actions` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `type` tinyint(2) unsigned NOT NULL DEFAULT '0', `value` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_internals` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0',`trigger_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `position` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `speed` tinyint(1) NOT NULL DEFAULT '1', `slide` tinyint(1) NOT NULL DEFAULT '1', `margin_left` int(11) NOT NULL DEFAULT '0', `margin_top` int(11) NOT NULL DEFAULT '0', `margin_right` int(11) NOT NULL DEFAULT '0', `margin_bottom` int(11) NOT NULL DEFAULT '0', `style` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `close_on_click` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_senders` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `pid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `group_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `priority` tinyint(2) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `target_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `ask` tinyint(1) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `time` int(10) unsigned NOT NULL DEFAULT '0', `triggered` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_urls` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `referrer` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `time_on_site` int(10) unsigned NOT NULL DEFAULT '0', `blacklist` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_system_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `ask` tinyint(1) unsigned NOT NULL DEFAULT '0', `target_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `editable` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `area_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `group_id`;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."profiles` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(11) NOT NULL DEFAULT '0', `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `fax` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `street` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `zip` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `department` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `country` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `gender` tinyint(1) NOT NULL DEFAULT '0', `languages` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `comments` longtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `public` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."profile_pictures` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `internal_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `time` int(11) NOT NULL DEFAULT '0', `webcam` tinyint(1) NOT NULL DEFAULT '0', `data` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$allowedecs = Array(1050,1054,1054,1054,1060,1060,1060,1050,1060,1050,1060,1050,1060,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1060,1060,1050,1050);
	foreach($commands as $key => $command)
	{
		$result = @mysql_query($command,$_link);
		if(!$result && mysql_errno() != $allowedecs[$key])
			return mysql_errno() . ": " . mysql_error() . "\r\n\r\nMySQL Query: " . $commands[$key];
	}
	return true;
}
?>