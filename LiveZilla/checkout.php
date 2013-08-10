<?php
/****************************************************************************************
* LiveZilla checkout.php
* 
* Copyright 2013 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

define("IN_LIVEZILLA",true);

if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");
	
require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.protocol.inc.php");
defineURL("checkout.php");
@set_error_handler("handleError");

setDataProvider();
if(!empty($_POST["form_visitor_id"]) && !empty($_POST["form_total_price"]) && !empty($_POST["form_currency"]) && is_numeric(($_POST["form_total_price"])) && strlen(($_POST["form_currency"]))<=3)
{
	languageSelect();
	$ticket = new CommercialChatVoucher($_POST["form_voucher_type"],strtoupper(getId(16)));
	$ticket->VisitorId = $_POST["form_visitor_id"];
	//$ticket->BusinessType = $_POST["form_business_type"];
	$ticket->Company = $_POST["form_company"];
	$ticket->Email = $_POST["form_email"];
	//$ticket->TaxID = $_POST["form_taxid"];
	$ticket->Firstname = $_POST["form_firstname"];
	$ticket->Lastname = $_POST["form_lastname"];
	$ticket->Address1 = $_POST["form_address_1"];
	$ticket->Address2 = $_POST["form_address_2"];
	$ticket->ZIP = $_POST["form_zip"];
	$ticket->State = $_POST["form_state"];
	$ticket->Country = $_POST["form_country"];
	$ticket->Phone = $_POST["form_phone"];
	$ticket->City = $_POST["form_city"];
	$ticket->Extends = $_POST["form_extends"];
	
	if(!empty($ticket->Extends))
	{
		$eticket = new CommercialChatVoucher("",$ticket->Extends);
		if($eticket->Load())
		{
			if(!empty($eticket->Extends))
				$ticket->Extends = $eticket->Extends;
		}
		else
			$ticket->Extends = "";
	}
	
	if(!empty($CONFIG["db"]["cct"][$_POST["form_voucher_type"]]))
	{
		$ticket->Language = $DEFAULT_BROWSER_LANGUAGE;
		$ticket->ChatSessionsMax = $CONFIG["db"]["cct"][$_POST["form_voucher_type"]]->ChatSessionsMax;
		$ticket->ChatTimeMax = $CONFIG["db"]["cct"][$_POST["form_voucher_type"]]->ChatTimeMax * 60;
		$ticket->Price = $CONFIG["db"]["cct"][$_POST["form_voucher_type"]]->Price;
		if(!empty($CONFIG["gl_ccsv"]))
			$ticket->VAT = round(($CONFIG["db"]["cct"][$_POST["form_voucher_type"]]->Price*$CONFIG["gl_ccva"])/100,2);
		$ticket->CurrencyISOThreeLetter = $CONFIG["db"]["cct"][$_POST["form_voucher_type"]]->CurrencyISOThreeLetter;
		$ticket->Save();
		$ticket->SendCreatedEmail();
	}
	$html = getFile(PATH_TEMPLATES . "payment/paypal.tpl");
	$html = str_replace("<!--account-->",$CONFIG["db"]["ccpp"]["PayPal"]->Account,$html);
	$html = str_replace("<!--price-->",($_POST["form_total_price"]-$_POST["form_vat"]),$html);
	$html = str_replace("<!--tax-->",($_POST["form_vat"]),$html);
	$html = str_replace("<!--currency-->",($_POST["form_currency"]),$html);
	$html = str_replace("<!--user_id-->",($_POST["form_visitor_id"]),$html);
	$html = str_replace("<!--order_id-->",$ticket->Id,$html);
	$html = str_replace("<!--voucher_id-->",base64UrlEncode($ticket->Id),$html);
	$html = str_replace("<!--server-->",LIVEZILLA_URL,$html);
	
	if(!empty($_POST["form_extends"]) && !empty($_POST["form_group"]))
		$html = str_replace("<!--co-->","&amp;co=" . base64UrlEncode($_POST["form_extends"]) . "&amp;intgroup=" . base64UrlEncode($_POST["form_group"]),$html);
	else if(!empty($_POST["form_group"]))
		$html = str_replace("<!--co-->","&amp;intgroup=" . base64UrlEncode($_POST["form_group"]),$html);
	else
		$html = str_replace("<!--co-->","",$html);
		
	exit($html);
}
else if(!empty($_GET["confirm"]) && $_GET["confirm"]=="1" && !empty($_GET["vc"]) && strlen(base64UrlDecode($_GET["vc"]))==16)
{
	require(LIVEZILLA_PATH . "_lib/functions.pp.paypal.inc.php");
	$voucher = new CommercialChatVoucher("",base64UrlDecode($_GET["vc"]));
	if($voucher->Load())
	{
		if(PayProvValidatePayment($voucher->Price))
		{
			languageSelect($voucher->Language);
			$voucher->SetPaymentDetails(PayProvGetPaymentId(),PayProvGetPayerId(),PayProvGetPaymentDetails());
			if(empty($PAYMENTERROR))
				$voucher->SetVoucherParams(!empty($voucher->Voided),true,false,false,false,true,base64UrlDecode($_GET[GET_EXTERN_GROUP]));
			else
				$voucher->SetVoucherParams(!empty($voucher->Voided),false,false,false,false);
		}
	}
}
unloadDataProvider();
?>
