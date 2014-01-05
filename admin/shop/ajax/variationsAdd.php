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

define("PAGE_NAME",$kaTranslate->translate('Shop:Add a new variation'));

if(isset($_POST['save'])) {
	if(!isset($_POST['copy'])) $_POST['copy']="";
	if(!isset($_POST['collection'])||$_POST['collection']=="-new-") $_POST['collection']=$_POST['newcollection'];
	$idsvar=$kaShop->addVariation(intval($_GET['idsitem']),$_POST['name'],$_POST['collection'],$_POST['copy']);
	if($idsvar) {
		?>
		<script type="text/javascript">window.parent.k_reloadVariations()</script>
		<meta http-equiv="refresh" content="0; url=variationsEdit.php?idsvar=<?= $idsvar; ?>">
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
		<?
		$collections=$kaShop->getVariationCollections(array("idsitem"=>$_GET['idsitem']));
		$values=$collections;
		$labels=$collections;
		$values[]="-new-";
		$labels[]=$kaTranslate->translate("Shop:Add a new collection");
		$display="none";
		if(count($values)>1) echo b3_create_select("collection",$kaTranslate->translate("Shop:Collection").' ',$labels,$values,"");
		else $display="block";
		?>
		<div id="newcollectionContainer" style="display:<?= $display; ?>;"><?= b3_create_input("newcollection","text",$kaTranslate->translate('Shop:New collection\'s name')."<br />","","200px",64,''); ?></div>
		<script type="text/javascript">
			if(document.getElementById('collection')) {
				kAddEvent(document.getElementById('collection'),"onchange",swapNewCollection);
				}
			function swapNewCollection() {
				var c=document.getElementById('collection');
				var nc=document.getElementById('newcollectionContainer');
				console.log(c.value);
				nc.style.display=(c.value=='-new-'?'block':'none');
				}
			</script>
		
		<br />
		
		<div class="title"><?= b3_create_input("name","text",$kaTranslate->translate('Shop:Variation name')."<br />","","400px",255,''); ?></div>
		<br />

		<?
		$values=array("");
		$labels=array("");
		foreach($kaShop->getVariations(array("idsitem"=>$_GET['idsitem'])) as $row) {
			$values[]=$row['idsvar'];
			$labels[]=$row['name'];
			}
		if(count($values)>1) echo b3_create_select("copy",$kaTranslate->translate("Shop:Copy variation from").' ',$labels,$values,"","auto",true);
		?>
		</div>

	<div class="submit">
		<input type="submit" name="save" value="<?= $kaTranslate->translate("Shop:Create variation"); ?>" class="button" />
		</div>
	</form>

</body>
</html>