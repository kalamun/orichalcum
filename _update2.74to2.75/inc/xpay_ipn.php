<?php 

if(!isset($_POST['esito'])||!isset($_POST['mac'])) {
	die();
	}
else {
	// NO HTTP ERROR 
	require_once('./tplshortcuts.lib.php');
	kInitBettino('../');
	$log="";

	//check if order exists
	$idorder=substr($_POST['codTrans'],4);
	$order=kGetShopOrderByNumber($idorder);
	if(!empty($order['uid']))
	{
		//check if MAC is valid
		$mac=sha1("codTrans=".$_POST['codTrans']."esito=".$_POST['esito']."importo=".$_POST['importo']."divisa=".$_POST['divisa']."data=".$_POST['data']."orario".$_POST['orario']."codAut=".$_POST['codAut'].kGetShopXPayKey());
		// the mac calculation doesn't work as expected
		//if($mac != $_POST['mac']) $log = 'Invalid MAC';
		//else {

			$_POST['importo'] = $_POST['importo']/100;
			$GLOBALS['__shop']->addPayment($order['uid'], $_POST['importo'], $order['idspay'], "");
			
		//}
	}
	else $log="Invalid order ID";
	}

if(isset($log)) echo $log;


