<?php
/* (c) Kalamun.org - GNU/GPL 3 */
error_reporting(0);
session_start();
if(!isset($_SESSION['iduser'])) die("You don't have permissions to access this informations");
if(!isset($_GET['idord'])) die('Error selecting order');

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../users/users.lib.php');
require_once('../../inc/main.lib.php');
require_once("../../inc/config.lib.php");
$kaUsers=new kaUsers();
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('shop');
$kaImpostazioni=new kaImpostazioni();

if(!$kaUsers->canIUse('shop')) die("You don't have permissions to access this informations");

include('../shop.lib.php');
$kaShop=new kaShop();

$o=$kaShop->getOrderById($_GET['idord']);

define("PAGE_NAME",$kaTranslate->translate('Shop:Details'));


function kXmlParser($string) {
	$output=array();
	foreach(preg_split("/\<[^\/][^\>]*?\>/",$string) as $elm) {
		$elm=trim($elm);
		$tag=preg_replace("/.*\<\/(.+)\>$/","$1",$elm);
		$contents=preg_replace("/(.*)\<\/.*\>/","$1",$elm);
		$output[$tag]=$contents;
		}
	return $output;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<style type="text/css">
	@import "<?php echo ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>";
	@import "<?php echo ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>";
	@import "<?php echo ADMINDIR; ?>css/iframewindow.css?<?= SW_VERSION; ?>";
	@import "../css/substyle.css?<?= SW_VERSION; ?>";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>"></script>
</head>

<body>
<h1><?= PAGE_NAME ?> - <?= $kaTranslate->translate('Shop:Order number'); ?> <?= $o['uid']; ?></h1>
<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>

<div class="padding">
	
	<?
	$totalamount=0;
	if(count($o['transactions'])>0) {
		foreach($o['transactions'] as $t) {
			$totalamount+=$t['value'];
			}
		}

	/* ACTIONS */
	//report shipment
	if(isset($_POST['reportshipment'])) {
		if($kaShop->reportShipment($o['idord'],$_POST['method'],$_POST['tracking_number'],$_POST['tracking_url'])) {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Successfully saved').'</div>';
			echo $kaTranslate->translate('Shop:This order was moved into CLOSED orders list');
			$o=$kaShop->getOrderById($_GET['idord']);
			?><script type="text/javascript">window.parent.markShipment('<?= $o['uid']; ?>');</script><?
			}
		else echo '<div id="MsgAlert">'.$kaTranslate->translate('Shop:Sorry, error while saving').'</div>';
		}
	//report payment
	elseif(isset($_POST['reportpayment'])) {
		if($kaShop->addPayment($o['idord'],$_POST['value'],$_POST['method'],$_POST['details'])) {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Successfully saved').'</div>';
			$o=$kaShop->getOrderById($o['idord']);
			$totalamount=0;
			if(count($o['transactions'])>0) {
				foreach($o['transactions'] as $t) {
					$totalamount+=$t['value'];
					}
				}
			if($totalamount>=$o['totalprice']) { ?><script type="text/javascript">window.parent.markPayment('<?= $o['uid']; ?>');</script><? }
			}
		else echo '<div id="MsgAlert">'.$kaTranslate->translate('Shop:Sorry, error while saving').'</div>';
		}
	?>

	<table style="width:100%;">
	<tr>
	<td class="sheetCell">
		<h2><?= $kaTranslate->translate('Shop:Order number'); ?> <?= $o['uid']; ?></h2>
		<table>
			<tr><th><?= $kaTranslate->translate('Shop:Date'); ?></th><td><?= str_replace(" ",' <img src="../../img/clock10.png" width="10" height="10" /> ',$o['friendlydate']); ?></td></tr>
			<tr><th><?= $kaTranslate->translate('Shop:Total amount'); ?></th><td><?= $o['totalprice']; ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?></td></tr>
			</table>
		</td>
	<td class="sheetCell">
		<h2><?= $kaTranslate->translate('Shop:Personal data'); ?></h2>
		<table>
			<tr><th><?= $kaTranslate->translate('Shop:Name'); ?></th><td><?= $o['member']['name']; ?></td></tr>
			<tr><th><?= $kaTranslate->translate('Shop:E-mail'); ?></th><td><?= $o['member']['email']; ?></td></tr>
			<tr><th><?= $kaTranslate->translate('Shop:Username'); ?></th><td><a href="<?= ADMINDIR; ?>members/modifica.php?idmember=<?= $o['member']['idmember']; ?>" target="__top"><?= $o['member']['username']; ?></a></td></tr>
			<?
			foreach(kXmlParser($o['invoice_data']) as $ka=>$v) { ?>
				<tr><th><?= $ka; ?></th><td><?= $v; ?></td></tr>
				<? }
			?>
			</table>
		</td>
		</tr>
	<tr>
	<td class="sheetCell" colspan="2">
		<h2><?= $kaTranslate->translate('Shop:Order details'); ?></h2>
		<table>
			<?
			foreach($o['items'] as $item) { ?>
				<tr><td><?= $item['qta']; ?></td>
					<td><?
						echo $item['titolo'];
						if(isset($item['variations'])&&is_array($item['variations'])) {
							foreach($item['variations'] as $v) {
								echo ', '.$v['collection'].' '.$v['name'];
								}
							}
						?></td>
					<td><?= $item['realprice']; ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?></td>
					<td><small><a href="<?= ADMINRELDIR; ?>shop/edit.php?idsitem=<?= $item['idsitem']; ?>" target="_blank"><?= $kaTranslate->translate('Shop:Sheet'); ?></a></small></td>
					</tr>
				<? }
			?>
			</table>
		
		</td>
		</tr>
	<tr>
	<td class="sheetCell">
		<h2><?= $kaTranslate->translate('Shop:Payment details'); ?></h2>
		<table class="transactions"><?
		if(count($o['transactions'])>0) {
			foreach($o['transactions'] as $t) {
				?><tr><th><?= $t['value']; ?> <?= $t['currency']; ?></th><td><?= $t['friendlydate']; ?> - <?= $t['method']; ?><br /><?= $t['details']; ?></td></tr><?
				}
			}
		else {
			?><tr><td><?= $kaTranslate->translate('Shop:No payments received yet'); ?></td></tr><?
			}
		?></table>

		<? if($o['payed']=='n') { ?>
		<div class="reportPayment">
			<h3 onclick="javascript:document.getElementById('reportPayment').style.display='block';" style="cursor:pointer;"><?= $kaTranslate->translate('Shop:Mark as payed'); ?></h3>
			<form action="?idord=<?= $_GET['idord']; ?>" method="post">
			<div id="reportPayment" style="display:none;">
				<table>
				<? foreach($kaShop->getPaymentMethodsByZone($o['idzone']) as $p) {
					$option[]=$p['name'];
					$value[]=$p['idspay'];
					} ?>
				<tr><td><label for="method"><?= $kaTranslate->translate('Shop:Payment method'); ?></label></td><td><?= b3_create_select("method","",$option,$value,$o['idspay']); ?></td></tr>
				<tr><td><label for="value"><?= $kaTranslate->translate('Shop:Amount'); ?></label></td><td><?= b3_create_input("value","text","",number_format($o['totalprice']-$totalamount,2),"50px",8); ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?></td></tr>
				<tr><td><label for="details"><?= $kaTranslate->translate('Shop:Details'); ?></label></td><td><?= b3_create_textarea("details","","","200px","50px"); ?></td></tr>
				<tr><td colspan="2" class="submit" style="vertical-align:bottom;"><input type="submit" value="<?= $kaTranslate->translate('Shop:Report'); ?>" class="button" name="reportpayment" /></td></tr>
				</table>
				</form>
				</div>
			</div>
			<? } ?>
		</td>
	<td class="sheetCell">
		<h2><?= $kaTranslate->translate('Shop:Invoice data'); ?></h2>
		<table>
		<?
		foreach(kXmlParser($o['invoice_data']) as $ka=>$v) { ?>
			<tr><th><?= $ka; ?></th><td><?= $v; ?></td></tr>
			<? }
		?></table>
		</td>
	</tr>
	<tr>
	<td class="sheetCell">
		<h2><?= $kaTranslate->translate('Shop:Shipment details'); ?></h2>
		<div class="shipped <?= $o['shipped']; ?>" style="margin:10px 0;"><?= $o['shipped']=='n'?$kaTranslate->translate('Shop:Not yet shipped'):$kaTranslate->translate('Shop:Shipped'); ?></div>
		<table>
		<tr><th><?= $kaTranslate->translate('Shop:Deliverer'); ?></th><td><?= $o['deliverer']['name']; ?></td></tr>
		<? if($o['shipped']=='s') {
			echo '<tr><th>'.$kaTranslate->translate('Shop:Shipped on').'</th><td>';
			echo preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3.$2.$1 <img src=\"../img/clock10.png\" width=\"10\" height=\"10\" /> $4:$5",$o['shippedon']).'</td></tr>';
			echo '<tr><th>'.$kaTranslate->translate('Shop:Deliverer').'</th><td>'.$o['deliverer']['name'].'</td></tr>';
			echo '<tr><th>'.$kaTranslate->translate('Shop:Tracking number').'</th><td>'.$o['tracking_number'].'</td></tr>';
			echo '<tr><th>'.$kaTranslate->translate('Shop:Tracking URL').'</th><td><a href="'.$o['tracking_url'].'">'.$o['tracking_url'].'</a></td></tr>';
			} ?>
		</table>
		<?
		if($o['shipped']=='n') { ?>
		<div class="reportPayment">
			<h3 onclick="javascript:document.getElementById('reportShipment').style.display='block';" style="cursor:pointer;"><?= $kaTranslate->translate('Shop:Report shipment'); ?></h3>
			
			<div id="reportShipment" style="display:none;">
				<form action="?idord=<?= $_GET['idord']; ?>" method="post">
					<table>
					<? foreach($kaShop->getDeliverersByZone($o['idzone']) as $p) {
						$option[]=$p['name'];
						$value[]=$p['iddel'];
						} ?>
					<tr><td><label for="method"><?= $kaTranslate->translate('Shop:Deliverer'); ?></label></td><td><?= b3_create_select("method","",$option,$value,$o['iddel']); ?></td></tr>
					<tr><td><label for="tracking_number"><?= $kaTranslate->translate('Shop:Tracking number'); ?></label></td><td><?= b3_create_input("tracking_number","text","","","100px",255); ?></td></tr>
					<tr><td><label for="tracking_url"><?= $kaTranslate->translate('Shop:Tracking URL'); ?></label></td><td><?= b3_create_input("tracking_url","text","","","120px",255); ?></td></tr>
					<tr><td colspan="2" class="submit" style="vertical-align:bottom;"><input type="submit" value="<?= $kaTranslate->translate('Shop:Report'); ?>" class="button" name="reportshipment" /></td></tr>
					</table>
					</form>
				</div>
			</div>
			<? } ?>
		</td>
	<td class="sheetCell">
		<h2><?= $kaTranslate->translate('Shop:Shipment data'); ?></h2>
		<table>
		<?
		foreach(kXmlParser($o['shipping_address']) as $ka=>$v) { ?>
			<tr><th><?= $ka; ?></th><td><?= $v; ?></td></tr>
			<? }
		?></table>
		</td>
	</tr>
	</table>

	</div>

</body>
</html>