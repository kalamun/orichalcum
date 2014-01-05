<?
$items="";
$cart=kGetShopCart();
foreach($cart['items'] as $item) {
	$id=intval($item['idsitem']);
	$qty=intval($item['qty']);
	$name=str_replace("^","",$item['titolo']);
	if(strlen($name)>120) $name=substr($name,0,120);
	$price=number_format($item['prezzo']*$item['qty'],2,',','');
	$items=$id.'^'.$name.'^'.$qty.'^'.$price.'^'.kGetShopCurrency().';';
	}

$customerId=kGetSiteName();
$customerId=preg_replace("/\W*/","",strtoupper($customerId));
$customerId=substr($customerId,0,2).substr($customerId,-2);

$mac=strtoupper(md5(kGetShopVirtualPayBusinessId().$customerId.kGetShopOrderNumber().number_format(kGetShopOrderTotalAmount(),2,",","").kGetShopCurrency().kGetShopVirtualPayABI().$items.kGetShopVirtualPayKEY()));

?>
<form action="https://www.payment.fccrt.it/CheckOutEGIPSy.asp" method="post" id="PayPalForm">
	<div style="display:none;">
	<input type="hidden" name="MERCHANT_ID" value="<?= kGetShopVirtualPayBusinessId(); ?>">
	<input type="hidden" name="ORDER_ID" value="<?= $customerId.kGetShopOrderNumber(); ?>">
	<input type="hidden" name="IMPORTO" value="<?= number_format(kGetShopOrderTotalAmount(),2,",",""); ?>">
	<input type="hidden" name="DIVISA" value="<?= kGetShopCurrency(); ?>">
	<input type="hidden" name="ABI" value="<?= kGetShopVirtualPayABI(); ?>">
	<input type="hidden" name="ITEMS" value="<?= $items; ?>">
	<input type="hidden" name="URLOK" value="<?= kGetSiteURL().kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetShopPayPalReturnPage(true); ?>">
	<input type="hidden" name="URLKO" value="<?= kGetSiteURL().kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetShopPayPalReturnPage(false); ?>">
	<input type="hidden" name="URLACK" value="<?= kGetSiteURL().kGetBaseDir(); ?>inc/virtualpay_ipn.php">
	<input type="hidden" name="URLNACK" value="<?= kGetSiteURL().kGetBaseDir(); ?>inc/virtualpay_ipn_fail.php">
	<input type="hidden" name="MAC" value="<?= $mac; ?>">
	</div>
	<input type="submit" class="submit" value="<?= kTranslate('Paga con VirtualPay'); ?>">
	</form>
<script type="text/javascript">
	document.getElementById('PayPalForm').submit();
	</script>
