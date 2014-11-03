<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once('./connect.inc.php');
require_once('kalamun.lib.php');
require_once('./sessionmanager.inc.php');
require_once('./main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

require_once(ADMINRELDIR.'menu/menu.lib.php');
$kaMenu=new kaMenu();
if(!isset($_GET['c'])) $_GET['c']='';
$kaMenu->setCollection($_GET['c']);

$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('menu');

define("PAGE_NAME","Men&ugrave;");
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
<h1><?= $kaTranslate->translate("Menu:Select where to insert the page into the menu"); ?></h1>
<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
<div class="smenu sel" id="tabs">
	<ul>
	<?php 
	$menu=array();
	foreach($kaMenu->getCollections() as $k=>$c) { ?>
		<li><a href="?c=<?= $c; ?>" class="<?= ($_GET['c']==$c?'sel':''); ?>"><?= $c==""?$kaTranslate->translate('Menu:Main menu'):$c; ?></a></li>
		<?php  }
	?>
	</ul>
	</div>



<div class="padding" id="DragZone">
	<div class="selectElm"><?php 		$menuC=$kaMenu->getMenuContents();
		$menuS=$kaMenu->getMenuStructure(0);
		
		function printSubMenu($submenu) {
			global $menuC, $kaTranslate;
			
			echo '<ul>';
			$i=0;
			foreach($submenu as $ka=>$v) {
				if($ka!='data') {
					if($i==0) echo '<li class="placeholder"><a href="javascript:selectElement('.$menuC[$v['data']]['idmenu'].',\'before\');"><span>'.$kaTranslate->translate('Menu:Insert before').' “'.$menuC[$v['data']]['label'].'”</span></a></li>';
					echo '<li>'.$menuC[$v['data']]['label'].'</li>';
					if(count($v)<=1) echo '<ul><li class="placeholder"><a href="javascript:selectElement('.$menuC[$v['data']]['idmenu'].',\'inside\');"><span>'.$kaTranslate->translate('Menu:Insert as submenu of').' “'.$menuC[$v['data']]['label'].'”</span></a></li></ul>';
					printSubMenu($v);
					echo '<li class="placeholder"><a href="javascript:selectElement('.$menuC[$v['data']]['idmenu'].',\'after\');"><span>'.$kaTranslate->translate('Menu:Insert after').' “'.$menuC[$v['data']]['label'].'”</span></a></li>';
					$i++;
					}
				}
			echo '</ul>';
			}

		if(count($menuS)==0)
		{
			// if the menu is empty, show a placeholder
			?>
			<ul>
				<li><a href="javascript:selectElement(0,'after');"><?= $kaTranslate->translate("Menu:This menu is currently empty... click here to insert the first element!"); ?></a></li>
			</ul>
			<?php 		} else printSubMenu($menuS);
		?></div>
	</div>

	<script type="text/javascript">
		function selectElement(id,where) {
			window.parent.selectElement(id,where);
			window.parent.k_closeIframeWindow();
			}
		</script>

</body>
</html>