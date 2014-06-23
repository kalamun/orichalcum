<?php
error_reporting(0);
session_start();
if(!isset($_SESSION['iduser'])) die("You don't have permissions to access this informations");
if(!isset($_GET['idord'])) die('Error selecting order');

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../users/users.lib.php');
require_once('../../inc/main.lib.php');
$kaUsers=new kaUsers();
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('shop');

if(!$kaUsers->canIUse('shop')) die("You don't have permissions to access this informations");

include('../shop.lib.php');
$kaShop=new kaShop();

$o=$kaShop->getOrderById($_GET['idord']);
?>
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

<?
if($o['payed']=='n') { ?>
	<input type="button" class="smallbutton" value="<?= $kaTranslate->translate('Shop:Mark as payed'); ?>" onclick="k_openIframeWindow('ajax/paymentManager.php?idord=<?= $o['idord']; ?>','600px','400px');" />
	<? }
else { ?>
	<input type="button" class="smallbutton" value="<?= $kaTranslate->translate('Shop:Reprocess payment'); ?>" onclick="k_openIframeWindow('ajax/paymentManager.php?idord=<?= $o['idord']; ?>','600px','400px');" />
	<? } ?>