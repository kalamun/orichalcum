<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="PayPalForm">
	<div style="display:none;">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="<?= kGetShopPayPalBusinessId(); ?>">
	<input type="hidden" name="lc" value="IT">
	<input type="hidden" name="item_name" value="<?= kGetShopOrderNumber(); ?>">
	<input type="hidden" name="amount" value="<?= kGetShopOrderTotalAmount(); ?>">
	<input type="hidden" name="currency_code" value="<?= kGetShopCurrency(); ?>">
	<input type="hidden" name="button_subtype" value="services">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="rm" value="0">
	<input type="hidden" name="notify_url" value="<?= kGetSiteURL().kGetBaseDir(); ?>inc/paypal_ipn.php">
	<input type="hidden" name="return" value="<?= kGetSiteURL().kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetShopPayPalReturnPage(true); ?>">
	<input type="hidden" name="cancel_return" value="<?= kGetSiteURL().kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetShopPayPalReturnPage(false); ?>">
	<input type="hidden" name="custom" value="<?= kGetShopOrderNumber(); ?>">
	</div>
	<input type="submit" class="submit" value="<?= kTranslate('Paga con PayPal'); ?>" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
<script type="text/javascript">
	document.getElementById('PayPalForm').submit();
	</script>
