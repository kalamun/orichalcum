<?php
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

if(!isset($_SESSION['iduser'])) die("You don't have permissions to access this informations");
if(!isset($_GET['idord'])) die('Error selecting order');

$kaTranslate->import('shop');

if(!$kaUsers->canIUse('shop')) die("You don't have permissions to access this informations");

include('../shop.lib.php');
$kaShop=new kaShop();

$o=$kaShop->getOrderById($_GET['idord']);
?>
<h2><?= $kaTranslate->translate('Shop:Payment details'); ?></h2>
<table class="transactions"><?php 
if(count($o['transactions'])>0) {
	foreach($o['transactions'] as $t) {
		?><tr><th><?= $t['value']; ?> <?= $t['currency']; ?></th><td><?= $t['friendlydate']; ?> - <?= $t['method']; ?><br /><?= $t['details']; ?></td></tr><?php 
		}
	}
else {
	?><tr><td><?= $kaTranslate->translate('Shop:No payments received yet'); ?></td></tr><?php 
	}
?></table>

<?php 
if($o['payed']=='n') { ?>
	<input type="button" class="smallbutton" value="<?= $kaTranslate->translate('Shop:Mark as payed'); ?>" onclick="k_openIframeWindow('ajax/paymentManager.php?idord=<?= $o['idord']; ?>','600px','400px');" />
	<?php  }
else { ?>
	<input type="button" class="smallbutton" value="<?= $kaTranslate->translate('Shop:Reprocess payment'); ?>" onclick="k_openIframeWindow('ajax/paymentManager.php?idord=<?= $o['idord']; ?>','600px','400px');" />
	<?php  } 