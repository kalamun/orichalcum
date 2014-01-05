<?php

// check input variables
if(!isset($_GET['statoattuale'])) die();
if(!isset($_GET['ORDERNUMBER'])) die();
if(!isset($_GET['MERCHANTNUMBER'])) die();
if(!isset($_GET['mac'])) die();

// NO HTTP ERRORs
require_once('./tplshortcuts.lib.php');
kInitBettino('../');


// check MAC
$mac="";
foreach($_GET as $k=>$v) {
	if($k=='mac') break;
	$mac.=$k.'='.$v.'&';
	}
$mac.=kGetShopPagOnlineKEY();

$mac=md5($mac);
$MACtemp="";
for($i=0;$i<strlen($mac);$i=$i+2) {
	$MACtemp.=chr(hexdec(substr($mac,$i,2)));
	}
$mac=$MACtemp;
$mac=base64_encode($mac);

if($mac!=$_GET['mac']) {
	die();
	}


// check if order exists
$order=kGetShopOrderByNumber(trim($_GET['ORDERNUMBER']));
if(!isset($order['uid'])||$order['uid']=="") {
	die();
	}


// payment accepted
if($_GET['statoattuale']=="OK"||$_GET['statoattuale']=="IC"||$_GET['statoattuale']=="CO") {
	$GLOBALS['__shop']->addPayment($order['uid'],$order['totalprice'],$order['idspay'],"txn_id='".$_GET['numeroOrdine']."'");
	}

// payment refused
else {
	}


?>

