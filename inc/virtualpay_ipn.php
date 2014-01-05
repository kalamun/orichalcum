<?php 

/*
?TRANSACTION_ID=D3A6BB9FD0224E5DB4B8D0ABAD90A8E9&MERCHANT_ID=NCHTEST&ORDER_ID=LINI0BFC71FE&COD_AUT=TESTOK&IMPORTO=0,01&DIVISA=EUR&MAC=343749B415731E0C984DE077901497B0


A tutti gli URL sopra descritti il server E-Gipsy aggiunge i dati necessari ad
identificare la transazione di pagamento:
• TRANSACTION_ID identificativo univoco della transazione
• MERCHANT_ID identificativo del Merchant.
• ORDER_ID identificativo dell’ordine.
• COD_AUT codice di autorizzazione restituito dall’ente autorizzante.
• IMPORTO importo dell’ordine.
• DIVISA divisa.
• MAC codice di controllo dell’integrità (descritto successivamente).
*/

if(!isset($_GET['TRANSACTION_ID'])||!isset($_GET['MERCHANT_ID'])||!isset($_GET['ORDER_ID'])||!isset($_GET['COD_AUT'])||!isset($_GET['IMPORTO'])||!isset($_GET['DIVISA'])||!isset($_GET['MAC'])) {
	die();
	}
else { 
	// NO HTTP ERROR 
	require_once('./tplshortcuts.lib.php');
	kInitBettino('../');
	$log="";

	//check if order exists
	$idorder=substr($_GET['ORDER_ID'],4);
	$order=kGetShopOrderByNumber($idorder);
	if(isset($order['uid'])&&$order['uid']!="") {
		//check if MAC is valid
		//$mac=strtoupper(md5(kGetShopVirtualPayBusinessId().$customerId.kGetShopOrderNumber()."0,01".kGetShopCurrency().kGetShopVirtualPayABI().$items.kGetShopVirtualPayKEY()));

		//search for duplicate transitions
		if($__shop->tnxIdExists($_GET['TRANSACTION_ID'])) $log="Duplicate transition";
		else {
			$_GET['IMPORTO']=str_replace(",",".",$_GET['IMPORTO']);
			$GLOBALS['__shop']->addPayment($order['uid'],$_GET['IMPORTO'],$order['idspay'],"txn_id='".$_GET['TRANSACTION_ID']."'");
			}
		}
	else $log="Invalid order ID";
	}

if(isset($log)) echo $log;


?>
