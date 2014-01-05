<?
/* (c)2012 Kalamun GPL v3*/

require_once('../../../inc/tplshortcuts.lib.php');
kInitBettino('../../../');


/* print cart list */
if(isset($_GET['printCartList'])) {
	printCart();
	}
	
/* print a compact version of cart */
if(isset($_GET['printCompactCart'])) {
	printCompactCart();
	}
	
/* add to cart */
elseif(isset($_POST['addToCart'])) {
	kShopAddToCart(intval($_POST['addToCart']),1);
	}

/* remove from cart */
elseif(isset($_POST['removeFromCart'])) {
	kShopRemoveFromCart(intval($_POST['removeFromCart']),1);
	}

/* get deliverer form giving country code */
elseif(isset($_GET['getDeliversByCountryCode'])) {
	$cart=kGetShopCart();
	$zone=kGetShopZoneByCountryCode($_GET['getDeliversByCountryCode']);
	$deliverers=kGetShopDeliverersByZone($zone);
	$i=0;
	foreach($deliverers as $del) {
		kSetShopDelivererById($del['iddel']);
		$spedizione=kGetShopDelivererPriceByKg($cart['totalweight']);
		?><input type="radio" name="Deliverer" id="Deliverer<?= $del['iddel']; ?>" value="<?= $del['iddel']; ?>"<?= ($del['iddel']==$Deliverer||($del['iddel']!=$Deliverer&&$i==0)?' checked':''); ?>> <label for="Deliverer<?= $del['iddel']; ?>"><strong><?= $del['name']; ?></strong></label><br /><?
		$i++;
		}
	}

/* get payment methods form giving country code */
elseif(isset($_GET['getPaymentsByCountryCode'])) {
	$cart=kGetShopCart();
	$zone=kGetShopZoneByCountryCode($_GET['getPaymentsByCountryCode']);
	$payments_modes=kGetShopPaymentsByZone($zone);
	$i=0;
	foreach($payments_modes as $pay) {
		kSetShopPaymentById($pay['idspay']);
		?>
		<input type="radio" name="Payment" id="Payment<?= $pay['idspay']; ?>" value="<?= $pay['idspay']; ?>"<?= ($i==0)?' checked':''; ?>><label for="Payment<?= $pay['idspay']; ?>"><strong><?= $pay['name']; ?></strong> <?= ($pay['price']>0?'('.$pay['price'].' '.kGetShopCurrency("symbol").')':'') ?><?= ($pay['pricepercent']>0?'('.$pay['pricepercent'].'%)':'') ?></label>
		<div style="padding-left:20px;"><?= $pay['descr']; ?></div>
		<?
		$i++;
		}
	}

/* save order data */
elseif(isset($_POST['saveOrderData'])) {
	if(isset($_POST['name'])) kSetShopCartVar('customer_name',$_POST['name']);
	if(isset($_POST['email'])) kSetShopCartVar('customer_email',$_POST['email']);
	if(isset($_POST['Phone'])) kSetShopCartVar('customer_Phone',$_POST['Phone']);
	if(isset($_POST['Address'])) kSetShopCartVar('customer_Address',$_POST['Address']);
	if(isset($_POST['City'])) kSetShopCartVar('customer_City',$_POST['City']);
	if(isset($_POST['ZipCode'])) kSetShopCartVar('customer_ZipCode',$_POST['ZipCode']);
	if(isset($_POST['Country'])) kSetShopCartVar('customer_Country',$_POST['Country']);
	if(isset($_POST['del_name'])) kSetShopCartVar('del_name',$_POST['del_name']);
	if(isset($_POST['del_email'])) kSetShopCartVar('del_email',$_POST['del_email']);
	if(isset($_POST['del_Phone'])) kSetShopCartVar('del_Phone',$_POST['del_Phone']);
	if(isset($_POST['del_Address'])) kSetShopCartVar('del_Address',$_POST['del_Address']);
	if(isset($_POST['del_City'])) kSetShopCartVar('del_City',$_POST['del_City']);
	if(isset($_POST['del_ZipCode'])) kSetShopCartVar('del_ZipCode',$_POST['del_ZipCode']);
	if(isset($_POST['del_Country'])) kSetShopCartVar('del_Country',$_POST['del_Country']);
	if(isset($_POST['del_Notes'])) kSetShopCartVar('del_Notes',$_POST['del_Notes']);
	if(isset($_POST['Deliverer'])) kSetShopCartVar('deliverer',$_POST['Deliverer']);
	if(isset($_POST['pay_name'])) kSetShopCartVar('pay_name',$_POST['pay_name']);
	if(isset($_POST['pay_CodiceFiscale'])) kSetShopCartVar('pay_CodiceFiscale',$_POST['pay_CodiceFiscale']);
	if(isset($_POST['pay_VAT'])) kSetShopCartVar('pay_VAT',$_POST['pay_VAT']);
	if(isset($_POST['pay_email'])) kSetShopCartVar('pay_email',$_POST['pay_email']);
	if(isset($_POST['pay_Address'])) kSetShopCartVar('pay_Address',$_POST['pay_Address']);
	if(isset($_POST['pay_City'])) kSetShopCartVar('pay_City',$_POST['pay_City']);
	if(isset($_POST['pay_ZipCode'])) kSetShopCartVar('pay_ZipCode',$_POST['pay_ZipCode']);
	if(isset($_POST['pay_Country'])) kSetShopCartVar('pay_Country',$_POST['pay_Country']);
	if(isset($_POST['Payment'])) kSetShopCartVar('payment',$_POST['Payment']);
	}

/* get order summary */
elseif(isset($_GET['getOrderSummary'])) { ?>
	<div class="shopItemList"><? printCart(); ?></div>
	<br />
	<div style="font-size:2em;"><?= kTranslate("Total amount"); ?>: <strong><?= number_format(kGetShopCartTotalAmount(),2); ?> &euro;</strong></div>
	<br />

	<h3><?= kTranslate("Dati personali"); ?></h3>
	<table>
	<tr><th><?= kTranslate("Full name"); ?></td><td><?= kGetShopCartVar('customer_name'); ?></td></tr>
	<tr><th><?= kTranslate("E-mail"); ?></th><td><?= kGetShopCartVar('customer_email'); ?></td></tr>
	<tr><th><?= kTranslate("Phone number"); ?></th><td><?= kGetShopCartVar('customer_phone'); ?></td></tr>
	<tr><th><?= kTranslate("Address"); ?></th><td><?= kGetShopCartVar('customer_address'); ?></td></tr>
	<tr><th><?= kTranslate("City"); ?></th><td><?= kGetShopCartVar('customer_city'); ?> - <?= kTranslate("Zip Code"); ?> <?= kGetShopCartVar('customer_zipcode'); ?></td></tr>
	<tr><th><?= kTranslate("Country"); ?></th><td><?= kGetShopCartVar('customer_country'); ?></td></tr>
	</table><br />


	<h3><?= kTranslate("Modalita di pagamento"); ?></h3>
	<?
	$pay=kGetShopPaymentById(kGetShopCartVar('payment'));
	echo '<strong>'.$pay['name'].'</strong>';
	echo $pay['descr'];


	/* check order validity */
	$log="";
	
	if(kGetShopCartVar('customer_name')=="") $log="Non hai scritto il tuo nome";
	elseif(kGetShopCartVar('customer_email')=="") $log="Non hai scritto la tua email";
	elseif(kGetShopCartVar('customer_country')=="") $log="Il tuo paese non risulta valido";
	elseif(kGetShopCartItemsCount()==0) $log="Il tuo carrello è vuoto";
	
	if($log=="") {
		/* show buy button */
		echo '<div class="box" style="font-size:1.5em;text-align:center;"><a href="javascript:TShop.saveOrder(\'orderSummary\');" class="button">'.kTranslate("Conferma l'ordine").'</a></div><br />';
		}
	else {
		echo '<div class="box"><h2 style="color:#ca0000;">'.kTranslate($log).'!</h2></div><br />';
		}


	}

elseif(isset($_POST['saveOrder'])) {
	$pay=kGetShopPaymentById(kGetShopCartVar('payment'));
	$uid=kShopSaveOrder(false);
	if($uid!=false) {
		kSetShopOrderByNumber($uid);
		?>
		<div class="textBox">
			<?= kTranslate('Your order was successfully processed'); ?>.<br /><br />
			<?
			if($pay['gateway']=='paypal') {
				?>
				<?= kTranslate('You are about to be redirected to PayPal for payment'); ?><br />
				<?= kTranslate("If it doesn't work, click on the following link"); ?><br />
				<?
				kPrintShopPayPalForm();
				}
			elseif($pay['gateway']=='virtualpay') {
				?>
				<?= kTranslate('You are about to be redirected to VirtualPay for payment'); ?><br />
				<?= kTranslate("If it doesn't work, click on the following link"); ?><br />
				<?
				kPrintShopVirtualPayForm();
				}
			?>
			</div>
			</div>
		<?
		kShopEmptyCart();
		}
	else { ?>
		<div class="textBox">
			<?= kTranslate('We are sorry, some errors occurred while processing your order'); ?><br />
			<a href="mailto:<?= kGetAdminEmail(); ?>"><?= kTranslate('Please contact us'); ?></a><br />
			</div>
		<? }
	}


/****************************/
/*        functions         */
/****************************/

function printCart() { ?>
	<table class="cart">
	<?
	$i=0;
	$cart=kGetShopCart();
	foreach($cart['items'] as $item) {
		if($i==0) {
			?><tr><th class="qty"><?= kTranslate('qta'); ?></th><th class="title"><?= kTranslate('oggetto'); ?></th><th class="price"><?= kTranslate('prezzo'); ?></th>
			</tr><? } ?>
		<tr class="<?= ($i%2==0?'odd':'even'); ?>">
		<td class="qty">
			<? if(isset($_GET['removeButton'])&&$_GET['removeButton']=="true"&&kGetShopCartItemsCount()>1) { ?><a href="javascript:TShop.removeFromCart(<?= $item['idsitem']; ?>,<?= $_GET['removeButton']; ?>,<?= $_GET['buyButton']; ?>);"><img src="<?= kGetTemplateDir(); ?>img/remove.png" width="10" height="10" alt="<?= kTranslate('rimuovi'); ?>" /></a><? } ?>
			<?= $item['qty']; ?>
			<? if(isset($_GET['removeButton'])&&$_GET['removeButton']=="true") { ?><a href="javascript:TShop.addToCart(<?= $item['idsitem']; ?>,null,<?= $_GET['removeButton']; ?>,<?= $_GET['buyButton']; ?>);"><img src="<?= kGetTemplateDir(); ?>img/add.png" width="10" height="10" alt="<?= kTranslate('aggiungi'); ?>" /></a><? } ?>
			</td>
		<td class="title"><a href="<?= $item['permalink']; ?>"><?= $item['titolo']; ?></a></td>
		<td class="price"><?= number_format($item['prezzo']*$item['qty'],2); ?> &euro;</td>
		</tr>
		<?
		$i++;
		}
	if($i==0) echo '<tr><td>'.kTranslate('Il tuo carrello è vuoto').'</td></tr>';
	else echo '<tr><th colspan="3" class="total">'.kTranslate('totale').' '.number_format(kGetShopCartItemsAmount(),2).' &euro;</th></tr>';
	?>
	</table>
	<? if(isset($_GET['buyButton'])&&$_GET['buyButton']=="true"&&$i>0) { ?><div class="submit"><a href="<?= kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetShopDir(); ?>?cart" class="smallbutton"><?= kTranslate('acquista'); ?></a></div><? } ?>
	<? }

function printCompactCart() {
	$cart=kGetShopCart();
	echo $cart['itemsnumber'].' - '.$cart['totalprice'].' '.kGetShopCurrency("symbol");
	}


?>