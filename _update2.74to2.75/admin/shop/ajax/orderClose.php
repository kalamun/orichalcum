<?php /* (c) Kalamun.org - GNU/GPL 3 */

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

define("PAGE_NAME",$kaTranslate->translate('Shop:Close'));

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
	if(isset($_POST['close'])) {
		if($kaShop->closeOrder($o['idord'])) {
			echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Successfully saved').'</div>';
			echo $kaTranslate->translate('Shop:This order was moved into CLOSED orders list');
			$o=$kaShop->getOrderById($_GET['idord']);
			?><script type="text/javascript">window.parent.removeOrder('<?= $o['uid']; ?>');</script>
			</body>
			</html>
			<?php  die();
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
			<tr><th><?= $kaTranslate->translate('Shop:Name'); ?></th><td><?= isset($o['member']['name'])?$o['member']['name']:''; ?></td></tr>
			<tr><th><?= $kaTranslate->translate('Shop:E-mail'); ?></th><td><?= isset($o['member']['email'])?$o['member']['email']:''; ?></td></tr>
			<tr><th><?= $kaTranslate->translate('Shop:Username'); ?></th><td><?= isset($o['member']['username'])?$o['member']['username']:''; ?></td></tr>
			<?php 
			if(isset($o['member']['metadata'])) {
				foreach($o['member']['metadata'] as $ka=>$v) {
					?><tr><th><?= $ka; ?></th><td><?= $v; ?></td></tr><?php 
					}
				}
			?>
			</table>
		</td>
		</tr>
	</table>

	<div class="submit">
		<form action="?idord=<?= $o['idord']; ?>" method="post">
			<?= $kaTranslate->translate('Shop:Are you sure?'); ?> <input type="submit" value="<?= $kaTranslate->translate('Shop:Close order'); ?>" class="button" name="close" />
			</form>
		</div>
	</div>

</body>
</html>