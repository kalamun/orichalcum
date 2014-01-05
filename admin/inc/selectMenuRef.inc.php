<?php
/* (c) Kalamun.org - GNU/GPL 3 */

require_once('./connect.inc.php');
require_once('kalamun.lib.php');
require_once('./sessionmanager.inc.php');
require_once('./main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

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
<h1>Men&ugrave;</h1>
<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
<div class="help">Seleziona il punto in cui vuoi inserire la pagina</div>


<div class="padding" id="DragZone">
	<div class="selectElm"><?php
		require_once(ADMINRELDIR.'menu/menu.lib.php');
		$kaMenu=new kaMenu();
		$menuC=$kaMenu->getMenuContents();
		$menuS=$kaMenu->getMenuStructure(0);
		
		function printSubMenu($submenu) {
			global $menuC;
			
			echo '<ul>';
			$i=0;
			foreach($submenu as $ka=>$v) {
				if($ka!='data') {
					if($i==0) echo '<li class="segnaposto"><a href="javascript:selectElement('.$menuC[$v['data']]['idmenu'].',\'before\');"></a></li>';
					echo '<li>'.$menuC[$v['data']]['label'].'</li>';
					if(count($v)<=1) echo '<ul><li class="segnaposto"><a href="javascript:selectElement('.$menuC[$v['data']]['idmenu'].',\'inside\');"></a></li></ul>';
					printSubMenu($v);
					echo '<li class="segnaposto"><a href="javascript:selectElement('.$menuC[$v['data']]['idmenu'].',\'after\');"></a></li>';
					$i++;
					}
				}
			echo '</ul>';
			}
		printSubMenu($menuS);
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