<?
kSetMenuSelectedByURL(kGetShopDir());
kPrintHeader();
?>

<?
$_GET['q']=6; //si possono aggiungere 6 bottiglie alla volta, non di meno
if(isset($_GET['addtocart'])) {
	kShopAddToCart($_GET['addtocart'],$_GET['q']);
	}
if(isset($_GET['removefromcart'])) {
	kShopRemoveFromCart($_GET['removefromcart'],$_GET['q']);
	}
?>

<div class="contenuti shop">	
	<div class="crumbs"><a href="<?= kGetHomeDir(); ?>">Home</a><? kPrintCrumbs(); ?></div>

	<div class="submenu"><?
	$ancestors=kGetCrumbs();
	if($ancestors[0]['idmenu']>0) echo kPrintMenu($ancestors[0]['idmenu'],false);
	?></div>

	<?
	if($_GET['subdir']=='carrello') {
		/**** CARRELLO ****/
		if(!isset($_GET['s'])) $_GET['s']=1;
		
		//salvataggi
		if(isset($_POST['name'])) kSetShopCartVar('customer_name',$_POST['name']);
		if(isset($_POST['email'])) kSetShopCartVar('customer_email',$_POST['email']);
		if(isset($_POST['Phone'])) kSetShopCartVar('customer_phone',$_POST['Phone']);
		if(isset($_POST['Address'])) kSetShopCartVar('customer_address',$_POST['Address']);
		if(isset($_POST['City'])) kSetShopCartVar('customer_city',$_POST['City']);
		if(isset($_POST['ZipCode'])) kSetShopCartVar('customer_zipcode',$_POST['ZipCode']);
		if(isset($_POST['Country'])) kSetShopCartVar('customer_country',$_POST['Country']);
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
		//fine salvataggi
		
		?>
		<div class="monocolonna" style="float:right;">
		<?
		if($_GET['s']<6) { ?>
			<div class="box">
				<ol class="steps"><?
				$steps=array("Revisione","Dati personali","Spedizione","Pagamento","Conferma");
				foreach($steps as $i=>$s) { ?>
					<li<?= ($i+1==$_GET['s']?' class="sel"':''); ?>><strong><?= $i+1; ?></strong> <?= $s; ?></li>
					<? }
				?></ol>
				<div style="clear:both;"></div>
				</div>
			<form action="" method="post">
			<? }

		if($_GET['s']==1) { ?>
			<div class="textBox">
				<h1><?= kTranslate('Your cart'); ?> &gt; <?= $steps[$_GET['s']-1]; ?></h1>
				<div class="box" style="width:200px;float:left;margin-right:20px;">
					Verifica il tuo ordine. Se c'&egrave; qualcosa di sbagliato sei ancora in tempo per correggerlo!
					</div>
				<table class="cartList">
				<tr><th>Qta</th><th>Vino</th><th>Prezzo</th><th></th></tr>
				<?
				$i=0;
				$cart=kGetShopCart();
				foreach($cart['items'] as $item) { ?>
					<tr>
					<td><?= $item['qty']; ?></td>
					<td><a href="<?= $item['permalink']; ?>"><strong><?= $item['titolo']; ?></strong></a></td>
					<td align="right"><?= number_format($item['prezzo']*$item['qty'],2); ?> &euro;</td>
					<td><a href="?removefromcart=<?= $item['idsitem']; ?>&q=1"><img src="<?= kGetTemplateDir(); ?>img/key_minus.png" width="13" height="13" alt="-" title="Remove" /></a> <a href="?addtocart=<?= $item['idsitem']; ?>&q=1"><img src="<?= kGetTemplateDir(); ?>img/key_plus.png" width="13" height="13" alt="+" title="Add" /></a> </a></td>
					</tr>
					<?
					$i++;
					}
				if($i==0) echo '<tr><td>'.kTranslate('Your cart is empty').'</td></tr>';
				else echo '<tr><th colspan="4" style="text-align:right;"><strong>'.kTranslate('Totale').' '.number_format(kGetShopCartTotalAmount(),2).' &euro;</strong></th></tr>';
				?>
				</table>
				</div>
			<? }
			

		elseif($_GET['s']==2) {
			$name=kGetShopCartVar('customer_name')!=""?kGetShopCartVar('customer_name'):kGetMemberName();
			$email=kGetShopCartVar('customer_email')!=""?kGetShopCartVar('customer_email'):kGetMemberEmail();
			$Phone=kGetShopCartVar('customer_phone')!=""?kGetShopCartVar('customer_phone'):kGetMemberMetadata('Phone');
			$Address=kGetShopCartVar('customer_address')!=""?kGetShopCartVar('customer_address'):kGetMemberMetadata('Address');
			$City=kGetShopCartVar('customer_city')!=""?kGetShopCartVar('customer_city'):kGetMemberMetadata('City');
			$ZipCode=kGetShopCartVar('customer_zipcode')!=""?kGetShopCartVar('customer_zipcode'):kGetMemberMetadata('ZipCode');
			$Country=kGetShopCartVar('customer_country')!=""?kGetShopCartVar('customer_country'):kGetMemberMetadata('Country');
			?>
			<div class="textBox">
				<h1><?= kTranslate('Your cart'); ?> &gt; <?= $steps[$_GET['s']-1]; ?></h1>
				<div class="box" style="width:200px;float:left;margin-right:20px;">
					Verifica i tui dati personali.
					</div>
				
				<div style="float:left;">
				<table class="form">
				<tr><td>Nome e Cognome *</td><td><input type="text" name="name" value="<?= str_replace('"','&quot;',$name); ?>" class="normal" /></td></tr>
				<tr><td>E-mail *</td><td><input type="text" name="email" value="<?= str_replace('"','&quot;',$email); ?>" class="normal" /></td></tr>
				<tr><td>Telefono</td><td><input type="text" name="Phone" value="<?= str_replace('"','&quot;',$Phone); ?>" class="normal" /></td></tr>
				<tr><td>Indirizzo</td><td><input type="text" name="Address" value="<?= str_replace('"','&quot;',$Address); ?>" class="normal" /></td></tr>
				<tr><td>Citt&agrave;</td><td><input type="text" name="City" value="<?= str_replace('"','&quot;',$City); ?>" class="normal" /> CAP <input type="text" name="ZipCode" value="<?= str_replace('"','&quot;',$ZipCode); ?>" class="normal" style="width:50px;" /></td></tr>
				<tr><td>Stato</td><td><input type="text" name="Country" value="<?= str_replace('"','&quot;',$Country); ?>" class="normal" /></td></tr>
				</table>
				<br />
				<small>* campo obbligatorio</small>
				<br />
				<br />
				</div>

				</div>
			<? }
			

		elseif($_GET['s']==3) {
			$countries=kGetShopCountries();
			$name=kGetShopCartVar('del_name')!=""?kGetShopCartVar('del_name'):kGetMemberName();
			$email=kGetShopCartVar('del_email')!=""?kGetShopCartVar('del_email'):kGetMemberEmail();
			$Address=kGetShopCartVar('del_Address')!=""?kGetShopCartVar('del_Address'):kGetMemberMetadata('Address');
			$Phone=kGetShopCartVar('del_Phone')!=""?kGetShopCartVar('del_Phone'):kGetMemberMetadata('Phone');
			$City=kGetShopCartVar('del_City')!=""?kGetShopCartVar('del_City'):kGetMemberMetadata('City');
			$ZipCode=kGetShopCartVar('del_ZipCode')!=""?kGetShopCartVar('del_ZipCode'):kGetMemberMetadata('ZipCode');
			$Country=kGetShopCartVar('del_Country')!=""?kGetShopCartVar('del_Country'):kGetMemberMetadata('Country');
			$Deliverer=kGetShopCartVar('deliverer')!=""?kGetShopCartVar('deliverer'):false;
			$Notes=kGetShopCartVar('del_Notes')!=""?kGetShopCartVar('del_Notes'):false;
			?>
			<div class="textBox">
				<h1><?= kTranslate('Your cart'); ?> &gt; <?= $steps[$_GET['s']-1]; ?></h1>
				<div class="box" style="width:200px;float:left;margin-right:20px;">
					Specifica l'indirizzo del destinatario e scegli la zona di residenza del destinatario.
					</div>
				
				<div style="float:left;">
					<h3>Indirizzo di spedizione</h3>
					<table class="form">
					<tr><td>Nome e Cognome *</td><td><input type="text" name="del_name" value="<?= str_replace('"','&quot;',$name); ?>" class="normal" /></td></tr>
					<tr><td>Telefono *</td><td><input type="text" name="del_Phone" value="<?= str_replace('"','&quot;',$Phone); ?>" class="normal" /></td></tr>
					<tr><td>Indirizzo *</td><td><input type="text" name="del_Address" value="<?= str_replace('"','&quot;',$Address); ?>" class="normal" /></td></tr>
					<tr><td>Citt&agrave; *</td><td><input type="text" name="del_City" value="<?= str_replace('"','&quot;',$City); ?>" class="normal" /> CAP <input type="text" name="del_ZipCode" value="<?= str_replace('"','&quot;',$ZipCode); ?>" class="normal" style="width:50px;" /></td></tr>
					<tr><td>Stato *</td><td><select name="del_Country"><?
						foreach($countries as $country) { ?>
							<option value="<?= $country['country']; ?>"<?= $country['country']==$Country?' selected':''; ?>><?= $country['country']; ?></option>
							<? }
						?></select></td></tr>
					<tr><td>Note per il corriere</td><td><input type="text" name="del_Notes" value="<?= str_replace('"','&quot;',$Notes); ?>" class="normal" style="width:200px;" /></td></tr>
					</table><br />
					<small>* campo obbligatorio</small>
					<br />
					<br />

					<h3>Zona di destinazione</h3>
					<?
					$cart=kGetShopCart();
					$deliverers=kGetShopDeliverersByZone(1);
					$i=0;
					foreach($deliverers as $del) {
						kSetShopDelivererById($del['iddel']);
						$spedizione=kGetShopDelivererPriceByKg($cart['totalweight']);
						?><input type="radio" name="Deliverer" id="Deliverer<?= $del['iddel']; ?>" value="<?= $del['iddel']; ?>"<?= ($del['iddel']==$Deliverer||($del['iddel']!=$Deliverer&&$i==0)?' checked':''); ?>> <label for="Deliverer<?= $del['iddel']; ?>"><strong><?= $del['name']; ?></strong></label><br /><?
						$i++;
						}
					?>
					</div>
				</div>
			<? }

		elseif($_GET['s']==4) {
			$countries=kGetShopCountries();
			$name=kGetShopCartVar('pay_name')!=""?kGetShopCartVar('pay_name'):kGetMemberName();
			$CodiceFiscale=kGetShopCartVar('pay_CodiceFiscale')!=""?kGetShopCartVar('pay_CodiceFiscale'):"";
			$VAT=kGetShopCartVar('pay_VAT')!=""?kGetShopCartVar('pay_VAT'):"";
			$email=kGetShopCartVar('pay_email')!=""?kGetShopCartVar('pay_email'):kGetMemberEmail();
			$Address=kGetShopCartVar('pay_Address')!=""?kGetShopCartVar('pay_Address'):kGetMemberMetadata('Address');
			$City=kGetShopCartVar('pay_City')!=""?kGetShopCartVar('pay_City'):kGetMemberMetadata('City');
			$ZipCode=kGetShopCartVar('pay_ZipCode')!=""?kGetShopCartVar('pay_ZipCode'):kGetMemberMetadata('Zip Code');
			$Country=kGetShopCartVar('pay_Country')!=""?kGetShopCartVar('pay_Country'):kGetMemberMetadata('Country');
			$Payment=kGetShopCartVar('payment')!=""?kGetShopCartVar('payment'):false;
			?>
			<div class="textBox">
				<h1><?= kTranslate('Your cart'); ?> &gt; <?= $steps[$_GET['s']-1]; ?></h1>
				<div class="box" style="width:200px;float:left;margin-right:20px;">
					Inserisci i dati di fatturazione e scegli la modalit&agrave; di pagamento tra quelle disponibili.
					</div>
				
				<div style="float:left;width:400px;">
					<h3>Dati di fatturazione</h3>
					<table class="form">
					<tr><td>Nome e Cognome *<br />/ Ragione sociale</td><td><input type="text" name="pay_name" value="<?= str_replace('"','&quot;',$name); ?>" class="normal" /></td></tr>
					<tr><td>Codice Fiscale *</td><td><input type="text" name="pay_CodiceFiscale" class="normal" value="<?= str_replace('"','&quot;',$CodiceFiscale); ?>" /></td></tr>
					<tr><td>Partita IVA</td><td><input type="text" name="pay_VAT" class="normal" value="<?= str_replace('"','&quot;',$VAT); ?>" /></td></tr>
					<tr><td>Indirizzo *</td><td><input type="text" name="pay_Address" value="<?= str_replace('"','&quot;',$Address); ?>" class="normal" /></td></tr>
					<tr><td>Citt&agrave; *</td><td><input type="text" name="pay_City" value="<?= str_replace('"','&quot;',$City); ?>" class="normal" /> CAP <input type="text" name="pay_ZipCode" value="<?= str_replace('"','&quot;',$ZipCode); ?>" class="normal" style="width:50px;" /></td></tr>
					<tr><td>Stato *</td><td><select name="pay_Country"><?
						foreach($countries as $country) { ?>
							<option value="<?= $country['country']; ?>"<?= $country['country']==$Coutry?' selected':''; ?>><?= $country['country']; ?></option>
							<? }
						?></select></td></tr>
					</table>
					<br />
					<small>* campo obbligatorio</small>
					<br />
					<br />
					
					<h3>Modalit&agrave; di pagamento</h3>
					<?
					$payments_modes=kGetShopPaymentsByZone(1);
					$i=0;
					foreach($payments_modes as $pay) {
						kSetShopPaymentById($pay['idspay']);
						?>
						<input type="radio" name="Payment" id="Payment<?= $pay['idspay']; ?>" value="<?= $pay['idspay']; ?>"<?= ($pay['idspay']==$Payment||($pay['idspay']!=$Payment&&$i==0)?' checked':''); ?>><label for="Payment<?= $pay['idspay']; ?>"><strong><?= $pay['name']; ?></strong></label>
						<div style="padding-left:20px;"><?= $pay['descr']; ?></div>
						<?
						$i++;
						}
					?>
					</div>
				</div>
			<? }
			
		elseif($_GET['s']==5) {
			?>
			<div class="textBox">
				<h1><?= kTranslate('Your cart'); ?> &gt; <?= $steps[$_GET['s']-1]; ?></h1>
				<div class="box" style="width:200px;float:left;margin-right:20px;">
					Ecco un riepilogo generale del tuo ordine, prima di confermarlo definitivamente.
					</div>
				
				<div style="float:left;width:400px;">
					<h3>Il tuo ordine</h3>
					<table class="cartList" style="width:100%;font-size:1em;">
					<tr><th>Qta</th><th>Vino</th><th>Prezzo</th><th></th></tr>
					<?
					$i=0;
					$cart=kGetShopCart();
					foreach($cart['items'] as $item) { ?>
						<tr>
						<td><?= $item['qty']; ?></td>
						<td><a href="<?= $item['permalink']; ?>"><strong><?= $item['titolo']; ?></strong></a></td>
						<td align="right"><?= number_format($item['prezzo']*$item['qty'],2); ?> &euro;</td>
						</tr>
						<?
						$i++;
						}
					if($i==0) { ?><tr><td><?= kTranslate('Your cart is empty'); ?></td></tr><? }
					else { ?><tr><th colspan="4" style="text-align:right;"><?= kTranslate('Totale').': '.number_format(kGetShopCartItemsAmount(),2); ?> &euro;<br />
						Contributo spese di spedizione: <?= number_format(kGetShopCartShippingPrice()+kGetShopCartPaymentPrice(kGetShopCartItemsAmount()+kGetShopCartShippingPrice()),2); ?> &euro;</th></tr> <? }
					?>
					</table><br />
					
					<div class="box" style="text-align:center;background-color:#c5b8a5;"><h1 style="color:#000;">Prezzo totale: <?= number_format(kGetShopCartTotalAmount(),2); ?> &euro;</h1></div>
					<br />

					<h3>I tuoi dati</h3>
					<table>
					<tr><th>Nome e Cognome</td><td><?= kGetShopCartVar('customer_name'); ?></td></tr>
					<tr><th>E-mail</th><td><?= kGetShopCartVar('customer_email'); ?></td></tr>
					<tr><th>Telefono</th><td><?= kGetShopCartVar('customer_phone'); ?></td></tr>
					<tr><th>Indirizzo</th><td><?= kGetShopCartVar('customer_address'); ?></td></tr>
					<tr><th>Citt&agrave;</th><td><?= kGetShopCartVar('customer_city'); ?> - CAP <?= kGetShopCartVar('customer_zipcode'); ?></td></tr>
					<tr><th>Stato</th><td><?= kGetShopCartVar('customer_country'); ?></td></tr>
					</table><br />

					<h3>Indirizzo di spedizione</h3>
					<table>
					<tr><th>Nome e Cognome</th><td><?= kGetShopCartVar('del_name'); ?></td></tr>
					<tr><th>E-mail</th><td><?= kGetShopCartVar('del_email'); ?></td></tr>
					<tr><th>Telefono</th><td><?= kGetShopCartVar('del_Phone'); ?></td></tr>
					<tr><th>Indirizzo</th><td><?= kGetShopCartVar('del_Address'); ?></td></tr>
					<tr><th>Citt&agrave;</th><td><?= kGetShopCartVar('del_City'); ?> - CAP <?= kGetShopCartVar('del_ZipCode'); ?></td></tr>
					<tr><th>Stato</th><td><?= kGetShopCartVar('del_Country'); ?></td></tr>
					</table><br />

					<h3>Dati di fatturazione</h3>
					<table>
					<tr><th>Nome e Cognome<br />/ Ragione sociale</th><td><?= kGetShopCartVar('pay_name'); ?></td></tr>
					<tr><th>Codice Fiscale</th><td><?= kGetShopCartVar('pay_CodiceFiscale'); ?></td></tr>
					<tr><th>Partita IVA</th><td><?= kGetShopCartVar('pay_VAT'); ?></td></tr>
					<tr><th>Indirizzo</th><td><?= kGetShopCartVar('pay_Address'); ?></td></tr>
					<tr><th>Citt&agrave;</th><td><?= kGetShopCartVar('pay_City'); ?> - CAP <?= kGetShopCartVar('pay_ZipCode'); ?></td></tr>
					<tr><th>Stato</th><td><?= kGetShopCartVar('pay_Country'); ?></td></tr>
					</table><br />
					
					<h3>Metodo di pagamento</h3>
					<?
					$pay=kGetShopPaymentById(kGetShopCartVar('payment'));
					echo '<strong>'.$pay['name'].'</strong>';
					echo $pay['descr'];
					?>
					</div>
				</div>
			<br />
			<div class="navButton">
				<? if($_GET['s']>1) { ?><input type="submit" name="prev" value="&lt; Indietro" style="cursor:pointer;" onclick="this.form.action='?s=<?= $_GET['s']-1; ?>';" /><? } ?>
				<?
				if(kGetShopCartVar('customer_name')!=""&&
					kGetShopCartVar('customer_email')!=""&&
					kGetShopCartVar('del_name')!=""&&
					kGetShopCartVar('del_Address')!=""&&
					kGetShopCartVar('del_Phone')!=""&&
					kGetShopCartVar('del_City')!=""&&
					kGetShopCartVar('del_ZipCode')!=""&&
					kGetShopCartVar('del_Country')!=""&&
					kGetShopCartVar('pay_name')!=""&&
					kGetShopCartVar('pay_CodiceFiscale')!=""&&
					kGetShopCartVar('pay_Address')!=""&&
					kGetShopCartVar('pay_City')!=""&&
					kGetShopCartVar('pay_Country')!=""
					) {
					?><input type="submit" style="font-size:1.3em;padding:10px 20px;" name="next" value="CONFERMA ORDINE" class="submit" onclick="this.form.action='?s=<?= $_GET['s']+1; ?>';" /><?
					}
				else {
					?>Devi compilare tutti i dati necessari alla registrazione del tuo ordine.<?
					}
				?>
				</div>
			</form>
			</div>
			<? }
			
		elseif($_GET['s']==6) {
			$pay=kGetShopPaymentById(kGetShopCartVar('payment'));
			$uid=kShopSaveOrder();
			if($uid!=false) {
				kSetShopOrderByNumber($uid);
				?>
				<div class="textBox">
					Il tuo ordine &egrave; stato processato con successo.<br /><br />
					<?
					if($pay['paypal']=='s') {
						?>
						Stai per essere rediretto sul sito di PayPal per procedere con il pagamento.<br />
						Nel caso il redizionamento non funzionasse, premi il tasto qui sotto:<br />
						<?
						kPrintShopPayPalForm();
						}
					?>
					</div>
					</div>
				<? }
			else { ?>
				<div class="textBox">
					Si sono verificati dei problemi durante il salvataggio del tuo ordine.<br />
					Per favore, riprova tra poco.
					</div>
				<? }
			}
			
	
		if($_GET['s']<5) { ?>
			<div class="navButton">
				<? if($_GET['s']>1) { ?><input type="submit" name="prev" value="&lt; Indietro" style="cursor:pointer;" onclick="this.form.action='?s=<?= $_GET['s']-1; ?>';" /><? } ?>
				<input type="submit" name="next" value="Avanti &gt;" class="submit" onclick="this.form.action='?s=<?= $_GET['s']+1; ?>';" />
				</div>
			</form>
			</div>
			<? }
		}
	
	else {
		/**** OGGETTI ****/
		?>
		<div class="monocolonna" style="float:right;">
		<?
		if(kHaveShopItem()) {
			kSetShopItemByDir();
			$phg=kGetShopItemPhotogallery();
			if(isset($phg[0])) {
				kSetImage($phg[0]);
				?><div class="bottiglia"><img src="<?= kGetImageURL(); ?>" width="<?= kGetImageWidth(); ?>" height="<?= kGetImageHeight(); ?>" alt="" /></div><?
				}
			?>
			<div class="textBox">
				<h2><?= kGetShopItemTitle(); ?></h2>
				<?= kGetShopItemText(); ?>
				<div class="box">
					<div style="float:right;text-align:right;"><a href="?addtocart=<?= kGetShopItemId(); ?>&q=6" class="smallbutton">aggiungi al carrello</a><br />
						</div>
					Prezzo a bottiglia: <strong><?= kGetShopItemPrice(); ?> <?= kGetShopCurrency("symbol"); ?></strong><br />
					<small>Acquisto possibile in confezioni da 6 bottiglie e multipli</small>
					</div>
				<div style="text-align:right;"><small><a href="<?= kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetShopDir(); ?>">Torna al negozio</a></small></div>
				</div><?
			}

		else {
			?><div class="textBox"><?
			foreach(kGetShopItemQuickList() as $item) { ?>
				<div class="shopItem">
					<?
					kSetShopItemByDir($item['dir']);
					$phg=kGetShopItemPhotogallery();
					if(isset($phg[0])) {
						kSetImage($phg[0]);
						?><div class="prevBottiglia"><a href="<?= kGetShopItemPermalink(); ?>"><img src="<?= kGetImageURL(); ?>" height="330" alt="" /></a></div><?
						}
					?>
					<h3><a href="<?= kGetShopItemPermalink(); ?>"><?= kGetShopItemTitle(); ?></a></h3>
					<?= kGetShopItemSubtitle(); ?>
					<div class="prezzo"><?= preg_replace("/(\d*)\.(\d*)/","<strong>$1</strong>.$2",kGetShopItemPrice()); ?> <?= kGetShopCurrency("symbol"); ?></div>
					</div>
				<? }
			?></div><?
			}
			?>
		</div>
		<? } ?>
	</div>

<?
kPrintFooter();
?>