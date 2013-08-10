<?php

// IMPORTANT: this file is disabled for security reasons by default. 
// ********** Please remove the exit statement for testing. ********
exit("IMPORTANT: this file is disabled for security reasons by default.");

define("LIVEZILLA_PATH","./../");
require("./../api.php");
$API = new LiveZillaAPI();

// ********** ********** ********** **********
// EXAMPLE: How to add an operator dynamically
// ********** ********** ********** **********

// Operator login ID (required; allowed chars=a-z,A-Z,0-9)
$operatorloginid = "myoperatorid123";

// Email (required)
$email = "email@ofmyoperator.domain";

// Full name (required)
$fullname = "John Doe";

// Operator permission set (required; please refer to documentation for further details)
$permissions = "11100021000001101100";

// Operator webspace (required; webspace available for file uploads;)
$webspace = 10; //MB

// Operator password (required; please pass a MD5-encoded password)
$password = md5("mypassword");

// Administrator permissions (required)
$administrator = false;

// Groups (required)
$groups = array();
$groups[] = base64_encode("mygroupid123");

// Administrator permissions (required)
$language = "EN";

// Create the operator through API
$operator = $API->CreateOperator($operatorloginid, $fullname, $email, $permissions, $webspace, $password, $administrator, $groups, $language);

// Optional: Add predefined message sets
$API->AddPredefinedMessageSet($operator->SystemId, '', 'EN', true, 'Hello, my name is %operator_name%. Do you need help? Start Live-Chat now to get assistance.', 'Hello, my name is %operator_name%. Do you need help? Start Live-Chat now to get assistance.','Hello %external_name%, my name is %operator_name%, how may I help you?', 'Website Operator %operator_name% would like to redirect you to this URL:

%target_url%', 'Website Operator %operator_name% would like to redirect you to this URL:

%target_url%',"Chat Transcript\r\n%website_name% / %group_description%\r\n\r\nDate: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nChat reference number: %chat_id%\r\n-------------------------------------------------------------\r\n%mailtext%","Thank you, we have received your message!\r\nWe will get in touch with you as soon as possible.\r\n-------------------------------------------------------------\r\nDate: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nGroup: %group_description%\r\n-------------------------------------------------------------\r\n%mailtext%");
$API->AddPredefinedMessageSet($operator->SystemId, '', 'DE', false, utf8_encode("Guten Tag, meine Name ist %operator_name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat."), utf8_encode("Guten Tag, meine Name ist %operator_name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat."),"Guten Tag %external_name%, mein Name ist %operator_name% wie kann ich Ihnen helfen?", utf8_encode("Ein Betreuer dieser Webseite (%operator_name%) möchte Sie auf einen anderen Bereich weiterleiten:

%target_url%"),utf8_encode("Ein Betreuer dieser Webseite (%operator_name%) möchte Sie auf einen anderen Bereich weiterleiten:

%target_url%"),"Mitschrift Ihres Chats\r\n%website_name% / %group_description%\r\n\r\nDatum: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nChat Referenz-Nummer: %chat_id%\r\n-------------------------------------------------------------\r\n%mailtext%","Vielen Dank, wir haben Ihre Nachricht erhalten und werden uns umgehend mit Ihnen in Verbindung setzen.\r\n-------------------------------------------------------------\r\nDatum: %localdate%\r\n-------------------------------------------------------------\r\n%details%\r\nAbteilung: %group_description%\r\n-------------------------------------------------------------\r\n%mailtext%");

// Use this command to delete an operator
//$API->DeleteOperator($operatorloginid);

?>


