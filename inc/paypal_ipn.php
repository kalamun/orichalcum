<?php 
/*
PAYPAL IPN confirm payment
*/

require_once('./tplshortcuts.lib.php');
kInitBettino('../');

// Read the post from PayPal and add 'cmd' 
$req='cmd=_notify-validate'; 
if(function_exists('get_magic_quotes_gpc')) $get_magic_quotes_exists=true; 

foreach($_POST as $k=>$v)
{
	if($get_magic_quotes_exists==true&&get_magic_quotes_gpc()==1) $v=stripslashes($v); 
	$v=urlencode($v); 
	$req.="&".$k."=".$v; 
}

// Post back to PayPal to validate 
$header = ""; 
$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n"; 
$header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
$header .= "Host: www.paypal.com\r\n"; 
$header .= "Content-Length: ".strlen($req)."\r\n\r\n"; 
$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30); 

if(!$fp)
{
	// HTTP ERROR
	// Mark the order as valid and send a notification to the admin
	// ! this should be done better, but for now I prefer do not miss any payments due to server errors.

	mark_as_payed();

} else {
	// NO HTTP ERROR
	// Check if the order is valid

	fputs($fp, $header.$req);
	while(!feof($fp))
	{ 
		$res = fgets($fp, 1024);

		if(strcmp($res, "VERIFIED") == 0)
		{
			mark_as_payed();

		} elseif(strcmp($res,"INVALID") == 0) {
			trigger_error("Invalid payment");
		}
	}
}


function mark_as_payed()
{
	// Identify order id
	$order = kGetShopOrderByNumber($_POST['custom']);

	if(empty($order['idord']))
	{
		trigger_error("Invalid order ID");
		return false;
	}

	// Check the payment_status is Completed 
	if($_POST['payment_status'] == "Completed")
	{
		// Check that txn_id has not been previously processed 
		if($GLOBALS['__shop']->tnxIdExists($_POST['txn_id']))
		{
			trigger_error("tnxId already exists... double payment or attempting to fraud?");
			return false;

		} else {
			// Check that receiver_email is your Primary PayPal email
			if(!empty($_POST['receiver_email']) && strtolower($_POST['receiver_email']) != strtolower(kGetVar('shop-paypal',1)) && strtolower($_POST['receiver_id']) != strtolower(kGetVar('shop-paypal',1)))
			{
				trigger_error("receiver id doesn't correspond to the shop administrator");
				return false;
			}
			
			// Check that payment_currency is correct 
			if(strtoupper($_POST['mc_currency']) != kGetShopCurrency())
			{
				trigger_error("The payment currency doesn't correspond to the order's one");
				return false;
			}
			
			// Process payment 
			if(isset($_POST['mc_gross_1']) && !isset($_POST['mc_gross'])) $_POST['mc_gross'] = $_POST['mc_gross_1'];
				
			// the addPayment function also sends notifications to the customer and to the admin if the total order amount will be covered by the payment
			$GLOBALS['__shop']->addPayment($order['uid'], $_POST['mc_gross'], $order['idspay'], "txn_id='".$_POST['txn_id']."'");
		}

	} else {
		trigger_error("Unverified payment");
		return false;
	}
}

fclose ($fp); 
