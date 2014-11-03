<?php /* (c) Kalamun.org - GNU/GPL 3 */
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

define("PAGE_NAME",$kaTranslate->translate('Shop:Shipment'));
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
	/* ACTIONS */
	//report shipment
	if(isset($_POST['report'])) {
		if($kaShop->reportShipment($o['idord'],$_POST['method'],$_POST['tracking_number'],$_POST['tracking_url'])) {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Successfully saved').'</div>';
			echo $kaTranslate->translate('Shop:This order was moved into CLOSED orders list');
			$o=$kaShop->getOrderById($_GET['idord']);
			?><script type="text/javascript">window.parent.markShipment('<?= $o['uid']; ?>');</script><?php 
			}
		else echo '<div id="MsgAlert">'.$kaTranslate->translate('Shop:Sorry, error while saving').'</div>';
		}
	?>

	<table class="transactions"><?php 
	?></table>

	<?php 
	if($o['shipped']=='n') { ?>
	<div class="reportPayment">
		<h2><?= $kaTranslate->translate('Shop:Report shipment'); ?></h2>
		
		<form action="?idord=<?= $_GET['idord']; ?>" method="post">
			<table>
			<?php 
			$option=array();
			$value=array();
			foreach($kaShop->getDeliverersByZone($o['idzone']) as $p) {
				$option[]=$p['name'];
				$value[]=$p['iddel'];
				} ?>
			<tr><td><label for="method"><?= $kaTranslate->translate('Shop:Deliverer'); ?></label></td><td><?= b3_create_select("method","",$option,$value,$o['iddel']); ?></td>
				<td rowspan="3" class="submit" style="vertical-align:bottom;"><input type="submit" value="<?= $kaTranslate->translate('Shop:Report'); ?>" class="button" name="report" /></td>
				</tr>
			<tr><td><label for="tracking_number"><?= $kaTranslate->translate('Shop:Tracking number'); ?></label></td><td><?= b3_create_input("tracking_number","text","","","100px",255); ?></td></tr>
			<tr><td><label for="tracking_url"><?= $kaTranslate->translate('Shop:Tracking URL'); ?></label></td><td><?= b3_create_input("tracking_url","text","","","200px",255); ?></td></tr>
			</table>
			</form>
		</div>
		<?php  } ?>

	</div>

</body>
</html>