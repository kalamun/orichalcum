<?php
/* (c) Kalamun.org - GNU/GPL 3 */

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
require_once('../../inc/config.lib.php');
if(!isset($_SESSION['iduser'])) die('Operation denied');
if(!isset($_GET['idsitem'])) die('Item index is missing');

require_once('../shop.lib.php');
$kaShop=new kaShop();
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('shop');

define("PAGE_NAME",$kaTranslate->translate('Shop:Import variations from another item'));

if(isset($_POST['save'])&&isset($_POST['importfrom'])) {
	$idsvar=$kaShop->importVariations(intval($_POST['importfrom']),intval($_GET['idsitem']));
	if($idsvar) {
		?>
		<script type="text/javascript">
			window.parent.k_reloadVariations();
			//window.parent.k_closeIframeWindow();
			</script>
		<?
		}
	else {
		echo $kaTranslate->translate('Shop:Ops, some errors occurred while saving. Please retry.');
		}
	
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
	@import "<?php echo ADMINDIR; ?>css/selectmenuref.css?<?= SW_VERSION; ?>";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/imgframe.js?<?= SW_VERSION; ?>"></script>
</head>

<body>
<h1><?= PAGE_NAME; ?></h1>
<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>

<form action="?idsitem=<?= $_GET['idsitem']; ?>" method="post">
	<div class="padding" style="text-align:center;">
		<?= $kaTranslate->translate('Shop:Choose the item from which to import'); ?><br />
		<br />
		<select name="importfrom">
		<?
		foreach($kaShop->getItemsList() as $item) {
			if($item['idsitem']!=$_GET['idsitem']&&count($kaShop->getVariations(array("idsitem"=>$item['idsitem'])))>0) { ?>
				<option value="<?= $item['idsitem']; ?>"><?= $item['productcode'].' '.$item['titolo']; ?></option>
				<? }
			}
		?>
		</select>
		</div>

	<div class="submit">
		<input type="submit" name="save" value="<?= $kaTranslate->translate("Shop:Import variations"); ?>" class="button" />
		</div>
	</form>

</body>
</html>