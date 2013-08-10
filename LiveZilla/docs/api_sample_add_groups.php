<?php

// IMPORTANT: this file is disabled for security reasons by default. 
// ********** Please remove the exit statement for testing. ********
exit("IMPORTANT: this file is disabled for security reasons by default.");

define("LIVEZILLA_PATH","./../");
require("./../api.php");
$API = new LiveZillaAPI();

// ********** ********** ********** **********
// EXAMPLE: How to add a group dynamically
// ********** ********** ********** **********

// Group ID (required; allowed chars=a-z,A-Z,0-9)
$groupid = "mygroupid123";

// Email (required)
$email = "email@ofmygroup.domain";

// Group titles (required; the title for the default language - usually EN - is required, additional titles are optional)
$titles = array();
$titles["EN"] = base64_encode("The description of a group");
// $titles["DE"] = base64_encode("Die Beschreibung einer Gruppe");
// $titles["PT"] = base64_encode("A descriÃ§Ã£o de um grupo");

// Internal actions allowed (required; members of this groups can see and chat with members of other groups if true)
$internalactions = false;

// Visitor filters (optional; pass null or empty array for no filters)
$visitorfilters = array();
// $visitorfilters[base64_encode("ifthisispartoftheurlthevisitorwillbehidden")] = "Blacklist";
// $visitorfilters[base64_encode("ifthisisnotpartoftheurlthevisitorwillbehidden")] = "Whitelist";

// Opening Hours (optional; pass null or empty array for no opening hours)
$openinghours = array();
// $openinghours[] = array(1,28800,61200); // opened on mondays from 8 am to 5 pm
// $openinghours[] = array(7,28800,61200); // opened also on sundays from 8 am to 5 pm

// Chat Functions (optional; [0]=smileys,[1]=switch sounds,[2]=print,[3]=ratings,[4]=favourites,[5]=file transfer; default=111101)
$functions = "111101";

// Create the group through API
$API->CreateGroup($groupid,$titles,$internalactions,$email,$visitorfilters,$openinghours,$functions);

// Optional: Add predefined message sets
$API->AddPredefinedMessageSet('',$groupid, 'EN', true, 'Hello, my name is %operator_name%. Do you need help? Start Live-Chat now to get assistance.', 'Hello, my name is %operator_name%. Do you need help? Start Live-Chat now to get assistance.','Hello %external_name%, my name is %operator_name%, how may I help you?', 'Website Operator %operator_name% would like to redirect you to this URL:

%target_url%', 'Website Operator %operator_name% would like to redirect you to this URL:

%target_url%',"Chat Transcript\r\n%website_name% / %group_description%\r\n\r\nDate: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nChat reference number: %chat_id%\r\n-------------------------------------------------------------\r\n%mailtext%","Thank you, we have received your message!\r\nWe will get in touch with you as soon as possible.\r\n-------------------------------------------------------------\r\nDate: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nGroup: %group_description%\r\n-------------------------------------------------------------\r\n%mailtext%");
$API->AddPredefinedMessageSet('',$groupid, 'DE', false, utf8_encode("Guten Tag, meine Name ist %operator_name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat."), utf8_encode("Guten Tag, meine Name ist %operator_name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat."),"Guten Tag %external_name%, mein Name ist %operator_name% wie kann ich Ihnen helfen?", utf8_encode("Ein Betreuer dieser Webseite (%operator_name%) möchte Sie auf einen anderen Bereich weiterleiten:

%target_url%"),utf8_encode("Ein Betreuer dieser Webseite (%operator_name%) möchte Sie auf einen anderen Bereich weiterleiten:

%target_url%"),"Mitschrift Ihres Chats\r\n%website_name% / %group_description%\r\n\r\nDatum: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nChat Referenz-Nummer: %chat_id%\r\n-------------------------------------------------------------\r\n%mailtext%","Vielen Dank, wir haben Ihre Nachricht erhalten und werden uns umgehend mit Ihnen in Verbindung setzen.\r\n-------------------------------------------------------------\r\nDatum: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nAbteilung: %group_description%\r\n-------------------------------------------------------------\r\n%mailtext%");

// Use this command to delete a group
//$API->DeleteGroup($groupid);

?>


