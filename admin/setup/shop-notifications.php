<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Negozio Notifiche");
include_once("../inc/head.inc.php");

if(isset($_POST['update'])) {
	$kaImpostazioni->replaceParam('shop-mail_checkout',b3_htmlize($_POST['mail_checkout'],false),b3_htmlize($_POST['mail_checkout_title'],false,""));
	$kaImpostazioni->replaceParam('shop-mail_payed',b3_htmlize($_POST['mail_payed'],false),b3_htmlize($_POST['mail_payed_title'],false,""));
	$kaImpostazioni->replaceParam('shop-mail_sended',b3_htmlize($_POST['mail_sended'],false),b3_htmlize($_POST['mail_sended_title'],false,""));
	$kaImpostazioni->replaceParam('shop-mail_from',b3_htmlize($_POST['mail_from1'],false,""),b3_htmlize($_POST['mail_from2'],false,""));
	$kaLog->add("UPD",'Shop: Modificate le notifiche e-mail');
	echo '<div id="MsgSuccess">Notifiche modificate con successo</div>';
	}

$v['shop-mail_from1']="";
$v['shop-mail_from2']="";
$v['shop-mail_checkout1']="";
$v['shop-mail_checkout2']="";
$v['shop-mail_payed1']="";
$v['shop-mail_payed2']="";
$v['shop-mail_sended1']="";
$v['shop-mail_sended2']="";
?>
<h1><?= $kaTranslate->translate('Negozio'); ?></h1>
<?php  include('shopmenu.php'); ?>
<br />

<form action="" method="post">
	<?php 
	$query="SELECT * FROM ".TABLE_CONFIG." WHERE param LIKE 'shop-mail_%' AND ll='".$_SESSION['ll']."'";
	$results=ksql_query($query);
	while($row=ksql_fetch_array($results)) {
		$v[$row['param'].'1']=$row['value1'];
		$v[$row['param'].'2']=$row['value2'];
		}
		?>

	<div class="box">
		<h2 style="display:inline;">Mittente</h2>
		<?= b3_create_input("mail_from1","text","Nome ",b3_lmthize($v['shop-mail_from1'],"input"),"200px"); ?></td>
		<?= b3_create_input("mail_from2","text","E-mail ",b3_lmthize($v['shop-mail_from2'],"input"),"200px"); ?></td>
		</div>
	<br />
	<br />

	<h2>E-mail inviata alla conferma dell'ordine</h2>
	<div class="box">
		<table>
			<tr>
				<td><label for="mail_checkout_title">Oggetto dell'e-mail</label></td>
				<td><?= b3_create_input("mail_checkout_title","text","",b3_lmthize($v['shop-mail_checkout2'],"input"),"300px"); ?></td>
				</tr>
			<tr>
				<td><label for="mail_checkout">Testo dell'e-mail</label></td>
				<td><?= b3_create_textarea("mail_checkout","",b3_lmthize($v['shop-mail_checkout1'],"textarea"),"800px","200px",RICH_EDITOR); ?></td>
				</tr>
			</table>
		</div><br /><br />

	<h2>E-mail inviata al pagamento dell'ordine</h2>
	<div class="box">
		<table>
			<tr>
				<td><label for="mail_checkout_title">Oggetto dell'e-mail</label></td>
				<td><?= b3_create_input("mail_payed_title","text","",b3_lmthize($v['shop-mail_payed2'],"input"),"300px"); ?></td>
				</tr>
			<tr>
				<td><label for="mail_checkout">Testo dell'e-mail</label></td>
				<td><?= b3_create_textarea("mail_payed","",b3_lmthize($v['shop-mail_payed1'],"textarea"),"800px","200px",RICH_EDITOR); ?></td>
				</tr>
			</table>
		</div><br /><br />

	<h2>E-mail inviata alla spedizione dell'ordine</h2>
	<div class="box">
		<table>
			<tr>
				<td><label for="mail_checkout_title">Oggetto dell'e-mail</label></td>
				<td><?= b3_create_input("mail_sended_title","text","",b3_lmthize($v['shop-mail_sended2'],"input"),"300px"); ?></td>
				</tr>
			<tr>
				<td><label for="mail_checkout">Testo dell'e-mail</label></td>
				<td><?= b3_create_textarea("mail_sended","",b3_lmthize($v['shop-mail_sended1'],"textarea"),"800px","200px",RICH_EDITOR); ?></td>
				</tr>
			</table>
		</div><br /><br />

	<script type="text/javascript">
		for(var key in kTxtArea) {
			kTxtArea[key].addKey('separator.gif','|',null,'separator');
			kTxtArea[key].addKey('i_name.gif','Nome dell\'acquirente','setHTMLTag','','','{NAME} ');
			kTxtArea[key].addKey('i_address.gif','Indirizzo dell\'acquirente','setHTMLTag','','','{ADDRESS} ');
			kTxtArea[key].addKey('i_deliverer.gif','Corriere','setHTMLTag','','','{DELIVERER} ');
			kTxtArea[key].addKey('i_tracking.gif','Tracking della spedizione','setHTMLTag','','','{TRACKING_NUMBER} ');
			kTxtArea[key].addKey('i_shipping.gif','Indirizzo di spedizione','setHTMLTag','','','{SHIPPING_ADDRESS} ');
			kTxtArea[key].addKey('i_payment.gif','Modalità di pagamento','setHTMLTag','','','{PAYMENT_METHOD} ');
			kTxtArea[key].addKey('i_orderitems.gif','Riepilogo dell\'ordine','setHTMLTag','','','{ORDER_ITEMS} ');
			kTxtArea[key].addKey('i_ordernumber.gif','Numero ordine','setHTMLTag','','','{ORDER_NUMBER} ');
			kTxtArea[key].addKey('i_invoicedata.gif','Dati di fatturazione','setHTMLTag','','','{BILLING_DATA} ');
			}
		</script>

	<div style="clear:both;"></div>
	<br />
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" /></div>
	</form>

<?php 
include_once("../inc/foot.inc.php");
