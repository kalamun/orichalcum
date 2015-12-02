<?php
$customerId=kGetSiteName();
$customerId=preg_replace("/\W*/","",strtoupper($customerId));
$customerId=substr($customerId,0,2).substr($customerId,-2);
$codTrans = $customerId.kGetShopOrderNumber();
$importo = number_format(kGetShopOrderTotalAmount(),2,"","");

$mac=sha1("codTrans=".$codTrans."divisa=".kGetShopCurrency()."importo=".$importo.kGetShopXPayKey());

?>
<form action="https://coll-ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet" method="post" id="XPayForm">
	<div style="display:none;">
	<input type="hidden" name="alias" value="<?= kGetShopXPayBusinessId(); ?>">
	<input type="hidden" name="codTrans" value="<?= $codTrans; ?>">
	<input type="hidden" name="importo" value="<?= $importo; ?>">
	<input type="hidden" name="divisa" value="<?= kGetShopCurrency(); ?>">
	<input type="hidden" name="url" value="<?= kGetSiteURL().kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetShopReturnPage(true); ?>">
	<input type="hidden" name="url_back" value="<?= kGetSiteURL().kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetShopReturnPage(false); ?>">
	<input type="hidden" name="urlpost" value="<?= kGetSiteURL().kGetBaseDir(); ?>inc/xpay_ipn.php">
	<input type="hidden" name="mac" value="<?= $mac; ?>">
	<input type="hidden" name="languageId" value="ITA">
	<input type="hidden" name="Note1" value="Order number: <?= kGetShopOrderNumber(); ?>">
	</div>
	<input type="submit" class="submit" value="<?= kTranslate('Paga con Carta di Credito'); ?>">
	</form>
<script type="text/javascript">
	document.getElementById('XPayForm').submit();
</script>
