<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
require_once('../../inc/config.lib.php');
if(!isset($_SESSION['iduser'])) die('Operation denied');
if(!isset($_GET['idsvar'])) die('Variation index is missing');

require_once('../shop.lib.php');
$kaShop=new kaShop();
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('shop');

define("PAGE_NAME",$kaTranslate->translate('Shop:Add a new variation'));

if(isset($_POST['save'])) {
	$vars=array();
	$vars['idsvar']=$_GET['idsvar'];
	$vars['name']=$_POST['name'];
	$vars['descr']=b3_htmlize($_POST['descr'],false);
	$vars['price']=$_POST['price'];
	$idsvar=$kaShop->updateVariation($vars);
	if($idsvar) {
		?><script type="text/javascript">window.parent.k_reloadVariations()</script><?php 
		echo $kaTranslate->translate('Shop:Successfully updated');
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

<link rel="stylesheet" href="<?= ADMINDIR; ?>css/init.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/selectmenuref.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/kzeneditor.css?<?= SW_VERSION; ?>" type="text/css" />

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/imgframe.js?<?= SW_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>"></script>
</head>

<body>
<h1><?= PAGE_NAME; ?></h1>
<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>

<form action="?idsvar=<?= $_GET['idsvar']; ?>" method="post">
	<div class="padding">
		<div style="text-align:center;">
		<?php 
		$variation=$kaShop->getVariations(array("idsvar"=>$_GET['idsvar']));
		if(!isset($variation[0])) die('Error retriving variation');
		$variation=$variation[0];

		$collections=$kaShop->getVariationCollections(array("idsitem"=>$variation['idsitem']));
		$values=$collections;
		$labels=$collections;
		$values[]="-new-";
		$labels[]=$kaTranslate->translate("Shop:Add a new collection");
		$display="none";
		if(count($values)>1) echo b3_create_select("collection",$kaTranslate->translate("Shop:Collection").' ',$labels,$values,$variation['collection']);
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
		
		<div class="title"><?= b3_create_input("name","text",$kaTranslate->translate('Shop:Variation name')."<br />",b3_lmthize($variation['name'],"input"),"400px",255,''); ?></div>
		<br />
		</div>
		
		<?= b3_create_textarea("descr",$kaTranslate->translate('Shop:Description')."<br />",b3_lmthize($variation['descr'],"textarea"),"100%","100px"); ?>
		<br />

		<?= b3_create_input("price","text",$kaTranslate->translate('Shop:Price')." ",b3_lmthize($variation['price'],"input"),"100px",8,''); ?>
		<div class="note"><?= $kaTranslate->translate('Shop:You can add, subtract or reset the price using this formulas'); ?>: +1.50 , -7.00 , 15.30 , +10% , -20% , 120%<br />
			<?= $kaTranslate->translate('Shop:Leave empty to maintain the same price of the item'); ?></div>
		<br />

		</div>

	<div class="submit">
		<input type="submit" name="save" value="<?= $kaTranslate->translate("UI:Save"); ?>" class="button" />
		</div>
	</form>

<script type="text/javascript">
	var txts=new kInitZenEditor;
	txts.init('<?= addslashes(ADMINDIR); ?>');
</script>

</body>
</html>