<?php 

$email=$_GET['ipn_email']; 
$header=""; 
$emailtext=""; 

// Read the post from PayPal and add 'cmd' 
$req='cmd=_notify-validate'; 
if(function_exists('get_magic_quotes_gpc')) $get_magic_quotes_exists=true; 

foreach($_POST as $k=>$v) {
	if($get_magic_quotes_exists==true&&get_magic_quotes_gpc()==1) $v=stripslashes($v); 
	$v=urlencode($v); 
	$req.="&".$k."=".$v; 
	}

// Post back to PayPal to validate 
$header.="POST /cgi-bin/webscr HTTP/1.1\r\n"; 
$header.="Content-Type: application/x-www-form-urlencoded\r\n"; 
$header.="Host: www.paypal.com\r\n"; 
$header.="Content-Length: ".strlen($req)."\r\n\r\n"; 
$fp=fsockopen('ssl://www.paypal.com',443,$errno,$errstr,30); 
 

if(!$fp) {
	// HTTP ERROR 
	// TO DO
	}
else { 
	// NO HTTP ERROR 
	require_once('./tplshortcuts.lib.php');
	kInitBettino('../');
	$log="";

	fputs($fp,$header.$req); 
	while(!feof($fp)) { 
		$res=fgets($fp,1024); 
		if(strcmp($res,"VERIFIED")==0) { 
			// Check the payment_status is Completed 
			if($_POST['payment_status']=="Completed") {
				// Check that txn_id has not been previously processed 
				if($__shop->tnxIdExists($_POST['txn_id'])) $log="tnxId exists... double payment or attempting to fraud?";
				else {
					// Check that receiver_email is your Primary PayPal email
					//receiver_id: 9BQV5YMAKZRCJ
					// Check that payment_amount/payment_currency are correct 
					//mc_currency: USD
					// Process payment 
					// Identify order id
					$order=kGetShopOrderByNumber($_POST['custom']);
					if($order['idord']!="") {
						if(isset($_POST['mc_gross_1'])&&!isset($_POST['mc_gross'])) $_POST['mc_gross']=$_POST['mc_gross_1'];
						$GLOBALS['__shop']->addPayment($order['uid'],$_POST['mc_gross'],$order['idspay'],"txn_id='".$_POST['txn_id']."'");
						}
					// If 'VERIFIED', send an email of IPN variables and values to the 
					// specified email address
					}
				}
			else $log="Payment not verified";
			}
		if(strcmp($res,"INVALID")==0||$log!="") { 
			//ERRORE
			if($log=="") $log="Invalid payment";
			}	 
		} 
	}
fclose ($fp); 



?>
