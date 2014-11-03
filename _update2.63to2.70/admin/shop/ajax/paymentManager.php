<?php /* (c) Kalamun.org - GNU/GPL 3 */
//error_reporting(0);
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

define("PAGE_NAME",$kaTranslate->translate('Shop:Transactions'));
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
	
	<?php 
	$totalamount=0;
	if(count($o['transactions'])>0) {
		foreach($o['transactions'] as $t) {
			$totalamount+=$t['value'];
			}
		}

	/* ACTIONS */
	//report payment
	if(isset($_POST['report'])) {
		if($kaShop->addPayment($o['idord'],$_POST['value'],$_POST['method'],$_POST['details'])) {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Successfully saved').'</div>';
			$o=$kaShop->getOrderById($_GET['idord']);
			$totalamount=0;
			if(count($o['transactions'])>0) {
				foreach($o['transactions'] as $t) {
					$totalamount+=$t['value'];
					}
				}
			if($totalamount>=$o['totalprice']) { ?><script type="text/javascript">window.parent.markPayment('<?= $o['uid']; ?>');</script><?php  }
			}
		else echo '<div id="MsgAlert">'.$kaTranslate->translate('Shop:Sorry, error while saving').'</div>';
		}
	elseif(isset($_POST['reprocess'])) {
		if($kaShop->reprocessPayment($o['idord'])) {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Successfully reprocessed').'</div>';
			$o=$kaShop->getOrderById($_GET['idord']);
			}
		}
	?>

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
	if($o['totalprice']>$totalamount) { ?>
	<div class="reportPayment">
		<h2><?= $kaTranslate->translate('Shop:Report a payment'); ?></h2>
		
		<form action="?idord=<?= $_GET['idord']; ?>" method="post">
			<table>
			<?php 
			$option=array();
			$value=array();
			foreach($kaShop->getPaymentMethodsByZone($o['idzone']) as $p) {
				$option[]=$p['name'];
				$value[]=$p['idspay'];
				} ?>
			<tr><td><label for="method"><?= $kaTranslate->translate('Shop:Payment method'); ?></label></td><td><?= b3_create_select("method","",$option,$value,$o['idspay']); ?></td>
				<td rowspan="3" class="submit" style="vertical-align:bottom;"><input type="submit" value="<?= $kaTranslate->translate('Shop:Report'); ?>" class="button" name="report" /></td>
				</tr>
			<tr><td><label for="value"><?= $kaTranslate->translate('Shop:Amount'); ?></label></td><td><?= b3_create_input("value","text","",number_format($o['totalprice']-$totalamount,2,".",""),"50px",8); ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?></td></tr>
			<tr><td><label for="details"><?= $kaTranslate->translate('Shop:Details'); ?></label></td><td><?= b3_create_textarea("details","","","200px","50px"); ?></td></tr>
			</table>
			</form>
		</div>
		<?php  }

	else { ?>
	<div class="reportPayment">
		<h2><?= $kaTranslate->translate('Shop:Reprocess payment'); ?></h2>
		
		<form action="?idord=<?= $_GET['idord']; ?>" method="post">
			<table>
			<tr><td class="submit" style="vertical-align:bottom;">
				<input type="submit" value="<?= $kaTranslate->translate('Shop:Report'); ?>" class="button" name="reprocess" />
				</td></tr>
			</table>
			</form>
		</div>
		<?php  }
		?>

	</div>

</body>
</html>