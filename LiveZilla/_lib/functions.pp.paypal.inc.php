<?php

function PayProvValidatePayment($_price)
{
	global $PAYMENTERROR;
	$success = false;
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) 
	{
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}
	$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	
	$fp = @fsockopen("ssl://www.paypal.com", 443, $errno, $errstr, 30);

	if (!$fp) 
	{
		$PAYMENTERROR = "There's a technical problem when validating this payment through ssl://www.paypal.com:" . "\r\n\r\nHTTP ERROR " . $errno . ":\r\n" . $errstr . "\r\n";
		return false;
	} 
	else
	{
		fputs ($fp, $header . $req);
		while (!feof($fp)) 
		{
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0) 
			{
				$success = true;
			}
  		}
		fclose($fp);
	}
	
	if(!empty($_POST["payment_status"]) && strtolower($_POST["payment_status"])!="completed")
	{
		$PAYMENTERROR = "Payment pending or refunded/aborted/rejected.\r\n";
	}
	if(!(!empty($_POST["mc_gross"]) && $_price == abs($_POST["mc_gross"])))
	{
		$PAYMENTERROR = "Invalid amount (possible fraud attempt).\r\nInvoice amount: " . $_price . "\r\nPayment amount: ".$_POST["mc_gross"]."\r\n";
	}
	return $success;
}

function PayProvGetPaymentId()
{
	return $_POST["txn_id"];
}

function PayProvGetPayerId()
{
	return $_POST["payer_email"];
}

function PayProvGetPaymentDetails()
{
	global $PAYMENTERROR;
	$details = (!empty($PAYMENTERROR)) ? ($PAYMENTERROR . "\r\n"): "";
	foreach($_POST as $key => $value)
		$details .= $key . ": " . $value . "\r\n";
	return $details;
}

?>