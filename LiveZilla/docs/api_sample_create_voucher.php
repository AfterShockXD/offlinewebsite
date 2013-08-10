<?php

// ********** Please remove the exit statement below ********
exit("IMPORTANT: this file is disabled for security reasons.");

define("LIVEZILLA_PATH","./../");
require("./../api.php");
$API = new LiveZillaAPI();

// ********** ********** ********** ********** **********
// EXAMPLE: How to add an chat voucher dynamically
// ********** ********** ********** ********** **********

// creating the voucher id which is used to start a chat
$voucher_id = substr(strtoupper(md5(uniqid())),0,16);

$billing_types = $API->GetChatBillingTypes();

if(empty($billing_types))
	exit("There are no chat billing types existing. Please create a chat billing type under LiveZilla Server Admin -> Server Configuration -> Commercial Chats / Chat Vouchers");

// in this sample, we assume that the buyer has ordered a chat voucher based on the first billing type registered in the system
$billing_type_id = key($billing_types);

$voucher = new CommercialChatVoucher($billing_type_id,$voucher_id);
$voucher->Company = "The company of the buyer";
$voucher->Email = "The email of the buyer";
$voucher->Firstname = "The first name of the buyer";
$voucher->Lastname = "The last name of the buyer";
$voucher->Address1 = "The address of the buyer";
$voucher->Address2 = "The address of the buyer";
$voucher->ZIP = "The ZIP address of the buyer";
$voucher->State = "The state of the buyer";
$voucher->Country = "US"; // ISO two letter country code
$voucher->Phone = "The phone number of the buyer";
$voucher->City = "The city of the buyer";
$voucher->Language = "EN"; // ISO two letter language code

// apply billing type criterias
$voucher->ChatSessionsMax = $billing_types[$billing_type_id]->ChatSessionsMax;
$voucher->ChatTimeMax = $billing_types[$billing_type_id]->ChatTimeMax * 60;
// please note that the expiration date will be generated automatically when the chat voucher is being used for the first time - you don't need to set this up here

$voucher->Price = 9.99; // price from online shop
$voucher->VAT = 1.89; // tax included in price
$voucher->CurrencyISOThreeLetter = "USD"; // ISO three letter currency code

// register voucher
$voucher->Save();

// mark voucher as active and paid
$is_voided = false;
$is_paid = true;
$voucher->SetVoucherParams($is_voided,$is_paid);

//[Optional] Set payment details
//$voucher->SetPaymentDetails($_transactionId,$_payerId,$_paymentDetails);

echo "Congratulations!<br><br>A new voucher has been registered. You can start a chat using this voucher code id: <b>" . $voucher_id . "</b>";

?>


