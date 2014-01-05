<?
kSetMenuSelectedByURL($GLOBALS['__dir__']);
kPrintHeader();
?>

<div id="corpo">
	<div id="titleBox">
		<h1><?= kGetPageTitle(); ?></h1>
		</div>

	<div id="contentsBox">
		<?
		$cart=kGetShopCart();
		
		// if cart is empty, show a list of all items
		if($cart['itemsnumber']==0) { ?>
			<?= kGetPagePreview(); ?>
			<br />
			<table class="catalog">
				<tr>
					<th class="title"><?= kTranslate('Titolo'); ?></th>
					<th><?= kTranslate('Anno'); ?></th>
					<th class="price"><?= kTranslate('Prezzo'); ?></th>
					</tr>
				<?
				$i=0;
				foreach(kGetShopItemQuickList() as $item) {
					kSetShopItemByDir($item['dir']);
					?>
					<tr class="<?= $i%2==0?'odd':'even'; ?>">
					<td class="title"><a href="<?= kGetShopItemPermalink(); ?>"><?= kGetShopItemTitle(); ?></a></td>
					<td class="date"><?= kGetShopItemDate("%Y"); ?></td>
					<td class="price"><?= kGetShopItemPrice(); ?> <?= kGetShopCurrency("symbol"); ?></td>
					</tr>
					<?
					$i++;
					}
					?>
				</table>
			
			<? }
		
		// else show the cart
		else {
			if(!isset($_GET['saveCart'])) {
				/**** CARRELLO ****/
				
				$name="";
				$email="";
				$Phone="";
				$Address="";
				$City="";
				$ZipCode="";
				
				if(kMemberIsLogged()) {
					$name=kGetMemberName();
					$email=kGetMemberEmail();
					$Phone=kGetMemberMetadata('Phone');
					$Address=kGetMemberMetadata('Address');
					$City=kGetMemberMetadata('City');
					$ZipCode=kGetMemberMetadata('ZipCode');
					}
				?>

				<script type="text/javascript">
					var TShop=new kShop();
					var actualStep=1;
					function cartNextStep() {
						if(!cartCheckMandatory()) {
							document.getElementById('mandatoryAlert').style.display="block";
							return false;
							}
						document.getElementById('mandatoryAlert').style.display="none";
						document.getElementById('step'+actualStep).style.display="none";
						actualStep++;
						document.getElementById('step'+actualStep).style.display="block";
						cartShowPrevNext();
						}
					function cartPrevStep() {
						document.getElementById('mandatoryAlert').style.display="none";
						document.getElementById('step'+actualStep).style.display="none";
						actualStep--;
						document.getElementById('step'+actualStep).style.display="block";
						cartShowPrevNext();
						}
					function cartShowPrevNext() {
						var buttons=document.getElementById('stepsNavigator').getElementsByTagName('A');
						if(actualStep==1) {
							buttons[0].style.display='none';
							buttons[1].style.display='inline-block';
							}
						else if(actualStep==3) {
							buttons[0].style.display='inline-block';
							buttons[1].style.display='none';
							TShop.saveCartVars('orderDetails');
							TShop.getOrderSummary('orderSummary');
							}
						else {
							buttons[0].style.display='inline-block';
							buttons[1].style.display='inline-block';
							}
						}
					function cartCheckMandatory() {
						var container=document.getElementById('step'+actualStep);
						var toCheck=Array();
						var elements=container.getElementsByTagName('INPUT');
						for(var i=0;elements[i];i++) { toCheck.push(elements[i]); }
						var elements=container.getElementsByTagName('SELECT');
						for(var i=0;elements[i];i++) { toCheck.push(elements[i]); }
						var elements=container.getElementsByTagName('TEXTAREA');
						for(var i=0;elements[i];i++) { toCheck.push(elements[i]); }
						for(var i=0;toCheck[i];i++) {
							if(toCheck[i].getAttribute("mandatory")=="true") {
								if(toCheck[i].value=="") return false;
								}
							}
						return true;
						}
					</script>
			
				<form action="?cart" method="post" id="orderDetails">

				<div id="step1">
					<div id="shopItemList" class="shopItemList"></div>
					<script type="text/javascript">TShop.printCartList('shopItemList',true,false);</script>
					</div>
					
				<div id="step2" style="display:none;">
					<h2><?= kTranslate("Dati personali"); ?></h2>
					<table class="form">
					<tr><td><?= kTranslate("Full name"); ?> *</td><td><input type="text" name="name" id="field_name" mandatory="true" value="<?= str_replace('"','&quot;',$name); ?>" class="normal" onchange="TShop.syncField(this);" /></td></tr>
					<tr><td><?= kTranslate("E-mail"); ?> *</td><td><input type="text" name="email" id="field_email" mandatory="true" value="<?= str_replace('"','&quot;',$email); ?>" class="normal" onchange="TShop.syncField(this);" /></td></tr>
					<tr><td><?= kTranslate("Phone number"); ?></td><td><input type="text" name="Phone" id="field_Phone" value="<?= str_replace('"','&quot;',$Phone); ?>" class="normal" onchange="TShop.syncField(this);" /></td></tr>
					<tr><td><?= kTranslate("Address"); ?></td><td><input type="text" name="Address" id="field_Address" value="<?= str_replace('"','&quot;',$Address); ?>" class="normal" onchange="TShop.syncField(this);" /></td></tr>
					<tr><td><?= kTranslate("City"); ?></td><td><input type="text" name="City" id="field_City" value="<?= str_replace('"','&quot;',$City); ?>" class="normal" onchange="TShop.syncField(this);" /> <?= kTranslate("Zip Code"); ?> <input type="text" name="ZipCode" id="field_ZipCode" value="<?= str_replace('"','&quot;',$ZipCode); ?>" class="normal" style="width:50px;" onchange="TShop.syncField(this);" /></td></tr>
					<tr><td><?= kTranslate("Country"); ?> *</td><td><select name="Country" id="Country" mandatory="true" onchange="TShop.refreshPayments('payment',this.value)"><?
						foreach(kGetShopCountries() as $country) { ?>
							<option value="<?= $country['ll']; ?>"<?= $country['country']=="Italy"?' selected':''; ?>><?= $country['country']; ?></option>
							<? }
						?></select></td></tr>
					<tr style="display:none;"><td><?= kTranslate("Payment Method"); ?></td><td>
						<div id="payment"></div>
						<script type="text/javascript">TShop.refreshPayments('payment',document.getElementById('Country').value)</script>
						</td></tr>

					</table>
					<div style="text-align:right;"><small>* <?= kTranslate("Mandatory field"); ?></small></div>
					</div>

				<div id="step3" style="display:none;">
					<h2><?= kTranslate('Riepilogo'); ?></h2>
					<div id="orderSummary"></div>
					</div>
					
				<div id="stepsNavigator">
					<? if(kGetShopCartItemsCount()>0) { ?>
						<a onclick="return cartPrevStep()" class="smallbutton" style="display:none"><?= kTranslate('Indietro'); ?></a>
						<a onclick="return cartNextStep()" class="button"><?= kTranslate('Avanti'); ?></a>
						<? } ?>
					<div id="mandatoryAlert"><?= kTranslate('Compila i campi obbligatori'); ?></div>
					</div>
					
				</form>
				<?
				}
					
			else {
				$pay=kGetShopPaymentById(kGetShopCartVar('payment'));
				$uid=kShopSaveOrder();
				if($uid!=false) {
					kSetShopOrderByNumber($uid);
					?>
					<?= kTranslate('Your order was successfully processed'); ?>.<br /><br />
					<?
					if($pay['paypal']=='s') {
						?>
						<?= kTranslate('You are about to be redirected to PayPal for payment'); ?><br />
						<?= kTranslate("If it doesn't work, click on the following link"); ?><br />
						<?
						kPrintShopPayPalForm();
						}
					}
				else { ?>
					<?= kTranslate('We are sorry, some errors occurred while processing your order'); ?><br />
					<a href="mailto:<?= kGetAdminEmail(); ?>"><?= kTranslate('Please contact us'); ?></a><br />
					<? }
				}
			}
		
		?>
		</div>

		
	<div id="sideBar">
		<?
		/* LOGIN */
		if(!kMemberIsLogged()) { ?>
			<div class="subtitleBox">
				<h4><?= kTranslate('Hai già un utente?'); ?></h4>
				<p><?= kTranslate("Accedi per terminare velocemente l'acquisto"); ?></p>
				</div>
			<?
			kPrintLogInForm();
			}
		else { ?>
			<div class="subtitleBox"><h2><?= kTranslate('La tua area riservata'); ?></h2></div>
			<div class="padding">
			<?= kTranslate('Ti sei identificato come'); ?><br />
			<em><?= kGetMemberName(); ?></em><br />
			<br />
			<a href="?logout" class="smallbutton"><?= kTranslate('Esci'); ?></a>
			</div>
			<? } ?>
		</div>

	<div style="clear:both;"></div>
	</div>
	
<? kPrintFooter(); ?>
