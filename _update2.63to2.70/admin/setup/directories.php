<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:Reserved URLs");
include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_POST['update'])) {
	$log="";
	require_once('../menu/menu.lib.php');
	$kaMenu=new kaMenu();
	foreach($_POST['dir'] as $k=>$newdir) {
		$olddir=$kaImpostazioni->getVar($k,1);
		if($kaImpostazioni->replaceParam($k,$newdir,"")) {
			foreach($kaMenu->getMenuElementsByUrl(array("url"=>$olddir)) as $m) {
				$kaMenu->updateDirAndLabel($m['idmenu'],$newdir);
				}
			foreach($kaMenu->getMenuElementsByUrl(array("url"=>$olddir.'/')) as $m) {
				$kaMenu->updateDirAndLabel($m['idmenu'],$newdir);
				}
			}
		else $log.=$kaTranslate->translate("Setup:Error while saving the %s parameter",$k)."<br />";
		}
	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Setup:Error updating reserved URLs');
		}
	else {
		$kaLog->add("UPD",'Setup:Reserved URLs updated');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Setup:Successfully saved').'</div>';
		}
	}
/* FINE AZIONI */

?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<form action="?" method="post">
<table class="tabella">
<tr><th><?= $kaTranslate->translate('Menu:Home page'); ?></th><td><?= b3_create_input("dir[home_page]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/',b3_lmthize($kaImpostazioni->getVar('home_page',1),"input"),"400px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Menu:News'); ?></th><td><?= b3_create_input("dir[dir_news]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/',b3_lmthize($kaImpostazioni->getVar('dir_news',1),"input"),"400px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Menu:Photogallery'); ?></th><td><?= b3_create_input("dir[dir_photogallery]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/',b3_lmthize($kaImpostazioni->getVar('dir_photogallery',1),"input"),"400px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Menu:Private area'); ?></th><td><?= b3_create_input("dir[dir_private]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/',b3_lmthize($kaImpostazioni->getVar('dir_private',1),"input"),"400px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Menu:Shop'); ?></th><td><?= b3_create_input("dir[dir_shop]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/',b3_lmthize($kaImpostazioni->getVar('dir_shop',1),"input"),"400px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Menu:Shop Cart'); ?></th><td><?= b3_create_input("dir[dir_shop_cart]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/'.b3_lmthize($kaImpostazioni->getVar('dir_shop',1),"input").'/',b3_lmthize($kaImpostazioni->getVar('dir_shop_cart',1),"input"),"300px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Menu:Shop Manufacturers'); ?></th><td><?= b3_create_input("dir[dir_shop_manufacturers]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/'.b3_lmthize($kaImpostazioni->getVar('dir_shop',1),"input").'/',b3_lmthize($kaImpostazioni->getVar('dir_shop_manufacturers',1),"input"),"300px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Menu:Users'); ?></th><td><?= b3_create_input("dir[dir_users]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/',b3_lmthize($kaImpostazioni->getVar('dir_users',1),"input"),"400px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Setup:RSS Feed'); ?></th><td><?= b3_create_input("dir[dir_feed]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/',b3_lmthize($kaImpostazioni->getVar('dir_feed',1),"input"),"400px",250); ?></td></tr>
<tr><th><?= $kaTranslate->translate('Setup:Search'); ?></th><td><?= b3_create_input("dir[dir_search]","text",SITE_URL."/".strtolower($_SESSION['ll']).'/',b3_lmthize($kaImpostazioni->getVar('dir_search',1),"input"),"400px",250); ?></td></tr>
</table>
<br /><br />
<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"></div>
</form></div><br /><br />

<?php 
include_once("../inc/foot.inc.php");
